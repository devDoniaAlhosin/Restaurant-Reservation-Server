<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class GoogleController extends Controller
{
    // Redirect the user to Google's OAuth page
    public function redirectToGoogle()
    {$redirectUrl = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        Log::info('Redirecting to Google: ' . $redirectUrl);
        return Socialite::driver('google')
            ->scopes(['profile', 'email'])
            ->stateless()
            ->redirect();
    }



    public function handleGoogleCallback(Request $request)
    {
        // fetch user Token . User details
//        dd(Socialite::driver('google')->stateless()->user());

        try {
            // Fetch user details from Google
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Extract Google user data
            $email = $googleUser->email;
            $image = $googleUser->avatar;
            $address = 'N/A';
            $phone = 'N/A';


            Log::info('Google User Data: ', (array) $googleUser);
            Log::info('Google User ID: ' . ($googleUser->id ?? 'Not Available'));
            Log::info('Google User Email: ' . $email);

            // Find or create user
            $user = User::where('email', $email)->first();
            $email_verified_at = $googleUser->user['email_verified'] ? now() : null;

            if ($user) {
                Auth::login($user);
            } else {
                $user = User::create([
                    'username' => $googleUser->name,
                    'email' => $email,
                    'google_id' => $googleUser->user['sub'],
                    'image' => $image,
                    'password' => Hash::make('secret'),
                    'email_verified_at' => $email_verified_at,
                    'address' => $address,
                    'phone' => $phone,
                ]);
                Auth::login($user);
            }

            // Create API token
            $token = $user->createToken('auth_token')->plainTextToken;

//            $cookie = cookie('token', $token, 60*24);
            $userData = json_encode($user);
//            $userCookie = cookie('user', $user, 60*24);

            $cookie = cookie('token', $token, 60*24, '/', 'localhost', false, true, false, 'None');
            $userCookie = cookie('user', $userData, 60*24, '/', 'localhost', false, true, false, 'None');

            return redirect(config('app.frontend_url') . '/')->withCookies([$cookie, $userCookie]);
//            return response()->json([
//                'token' => $token,
//                'user' => $user,
//            ]);


        } catch (\Exception $e) {
            Log::error('Google OAuth Error: ' . $e->getMessage());
            return response()->json(['error' => 'Authentication failed'], 500);
        }
    }


}


