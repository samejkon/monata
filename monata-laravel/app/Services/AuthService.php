<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        protected Admin $model,
    ) {}
    /**
     * Attempt to log in as an admin.
     *
     * @param  array  $data
     * @return \App\Models\Admin|false
     */
    public function login($data): Admin|false
    {
        $admin = $this->model
            ->where('email', $data['email'])
            ->first();

        if (! $admin || ! Hash::check($data['password'], $admin->password)) {
            return false;
        }

        return $admin;
    }
}
