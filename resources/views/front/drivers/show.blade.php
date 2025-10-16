@php
    $recentResults = collect($driver->recent_results ?? []);
    $seasonYears = collect($driver->season_years ?? []);
@endphp

<x-front.layout :title="$driver->name">
    <section class="mb-10 rounded-3xl border border-slate-800/80 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-8 shadow-xl shadow-slate-950/40">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-slate-500">Driver profile</p>
                <h1 class="mt-3 text-3xl font-semibold text-white sm:text-4xl">{{ $driver->name }}</h1>
                <p class="mt-4 max-w-2xl text-sm text-slate-400 sm:text-base">
                    Profile data hits cached aggregates for lap counts and tyre stints, so you can move between drivers without waiting on expensive joins.
                </p>
            </div>
            <dl class="grid grid-cols-2 gap-4 text-sm text-slate-300 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-800/70 bg-slate-950/60 px-4 py-5 text-right">
                    <dt class="text-xs uppercase tracking-[0.3em] text-slate-500">Number</dt>
                    <dd class="mt-2 text-lg font-semibold text-white">#{{ $driver->driver_number }}</dd>
                </div>
                <div class="rounded-2xl border border-slate-800/70 bg-slate-950/60 px-4 py-5 text-right">
                    <dt class="text-xs uppercase tracking-[0.3em] text-slate-500">Team</dt>
                    <dd class="mt-2 text-lg font-semibold text-white">{{ $driver->team_name ?? 'Unknown' }}</dd>
                </div>
                <div class="rounded-2xl border border-slate-800/70 bg-slate-950/60 px-4 py-5 text-right">
                    <dt class="text-xs uppercase tracking-[0.3em] text-slate-500">Nationality</dt>
                    <dd class="mt-2 text-lg font-semibold text-white">{{ $driver->nationality ?? '—' }}</dd>
                </div>
            </dl>
        </div>
        <div class="mt-8 grid grid-cols-1 gap-4 text-sm text-slate-300 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-800/70 bg-slate-950/60 px-4 py-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Recorded laps</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ number_format((int) $driver->laps_count) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-800/70 bg-slate-950/60 px-4 py-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Tyre stints</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ number_format((int) $driver->stints_count) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-800/70 bg-slate-950/60 px-4 py-4">
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Seasons raced</p>
                <p class="mt-2 text-2xl font-semibold text-white">{{ $seasonYears->count() }}</p>
            </div>
        </div>
    </section>

    <section class="mb-10 grid gap-6 lg:grid-cols-[1fr,0.8fr]">
        <div class="rounded-3xl border border-slate-800/70 bg-slate-900/60">
            <header class="flex items-center justify-between border-b border-slate-800/60 px-6 py-4">
                <h2 class="text-lg font-semibold text-white">Recent results</h2>
                <span class="text-[10px] uppercase tracking-[0.3em] text-slate-500">Latest {{ $recentResults->count() }} races</span>
            </header>
            @if($recentResults->isEmpty())
                <p class="px-6 py-5 text-sm text-slate-400">No race classification data found.</p>
            @else
                <div class="divide-y divide-slate-800/60">
                    @foreach($recentResults as $result)
                        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 text-sm text-slate-200">
                            <div>
                                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">{{ $result['season_year'] }} &middot; {{ $result['meeting_name'] }}</p>
                                <p class="text-base font-semibold text-white">P{{ $result['position'] }}</p>
                            </div>
                            <div class="text-right text-xs text-slate-400">
                                <p>{{ $result['recorded_at'] ? \Carbon\Carbon::parse($result['recorded_at'])->timezone(config('app.timezone'))->format('d M Y H:i') : 'TBC' }}</p>
                                <a href="{{ route('races.show', $result['meeting_id']) }}" class="inline-flex items-center gap-1 rounded-full border border-red-600/60 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.3em] text-red-300 transition hover:border-red-500 hover:text-white">
                                    Race detail
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <aside class="rounded-3xl border border-slate-800/70 bg-slate-900/60">
            <header class="border-b border-slate-800/60 px-6 py-4">
                <h2 class="text-lg font-semibold text-white">Season history</h2>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Distinct seasons with lap data</p>
            </header>
            <ul class="divide-y divide-slate-800/60">
                @forelse($seasonYears as $year)
                    <li class="flex items-center justify-between px-6 py-3 text-sm text-slate-200">
                        <span class="font-medium text-white">{{ $year }}</span>
                        <a href="{{ route('seasons.show', $year) }}" class="inline-flex items-center gap-1 rounded-full border border-slate-700/60 px-2.5 py-1 text-[10px] uppercase tracking-[0.3em] text-slate-400 transition hover:border-red-500 hover:text-white">
                            Season
                        </a>
                    </li>
                @empty
                    <li class="px-6 py-4 text-sm text-slate-400">No seasons recorded.</li>
                @endforelse
            </ul>
        </aside>
    </section>

    <section class="rounded-3xl border border-slate-800/70 bg-slate-900/60 p-6">
        <h2 class="text-lg font-semibold text-white">API endpoints behind this view</h2>
        <p class="mt-2 text-sm text-slate-400">
            Driver pages reuse <code class="rounded bg-slate-800 px-2 py-1 text-xs text-slate-200">/api/v1/drivers/{{ $driver->driver_number }}</code> so the browser UI stays in sync with programmatic integrations.
        </p>
        <p class="mt-3 text-xs uppercase tracking-[0.3em] text-slate-500">All expensive pieces are cached for 10 minutes.</p>
    </section>
</x-front.layout>
