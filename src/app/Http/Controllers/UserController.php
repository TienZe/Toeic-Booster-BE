<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\GetPaginatedUsersRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {
    }

    /**
     * Update user profile
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $this->userService->updateProfile($request->validated());
        return $user;
    }

    public function getUsers(GetPaginatedUsersRequest $request)
    {
        return $this->userService->getUsers($request->validated());
    }
}
