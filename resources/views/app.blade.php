<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Do123</title>
        <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
        @routes
        @vite('resources/js/app.js')
        @inertiaHead
    </head>
    <body class="font-sans antialiased dark:bg-black dark:text-white/50">
        @inertia
    </body>
</html>