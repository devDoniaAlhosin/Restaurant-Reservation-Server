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
     * @OA\Get(
     *     path="/api/verify-email/{id}/{hash}",
     *     tags={"Auth"},
     *     summary="Verify user's email using ID and hash",
     *     description="This endpoint verifies the user's email using a signed URL. It requires the user ID and verification hash in the URL.",
     *     operationId="verifyEmailByIdHash",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="hash",
     *         in="path",
     *         required=true,
     *         description="Verification hash",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email address successfully verified",
     *         @OA\JsonContent(
     *             type="string",
     *             example="Email address successfully verified"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid verification link",
     *         @OA\JsonContent(
     *             type="string",
     *             example="Invalid verification link"
     *         )
     *     ),
     *     security={{ "sanctum": {} }},
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests",
     *         @OA\JsonContent(
     *             type="string",
     *             example="Too many requests. Please try again later."
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             type="string",
     *             example="Unauthenticated."
     *         )
     *     )
     * )
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
//        return redirect()->to($verificationUrl);
    }


}
