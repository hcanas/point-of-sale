<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function store(SaveProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json($product, 201);
    }

    public function update(SaveProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return response()->json($product);
    }
}
