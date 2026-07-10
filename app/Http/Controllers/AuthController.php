<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'not_regex:/[\r\n]/'],
            'password' => ['required', 'string'],
        ]);

        $token = $this->guard()->attempt($credentials);

        if (! $token) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        return $this->tokenResponse($token);
    }

    public function me(): JsonResponse
    {
        return response()->json([
            'user' => $this->guard()->user(),
        ]);
    }

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        $this->guard()->logout();

        $response = $request->expectsJson()
            ? response()->json(['message' => 'Logged out successfully.'])
            : redirect()->route('login');

        return $response->withoutCookie('token');
    }

    private function tokenResponse(string $token, int $status = 200): JsonResponse
    {
        $guard = $this->guard()->setToken($token);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
            'user' => $guard->user(),
        ], $status)->withCookie(cookie(
            'token',
            $token,
            $guard->factory()->getTTL(),
            '/',
            null,
            request()->isSecure(),
            true,
            false,
            'lax',
        ));
    }

    private function guard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = Auth::guard('api');

        return $guard;
    }
}
