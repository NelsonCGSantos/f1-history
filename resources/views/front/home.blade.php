<x-front.layout title="Season Overview">
    <section class="mb-10 flex flex-col gap-6 rounded-3xl border border-slate-800/80 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-8 shadow-xl shadow-slate-950/40 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.35em] text-slate-400">Formula 1 data explorer</p>
            <h1 class="mt-3 text-3xl font-semibold text-white sm:text-4xl">Dive into {{ $latestSeasonYear ?? 'every' }} season of racing history</h1>
            <p class="mt-4 max-w-2xl text-sm text-slate-400 sm:text-base">
                Browse seasons, inspect race classifications, review tyre strategies, and keep up with driver performance.
                The interface caches heavy queries and only loads what you need, so navigating across years stays snappy.
            </p>
            <div class="mt-6 flex flex-wrap items-center gap-3">
                @if($latestSeasonYear)
                    <a href="{{ route('seasons.show', $latestSeasonYear) }}" class="inline-flex items-center gap-2 rounded-full bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-red-900/40 transition hover:bg-red-500">
                        View {{ $latestSeasonYear }} season
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 4.5H19.5V10.5M19.5 4.5L4.5 19.5" />
                        </svg>
                    </a>
                @endif
                <a href="#season-list" class="inline-flex items-center gap-2 rounded-full border border-slate-700/60 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:border-slate-500 hover:text-white">Jump to seasons</a>
            </div>
        </div>
        <dl class="grid grid-cols-2 gap-4 text-sm text-slate-300 sm:grid-cols-1">
            <div class="rounded-2xl border border-slate-700/60 bg-slate-900/60 px-4 py-3 text-right">
                <dt class="text-xs uppercase tracking-[0.3em] text-slate-500">Seasons tracked</dt>
                <dd class="mt-2 text-3xl font-semibold text-white">{{ number_format($seasons->count()) }}</dd>
            </div>
            @if($latestSeasonYear)
                <div class="rounded-2xl border border-slate-700/60 bg-slate-900/60 px-4 py-3 text-right">
                    <dt class="text-xs uppercase tracking-[0.3em] text-slate-500">Latest season</dt>
                    <dd class="mt-2 text-3xl font-semibold text-white">{{ $latestSeasonYear }}</dd>
                </div>
            @endif
        </dl>
    </section>

    <section id="season-list" class="mb-12">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-white">Seasons</h2>
                <p class="mt-2 text-sm text-slate-400">Select a year to view every session, stint, and official classification.</p>
            </div>
            <div class="hidden sm:flex items-center gap-2 text-xs text-slate-400">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                <span>Cached for rapid navigation</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($seasons as $season)
                <a href="{{ route('seasons.show', $season->season_year) }}" class="group flex flex-col justify-between rounded-3xl border border-slate-800/70 bg-slate-900/60 p-6 transition hover:border-red-600/60 hover:bg-slate-900/80">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-3xl font-semibold text-white">{{ $season->season_year }}</p>
                        <span class="rounded-full border border-slate-700 px-3 py-1 text-xs uppercase tracking-[0.3em] text-slate-400 group-hover:border-red-500/80 group-hover:text-red-200">Season</span>
                    </div>
                    <dl class="mt-6 grid grid-cols-2 gap-4 text-sm text-slate-300">
                        <div>
                            <dt class="text-xs uppercase tracking-[0.25em] text-slate-500">Grands Prix</dt>
                            <dd class="mt-1 text-lg font-semibold text-white">{{ $season->race_count }}</dd>
                        </div>
                        <div class="text-right">
                            <dt class="text-xs uppercase tracking-[0.25em] text-slate-500">Status</dt>
                            <dd class="mt-1 text-xs font-medium uppercase text-emerald-400">Ready</dd>
                        </div>
                    </dl>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mb-8">
        <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-white">Latest race weekends</h2>
                <p class="text-sm text-slate-400">Recent events with session counts so you can jump straight into race strategy.</p>
            </div>
            <a href="{{ $latestSeasonYear ? route('seasons.show', $latestSeasonYear) : '#' }}" class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.3em] text-red-400 hover:text-red-300">
                Browse full calendar
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 4.5H19.5V10.5M19.5 4.5L4.5 19.5" />
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            @forelse($recentMeetings as $meeting)
                <article class="flex flex-col rounded-3xl border border-slate-800/70 bg-slate-900/60 p-6">
                    <header class="flex flex-col gap-1">
                        <p class="text-xs uppercase tracking-[0.3em] text-slate-500">{{ $meeting->country ?? 'Unknown' }}</p>
                        <h3 class="text-lg font-semibold text-white">{{ $meeting->name }}</h3>
                        <p class="text-xs text-slate-400">{{ optional($meeting->start_date)->format('d M Y') }} — {{ optional($meeting->end_date)->format('d M Y') }}</p>
                    </header>
                    <dl class="mt-4 grid grid-cols-3 gap-3 text-center text-xs text-slate-300">
                        @foreach($meeting->sessions->take(3) as $session)
                            <div class="rounded-2xl border border-slate-800/60 bg-slate-950/60 px-3 py-3">
                                <dt class="uppercase tracking-[0.25em] text-[10px] text-slate-500">{{ $session->type ?? 'SESSION' }}</dt>
                                <dd class="mt-2 text-base font-semibold text-white">{{ $session->laps_count ?? 0 }}</dd>
                                <span class="mt-1 block text-[10px] uppercase tracking-[0.3em] text-slate-500">laps</span>
                            </div>
                        @endforeach
                    </dl>
                    <footer class="mt-6 flex items-center justify-between gap-3">
                        <a href="{{ route('races.show', $meeting->id) }}" class="inline-flex items-center gap-2 rounded-full border border-red-600/60 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-red-300 transition hover:border-red-500 hover:text-white">
                            Race detail
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 4.5H19.5V10.5M19.5 4.5L4.5 19.5" />
                            </svg>
                        </a>
                        <span class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Session counts cached</span>
                    </footer>
                </article>
            @empty
                <p class="rounded-3xl border border-slate-800/70 bg-slate-900/60 p-6 text-sm text-slate-400">No meetings imported yet. Run <code class="rounded bg-slate-800 px-2 py-1 text-xs text-slate-200">php artisan f1:import &lt;season&gt;</code> to populate the database.</p>
            @endforelse
        </div>
    </section>
</x-front.layout>
