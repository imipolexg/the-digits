<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Auth;
use RandomLib\Factory as RandomFactory;
use Socialite;

class SocialAuthController extends Controller
{

    /**
     * Redirect to social authentication page
     *
     * @return Response
     */
    public function redirectToProvider($provider)
    {
        if (!$this->isSupportedProvder($provider)) {
            return redirect('/login');
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle social auth callback
     *
     * @return Response
     */
    public function handleProviderCallback($provider)
    {
        if (!$this->isSupportedProvder($provider)) {
            return redirect('/login');
        }

        $socialUser = Socialite::driver($provider)->user();
        \Log::info($provider);

        $user = User::where('email', $socialUser->email)->first();
        if ($user !== null) {
            if ($user->social_provider === null || $user->social_provider !== $provider) {
                // A user exists with this email via another social provider (or no social provider at all)
                $conflict = 'auth.social-conflict-' . $user->social_provider;

                return redirect('/login')->withInput([ 'email' => $user->email])->withErrors([ 'email' => trans($conflict) ]);
           }
        }

        $user = $this->createOrUpdateSocialUser($provider, $user, $socialUser);

        Auth::guard()->login($user);

        return redirect('/home');
    }

    protected function createOrUpdateSocialUser($provider, $user, $socialUser)
    {
        $attrs = [
            'avatar'          => $socialUser->avatar,
            'expires_in'      => $socialUser->expiresIn,
            'external_id'     => $socialUser->id,
            'name'            => $socialUser->name,
            'nickname'        => $socialUser->nickname,
            'refresh_token'   => $socialUser->refreshToken,
            'token'           => $socialUser->token,
            'social_provider' => $provider,
        ];

        if ($user === null) {
            $attrs['email'] = $socialUser->email;
            $attrs['password'] = $this->makeRandomPass();

            return User::create($attrs);
        }

        $user->update($attrs);
        return $user;
    }

    protected function isSupportedProvder($provider)
    {
        $providers = ['github', 'google', 'facebook'];

        return in_array($provider, $providers);
    }

    protected function makeRandomPass()
    {
        $randomFactory = new RandomFactory();
        $generator = $randomFactory->getMediumStrengthGenerator();
        return bcrypt($generator->generateString(64));
    }
}
