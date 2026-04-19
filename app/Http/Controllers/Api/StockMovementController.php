<?php

namespace App\Http\Controllers\Api;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class StockMovementController extends Controller
{
    public function store(StockAdjustmentRequest $request, Product $product): JsonResponse
    {
        $validated = $request->validated();
        $quantity = (int) $validated['quantity'];
        $newStock = $product->stock + $quantity;

        if ($newStock < 0) {
            return response()->json(['message' => 'Insufficient stock for this adjustment.'], 422);
        }

        $product->stockMovements()->create([
            'quantity' => $quantity,
            'after_quantity' => $newStock,
            'reference_type' => StockMovementType::Adjustment,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $product->update(['stock' => $newStock]);
        $product->refresh();

        return response()->json($product);
    }
}
