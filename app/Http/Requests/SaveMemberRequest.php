<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class SaveMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(Auth::user()?->role, [UserRole::ADMIN, UserRole::MANAGER]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'name_extension' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
            'share_capital' => ['required', 'numeric', 'min:0'],
            'tin_number' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $memberId = $this->route('member')?->id;

                $query = Member::query()
                    ->whereRaw('LOWER(first_name) = LOWER(?)', [$this->input('first_name')])
                    ->whereRaw('LOWER(last_name) = LOWER(?)', [$this->input('last_name')]);

                $middleName = $this->input('middle_name');
                $nameExtension = $this->input('name_extension');

                $query->when($middleName, fn ($q) => $q->whereRaw('LOWER(middle_name) = LOWER(?)', [$middleName]))
                    ->when(! $middleName, fn ($q) => $q->whereNull('middle_name'));

                $query->when($nameExtension, fn ($q) => $q->whereRaw('LOWER(name_extension) = LOWER(?)', [$nameExtension]))
                    ->when(! $nameExtension, fn ($q) => $q->whereNull('name_extension'));

                if ($memberId) {
                    $query->where('id', '!=', $memberId);
                }

                if ($query->exists()) {
                    $validator->errors()->add('full_name', 'A member with this name already exists.');
                }
            },
        ];
    }
}
