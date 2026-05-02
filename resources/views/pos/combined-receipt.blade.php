<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Combined Receipts</title>
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
        .divider-thick { border-bottom: 2px solid #000; margin: 15px 0; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .addon-row { font-size: 10px; margin-left: 10px; font-style: italic; }
        .total-row { display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; margin-top: 5px; }
        .grand-total-row { display: flex; justify-content: space-between; font-size: 16px; font-weight: bold; margin-top: 10px; background: #f0f0f0; padding: 5px; }
        .footer { margin-top: 20px; font-size: 10px; }
        .order-section { margin-bottom: 20px; }
        .page-break { page-break-after: always; }
        
        @media print {
            body { width: 100%; padding: 0; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body @if(!request()->boolean('preview')) onload="window.print()" @endif>
    @if(!request()->boolean('preview'))
        <div class="no-print" style="margin-bottom: 20px; text-align: center;">
            <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Print All Receipts</button>
            <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">Close</button>
        </div>
    @endif

    {{-- Store header --}}
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

    <div class="divider-thick"></div>
    
    <div class="text-center bold" style="font-size: 14px; margin-bottom: 10px;">
        COMBINED RECEIPT
    </div>
    <div class="text-center" style="margin-bottom: 10px;">
        {{ $orders->count() }} Orders | {{ now()->format('d/m/Y H:i') }}
    </div>

    <div class="divider"></div>

    @php $grandTotal = 0; @endphp

    @foreach($orders as $order)
        <div class="order-section">
            <div class="item-row bold" style="background: #e0e0e0; padding: 3px;">
                <span>Order #{{ $order->id }}</span>
                <span>{{ $order->order_type === 'dine_in' ? 'DINE-IN' : 'TAKE-AWAY' }}</span>
            </div>
            
            @if($order->table_number)
                <div class="item-row">
                    <span>Table: {{ $order->table_number }}</span>
                    <span>{{ $order->created_at->format('H:i') }}</span>
                </div>
            @endif

            <div style="margin: 5px 0;">
                @foreach($order->items as $item)
                    @php
                        $each = (float) $item->unit_price + (float) ($item->variant_price ?? 0);
                    @endphp
                    <div class="item-row">
                        <span style="flex: 2;">
                            {{ $item->product->name }}@if($item->variant) ({{ $item->variant->receipt_label ?: $item->variant->name }})@endif
                            @if(($item->item_type ?? 'dine_in') === 'takeaway')
                                <span style="font-size: 10px; color: #f97316; font-weight: bold;">[PACK]</span>
                            @endif
                        </span>
                        <span style="flex: 1; text-align: center;">{{ $item->quantity }}</span>
                        <span style="flex: 1; text-align: right;">${{ number_format($item->subtotal, 2) }}</span>
                    </div>
                    
                    @foreach($item->addons as $addon)
                        <div class="addon-row">
                            + {{ $addon->name }} (${{ number_format($addon->price, 2) }})
                        </div>
                    @endforeach
                @endforeach
            </div>

            <div class="item-row bold">
                <span>Order Total</span>
                <span>${{ number_format($order->total_amount, 2) }}</span>
            </div>

            @php $grandTotal += $order->total_amount; @endphp
        </div>

        @if(!$loop->last)
            <div class="divider"></div>
        @endif
    @endforeach

    <div class="divider-thick"></div>

    <div class="grand-total-row">
        <span>GRAND TOTAL</span>
        <span>${{ number_format($grandTotal, 2) }}</span>
    </div>

    <div class="divider"></div>

    <div class="item-row">
        <span>Payment Method</span>
        <span class="bold uppercase">{{ $orders->first()->payment_method }}</span>
    </div>
    <div class="item-row">
        <span>Amount Paid</span>
        <span>${{ number_format($grandTotal, 2) }}</span>
    </div>

    <div class="divider"></div>

    <div class="text-center footer">
        <p class="bold">{{ $tenant->receipt_footer ?? 'THANK YOU FOR YOUR VISIT!' }}</p>
        <p>Please come again</p>
        <p>Powered by F&B Cloud</p>
    </div>
</body>
</html>
