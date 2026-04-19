<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class SaveUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(Auth::user()?->role, [UserRole::ADMIN, UserRole::MANAGER]);
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'name_extension' => ['nullable', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($userId)],
            'password' => $userId ? ['nullable', 'confirmed', Password::min(8)] : ['required', 'confirmed', Password::min(8)],
            'role' => ['required', 'in:'.implode(',', UserRole::nonAdmin())],
            'is_active' => ['boolean'],
        ];
    }

    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validateAdminUserModification($validator),
            fn (Validator $validator) => $this->validateDuplicateName($validator),
        ];
    }

    private function validateAdminUserModification(Validator $validator): void
    {
        $targetUser = $this->route('user');
        if ($targetUser?->role === UserRole::ADMIN) {
            $validator->errors()->add('role', 'Cannot modify users with the admin role.');
        }
    }

    private function validateDuplicateName(Validator $validator): void
    {
        $userId = $this->route('user')?->id;
        $query = User::query()
            ->whereRaw('LOWER(first_name) = LOWER(?)', [$this->input('first_name')])
            ->whereRaw('LOWER(last_name) = LOWER(?)', [$this->input('last_name')])
            ->when($this->input('middle_name'), fn ($q) => $q->whereRaw('LOWER(middle_name) = LOWER(?)', [$this->input('middle_name')]))
            ->when(! $this->input('middle_name'), fn ($q) => $q->whereNull('middle_name'))
            ->when($this->input('name_extension'), fn ($q) => $q->whereRaw('LOWER(name_extension) = LOWER(?)', [$this->input('name_extension')]))
            ->when(! $this->input('name_extension'), fn ($q) => $q->whereNull('name_extension'))
            ->when($userId, fn ($q) => $q->where('id', '!=', $userId));

        if ($query->exists()) {
            $validator->errors()->add('full_name', 'A user with this name already exists.');
        }
    }

    protected function passedValidation(): void
    {
        if ($this->route('user')?->id && ($this->password === null || $this->password === '')) {
            $this->request->remove('password');
            $this->request->remove('password_confirmation');
        }
    }
}
