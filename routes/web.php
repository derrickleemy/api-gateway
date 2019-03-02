<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

//  This won't work unless the access token is in the header
$router->group(['middleware' => 'auth:api'], function () use ($router) {
    $router->get('/', function () use ($router) {return $router->app->version();});
    $router->post('/logout', '\App\Auth\LoginController@logout');
});

$router->post('/client', '\App\Auth\LoginController@client');
$router->post('/login', '\App\Auth\LoginController@login');
$router->post('/login/refresh', '\App\Auth\LoginController@refresh');

// LumenPassport Routes 
$router->post('/oauth/token', '\Dusterio\LumenPassport\Http\Controllers\AccessTokenController@issueToken');
$router->group(['middleware' => 'auth', 'prefix' => 'oauth'], function () use ($router) {
    $router->get('/tokens', '\Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController@forUser');
    $router->delete('/tokens/{token_id}', '\Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController@destroy');
    $router->post('/tokens/refresh', '\Laravel\Passport\Http\Controllers\TransientTokenController@refresh');
});