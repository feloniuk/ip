<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

final class AuthService
{
    public function login(string $email, string $password): array
    {
        Log::info('Login attempt', ['email' => $email]);
        
        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            Log::warning('Login failed', ['email' => $email]);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        Log::info('Login successful', ['user_id' => $user->id]);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ]
        ];
    }

    public function logout(Request $request): void
    {
        Log::info('Logout attempt');
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Log::info('Logout successful');
    }

    public function getCurrentUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'created_at' => $user->created_at->toISOString(),
        ];
    }
}