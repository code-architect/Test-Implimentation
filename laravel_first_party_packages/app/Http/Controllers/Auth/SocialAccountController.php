<?php

namespace App\Http\Controllers\Auth;

use App\Models\SocialAccount;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Session;

use Illuminate\Http\Request;
use Socialite;

use App\Http\Controllers\Controller;

class SocialAccountController extends Controller
{
    /**
     * This will execute after the service provider authenticated the user.
     * The user has authorized to give us access to the service provider.
     * The service provider then gonna send the user back and its gonna send them o this via url(route).
     * Its gonna send back with an authorization grant
     * @param $provider
     * @return mixed
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }


    /**
     * After socialite redirects back to the service provider one more time, with the authorization grant,
     * the service provider is going to receive the authorization grant if it is valid. Then its going to
     *  convert all the user information going to need, including an access token. We save it here.
     * @param $provider
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function handleProviderCallback($provider)
    {
        try {
            $user = Socialite::driver($provider)->user();
        }catch (\Exception $e) {
            return redirect('/login');
        }

        $authUser = $this->findOrCreateUser($user, $provider);

        Auth::login($authUser, true);

        return redirect('/home');
    }


    public function findOrCreateUser($socialUser, $provider)
    {
        $account = SocialAccount::where('provider_name', $provider)->where('provider_id', $socialUser->getId())->first();

        if($account)
        {
            return $account->user;
        }else{
            // check if the user's email is there
            $user = User::where('email', $socialUser->getEmail())->first();
            // if not found create the new user
            if(!$user)
            {
                $user = User::create([
                    'email' =>  $socialUser->getEmail(),
                    'name'  =>  $socialUser->getName()
                ]);
            }

            // if user is there but logged in with a specific provider, create it
            $user->accounts()->create([
                'provider_name' =>  $provider,
                'provider_id' =>  $socialUser->getId()
            ]);

            return $user;
        }
    }
}
