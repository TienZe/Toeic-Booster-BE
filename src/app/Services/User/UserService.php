<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\User\UserRepository;
use App\Services\Auth\AuthService;

class UserService
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly UserRepository $userRepository
    ) {
    }

    /**
     * Update user profile
     *
     * @param array<string, mixed> $data
     * @return \App\Models\User
     */
    public function updateProfile(array $data): User
    {
        $user = $this->authService->getAuthenticatedUser();
        return $this->userRepository->updateUser($user->id, $data);
    }
}
