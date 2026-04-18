<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Http\Requests\StockAdjustmentRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;

class StockMovementController extends Controller
{
    public function store(StockAdjustmentRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $quantity = (int) $validated['quantity'];
        $newStock = $product->stock + $quantity;

        if ($newStock < 0) {
            return redirect()->back()->with('error', 'Insufficient stock for this adjustment.');
        }

        $product->stockMovements()->create([
            'quantity' => $quantity,
            'after_quantity' => $newStock,
            'reference_type' => StockMovementType::Adjustment,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $product->update(['stock' => $newStock]);

        return redirect()->back()->with('success', 'Stock adjusted successfully.');
    }
}
