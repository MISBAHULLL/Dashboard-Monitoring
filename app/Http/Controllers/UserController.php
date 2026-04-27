<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Team;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        return Inertia::render('Users/Index', [
            'users' => User::with('team')->latest()->get(),
            'teams' => Team::active()->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['admin', 'member'])],
            'team_id' => 'nullable|exists:teams,id',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        $user = User::create($validated);

        ActivityLogger::created('user', $user->id, $user->name, "Menambahkan user '{$user->name}'", $validated);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'member'])],
            'team_id' => 'nullable|exists:teams,id',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $oldValues = $user->getOriginal();
        $user->update($validated);

        ActivityLogger::updated('user', $user->id, $user->name, $oldValues, $user->fresh()->toArray(), "Mengupdate user '{$user->name}'");

        return back()->with('success', 'User berhasil diupdate.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === request()->user()->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        ActivityLogger::deleted('user', $user->id, $user->name, "Menghapus user '{$user->name}'");

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
