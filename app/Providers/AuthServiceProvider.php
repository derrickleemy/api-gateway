<?php

namespace App\Providers;

use App\User;
use Illuminate\Auth\AuthServiceProvider as ServiceProvider;

// use Illuminate\Support\ServiceProvider;

use PeterPetrus\Auth\PassportToken;

class AuthServiceProvider extends ServiceProvider
{

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // dd($this->app['auth']->guard('api')->user());

        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.
        $this->app['auth']->viaRequest('passport', function ($request) {

            // Create PassportToken with the bearer token
            $token = new PassportToken($request->bearerToken());
            $token_exists = PassportToken::existsValidToken($token->token_id, $token->user_id);

            // Get the user associated with the token
            if ($token_exists) {
                $user = User::find($token->user_id);

                // Manually set the token of the user to the PassportToken
                // This is different than originally expected. This will set the AccessToken as a PassportToken instead of Laravel's Token
                $user->withAccessToken($token); 
                return $user;
            }

            return null;
            // dd($request->bearerToken());
            // dd($test);
            // dd(Auth::check(), Auth::user());
            // Get instance of the user by the accessToken
            // dd("test");
            // dd(Auth::user());
            // dd($request);
            // dd($request->user('api'));
            // dd(Auth::guard('api')->user());
            // return User::find(1);
        });
        // accessToken is a string

        // $this->app['auth']->viaRequest('passport', function ($request) {

        //     $accessToken = $request->cookie('accessToken');
        //     if ($accessToken) {
        //         $accessToken = $this->app['encrypter']->decrypt($accessToken);
        //         $accessToken = new Token($accessToken);
        //         dd($accessToken);

        //         // // Need to find a way to get the user with this access token
        //         // $user = OauthAccessToken::find($request->cookie('accessToken'))->first();
        //         $user = User::find(1);
        //         // dd($user->token());
        //         return $user;
        //     }

        // });
    }
}

// Not very sure about guards, should i use custom guard or passport guard
