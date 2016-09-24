<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Illuminate\Http\Request;
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
     * @param Request The HTTP request
     * @param $provider The OAuth provider
     *
     * @return Response
     */
    public function handleProviderCallback(Request $request, $provider)
    {
        if (!$this->isSupportedProvder($provider)) {
            return redirect('/login');
        }

        $socialUser = Socialite::driver($provider)->user();

        $user = User::where('email', $socialUser->email)->first();

        // We now allow users to 'connect' their existing accounts with a social
        // provider. Right now the last used social provider will overwrite the
        // relevant info, including external_id, avatar url and social_provider.
        // It would be better to have a table of per-user social providers, but
        // we don't really need the data for anything important.
        $user = $this->createOrUpdateSocialUser($provider, $user, $socialUser);

        // Regenerate session to prevent session fixation
        $request->session()->regenerate();

        Auth::guard()->login($user);

        return redirect('/home');
    }

    protected function createOrUpdateSocialUser($provider, $user, $socialUser)
    {
        $attrs = [
            'avatar'          => $socialUser->avatar,
            'expires_in'      => $socialUser->expiresIn,
            'external_id'     => $socialUser->id,
            'name'            => $socialUser->name ? $socialUser->name : "No name provided",
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
