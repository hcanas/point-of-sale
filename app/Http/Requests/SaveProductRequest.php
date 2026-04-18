<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaveProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(Auth::user()?->role, [UserRole::ADMIN, UserRole::MANAGER, UserRole::INVENTORY]);
    }

    public function rules(): array
    {
        return [
            'barcode' => [
                'nullable',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null) {
                        return;
                    }

                    $productId = $this->route('product')?->id;

                    $exists = Product::whereRaw('LOWER(barcode) = LOWER(?)', [$value])
                        ->when($productId, fn ($query) => $query->where('id', '!=', $productId))
                        ->exists();

                    if ($exists) {
                        $fail('The barcode has already been taken.');
                    }
                },
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $productId = $this->route('product')?->id;

                    $exists = Product::whereRaw('LOWER(name) = LOWER(?)', [$value])
                        ->when($productId, fn ($query) => $query->where('id', '!=', $productId))
                        ->exists();

                    if ($exists) {
                        $fail('The name has already been taken.');
                    }
                },
            ],
            'description' => ['nullable', 'string'],
            'stock_warning_level' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
