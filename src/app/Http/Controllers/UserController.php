<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\GetPaginatedUsersRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\UpdateUserRequest;
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
        $user = $this->userService->updateProfile($request->user()->id, $request->validated());

        return $user;
    }

    public function getUsers(GetPaginatedUsersRequest $request)
    {
        return $this->userService->getUsers($request->validated());
    }

    public function updateUser(UpdateUserRequest $request, $id)
    {
        return $this->userService->updateUser($id, $request->validated());
    }

    public function deleteUser($id)
    {
        $deleted = $this->userService->deleteUser($id);

        return ["deleted" => $deleted];
    }
}
