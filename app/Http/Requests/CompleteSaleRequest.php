<?php

namespace App\Http\Requests;

use App\Models\Product;
use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CompleteSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $items = $this->input('items', []);
        $productIds = collect($items)->pluck('id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        return [
            'member_id' => 'required|exists:members,id',
            'amount_tendered' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, Closure $fail) use ($products) {
                    if (! $products->has($value)) {
                        $fail('Product not found.');
                    }
                },
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                function (string $attribute, mixed $value, Closure $fail) use ($products) {
                    $index = (int) str_replace('items.', '', str_replace('.quantity', '', $attribute));
                    $productId = $this->input("items.{$index}.id");
                    $product = $products->get($productId);

                    if ($product && $product->stock < $value) {
                        $fail("Insufficient stock. Available: {$product->stock}");
                    }
                },
            ],
            'items.*.price' => 'required|numeric|min:0',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = [];
        $allErrors = $validator->errors()->toArray();

        foreach ($allErrors as $key => $messages) {
            if (str_starts_with($key, 'items.')) {
                $index = (int) str_replace('items.', '', $key);
                $errors[$index] = $messages[0];
            }
        }

        if (! empty($errors)) {
            throw new HttpResponseException(response()->json(['errors' => $errors], 422));
        }

        parent::failedValidation($validator);
    }
}
