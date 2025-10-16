<x-front.layout :title="'Standings '.$year">
    <section class="mb-10 rounded-3xl border border-slate-800/80 bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-8 shadow-xl shadow-slate-950/40">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs uppercase tracking-[0.35em] text-slate-500">Drivers championship</p>
                <h1 class="mt-3 text-3xl font-semibold text-white sm:text-4xl">Standings for {{ $year }}</h1>
                <p class="mt-4 max-w-2xl text-sm text-slate-400 sm:text-base">
                    This table reuses the same cached aggregation that powers the API. Points and podium counts are grouped server-side so the page renders instantly even on large datasets.
                </p>
            </div>
            <a href="{{ route('seasons.show', $year) }}" class="inline-flex items-center gap-2 rounded-full border border-red-600/60 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-red-300 transition hover:border-red-500 hover:text-white">
                Back to season
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 19.5L4.5 13.5L10.5 7.5M19.5 19.5V7.5" />
                </svg>
            </a>
        </div>
        <div class="mt-6 flex flex-wrap gap-4 text-xs text-slate-400">
            <span class="inline-flex items-center gap-2 rounded-full border border-slate-700/60 px-3 py-1">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                Cached for 5 minutes
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-slate-700/60 px-3 py-1">
                <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                Expand rows for race-by-race history
            </span>
        </div>
    </section>

    <section class="overflow-hidden rounded-3xl border border-slate-800/70 bg-slate-900/60">
        <header class="grid grid-cols-[4rem,1fr,8rem,8rem,8rem,8rem] gap-3 border-b border-slate-800/60 px-4 py-3 text-[11px] uppercase tracking-[0.3em] text-slate-500 sm:px-6">
            <span>#</span>
            <span>Driver</span>
            <span class="text-right">Points</span>
            <span class="text-right">Wins</span>
            <span class="text-right">Podiums</span>
            <span class="text-right">Races</span>
        </header>
        <div class="divide-y divide-slate-800/60">
            @foreach($standings as $rank => $entry)
                <article x-data="{ open: false }" class="hover:bg-slate-900/80">
                    <button @click="open = !open" class="grid w-full grid-cols-[4rem,1fr,8rem,8rem,8rem,8rem] gap-3 px-4 py-4 text-left text-sm text-slate-200 transition sm:px-6">
                        <span class="font-semibold text-white">{{ $rank + 1 }}</span>
                        <span class="flex flex-col">
                            <span class="font-semibold text-white">{{ $entry['driver']->name }}</span>
                            <span class="text-xs uppercase tracking-[0.3em] text-slate-500">
                                #{{ $entry['driver']->driver_number }} &middot; {{ $entry['driver']->team_name }}
                            </span>
                        </span>
                        <span class="text-right text-lg font-semibold text-white">{{ $entry['points'] }}</span>
                        <span class="text-right">{{ $entry['wins'] }}</span>
                        <span class="text-right">{{ $entry['podiums'] }}</span>
                        <span class="text-right">{{ $entry['races'] }}</span>
                    </button>
                    <div x-cloak x-show="open" x-transition class="border-t border-slate-800/60 bg-slate-950/60 px-4 py-4 text-sm text-slate-300 sm:px-6">
                        <h3 class="text-xs uppercase tracking-[0.3em] text-slate-500">Race results</h3>
                        <div class="mt-3 grid gap-3 text-xs sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($entry['results'] as $result)
                                <div class="rounded-2xl border border-slate-800/60 bg-slate-900/80 px-4 py-3">
                                    <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">{{ $result['meeting_name'] }}</p>
                                    <p class="mt-2 text-lg font-semibold text-white">P{{ $result['position'] }}</p>
                                    <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">+{{ $result['points'] }} pts</p>
                                    <p class="mt-2 text-[11px] text-slate-400">Recorded at {{ $result['recorded_at'] ? \Carbon\Carbon::parse($result['recorded_at'])->timezone(config('app.timezone'))->format('d M Y H:i') : 'TBC' }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                            <a href="{{ route('drivers.show', $entry['driver']->driver_number) }}" class="inline-flex items-center gap-2 rounded-full border border-red-600/60 px-3 py-1 text-xs font-semibold uppercase tracking-[0.3em] text-red-300 transition hover:border-red-500 hover:text-white">
                                Driver profile
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 4.5H19.5V10.5M19.5 4.5L4.5 19.5" />
                                </svg>
                            </a>
                            <span>History cached to avoid re-running aggregation when you expand rows.</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
</x-front.layout>

