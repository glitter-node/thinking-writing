<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="application-name" content="Glitter Thought Write">
        <meta name="application-version" content="{{ app_version() }}">
        <meta name="description" content="A personal thinking workspace for capturing and evolving ideas.">

        <title>{{ config('app.name', 'Glitter Thought Write') }}</title>

        <link rel="icon" href="/favicon/favicon.ico">
        <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
        <link rel="apple-touch-icon" href="/favicon/apple-touch-icon.png">
        <link rel="manifest" href="/favicon/site.webmanifest">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,700&family=dm-sans:400,500,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-['DM_Sans'] antialiased">
        <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(251,146,60,0.25),_transparent_28%),radial-gradient(circle_at_top_right,_rgba(20,184,166,0.18),_transparent_24%),linear-gradient(180deg,_#1c1917,_#09090b)]">
            @include('layouts.navigation')

            @isset($header)
                <header class="mx-auto max-w-7xl px-4 pb-4 pt-8 sm:px-6 lg:px-8">
                    <div class="glass-panel px-6 py-5">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="pb-12">
                {{ $slot }}
            </main>

            <footer class="mx-auto max-w-7xl px-4 pb-8 sm:px-6 lg:px-8">
                <div class="border-t border-white/10 pt-5 text-sm text-stone-400">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p>Glitter Thought Write - {{ app_version() }}</p>
                        <a href="{{ route('about') }}" class="text-stone-300 transition hover:text-orange-200">About</a>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
