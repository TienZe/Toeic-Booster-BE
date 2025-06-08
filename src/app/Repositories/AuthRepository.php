<?php

namespace App\Repositories;

use App\Models\User;

class AuthRepository
{
    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create new user
     *
     * @param array $data
     * @return User
     * @throws \Exception
     */
    public function createUser(array $data): User
    {
        try {
            $user = new User($data);
            $user->save();
            $user->refresh();

            return $user;
        } catch (\Exception $e) {
            throw new \Exception('Failed to create user: ' . $e->getMessage());
        }
    }
}
