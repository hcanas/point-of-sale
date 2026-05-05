<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Sale::query()
            ->select(['id', 'reference_number', 'member_id', 'member_name', 'member_short_name', 'total_amount', 'created_at'])
            ->latest();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('member_name', 'like', "%{$search}%");
            });
        }

        $limit = $request->input('limit', 5);
        $sales = $query->limit($limit)->get();

        return response()->json($sales);
    }
}
