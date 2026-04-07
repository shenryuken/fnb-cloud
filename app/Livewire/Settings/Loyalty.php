<?php

namespace App\Livewire\Settings;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Loyalty Program')]
class Loyalty extends Component
{
    public float $points_earn_points = 1;
    public float $points_earn_amount = 1;
    public int $points_redeem_points = 100;
    public float $points_redeem_amount = 1;
    public int $points_min_redeem = 0;
    public bool $points_promo_is_enabled = false;
    public float $points_promo_multiplier = 2;
    public ?string $points_promo_starts_at = null;
    public ?string $points_promo_ends_at = null;

    public function mount(): void
    {
        $tenant = auth()->user()->tenant;

        $this->points_earn_points = (float) ($tenant->points_earn_points ?? 1);
        $this->points_earn_amount = (float) ($tenant->points_earn_amount ?? 1);
        $this->points_redeem_points = (int) ($tenant->points_redeem_points ?? 100);
        $this->points_redeem_amount = (float) ($tenant->points_redeem_amount ?? 1);
        $this->points_min_redeem = (int) ($tenant->points_min_redeem ?? 0);
        $this->points_promo_is_enabled = (bool) ($tenant->points_promo_is_enabled ?? false);
        $this->points_promo_multiplier = (float) ($tenant->points_promo_multiplier ?? 2);
        $this->points_promo_starts_at = $tenant->points_promo_starts_at?->format('Y-m-d\TH:i');
        $this->points_promo_ends_at = $tenant->points_promo_ends_at?->format('Y-m-d\TH:i');

        if ($this->points_earn_amount <= 0) {
            $this->points_earn_amount = 1;
        }
        if ($this->points_redeem_points <= 0) {
            $this->points_redeem_points = 100;
        }
    }

    public function save(): void
    {
        $this->validate([
            'points_earn_points' => 'required|numeric|min:0|max:100000',
            'points_earn_amount' => 'required|numeric|min:0.01|max:100000',
            'points_redeem_points' => 'required|integer|min:1|max:1000000000',
            'points_redeem_amount' => 'required|numeric|min:0|max:100000',
            'points_min_redeem' => 'required|integer|min:0|max:1000000000',
            'points_promo_is_enabled' => 'boolean',
            'points_promo_multiplier' => 'required|numeric|min:0|max:1000',
            'points_promo_starts_at' => 'nullable|date',
            'points_promo_ends_at' => 'nullable|date',
        ]);

        $tenant = auth()->user()->tenant;
        $tenant->update([
            'points_earn_points' => round(max(0, (float) $this->points_earn_points), 4),
            'points_earn_amount' => round(max(0.01, (float) $this->points_earn_amount), 2),
            'points_redeem_points' => max(1, (int) $this->points_redeem_points),
            'points_redeem_amount' => round(max(0, (float) $this->points_redeem_amount), 2),
            'points_min_redeem' => max(0, (int) $this->points_min_redeem),
            'points_promo_is_enabled' => (bool) $this->points_promo_is_enabled,
            'points_promo_multiplier' => round(max(0, (float) $this->points_promo_multiplier), 2),
            'points_promo_starts_at' => filled($this->points_promo_starts_at) ? $this->points_promo_starts_at : null,
            'points_promo_ends_at' => filled($this->points_promo_ends_at) ? $this->points_promo_ends_at : null,
        ]);

        $tenant->update([
            'points_earn_rate' => round(max(0, ((float) $this->points_earn_points) / max(0.01, (float) $this->points_earn_amount)), 4),
            'points_redeem_value_per_100' => round(max(0, ((float) $this->points_redeem_amount) * (100 / max(1, (int) $this->points_redeem_points))), 2),
        ]);

        $this->dispatch('notify', message: 'Loyalty program settings updated successfully.', type: 'success');
    }

    public function render()
    {
        return view('livewire.settings.loyalty')->layout('layouts.app');
    }
}
