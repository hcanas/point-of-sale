<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveUserRequest;
use App\Http\Requests\UserFilterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(UserFilterRequest $request): Response
    {
        $users = User::query()
            ->when($request->search, fn ($query) => $query
                ->where('first_name', 'like', "%{$request->search}%")
                ->orWhere('last_name', 'like', "%{$request->search}%")
                ->orWhere('username', 'like', "%{$request->search}%")
            )
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

        return Inertia::render('Users/UserList', [
            'users' => $users,
        ]);
    }

    public function store(SaveUserRequest $request): RedirectResponse
    {
        User::create($request->validated());

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user): Response
    {
        $user->load(['creator', 'updater']);
        $user->append('deletable');

        return Inertia::render('Users/UserProfile', [
            'user' => $user,
        ]);
    }

    public function update(SaveUserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
