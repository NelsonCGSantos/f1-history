<x-front.layout :title="$meeting->name">
    <section class="mb-10 rounded-3xl border border-slate-800/80 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-8 shadow-xl shadow-slate-950/40">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-slate-500">Grand Prix report</p>
                <h1 class="mt-3 text-3xl font-semibold text-white sm:text-4xl">{{ $meeting->name }}</h1>
                <p class="mt-4 max-w-2xl text-sm text-slate-400 sm:text-base">
                    Classification and tyre stint data are preloaded once per race, then cached for subsequent visits. Only drill into laps and telemetry when you truly need it.
                </p>
            </div>
            <dl class="grid grid-cols-2 gap-4 text-sm text-slate-300">
                <div class="rounded-2xl border border-slate-800/70 bg-slate-950/60 px-4 py-5 text-right">
                    <dt class="text-xs uppercase tracking-[0.3em] text-slate-500">Country</dt>
                    <dd class="mt-2 text-lg font-semibold text-white">{{ $meeting->country ?? 'Unknown' }}</dd>
                </div>
                <div class="rounded-2xl border border-slate-800/70 bg-slate-950/60 px-4 py-5 text-right">
                    <dt class="text-xs uppercase tracking-[0.3em] text-slate-500">Season</dt>
                    <dd class="mt-2 text-lg font-semibold text-white">{{ $meeting->season_year }}</dd>
                </div>
            </dl>
        </div>
        <div class="mt-8 grid grid-cols-1 gap-4 text-sm text-slate-300 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-800/70 bg-slate-950/60 px-4 py-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Sessions cached</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ $meeting->sessions->count() }}</p>
            </div>
            <div class="rounded-2xl border border-slate-800/70 bg-slate-950/60 px-4 py-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Recorded laps</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ $meeting->sessions->sum('laps_count') }}</p>
            </div>
            <div class="rounded-2xl border border-slate-800/70 bg-slate-950/60 px-4 py-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Stints captured</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ $meeting->sessions->sum('stints_count') }}</p>
            </div>
        </div>
    </section>

    <section class="mb-10 grid gap-6 lg:grid-cols-[1.15fr,0.85fr]">
        <div class="rounded-3xl border border-slate-800/70 bg-slate-900/60">
            <header class="flex items-center justify-between border-b border-slate-800/60 px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-white">Race classification</h2>
                    <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Latest timing snapshot</p>
                </div>
                @if($raceSession)
                    <span class="rounded-full border border-slate-700/60 px-3 py-1 text-[10px] uppercase tracking-[0.3em] text-slate-400">
                        {{ optional($raceSession->start_time)->format('d M Y') }}
                    </span>
                @endif
            </header>
            @if($classification->isEmpty())
                <p class="px-6 py-5 text-sm text-slate-400">No classification data yet. Import positions for this session to populate the table.</p>
            @else
                <div class="divide-y divide-slate-800/60">
                    @foreach($classification as $row)
                        <div class="flex items-center justify-between px-6 py-4 text-sm text-slate-200">
                            <div class="flex items-center gap-4">
                                <span class="text-2xl font-semibold text-white">P{{ $row['position'] }}</span>
                                <div>
                                    <p class="text-base font-semibold text-white">{{ $row['driver']->name }}</p>
                                    <p class="text-xs uppercase tracking-[0.3em] text-slate-500">
                                        #{{ $row['driver']->driver_number }} &middot; {{ $row['driver']->team_name }} &middot; {{ $row['driver']->abbreviation }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right text-xs text-slate-400">
                                <p>Recorded {{ $row['recorded_at'] ? \Carbon\Carbon::parse($row['recorded_at'])->timezone(config('app.timezone'))->format('d M Y H:i') : 'TBC' }}</p>
                                <a href="{{ route('drivers.show', $row['driver']->driver_number) }}" class="inline-flex items-center gap-1 rounded-full border border-red-600/60 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.3em] text-red-300 transition hover:border-red-500 hover:text-white">
                                    Driver profile
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <aside class="rounded-3xl border border-slate-800/70 bg-slate-900/60">
            <header class="border-b border-slate-800/60 px-6 py-4">
                <h2 class="text-lg font-semibold text-white">Weekend sessions</h2>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Counts cached per session</p>
            </header>
            <div class="divide-y divide-slate-800/60">
                @foreach($meeting->sessions as $session)
                    <div class="flex items-center justify-between px-6 py-4 text-sm text-slate-200">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">{{ $session->type ?? 'SESSION' }}</p>
                            <p class="text-sm text-slate-300">{{ optional($session->start_time)->format('d M Y H:i') ?? 'TBC' }}</p>
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
                @endforeach
            </div>
        </aside>
    </section>

    <section class="mb-10 rounded-3xl border border-slate-800/70 bg-slate-900/60">
        <header class="flex flex-col gap-2 border-b border-slate-800/60 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-white">Tyre strategy</h2>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Grouped stints by driver</p>
            </div>
            <span class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Grouped client-side from cached query</span>
        </header>
        @if($stintsByDriver->isEmpty())
            <p class="px-6 py-5 text-sm text-slate-400">No stint data recorded for this race.</p>
        @else
            <div class="grid gap-4 px-6 py-6 sm:grid-cols-2">
                @foreach($stintsByDriver as $entry)
                    <article class="rounded-2xl border border-slate-800/60 bg-slate-950/60 p-5">
                        <header class="flex justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">{{ $entry['driver']->team_name }}</p>
                                <h3 class="text-lg font-semibold text-white">{{ $entry['driver']->name }}</h3>
                            </div>
                            <span class="rounded-full border border-slate-700/60 px-3 py-1 text-[10px] uppercase tracking-[0.3em] text-slate-400">
                                #{{ $entry['driver']->driver_number }}
                            </span>
                        </header>
                        <div class="mt-4 space-y-3 text-sm text-slate-300">
                            @foreach($entry['stints'] as $stint)
                                <div class="flex items-center justify-between rounded-xl border border-slate-800/60 bg-slate-900/80 px-4 py-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Stint {{ $stint['stint_number'] }}</p>
                                        <p class="text-sm text-slate-200">Laps {{ $stint['start_lap'] }} — {{ $stint['end_lap'] }}</p>
                                    </div>
                                    <div class="text-right text-xs text-slate-400">
                                        <p class="font-semibold text-white">{{ $stint['tire_compound'] }}</p>
                                        <p>Tyre age {{ $stint['tyre_age_at_start'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</x-front.layout>

