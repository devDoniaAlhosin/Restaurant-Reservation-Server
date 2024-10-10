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
     * @OA\Post(
     *     path="/api/login",
     *     summary="User Login",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"login", "password"},
     *             @OA\Property(property="login", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="yourpassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful Login",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="your-token-string"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="username"),
     *                 @OA\Property(property="role", type="string", example="user")
     *             ),
     *             @OA\Property(property="message", type="string", example="User Successfully Logged In")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid Login Credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid Login Credentials")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout the authenticated user",
     *     description="Destroys the authenticated session and logs the user out by deleting their authentication token.",
     *     operationId="logoutUser",
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Logged out successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="User is not authenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="User is not authenticated"
     *             )
     *         )
     *     ),
     *     security={{ "sanctum": {} }}
     * )
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
