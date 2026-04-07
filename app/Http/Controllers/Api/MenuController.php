<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class MenuController extends Controller
{
    /**
     * Display the full menu (categories with products).
     */
    public function index(): JsonResponse
    {
        // Get active categories with their active products, ordered by sort_order
        $menu = Category::where('is_active', true)
            ->with(['products' => function ($query) {
                $query->where('is_active', true)
                    ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return response()->json($menu);
    }
}
