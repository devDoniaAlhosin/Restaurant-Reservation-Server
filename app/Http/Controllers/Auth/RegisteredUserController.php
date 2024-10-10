<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\API\UserSchema;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Operations about user authentication"
 * )
 */
class RegisteredUserController extends Controller
{
   /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "email", "password", "phone"},
     *             @OA\Property(property="username", type="string", example="john_doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="strong_password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="strong_password"),
     *             @OA\Property(property="phone", type="string", example="+1234567890"),
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="image", type="string", format="binary"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registration successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Registration successful. Please verify your email to activate your account."),
     *             @OA\Property(property="token", type="string", example="your_token_here"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="verification_url", type="string", example="https://example.com/verify?token=your_token_here"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The username has already been taken."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Image upload failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Image upload failed"),
     *         )
     *     )
     * )
     *
     * Handle an incoming registration request.
     *
     * @param \Illuminate\Http\Request $request The incoming request containing registration data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure with a verification URL and user token.
     */

    public function store(Request $request): JsonResponse
    {
        Configuration::instance([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);
        if (empty(env('CLOUDINARY_CLOUD_NAME'))) {
            Log::error('Cloudinary environment variables are not set.');
        }

        Log::info('Cloudinary Config:', [
            'CLOUDINARY_CLOUD_NAME' => env('CLOUDINARY_CLOUD_NAME'),
            'CLOUDINARY_API_KEY' => env('CLOUDINARY_API_KEY'),
            'CLOUDINARY_API_SECRET' => env('CLOUDINARY_API_SECRET'),
        ]);


        $request->validate([
            'username' => ['required', 'string', 'min:4', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed','min:6' , Rules\Password::defaults()],
            'phone' => ['required'],
            'address' => ['nullable', 'min:5', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048']
        ]);
        $image_url = null;

        if ($request->hasFile('image')) {
            $uploadedFile = $request->file('image');
            try {
                $cloudinary = new Cloudinary();
                $uploadResult = $cloudinary->uploadApi()->upload($uploadedFile->getRealPath());
                Log::info('Cloudinary upload response:', ['uploadResult' => $uploadResult]);

                // Ensure the secure URL is available
                if (isset($uploadResult['secure_url'])) {
                    $image_url = $uploadResult['secure_url'];
                    Log::info('Image uploaded successfully:', ['secure_url' => $image_url]);
                } else {
                    Log::error('Cloudinary upload did not return a secure URL');
                }
            } catch (\Exception $e) {
                Log::error('Image upload failed:', ['error' => $e->getMessage(),'stack' => $e->getTraceAsString()]);

                return response()->json(['message' => 'Image upload failed', 'error' => $e->getMessage()], 500);
            }
        }




        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'image' => $image_url,
        ]);

        event(new Registered($user));

        Auth::login($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        $cookie = cookie('token', $token, 60 * 24, null, null, true, true);

        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Log the signed URL for debugging
        Log::info('Signed URL for email verification: ' . $signedUrl);

         return response()->json([
            'message' => 'Registration successful. Please verify your email to activate your account.',
            'token' => $token,
            'user' => $user,
             'verification_url' => $signedUrl,
        ])->withCookie($cookie);
    }
}
