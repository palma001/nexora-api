<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user.
     * 
     * @OA\Post(
     *     path="/api/register",
     *     @OA\Parameter(
     *         name="name", in="query", required=true, @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email", in="query", required=true, @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="password", in="query", required=true, @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="201", description="User registered successfully")
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $this->authService->register($request->all());

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Login user and create token.
     * 
     * @OA\Post(
     *     path="/api/login",
     *     @OA\Response(response="200", description="Login successful")
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $data = $this->authService->login($request->only('login', 'password'));

        return response()->json([
            'message' => 'Login successful',
            'user' => $data['user'],
            'token' => $data['token'],
        ]);
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json(array_merge($user->toArray(), [
            'permissions' => $user->getAllPermissions()
        ]));
    }
}
