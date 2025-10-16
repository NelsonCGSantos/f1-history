<x-front.layout :title="'Season '.$year">
    <section class="mb-10 rounded-3xl border border-slate-800/80 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-8 shadow-xl shadow-slate-950/40">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-slate-500">Season overview</p>
                <h1 class="mt-3 text-3xl font-semibold text-white sm:text-4xl">Formula 1 {{ $year }}</h1>
                <p class="mt-4 max-w-2xl text-sm text-slate-400 sm:text-base">
                    Cached summaries and pre-counted session data keep this page quick to load. Dive into each Grand Prix to examine classifications and stint usage without waiting on fresh queries.
                </p>
            </div>
            <div class="flex flex-wrap gap-4 text-sm text-slate-300 sm:justify-end">
                <a href="{{ route('seasons.standings', $year) }}" class="inline-flex items-center gap-2 rounded-full border border-red-600/60 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-red-300 transition hover:border-red-500 hover:text-white">
                    View standings
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 4.5H19.5V10.5M19.5 4.5L4.5 19.5" />
                    </svg>
                </a>
                <a href="#calendar" class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.3em] text-slate-400 hover:text-slate-200">Skip to calendar</a>
            </div>
        </div>
        <dl class="mt-8 grid grid-cols-2 gap-4 text-center sm:grid-cols-4">
            <div class="rounded-2xl border border-slate-800/70 bg-slate-950/60 px-4 py-6">
                <dt class="text-xs uppercase tracking-[0.3em] text-slate-500">Grands Prix</dt>
                <dd class="mt-3 text-3xl font-semibold text-white">{{ number_format($meetingCount) }}</dd>
            </div>
            <div class="rounded-2xl border border-slate-800/70 bg-slate-950/60 px-4 py-6">
                <dt class="text-xs uppercase tracking-[0.3em] text-slate-500">Recorded laps</dt>
                <dd class="mt-3 text-3xl font-semibold text-white">{{ number_format($totalLaps) }}</dd>
            </div>
            <div class="rounded-2xl border border-slate-800/70 bg-slate-950/60 px-4 py-6">
                <dt class="text-xs uppercase tracking-[0.3em] text-slate-500">Classification points</dt>
                <dd class="mt-3 text-3xl font-semibold text-white">{{ number_format($totalPositions) }}</dd>
            </div>
            <div class="rounded-2xl border border-slate-800/70 bg-slate-950/60 px-4 py-6">
                <dt class="text-xs uppercase tracking-[0.3em] text-slate-500">Tyre stints</dt>
                <dd class="mt-3 text-3xl font-semibold text-white">{{ number_format($totalStints) }}</dd>
            </div>
        </dl>
    </section>

    <section id="calendar" class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-white">Race calendar</h2>
                <p class="text-sm text-slate-400">Session counts are pre-calculated server-side to keep this calendar responsive.</p>
            </div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Click a round to expand session details</p>
        </div>

        <div class="space-y-4">
            @foreach($meetings as $index => $meeting)
                <article x-data="{ open: false }" class="rounded-3xl border border-slate-800/70 bg-slate-900/60 p-5 transition hover:border-red-600/40">
                    <header @click="open = !open" class="flex cursor-pointer items-center justify-between gap-4">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.35em] text-slate-500">Round {{ $index + 1 }}</p>
                            <h3 class="mt-1 text-lg font-semibold text-white">{{ $meeting->name }}</h3>
                            <p class="text-xs text-slate-400">
                                {{ $meeting->location ?? 'Unknown circuit' }} &bull;
                                {{ optional($meeting->start_date)->format('d M') }} - {{ optional($meeting->end_date)->format('d M Y') }}
                            </p>
                        </div>
                        <dl class="grid grid-cols-3 gap-3 whitespace-nowrap text-center text-xs text-slate-300">
                            <div class="rounded-2xl border border-slate-800/60 bg-slate-950/60 px-4 py-3">
                                <dt class="tracking-[0.3em] text-[10px] uppercase text-slate-500">Laps</dt>
                                <dd class="mt-2 text-lg font-semibold text-white">{{ $meeting->sessions->sum('laps_count') }}</dd>
                            </div>
                            <div class="rounded-2xl border border-slate-800/60 bg-slate-950/60 px-4 py-3">
                                <dt class="tracking-[0.3em] text-[10px] uppercase text-slate-500">Classifications</dt>
                                <dd class="mt-2 text-lg font-semibold text-white">{{ $meeting->sessions->sum('positions_count') }}</dd>
                            </div>
                            <div class="rounded-2xl border border-slate-800/60 bg-slate-950/60 px-4 py-3">
                                <dt class="tracking-[0.3em] text-[10px] uppercase text-slate-500">Stints</dt>
                                <dd class="mt-2 text-lg font-semibold text-white">{{ $meeting->sessions->sum('stints_count') }}</dd>
                            </div>
                        </dl>
                    </header>

                    <div x-cloak x-show="open" x-transition class="mt-5 border-t border-slate-800/60 pt-5">
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[2fr,1fr]">
                            <div class="space-y-3">
                                @foreach($meeting->sessions as $session)
                                    <div class="rounded-2xl border border-slate-800/60 bg-slate-950/60 px-4 py-4">
                                        <div class="flex flex-wrap items-baseline justify-between gap-3">
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">{{ $session->type ?? 'SESSION' }}</p>
                                                <p class="text-sm text-slate-300">
                                                    {{ optional($session->start_time)->timezone(config('app.timezone'))->format('d M Y H:i') ?? 'TBC' }}
                                                    —
                                                    {{ optional($session->end_time)->timezone(config('app.timezone'))->format('H:i') ?? 'TBC' }}
                                                </p>
                                            </div>
                                            <dl class="flex items-center gap-5 text-xs text-slate-400">
                                                <div>
                                                    <dt class="tracking-[0.25em] text-[10px] uppercase text-slate-500">Laps</dt>
                                                    <dd class="mt-1 text-base font-semibold text-white">{{ $session->laps_count }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="tracking-[0.25em] text-[10px] uppercase text-slate-500">Positions</dt>
                                                    <dd class="mt-1 text-base font-semibold text-white">{{ $session->positions_count }}</dd>
                                                </div>
                                                <div>
                                                    <dt class="tracking-[0.25em] text-[10px] uppercase text-slate-500">Stints</dt>
                                                    <dd class="mt-1 text-base font-semibold text-white">{{ $session->stints_count }}</dd>
                                                </div>
                                            </dl>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <aside class="flex flex-col gap-3">
                                <a href="{{ route('races.show', $meeting->id) }}" class="inline-flex items-center justify-between rounded-2xl border border-red-600/60 bg-red-600/10 px-4 py-4 text-sm font-semibold text-red-200 transition hover:border-red-500 hover:bg-red-600/20 hover:text-white">
                                    Race report
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 4.5H19.5V10.5M19.5 4.5L4.5 19.5" />
                                    </svg>
                                </a>
                                <div class="rounded-2xl border border-slate-800/60 bg-slate-950/60 px-4 py-4 text-xs text-slate-400">
                                    <p class="font-medium uppercase tracking-[0.3em] text-slate-500">Why cached?</p>
                                    <p class="mt-2 leading-relaxed">
                                        Session counts are pre-computed when the page loads so you can expand multiple rounds without re-querying the database. Drill into laps on the race page only when you need them.
                                    </p>
                                </div>
                            </aside>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</x-front.layout>

