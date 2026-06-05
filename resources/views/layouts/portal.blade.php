<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>{{ config('academyhub.school_name', config('app.name', 'AcademyHub')) }} &middot; CBT Portal</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>

    <body class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50 text-slate-900">
        <main>
            {{ $slot }}
        </main>

        @livewireScripts
        @stack('scripts')
    </body>
</html>

