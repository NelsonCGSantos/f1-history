<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'F1 History' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 font-sans antialiased selection:bg-red-600/80 selection:text-white">
    @php
        $latestSeasonYear = \Illuminate\Support\Facades\Cache::remember(
            'front:navigation:latest-season',
            now()->addMinutes(30),
            fn () => \App\Models\Meeting::max('season_year')
        );
    @endphp

    <header class="border-b border-slate-800/60 bg-slate-950/70 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-red-500 transition hover:text-red-400">
                <span class="rounded-full bg-red-600 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-white">F1</span>
                <span class="text-lg font-semibold text-white">History</span>
            </a>
            <nav class="flex items-center gap-4 text-sm font-medium text-slate-300">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-white' : 'hover:text-white transition' }}">Overview</a>
                @if($latestSeasonYear)
                    <a href="{{ route('seasons.show', $latestSeasonYear) }}" class="{{ request()->routeIs('seasons.show') && (int) request()->route('year') === (int) $latestSeasonYear ? 'text-white' : 'hover:text-white transition' }}">Latest Season</a>
                    <a href="{{ route('seasons.standings', $latestSeasonYear) }}" class="{{ request()->routeIs('seasons.standings') && (int) request()->route('year') === (int) $latestSeasonYear ? 'text-white' : 'hover:text-white transition' }}">Standings</a>
                @endif
                <a href="https://www.formula1.com/" target="_blank" rel="noopener" class="hidden sm:inline-flex items-center gap-1 rounded-full border border-slate-700/60 px-3 py-1 text-xs uppercase tracking-widest text-slate-400 hover:border-slate-500 hover:text-white">
                    Live Timing
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 4.5H19.5V10.5M19.5 4.5L4.5 19.5" />
                    </svg>
                </a>
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    <footer class="border-t border-slate-800/60 bg-slate-950/80">
        <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>&copy; {{ now()->year }} F1 History. Data sourced from OpenF1.</p>
            <p>Built with Laravel, optimised for fast queries and cached responses.</p>
        </div>
    </footer>
</body>
</html>

