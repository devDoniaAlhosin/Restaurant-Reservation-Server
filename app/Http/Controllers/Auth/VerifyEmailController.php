<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse|JsonResponse
    // RedirectResponse ,JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
//            return redirect()->intended(
//                config('app.frontend_url').RouteServiceProvider::HOME.'?verified=1'
//            );
//            return $this->handleResponse($request, 'Email address already verified.');
            return response()->json('Email address already verified');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }
//        return redirect()->intended(config('app.frontend_url') . RouteServiceProvider::HOME . '?verified=1');

//        return redirect()->intended(
//            config('app.frontend_url').RouteServiceProvider::HOME.'?verified=1'
//        );
        return response()->json('Email address successfully verified');
    }


}
