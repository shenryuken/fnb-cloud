<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        // TenantScope filters by tenant_id automatically
        $orders = Order::with(['items.product', 'user'])
            ->latest()
            ->paginate(15);

        return response()->json($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'table_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.addon_ids' => 'nullable|array',
            'items.*.addon_ids.*' => 'exists:product_addons,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
        ]);

        try {
            return DB::transaction(function () use ($validated, $request) {
                $order = Order::create([
                    'user_id' => $request->user()?->id,
                    'table_number' => $validated['table_number'],
                    'notes' => $validated['notes'],
                    'status' => 'pending',
                    'total_amount' => 0,
                ]);

                $totalAmount = 0;

                foreach ($validated['items'] as $itemData) {
                    $product = Product::findOrFail($itemData['product_id']);
                    
                    $unitPrice = $product->price;
                    $variantPrice = 0;
                    $variantId = $itemData['variant_id'] ?? null;

                    if ($variantId) {
                        $variant = $product->variants()->findOrFail($variantId);
                        $variantPrice = $variant->price;
                    }

                    $itemSubtotal = ($unitPrice + $variantPrice) * $itemData['quantity'];
                    
                    $orderItem = $order->items()->create([
                        'product_id' => $product->id,
                        'variant_id' => $variantId,
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $unitPrice,
                        'variant_price' => $variantPrice,
                        'subtotal' => $itemSubtotal,
                        'notes' => $itemData['notes'] ?? null,
                    ]);

                    // Handle Addons
                    if (!empty($itemData['addon_ids'])) {
                        foreach ($itemData['addon_ids'] as $addonId) {
                            $addon = $product->addons()->findOrFail($addonId);
                            
                            $orderItem->addons()->create([
                                'addon_id' => $addon->id,
                                'name' => $addon->name,
                                'price' => $addon->price,
                            ]);

                            $itemSubtotal += ($addon->price * $itemData['quantity']);
                        }
                        
                        // Update order item subtotal after addons
                        $orderItem->update(['subtotal' => $itemSubtotal]);
                    }

                    $totalAmount += $itemSubtotal;
                }

                $order->update(['total_amount' => $totalAmount]);

                return response()->json($order->load('items.product', 'items.variant', 'items.addons'), 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order): JsonResponse
    {
        return response()->json($order->load(['items.product', 'user']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|required|in:pending,processing,completed,cancelled',
            'table_number' => 'sometimes|nullable|string',
            'notes' => 'sometimes|nullable|string',
        ]);

        $order->update($validated);

        return response()->json($order->load(['items.product', 'user']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order): JsonResponse
    {
        // For F&B, we usually cancel instead of deleting, but this is for REST consistency
        $order->delete();
        return response()->json(null, 204);
    }
}
