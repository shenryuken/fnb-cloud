<?php

namespace App\DTOs;

/**
 * Complete, framework-agnostic description of an order to be created.
 *
 * Produced from the Livewire POS component or an API request, then consumed
 * by CreateOrderAction. Pricing fields (subtotal/discount/tax/total) are passed
 * through as computed by the caller; CreateOrderAction re-validates voucher,
 * points, and loyalty rules server-side before persisting.
 */
class CreateOrderData
{
    /**
     * @param  array<int, CartItemData>  $items
     * @param  array<int, array{method: string, amount: float}>|null  $paymentSplits
     */
    public function __construct(
        public array $items,
        public string $orderType = 'dine_in',
        public ?int $customerId = null,
        public ?int $tableId = null,
        public ?string $tableNumber = null,
        public ?string $notes = null,
        public ?int $shiftId = null,
        public ?int $userId = null,

        // Pricing (computed by the pricing service / caller)
        public float $subtotalAmount = 0,
        public float $totalAmount = 0,
        public string $discountType = 'percent',
        public float $discountValue = 0,
        public float $discountAmount = 0,
        public float $manualDiscountAmount = 0,
        public float $taxRate = 0,
        public float $taxAmount = 0,

        // Voucher + loyalty
        public ?string $voucherCode = null,
        public int $pointsRedeemed = 0,
        public float $pointsRedeemPoints = 100,
        public float $pointsRedeemAmount = 1,
        public float $pointsEarnRate = 1,
        public float $pointsPromoMultiplier = 1.0,

        // Payment
        public string $paymentMethod = 'cash',
        public bool $isSplitPayment = false,
        public ?array $paymentSplits = null,
        public float $amountReceived = 0,
        public float $changeAmount = 0,

        // Status: 'paid' for immediate checkout, 'unpaid' for pay-later
        public string $paymentStatus = 'paid',
        public string $status = 'completed',

        // Offline sync idempotency key
        public ?string $clientUuid = null,
        public string $source = 'web',
    ) {}
}
