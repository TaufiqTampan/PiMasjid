<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of users (super_admin only).
     */
    public function index(): Response
    {
        Gate::authorize('impersonate_user');

        $users = User::orderBy('role')->orderBy('name')->get()->map(fn ($user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'role_label' => $this->getRoleLabel($user->role),
            'is_active' => $user->is_active,
            'email_verified_at' => $user->email_verified_at?->format('Y-m-d'),
            'created_at' => $user->created_at->format('Y-m-d'),
        ]);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'isImpersonating' => session()->has('impersonated_by'),
        ]);
    }

    /**
     * Impersonate another user (super_admin only).
     */
    public function impersonate(User $user)
    {
        Gate::authorize('impersonate_user');

        // Prevent impersonating yourself
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Tidak bisa impersonate diri sendiri.');
        }

        // Store original user ID
        session(['impersonated_by' => auth()->id()]);

        // Login as the target user
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('info', "Sekarang login sebagai: {$user->name}");
    }

    /**
     * Stop impersonating and return to original user.
     */
    public function stopImpersonation()
    {
        $originalUserId = session('impersonated_by');

        if (! $originalUserId) {
            return redirect()->route('dashboard')
                ->with('error', 'Tidak sedang impersonate.');
        }

        // Return to original user
        Auth::loginUsingId($originalUserId);
        session()->forget('impersonated_by');

        return redirect()->route('dashboard')
            ->with('success', 'Kembali ke akun admin.');
    }

    /**
     * Store a new user (super_admin only).
     */
    public function store(Request $request)
    {
        Gate::authorize('manage_users');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:super_admin,ketua,bendahara,marbot',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Delete a user (super_admin only).
     */
    public function destroy(User $user)
    {
        Gate::authorize('manage_users');

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    /**
     * Get role label in Indonesian.
     */
    private function getRoleLabel(string $role): string
    {
        return match ($role) {
            'super_admin' => 'Super Admin',
            'ketua' => 'Ketua',
            'bendahara' => 'Bendahara',
            'marbot' => 'Marbot',
            default => $role,
        };
    }
}
