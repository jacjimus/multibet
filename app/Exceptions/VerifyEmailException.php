<?php

namespace App\Exceptions;

use Illuminate\Validation\ValidationException;

class VerifyEmailException extends ValidationException
{
    /**
     * Email verification user messages.
     *
     * @param \App\User $user
     *
     * @return static
     */
    public static function forUser($user)
    {
        $message = trans('verification.verify-email-error', [
            'data' => urlencode(base64_encode(json_encode(['email' => $user->email]))),
        ]);

        return static::withMessages(['email' => [$message]]);
    }
}
