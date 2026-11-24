@php
    use App\Enums\SlotStatus;
    use App\Enums\SpaceType;
    use Illuminate\Support\Carbon;

    $eur = fn ($value) => number_format((float) $value, 0, ',', ' ').' €';
    $pct = fn ($value) => number_format((float) $value, 1, ',', ' ').' %';
    $icon = fn (string $name) => asset('assets/conferences/icons/'.$name.'.svg');

    $total = $stats['total'];
    $spaceStats = collect($stats['spaces']);
    $allSlots = \App\Models\Slot::query()
        ->with(['space:id,type,name', 'exhibitor:id,company_name'])
        ->ordered()
        ->get();

    $reservedCount = $allSlots->where('status', SlotStatus::Reserved)->count();
    $paidCount = $allSlots->where('status', SlotStatus::Paid)->count();
    $optionCount = $allSlots->where('status', SlotStatus::Option)->count();
    $availableCount = $allSlots->where('status', SlotStatus::Available)->count();
    $takenCount = $reservedCount + $paidCount + $optionCount;
    $occupancyPct = $allSlots->count() > 0 ? round($takenCount / $allSlots->count() * 100, 1) : 0;

    $calendar = $allSlots->groupBy(fn ($slot) => $slot->date->toDateString());
    $selectedDay = request('planning_day');
    if (! $selectedDay || ! $calendar->has($selectedDay)) {
        $selectedDay = $calendar->keys()->first();
    }
    $daySlots = $calendar->get($selectedDay, collect());
    $timelineStart = 9 * 60;
    $timelineEnd = 18 * 60;
    $minutePx = 0.90;
    $timelineHeight = ($timelineEnd - $timelineStart) * $minutePx;
    $times = collect(range(9, 17))->map(fn ($hour) => sprintf('%02d:00', $hour));

    $hasFilters = filled($filters['space'] ?? null) || filled($filters['date'] ?? null)
        || filled($filters['status'] ?? null) || filled($filters['search'] ?? null);
    $statusStyle = [
        'available' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'reserved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'option' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'paid' => 'bg-violet-50 text-violet-700 ring-violet-200',
    ];

    // Tableau : les réservations et les options ; on peut afficher les disponibles via le filtre statut.
    $showAvailable = ($filters['status'] ?? null) === SlotStatus::Available->value;
    $reservationRows = $slots->filter(fn ($slot) => $showAvailable || $slot->status !== SlotStatus::Available)->values();
    $perPage = 10;
    $currentPage = max(1, min((int) request('page', 1), max(1, (int) ceil($reservationRows->count() / $perPage))));
    $pageRows = $reservationRows->forPage($currentPage, $perPage);
    $pageCount = max(1, (int) ceil($reservationRows->count() / $perPage));
    $currentQuery = request()->except('page');
    $dayQuery = request()->except(['planning_day', 'page']);

    $cards = [
        ['label' => 'Total créneaux', 'value' => $allSlots->count(), 'icon' => 'calendar-days', 'color' => 'text-blue-700', 'tint' => 'bg-blue-50'],
        ['label' => 'Réservés', 'value' => $reservedCount, 'icon' => 'calendar-check', 'color' => 'text-emerald-700', 'tint' => 'bg-emerald-50'],
        ['label' => 'Disponibles', 'value' => $availableCount, 'icon' => 'calendar-check', 'color' => 'text-emerald-700', 'tint' => 'bg-emerald-50'],
        ['label' => 'En option', 'value' => $optionCount, 'icon' => 'hourglass', 'color' => 'text-orange-600', 'tint' => 'bg-orange-50'],
        ['label' => 'Payés', 'value' => $paidCount, 'icon' => 'check-circle', 'color' => 'text-violet-700', 'tint' => 'bg-violet-50'],
        ['label' => 'CA réservé HT', 'value' => $eur($total['revenue']), 'icon' => 'coins', 'color' => 'text-blue-700', 'tint' => 'bg-blue-50'],
        ['label' => 'CA encaissé HT', 'value' => $eur($total['collected']), 'icon' => 'wallet', 'color' => 'text-emerald-700', 'tint' => 'bg-emerald-50'],
    ];
