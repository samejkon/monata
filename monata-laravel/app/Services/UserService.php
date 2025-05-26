<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected User $model,
    ) {}

    /**
     * Attempt to log in as a user.
     * 
     * @param  array  $data
     * @return \App\Models\User|false
     */
    public function login($data): User|false
    {
        $user = $this->model
            ->where('email', $data['email'])
            ->first();

        if (! $user) {
            return false;
        }

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return false;
        }

        return $user;
    }
}
