<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Psr\Log\LoggerInterface;

final class AuthService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ValidationFactory $validator
    ) {}

    public function login(Request $request): array
    {
        $this->validator->make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ])->validate();

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        $request->session()->regenerate();

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ];
    }

    public function logout(Request $request): void
    {
        $this->logger->info('Logout attempt');

        Auth::logout();
        $this->invalidateSession($request->session());

        $this->logger->info('Logout successful');
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

    private function invalidateSession(Session $session): void
    {
        $session->invalidate();
        $session->regenerateToken();
    }
}