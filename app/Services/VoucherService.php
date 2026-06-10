<?php

namespace App\Services;

use App\Exceptions\OrderException;
use App\Models\Customer;
use App\Models\CustomerVoucher;
use App\Models\Order;
use App\Models\Voucher;

/**
 * Encapsulates voucher resolution + validation for order creation.
 *
 * Extracted from Pos::checkout(). MUST be called inside a DB transaction
 * because it relies on lockForUpdate() to prevent double-spend / race
 * conditions on voucher usage counts and per-customer limits.
 */
class VoucherService
{
    /**
     * Resolve and fully validate a voucher for the given checkout context.
     * Throws OrderException (user-safe message) on any validation failure.
     *
     * @return array{voucher: Voucher|null, customerVoucher: CustomerVoucher|null}
     */
    public function validateForCheckout(
        ?string $voucherCode,
        ?Customer $customer,
        int $points,
        float $manualDiscountAmount,
    ): array {
        if (!filled($voucherCode)) {
            return ['voucher' => null, 'customerVoucher' => null];
        }

        $voucherCode = strtoupper(trim($voucherCode));

        $customerVoucher = CustomerVoucher::query()->where('code', $voucherCode)->lockForUpdate()->first();
        if ($customerVoucher) {
            $voucher = Voucher::query()->where('id', $customerVoucher->voucher_id)->lockForUpdate()->first();
        } else {
            $voucher = Voucher::query()->where('code', $voucherCode)->lockForUpdate()->first();
        }

        if (
            !$voucher
            || !(bool) $voucher->is_active
            || ($voucher->starts_at && now()->lt($voucher->starts_at))
            || ($voucher->ends_at && now()->gt($voucher->ends_at))
            || ($voucher->usage_limit !== null && (int) $voucher->usage_count >= (int) $voucher->usage_limit)
        ) {
            throw OrderException::make('Voucher code is not valid.');
        }

        if ($customerVoucher) {
            if (!$customer) {
                throw OrderException::make('Select a customer to use this voucher.');
            }
            if ((int) $customerVoucher->customer_id !== (int) $customer->id) {
                throw OrderException::make('This voucher is not assigned to this customer.');
            }
            if ($customerVoucher->used_at !== null || $customerVoucher->used_order_id !== null) {
                throw OrderException::make('This voucher has already been used.');
            }
            if ($customerVoucher->expires_at && now()->gt($customerVoucher->expires_at)) {
                throw OrderException::make('This voucher has expired.');
            }
        }

        if ($voucher->per_customer_limit !== null || (bool) $voucher->first_time_only) {
            if (!$customer) {
                throw OrderException::make('Select a customer to use this voucher.');
            }
        }

        if (!(bool) $voucher->can_combine_with_points && $points > 0) {
            throw OrderException::make('This voucher cannot be combined with points.');
        }
        if (!(bool) $voucher->can_combine_with_manual_discount && $manualDiscountAmount > 0) {
            throw OrderException::make('This voucher cannot be combined with manual discount.');
        }

        if ($customer && (bool) $voucher->first_time_only) {
            $hasOrders = Order::query()
                ->where('customer_id', $customer->id)
                ->where('payment_status', 'paid')
                ->exists();
            if ($hasOrders) {
                throw OrderException::make('This voucher is only for first-time customers.');
            }
        }

        if ($customer && $voucher->per_customer_limit !== null) {
            $used = Order::query()
                ->where('customer_id', $customer->id)
                ->where('voucher_id', $voucher->id)
                ->where('payment_status', 'paid')
                ->lockForUpdate()
                ->count();
            if ($used >= (int) $voucher->per_customer_limit) {
                throw OrderException::make('This voucher has already been used by this customer.');
            }
        }

        return ['voucher' => $voucher, 'customerVoucher' => $customerVoucher];
    }

    /**
     * Mark a voucher as consumed by an order (increments usage, links customer voucher).
     * Must run inside the same transaction as validateForCheckout().
     */
    public function consume(Voucher $voucher, ?CustomerVoucher $customerVoucher, Order $order): void
    {
        Voucher::where('id', $voucher->id)->lockForUpdate()->increment('usage_count');

        if ($customerVoucher) {
            $customerVoucher->update([
                'used_order_id' => $order->id,
                'used_at' => now(),
            ]);
        }
    }

    /**
     * Auto-issue any min-spend vouchers triggered by this order.
     * Mirrors the issuable-voucher loop in Pos::checkout().
     *
     * @return array<int, array{code: string, expires_at: string|null}>
     */
    public function issueOnSpend(Customer $customer, Order $order): array
    {
        $issuedCodes = [];

        $issuable = Voucher::query()
            ->whereNotNull('issue_on_min_spend')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->get();

        foreach ($issuable as $v) {
            $minSpend = (float) ($v->issue_on_min_spend ?? 0);
            if ($minSpend <= 0 || (float) $order->total_amount < $minSpend) {
                continue;
            }

            $expiresAt = null;
            if ($v->issue_expires_in_days !== null) {
                $expiresAt = now()->addDays((int) $v->issue_expires_in_days);
            }
            if ($v->ends_at && (!$expiresAt || $v->ends_at->lt($expiresAt))) {
                $expiresAt = $v->ends_at;
            }

            $prefix = strtoupper(trim((string) ($v->code ?? 'VOUCHER')));
            $generated = null;
            for ($i = 0; $i < 5; $i++) {
                $candidate = $prefix . '-' . now()->format('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
                if (!CustomerVoucher::where('code', $candidate)->exists()) {
                    $generated = $candidate;
                    break;
                }
            }
            if (!$generated) {
                continue;
            }

            CustomerVoucher::create([
                'voucher_id' => $v->id,
                'customer_id' => $customer->id,
                'code' => $generated,
                'issued_from_order_id' => $order->id,
                'issued_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            $issuedCodes[] = [
                'code' => $generated,
                'expires_at' => $expiresAt?->toDateString(),
            ];
        }

        return $issuedCodes;
    }
}