@endphp

<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">
    <div class="min-w-0 space-y-5">
        {{-- Fil d'Ariane et en-tête --}}
        <nav class="flex items-center gap-2 text-xs text-slate-500" aria-label="Fil d'Ariane">
            <a href="{{ route('admin.index') }}" class="hover:text-blue-700">Accueil</a>
            <span aria-hidden="true">›</span>
            <span class="font-bold text-[#0F2347]">Conférences &amp; Lounge</span>
        </nav>

        <header class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex min-w-0 items-start gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-blue-50">
                    <img src="{{ $icon('presentation') }}" alt="" class="size-7" aria-hidden="true">
                </span>
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">Conférences &amp; Lounge des Technologies</h1>
                    <p class="mt-1 text-sm text-slate-500">Suivi, planning et reporting des réservations · REFRIGAIR EXPO 2027</p>
                    <p class="mt-1 flex items-center gap-2 text-xs font-semibold text-blue-700">
                        <img src="{{ $icon('map-pin') }}" alt="" class="size-3.5" aria-hidden="true"> OFEC Casablanca · 2–4 février 2027
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="#planning" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-bold text-[#0F2347] transition hover:bg-blue-50">
                    <img src="{{ $icon('calendar') }}" class="size-4" alt="" aria-hidden="true"> Planning
                </a>
                <a href="{{ route('admin.slots.export.pdf', 'fr') }}" class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-white px-3.5 py-2.5 text-sm font-bold text-blue-700 transition hover:bg-blue-50">
                    <img src="{{ $icon('file-pdf') }}" class="size-4" alt="" aria-hidden="true"> Exporter PDF
                </a>
                <a href="{{ route('admin.slots.export.csv') }}" class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-white px-3.5 py-2.5 text-sm font-bold text-blue-700 transition hover:bg-blue-50">
                    <img src="{{ $icon('file-csv') }}" class="size-4" alt="" aria-hidden="true"> Exporter CSV
                </a>
            </div>
        </header>

        {{-- 7 indicateurs : toujours calculés depuis la base de données --}}
        <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7" aria-label="Indicateurs">
            @foreach ($cards as $card)
                <article class="min-w-0 rounded-2xl border border-blue-100 bg-white px-3 py-3.5 shadow-sm">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $card['tint'] }}">
                            <img src="{{ $icon($card['icon']) }}" alt="" aria-hidden="true" class="size-6">
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold leading-4 text-slate-600">{{ $card['label'] }}</p>
                            <p class="mt-1 break-words text-xl font-extrabold tracking-tight tabular-nums {{ $card['color'] }}">{{ $card['value'] }}</p>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- Occupation par espace et anneau global --}}
        <section class="grid gap-4 xl:grid-cols-[1.45fr_0.85fr]">
            <article class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="text-lg font-extrabold text-[#0F2347]">Répartition par espace</h2>
                <div class="mt-4 space-y-4">
                    @foreach ($spaceStats as $space)
                        @php
                            $conference = str_contains(mb_strtolower($space['label']), 'conférence');
                            $ratio = $space['total'] > 0 ? round(100 * $space['sold'] / $space['total'], 1) : 0;
                            $image = $conference ? 'salle-conference.png' : 'lounge-technologies.png';
                        @endphp
                        <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 last:border-0 last:pb-0 sm:flex-row sm:items-center">
                            <img src="{{ asset('assets/conferences/images/'.$image) }}" alt="{{ $space['label'] }}" class="h-20 w-full rounded-xl object-cover sm:w-32" loading="lazy">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <h3 class="font-extrabold text-[#0F2347]">{{ $space['label'] }}</h3>
                                        <p class="text-xs text-slate-500">{{ $conference ? '1 heure · 1 500 € HT' : '2 heures · 1 000 € HT' }} / créneau</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-extrabold text-[#0F2347]">{{ $space['sold'] }} / {{ $space['total'] }}</p>
                                        <p class="text-xs text-slate-500">{{ $pct($ratio) }}</p>
                                    </div>
                                </div>
                                <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-label="{{ $space['label'] }}" aria-valuenow="{{ $space['sold'] }}" aria-valuemin="0" aria-valuemax="{{ $space['total'] }}">
                                    <div class="h-full rounded-full {{ $conference ? 'bg-[#2563EB]' : 'bg-[#0D9488]' }}" style="width: {{ $ratio }}%"></div>
                                </div>
                                <p class="mt-2 text-right text-sm font-extrabold {{ $conference ? 'text-blue-700' : 'text-teal-700' }}">{{ $eur($space['revenue']) }} HT</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="text-lg font-extrabold text-[#0F2347]">Occupation des créneaux</h2>
                <div class="mt-5 flex flex-col items-center gap-5 sm:flex-row xl:flex-col 2xl:flex-row">
                    <div class="grid size-44 shrink-0 place-items-center rounded-full" style="background: conic-gradient(#2563EB {{ $occupancyPct }}%, #CBD5E1 0)">
                        <div class="grid size-32 place-content-center rounded-full bg-white text-center">
                            <p class="text-2xl font-extrabold text-[#0F2347]">{{ $takenCount }} / {{ $allSlots->count() }}</p>
                            <p class="text-xs font-semibold text-slate-600">Créneaux pris</p>
                        </div>
                    </div>
                    <dl class="w-full space-y-2.5 text-sm">
                        @foreach ([
                            ['label' => 'Réservés', 'n' => $reservedCount, 'color' => 'bg-blue-600'],
                            ['label' => 'Disponibles', 'n' => $availableCount, 'color' => 'bg-slate-300'],
                            ['label' => 'En option', 'n' => $optionCount, 'color' => 'bg-orange-500'],
                            ['label' => 'Payés', 'n' => $paidCount, 'color' => 'bg-violet-600'],
                        ] as $item)
                            <div class="flex items-center gap-2">
                                <span class="size-2.5 shrink-0 rounded-full {{ $item['color'] }}"></span>
                                <dt class="flex-1 text-slate-600">{{ $item['label'] }} ({{ $item['n'] }})</dt>
                                <dd class="font-bold tabular-nums text-[#0F2347]">{{ $allSlots->count() ? $pct($item['n'] / $allSlots->count() * 100) : '0 %' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </article>
        </section>

        {{-- Réservations (gauche) + Planning global (droite) --}}
        <section class="grid items-start gap-4 2xl:grid-cols-[minmax(0,1.65fr)_minmax(340px,0.85fr)]">
            <article class="min-w-0 rounded-2xl border border-blue-100 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="reservations-title">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="reservations-title" class="flex items-center gap-2 text-lg font-extrabold text-[#0F2347]">
                        <img src="{{ $icon('calendar-check') }}" alt="" class="size-5" aria-hidden="true">
                        Réservations ({{ $reservationRows->count() }})
                    </h2>
                    <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">Consultation uniquement</span>
                </div>

                <form method="GET" action="{{ route('admin.slots.index') }}" class="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                    @if ($selectedDay)<input type="hidden" name="planning_day" value="{{ $selectedDay }}">@endif
                    <label class="sr-only" for="admin-slot-space">Espace</label>
                    <select id="admin-slot-space" name="space" class="w-full rounded-xl border-slate-300 text-xs">
                        <option value="">Tous les espaces</option>
                        @foreach ($spaceTypes as $type)
                            <option value="{{ $type->value }}" @selected(($filters['space'] ?? '') === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    <label class="sr-only" for="admin-slot-date">Date</label>
                    <select id="admin-slot-date" name="date" class="w-full rounded-xl border-slate-300 text-xs">
                        <option value="">Toutes les dates</option>
                        @foreach ($days as $day)
                            <option value="{{ $day->toDateString() }}" @selected(($filters['date'] ?? '') === $day->toDateString())>{{ $day->locale('fr')->isoFormat('ddd D MMM YYYY') }}</option>
                        @endforeach
                    </select>
                    <label class="sr-only" for="admin-slot-status">Statut</label>
                    <select id="admin-slot-status" name="status" class="w-full rounded-xl border-slate-300 text-xs">
                        <option value="">Tous les statuts</option>
                        <option value="taken" @selected(($filters['status'] ?? '') === 'taken')>Pris (tous)</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    <label class="sr-only" for="admin-slot-search">Recherche</label>
                    <input id="admin-slot-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Société, code, sujet…" class="w-full rounded-xl border-slate-300 text-xs">
                    <div class="flex gap-2 sm:col-span-2 xl:col-span-4">
                        <button type="submit" class="rounded-lg bg-[#2563EB] px-4 py-2 text-xs font-bold text-white hover:bg-blue-700">Filtrer</button>
                        @if ($hasFilters)
                            <a href="{{ route('admin.slots.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">Réinitialiser</a>
                        @endif
                    </div>
                </form>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[790px] text-left text-xs">
                        <thead class="bg-slate-50 font-bold text-slate-600">
                            <tr>
                                <th class="rounded-l-lg px-3 py-3">Code</th><th class="px-3 py-3">Espace</th>
                                <th class="px-3 py-3">Date</th><th class="px-3 py-3">Horaire</th>
                                <th class="px-3 py-3">Société</th><th class="px-3 py-3">Statut</th>
                                <th class="px-3 py-3 text-right">Prix HT</th><th class="rounded-r-lg px-3 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($pageRows as $slot)
                                <tr class="hover:bg-blue-50/30">
                                    <td class="px-3 py-3 font-extrabold text-[#0F2347]">{{ $slot->code }}</td>
                                    <td class="px-3 py-3 text-slate-600">{{ $slot->space->type === SpaceType::Conference ? 'Conférence' : 'Lounge' }}</td>
                                    <td class="whitespace-nowrap px-3 py-3 text-slate-600">{{ $slot->date->locale('fr')->isoFormat('ddd D MMM YYYY') }}</td>
                                    <td class="whitespace-nowrap px-3 py-3 font-semibold text-slate-700">{{ $slot->time_range }}</td>
                                    <td class="max-w-44 truncate px-3 py-3 font-bold text-[#0F2347]" title="{{ $slot->holder_name }}">{{ $slot->holder_name ?? '—' }}</td>
                                    <td class="px-3 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold ring-1 {{ $statusStyle[$slot->status->value] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }}">{{ $slot->status->label() }}</span></td>
                                    <td class="whitespace-nowrap px-3 py-3 text-right font-semibold text-slate-700">{{ $eur($slot->price_excl_tax) }}</td>
                                    <td class="px-3 py-3 text-center">
                                        <details class="relative inline-block text-left">
                                            <summary class="inline-flex cursor-pointer list-none items-center gap-1 rounded-lg px-2 py-1 text-blue-700 hover:bg-blue-50" aria-label="Consulter {{ $slot->code }}">
                                                <img src="{{ $icon('eye') }}" alt="" class="size-4" aria-hidden="true">
                                                <span class="text-xs font-bold">Voir</span>
                                            </summary>
                                            <div class="absolute right-0 z-20 w-64 rounded-xl border border-slate-200 bg-white p-3 text-left text-xs shadow-xl">
                                                <p class="mb-2 font-extrabold text-[#0F2347]">Créneau {{ $slot->code }}</p>
                                                <p class="mb-1 text-slate-600">{{ $slot->title ?: 'Aucun sujet renseigné' }}</p>
                                                @if ($slot->speakers)<p class="mb-1 text-slate-500">Intervenants : {{ $slot->speakers }}</p>@endif
                                                @if ($slot->summary)<p class="mb-2 text-slate-500">{{ $slot->summary }}</p>@endif
                                                @if ($slot->support_path)
                                                    <a class="font-bold text-blue-700 underline" href="{{ route('admin.slots.support', $slot) }}">Télécharger le support</a>
                                                @elseif ($slot->status->isSold())
                                                    <p class="text-amber-700">Support en attente</p>
                                                @endif
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-10 text-center text-slate-500">Aucun créneau à afficher avec ces filtres.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3 text-xs text-slate-500">
                    <span>{{ $reservationRows->isEmpty() ? 0 : (($currentPage - 1) * $perPage + 1) }}–{{ min($currentPage * $perPage, $reservationRows->count()) }} sur {{ $reservationRows->count() }} résultat(s)</span>
                    <div class="flex items-center gap-2">
                        <span class="rounded-lg border border-slate-200 px-2.5 py-1.5">{{ $perPage }} par page</span>
                        @if ($currentPage > 1)
                            <a href="{{ route('admin.slots.index', $currentQuery + ['page' => $currentPage - 1]) }}#reservations-title" class="rounded-lg border border-slate-200 px-2.5 py-1.5 font-bold hover:bg-slate-50">‹</a>
                        @endif
                        <span class="rounded-lg bg-[#2563EB] px-2.5 py-1.5 font-extrabold text-white">{{ $currentPage }} / {{ $pageCount }}</span>
                        @if ($currentPage < $pageCount)
                            <a href="{{ route('admin.slots.index', $currentQuery + ['page' => $currentPage + 1]) }}#reservations-title" class="rounded-lg border border-slate-200 px-2.5 py-1.5 font-bold hover:bg-slate-50">›</a>
                        @endif
                    </div>
                </div>
            </article>

            <article id="planning" class="min-w-0 rounded-2xl border border-blue-100 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="planning-title">
                <h2 id="planning-title" class="flex items-center gap-2 text-lg font-extrabold text-[#0F2347]">
                    <img src="{{ $icon('calendar-days') }}" alt="" class="size-5" aria-hidden="true">
                    Planning global
                </h2>
                <p class="mt-1 text-xs text-slate-500">Horaires exacts enregistrés en base, lecture seule</p>
                <div class="mt-4 flex flex-wrap gap-1.5">
                    @forelse ($calendar as $date => $daily)
                        <a href="{{ route('admin.slots.index', $dayQuery + ['planning_day' => $date]) }}#planning"
                           @if ($date === $selectedDay) aria-current="date" @endif
                           class="rounded-lg border px-3 py-2 text-xs font-bold {{ $date === $selectedDay ? 'border-blue-600 bg-[#2563EB] text-white' : 'border-slate-200 bg-white text-slate-600 hover:bg-blue-50' }}">
                            {{ $daily->first()->date->locale('fr')->isoFormat('ddd D MMM') }}
                        </a>
                    @empty
                        <span class="text-xs text-slate-400">Aucun jour configuré</span>
                    @endforelse
                </div>

                @if ($selectedDay)
                    <div class="mt-4 grid grid-cols-[43px_minmax(0,1fr)_minmax(0,1fr)] gap-1.5 text-[10px] sm:text-xs">
                        <div class="rounded-lg bg-slate-100 py-3 text-center font-extrabold text-[#0F2347]">Heure</div>
                        <div class="rounded-lg bg-blue-50 px-1 py-2 text-center font-extrabold text-blue-900">Salle de conférence <span class="block font-medium">1 h · 1 500 €</span></div>
                        <div class="rounded-lg bg-teal-50 px-1 py-2 text-center font-extrabold text-teal-900">Lounge des Technologies <span class="block font-medium">2 h · 1 000 €</span></div>

                        <div class="relative" style="height: {{ $timelineHeight }}px">
                            @foreach ($times as $time)
                                @php $hour = (int) substr($time, 0, 2); @endphp
                                <span class="absolute right-1 -translate-y-1/2 font-semibold text-slate-600" style="top: {{ ($hour * 60 - $timelineStart) * $minutePx }}px">{{ $time }}</span>
                            @endforeach
                        </div>

                        @foreach ([SpaceType::Conference, SpaceType::Lounge] as $spaceType)
                            <div class="relative overflow-hidden rounded-lg border border-slate-200 bg-slate-50/60" style="height: {{ $timelineHeight }}px">
                                @foreach ($times as $time)
                                    @php $hour = (int) substr($time, 0, 2); @endphp
                                    <div class="pointer-events-none absolute inset-x-0 border-t border-dashed border-slate-300" style="top: {{ ($hour * 60 - $timelineStart) * $minutePx }}px"></div>
                                @endforeach
                                @foreach ($daySlots->filter(fn ($slot) => $slot->space->type === $spaceType) as $planningSlot)
                                    @php
                                        $startParts = array_map('intval', explode(':', $planningSlot->start_label));
                                        $endParts = array_map('intval', explode(':', $planningSlot->end_label));
                                        $startMinute = $startParts[0] * 60 + $startParts[1];
                                        $endMinute = $endParts[0] * 60 + $endParts[1];
                                        $top = max(0, ($startMinute - $timelineStart) * $minutePx);
                                        $height = max(22, ($endMinute - $startMinute) * $minutePx);
                                        $tileColor = match ($planningSlot->status) {
                                            SlotStatus::Available => 'border-emerald-200 bg-emerald-50/90 text-emerald-800',
                                            SlotStatus::Option => 'border-amber-300 bg-amber-50 text-amber-900',
                                            SlotStatus::Paid => 'border-violet-300 bg-violet-50 text-violet-900',
                                            default => 'border-blue-300 bg-blue-50 text-blue-900',
                                        };
                                    @endphp
                                    <div title="{{ $planningSlot->code }} · {{ $planningSlot->time_range }} · {{ $planningSlot->holder_name ?: $planningSlot->status->label() }}"
                                         class="absolute inset-x-0.5 overflow-hidden rounded-lg border px-1.5 py-1 text-[10px] leading-tight {{ $tileColor }}"
                                         style="top: {{ $top }}px; height: {{ $height }}px">
                                        <p class="font-extrabold">{{ $planningSlot->code }} · {{ $planningSlot->time_range }}</p>
                                        <p class="mt-0.5 truncate font-semibold">{{ $planningSlot->holder_name ?: $planningSlot->status->label() }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 flex flex-wrap gap-3 text-[11px] font-semibold text-slate-600">
                        <span>🟢 Disponible</span><span>🔵 Réservé</span><span>🟠 Option</span><span>🟣 Payé</span>
                    </div>
                @endif
            </article>
        </section>

        {{-- Synthèse des titulaires, conservée du code fourni --}}
        <section class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="holders-title">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="holders-title" class="text-lg font-extrabold text-[#0F2347]">Répartition par société</h2>
                <span class="text-xs text-slate-500">Récapitulatif des réservations, options et paiements</span>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[680px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs font-bold text-slate-500">
                        <tr><th class="py-3">Société</th><th class="py-3">Créneaux</th><th class="py-3">Conférence</th><th class="py-3">Lounge</th><th class="py-3 text-right">CA réservé HT</th><th class="py-3 text-right">Encaissé HT</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($holders as $holder)
                            <tr>
                                <td class="py-3 font-bold text-[#0F2347]">{{ $holder['name'] ?: '—' }}</td>
                                <td class="py-3 text-xs text-slate-600">{{ implode(', ', $holder['codes']) }}</td>
                                <td class="py-3 text-slate-700">{{ $holder['conference'] }}</td>
                                <td class="py-3 text-slate-700">{{ $holder['lounge'] }}</td>
                                <td class="py-3 text-right font-bold text-slate-800">{{ $eur($holder['revenue']) }}</td>
                                <td class="py-3 text-right font-bold text-emerald-700">{{ $eur($holder['collected']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 text-center text-slate-500">Aucune réservation pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-xs text-slate-500">{{ $pendingRequestsCount }} demande(s) en attente de traitement par les commerciaux.</p>
        </section>
    </div>
</x-admin.layout>
