<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->withCount('presentations')
            ->latest()
            ->get();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load([
            'presentations' => function ($query) {
                $query->withCount('responses')->latest();
            },
        ]);

        return view('admin.users.show', compact('user'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'is_superuser' => ['required', 'boolean'],
        ]);

        if ($request->user()->id === $user->id && ! $request->boolean('is_superuser')) {
            return back()->with('status', 'You cannot remove your own superuser access.');
        }

        $user->update([
            'is_superuser' => $request->boolean('is_superuser'),
        ]);

        return back()->with('status', 'User role updated.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return back()->with('status', 'You cannot delete your own account from admin.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'User deleted.');
    }
}
