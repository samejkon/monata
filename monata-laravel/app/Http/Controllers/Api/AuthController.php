<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $service
    ) {}

    /**
     * Authenticate a user and return the user data with an access token.
     *
     * @param \Modules\ApiUserManager\Http\Controllers\Requests\Auth\LoginRequest $request
     *
     */
    public function login(Request $request)
    {
        $user = $this->service->login($request->all());

        if (! $user) {
            return response()->json(['message' => 'Error'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
        ]);
    }
}
