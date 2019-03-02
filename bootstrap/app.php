<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(dirname(__DIR__)))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
 */

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
 */

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton('cookie', function () use ($app) {
    return $app->loadComponent('session', 'Illuminate\Cookie\CookieServiceProvider', 'cookie');
});

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
 */

$app->routeMiddleware([
    // 'auth' => App\Http\Middleware\Authenticate::class,
    'auth' => Illuminate\Auth\Middleware\Authenticate::class,
    'client' => \Laravel\Passport\Http\Middleware\CheckClientCredentials::class,
]);

$app->middleware([
    'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse'  // <--- added
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
 */

$app->register(App\Providers\AuthServiceProvider::class);
$app->register(Laravel\Passport\PassportServiceProvider::class);
$app->register(Dusterio\LumenPassport\PassportServiceProvider::class);
$app->register(Sentry\SentryLaravel\SentryLumenServiceProvider::class);
$app->register(Urameshibr\Providers\FormRequestServiceProvider::class);
$app->register(Appzcoder\LumenRoutesList\RoutesCommandServiceProvider::class);

$app->bind('Illuminate\Contracts\Cookie\QueueingFactory', 'cookie');

Laravel\Passport\Passport::tokensExpireIn(Carbon\Carbon::now()->addMinutes(10));
Laravel\Passport\Passport::refreshTokensExpireIn(Carbon\Carbon::now()->addDays(10));


/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
 */

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/web.php';
});

// Load config/auth.php
$app->configure('auth');

return $app;
