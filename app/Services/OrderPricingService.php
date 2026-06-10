<?php

namespace App\Services;

/**
 * Pure pricing calculator for orders.
 *
 * Extracted verbatim from Pos::recalculateTotals() so the POS component and
 * the API share one source of truth for money math. No side effects, no DB:
 * give it the inputs, get back the breakdown.
 */
class OrderPricingService
{
    /**
     * Compute the full pricing breakdown for a cart.
     *
     * @param  float  $subTotal              Sum of line subtotals.
     * @param  string $discountType          'fixed' or 'percent'.
     * @param  float  $discountValue         Manual discount value.
     * @param  array<int, array{name?: string, rate?: float}>  $taxes  Active tax lines.
     * @param  string|null $voucherDiscountType  'fixed' or 'percent'.
     * @param  float  $voucherDiscountValue
     * @param  string|null $voucherCode      Applied voucher code (null/empty = none).
     * @param  int    $appliedPoints         Points being redeemed.
     * @param  float  $pointsRedeemAmount    Cash value for $pointsRedeemPoints points.
     * @param  float  $pointsRedeemPoints    Points required to equal $pointsRedeemAmount.
     *
     * @return array{
     *     subtotal: float,
     *     manual_discount: float,
     *     voucher_discount: float,
     *     points_discount: float,
     *     discount_amount: float,
     *     taxable: float,
     *     tax_rate: float,
     *     tax_amount: float,
     *     tax_breakdown: array<int, array{name: string, rate: float, amount: float}>,
     *     total: float
     * }
     */
    public function calculate(
        float $subTotal,
        string $discountType,
        float $discountValue,
        array $taxes,
        ?string $voucherDiscountType = null,
        float $voucherDiscountValue = 0,
        ?string $voucherCode = null,
        int $appliedPoints = 0,
        float $pointsRedeemAmount = 1,
        float $pointsRedeemPoints = 100,
    ): array {
        $subTotal = round(max(0, $subTotal), 2);

        $manualDiscount = 0.0;
        if ($discountType === 'fixed') {
            $manualDiscount = round(min($subTotal, max(0, $discountValue)), 2);
        } else {
            $rate = min(100, max(0, $discountValue));
            $manualDiscount = round($subTotal * ($rate / 100), 2);
        }

        $remainingAfterManual = round(max(0, $subTotal - $manualDiscount), 2);

        $voucherDiscount = 0.0;
        if (filled($voucherCode) && $voucherDiscountValue > 0) {
            if ($voucherDiscountType === 'fixed') {
                $voucherDiscount = round(min($remainingAfterManual, max(0, $voucherDiscountValue)), 2);
            } else {
                $rate = min(100, max(0, $voucherDiscountValue));
                $voucherDiscount = round($remainingAfterManual * ($rate / 100), 2);
            }
        }

        $remainingAfterVoucher = round(max(0, $remainingAfterManual - $voucherDiscount), 2);

        $pointsDiscount = 0.0;
        if ($appliedPoints > 0) {
            $valuePerPoint = $pointsRedeemAmount / max(1, $pointsRedeemPoints);
            $amount = round($appliedPoints * max(0, $valuePerPoint), 2);
            if ($amount > 0) {
                $pointsDiscount = round(min($remainingAfterVoucher, $amount), 2);
            }
        }

        $totalDiscount = round(min($subTotal, $manualDiscount + $voucherDiscount + $pointsDiscount), 2);
        $taxable = round(max(0, $subTotal - $totalDiscount), 2);

        $taxRateSum = 0.0;
        foreach ($taxes as $t) {
            $taxRateSum += (float) ($t['rate'] ?? 0);
        }
        $taxRate = round($taxRateSum, 2);

        $breakdown = [];
        $taxSum = 0.0;
        foreach ($taxes as $t) {
            $rate = (float) ($t['rate'] ?? 0);
            if ($rate <= 0) {
                continue;
            }

            $name = trim((string) ($t['name'] ?? 'Tax'));
            $amount = round($taxable * ($rate / 100), 2);
            $breakdown[] = [
                'name' => $name !== '' ? $name : 'Tax',
                'rate' => round($rate, 2),
                'amount' => $amount,
            ];
            $taxSum += $amount;
        }

        $taxSum = round($taxSum, 2);

        return [
            'subtotal' => $subTotal,
            'manual_discount' => $manualDiscount,
            'voucher_discount' => $voucherDiscount,
            'points_discount' => $pointsDiscount,
            'discount_amount' => $totalDiscount,
            'taxable' => $taxable,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxSum,
            'tax_breakdown' => $breakdown,
            'total' => round($taxable + $taxSum, 2),
        ];
    }

    /**
     * Resolve the active points-earn promo multiplier for the current time.
     * Mirrors Pos::currentPointsPromoMultiplier().
     */
    public function promoMultiplier(
        bool $promoEnabled,
        float $multiplier,
        ?string $startsAt = null,
        ?string $endsAt = null,
    ): float {
        if (!$promoEnabled) {
            return 1.0;
        }

        $multiplier = max(0, $multiplier);
        if ($multiplier <= 0) {
            return 1.0;
        }

        $now = now();
        $start = filled($startsAt) ? \Illuminate\Support\Carbon::parse($startsAt) : null;
        $end = filled($endsAt) ? \Illuminate\Support\Carbon::parse($endsAt) : null;

        if ($start && $now->lt($start)) {
            return 1.0;
        }
        if ($end && $now->gt($end)) {
            return 1.0;
        }

        return $multiplier;
    }
}
