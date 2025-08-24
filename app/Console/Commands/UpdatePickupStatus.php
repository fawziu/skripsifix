<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\WhatsAppNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdatePickupStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pickup:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update pickup status automatically based on scheduled pickup time';

    private WhatsAppNotificationService $whatsappService;

    public function __construct(WhatsAppNotificationService $whatsappService)
    {
        parent::__construct();
        $this->whatsappService = $whatsappService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for pickup status updates...');

        // Get orders with pickup requests that are due
        $orders = Order::where('status', 'confirmed')
            ->whereNotNull('metadata->pickup_request')
            ->get();

        $updatedCount = 0;

        foreach ($orders as $order) {
            $pickupData = $order->metadata['pickup_request'] ?? null;

            if (!$pickupData) {
                continue;
            }

            // Check if pickup time has arrived
            if ($this->isPickupTimeDue($pickupData)) {
                $this->updateOrderToPickedUp($order, $pickupData);
                $updatedCount++;
            }
        }

        $this->info("Updated {$updatedCount} orders to picked up status.");

        return 0;
    }

    /**
     * Check if pickup time has arrived
     */
    private function isPickupTimeDue(array $pickupData): bool
    {
        if (!isset($pickupData['pickup_date']) || !isset($pickupData['pickup_time'])) {
            return false;
        }

        $pickupDate = $pickupData['pickup_date'];
        $pickupTime = $pickupData['pickup_time'];

        // Parse pickup time range (e.g., "08:00-10:00")
        $timeRange = explode('-', $pickupTime);
        if (count($timeRange) !== 2) {
            return false;
        }

        $startTime = $timeRange[0];
        $endTime = $timeRange[1];

        // Create datetime objects
        $pickupDateTime = Carbon::parse($pickupDate . ' ' . $startTime);
        $currentDateTime = Carbon::now();

        // Check if current time is within pickup window (with 3 minutes buffer)
        $pickupEndTime = Carbon::parse($pickupDate . ' ' . $endTime);

        return $currentDateTime->between($pickupDateTime->subMinutes(3), $pickupEndTime->addMinutes(3));
    }

    /**
     * Update order status to picked up
     */
    private function updateOrderToPickedUp(Order $order, array $pickupData): void
    {
        try {
            // Update order status
            $order->update([
                'status' => 'picked_up',
            ]);

            // Create status history
            $order->statusHistory()->create([
                'status' => 'picked_up',
                'notes' => 'Status otomatis diupdate: Pickup time telah tiba - ' .
                          $pickupData['pickup_date'] . ' ' . $pickupData['pickup_time'],
                'updated_by' => 1, // System user
            ]);

            // Generate WhatsApp notification
            $whatsappLink = $this->whatsappService->generateStatusNotificationLink(
                $order,
                'picked_up',
                (object) ['name' => 'System']
            );

            // Log the update
            Log::info('Automatic pickup status update', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'pickup_date' => $pickupData['pickup_date'],
                'pickup_time' => $pickupData['pickup_time'],
                'whatsapp_link' => $whatsappLink,
            ]);

            $this->info("Order #{$order->order_number} updated to picked up status.");

        } catch (\Exception $e) {
            Log::error('Failed to update pickup status automatically', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            $this->error("Failed to update order #{$order->order_number}: " . $e->getMessage());
        }
    }
}
