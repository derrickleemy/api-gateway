<?php

namespace App\Auth;

use App\User;
use Auth;
use GuzzleHttp\Client;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Application;

class LoginProxy
{
    const REFRESH_TOKEN = 'refreshToken';
    const ACCESS_TOKEN = 'accessToken';

    private $cookie;
    private $config;
    private $request;
    private $auth;
    private $encrypter;

    public function __construct(Application $app)
    {
        $this->auth = $app->make('auth');
        $this->cookie = $app->make('cookie');
        $this->config = $app->make('config');
        $this->request = $app->make('request');
        $this->encrypter = $app->make('encrypter');
    }

    /**
     * Attempt to create an access token using user credentials
     *
     * @param string $email
     * @param string $password
     */
    public function attemptLogin($email, $password)
    {
        try {
            $user = User::where('email', $email)->firstOrFail();
            return $this->proxy('password', ['username' => $email, 'password' => $password]);
        } catch (ModelNotFoundException $e) {
            throw new AuthenticationException('invalid_credentials');
        }
    }

    /**
     * Attempt to create an access token using client credentials
     */
    public function attemptClientLogin()
    {
        return $this->proxy('client_credentials');
    }

    /**
     * Attempt to refresh the access token used a refresh token that
     * has been saved in a cookie
     */
    public function attemptRefresh()
    {
        $refreshToken = $this->request->cookie(self::REFRESH_TOKEN);

        return $this->proxy('refresh_token', ['refresh_token' => $this->encrypter->decrypt($refreshToken)]);
    }

    /**
     * Proxy a request to the OAuth server.
     *
     * @param string $grantType what type of grant type should be proxied
     * @param array $data the data to send to the server
     */
    public function proxy($grantType, array $data = [])
    {
        if (empty($data)) {
            // Client Credentials
            $data = array_merge($data, [
                'client_id' => env('CLIENT_ID'),
                'client_secret' => env('CLIENT_SECRET'),
                'grant_type' => $grantType,
            ]);
        } else {
            // Password Credentials
            $data = array_merge($data, [
                'client_id' => env('PASSWORD_CLIENT_ID'),
                'client_secret' => env('PASSWORD_CLIENT_SECRET'),
                'grant_type' => $grantType,
            ]);
        }

        $data = $this->generateTokens(['form_params' => $data]);
        $accessToken = $data['access_token'];
        $encryptedAccessToken = $this->encrypter->encrypt($data['access_token']);
        $this->generateAccessTokenCookie($encryptedAccessToken);

        $refreshToken = isset($data['refresh_token']) ? $data['refresh_token'] : null;

        if ($refreshToken) {
            $encryptedRefreshToken = $this->encrypter->encrypt($refreshToken);
            $this->generateRefreshTokenCookie($encryptedRefreshToken);

            $data = [
                'token_type' => $data['token_type'],
                'access_token' => $accessToken,
                'expires_in' => $data['expires_in'],
                'refresh_token' => $refreshToken,
            ];
        } else {
            $data = [
                'token_type' => $data['token_type'],
                'access_token' => $accessToken,
                'expires_in' => $data['expires_in'],
            ];
        }

        return $data;
    }

    public function generateTokens($formParams)
    {
        $client = new Client();
        $response = $client->post(sprintf('%s/oauth/token', $this->config->get('app.url')), $formParams);
        return json_decode((string) $response->getBody(), true);
    }

    public function generateAccessTokenCookie($accessToken)
    {
        $this->cookie->queue(
            self::ACCESS_TOKEN, // Name
            $accessToken, // Value
            10, // Time in Minutes (10 mins)
            "/", // Path
            "ceito.localhost", // Domain
            true, // Secure
            true// HttpOnly
        );

    }

    public function generateRefreshTokenCookie($refreshToken)
    {
        $this->cookie->queue(
            self::REFRESH_TOKEN, // Name
            $refreshToken, // Value
            14400, // Time in Minutes (10 days)
            "/", // Path
            "ceito.localhost", // Domain
            true, // Secure
            true// HttpOnly
        );
    }

    /**
     * Logs out the user. We revoke access token and refresh token.
     * Also instruct the client to forget the refresh cookie.
     */
    public function logout()
    {
        // Get the accessToken from Auth
        // Need to fix AuthServiceProvider first
        $user = $this->auth->user();
        $accessToken = $user->token();
        dd($user);
        // $refreshToken = $this->db
        //     ->table('oauth_refresh_tokens')
        //     ->where('access_token_id', $accessToken->id)
        //     ->update([
        //         'revoked' => true,
        //     ]);

        //     dd($refreshToken);
        $accessToken->revoke();

        $this->cookie->queue($this->cookie->forget(self::REFRESH_TOKEN));
    }
}
