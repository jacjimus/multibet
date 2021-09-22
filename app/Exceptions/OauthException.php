<?php

namespace App\Exceptions;

use Illuminate\Validation\ValidationException;

class OauthException extends ValidationException
{
    /**
     * Render the exception as an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return x_res_view('oauth.error', [
            'title' => trans('auth.oauth-error'),
            'errors' => $this->errors(),
        ], 400);
    }
}
