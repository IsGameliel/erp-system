<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $applicationBrandName }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 text-slate-900 antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-[280px_minmax(0,1fr)]">
            <x-sidebar />

            <div class="min-w-0">
                <x-topbar />

                <main class="space-y-6 px-4 py-6 sm:px-6 lg:px-8">
                    @isset($header)
                        <header class="rounded-3xl border border-white/70 bg-white/90 px-6 py-5 shadow-sm shadow-slate-200/60">
                            {{ $header }}
                        </header>
                    @endisset

                    <x-alert />

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
