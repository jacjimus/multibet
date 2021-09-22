<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class VerificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Mark the user's email address as verified.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\User                $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request, User $user)
    {
        //invalid verification
        if (!URL::hasValidSignature($request)) {
            return x_res_json([
                'status' => trans('verification.invalid'),
            ], 400);
        }

        //already verified
        if ($user->hasVerifiedEmail()) {
            return x_res_json([
                'status' => trans('verification.already_verified'),
            ], 400);
        }

        //verify
        $user->markEmailAsVerified();

        //event - auth verified
        event(new Verified($user));

        //response
        return x_res_json([
            'status' => trans('verification.verified'),
        ]);
    }

    /**
     * Resend the email verification notification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend(Request $request)
    {
        //validate - request email
        $this->validate($request, ['email' => 'required|email']);

        //get email user
        $email = trim($request->email);
        //$user = User::withTrashed()->where('email', $email)->first();
        $user = User::where('email', $email)->first();

        //user not found
        if (is_null($user)) {
            throw ValidationException::withMessages([
                'email' => [
                    trans('verification.user-email', ['email' => $email]),
                ],
            ]);
        }

        //already verified
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => [
                    trans('verification.already_verified_email', ['email' => $email]),
                ],
            ]);
        }

        //send verification email
        $user->sendEmailVerificationNotification();

        //response
        return x_res_json([
            'status' => [
                trans('verification.verify-email-sent', ['email' => $email]),
            ],
        ]);
    }
}
