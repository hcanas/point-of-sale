<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveMemberRequest;
use App\Models\Member;
use Illuminate\Http\JsonResponse;

class MemberController extends Controller
{
    public function store(SaveMemberRequest $request): JsonResponse
    {
        $member = Member::create([
            ...$request->validated(),
            'balance' => 0,
        ]);

        return response()->json($member, 201);
    }

    public function update(SaveMemberRequest $request, Member $member): JsonResponse
    {
        $member->update($request->validated());

        return response()->json($member);
    }
}
