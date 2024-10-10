<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
     /**
     * @OA\Post(
     *     path="/api/email/verification-notification",
     *     tags={"Auth"},
     *     summary="Resend email verification notification",
     *     description="Sends a new email verification link to the authenticated user. If the user has already verified their email, they are redirected to the home page.",
     *     operationId="sendVerificationNotification",
     *     @OA\Response(
     *         response=200,
     *         description="Verification link sent",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="verification-link-sent"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="User already verified",
     *         @OA\JsonContent(
     *             type="string",
     *             example="Redirected to home"
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests",
     *         @OA\JsonContent(
     *             type="string",
     *             example="Too many requests. Please try again later."
     *         )
     *     ),
     *     security={{ "sanctum": {} }},
     *     @OA\RequestBody(
     *         required=false,
     *         description="No body is required, authenticated user is automatically detected"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => 'verification-link-sent']);
    }
}
