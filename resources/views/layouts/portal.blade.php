<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
        <meta name="apple-mobile-web-app-title" content="{{ config('academyhub.school_name', config('app.name', 'AcademyHub')) }}" />
        <meta name="mobile-web-app-capable" content="yes" />
        <meta name="theme-color" content="#0f172a" />
        <link rel="manifest" href="/manifest.json" />
        @if(config('academyhub.school_logo'))
            <link rel="apple-touch-icon" href="{{ asset('uploads/'.str_replace('\\','/',config('academyhub.school_logo'))) }}" />
        @else
            <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" />
        @endif

        <title>{{ config('academyhub.school_name', config('app.name', 'AcademyHub')) }} &middot; CBT Portal</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>

    <body class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50 text-slate-900">
        {{-- Native PWA Boot Splash Screen --}}
        <x-pwa-splash-screen />

        <main>
            {{ $slot }}
        </main>

        @livewireScripts
        @stack('scripts')
    </body>
</html>

