<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    //traits
    use ResetsPasswords;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Validation rules.
     *
     * @return void
     */
    public function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => array_merge(['required', 'confirmed'], x_arr(config('auth.password_rules'))),
        ];
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $response
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetResponse(Request $request, $response)
    {
        return ['message' => trans('auth.password-reset-success')];
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $response
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        return x_res_json(['message' => trans($response)], 400);
    }

    /**
     * Password reset view.
     *
     * @return \Illuminate\Http\Response
     */
    public function passwordReset($token, $email)
    {
        $email = base64_decode(urldecode($email));

        return x_res_view('auth.password-reset', ['token' => $token, 'email' => $email]);
    }
}
