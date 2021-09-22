<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\OauthException;
use App\Http\Controllers\Controller;
use App\Models\OauthProvider;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Socialite\Facades\Socialite;

class OauthController extends Controller
{
    //traits
    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $providers = x_split(',', trim(config('auth.oauth_providers')), $count, 1, 1);
        foreach ($providers as $provider) {
            config(["services.$provider.redirect" => route('oauth.callback', $provider)]);
        }
    }

    /**
     * Redirect the user to the provider authentication page.
     *
     * @param string $provider
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect($provider)
    {
        return ['url' => Socialite::driver($provider)->stateless()->redirect()->getTargetUrl()];
    }

    /**
     * Obtain the user information from the provider.
     *
     * @param string $provider
     *
     * @return \Illuminate\Http\Response
     */
    public function handleCallback($provider)
    {
        //get oauth user
        $user = Socialite::driver($provider)->stateless()->user();

        //get or create user
        $user = $this->findOrCreateUser($provider, $user);

        //authenticate session
        $token = $this->guard()->login($user);
        $expires = $this->guard()->getPayload()->get('exp') - time();
        $this->guard()->setToken($token);

        //session data
        $data = [
            'message' => trans('auth.login-success'),
            'token_type' => 'bearer',
            'token' => $token,
            'expires' => $expires,
        ];

        //response
        return x_res_view('oauth.callback', ['data' => $data]);
    }

    /**
     * Find or Create OAuth User.
     *
     * @param string                            $provider
     * @param \Laravel\Socialite\Contracts\User $sUser
     *
     * @return \App\Models\User
     */
    protected function findOrCreateUser($provider, $user)
    {
        //existing oauth session
        if ($oauth_provider = OauthProvider::where('provider', $provider)->where('provider_user_id', $user->getId())->first()) {
            try {
                //update oauth tokens
                $data = [
                    'access_token' => $user->token,
                    'refresh_token' => $user->refreshToken,
                ];
                $oauth_provider->setInput($data);
                $oauth_provider->save();

                //set oauth session user
                $oauth_user = $oauth_provider->user;

                //update user avatar
                if (x_is_url($avatar = $user->getAvatar()) && !x_is_string($oauth_user->avatar, 1)) {
                    $oauth_user->setInput(['avatar' => $avatar]);
                    $oauth_user->save();
                }

                //return user
                return $oauth_user;
            } catch (Exception $e) {
                $errors = method_exists($e, 'errors') ? $e->errors() : ['message' => $e->getMessage()];

                throw OauthException::withMessages($errors);
            }
        }

        //oauth user email
        $email = $user->getEmail();

        //update & return existing email user
        if ($email_user = User::where('email', $email)->first()) {
            try {
                //create user oauth session
                $email_user->oauthProviders()->create([
                    'provider' => $provider,
                    'provider_user_id' => $user->getId(),
                    'access_token' => $user->token,
                    'refresh_token' => $user->refreshToken,
                ]);

                //update user avatar
                if (x_is_url($avatar = $user->getAvatar()) && !x_is_string($email_user->avatar, 1)) {
                    $email_user->setInput(['avatar' => $avatar]);
                    $email_user->save();
                }

                //return user
                return $email_user;
            } catch (Exception $e) {
                $errors = method_exists($e, 'errors') ? $e->errors() : ['message' => $e->getMessage()];

                throw OauthException::withMessages($errors);
            }
        }

        //email taken exception (checks trashed)
        if (User::withTrashed()->where('email', $email)->exists()) {
            throw OauthException::withMessages([
                'message' => trans('auth.email-taken', ['email' => $email]),
            ]);
        }

        //create user
        return $this->createUser($provider, $user);
    }

    /**
     * @param string                            $provider
     * @param \Laravel\Socialite\Contracts\User $sUser
     *
     * @return \App\Models\User
     */
    protected function createUser($provider, $sUser)
    {
        try {
            //register user
            $user = app('UserService')->createUser([
                'avatar' => $sUser->getAvatar(),
                'name' => $sUser->getName(),
                'email' => $sUser->getEmail(),
                'email_verified_at' => now(),
            ]);

            //create user oauth session
            $user->oauthProviders()->create([
                'provider' => $provider,
                'provider_user_id' => $sUser->getId(),
                'access_token' => $sUser->token,
                'refresh_token' => $sUser->refreshToken,
            ]);

            //return user
            return $user;
        } catch (Exception $e) {
            $errors = method_exists($e, 'errors') ? $e->errors() : ['message' => $e->getMessage()];

            throw OauthException::withMessages($errors);
        }
    }
}
