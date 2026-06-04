<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <title>WatchList</title>
    </head>
    <body class="min-h-screen flex items-center justify-center p-6 bg-white dark:bg-black text-black dark:text-white">
        <main class="w-full max-w-md text-center">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-3 mb-6 justify-center">
                <x-app-logo-icon class="h-8 w-8" />
            </a>
            <h1 class="text-2xl font-medium mb-2">Welcome!</h1>
            <p class="text-sm mb-6 text-gray-600 dark:text-gray-300"></p>
            <div class="flex justify-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 border rounded text-sm">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 border rounded text-sm">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="px-4 py-2 border rounded text-sm">Sign up</a>
                    @endif
                @endauth
            </div>
        </main>
    </body>
</html>
