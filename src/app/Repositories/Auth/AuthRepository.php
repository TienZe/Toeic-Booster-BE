<?php

namespace App\Repositories\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
            $data['password'] = Hash::make($data['password']);
            return User::create($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create user: ' . $e->getMessage());
        }
    }
}
