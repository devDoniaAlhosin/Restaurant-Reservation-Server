<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse|JsonResponse
    // RedirectResponse ,JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json('Email address already verified');
//            $frontendUrl = config('app.frontend_url') . '/verify-email?message=email_already_verified';
//            return redirect()->to($frontendUrl);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));

        }

        return response()->json('Email address successfully verified');
//        $verificationUrl = config('app.frontend_url') . "/verify-email?verificationUrl=" . urlencode($request->fullUrl());
//
//        return redirect()->to($verificationUrl);
    }


}
