<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    /**
     * Update user by ID
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return \App\Models\User
     */
    public function updateUser(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user->fresh();
    }
}
