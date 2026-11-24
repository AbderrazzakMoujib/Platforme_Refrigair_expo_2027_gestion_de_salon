@php
    use App\Enums\SlotStatus;
    $money = fn ($n) => number_format((float) $n, 0, ',', ' ').' €';
    $total = $stats['total'];
    $reservedCount = $slots->filter(fn ($s) => $s->status === SlotStatus::Reserved)->count();
    $paidCount = $slots->filter(fn ($s) => $s->status === SlotStatus::Paid)->count();
    $soldSlots = $slots->filter(fn ($s) => $s->status->isTaken());
    $slotErrors = $errors->getBag('slot');
    $reopenId = (int) old('slot_id', 0);
    $days = $planning->keys()->values();
    $currentDay = $days->first();
    $firstFreeId = $slots->first(fn ($s) => $s->status === SlotStatus::Available)?->id;
    $icon = fn (string $name) => asset('assets/conferences/icons/'.$name.'.svg');
@endphp

<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercialInitials">
    <div class="space-y-6" id="conference-commercial-page">
        <nav class="flex items-center gap-2 text-xs font-medium text-slate-500" aria-label="Fil d'Ariane">
            <a href="{{ route('commercial.index') }}" class="hover:text-blue-700">Espace commercial</a>
            <span>/</span><span class="text-[#0F2347]">Conférences & Lounge</span>
        </nav>

        <header class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div class="flex items-start gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-700"><img src="{{ $icon('calendar-check') }}" alt="" class="size-6"></span>
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">Conférences & Lounge</h1>
                    <p class="mt-1 text-sm text-slate-500">Gestion commerciale des créneaux, réservations et demandes · OFEC Casablanca · 2–4 février 2027</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('commercial.slots.export.pdf', 'fr') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-bold text-slate-700 hover:border-blue-200">Programme PDF</a>
                <a href="{{ route('commercial.slots.export.csv') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-bold text-slate-700 hover:border-blue-200">Export CSV</a>
                <button type="button" data-open-first-free data-new-status="reserved" class="rounded-xl bg-[#2563EB] px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-700">+ Réserver un créneau</button>
            </div>
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($slotErrors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <strong>Modification impossible :</strong>
                <ul class="mt-1 list-disc pl-5">@foreach ($slotErrors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- KPIs : calculés uniquement depuis les créneaux enregistrés. --}}
        <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6" aria-label="Indicateurs">
            @foreach ([
                ['Total créneaux', $total['total'], 'calendar', 'text-blue-700', 'bg-blue-50'],
                ['Réservés', $reservedCount, 'calendar-check', 'text-emerald-700', 'bg-emerald-50'],
                ['Disponibles', $total['available'], 'calendar-days', 'text-blue-700', 'bg-blue-50'],
                ['Options', $total['options'], 'hourglass', 'text-amber-600', 'bg-amber-50'],
                ['CA réservé HT', $money($total['revenue']), 'wallet', 'text-violet-700', 'bg-violet-50'],
                ['CA encaissé HT', $money($total['collected']), 'coins', 'text-emerald-700', 'bg-emerald-50'],
            ] as [$label, $value, $iconName, $tone, $bg])
                <article class="flex min-w-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl {{ $bg }}"><img src="{{ $icon($iconName) }}" class="size-6" alt=""></span>
                    <div class="min-w-0"><p class="text-xs font-semibold text-slate-500">{{ $label }}</p><p class="mt-1 truncate text-xl font-extrabold tracking-tight {{ $tone }}">{{ $value }}</p></div>
                </article>
            @endforeach
        </section>

        <section class="grid gap-3 lg:grid-cols-2" aria-label="Répartition par espace">
            @foreach ($stats['spaces'] as $key => $spaceStats)
                @php
                    $isConference = $key === 'conference';
                    $pct = $spaceStats['total'] ? round($spaceStats['sold'] * 100 / $spaceStats['total']) : 0;
                @endphp
                <article class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="grid size-12 shrink-0 place-items-center rounded-full {{ $isConference ? 'bg-blue-50' : 'bg-emerald-50' }}"><img src="{{ $icon($isConference ? 'users' : 'layers') }}" class="size-6" alt=""></span>
                        <div class="min-w-0 flex-1"><h2 class="font-extrabold text-[#0F2347]">{{ $spaceStats['label'] }}</h2><p class="text-xs text-slate-500">{{ $spaceStats['sold'] }} / {{ $spaceStats['total'] }} créneaux vendus</p></div>
                        <span class="rounded-lg px-3 py-1.5 text-xs font-bold {{ $isConference ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $money($isConference ? 1500 : 1000) }} HT / créneau</span>
                    </div>
                    <div class="mt-3 flex items-center gap-3 pl-0 sm:pl-14">
                        <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full {{ $isConference ? 'bg-blue-600' : 'bg-emerald-500' }}" style="width: {{ $pct }}%"></div></div>
                        <span class="text-xs font-bold tabular-nums text-slate-700">{{ $pct }}%</span>
                    </div>
                </article>
            @endforeach
        </section>

        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_310px]">
            <div class="min-w-0 space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" aria-label="Planning global">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <h2 class="flex items-center gap-2 text-lg font-extrabold text-[#0F2347]"><img src="{{ $icon('calendar-days') }}" class="size-6" alt="">Planning global</h2>
                        <div class="flex flex-wrap gap-1.5" role="group" aria-label="Choisir la date">
                            @foreach ($days as $date)
                                <button type="button" data-day-button="{{ $date }}" aria-pressed="{{ $date === $currentDay ? 'true' : 'false' }}"
                                    class="rounded-lg px-3 py-2 text-xs font-bold transition {{ $date === $currentDay ? 'bg-[#2563EB] text-white' : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                                    {{ \Illuminate\Support\Carbon::parse($date)->locale('fr')->isoFormat('dddd D MMM YYYY') }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Horaires réels de la base de données. Cliquez sur un créneau pour consulter ou modifier sa réservation.</p>
                    @forelse ($planning as $date => $bySpace)
                        @php
                            $dailySlots = $slots->filter(fn ($s) => $s->date->toDateString() === $date);
                            $startTimes = $dailySlots->map(fn ($s) => $s->start_label)->unique()->sort()->values();
                        @endphp
                        <div data-day-panel="{{ $date }}" @if ($date !== $currentDay) hidden @endif class="mt-4 overflow-x-auto">
                            <table class="w-full min-w-[620px] border-separate border-spacing-y-1 text-left text-xs">
                                <thead><tr>
                                    <th class="w-28 rounded-l-xl bg-slate-100 p-3 font-bold text-[#0F2347]">Horaire</th>
                                    @foreach ($spaces as $space)
                                        <th class="p-3 font-extrabold text-[#0F2347] {{ $space->type->value === 'conference' ? 'bg-blue-50' : 'bg-emerald-50' }}">
                                            <span class="flex items-center gap-2"><img src="{{ $icon($space->type->value === 'conference' ? 'users' : 'layers') }}" class="size-5" alt="">{{ $space->type->label() }}</span>
                                            <span class="mt-1 block font-medium text-slate-500">{{ $space->type->value === 'conference' ? '1 h · 1 500 € HT' : '2 h · 1 000 € HT' }}</span>
                                        </th>
                                    @endforeach
                                </tr></thead>
                                <tbody>
                                    @foreach ($startTimes as $startTime)
                                        <tr>
                                            <th class="rounded-lg bg-slate-50 p-3 align-top font-extrabold tabular-nums text-[#0F2347]">{{ $startTime }}</th>
                                            @foreach ($spaces as $space)
                                                @php $cellSlot = ($bySpace[$space->id] ?? collect())->first(fn ($s) => $s->start_label === $startTime); @endphp
                                                <td class="align-top">
                                                    @if ($cellSlot)
                                                        <button type="button" data-slot-dialog="slot-dialog-{{ $cellSlot->id }}"
                                                            class="block w-full rounded-lg border p-3 text-left transition hover:ring-2 hover:ring-blue-300 focus-visible:ring-2 focus-visible:ring-blue-600 {{ $cellSlot->status->tileClasses() }}">
                                                            <span class="flex flex-wrap items-center justify-between gap-1">
                                                                <strong class="tabular-nums">{{ $cellSlot->time_range }}</strong>
                                                                <span class="rounded-full bg-white/75 px-2 py-0.5 text-[10px] font-bold">{{ $cellSlot->status->label() }}</span>
                                                            </span>
                                                            @php
                                                                $cellOwnerId = $cellSlot->exhibitor_id !== null ? $cellSlot->exhibitor?->commercial_id : $cellSlot->commercial_id;
                                                                $cellHolderVisible = ! $cellSlot->status->isTaken() || $cellOwnerId === $commercial->id;
                                                            @endphp
                                                            <span class="mt-1 block text-[11px] font-semibold">{{ $cellSlot->code }} · {{ $cellSlot->status->isTaken() ? ($cellHolderVisible ? ($cellSlot->holder_name ?: 'Réservation confirmée') : 'Réservation confirmée') : 'Créneau disponible' }}</span>
                                                            @if ($cellSlot->title)<span class="mt-1 block truncate font-medium opacity-75">{{ $cellSlot->title }}</span>@endif
                                                        </button>
                                                    @else
                                                        <div class="rounded-lg border border-dashed border-slate-200 bg-slate-50/40 p-3 text-center text-slate-400" title="Aucun créneau ne commence à cette heure">—</div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if ($startTimes->isEmpty()) <p class="p-4 text-sm text-slate-500">Aucun créneau ce jour.</p> @endif
                        </div>
                    @empty
                        <p class="mt-4 rounded-xl bg-slate-50 p-5 text-sm text-slate-500">Aucun créneau configuré.</p>
                    @endforelse
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5" aria-labelledby="reservations-heading">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <h2 id="reservations-heading" class="flex items-center gap-2 text-lg font-extrabold text-[#0F2347]"><img src="{{ $icon('calendar-check') }}" class="size-5" alt=""> Réservations <span class="text-sm font-semibold text-slate-400">({{ $soldSlots->count() }})</span></h2>
                        <div class="flex flex-wrap gap-2">
                            <input id="slots-search" type="search" placeholder="Rechercher société, code…" aria-label="Rechercher les réservations" class="w-full rounded-lg border-slate-300 text-xs sm:w-48">
                            <select id="slots-space-filter" aria-label="Filtrer par espace" class="rounded-lg border-slate-300 text-xs"><option value="">Tous les espaces</option><option value="conference">Conférence</option><option value="lounge">Lounge</option></select>
                            <select id="slots-status-filter" aria-label="Filtrer par statut" class="rounded-lg border-slate-300 text-xs"><option value="">Tous les statuts</option>@foreach ($statuses as $status) @if ($status->isTaken())<option value="{{ $status->value }}">{{ $status->label() }}</option>@endif @endforeach</select>
                        </div>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[720px] text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500"><tr><th class="rounded-l-lg p-3">Code</th><th class="p-3">Date</th><th class="p-3">Horaire</th><th class="p-3">Espace</th><th class="p-3">Société / titulaire</th><th class="p-3">Statut</th><th class="p-3">Prix HT</th><th class="rounded-r-lg p-3">Actions</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($soldSlots as $slot)
                                    @php $slotOwnerId = $slot->exhibitor_id !== null ? $slot->exhibitor?->commercial_id : $slot->commercial_id; $slotHolderVisible = $slotOwnerId === $commercial->id; @endphp
                                    <tr data-reservation-row data-search="{{ mb_strtolower($slot->code.' '.($slotHolderVisible ? ($slot->holder_name ?? '').' '.($slot->title ?? '') : '')) }}" data-space="{{ $slot->space->type->value }}" data-status="{{ $slot->status->value }}">
                                        <td class="p-3 font-bold text-[#0F2347]">{{ $slot->code }}</td>
                                        <td class="p-3 whitespace-nowrap">{{ $slot->date->locale('fr')->isoFormat('DD/MM/YYYY') }}</td>
                                        <td class="p-3 whitespace-nowrap">{{ $slot->time_range }}</td>
                                        <td class="p-3">{{ $slot->space->type->label() }}</td>
                                        <td class="p-3 font-semibold">{{ $slotHolderVisible ? ($slot->holder_name ?: 'Réservation confirmée') : 'Réservation confirmée' }}</td>
                                        <td class="p-3"><span class="rounded-full px-2 py-1 font-bold {{ $slot->status->pillClasses() }}">{{ $slot->status->label() }}</span></td>
                                        <td class="p-3 whitespace-nowrap">{{ $money($slot->price_excl_tax) }}</td>
                                        <td class="p-3">@if (! $slot->status->isTaken() || $slotHolderVisible)<button type="button" data-slot-dialog="slot-dialog-{{ $slot->id }}" class="font-bold text-blue-700 hover:underline">Voir / Modifier</button>@else<span class="text-slate-400">Lecture seule</span>@endif</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="p-6 text-center text-slate-500">Aucune réservation pour le moment.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <p id="slots-no-filter-results" hidden class="py-4 text-center text-xs text-slate-500">Aucune réservation correspondante.</p>
                    </div>
                </section>
            </div>

            <aside class="space-y-5" aria-label="Actions et demandes">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="flex items-center gap-2 text-lg font-extrabold text-[#0F2347]"><img src="{{ $icon('bolt') }}" class="size-5" alt="">Actions rapides</h2>
                    <div class="mt-4 space-y-2">
                        <button type="button" data-open-first-free data-new-status="reserved" class="flex w-full items-center gap-3 rounded-xl bg-[#2563EB] px-4 py-3 text-left text-sm font-bold text-white hover:bg-blue-700"><img src="{{ $icon('plus-circle') }}" class="size-5 brightness-0 invert" alt="">Créer une réservation</button>
                        <button type="button" data-open-first-free data-new-status="option" class="flex w-full items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-left text-sm font-bold text-slate-700 hover:bg-slate-50"><img src="{{ $icon('hourglass') }}" class="size-5" alt="">Enregistrer une option</button>
                        <a href="{{ route('commercial.exhibitors.index') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50"><img src="{{ $icon('users') }}" class="size-5" alt="">Consulter mes exposants</a>
                        <a href="{{ route('commercial.slots.export.pdf', 'fr') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50"><img src="{{ $icon('file-pdf') }}" class="size-5" alt="">Exporter le planning (PDF)</a>
                        <a href="{{ route('commercial.slots.export.csv') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50"><img src="{{ $icon('file-csv') }}" class="size-5" alt="">Exporter les réservations (CSV)</a>
                    </div>
                </section>
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="flex items-center gap-2 text-base font-extrabold text-[#0F2347]"><img src="{{ $icon('clock') }}" class="size-5" alt="">Dernières demandes</h2>
                        <a href="{{ route('commercial.slot-requests.index') }}" class="text-xs font-bold text-blue-700 hover:underline">Voir tout</a>
                    </div>
                    @forelse ($recentRequests as $recent)
                        <a href="{{ route('commercial.slot-requests.index') }}" class="block border-b border-slate-100 py-3 last:border-0 hover:bg-blue-50/30">
                            <div class="flex items-center justify-between gap-2"><strong class="text-sm text-slate-800">{{ $recent->exhibitor?->company_name ?? 'Exposant' }}</strong><span class="shrink-0 rounded-full bg-amber-50 px-2 py-1 text-[11px] font-bold text-amber-700">En attente</span></div>
                            <p class="mt-1 text-xs text-slate-500">{{ $recent->firstChoice?->code ?? 'Créneau à préciser' }} · {{ $recent->firstChoice?->date?->locale('fr')->isoFormat('DD MMM YYYY') ?? '' }} · {{ $recent->firstChoice?->time_range ?? '' }}</p>
                        </a>
                    @empty
                        <p class="mt-4 rounded-xl bg-slate-50 p-4 text-xs text-slate-500">Aucune demande en attente.</p>
                    @endforelse
                    @if ($pendingRequestsCount > 0)
                        <a href="{{ route('commercial.slot-requests.index') }}" class="mt-3 block rounded-xl border border-blue-200 p-3 text-center text-xs font-bold text-blue-700 hover:bg-blue-50">{{ $pendingRequestsCount }} demande(s) à traiter</a>
                    @endif
                </section>
            </aside>
        </div>
    </div>

    {{-- Modals réelles : réservation, option, changement de statut et supports. --}}
    @foreach ($slots as $slot)
        @php
            $dialogOwnerId = $slot->exhibitor_id !== null ? $slot->exhibitor?->commercial_id : $slot->commercial_id;
        @endphp
        @if (! $slot->status->isTaken() || $dialogOwnerId === $commercial->id)
        @php
            $useOld = $reopenId === $slot->id;
            $val = fn ($field) => $useOld ? old($field) : $slot->{$field};
            $selStatus = $useOld ? old('status') : $slot->status->value;
            $holderType = $useOld ? old('holder_type', 'exhibitor') : ($slot->is_external ? 'external' : 'exhibitor');
        @endphp
        <dialog id="slot-dialog-{{ $slot->id }}" class="m-auto w-[calc(100%-2rem)] max-w-2xl rounded-2xl p-0 shadow-2xl backdrop:bg-slate-900/60" aria-labelledby="dialog-title-{{ $slot->id }}">
            <div class="max-h-[90vh] overflow-y-auto p-5 sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div><h2 id="dialog-title-{{ $slot->id }}" class="text-lg font-extrabold text-[#0F2347]">Créneau {{ $slot->code }}</h2><p class="mt-1 text-xs text-slate-500">{{ $slot->space->type->label() }} · {{ $slot->date->locale('fr')->isoFormat('DD/MM/YYYY') }} · {{ $slot->time_range }} · {{ $money($slot->price_excl_tax) }} HT</p></div>
                    <button type="button" data-close-dialog class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Fermer">✕</button>
                </div>
                <form method="POST" action="{{ route('commercial.slots.update', $slot) }}" class="mt-5 space-y-4" data-slot-form>
                    @csrf @method('PUT')
                    <input type="hidden" name="slot_id" value="{{ $slot->id }}">
                    <label class="block text-sm font-semibold text-slate-700">Statut
                        <select name="status" class="mt-1 block w-full rounded-xl border-slate-300 text-sm" data-status-select>
                            @foreach ($statuses as $status)<option value="{{ $status->value }}" @selected($selStatus === $status->value)>{{ $status->label() }}</option>@endforeach
                        </select>
                    </label>
                    <fieldset data-holder-block>
                        <legend class="text-sm font-semibold text-slate-700">Réservation pour</legend>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            <label class="rounded-xl border border-slate-200 p-3 text-sm"><input type="radio" name="holder_type" value="exhibitor" @checked($holderType === 'exhibitor') class="mr-1 text-blue-600"> Exposant inscrit</label>
                            <label class="rounded-xl border border-slate-200 p-3 text-sm"><input type="radio" name="holder_type" value="external" @checked($holderType === 'external') class="mr-1 text-blue-600"> Société / contact externe</label>
                        </div>
                        <div data-holder-panel="exhibitor" class="mt-3">
                            <label class="block text-sm font-medium">Exposant
                                <select name="exhibitor_id" class="mt-1 block w-full rounded-xl border-slate-300 text-sm"><option value="">Choisir un exposant</option>@foreach ($exhibitors as $e)<option value="{{ $e->id }}" @selected((int) $val('exhibitor_id') === $e->id)>{{ $e->company_name }}</option>@endforeach</select>
                            </label>
                        </div>
                        <div data-holder-panel="external" class="mt-3 space-y-3">
                            <label class="block text-sm font-medium">Société / titulaire<input name="exhibitor_name" value="{{ $val('exhibitor_name') }}" maxlength="255" class="mt-1 block w-full rounded-xl border-slate-300 text-sm"></label>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="block text-sm font-medium">Contact<input name="contact_name" value="{{ $val('contact_name') }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm"></label>
                                <label class="block text-sm font-medium">Téléphone<input name="contact_phone" value="{{ $val('contact_phone') }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm"></label>
                            </div>
                            <label class="block text-sm font-medium">E-mail<input name="contact_email" type="email" value="{{ $val('contact_email') }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm"></label>
                        </div>
                    </fieldset>
                    <label class="block text-sm font-medium">Sujet / titre<input name="title" value="{{ $val('title') }}" maxlength="255" class="mt-1 block w-full rounded-xl border-slate-300 text-sm"></label>
                    <label class="block text-sm font-medium">Résumé (5 lignes maximum)<textarea name="summary" rows="3" maxlength="1000" class="mt-1 block w-full rounded-xl border-slate-300 text-sm">{{ $val('summary') }}</textarea></label>
                    <label class="block text-sm font-medium">Intervenants<textarea name="speakers" rows="2" maxlength="1000" class="mt-1 block w-full rounded-xl border-slate-300 text-sm">{{ $val('speakers') }}</textarea></label>
                    <label class="block text-sm font-medium">Notes internes<textarea name="notes" rows="2" maxlength="2000" class="mt-1 block w-full rounded-xl border-slate-300 text-sm">{{ $val('notes') }}</textarea></label>
                    @if ($slot->status->isTaken())
                        <p class="rounded-xl bg-amber-50 p-3 text-xs text-amber-800">Pour attribuer ce créneau à une autre société, libérez-le d'abord et sauvegardez, puis effectuez une nouvelle réservation.</p>
                    @endif
                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-4"><button type="button" data-close-dialog class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold">Annuler</button><button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700">Enregistrer</button></div>
                </form>
                @if ($slot->status->isSold())
                    <div class="mt-5 border-t border-slate-200 pt-4">
                        <h3 class="text-sm font-bold text-slate-800">Support de présentation</h3>
                        @if ($slot->support_path)
                            <a href="{{ route('commercial.slots.support', $slot) }}" class="mt-2 inline-block text-sm font-bold text-blue-700 hover:underline">Télécharger {{ $slot->support_original_name ?? 'le support' }}</a>
                        @else
                            <p class="mt-1 text-xs text-slate-500">Aucun document déposé.</p>
                        @endif
                        <form method="POST" enctype="multipart/form-data" action="{{ route('commercial.slots.support.store', $slot) }}" class="mt-3 flex flex-wrap items-center gap-2">
                            @csrf
                            <input type="file" name="support" accept=".pdf,.ppt,.pptx" required class="min-w-0 flex-1 text-xs" aria-label="Support de présentation">
                            <button type="submit" class="rounded-xl bg-[#0F2347] px-4 py-2 text-xs font-bold text-white">Envoyer (20 Mo max)</button>
                        </form>
                    </div>
                @endif
            </div>
        </dialog>
        @endif
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const page = document.getElementById('conference-commercial-page');
            if (!page) return;
            const buttons = page.querySelectorAll('[data-day-button]');
            const panels = page.querySelectorAll('[data-day-panel]');
            buttons.forEach(button => button.addEventListener('click', () => {
                const date = button.dataset.dayButton;
                panels.forEach(panel => { panel.hidden = panel.dataset.dayPanel !== date; });
                buttons.forEach(other => {
                    const active = other === button;
                    other.setAttribute('aria-pressed', active ? 'true' : 'false');
                    other.classList.toggle('bg-[#2563EB]', active);
                    other.classList.toggle('text-white', active);
                    other.classList.toggle('bg-white', !active);
                    other.classList.toggle('text-slate-700', !active);
                    other.classList.toggle('border', !active);
                    other.classList.toggle('border-slate-200', !active);
                });
            }));
            function openDialog(id) { const el = document.getElementById(id); if (el && !el.open) el.showModal(); }
            document.querySelectorAll('[data-slot-dialog]').forEach(btn => btn.addEventListener('click', () => openDialog(btn.dataset.slotDialog)));
            document.querySelectorAll('[data-close-dialog]').forEach(btn => btn.addEventListener('click', () => btn.closest('dialog')?.close()));
            document.querySelectorAll('dialog[id^="slot-dialog-"]').forEach(dialog => {
                dialog.addEventListener('click', e => { if (e.target === dialog) dialog.close(); });
                const form = dialog.querySelector('[data-slot-form]');
                const status = form?.querySelector('[data-status-select]');
                const holder = form?.querySelector('[data-holder-block]');
                const update = () => {
                    if (!form || !status) return;
                    if (holder) holder.hidden = status.value === 'available';
                    const type = form.querySelector('[name="holder_type"]:checked')?.value;
                    form.querySelectorAll('[data-holder-panel]').forEach(panel => {
                        const active = panel.dataset.holderPanel === type;
                        panel.hidden = !active;
                        panel.querySelectorAll('input, select').forEach(input => input.disabled = !active || status.value === 'available');
                    });
                };
                status?.addEventListener('change', update);
                form?.querySelectorAll('[name="holder_type"]').forEach(input => input.addEventListener('change', update));
                update();
            });
            const firstFreeId = @json($firstFreeId);
            document.querySelectorAll('[data-open-first-free]').forEach(btn => btn.addEventListener('click', () => {
                if (firstFreeId) {
                    openDialog(`slot-dialog-${firstFreeId}`);
                    const select = document.querySelector(`#slot-dialog-${firstFreeId} [data-status-select]`);
                    if (select) { select.value = btn.dataset.newStatus || 'reserved'; select.dispatchEvent(new Event('change')); }
                }
                else window.alert('Aucun créneau disponible.');
            }));
            const search = page.querySelector('#slots-search');
            const space = page.querySelector('#slots-space-filter');
            const status = page.querySelector('#slots-status-filter');
            const rows = [...page.querySelectorAll('[data-reservation-row]')];
            const empty = page.querySelector('#slots-no-filter-results');
            const filterRows = () => {
                const term = (search?.value || '').trim().toLocaleLowerCase();
                let visible = 0;
                rows.forEach(row => {
                    const show = (!term || row.dataset.search.includes(term)) && (!space.value || row.dataset.space === space.value) && (!status.value || row.dataset.status === status.value);
                    row.hidden = !show;
                    if (show) visible++;
                });
                if (empty) empty.hidden = visible !== 0 || rows.length === 0;
            };
            [search, space, status].forEach(el => el?.addEventListener(el === search ? 'input' : 'change', filterRows));
            const reopenId = @json($reopenId);
            if (reopenId) openDialog(`slot-dialog-${reopenId}`);
        });
    </script>
</x-commercial.layout>
