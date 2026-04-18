<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductFilterRequest;
use App\Http\Requests\SaveProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(ProductFilterRequest $request): Response
    {
        $products = Product::query()
            ->when($request->search, fn ($query) => $query
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('barcode', 'like', "%{$request->search}%")
            )
            ->orderBy($request->sortColumn(), $request->sortDirection())
            ->paginate(10)
            ->onEachSide(1)
            ->withQueryString();

        return Inertia::render('Products/ProductList', [
            'products' => $products,
        ]);
    }

    public function store(SaveProductRequest $request): RedirectResponse
    {
        Product::create($request->validated());

        return redirect()->back()->with('success', 'Product created successfully.');
    }

    public function show(Request $request, Product $product): Response
    {
        $movements = $product->stockMovements()
            ->with(['creator'])
            ->when($request->search, fn ($query) => $query
                ->where('notes', 'like', "%{$request->search}%")
                ->orWhere('reference', 'like', "%{$request->search}%")
            )
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $product->load(['creator', 'updater']);
        $product->append('deletable');

        return Inertia::render('Products/ProductProfile', [
            'product' => $product,
            'movements' => $movements,
        ]);
    }

    public function update(SaveProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()->back()->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if (! $product->deletable) {
            return redirect()->back()->with('error', 'Cannot delete product with associated records.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
