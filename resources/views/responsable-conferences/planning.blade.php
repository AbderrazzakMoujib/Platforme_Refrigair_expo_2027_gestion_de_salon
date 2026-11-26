
<x-responsable-conferences.layout :user="auth()->user()">

@php
    $available = \App\Enums\SlotStatus::Available;
    $option = \App\Enums\SlotStatus::Option;
    $reserved = \App\Enums\SlotStatus::Reserved;
    $paid = \App\Enums\SlotStatus::Paid;

    $totalSlots = $slots->count();
    $availableSlots = $slots->where('status', $available)->values();

    $availableCount = $slots->where('status', $available)->count();
    $optionCount = $slots->where('status', $option)->count();

    $reservedCount = $slots->filter(
        fn ($slot) => in_array($slot->status, [$reserved, $paid], true)
    )->count();

    $percent = fn ($count) => $totalSlots
        ? number_format($count * 100 / $totalSlots, 1, ',', ' ')
        : '0';

    $groupedSlots = $slots->groupBy(
        fn ($slot) => $slot->date->format('Y-m-d')
    );

    $dates = $groupedSlots->keys()->sort()->values();
@endphp

<div id="conference-planning" class="space-y-6">

    @if (session('status'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
    @endif
    @if ($errors->getBag('slot')->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <strong>Réservation impossible :</strong>
            <ul class="mt-1 list-disc pl-5">@foreach ($errors->getBag('slot')->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- PAGE HEADER --}}
    <section class="flex flex-wrap items-center justify-between gap-4">

        <div>
            <p class="text-sm font-bold text-blue-600">
                Conférences & Lounge
            </p>

            <div class="mt-3 flex items-center gap-4">

                <div class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                    <x-admin.icon name="calendar" class="size-7"/>
                </div>

                <div>
                    <h1 class="text-2xl font-black tracking-tight text-[#0F2347] sm:text-3xl">
                        Planning des conférences
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Consultez les créneaux, les disponibilités
                        et les réservations du salon REFRIGAIR EXPO 2027.
                    </p>
                </div>
            </div>
        </div>

        <a href="{{ route('responsable-conferences.planning.export.pdf', 'fr') }}" class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm font-bold text-blue-700 shadow-sm transition hover:bg-blue-50">
            <x-admin.icon name="file-pdf" class="size-5"/> Télécharger le planning PDF
        </a>

        @if ($availableSlots->isNotEmpty())
            <button type="button" data-open-first-free class="mb-2 inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                <x-admin.icon name="calendar" class="size-5"/> Créer une réservation
            </button>
        @endif
        <a href="{{ route('responsable-conferences.requests.index') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">

            <x-admin.icon name="inbox" class="size-5"/>
            Gérer les demandes
        </a>
    </section>

    {{-- KPI CARDS --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach([
            [
                'label' => 'Total créneaux',
                'value' => $totalSlots,
                'percent' => null,
                'icon' => 'calendar',
                'color' => 'blue',
            ],
            [
                'label' => 'Disponibles',
                'value' => $availableCount,
                'percent' => $percent($availableCount).'%',
                'icon' => 'clipboard-check',
                'color' => 'emerald',
            ],
            [
                'label' => 'Réservés / Payés',
                'value' => $reservedCount,
                'percent' => $percent($reservedCount).'%',
                'icon' => 'users',
                'color' => 'violet',
            ],
            [
                'label' => 'En option',
                'value' => $optionCount,
                'percent' => $percent($optionCount).'%',
                'icon' => 'chart',
                'color' => 'amber',
            ],
        ] as $stat)

            @php
                $style = match($stat['color']) {
                    'emerald' => ['bg-emerald-100 text-emerald-700', 'bg-emerald-50 text-emerald-700'],
                    'violet' => ['bg-violet-100 text-violet-700', 'bg-violet-50 text-violet-700'],
                    'amber' => ['bg-amber-100 text-amber-700', 'bg-amber-50 text-amber-700'],
                    default => ['bg-blue-100 text-blue-700', 'bg-blue-50 text-blue-700'],
                };
            @endphp

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-start justify-between gap-2">

                    <span class="grid size-12 place-items-center rounded-xl {{ $style[0] }}">
                        <x-admin.icon :name="$stat['icon']" class="size-6"/>
                    </span>

                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500">
                        REFRIGAIR 2027
                    </span>
                </div>

                <p class="mt-4 text-sm font-semibold text-slate-600">
                    {{ $stat['label'] }}
                </p>

                <div class="mt-2 flex items-center gap-3">

                    <span class="text-3xl font-black text-[#0F2347]">
                        {{ $stat['value'] }}
                    </span>

                    @if($stat['percent'])
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $style[1] }}">
                            {{ $stat['percent'] }}
                        </span>
                    @endif
                </div>
            </article>

        @endforeach
    </section>

    {{-- MAIN PROGRAMME --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- PROGRAMME TITLE --}}
        <div class="flex flex-wrap items-center justify-between gap-4 p-5 sm:p-6">

            <div class="flex items-center gap-3">

                <span class="grid size-12 place-items-center rounded-xl bg-blue-100 text-blue-600">
                    <x-admin.icon name="calendar" class="size-6"/>
                </span>

                <div>
                    <h2 class="text-lg font-black text-[#0F2347]">
                        Programme des conférences
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Sélectionnez une journée pour consulter les créneaux.
                    </p>
                </div>
            </div>

            <div class="rounded-xl bg-blue-50 px-4 py-3 text-right">

                <p class="text-xs font-bold text-blue-700">
                    REFRIGAIR EXPO 2027
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Du 02 au 04 février 2027
                </p>
            </div>
        </div>

        {{-- DAY TABS --}}
        <div class="px-5 pb-5 sm:px-6">

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">

                <button
                    type="button"
                    data-day="all"
                    aria-pressed="true"
                    class="planning-day-button rounded-xl border border-[#0F2347] bg-[#0F2347] px-4 py-3 text-sm font-bold text-white transition">

                    Toutes les journées
                </button>

                @foreach($dates as $date)

                    @php
                        $day = \Illuminate\Support\Carbon::parse($date);
                    @endphp

                    <button
                        type="button"
                        data-day="{{ $date }}"
                        aria-pressed="false"
                        class="planning-day-button rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-[#0F2347] transition hover:border-blue-400">

                        <span class="capitalize">
                            {{ $day->locale('fr')->isoFormat('dddd') }}
                        </span>

                        <span class="ml-2">
                            {{ $day->format('d/m/Y') }}
                        </span>
                    </button>

                @endforeach

            </div>
        </div>

        {{-- FILTERS --}}
        <div class="mx-5 grid gap-4 rounded-xl bg-[#F1F6FD] p-4 sm:mx-6 md:grid-cols-3">

            <div>
                <label for="planning-search" class="mb-2 block text-xs font-bold text-[#0F2347]">
                    Rechercher
                </label>

                <input
                    id="planning-search"
                    type="search"
                    placeholder="Code, entreprise, conférence..."
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-200"
                >
            </div>

            <div>
                <label for="planning-space" class="mb-2 block text-xs font-bold text-[#0F2347]">
                    Espace
                </label>

                <select
                    id="planning-space"
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-200">

                    <option value="all">Tous les espaces</option>

                    @foreach($spaces as $space)
                        <option value="{{ $space->id }}">
                            {{ $space->name ?: $space->type->label() }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label for="planning-status" class="mb-2 block text-xs font-bold text-[#0F2347]">
                    Statut
                </label>

                <select
                    id="planning-status"
                    class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-200">

                    <option value="all">Tous les statuts</option>
                    <option value="available">Disponible</option>
                    <option value="option">En option</option>
                    <option value="reserved">Réservé</option>
                    <option value="paid">Payé</option>
                </select>
            </div>
        </div>

        {{-- LEGEND --}}
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 px-5 py-5 sm:px-6">

            <div class="flex flex-wrap gap-x-5 gap-y-2 text-xs font-medium text-slate-600">

                @foreach([
                    ['Disponible', 'bg-emerald-500'],
                    ['Option', 'bg-amber-500'],
                    ['Réservé', 'bg-blue-500'],
                    ['Payé', 'bg-violet-500'],
                ] as [$label, $color])

                    <span class="inline-flex items-center gap-2">
                        <span class="size-3 rounded-full {{ $color }}"></span>
                        {{ $label }}
                    </span>

                @endforeach
            </div>

            <span id="planning-count"
                  role="status"
                  aria-live="polite"
                  class="rounded-full bg-blue-50 px-4 py-2 text-xs font-bold text-[#0F2347]">

                {{ $totalSlots }} créneau(x)
            </span>
        </div>

        {{-- PLANNING DAYS --}}
        <div class="space-y-7 p-4 sm:p-6">

            @forelse($groupedSlots as $date => $daySlots)

                @php
                    $day = \Illuminate\Support\Carbon::parse($date);
                @endphp

                <section data-planning-section="{{ $date }}" class="space-y-3">

                    {{-- DAY TITLE --}}
                    <div class="flex items-center gap-4 rounded-xl bg-[#F1F6FD] p-3">

                        <div class="flex size-14 shrink-0 flex-col items-center justify-center rounded-xl bg-[#0F2347] text-white">

                            <span class="text-2xl font-black leading-none">
                                {{ $day->format('d') }}
                            </span>

                            <span class="mt-1 text-[10px] font-bold uppercase">
                                {{ $day->locale('fr')->isoFormat('MMM') }}
                            </span>
                        </div>

                        <div>
                            <h3 class="text-base font-extrabold capitalize text-[#0F2347]">
                                {{ $day->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                            </h3>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ $daySlots->count() }} créneau(x) programmés
                            </p>
                        </div>
                    </div>

                    {{-- HORIZONTAL SLOT CARDS --}}
                    <div class="grid gap-3 xl:grid-cols-2">

                        @foreach($daySlots as $slot)

                            @php
                                $status = $slot->status->value;

                                $theme = match($status) {
                                    'available' => [
                                        'border-l-emerald-500',
                                        'bg-emerald-50 text-emerald-800',
                                        'bg-emerald-50',
                                    ],
                                    'option' => [
                                        'border-l-amber-500',
                                        'bg-amber-100 text-amber-800',
                                        'bg-amber-50',
                                    ],
                                    'reserved' => [
                                        'border-l-blue-500',
                                        'bg-blue-100 text-blue-800',
                                        'bg-blue-50',
                                    ],
                                    'paid' => [
                                        'border-l-violet-500',
                                        'bg-violet-100 text-violet-800',
                                        'bg-violet-50',
                                    ],
                                };

                                $company = $slot->holder_name;

                                $commercial = $slot->exhibitor?->commercial?->name
                                    ?? $slot->commercial?->name;

                                $search = implode(' ', array_filter([
                                    $slot->code,
                                    $slot->space?->name,
                                    $slot->space?->type?->label(),
                                    $slot->title,
                                    $company,
                                    $commercial,
                                    $slot->speakers,
                                ]));
                            @endphp

                            <article
                                data-slot
                                data-date="{{ $date }}"
                                data-space="{{ $slot->space_id }}"
                                data-status="{{ $status }}"
                                data-search="{{ $search }}"
                                class="overflow-hidden rounded-xl border border-l-4 border-slate-200 {{ $theme[0] }} bg-white shadow-sm transition hover:shadow-md">

                                <div class="flex min-w-0 gap-3 p-3">

                                    {{-- TIME COLUMN --}}
                                    <div class="flex w-[76px] shrink-0 flex-col items-center justify-center rounded-lg {{ $theme[2] }} px-2 py-4 text-center">

                                        <x-admin.icon name="calendar" class="size-5 text-[#0F2347]"/>

                                        <span class="mt-3 text-sm font-black text-[#0F2347]">
                                            {{ $slot->start_label }}
                                        </span>

                                        <span class="my-1 text-xs text-slate-400">
                                            —
                                        </span>

                                        <span class="text-sm font-black text-[#0F2347]">
                                            {{ $slot->end_label }}
                                        </span>
                                    </div>

                                    {{-- CARD BODY --}}
                                    <div class="min-w-0 flex-1">

                                        <div class="flex flex-wrap items-start justify-between gap-2">

                                            <div class="flex flex-wrap items-center gap-2">

                                                <span class="rounded-lg bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700">
                                                    {{ $slot->code }}
                                                </span>

                                                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                                                    {{ $slot->space?->name ?: $slot->space?->type?->label() ?? 'Espace' }}
                                                </span>
                                            </div>

                                            <span class="rounded-full px-3 py-1 text-[11px] font-bold {{ $theme[1] }}">
                                                {{ $slot->status->label() }}
                                            </span>
                                        </div>

                                        @if($status === 'available')

                                            {{-- AVAILABLE SLOT --}}
                                            <div class="mt-3 rounded-xl border border-dashed border-emerald-300 bg-emerald-50/60 px-4 py-4">

                                                <p class="font-bold text-emerald-800">
                                                    Créneau disponible
                                                </p>

                                                <p class="mt-1 text-xs text-emerald-700">
                                                    Aucune réservation confirmée.
                                                </p>
                                                <button type="button" data-reservation-slot-id="{{ $slot->id }}" class="mt-3 rounded-lg bg-[#2563EB] px-3 py-2 text-xs font-bold text-white hover:bg-blue-700">Réserver ce créneau</button>
                                            </div>

                                        @else

                                            {{-- CONFERENCE INFORMATION --}}
                                            <h4 class="mt-3 line-clamp-2 text-sm font-extrabold text-[#0F2347]">
                                                {{ $slot->title ?: 'Sujet non renseigné' }}
                                            </h4>

                                            <div class="mt-3 flex items-center gap-3">

                                                <div class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-lg border border-slate-200 bg-white">

                                                    @if($slot->exhibitor?->logo_url)

                                                        <img
                                                            src="{{ $slot->exhibitor->logo_url }}"
                                                            alt="Logo {{ $slot->exhibitor->company_name }}"
                                                            loading="lazy"
                                                            class="h-full w-full object-contain p-1"
                                                        >

                                                    @else

                                                        <span class="text-xs font-black text-blue-700">
                                                            {{ $slot->exhibitor?->initials ?? 'EX' }}
                                                        </span>

                                                    @endif
                                                </div>

                                                <div class="min-w-0">

                                                    <p class="truncate text-sm font-bold text-[#0F2347]">
                                                        {{ $company ?: 'Titulaire non renseigné' }}
                                                    </p>

                                                    <p class="mt-1 truncate text-xs text-slate-500">
                                                        Commercial : {{ $commercial ?: 'Non renseigné' }}
                                                    </p>
                                                </div>
                                            </div>

                                        @endif

                                        {{-- DETAILS --}}
                                        <details class="group mt-3 border-t border-slate-100 pt-3">

                                            <summary class="flex cursor-pointer list-none items-center justify-between text-xs font-bold text-blue-600">

                                                Voir les détails

                                                <span class="transition group-open:rotate-180">
                                                    ▼
                                                </span>
                                            </summary>

                                            <div class="mt-3 space-y-3 rounded-lg bg-slate-50 p-4 text-xs">

                                                <div class="flex justify-between gap-3">

                                                    <span class="text-slate-500">Code</span>

                                                    <strong class="text-[#0F2347]">
                                                        {{ $slot->code }}
                                                    </strong>
                                                </div>

                                                <div class="flex justify-between gap-3">

                                                    <span class="text-slate-500">Date</span>

                                                    <strong class="text-[#0F2347]">
                                                        {{ $slot->date->format('d/m/Y') }}
                                                    </strong>
                                                </div>

                                                <div class="flex justify-between gap-3">

                                                    <span class="text-slate-500">Horaire</span>

                                                    <strong class="text-[#0F2347]">
                                                        {{ $slot->time_range }}
                                                    </strong>
                                                </div>

                                                <div class="flex justify-between gap-3">

                                                    <span class="text-slate-500">Statut</span>

                                                    <strong class="text-[#0F2347]">
                                                        {{ $slot->status->label() }}
                                                    </strong>
                                                </div>

                                                @if($company)
                                                    <div class="flex justify-between gap-3">

                                                        <span class="text-slate-500">Titulaire</span>

                                                        <strong class="text-right text-[#0F2347]">
                                                            {{ $company }}
                                                        </strong>
                                                    </div>
                                                @endif

                                                @if($slot->contact_name)
                                                    <div class="flex justify-between gap-3">

                                                        <span class="text-slate-500">Contact</span>

                                                        <strong class="text-right text-[#0F2347]">
                                                            {{ $slot->contact_name }}
                                                        </strong>
                                                    </div>
                                                @endif

                                                @if($slot->speakers)
                                                    <div class="border-t border-slate-200 pt-3">

                                                        <p class="font-bold text-slate-500">
                                                            Intervenants
                                                        </p>

                                                        <p class="mt-1 whitespace-pre-line leading-5 text-slate-700">
                                                            {{ $slot->speakers }}
                                                        </p>
                                                    </div>
                                                @endif

                                                @if($slot->summary)
                                                    <div class="border-t border-slate-200 pt-3">

                                                        <p class="font-bold text-slate-500">
                                                            Description
                                                        </p>

                                                        <p class="mt-1 whitespace-pre-line leading-5 text-slate-700">
                                                            {{ $slot->summary }}
                                                        </p>
                                                    </div>
                                                @endif

                                            </div>
                                        </details>

                                        @if(in_array($slot->id, $manualReservationIds, true))
                                            <details class="mt-3 border-t border-slate-100 pt-3">
                                                <summary class="cursor-pointer text-xs font-bold text-blue-700">Modifier la réservation</summary>
                                                <form method="POST" action="{{ route('responsable-conferences.reservations.update', $slot) }}" class="mt-3 space-y-3 rounded-xl bg-slate-50 p-4">
                                                    @csrf
                                                    @method('PUT')
                                                    @if($slot->exhibitor_id === null)
                                                        <label class="block text-xs font-semibold text-slate-600">Société / titulaire
                                                            <input name="exhibitor_name" maxlength="255" value="{{ $slot->exhibitor_name }}" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                                        </label>
                                                    @endif
                                                    <label class="block text-xs font-semibold text-slate-600">Sujet / titre
                                                        <input name="title" maxlength="255" value="{{ $slot->title }}" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                                    </label>
                                                    <label class="block text-xs font-semibold text-slate-600">Résumé
                                                        <textarea name="summary" maxlength="1000" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">{{ $slot->summary }}</textarea>
                                                    </label>
                                                    <div class="grid gap-3 sm:grid-cols-2">
                                                        <label class="block text-xs font-semibold text-slate-600">Contact
                                                            <input name="contact_name" maxlength="255" value="{{ $slot->contact_name }}" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                                        </label>
                                                        <label class="block text-xs font-semibold text-slate-600">Téléphone
                                                            <input name="contact_phone" maxlength="40" value="{{ $slot->contact_phone }}" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                                        </label>
                                                    </div>
                                                    <label class="block text-xs font-semibold text-slate-600">E-mail du contact
                                                        <input type="email" name="contact_email" maxlength="255" value="{{ $slot->contact_email }}" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">
                                                    </label>
                                                    <label class="block text-xs font-semibold text-slate-600">Intervenants
                                                        <textarea name="speakers" maxlength="1000" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">{{ $slot->speakers }}</textarea>
                                                    </label>
                                                    <label class="block text-xs font-semibold text-slate-600">Notes internes
                                                        <textarea name="notes" maxlength="2000" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm">{{ $slot->notes }}</textarea>
                                                    </label>
                                                    <p class="text-[11px] text-slate-500">L’espace, la date et l’horaire restent fixes.</p>
                                                    <button type="submit" class="rounded-lg bg-[#2563EB] px-4 py-2 text-xs font-bold text-white hover:bg-blue-700">Enregistrer les modifications</button>
                                                </form>
                                            </details>
                                            <form method="POST" action="{{ route('responsable-conferences.reservations.destroy', $slot) }}" class="mt-2" onsubmit="return confirm('Supprimer cette réservation et libérer le créneau ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50">Supprimer la réservation</button>
                                            </form>
                                        @endif

                                    </div>
                                </div>
                            </article>

                        @endforeach
                    </div>
                </section>

            @empty

                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">
                    <p class="font-bold text-[#0F2347]">
                        Aucun créneau programmé.
                    </p>
                </div>

            @endforelse

            <div id="planning-empty"
                 hidden
                 class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">

                <p class="font-bold text-[#0F2347]">
                    Aucun créneau trouvé.
                </p>

                <p class="mt-2 text-sm text-slate-500">
                    Modifiez les filtres ou réinitialisez la recherche.
                </p>

                <button id="planning-reset"
                        type="button"
                        class="mt-4 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-bold text-white">

                    Réinitialiser
                </button>
            </div>
        </div>
    </section>
</div>
@php
    $firstAvailable = $availableSlots->first();
    $defaultSpaceId = old('space_id', $firstAvailable?->space_id ?? '');
    $defaultDate = old('date', $firstAvailable?->date?->toDateString() ?? '');
    $defaultSlotId = old('slot_id', $firstAvailable?->id ?? '');
    $reservationValue = fn ($field) => old($field);
    $reservationStatus = old('status', \App\Enums\SlotStatus::Reserved->value);
    $reservationHolderType = old('holder_type', 'external');
@endphp

@if ($availableSlots->isNotEmpty())
    <dialog id="create-reservation-dialog" class="m-auto w-[calc(100%-2rem)] max-w-2xl rounded-2xl p-0 shadow-2xl backdrop:bg-slate-900/60" aria-labelledby="create-reservation-title">
        <div class="max-h-[90vh] overflow-y-auto p-5 sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 id="create-reservation-title" class="text-lg font-extrabold text-[#0F2347]">Créer une réservation</h2>
                    <p class="mt-1 text-xs text-slate-500">Choisissez un espace, une date et un horaire disponibles.</p>
                </div>
                <button type="button" data-close-reservation-dialog class="rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Fermer">×</button>
            </div>
            <form method="POST" action="{{ route('responsable-conferences.reservations.store') }}" class="mt-5 space-y-4" data-reservation-form>
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-semibold text-slate-700">
                        Espace
                        <select id="reservation-space" name="space_id" required class="conference-booking-input mt-2">
                            <option value="">Choisir un espace</option>
                            @foreach ($spaces as $space)
                                @if ($availableSlots->contains('space_id', $space->id))
                                    <option value="{{ $space->id }}" @selected((string) $defaultSpaceId === (string) $space->id)>{{ $space->type->label() }}</option>
                                @endif
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">
                        Date
                        <select id="reservation-date" name="date" required class="conference-booking-input mt-2">
                            <option value="">Choisir une date</option>
                            @foreach ($spaces as $space)
                                @foreach ($availableSlots->where('space_id', $space->id)->unique(fn ($availableSlot) => $availableSlot->date->toDateString()) as $dateSlot)
                                    <option value="{{ $dateSlot->date->toDateString() }}" data-space-id="{{ $space->id }}" @selected((string) $defaultSpaceId === (string) $space->id && $defaultDate === $dateSlot->date->toDateString())>{{ $dateSlot->date->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </label>
                </div>
                <label class="block text-sm font-semibold text-slate-700">
                    Horaire
                    <select id="reservation-slot" name="slot_id" required class="conference-booking-input mt-2">
                        <option value="">Choisir un horaire</option>
                        @foreach ($availableSlots as $availableSlot)
                            <option value="{{ $availableSlot->id }}" data-space-id="{{ $availableSlot->space_id }}" data-date="{{ $availableSlot->date->toDateString() }}" @selected((string) $defaultSlotId === (string) $availableSlot->id)>{{ $availableSlot->start_label }} – {{ $availableSlot->end_label }} ({{ $availableSlot->code }})</option>
                        @endforeach
                    </select>
                </label>
                @include('conferences.partials.slot-booking-fields', ['statuses' => $statuses, 'selStatus' => $reservationStatus, 'holderType' => $reservationHolderType, 'value' => $reservationValue, 'allowAvailable' => false])
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" data-close-reservation-dialog class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold">Annuler</button>
                    <button type="submit" class="rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700">Enregistrer la réservation</button>
                </div>
            </form>
        </div>
    </dialog>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('conference-planning');
    if (!root) return;

    const search = root.querySelector('#planning-search');
    const space = root.querySelector('#planning-space');
    const status = root.querySelector('#planning-status');

    const buttons = [...root.querySelectorAll('[data-day]')];
    const cards = [...root.querySelectorAll('[data-slot]')];
    const sections = [...root.querySelectorAll('[data-planning-section]')];

    const counter = root.querySelector('#planning-count');
    const empty = root.querySelector('#planning-empty');
    const reset = root.querySelector('#planning-reset');

    const reservationDialog = document.getElementById('create-reservation-dialog');
    const reservationForm = reservationDialog?.querySelector('[data-reservation-form]');
    const spaceSelect = reservationForm?.querySelector('#reservation-space');
    const dateSelect = reservationForm?.querySelector('#reservation-date');
    const slotSelect = reservationForm?.querySelector('#reservation-slot');
    const statusSelect = reservationForm?.querySelector('[data-status-select]');

    function refreshSlotChoices(preferredSlotId = slotSelect?.value) {
        if (!spaceSelect || !dateSelect || !slotSelect) return;
        const matchingSlots = [...slotSelect.options].filter(option =>
            option.value && option.dataset.spaceId === spaceSelect.value && option.dataset.date === dateSelect.value
        );
        [...slotSelect.options].forEach(option => {
            if (!option.value) return;
            const available = matchingSlots.includes(option);
            option.hidden = !available;
            option.disabled = !available;
        });
        const selectedSlot = matchingSlots.find(option => option.value === String(preferredSlotId)) || matchingSlots[0];
        slotSelect.value = selectedSlot?.value || '';
    }

    function refreshDateChoices(preferredDate = dateSelect?.value) {
        if (!spaceSelect || !dateSelect) return;
        const matchingDates = [...dateSelect.options].filter(option =>
            option.value && option.dataset.spaceId === spaceSelect.value
        );
        [...dateSelect.options].forEach(option => {
            if (!option.value) return;
            const available = matchingDates.includes(option);
            option.hidden = !available;
            option.disabled = !available;
        });
        const selectedDate = matchingDates.find(option => option.value === String(preferredDate)) || matchingDates[0];
        dateSelect.value = selectedDate?.value || '';
        refreshSlotChoices();
    }

    function updateHolderPanels() {
        const activeType = reservationForm?.querySelector('[name="holder_type"]:checked')?.value;
        reservationForm?.querySelectorAll('[data-holder-panel]').forEach(panel => {
            const active = panel.dataset.holderPanel === activeType;
            panel.hidden = !active;
            panel.querySelectorAll('input, select').forEach(input => input.disabled = !active || statusSelect?.value === 'available');
        });
    }

    if (spaceSelect && dateSelect && slotSelect) {
        spaceSelect.addEventListener('change', () => refreshDateChoices(''));
        dateSelect.addEventListener('change', () => refreshSlotChoices(''));
        slotSelect.addEventListener('change', updateHolderPanels);
        refreshDateChoices(dateSelect.value);
    }
    reservationForm?.querySelectorAll('[name="holder_type"]').forEach(input => input.addEventListener('change', updateHolderPanels));
    statusSelect?.addEventListener('change', updateHolderPanels);
    updateHolderPanels();

    function openReservationForSlot(slotId = null) {
        if (!reservationDialog || !reservationDialog.showModal) return;
        if (slotId && slotSelect) {
            const option = [...slotSelect.options].find(item => item.value === String(slotId));
            if (option) {
                spaceSelect.value = option.dataset.spaceId;
                refreshDateChoices(option.dataset.date);
                slotSelect.value = option.value;
            }
        }
        if (!reservationDialog.open) reservationDialog.showModal();
    }

    document.querySelectorAll('[data-reservation-slot-id]').forEach(button => {
        button.addEventListener('click', () => openReservationForSlot(button.dataset.reservationSlotId));
    });
    document.querySelectorAll('[data-open-first-free]').forEach(button => {
        button.addEventListener('click', () => openReservationForSlot());
    });
    document.querySelectorAll('[data-close-reservation-dialog]').forEach(button => {
        button.addEventListener('click', () => button.closest('dialog')?.close());
    });
    reservationDialog?.addEventListener('click', event => {
        if (event.target === reservationDialog) reservationDialog.close();
    });

    const reopenedSlotId = @json((string) old('slot_id', ''));
    if (reopenedSlotId) openReservationForSlot(reopenedSlotId);
    let selectedDay = 'all';

    const normalize = (value) => String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();

    function updateButtons() {
        buttons.forEach(button => {
            const active = button.dataset.day === selectedDay;

            button.setAttribute('aria-pressed', String(active));

            button.classList.toggle('bg-[#0F2347]', active);
            button.classList.toggle('text-white', active);

            button.classList.toggle('bg-white', !active);
            button.classList.toggle('text-[#0F2347]', !active);
        });
    }

    function filterPlanning() {
        const keyword = normalize(search.value);
        let visible = 0;

        cards.forEach(card => {
            const dayMatch = selectedDay === 'all'
                || card.dataset.date === selectedDay;

            const spaceMatch = space.value === 'all'
                || card.dataset.space === space.value;

            const statusMatch = status.value === 'all'
                || card.dataset.status === status.value;

            const searchMatch = !keyword
                || normalize(card.dataset.search).includes(keyword);

            const show = dayMatch && spaceMatch && statusMatch && searchMatch;

            card.hidden = !show;

            if (show) visible++;
        });

        sections.forEach(section => {
            const hasVisible = [...section.querySelectorAll('[data-slot]')]
                .some(card => !card.hidden);

            section.hidden = !hasVisible;
        });

        counter.textContent = visible + ' créneau(x)';
        empty.hidden = visible !== 0;
    }

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            selectedDay = button.dataset.day;
            updateButtons();
            filterPlanning();
        });
    });

    [search, space, status].forEach(input => {
        input.addEventListener('input', filterPlanning);
        input.addEventListener('change', filterPlanning);
    });

    reset.addEventListener('click', () => {
        selectedDay = 'all';
        search.value = '';
        space.value = 'all';
        status.value = 'all';

        updateButtons();
        filterPlanning();
    });

    updateButtons();
    filterPlanning();
});
</script>

</x-responsable-conferences.layout>
