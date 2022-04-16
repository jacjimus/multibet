<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\VerifyEmailException;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    //traits
    use AuthenticatesUsers;

    //var - errors
    private $errors = [];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Validate login request.
     *
     * @param Request $request
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            'password' => 'string|required',
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        //set input
        $input = $request->all();
        $auth_password = null;
        $auth_value = null;
        $auth_key = 'email';

        //auth - email
        $auth_value = 'james@bremak.co.ke';

        //auth - password
        if (isset($input[$key = 'password'])) {
            $auth_password = $input[$key];
        }

        //check credentials
        if (is_null($auth_key)) {
            $this->errors['error'] = trans('auth.invalid-request');

            return false;
        }
        if (!x_is_string($auth_value, 1)) {
            $this->errors[$auth_key] = trans('auth.invalid-username', ['username' => $auth_key]);

            return false;
        }
        if (!x_is_string($auth_password, 1)) {
            $this->errors['password'] = trans('auth.invalid-password');

            return false;
        }
        if (x_is_email($auth_value)) {
            $auth_key = 'email';
        }

        //auth token - authenticate credentials
        if (!($token = $this->guard()->attempt([$auth_key => $auth_value, 'password' => $auth_password]))) {
            $this->errors['error'] = trans('auth.invalid-credentials');

            return false;
        }

        //auth user
        $user = $this->guard()->user();

        //check deleted
        if ($user->trashed()) {
            $this->errors['error'] = trans('auth.account-deleted');

            return false;
        }

        //check disabled
        if ((int) $user->status === 0) {
            $this->errors['error'] = trans('auth.account-disabled');

            return false;
        }

        //check email verification
        if ($auth_key == 'email' && config('auth.verify_email') && $user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            $this->errors['verify_email'] = 1;

            return false;
        }

        //set auth token
        $this->guard()->setToken($token);

        //success
        return true;
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);
        $token = (string) $this->guard()->getToken();
        $expires = $this->guard()->getPayload()->get('exp') - time();

        //session response
        return x_res_json([
            'message' => trans('auth.login-success'),
            'token_type' => 'bearer',
            'token' => $token,
            'expires' => $expires,
        ]);
    }

    /**
     * Get the failed login response instance.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        //VerifyEmailException
        if (isset($this->errors['verify_email'])) {
            $user = $this->guard()->user();

            throw VerifyEmailException::forUser($user);
        }

        //error messages
        $messages = is_array($errors = $this->errors) && !empty($errors) ? $errors : [
            $this->username() => [trans('auth.failed')],
        ];

        //ValidationException
        throw ValidationException::withMessages($messages);
    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
    }
}
