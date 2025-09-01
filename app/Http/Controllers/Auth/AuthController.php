<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function login(Request $request): JsonResponse
    {
    
        $userData = $this->authService->login($request);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => $userData
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request);

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $userData = $this->authService->getCurrentUser($request->user());

        return response()->json([
            'success' => true,
            'data' => $userData,
        ]);
    }
}