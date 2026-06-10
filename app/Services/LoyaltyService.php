<?php

namespace App\Services;

use App\Exceptions\OrderException;
use App\Models\Customer;

/**
 * Loyalty point earn/redeem logic, extracted from Pos::checkout().
 *
 * Must be used inside a DB transaction with the customer row locked
 * (lockForUpdate) so concurrent orders cannot oversend a point balance.
 */
class LoyaltyService
{
    /**
     * Validate that the customer can redeem the requested points.
     * Throws OrderException on failure.
     */
    public function assertCanRedeem(?Customer $customer, int $points): void
    {
        if ($points <= 0) {
            return;
        }

        if (!$customer) {
            throw OrderException::make('Select a customer to redeem points.');
        }

        if ((int) $customer->points_balance < $points) {
            throw OrderException::make('Customer does not have enough points.');
        }
    }

    /**
     * Compute how many points an order earns.
     */
    public function earnedPoints(float $subTotal, float $discountAmount, float $earnRate, float $promoMultiplier): int
    {
        $earnBase = round(max(0, $subTotal - $discountAmount), 2);
        $earned = (int) floor($earnBase * $earnRate * $promoMultiplier);

        return max(0, $earned);
    }

    /**
     * Apply the net balance change (earn minus redeem) to the customer.
     * Returns the number of points earned for recording on the order.
     */
    public function applyBalanceChange(Customer $customer, int $pointsRedeemed, float $subTotal, float $discountAmount, float $earnRate, float $promoMultiplier): int
    {
        if ($earnRate > 0) {
            $earned = $this->earnedPoints($subTotal, $discountAmount, $earnRate, $promoMultiplier);
            $newBalance = (int) $customer->points_balance - $pointsRedeemed + $earned;
            $customer->update(['points_balance' => max(0, $newBalance)]);

            return $earned;
        }

        if ($pointsRedeemed > 0) {
            $customer->update(['points_balance' => max(0, (int) $customer->points_balance - $pointsRedeemed)]);
        }

        return 0;
    }
}
