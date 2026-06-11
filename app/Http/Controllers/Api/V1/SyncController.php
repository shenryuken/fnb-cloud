<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Orders\BuildOrderDataAction;
use App\Actions\Orders\CreateOrderAction;
use App\Exceptions\OrderException;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    /**
     * Bootstrap payload for offline clients: catalog, customers, taxes and
     * server time. Supports delta sync via ?since=<ISO8601> so clients only
     * pull what changed since their last successful sync.
     */
    public function bootstrap(Request $request): JsonResponse
    {
        $since = $request->filled('since')
            ? Carbon::parse($request->string('since'))
            : null;

        $categories = Category::query()
            ->where('is_active', true)
            ->with(['products' => function ($query) use ($since) {
                $query->where('is_active', true)
                    ->when($since, fn ($q) => $q->where('updated_at', '>', $since))
                    ->with(['variants', 'addons'])
                    ->orderBy('sort_order');
            }])
            ->when($since, fn ($q) => $q->where('updated_at', '>', $since))
            ->orderBy('sort_order')
            ->get();

        $customers = Customer::query()
            ->when($since, fn ($q) => $q->where('updated_at', '>', $since))
            ->get(['id', 'name', 'email', 'mobile', 'points_balance', 'updated_at']);

        $tenantId = app()->bound('tenant_id') ? app('tenant_id') : null;
        $taxes = [];
        if ($tenantId && ($tenant = Tenant::find($tenantId))) {
            $taxes = $tenant->taxes()
                ->where('is_enabled', true)
                ->orderBy('name')
                ->get(['name', 'rate']);
        }

        return response()->json([
            'data' => [
                'categories' => $categories,
                'customers' => $customers,
                'taxes' => $taxes,
            ],
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Replay a batch of offline-captured order drafts. Each draft carries a
     * client_uuid for idempotency, so retries never create duplicates. Pricing
     * is recomputed server-side and persistence runs through CreateOrderAction
     * — the same canonical path the POS uses. Per-item failures are reported
     * without aborting the whole batch.
     */
    public function syncOrders(Request $request, BuildOrderDataAction $builder, CreateOrderAction $createOrder): JsonResponse
    {
        $validated = $request->validate([
            'orders' => ['required', 'array', 'min:1', 'max:100'],
            'orders.*.client_uuid' => ['required', 'uuid'],
            'orders.*.items' => ['required', 'array', 'min:1'],
        ]);

        $results = [];

        foreach ($validated['orders'] as $draft) {
            $clientUuid = $draft['client_uuid'];

            // Idempotency: skip drafts already persisted.
            $existing = Order::where('client_uuid', $clientUuid)->first();
            if ($existing) {
                $results[] = [
                    'client_uuid' => $clientUuid,
                    'status' => 'duplicate',
                    'order_id' => $existing->id,
                ];
                continue;
            }

            // Validate each draft individually so one bad record doesn't fail the batch.
            $itemValidator = Validator::make($draft, [
                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => ['required', 'integer'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
            ]);

            if ($itemValidator->fails()) {
                $results[] = [
                    'client_uuid' => $clientUuid,
                    'status' => 'failed',
                    'errors' => $itemValidator->errors()->toArray(),
                ];
                continue;
            }

            try {
                $data = $builder->build($draft, $request->user()?->id, 'offline');
                $order = $createOrder->execute($data);

                $results[] = [
                    'client_uuid' => $clientUuid,
                    'status' => 'synced',
                    'order_id' => $order->id,
                    'order' => new OrderResource($order->load(['items.product', 'customer'])),
                ];
            } catch (OrderException $e) {
                $results[] = [
                    'client_uuid' => $clientUuid,
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                ];
            }
        }

        $summary = [
            'synced' => collect($results)->where('status', 'synced')->count(),
            'duplicate' => collect($results)->where('status', 'duplicate')->count(),
            'failed' => collect($results)->where('status', 'failed')->count(),
        ];

        return response()->json([
            'summary' => $summary,
            'results' => $results,
            'synced_at' => now()->toIso8601String(),
        ]);
    }
}
