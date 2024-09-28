<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class GoogleController extends Controller
{
    // Redirect the user to Google's OAuth page
    public function redirectToGoogle()
    {$redirectUrl = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        \Log::info('Redirecting to Google: ' . $redirectUrl);
        return Socialite::driver('google')->stateless()->redirect();
    }



    public function handleGoogleCallback()
{
    try {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            Auth::login($user);
        } else {
            $user = User::create([
                'username' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'image' => $googleUser->getAvatar(),
                'password' => bcrypt('secret'),
                'email_verified_at' => now()
            ]);

            Auth::login($user);
        }


        $token = $user->createToken('auth_token')->plainTextToken;


        return redirect(config(app("app.frontend_url") . "/profile"));;


    } catch (\Exception $e) {
        return redirect('/login')->withErrors('Unable to login using Google.');
    }
}


}


