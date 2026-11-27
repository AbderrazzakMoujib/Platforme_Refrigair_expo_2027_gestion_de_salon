@php
    use App\Enums\SlotRequestStatus;
    use App\Enums\SlotStatus;
    use Illuminate\Support\Carbon;
    $money = fn ($amount) =>
        number_format((float) $amount, 0, ',', ' ') . ' € HT';
    $allSlots = collect($planning)
        ->flatMap(fn ($days) => collect($days)
            ->flatMap(fn ($slots) => collect($slots)))
        ->values();
    $totalCount = $allSlots->count();
    $freeCount = collect($availableSlots)
        ->flatMap(fn ($slots) => collect($slots))
        ->count();
    $reservedCount = $allSlots
        ->filter(fn ($slot) => $slot->status->isSold())
        ->count();
    $mineIds = $mySlots->pluck('id')
        ->map(fn ($id) => (int) $id)->all();
    $pendingIds = collect($requestedSlotIds)
        ->map(fn ($id) => (int) $id)->all();
    $days = $allSlots
        ->pluck('date')
        ->filter()
        ->map(fn ($date) => $date->toDateString())
        ->unique()
        ->sort()
        ->values();
    $defaultDay = $days->first();
    $selectedType = old('type', 'conference');
    $selectedSlot = (int) old('first_choice_slot_id', 0);
    $selectedSecondSlot = (int) old('second_choice_slot_id', 0);
    $prices = [
        'conference' => 1500,
        'lounge' => 1000,
    ];
    $spaces = [
        'conference' => 'Salle de conférence',
        'lounge' => 'Lounge des Technologies',
    ];
    $metrics = [
        [
            'label' => 'Total des créneaux',
            'value' => $totalCount,
            'icon' => 'calendar.svg',
            'tone' => 'blue',
        ],
        [
            'label' => 'Disponibles',
            'value' => $freeCount,
            'icon' => 'calendar-check.svg',
            'tone' => 'emerald',
        ],
        [
            'label' => 'Réservés',
            'value' => $reservedCount,
            'icon' => 'check-circle.svg',
            'tone' => 'blue',
        ],
        [
            'label' => 'Prix conférence',
            'value' => $money($prices['conference']),
            'icon' => 'presentation.svg',
            'tone' => 'violet',
        ],
        [
            'label' => 'Prix Lounge',
            'value' => $money($prices['lounge']),
            'icon' => 'layers.svg',
            'tone' => 'teal',
        ],
    ];
