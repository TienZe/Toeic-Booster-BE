<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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

        if (!empty($data['avatar'])) {
            $avatar = Cloudinary::uploadApi()->upload($data['avatar'], [
                "folder" => User::AVATAR_FOLDER,
            ]);

            $data['avatar'] = $avatar['secure_url'];
            $data['avatar_public_id'] = $avatar['public_id'];

            // Delete the old avatar
            if ($user->avatar_public_id) {
                $response =Cloudinary::uploadApi()->destroy($user->avatar_public_id);
            }
        }

        return $this->userRepository->updateUser($user->id, $data);
    }
}
