<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Cookie;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): JsonResponse
    {


        $login = $request->input('login'); // 'username' or 'email'
        $password = $request->input('password');


        $credentials = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? ['email' => $login, 'password' => $password]
            : ['username' => $login, 'password' => $password];


        Log::info('Login Attempt: ', $credentials);

        // Attempt login with the provided credentials
        if (!Auth::attempt($credentials)) {
            Log::error('Login failed for: ', $credentials);
            return response()->json(['message' => 'Invalid Login Credentials'], 401);
        }


        $user = Auth::user();

        // Generate a token based on the user role
        if ($user->role == 'admin') {
            $user->tokens()->delete();
            $token = $user->createToken('AdminToken')->plainTextToken;
            $cookie = cookie('token', $token, 60 * 24);
            Log::info('Admin logged in: ', ['user' => $user->username]);

            return response()->json([
                'token' => $token,
                'user' => $user,
                'message' => 'Admin Successfully Logged In'
            ])->withCookie($cookie);
        } elseif ($user->role == 'user') {
            $user->tokens()->delete();

            $token = $user->createToken('UserToken')->plainTextToken;
            $cookie = cookie('token', $token, 60 * 24);
            Log::info('User logged in: ', ['user' => $user->username]);

            return response()->json([
                'token' => $token,
                'user' => $user,
                'message' => 'User Successfully Logged In'
            ])->withCookie($cookie);
        } else {
            Log::error('Unauthorized role detected: ', ['role' => $user->role]);
            return response()->json(['message' => 'Unauthorized'], 403);
        }

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        if (Auth::check()) {
            $cookie = Cookie::forget('token');
            Auth::user()->tokens()->delete();
            return response()->json(['message' => 'Logged out successfully'])->withCookie($cookie);
        } else {
            return response()->json(['error' => 'User is not authenticated'], 401);
        }
    }
}
