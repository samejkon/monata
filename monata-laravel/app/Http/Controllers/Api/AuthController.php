<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

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
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->service->login($request->validated());

        if (! $user) {
            return response()->json('Invalid credentials')->setStatusCode(401);
        }

        return response()->json('Logged in successfully')->setStatusCode(200);
    }

    /**
     * Logout and delete the current access token.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request): Response
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
