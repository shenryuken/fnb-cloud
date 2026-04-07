<?php

namespace App\Livewire\Settings;

use App\Models\TenantBusinessHour;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Receipt extends Component
{
    use WithFileUploads;

    public $name;
    public $address;
    public $phone;
    public $receipt_email;
    public $receipt_header;
    public $receipt_footer;
    public $receipt_size;
    public $tax_rate;
    public $business_day_start_time;
    public $business_day_end_time;
    public array $business_hours = [];
    public array $taxes = [];
    public $logo;
    public $logo_url;

    public function mount()
    {
        $tenant = Auth::user()->tenant;
        $this->name = $tenant->name;
        $this->address = $tenant->address;
        $this->phone = $tenant->phone;
        $this->receipt_email = $tenant->receipt_email;
        $this->receipt_header = $tenant->receipt_header;
        $this->receipt_footer = $tenant->receipt_footer;
        $this->receipt_size = $tenant->receipt_size ?? '80mm';
        $this->tax_rate = (float) ($tenant->tax_rate ?? 0);
        $this->business_day_start_time = $tenant->business_day_start_time ? substr((string) $tenant->business_day_start_time, 0, 5) : '00:00';
        $this->business_day_end_time = $tenant->business_day_end_time ? substr((string) $tenant->business_day_end_time, 0, 5) : '23:59';
        $this->logo_url = $tenant->logo_url;

        $defaults = collect(range(0, 6))->mapWithKeys(fn ($d) => [
            $d => [
                'day_of_week' => $d,
                'open_time' => '09:00',
                'close_time' => '22:00',
                'is_closed' => false,
            ],
        ]);

        $hours = $tenant->businessHours()->get()->keyBy(fn ($h) => (int) $h->day_of_week);

        $this->business_hours = $defaults
            ->map(function ($row, $day) use ($hours) {
                $h = $hours->get((int) $day);
                if (!$h) {
                    return $row;
                }

                return [
                    'day_of_week' => (int) $h->day_of_week,
                    'open_time' => $h->open_time ? substr((string) $h->open_time, 0, 5) : null,
                    'close_time' => $h->close_time ? substr((string) $h->close_time, 0, 5) : null,
                    'is_closed' => (bool) $h->is_closed,
                ];
            })
            ->sortBy('day_of_week')
            ->values()
            ->all();

        $this->taxes = $tenant->taxes()->orderBy('name')->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'code' => $t->code,
                'rate' => (float) $t->rate,
                'is_enabled' => (bool) $t->is_enabled,
            ])->all();
    }

    public function save()
    {
        $tenant = Auth::user()->tenant;

        $this->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'receipt_email' => 'nullable|email',
            'receipt_header' => 'nullable|string',
            'receipt_footer' => 'nullable|string',
            'receipt_size' => 'required|in:58mm,80mm',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'business_day_start_time' => 'required|date_format:H:i',
            'business_day_end_time' => 'required|date_format:H:i',
            'business_hours' => 'required|array|size:7',
            'business_hours.*.day_of_week' => 'required|integer|min:0|max:6',
            'business_hours.*.open_time' => 'nullable|date_format:H:i',
            'business_hours.*.close_time' => 'nullable|date_format:H:i',
            'business_hours.*.is_closed' => 'required|boolean',
            'logo' => 'nullable|image|max:1024', // 1MB Max
        ]);

        $data = [
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'receipt_email' => $this->receipt_email,
            'receipt_header' => $this->receipt_header,
            'receipt_footer' => $this->receipt_footer,
            'receipt_size' => $this->receipt_size,
            'tax_rate' => (float) ($this->tax_rate ?? 0),
            'business_day_start_time' => $this->business_day_start_time,
            'business_day_end_time' => $this->business_day_end_time,
        ];

        if ($this->logo) {
            $path = $this->logo->store('logos', 'public');
            $data['logo_url'] = Storage::url($path);
            $this->logo_url = $data['logo_url'];
        }

        $tenant->update($data);

        foreach ($this->business_hours as $row) {
            $isClosed = (bool) ($row['is_closed'] ?? false);

            TenantBusinessHour::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'day_of_week' => (int) $row['day_of_week'],
                ],
                [
                    'is_closed' => $isClosed,
                    'open_time' => $isClosed ? null : ($row['open_time'] ?? null),
                    'close_time' => $isClosed ? null : ($row['close_time'] ?? null),
                ]
            );
        }

        // Sync taxes
        $keptIds = [];
        foreach ($this->taxes as $row) {
            $payload = [
                'tenant_id' => $tenant->id,
                'name' => trim((string) ($row['name'] ?? '')),
                'code' => $row['code'] ?? null,
                'rate' => (float) ($row['rate'] ?? 0),
                'is_enabled' => (bool) ($row['is_enabled'] ?? false),
            ];

            if (!filled($payload['name'])) {
                continue;
            }

            if (!empty($row['id'])) {
                $tax = $tenant->taxes()->find($row['id']);
                if ($tax) {
                    $tax->update($payload);
                    $keptIds[] = $tax->id;
                    continue;
                }
            }
            $tax = $tenant->taxes()->create($payload);
            $keptIds[] = $tax->id;
        }

        // delete removed
        $tenant->taxes()->whereNotIn('id', $keptIds)->delete();

        $this->dispatch('notify', message: 'Receipt settings updated successfully.', type: 'success');
    }

    public function addTax(): void
    {
        $this->taxes[] = ['name' => '', 'code' => '', 'rate' => 0, 'is_enabled' => true];
    }

    public function removeTax(int $index): void
    {
        unset($this->taxes[$index]);
        $this->taxes = array_values($this->taxes);
    }

    public function render()
    {
        return view('livewire.settings.receipt');
    }
}
