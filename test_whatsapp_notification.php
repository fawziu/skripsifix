<?php

require_once 'vendor/autoload.php';

use App\Services\WhatsAppNotificationService;
use App\Models\Order;
use App\Models\User;

// This is a simple test to demonstrate the WhatsApp notification functionality
// In a real application, this would be integrated into the Laravel framework

echo "=== WhatsApp Notification Service Test ===\n\n";

// Example usage of the WhatsApp notification service
$whatsappService = new WhatsAppNotificationService();

// Mock order data for testing
$mockOrder = (object) [
    'id' => 1,
    'order_number' => 'ORD-2024-001',
    'item_description' => 'Paket Elektronik',
    'tracking_number' => 'JNE123456789',
    'total_amount' => 150000,
    'customer' => (object) [
        'name' => 'John Doe',
        'phone' => '081234567890'
    ]
];

$mockUser = (object) [
    'name' => 'Admin User'
];

echo "1. Testing Status Update Notification:\n";
$statusLink = $whatsappService->generateStatusNotificationLink($mockOrder, 'confirmed', $mockUser);
echo "WhatsApp Link: " . ($statusLink ?? 'No link generated (no phone number)') . "\n\n";

echo "2. Testing Order Confirmation Notification:\n";
$confirmationLink = $whatsappService->generateOrderConfirmationLink($mockOrder, $mockUser);
echo "WhatsApp Link: " . ($confirmationLink ?? 'No link generated (no phone number)') . "\n\n";

echo "3. Testing Courier Assignment Notification:\n";
$courierLink = $whatsappService->generateCourierAssignmentLink($mockOrder, $mockUser);
echo "WhatsApp Link: " . ($courierLink ?? 'No link generated (no phone number)') . "\n\n";

echo "4. Testing Delivery Completion Notification:\n";
$deliveryLink = $whatsappService->generateDeliveryCompletionLink($mockOrder, $mockUser);
echo "WhatsApp Link: " . ($deliveryLink ?? 'No link generated (no phone number)') . "\n\n";

echo "=== Test Complete ===\n";
echo "Note: These links will open WhatsApp with pre-filled messages for the customer.\n";
echo "The messages include order details, status updates, and relevant information.\n";
