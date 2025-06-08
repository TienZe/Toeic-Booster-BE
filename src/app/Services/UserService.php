<?php

namespace App\Services;

use App\Entities\PaginatedList;
use App\Models\Role;
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
    public function updateProfile($userId, array $data): User
    {
        $user = User::findOrFail($userId);

        if (!empty($data['avatar'])) {
            $avatar = Cloudinary::uploadApi()->upload($data['avatar'], [
                "folder" => User::AVATAR_FOLDER,
            ]);

            $data['avatar'] = $avatar['secure_url'];
            $data['avatar_public_id'] = $avatar['public_id'];

            // Delete the old avatar
            if ($user->avatar_public_id) {
                $response = Cloudinary::uploadApi()->destroy($user->avatar_public_id);
            }
        }

        return $this->userRepository->updateUser($user->id, $data);
    }

    /**
     * Get registered users
     *
     * @param array{
     *     page?: int,
     *     limit?: int
     * } $options
     */
    public function getUsers($options = [])
    {
        $limit = $options['limit'] ?? 10;
        $page = $options['page'] ?? 0;
        $search = $options['search'] ?? null;
        $filteredStatus = $options['filtered_status'] ?? null;

        $query = User::where(function ($query) {
            $query->whereHas('roles', function ($query) {
                $query->where('name', '!=', Role::ADMIN);
            });
        })->orWhereDoesntHave('roles')
            ->orderByDesc('created_at');

        if (isset($search)) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
        }

        if (isset($filteredStatus)) {
            $query->where('status', $filteredStatus);
        }

        return PaginatedList::createFromQueryBuilder($query, $page, $limit);
    }

    public function updateUser($userId, $data)
    {
        $profileData = [];

        if (isset($data['name'])) {
            $profileData['name'] = $data['name'];
        }

        if (isset($data['avatar'])) {
            $profileData['avatar'] = $data['avatar'];
        }

        if (isset($data['status'])) {
            $profileData['status'] = $data['status'];
        }

        $updatedUser = $this->updateProfile($userId, $profileData);

        if (isset($data['new_password'])) {
            $updatedUser->password = $this->authService->generateHashPassword($data['new_password']);
            $updatedUser->save();
        }

        return $updatedUser;
    }
}
