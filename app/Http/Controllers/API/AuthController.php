<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\LoginApiRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Token-based authentication for the REST API (Laravel Sanctum personal access
 * tokens). Stateless: no session is opened, the client authenticates every
 * request with the issued bearer token.
 */
class AuthController extends Controller
{
    /**
     * Issue a personal access token for valid credentials.
     */
    public function login(LoginApiRequest $request): JsonResponse
    {
        $user = User::query()
            ->with('role')
            ->where('email', $request->string('email'))
            ->first();

        // Generic failure to avoid leaking which part of the credentials was
        // wrong, and to reject disabled accounts.
        if ($user === null || ! Hash::check((string) $request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => [__('This account has been disabled.')],
            ]);
        }

        $deviceName = $request->filled('device_name')
            ? (string) $request->string('device_name')
            : 'api';

        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ],
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Revoke the token used for the current request.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('Logged out.')]);
    }

    /**
     * Return the authenticated user.
     */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->load('role'));
    }
}

