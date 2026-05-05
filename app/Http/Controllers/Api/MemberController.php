<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveMemberRequest;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $members = Member::query()
            ->when($request->search, fn ($query) => $query
                ->where('first_name', 'like', "%{$request->search}%")
                ->orWhere('middle_name', 'like', "%{$request->search}%")
                ->orWhere('last_name', 'like', "%{$request->search}%")
            )
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(5)
            ->get();

        return response()->json($members);
    }

    public function store(SaveMemberRequest $request): JsonResponse
    {
        $member = Member::create([
            ...$request->validated(),
            'outstanding_balance' => 0,
        ]);

        return response()->json($member, 201);
    }

    public function update(SaveMemberRequest $request, Member $member): JsonResponse
    {
        $member->update($request->validated());

        return response()->json($member);
    }
}
