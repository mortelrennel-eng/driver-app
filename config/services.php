<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_CALLBACK_URL'),
        'token' => env('GITHUB_API_TOKEN'),
        'repo_owner' => env('GITHUB_REPO_OWNER'),
        'repo_name' => env('GITHUB_REPO_NAME'),
    ],

    'semaphore' => [
        'api_key' => env('SEMAPHORE_API_KEY'),
        'sender_name' => env('SEMAPHORE_SENDER_NAME'),
    ],

    'tracksolid' => [
        'app_key'    => env('TRACKSOLID_APP_KEY'),
        'app_secret' => env('TRACKSOLID_APP_SECRET'),
        'username'   => env('TRACKSOLID_USERNAME'),
        'password'   => env('TRACKSOLID_PASSWORD'),
        'api_url'    => env('TRACKSOLID_API_URL', 'https://hk-open.tracksolidpro.com/route/rest'),
        'drift'      => env('TRACKSOLID_TIME_DRIFT', 0),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
    ],

];
