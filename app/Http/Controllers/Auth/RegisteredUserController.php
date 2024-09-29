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

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
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

                \Log::info('Cloudinary upload response:', ['uploadResult' => $uploadResult]);

                // Ensure the secure URL is available
                if (isset($uploadResult['secure_url'])) {
                    $image_url = $uploadResult['secure_url'];
                    \Log::info('Image uploaded successfully:', ['secure_url' => $image_url]);
                } else {
                    \Log::error('Cloudinary upload did not return a secure URL');
                }
            } catch (\Exception $e) {
                \Log::error('Image upload failed:', ['error' => $e->getMessage()]);
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

         return response()->json([
            'message' => 'Registration successful. Please verify your email to activate your account.',
            'token' => $token,
            'user' => $user,
        ])->withCookie($cookie);
    }
}
