<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Normalize Indonesian phone numbers to standard +628xxxxxxxxxx format.
     */
    public static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[\s\-\.]/', '', trim($phone));

        if (str_starts_with($phone, '+62')) {
            return '+62'.ltrim(substr($phone, 3), '0');
        }

        if (str_starts_with($phone, '62')) {
            return '+62'.ltrim(substr($phone, 2), '0');
        }

        if (str_starts_with($phone, '08')) {
            return '+628'.substr($phone, 2);
        }

        if (str_starts_with($phone, '8')) {
            return '+628'.substr($phone, 1);
        }

        return $phone;
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $phone = $request->filled('phone') ? self::normalizePhone($request->phone) : null;
        if ($phone) {
            $request->merge(['phone' => $phone]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'nullable|string|max:20|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $phone,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'is_active' => true,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended('/');
    }
}
