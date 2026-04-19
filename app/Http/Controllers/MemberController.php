<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberFilterRequest;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    public function index(MemberFilterRequest $request): Response
    {
        $members = Member::query()
            ->when($request->search, function ($query) use ($request): void {
                $search = $request->search;
                if (is_numeric($search)) {
                    $query->where('id', $search);
                } else {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('name_extension', 'like', "%{$search}%");
                }
            })
            ->when($request->sortColumn() === 'last_name', function ($query) use ($request) {
                $query->orderByRaw("LOWER({$request->sortColumn()}) {$request->sortDirection()}")
                    ->orderByRaw("LOWER(first_name) {$request->sortDirection()}")
                    ->orderByRaw("LOWER(middle_name) {$request->sortDirection()}")
                    ->orderByRaw("LOWER(name_extension) {$request->sortDirection()}");
            }, function ($query) use ($request) {
                $query->orderBy($request->sortColumn(), $request->sortDirection());
            })
            ->paginate(10)
            ->onEachSide(1)
            ->withQueryString();

        return Inertia::render('Members/MemberList', [
            'members' => $members,
        ]);
    }

    public function show(Member $member, Request $request): Response
    {
        $member->load(['creator', 'updater']);
        $member->append('deletable');

        $ledgers = $member->ledgers()
            ->with(['creator'])
            ->when($request->search, function ($query) use ($request): void {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('reference_type', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('creator', function ($creatorQuery) use ($search) {
                            $creatorQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Members/MemberProfile', [
            'member' => $member,
            'ledgers' => $ledgers,
        ]);
    }

    public function destroy(Member $member): RedirectResponse
    {
        if (! $member->deletable) {
            return redirect()->back()->with('error', 'Cannot delete member with associated records.');
        }

        $member->delete();

        return redirect()->route('members.index')->with('success', 'Member deleted successfully.');
    }
}
