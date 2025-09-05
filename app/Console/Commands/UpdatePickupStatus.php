<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\User;
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
    protected $signature = 'pickup:update-status {--now=} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update pickup status automatically based on scheduled pickup time';

    private WhatsAppNotificationService $whatsappService;
    private string $timezone = 'Asia/Makassar';
    private ?Carbon $overrideNow = null;

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

        // Optionally override current time for testing (expects 'Y-m-d H:i' or 'Y-m-d H:i:s' in WITA)
        $nowOption = $this->option('now');
        if (!empty($nowOption)) {
            try {
                $this->overrideNow = Carbon::parse($nowOption, $this->timezone);
                $this->info('Using overridden current time (WITA): ' . $this->overrideNow->toDateTimeString());
            } catch (\Throwable $e) {
                $this->error('Invalid --now format. Use "Y-m-d H:i" or "Y-m-d H:i:s" (WITA).');
                return 1;
            }
        }

        // Get candidate orders; avoid DB-specific JSON path filter for portability
        $orders = Order::where('status', 'confirmed')
            ->whereNotNull('metadata')
            ->get();

        $this->line('Candidates (confirmed with metadata): ' . $orders->count());

        $updatedCount = 0;

        foreach ($orders as $order) {
            $pickupData = $order->metadata['pickup_request'] ?? null;

            if (!$pickupData) {
                continue;
            }

            // Parse window
            [$startAt, $endAt] = $this->parsePickupWindow($pickupData);
            if ($startAt === null || $endAt === null) {
                $this->line("Order #{$order->order_number}: invalid pickup window format: " . ($pickupData['pickup_time'] ?? ''));
                continue;
            }

            $now = $this->overrideNow ?? Carbon::now($this->timezone);

            // Buffer window
            $bufferedStart = $startAt->copy()->subMinutes(3);
            $bufferedEnd = $endAt->copy()->addMinutes(3);

            // Consider due once the window has started (>= start with buffer)
            $due = $now->greaterThanOrEqualTo($bufferedStart);

            $this->line(
                sprintf(
                    'Order #%s: date=%s time=%s | window=%s..%s | now=%s | due(started?)=%s',
                    $order->order_number,
                    $pickupData['pickup_date'] ?? '-',
                    $pickupData['pickup_time'] ?? '-',
                    $bufferedStart->toDateTimeString(),
                    $bufferedEnd->toDateTimeString(),
                    $now->toDateTimeString(),
                    $due ? 'yes' : 'no'
                )
            );

            if ($due) {
                if ($this->option('dry-run')) {
                    $this->line("DRY-RUN: would update order #{$order->order_number} to picked_up");
                } else {
                    $this->updateOrderToPickedUp($order, $pickupData);
                    $updatedCount++;
                }
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
        $pickupTime = (string) $pickupData['pickup_time'];

        // Parse pickup time range (tolerant): supports "08:00-10:00", "08:00 - 10:00", "08.00 - 10.00", en/em dashes
        $timeRange = preg_split('/\s*[-–—]\s*/', trim($pickupTime));
        if (!$timeRange || count($timeRange) !== 2) {
            return false;
        }

        // Extract time tokens and normalize dot to colon
        $extractTime = function (string $raw): ?string {
            $raw = trim($raw);
            if (preg_match('/(\d{1,2}[:\.]\d{2}(?::\d{2})?)/', $raw, $matches)) {
                $candidate = $matches[1];
                return str_replace('.', ':', $candidate);
            }
            return null;
        };

        $startTimeStr = $extractTime($timeRange[0]);
        $endTimeStr = $extractTime($timeRange[1]);
        if ($startTimeStr === null || $endTimeStr === null) {
            return false;
        }

        // Create datetime objects
        $parseWithFormats = function (string $date, string $time, string $tz): ?Carbon {
            $candidates = [
                'Y-m-d H:i',
                'Y-m-d H:i:s',
            ];
            foreach ($candidates as $format) {
                try {
                    $dt = Carbon::createFromFormat($format, $date . ' ' . $time, $tz);
                    if ($dt !== false) {
                        return $dt;
                    }
                } catch (\Throwable $e) {
                    // try next
                }
            }
            return null;
        };

        $pickupDateTime = $parseWithFormats($pickupDate, $startTimeStr, $this->timezone);
        $pickupEndTime = $parseWithFormats($pickupDate, $endTimeStr, $this->timezone);
        if ($pickupDateTime === null || $pickupEndTime === null) {
            return false;
        }

        $currentDateTime = $this->overrideNow ?? Carbon::now($this->timezone);

        // Check if current time is within pickup window (with 3 minutes buffer)
        return $currentDateTime->between($pickupDateTime->copy()->subMinutes(3), $pickupEndTime->copy()->addMinutes(3));
    }

    /**
     * Parse pickup window start/end in WITA. Returns [Carbon|null, Carbon|null]
     */
    private function parsePickupWindow(array $pickupData): array
    {
        if (!isset($pickupData['pickup_date']) || !isset($pickupData['pickup_time'])) {
            return [null, null];
        }

        $pickupDate = $pickupData['pickup_date'];
        $pickupTime = (string) $pickupData['pickup_time'];

        $timeRange = preg_split('/\s*[-–—]\s*/', trim($pickupTime));
        if (!$timeRange || count($timeRange) !== 2) {
            return [null, null];
        }

        $extractTime = function (string $raw): ?string {
            $raw = trim($raw);
            if (preg_match('/(\d{1,2}[:\.]\d{2}(?::\d{2})?)/', $raw, $matches)) {
                $candidate = $matches[1];
                return str_replace('.', ':', $candidate);
            }
            return null;
        };

        $startTimeStr = $extractTime($timeRange[0]);
        $endTimeStr = $extractTime($timeRange[1]);
        if ($startTimeStr === null || $endTimeStr === null) {
            return [null, null];
        }

        try {
            $start = Carbon::parse($pickupDate . ' ' . $startTimeStr, $this->timezone);
            $end = Carbon::parse($pickupDate . ' ' . $endTimeStr, $this->timezone);
        } catch (\Throwable $e) {
            return [null, null];
        }

        return [$start, $end];
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
            // Resolve a valid user model to attribute the notification
            $triggerUser = User::find(1) ?? $order->admin ?? $order->customer;
            $whatsappLink = $this->whatsappService->generateStatusNotificationLink($order, 'picked_up', $triggerUser);

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
