<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(Auth::user()?->role, [UserRole::ADMIN, UserRole::MANAGER, UserRole::INVENTORY]);
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'not_in:0'],
            'notes' => ['required', 'string', 'max:1000'],
        ];
    }
}
