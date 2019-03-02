<?php

return array(
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    
    'environment' => env('APP_ENV'),

    // capture release as git sha
    'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),

    // Capture bindings on SQL queries
    'breadcrumbs.sql_bindings' => true,

    // Capture default user context
    'user_context' => false,
);
