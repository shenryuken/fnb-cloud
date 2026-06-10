<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Orders\BuildOrderDataAction;
use App\Actions\Orders\CreateOrderAction;
use App\Exceptions\OrderException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    /**
     * List orders for the authenticated tenant (most recent first).
     * TenantScope applies tenant filtering automatically.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->with(['items.product', 'items.addons', 'items.components', 'customer', 'user', 'table'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('shift_id'), fn ($q) => $q->where('shift_id', $request->integer('shift_id')))
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 20), 100));

        return OrderResource::collection($orders);
    }

    /**
     * Show a single order.
     */
    public function show(Order $order): OrderResource
    {
        $order->load(['items.product', 'items.addons', 'items.components', 'customer', 'user', 'table']);

        return new OrderResource($order);
    }

    /**
     * Create an order from validated client input.
     *
     * Pricing is recomputed server-side via BuildOrderDataAction (the client
     * never dictates prices), and persistence runs through CreateOrderAction —
     * the same canonical action the POS uses. A client_uuid makes this
     * idempotent so retries from offline clients never double-charge.
     */
    public function store(StoreOrderRequest $request, BuildOrderDataAction $builder, CreateOrderAction $createOrder): JsonResponse
    {
        $clientUuid = $request->input('client_uuid');

        // Idempotency: if this UUID was already persisted, return the existing order.
        if ($clientUuid) {
            $existing = Order::where('client_uuid', $clientUuid)->first();
            if ($existing) {
                $existing->load(['items.product', 'items.addons', 'items.components', 'customer', 'user', 'table']);

                return (new OrderResource($existing))
                    ->additional(['idempotent_replay' => true])
                    ->response()
                    ->setStatusCode(200);
            }
        }

        try {
            $data = $builder->build($request->validated(), $request->user()?->id, 'api');
            $order = $createOrder->execute($data);
        } catch (OrderException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $order->load(['items.product', 'items.addons', 'items.components', 'customer', 'user', 'table']);

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }
}
