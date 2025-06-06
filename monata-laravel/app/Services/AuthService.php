<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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
    public function login($data): bool
    {
        if (!Auth::guard('admin')->attempt($data)) {
            return false;
        }

        session()->regenerate();

        return true;
    }
}
