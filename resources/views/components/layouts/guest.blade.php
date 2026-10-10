<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
        <meta name="apple-mobile-web-app-title" content="{{ config('academyhub.school_name', config('app.name', 'AcademyHub')) }}" />
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="theme-color" content="#0f172a" />
        <meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: light)" />
        <meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: dark)" />
        <link rel="manifest" href="/manifest.json" />
        @php
            $activeFavicon = null;
            if (config('academyhub.school_logo')) {
                $activeFavicon = asset('uploads/' . str_replace('\\', '/', config('academyhub.school_logo')));
            } elseif (app()->bound('currentTenant') && app('currentTenant') && !empty(app('currentTenant')->logo)) {
                $activeFavicon = asset('uploads/' . str_replace('\\', '/', app('currentTenant')->logo));
            } elseif (auth()->check() && auth()->user()->tenant && !empty(auth()->user()->tenant->logo)) {
                $activeFavicon = asset('uploads/' . str_replace('\\', '/', auth()->user()->tenant->logo));
            } else {
                $activeFavicon = asset('icon.png');
            }
        @endphp
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
        <link rel="shortcut icon" href="{{ $activeFavicon }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $activeFavicon }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ $activeFavicon }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $activeFavicon }}">

        <title>{{ config('academyhub.school_name', config('app.name', 'AcademyHub')) }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="h-full">
        {{-- Native PWA Boot Splash Screen --}}
        <x-pwa-splash-screen />

        {{ $slot }}
        <x-pwa-install-prompt />
    </body>
</html>
