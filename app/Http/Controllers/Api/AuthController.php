<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Handles user authentication via Sanctum API tokens.
 *
 * Provides registration, login, and logout endpoints.
 * All tokens are plain-text personal access tokens issued by Sanctum.
 */
class AuthController extends Controller
{
    /**
     * Register a new user and issue an API token.
     *
     * Validates the incoming registration data, creates the user record,
     * and returns a Sanctum plain-text token for immediate API access.
     *
     * @param Request $request The incoming HTTP request containing registration fields.
     * @return JsonResponse The newly created user and their API token (HTTP 201).
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // First name is required and capped at 255 chars for DB safety
            'name' => ['required', 'string', 'max:255'],
            // Last name is required separately per project spec
            'last_name' => ['required', 'string', 'max:255'],
            // Email must be unique to prevent duplicate accounts
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            // Password must be at least 8 chars and confirmed via password_confirmation field
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // Hashed automatically via User model cast
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Authenticate a user and issue an API token.
     *
     * Validates credentials against the database. On success, issues a new
     * Sanctum token. On failure, returns a 401 with a generic error message
     * to avoid leaking whether the email exists.
     *
     * @param Request $request The incoming HTTP request containing login credentials.
     * @return JsonResponse The authenticated user and their API token, or a 401 error.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        // Use a generic message for both "email not found" and "wrong password"
        // to prevent user enumeration attacks
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Revoke the current user's API token (logout).
     *
     * Deletes the token that was used to authenticate this request,
     * effectively logging the user out of this session only.
     *
     * @param Request $request The incoming authenticated HTTP request.
     * @return JsonResponse Confirmation message (HTTP 200).
     */
    public function logout(Request $request): JsonResponse
    {
        // Delete only the token used for this request, not all user tokens
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.',
        ]);
    }
}
