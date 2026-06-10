<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Full menu catalog: active categories with their active products,
     * including variants and addons. Offline clients pre-cache this payload.
     *
     * Supports delta sync via ?since=<ISO8601> to return only records
     * updated after the given timestamp.
     */
    public function index(Request $request): JsonResponse
    {
        $since = $request->filled('since')
            ? \Illuminate\Support\Carbon::parse($request->string('since'))
            : null;

        $categories = Category::query()
            ->where('is_active', true)
            ->when($since, fn ($q) => $q->where('updated_at', '>', $since))
            ->with(['products' => function ($query) use ($since) {
                $query->where('is_active', true)
                    ->when($since, fn ($q) => $q->where('updated_at', '>', $since))
                    ->with(['variants', 'addons'])
                    ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => $categories,
            'synced_at' => now()->toIso8601String(),
        ]);
    }
}