@endphp
<x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">
<div id="exhibitor-conferences" class="min-w-0 space-y-5">
    {{-- HEADER --}}
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <nav class="mb-2 flex items-center gap-2 text-xs font-medium text-slate-500"
                 aria-label="Fil d'Ariane">
                <a href="{{ route('exposant.index') }}"
                   class="hover:text-blue-700">
                    Espace exposant
                </a>
                <span>/</span>
                <span class="text-[#2563EB]">Conférences & Lounge</span>
            </nav>
            <h1 class="flex items-center gap-3 text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                <img
                    src="{{ asset('assets/conferences/icons/calendar.svg') }}"
                    alt=""
                    aria-hidden="true"
                    class="size-8"
                >
                Conférences & Lounge
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Réservez un créneau pour présenter vos solutions,
                innovations ou retours d'expérience.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <p class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-2 text-xs font-semibold text-blue-900">
                Refrigair Expo 2027 · 2–4 février · OFEC Casablanca
            </p>
            <a href="{{ route('exhibitor.slots.export.pdf', 'fr') }}" class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-[#2563EB] px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                <x-admin.icon name="file-pdf" class="size-4" />
                Programme PDF
            </a>
        </div>
    </header>
    {{-- MESSAGES --}}
    @if (session('status'))
        <div role="status"
             class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif
    @if ($errors->any())
        <div role="alert"
             class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-bold">Veuillez vérifier votre demande :</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    {{-- STATISTIQUES --}}
    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5"
             aria-label="Résumé des créneaux">
        @foreach ($metrics as $metric)
            <article class="flex min-w-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-slate-50">
                    <img
                        src="{{ asset('assets/conferences/icons/' . $metric['icon']) }}"
                        alt=""
                        aria-hidden="true"
                        class="size-6"
                    >
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-500">
                        {{ $metric['label'] }}
                    </p>
                    <p @class([
                        'mt-1 text-xl font-extrabold tabular-nums sm:text-2xl',
                        'text-emerald-700' => $metric['tone'] === 'emerald',
                        'text-violet-700' => $metric['tone'] === 'violet',
                        'text-teal-700' => $metric['tone'] === 'teal',
                        'text-blue-700' => $metric['tone'] === 'blue',
                    ])>
                        {{ $metric['value'] }}
                    </p>
                </div>
            </article>
        @endforeach
    </section>
    {{-- PRESENTATION DES ESPACES --}}
    <section class="grid gap-4 lg:grid-cols-2"
             aria-label="Espaces de présentation">
        {{-- SALLE DE CONFERENCE --}}
        <article class="group relative isolate min-h-[180px] overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-sm">
            <img
                src="{{ asset('assets/conferences/images/salle-conference.png') }}"
                alt="Salle de conférence Refrigair Expo"
                class="absolute inset-y-0 right-0 h-full w-[45%] object-cover transition duration-500 group-hover:scale-105"
                loading="lazy"
            >
            <div aria-hidden="true"
                 class="absolute inset-0 bg-gradient-to-r from-white via-white via-55% to-white/10"></div>
            <div class="relative flex h-full min-h-[180px] flex-col justify-between gap-4 p-5 sm:p-6">
                <div class="flex items-start gap-3">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-100/90">
                        <img
                            src="{{ asset('assets/conferences/icons/presentation.svg') }}"
                            alt=""
                            class="size-6"
                        >
                    </span>
                    <div class="max-w-[65%]">
                        <h2 class="text-lg font-extrabold text-[#0F2347]">
                            Salle de conférence
                        </h2>
                        <p class="mt-1 text-sm text-slate-600">
                            Présentez vos solutions, produits
                            ou retours d'expérience.
                        </p>
                    </div>
                </div>
                <div>
                    <p class="inline-flex rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-extrabold text-blue-700">
                        {{ $money($prices['conference']) }} / créneau
                    </p>
                    <p class="mt-2 text-xs font-semibold text-slate-600">
                        1 heure · 3–4 février 2027
                    </p>
                </div>
            </div>
        </article>
        {{-- LOUNGE --}}
        <article class="group relative isolate min-h-[180px] overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-sm">
            <img
                src="{{ asset('assets/conferences/images/lounge-technologies.png') }}"
                alt="Lounge des Technologies Refrigair Expo"
                class="absolute inset-y-0 right-0 h-full w-[45%] object-cover transition duration-500 group-hover:scale-105"
                loading="lazy"
            >
            <div aria-hidden="true"
                 class="absolute inset-0 bg-gradient-to-r from-white via-white via-55% to-white/10"></div>
            <div class="relative flex h-full min-h-[180px] flex-col justify-between gap-4 p-5 sm:p-6">
                <div class="flex items-start gap-3">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-emerald-100/90">
                        <img
                            src="{{ asset('assets/conferences/icons/layers.svg') }}"
                            alt=""
                            class="size-6"
                        >
                    </span>
                    <div class="max-w-[65%]">
                        <h2 class="text-lg font-extrabold text-[#0F2347]">
                            Lounge des Technologies
                        </h2>
                        <p class="mt-1 text-sm text-slate-600">
                            Démonstrations, innovations et échanges
                            avec les professionnels.
                        </p>
                    </div>
                </div>
                <div>
                    <p class="inline-flex rounded-lg bg-emerald-50 px-3 py-1.5 text-sm font-extrabold text-emerald-700">
                        {{ $money($prices['lounge']) }} / créneau
                    </p>
                    <p class="mt-2 text-xs font-semibold text-slate-600">
                        2 heures · 2–4 février 2027
                    </p>
                </div>
            </div>
        </article>
    </section>
    {{-- COLONNES INDEPENDANTES : pas de vide sous le planning --}}
    {{-- CONFIRMED SESSIONS — SUPPORT + PRIVATE CHAT --}}
@php
    $supportLabels = [
        'not_submitted' => ['Non envoyé', 'bg-slate-100 text-slate-700'],
        'pending' => ['À vérifier', 'bg-amber-100 text-amber-800'],
        'approved' => ['Approuvé', 'bg-emerald-100 text-emerald-800'],
        'revision_requested' => ['Modification demandée', 'bg-red-100 text-red-700'],
    ];
@endphp
    <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)]">
        {{-- COLONNE GAUCHE : PLANNING + RESERVATIONS --}}
        <div class="min-w-0 space-y-4">
        {{-- PLANNING --}}
        <section
            class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5"
            aria-labelledby="planning-heading"
        >
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="planning-heading"
                    class="flex items-center gap-2 text-lg font-extrabold text-[#0F2347]">
                    <img
                        src="{{ asset('assets/conferences/icons/calendar.svg') }}"
                        alt=""
                        class="size-5"
                    >
                    Planning des créneaux
                </h2>
                <div class="flex flex-wrap gap-1.5"
                     role="group"
                     aria-label="Choisir une date">
                    @foreach ($days as $day)
                        <button
                            type="button"
                            data-day="{{ $day }}"
                            aria-pressed="{{ $defaultDay === $day ? 'true' : 'false' }}"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-blue-50 aria-pressed:border-blue-700 aria-pressed:bg-[#2563EB] aria-pressed:text-white"
                        >
                            {{ Carbon::parse($day)->locale('fr')->isoFormat('ddd D MMM YYYY') }}
                        </button>
                    @endforeach
                </div>
            </div>
            <p class="mt-2 text-xs text-slate-500">
                Les horaires proviennent de la base de données.
                Cliquez sur un créneau libre pour le sélectionner.
            </p>
            {{-- LEGENDE --}}
            <div class="mt-4 flex flex-wrap gap-3 text-xs font-semibold text-slate-600">
                <span><span class="text-emerald-600">●</span> Disponible</span>
                <span><span class="text-blue-600">●</span> Sélectionné</span>
                <span><span class="text-amber-500">●</span> Déjà demandé</span>
                <span><span class="text-slate-400">●</span> Réservé / indisponible</span>
            </div>
            {{-- JOURS --}}
            @forelse ($days as $day)
                <div
                    data-day-panel="{{ $day }}"
                    @class([
                        'mt-4',
                        'hidden' => $defaultDay !== $day,
                    ])
                >
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($spaces as $type => $spaceLabel)
                            @php
                                $daySlots = collect(
                                    ($planning[$type] ?? collect())[$day] ?? collect()
                                )->values();
                            @endphp
                            <div class="min-w-0 overflow-hidden rounded-xl border border-slate-200">
                                <div class="border-b border-slate-200 px-4 py-3 {{ $type === 'conference' ? 'bg-blue-50' : 'bg-emerald-50' }}">
                                    <p class="text-sm font-extrabold text-[#0F2347]">
                                        {{ $spaceLabel }}
                                    </p>
                                    <p class="text-xs text-slate-600">
                                        {{ $money($prices[$type]) }}
                                        · {{ $type === 'conference' ? '1 heure' : '2 heures' }}
                                    </p>
                                </div>
                                <div class="space-y-2 p-3">
                                    @forelse ($daySlots as $slot)
                                        @php
                                            $mine = in_array((int) $slot->id, $mineIds, true);
                                            $pending = in_array((int) $slot->id, $pendingIds, true);
                                            $free = $slot->status === SlotStatus::Available
                                                && ! $pending;
                                        @endphp
                                        @if ($free)
                                            <button
                                                type="button"
                                                data-select-slot="{{ $slot->id }}"
                                                data-slot-type="{{ $type }}"
                                                aria-pressed="false"
                                                class="flex w-full items-center justify-between gap-2 rounded-lg border border-emerald-200 bg-white px-3 py-2.5 text-left text-sm transition hover:border-blue-400 hover:bg-blue-50 focus-visible:ring-2 focus-visible:ring-blue-600 aria-pressed:border-blue-600 aria-pressed:bg-blue-50"
                                            >
                                                <span class="font-bold text-slate-800">
                                                    {{ $slot->time_range }}
                                                    <small class="font-medium text-slate-400">
                                                        {{ $slot->code }}
                                                    </small>
                                                </span>
                                                <span
                                                    data-slot-label
                                                    class="text-xs font-bold text-emerald-700"
                                                >
                                                    Disponible
                                                </span>
                                            </button>
                                        @else
                                            <div class="flex items-center justify-between gap-2 rounded-lg px-3 py-2.5 text-sm {{ $mine ? 'bg-indigo-50 text-indigo-700' : ($pending ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-500') }}">
                                                <span class="font-bold">
                                                    {{ $slot->time_range }}
                                                    <small class="font-medium opacity-60">
                                                        {{ $slot->code }}
                                                    </small>
                                                </span>
                                                <span class="text-xs font-bold">
                                                    {{ $mine
                                                        ? 'Votre créneau'
                                                        : ($pending ? 'Déjà demandé' : 'Réservé') }}
                                                </span>
                                            </div>
                                        @endif
                                    @empty
                                        <p class="py-4 text-center text-xs text-slate-400">
                                            Pas de créneaux ce jour.
                                        </p>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="mt-4 rounded-xl bg-slate-50 p-5 text-sm text-slate-500">
                    Aucun créneau configuré.
                </div>
            @endforelse
        </section>
{{-- MES DEMANDES --}}
    <section
        class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5"
        aria-labelledby="requests-heading"
    >
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 id="requests-heading"
                class="flex items-center gap-2 text-lg font-extrabold text-[#0F2347]">
                <img
                    src="{{ asset('assets/conferences/icons/calendar-check.svg') }}"
                    alt=""
                    class="size-5"
                >
                Mes réservations / demandes
            </h2>
            <div class="flex flex-wrap gap-2 text-xs font-bold">
                <button
                    type="button"
                    data-filter="all"
                    aria-pressed="true"
                    class="rounded-lg bg-blue-50 px-3 py-2 text-blue-700 aria-pressed:bg-[#2563EB] aria-pressed:text-white"
                >
                    Toutes ({{ $myRequests->count() }})
                </button>
                <button
                    type="button"
                    data-filter="pending"
                    aria-pressed="false"
                    class="rounded-lg bg-amber-50 px-3 py-2 text-amber-800 aria-pressed:ring-2 aria-pressed:ring-amber-500"
                >
                    En attente ({{ $myRequests->where('status', SlotRequestStatus::Pending)->count() }})
                </button>
                <button
                    type="button"
                    data-filter="accepted"
                    aria-pressed="false"
                    class="rounded-lg bg-emerald-50 px-3 py-2 text-emerald-800 aria-pressed:ring-2 aria-pressed:ring-emerald-500"
                >
                    Acceptées ({{ $myRequests->where('status', SlotRequestStatus::Accepted)->count() }})
                </button>
                <button
                    type="button"
                    data-filter="rejected"
                    aria-pressed="false"
                    class="rounded-lg bg-red-50 px-3 py-2 text-red-700 aria-pressed:ring-2 aria-pressed:ring-red-500"
                >
                    Refusées ({{ $myRequests->where('status', SlotRequestStatus::Rejected)->count() }})
                </button>
                <button
                    type="button"
                    data-filter="cancelled"
                    aria-pressed="false"
                    class="rounded-lg bg-slate-100 px-3 py-2 text-slate-700 aria-pressed:ring-2 aria-pressed:ring-slate-400"
                >
                    Annulées ({{ $myRequests->where('status', SlotRequestStatus::Cancelled)->count() }})
                </button>
            </div>
        </div>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[850px] text-left text-sm">
                <thead class="bg-slate-50 text-xs text-slate-600">
                    <tr>
                        <th class="rounded-l-lg px-3 py-3 font-bold">Réf.</th>
                        <th class="px-3 py-3 font-bold">Date</th>
                        <th class="px-3 py-3 font-bold">Horaire</th>
                        <th class="px-3 py-3 font-bold">Espace</th>
                        <th class="px-3 py-3 font-bold">Sujet</th>
                        <th class="px-3 py-3 font-bold">Choix</th>
                        <th class="rounded-r-lg px-3 py-3 font-bold">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($myRequests as $item)
                        @php
                            $displaySlot = $item->status === SlotRequestStatus::Accepted
                                && $item->assignedSlot
                                ? ($item->firstChoice?->id === $item->assignedSlot->id
                                    ? $item->firstChoice
                                    : $item->secondChoice)
                                : $item->firstChoice;
                        @endphp
                        <tr data-request-status="{{ $item->status->value }}">
                            <td class="px-3 py-3 font-bold text-slate-800">
                                DEM-{{ str_pad((string) $item->id, 4, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-3 py-3 text-slate-600">
                                {{ $displaySlot?->date?->locale('fr')->isoFormat('DD MMM YYYY') ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 text-slate-600">
                                {{ $displaySlot?->time_range ?? '—' }}
                            </td>
                            <td class="px-3 py-3 text-slate-600">
                                {{ $item->type->label() }}
                            </td>
                            <td class="px-3 py-3 font-medium text-slate-800">
                                {{ $item->title }}
                            </td>
                            <td class="px-3 py-3 text-xs text-slate-600">
                                <span class="block">
                                    1er : {{ $item->firstChoice?->code ?? '—' }}
                                </span>
                                <span class="block">
                                    2e : {{ $item->secondChoice?->code ?? 'Non renseigné' }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $item->status->pillClasses() }}">
                                    {{ $item->status->label() }}
                                </span>
                                @if (
                                    in_array($item->status, [SlotRequestStatus::Rejected, SlotRequestStatus::Cancelled], true)
                                    && $item->rejection_reason
                                )
                                    <p class="mt-2 max-w-52 text-xs {{ $item->status === SlotRequestStatus::Cancelled ? 'text-slate-600' : 'text-red-700' }}">
                                        {{ $item->rejection_reason }}
                                    </p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"
                                class="px-3 py-6 text-center text-sm text-slate-500">
                                Aucune demande pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
        </div>
        {{-- COLONNE DROITE : FORMULAIRE + CONFERENCE / CHAT --}}
        <div class="min-w-0 space-y-4">
        {{-- FORMULAIRE NOUVELLE DEMANDE --}}
        <section
            id="nouvelle-demande"
            class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            aria-labelledby="form-heading"
        >
            {{-- HEADER FORMULAIRE --}}
            <div class="border-b border-blue-100 bg-gradient-to-br from-blue-50 via-white to-white px-5 py-5 sm:px-6">
                <div class="flex items-start gap-3">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-[#2563EB] text-white shadow-sm">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="size-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            aria-hidden="true"
                        >
                            <path d="M12 5v14M5 12h14"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-blue-600">
                            Conférences & Lounge
                        </p>
                        <h2 id="form-heading"
                            class="mt-1 text-lg font-extrabold leading-tight text-[#0F2347]">
                            Nouvelle demande de réservation
                        </h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Choisissez votre créneau préféré et, si vous le
                            souhaitez, un second créneau en alternative.
                            Présentez ensuite votre intervention.
                        </p>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-600">
                    <span class="rounded-full bg-blue-100 px-2.5 py-1 text-blue-800">
                        1. Créneaux
                    </span>
                    <span class="h-px w-4 bg-slate-200"></span>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1">
                        2. Présentation
                    </span>
                    <span class="h-px w-4 bg-slate-200"></span>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1">
                        3. Envoi
                    </span>
                </div>
            </div>
            <form
                id="slot-request-form"
                method="POST"
                action="{{ route('exhibitor.slots.request') }}"
                class="space-y-4 p-4 sm:p-5"
            >
                @csrf
                {{-- ETAPE 1 --}}
                <fieldset class="space-y-2">
                    <legend class="flex items-center gap-2 text-sm font-extrabold text-[#0F2347]">
                        <span class="grid size-6 place-items-center rounded-full bg-blue-600 text-xs text-white">
                            1
                        </span>
                        Choisissez vos créneaux
                    </legend>
                    <div class="space-y-2">
                        {{-- ESPACE --}}
                        <div>
                            <label
                                for="request-type"
                                class="mb-1.5 block text-xs font-bold text-slate-700"
                            >
                                Espace
                                <span class="text-red-600">*</span>
                            </label>
                            <select
                                name="type"
                                id="request-type"
                                required
                                class="block w-full min-w-0 rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-800 focus:border-blue-500 focus:bg-white focus:ring-blue-500"
                            >
                                @foreach ($spaces as $type => $spaceLabel)
                                    <option
                                        value="{{ $type }}"
                                        @selected($selectedType === $type)
                                    >
                                        {{ $spaceLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <p class="mt-1 text-xs font-semibold text-red-700">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        {{-- PREMIER CHOIX OBLIGATOIRE --}}
                        <div>
                            <label
                                for="request-slot"
                                class="mb-1.5 block text-xs font-bold text-slate-700"
                            >
                                1er choix · Date et horaire
                                <span class="text-red-600">*</span>
                            </label>
                            <select
                                name="first_choice_slot_id"
                                id="request-slot"
                                required
                                class="block w-full min-w-0 rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-800 focus:border-blue-500 focus:bg-white focus:ring-blue-500"
                            >
                                <option value="">Choisir un créneau libre</option>
                                @foreach ($spaces as $type => $spaceLabel)
                                    @foreach (collect($availableSlots)->get($type, collect()) as $slot)
                                        @continue(in_array((int) $slot->id, $pendingIds, true))
                                        <option
                                            value="{{ $slot->id }}"
                                            data-option-type="{{ $type }}"
                                            data-option-day="{{ $slot->date->toDateString() }}"
                                            @selected($selectedSlot === (int) $slot->id)
                                        >
                                            {{ $slot->full_label }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                            @error('first_choice_slot_id')
                                <p class="mt-1 text-xs font-semibold text-red-700">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        {{-- DEUXIEME CHOIX FACULTATIF --}}
                        <div>
                            <label
                                for="request-second-slot"
                                class="mb-1.5 block text-xs font-bold text-slate-700"
                            >
                                2e choix · Autre créneau
                                <span class="ml-1 rounded-full bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-500">
                                    Facultatif
                                </span>
                            </label>
                            <select
                                name="second_choice_slot_id"
                                id="request-second-slot"
                                class="block w-full min-w-0 rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-800 focus:border-blue-500 focus:bg-white focus:ring-blue-500"
                            >
                                <option value="">
                                    Aucun deuxième choix
                                </option>
                                @foreach ($spaces as $type => $spaceLabel)
                                    @foreach (collect($availableSlots)->get($type, collect()) as $slot)
                                        @continue(in_array((int) $slot->id, $pendingIds, true))
                                        <option
                                            value="{{ $slot->id }}"
                                            data-option-type="{{ $type }}"
                                            data-option-day="{{ $slot->date->toDateString() }}"
                                            @selected($selectedSecondSlot === (int) $slot->id)
                                        >
                                            {{ $slot->full_label }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                            @error('second_choice_slot_id')
                                <p class="mt-1 text-xs font-semibold text-red-700">
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="mt-2 text-[11px] leading-5 text-slate-500">
                                Vous pouvez proposer un deuxième créneau
                                comme alternative. Ce choix n'est pas obligatoire.
                            </p>
                        </div>
                    </div>
                    <div class="rounded-xl border border-blue-100 bg-blue-50 px-3 py-3 text-xs leading-5 text-blue-900">
                        Cliquez sur un créneau disponible dans le planning
                        pour remplir votre premier choix.
                        Le deuxième choix reste facultatif.
                    </div>
                </fieldset>
                <div class="border-t border-slate-100"></div>
                {{-- ETAPE 2 --}}
                <fieldset class="space-y-3">
                    <legend class="flex items-center gap-2 text-sm font-extrabold text-[#0F2347]">
                        <span class="grid size-6 place-items-center rounded-full bg-blue-600 text-xs text-white">
                            2
                        </span>
                        Informations de la présentation
                    </legend>
                    {{-- TITRE --}}
                    <div>
                        <label
                            for="request-title"
                            class="mb-1.5 block text-xs font-bold text-slate-700"
                        >
                            Sujet de l'intervention
                            <span class="text-red-600">*</span>
                        </label>
                        <input
                            id="request-title"
                            name="title"
                            type="text"
                            required
                            maxlength="150"
                            value="{{ old('title') }}"
                            placeholder="Ex. : Innovations en réfrigération industrielle"
                            class="block w-full min-w-0 rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:bg-white focus:ring-blue-500"
                        >
                        @error('title')
                            <p class="mt-1 text-xs font-semibold text-red-700">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    {{-- RESUME --}}
                    <div>
                        <label
                            for="request-summary"
                            class="mb-1.5 flex items-center justify-between gap-2 text-xs font-bold text-slate-700"
                        >
                            <span>
                                Résumé de l'intervention
                                <span class="text-red-600">*</span>
                            </span>
                            <span data-lines-count class="font-medium text-slate-400">
                                0 / 5 lignes
                            </span>
                        </label>
                        <textarea
                            id="request-summary"
                            name="summary"
                            required
                            maxlength="800"
                            rows="5"
                            placeholder="Présentez votre sujet et les points abordés..."
                            class="block w-full resize-y rounded-xl border-slate-300 bg-slate-50 px-3 py-3 text-sm focus:border-blue-500 focus:bg-white focus:ring-blue-500"
                        >{{ old('summary') }}</textarea>
                        <p class="mt-1 text-[11px] text-slate-500">
                            5 lignes maximum · 800 caractères maximum.
                        </p>
                        @error('summary')
                            <p class="mt-1 text-xs font-semibold text-red-700">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    {{-- INTERVENANTS --}}
                    <div>
                        <label
                            for="request-speakers"
                            class="mb-1.5 block text-xs font-bold text-slate-700"
                        >
                            Intervenant(s)
                            <span class="text-red-600">*</span>
                        </label>
                        <input
                            id="request-speakers"
                            name="speakers"
                            type="text"
                            required
                            maxlength="500"
                            value="{{ old('speakers') }}"
                            placeholder="Nom, prénom et fonction"
                            class="block w-full min-w-0 rounded-xl border-slate-300 bg-slate-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:bg-white focus:ring-blue-500"
                        >
                        @error('speakers')
                            <p class="mt-1 text-xs font-semibold text-red-700">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </fieldset>
                {{-- INFORMATION CONFIRMATION --}}
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-4">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 text-lg font-bold text-emerald-600">
                            ✓
                        </span>
                        <div>
                            <p class="text-xs font-extrabold text-emerald-900">
                                Après validation par le Responsable Conférences
                            </p>
                            <p class="mt-1 text-xs leading-5 text-emerald-900">
                                L'organisation prévoit une réponse par e-mail
                                sous 48 heures.
                                Après confirmation, envoyez votre support
                                PPT ou PDF au format 16:9, 20 Mo maximum,
                                au plus tard le 26 janvier 2027.
                                Présentez-vous 15 minutes avant votre créneau.
                            </p>
                        </div>
                    </div>
                </div>
                {{-- SUBMIT --}}
                <button
                    type="submit"
                    @disabled($freeCount < 1)
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                >
                    <img
                        src="{{ asset('assets/conferences/icons/send.svg') }}"
                        alt=""
                        aria-hidden="true"
                        class="size-4 brightness-0 invert"
                    >
                    Envoyer ma demande
                </button>
                <p class="text-center text-[11px] leading-4 text-slate-400">
                    L'envoi d'une demande ne constitue pas
                    une réservation confirmée.
                </p>
            </form>
        </section>
    <section id="conference-followup" class="min-w-0 overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm" aria-labelledby="conference-followup-title">
        <div class="border-b border-emerald-100 bg-gradient-to-r from-emerald-50 to-white px-4 py-4 sm:px-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="conference-followup-title" class="text-base font-extrabold text-[#0F2347]">Ma conférence</h2>
                @if($selectedConference)
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Confirmée</span>
                @endif
            </div>
            @if($confirmedRequests->count() > 1)
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($confirmedRequests as $choice)
                        <a href="{{ route('exhibitor.slots.index', ['conference' => $choice->id]) }}#conference-followup"
                           @class(['rounded-lg border px-3 py-1.5 text-xs font-semibold transition', 'border-blue-600 bg-blue-600 text-white' => $selectedConference?->id === $choice->id, 'border-slate-200 bg-white text-slate-600 hover:bg-blue-50' => $selectedConference?->id !== $choice->id])>
                            DEM-{{ str_pad((string) $choice->id, 4, '0', STR_PAD_LEFT) }}
                        </a>
                    @endforeach
                </div>
            @endif
            @if($selectedConference)
                <p class="mt-2 truncate text-sm font-bold text-slate-800">{{ $selectedConference->title }}</p>
                <p class="mt-1 text-xs font-medium text-slate-500">
                    {{ $selectedConference->assignedSlot?->date?->locale('fr')->isoFormat('D MMM YYYY') }}
                    · {{ $selectedConference->assignedSlot?->time_range }}
                    · {{ $selectedConference->assignedSlot?->space?->type?->label() }}
                </p>
            @else
                <p class="mt-2 text-sm text-slate-600">Après validation de votre réservation, vous pourrez transmettre votre support et discuter avec le Responsable Conférences ici.</p>
            @endif
        </div>
        @if($selectedConference)
            @php
                $confirmedSlot = $selectedConference->assignedSlot;
                $supportState = $selectedConference->support_review_status ?? 'not_submitted';
                [$supportLabel, $supportColor] = $supportLabels[$supportState] ?? $supportLabels['not_submitted'];
            @endphp
            <div class="grid gap-3 p-3 sm:p-4 2xl:grid-cols-2">
                <div class="min-w-0 rounded-xl border border-slate-200 p-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-sm font-extrabold text-[#0F2347]">Support de présentation</h3>
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $supportColor }}">{{ $supportLabel }}</span>
                    </div>
                    @if($confirmedSlot?->support_path)
                        <div class="mt-3 rounded-lg bg-slate-50 p-3">
                            <p class="break-all text-xs font-bold text-slate-800">{{ $confirmedSlot->support_original_name ?? 'Support transmis' }}</p>
                            <p class="mt-1 text-[11px] text-slate-500">{{ $confirmedSlot->support_uploaded_at?->format('d/m/Y H:i') }}</p>
                            <a href="{{ route('exhibitor.conferences.collaboration.download', $selectedConference) }}" class="mt-2 inline-block text-xs font-bold text-blue-700 underline">Télécharger</a>
                        </div>
                    @else
                        <p class="mt-3 rounded-lg border border-dashed border-slate-200 bg-slate-50 p-3 text-xs text-slate-500">Aucun fichier envoyé. PPT, PPTX ou PDF, 20 Mo max.</p>
                    @endif
                    @if($selectedConference->support_details)
                        <div class="mt-3 rounded-lg bg-slate-50 p-3">
                            <p class="text-[11px] font-bold text-slate-600">Détails de votre intervention</p>
                            <p class="mt-1 whitespace-pre-line break-words text-xs text-slate-700">{{ $selectedConference->support_details }}</p>
                        </div>
                    @endif
                    @if($selectedConference->support_review_note)
                        <p class="mt-3 rounded-lg bg-amber-50 p-3 text-xs text-amber-900">{{ $selectedConference->support_review_note }}</p>
                    @endif
                    @if(in_array($supportState, ['not_submitted', 'revision_requested'], true))
                        <form method="POST" enctype="multipart/form-data" action="{{ route('exhibitor.conferences.collaboration.support', $selectedConference) }}" class="mt-3 space-y-2">
                            @csrf
                            <label class="block text-xs font-bold text-slate-700" for="conference-support-file">{{ $confirmedSlot?->support_path ? 'Remplacer le fichier' : 'Ajouter le support' }}</label>
                            <input id="conference-support-file" name="support" type="file" accept=".pdf,.ppt,.pptx" required class="block w-full rounded-lg border border-slate-200 p-2 text-xs file:mr-2 file:rounded-md file:border-0 file:bg-blue-50 file:px-2 file:py-1.5 file:font-bold file:text-blue-700">
                            <label class="block text-xs font-bold text-slate-700" for="conference-support-details">Description détaillée</label>
                            <textarea id="conference-support-details" name="support_details" rows="3" minlength="10" maxlength="5000" required placeholder="Les thèmes abordés, démonstrations et besoins techniques..." class="block w-full rounded-lg border-slate-300 px-3 py-2 text-xs">{{ old('support_details', $selectedConference->support_details) }}</textarea>
                            <button class="w-full rounded-lg bg-blue-600 px-3 py-2.5 text-xs font-bold text-white hover:bg-blue-700">{{ $confirmedSlot?->support_path ? 'Envoyer une nouvelle version' : 'Envoyer le support' }}</button>
                        </form>
                    @else
                        @if($supportState === 'approved')
                            <p class="mt-3 rounded-lg bg-emerald-50 p-3 text-xs font-semibold text-emerald-800">Votre support est approuvé.</p>
                        @else
                            <p class="mt-3 rounded-lg bg-amber-50 p-3 text-xs font-semibold text-amber-900">Votre support est en cours de vérification. Vous pourrez le remplacer si une modification est demandée.</p>
                        @endif
                    @endif
                </div>
                <div class="flex min-w-0 flex-col rounded-xl border border-slate-200">
                    <div class="border-b border-slate-100 p-3">
                        <h3 class="text-sm font-extrabold text-[#0F2347]">Messagerie privée</h3>
                        <p class="mt-1 text-[11px] text-slate-500">Avec le Responsable Conférences</p>
                    </div>
                    <div class="max-h-[290px] min-h-44 flex-1 space-y-2 overflow-y-auto p-3" aria-label="Messages de la conférence">
                        @forelse($selectedConference->conferenceMessages->sortBy('id')->take(-15) as $message)
                            @php $mine = (int) $message->sender_id === (int) auth()->id(); @endphp
                            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[90%] rounded-xl px-3 py-2 {{ $mine ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-800' }}">
                                    <p class="text-[11px] font-bold">{{ $mine ? 'Vous' : ($message->sender?->name ?? 'Responsable Conférences') }}</p>
                                    <p class="mt-1 whitespace-pre-wrap break-words text-xs">{{ $message->body }}</p>
                                    <p class="mt-1 text-[10px] {{ $mine ? 'text-blue-100' : 'text-slate-500' }}">{{ $message->created_at?->format('d/m H:i') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="py-12 text-center text-xs text-slate-400">Aucun message. Commencez la conversation ici.</p>
                        @endforelse
                    </div>
                    <form method="POST" action="{{ route('exhibitor.conferences.collaboration.message', $selectedConference) }}" class="space-y-2 border-t border-slate-100 p-3">
                        @csrf
                        <label class="sr-only" for="conference-chat-body">Écrire un message</label>
                        <textarea id="conference-chat-body" name="body" rows="2" maxlength="4000" required placeholder="Écrire un message..." class="block w-full rounded-lg border-slate-300 p-2 text-xs"></textarea>
                        <button type="submit" class="w-full rounded-lg bg-blue-600 px-3 py-2.5 text-xs font-bold text-white hover:bg-blue-700">Envoyer le message</button>
                    </form>
                </div>
            </div>
        @endif
    </section>
        </div>
    </div>
</div>
{{-- JAVASCRIPT --}}
<script>
(() => {
    const root = document.getElementById('exhibitor-conferences');
    if (!root) return;
    const typeSelect = document.getElementById('request-type');
    const slotSelect = document.getElementById('request-slot');
    const secondSlotSelect = document.getElementById('request-second-slot');
    const dayButtons = [...root.querySelectorAll('[data-day]')];
    const dayPanels = [...root.querySelectorAll('[data-day-panel]')];
    const choiceButtons = [...root.querySelectorAll('[data-select-slot]')];
    const filterButtons = [...root.querySelectorAll('[data-filter]')];
    const requestRows = [...root.querySelectorAll('[data-request-status]')];
    const showDay = (day) => {
        dayButtons.forEach(button => {
            button.setAttribute(
                'aria-pressed',
                String(button.dataset.day === day)
            );
        });
        dayPanels.forEach(panel => {
            panel.classList.toggle(
                'hidden',
                panel.dataset.dayPanel !== day
            );
        });
    };
    const renderSlots = () => {
        const type = typeSelect.value;
        [slotSelect, secondSlotSelect].forEach(select => {
            const option = select.selectedOptions[0];
            if (
                option?.dataset.optionType &&
                option.dataset.optionType !== type
            ) {
                select.value = '';
            }
        });
        if (
            slotSelect.value &&
            slotSelect.value === secondSlotSelect.value
        ) {
            secondSlotSelect.value = '';
        }
        [slotSelect, secondSlotSelect].forEach(select => {
            [...select.options].forEach(option => {
                if (!option.dataset.optionType) return;
                const wrongType = option.dataset.optionType !== type;
                const duplicate = select === slotSelect
                    ? option.value === secondSlotSelect.value
                    : option.value === slotSelect.value;
                option.hidden = wrongType || duplicate;
                option.disabled = wrongType || duplicate;
            });
        });
        choiceButtons.forEach(button => {
            const selected =
                button.dataset.selectSlot === slotSelect.value;
            button.setAttribute('aria-pressed', String(selected));
            const label = button.querySelector('[data-slot-label]');
            if (label) {
                label.textContent = selected
                    ? '✓ Sélectionné'
                    : 'Disponible';
            }
        });
    };
    dayButtons.forEach(button => {
        button.addEventListener('click', () => {
            showDay(button.dataset.day);
        });
    });
    choiceButtons.forEach(button => {
        button.addEventListener('click', () => {
            const id = button.dataset.selectSlot;
            typeSelect.value = button.dataset.slotType;
            slotSelect.value = id;
            if (secondSlotSelect.value === id) {
                secondSlotSelect.value = '';
            }
            renderSlots();
            document.getElementById('nouvelle-demande')
                .scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
        });
    });
    typeSelect.addEventListener('change', () => {
        slotSelect.value = '';
        secondSlotSelect.value = '';
        renderSlots();
    });
    slotSelect.addEventListener('change', () => {
        if (secondSlotSelect.value === slotSelect.value) {
            secondSlotSelect.value = '';
        }
        renderSlots();
        const picked = slotSelect.selectedOptions[0];
        if (picked?.dataset.optionDay) {
            showDay(picked.dataset.optionDay);
        }
    });
    secondSlotSelect.addEventListener('change', () => {
        if (secondSlotSelect.value === slotSelect.value) {
            secondSlotSelect.value = '';
        }
        renderSlots();
    });
    *// Filter requests by status.*
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(el => {
                el.setAttribute(
                    'aria-pressed',
                    String(el === button)
                );
            });
            requestRows.forEach(row => {
                const hide =
                    button.dataset.filter !== 'all' &&
                    row.dataset.requestStatus !== button.dataset.filter;
                row.classList.toggle('hidden', hide);
            });
        });
    });
    *// Maximum 5 summary lines.*
    const summary = document.getElementById('request-summary');
    const counter = root.querySelector('[data-lines-count]');
    const updateLines = () => {
        if (!summary || !counter) return;
        const lines = summary.value.trim()
            ? summary.value.trim().split(/\r?\n/).length
            : 0;
        counter.textContent = `${lines} / 5 lignes`;
        counter.classList.toggle(
            'text-red-700',
            lines > 5
        );
        summary.setCustomValidity(
            lines > 5
                ? 'Le résumé ne doit pas dépasser 5 lignes.'
                : ''
        );
    };
    summary?.addEventListener('input', updateLines);
    updateLines();
    renderSlots();
    const initialSelected = slotSelect.selectedOptions[0];
    if (initialSelected?.dataset.optionDay) {
        showDay(initialSelected.dataset.optionDay);
    }
})();
</script>
</x-exhibitor.layout>
