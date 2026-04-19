<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function store(SaveUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        return response()->json($user, 201);
    }

    public function update(SaveUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if ($data['password'] === null || $data['password'] === '') {
            unset($data['password'], $data['password_confirmation']);
        }

        $user->update($data);

        return response()->json($user);
    }
}
