<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $service
    ) {}

    /**
     * Authenticate a user and return the user data with an access token.
     *
     * @param \Modules\ApiUserManager\Http\Controllers\Requests\Auth\LoginRequest $request
     *
     */
    public function login(LoginRequest $request)
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

    /**
     * Logout user and delete all tokens of the user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successfully!'
        ]);
    }
}
