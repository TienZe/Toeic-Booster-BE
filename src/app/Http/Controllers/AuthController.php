<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());
        return $result;
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());
        return $result;
    }

    /**
     * Logout user
     */
    public function logout()
    {
        $this->authService->logout();
        return true;
    }

    /**
     * Refresh token
     */
    public function refresh()
    {
        $result = $this->authService->refreshToken();
        return $result;
    }

    /**
     * Get authenticated user
     */
    public function me()
    {
        $user = $this->authService->getAuthenticatedUser();
        return $user;
    }
}
