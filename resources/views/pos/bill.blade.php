<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.2;
            width: {{ $tenant->receipt_size ?? '80mm' }};
            margin: 0 auto;
            padding: 10px;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider { border-bottom: 1px dashed #000; margin: 10px 0; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .addon-row { font-size: 10px; margin-left: 10px; font-style: italic; }
        .total-row { display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; margin-top: 5px; }
        .footer { margin-top: 20px; font-size: 10px; }
        .bill-header { 
            background: #000; 
            color: #fff; 
            padding: 8px; 
            text-align: center; 
            margin: -10px -10px 10px -10px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        
        @media print {
            body { width: 100%; padding: 0; margin: 0; }
            .no-print { display: none; }
            .bill-header { margin: 0 0 10px 0; }
        }
    </style>
</head>
<body @if(!request()->boolean('preview')) onload="window.print()" @endif>
    @if(!request()->boolean('preview'))
        <div class="no-print" style="margin-bottom: 20px; text-align: center;">
            <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Print Bill</button>
            <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">Close</button>
        </div>
    @endif

    <div class="bill-header">
        -- BILL --
    </div>

    <div class="text-center">
        @if($tenant->logo_url)
            <img src="{{ $tenant->logo_url }}" style="max-width: 50mm; max-height: 30mm; margin-bottom: 10px;">
        @endif
        <h2 class="bold" style="margin: 0;">{{ $tenant->name }}</h2>
        @if($tenant->address)
            <div>{{ $tenant->address }}</div>
        @endif
        @if($tenant->phone)
            <div>Tel: {{ $tenant->phone }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <div class="item-row">
        <span>Order #{{ $order->id }}</span>
        <span class="bold">{{ $order->order_type === 'dine_in' ? 'DINE-IN' : 'TAKE-AWAY' }}</span>
    </div>
    <div class="item-row">
        <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
    </div>
    @if($order->order_type === 'dine_in' && $order->table_number)
        <div class="item-row">
            <span>Table: {{ $order->table_number }}</span>
        </div>
    @endif
    @if($order->notes)
        <div class="item-row">
            <span>Note: {{ $order->notes }}</span>
        </div>
    @endif
    <div class="item-row">
        <span>Staff: {{ $order->user?->name ?? 'N/A' }}</span>
    </div>

    <div class="divider"></div>

    <div class="bold item-row">
        <span style="flex: 2;">Item</span>
        <span style="flex: 1; text-align: center;">Qty</span>
        <span style="flex: 1; text-align: right;">Total</span>
    </div>

    <div class="divider"></div>

    @foreach($order->items as $item)
        @php
            $each = (float) $item->unit_price + (float) ($item->variant_price ?? 0);
        @endphp
        <div class="item-row">
            <span style="flex: 2;">
                {{ $item->product->name }}@if($item->variant) ({{ $item->variant->receipt_label ?: $item->variant->name }})@endif
                <div style="font-size: 10px; color: #666; margin-top: 2px;">
                    RM {{ number_format($each, 2) }}
                </div>
            </span>
            <span style="flex: 1; text-align: center;">{{ $item->quantity }}</span>
            <span style="flex: 1; text-align: right;">RM {{ number_format($item->subtotal, 2) }}</span>
        </div>
        
        @foreach($item->addons as $addon)
            <div class="addon-row">
                + {{ $addon->name }} (RM {{ number_format($addon->price, 2) }})
            </div>
        @endforeach

        @foreach($item->components as $component)
            <div class="addon-row">
                Set: {{ $component->name }}@if(((float) ($component->extra_price ?? 0)) > 0) (+RM {{ number_format((float) $component->extra_price, 2) }})@endif
            </div>
        @endforeach
        
        @if($item->notes)
            <div class="addon-row" style="color: #666;">
                Note: {{ $item->notes }}
            </div>
        @endif
    @endforeach

    <div class="divider"></div>

    <div class="item-row">
        <span>Subtotal</span>
        <span>RM {{ number_format($order->subtotal_amount ?? $order->total_amount, 2) }}</span>
    </div>

    @if(($order->discount_amount ?? 0) > 0)
        <div class="item-row">
            <span>Discount</span>
            <span>- RM {{ number_format($order->discount_amount, 2) }}</span>
        </div>
    @endif

    @if(($order->tax_amount ?? 0) > 0)
        @php
            $taxTotal = (float) ($order->tax_amount ?? 0);
            $enabledTaxes = $tenant->taxes()->where('is_enabled', true)->orderBy('name')->get(['name', 'rate']);
            $ratesSum = (float) $enabledTaxes->sum('rate');
            $breakdown = [];

            if ($enabledTaxes->count() > 0 && $ratesSum > 0) {
                foreach ($enabledTaxes as $t) {
                    $rate = (float) ($t->rate ?? 0);
                    if ($rate <= 0) {
                        continue;
                    }
                    $breakdown[] = [
                        'name' => (string) ($t->name ?? 'Tax'),
                        'rate' => $rate,
                        'amount' => round($taxTotal * ($rate / $ratesSum), 2),
                    ];
                }

                $allocated = array_sum(array_map(fn ($r) => (float) ($r['amount'] ?? 0), $breakdown));
                $diff = round($taxTotal - $allocated, 2);
                if ($diff !== 0.0 && count($breakdown) > 0) {
                    $last = count($breakdown) - 1;
                    $breakdown[$last]['amount'] = round((float) ($breakdown[$last]['amount'] ?? 0) + $diff, 2);
                }
            }
        @endphp

        @if(count($breakdown) > 1)
            @foreach($breakdown as $row)
                <div class="item-row">
                    <span>{{ $row['name'] }} ({{ rtrim(rtrim(number_format((float) ($row['rate'] ?? 0), 2), '0'), '.') }}%)</span>
                    <span>RM {{ number_format((float) ($row['amount'] ?? 0), 2) }}</span>
                </div>
            @endforeach
            <div class="item-row">
                <span>Tax Total</span>
                <span>RM {{ number_format($taxTotal, 2) }}</span>
            </div>
        @elseif(count($breakdown) === 1)
            <div class="item-row">
                <span>{{ $breakdown[0]['name'] }} ({{ rtrim(rtrim(number_format((float) ($breakdown[0]['rate'] ?? 0), 2), '0'), '.') }}%)</span>
                <span>RM {{ number_format($taxTotal, 2) }}</span>
            </div>
        @else
            <div class="item-row">
                <span>Tax ({{ rtrim(rtrim(number_format((float) ($order->tax_rate ?? 0), 2), '0'), '.') }}%)</span>
                <span>RM {{ number_format($taxTotal, 2) }}</span>
            </div>
        @endif
    @endif

    <div class="divider"></div>
    
    <div class="total-row">
        <span>TOTAL DUE</span>
        <span>RM {{ number_format($order->total_amount, 2) }}</span>
    </div>

    <div class="divider"></div>

    <div class="text-center" style="padding: 10px 0;">
        <div class="bold" style="font-size: 14px; margin-bottom: 5px;">PAYMENT PENDING</div>
        <div style="font-size: 10px;">Please proceed to counter to make payment</div>
    </div>

    <div class="divider"></div>

    <div class="text-center footer">
        <p class="bold">{{ $tenant->receipt_footer ?? 'THANK YOU FOR YOUR VISIT!' }}</p>
        <p>Please come again</p>
        <p>Powered by F&B Cloud</p>
    </div>
</body>
</html>
