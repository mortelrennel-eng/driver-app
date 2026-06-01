<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Euro Taxi System</title>
    <meta http-equiv="refresh" content="0; url={{ auth()->check() ? route('dashboard') : route('login') }}">
</head>
<body>
    <p>Redirecting... <a href="{{ auth()->check() ? route('dashboard') : route('login') }}">Click here if not redirected.</a></p>
</body>
</html>
