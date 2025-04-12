<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Auth\AuthRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthService
{
    protected AuthRepository $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    /**
     * Register a new user
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function register(array $data): array
    {
        $user = $this->authRepository->createUser($data);
        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ];
    }

    /**
     * Login user and return token
     *
     * @param array $credentials
     * @return array
     * @throws \Exception
     */
    public function login(array $credentials): array
    {
        $user = $this->authRepository->findUserByEmail($credentials['email']);

        if (!$user || !$this->verifyPassword($user, $credentials['password'])) {
            throw new \Exception('Invalid credentials');
        }

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            throw new \Exception('Could not create token');
        }

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ];
    }

    /**
     * Logout user
     *
     * @return void
     */
    public function logout(): void
    {
        Auth::logout();
    }

    /**
     * Refresh user token
     *
     * @return array
     * @throws \Exception
     */
    public function refreshToken(): array
    {
        $token = Auth::refresh();
        $user = Auth::setToken($token)->user();

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ];
    }

    /**
     * Get authenticated user
     *
     */
    public function getAuthenticatedUser()
    {
        return Auth::user();
    }

    /**
     * Verify user password
     *
     * @param User $user
     * @param string $password
     * @return bool
     */
    public function verifyPassword(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }
}
