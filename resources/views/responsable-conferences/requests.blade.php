
<x-responsable-conferences.layout :user="auth()->user()">

@php
    $tabs = [
        [
            'key' => 'pending',
            'label' => 'En attente',
            'count' => $pendingCount,
            'icon' => 'calendar',
        ],
        [
            'key' => 'accepted',
            'label' => 'Validées',
            'count' => $acceptedCount,
            'icon' => 'clipboard-check',
        ],
        [
            'key' => 'rejected',
            'label' => 'Refusées',
            'count' => $rejectedCount,
            'icon' => 'inbox',
        ],
        [
            'key' => 'all',
            'label' => 'Toutes les demandes',
            'count' => $totalCount,
            'icon' => 'overview',
        ],
    ];

    $statistics = [
        [
            'label' => 'En attente',
            'value' => $pendingCount,
            'icon' => 'calendar',
            'background' => 'bg-amber-100',
            'color' => 'text-amber-600',
        ],
        [
            'label' => 'Validées',
            'value' => $acceptedCount,
            'icon' => 'clipboard-check',
            'background' => 'bg-emerald-100',
            'color' => 'text-emerald-600',
        ],
        [
            'label' => 'Refusées',
            'value' => $rejectedCount,
            'icon' => 'inbox',
            'background' => 'bg-red-100',
            'color' => 'text-red-600',
        ],
        [
            'label' => 'Total des demandes',
            'value' => $totalCount,
            'icon' => 'calendar',
            'background' => 'bg-blue-100',
            'color' => 'text-blue-600',
        ],
    ];

    $pendingStatus = \App\Enums\SlotRequestStatus::Pending;
    $availableStatus = \App\Enums\SlotStatus::Available;
@endphp

<div class="space-y-6">

    {{-- PAGE HEADER --}}

    <section>
        <p class="text-xs font-bold text-blue-600">
            Conférences & Lounge
            <span class="mx-2 text-slate-300">/</span>
            Demandes de réservation
        </p>

        <div class="mt-3 flex items-center gap-4">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <x-admin.icon name="calendar" class="size-7"/>
            </span>

            <div>
                <h1 class="text-2xl font-black tracking-tight text-[#0F2347] sm:text-3xl">
                    Demandes de réservation
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Examinez les demandes de réservation
                    et validez ou refusez les créneaux.
                </p>
            </div>
        </div>
    </section>

    {{-- SUCCESS MESSAGE --}}

    @if(session('status'))
        <div role="status"
             class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">

            {{ session('status') }}
        </div>
    @endif

    {{-- VALIDATION ERRORS --}}

    @if($errors->any())
        <div role="alert"
             class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">

            <p class="font-bold">
                Une erreur est survenue :
            </p>

            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- STATISTICS --}}

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach($statistics as $stat)

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-start justify-between">

                    <span class="grid size-12 place-items-center rounded-xl {{ $stat['background'] }} {{ $stat['color'] }}">

                        <x-admin.icon
                            :name="$stat['icon']"
                            class="size-6"
                        />

                    </span>

                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500">
                        REFRIGAIR 2027
                    </span>
                </div>

                <p class="mt-5 text-sm font-bold text-[#0F2347]">
                    {{ $stat['label'] }}
                </p>

                <p class="mt-2 text-3xl font-black text-[#0F2347]">
                    {{ $stat['value'] }}
                </p>
            </article>

        @endforeach
    </section>

    {{-- REQUESTS CONTAINER --}}

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- STATUS TABS --}}

        <nav class="grid gap-2 border-b border-slate-100 p-4 sm:grid-cols-2 xl:grid-cols-4"
             aria-label="Statuts des demandes">

            @foreach($tabs as $tab)

                <a href="{{ route('responsable-conferences.requests.index', array_merge(request()->except('status', 'page'), ['status' => $tab['key']])) }}"
                   @if($status === $tab['key']) aria-current="page" @endif
                   @class([
                       'flex items-center justify-center gap-2 rounded-xl border px-4 py-3 text-sm font-bold transition',
                       'border-[#0F2347] bg-[#0F2347] text-white' => $status === $tab['key'],
                       'border-slate-200 bg-white text-[#0F2347] hover:border-blue-400 hover:bg-blue-50' => $status !== $tab['key'],
                   ])>

                    <x-admin.icon
                        :name="$tab['icon']"
                        class="size-5"
                    />

                    {{ $tab['label'] }}
                    ({{ $tab['count'] }})
                </a>

            @endforeach
        </nav>

        {{-- FILTERS --}}

        <form method="GET"
              action="{{ route('responsable-conferences.requests.index') }}"
              class="border-b border-slate-100 bg-[#F1F6FD] p-4">

            <input type="hidden"
                   name="status"
                   value="{{ $status }}">

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1.4fr_1fr_1fr_1fr_auto]">

                {{-- SEARCH --}}

                <div>
                    <label for="request-search"
                           class="mb-2 block text-xs font-bold text-[#0F2347]">

                        Rechercher
                    </label>

                    <input
                        id="request-search"
                        name="search"
                        type="search"
                        maxlength="150"
                        value="{{ $search }}"
                        placeholder="Entreprise, sujet, contact..."
                        class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm focus:border-blue-500 focus:ring-blue-200"
                    >
                </div>

                {{-- TYPE --}}

                <div>
                    <label for="request-type"
                           class="mb-2 block text-xs font-bold text-[#0F2347]">

                        Type
                    </label>

                    <select id="request-type"
                            name="type"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm focus:border-blue-500">

                        <option value="">Tous les types</option>

                        <option value="conference"
                                @selected($type === 'conference')>

                            Conférence
                        </option>

                        <option value="lounge"
                                @selected($type === 'lounge')>

                            Lounge
                        </option>
                    </select>
                </div>

                {{-- SPACE --}}

                <div>
                    <label for="request-space"
                           class="mb-2 block text-xs font-bold text-[#0F2347]">

                        Espace demandé
                    </label>

                    <select id="request-space"
                            name="space_id"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm focus:border-blue-500">

                        <option value="">Tous les espaces</option>

                        @foreach($spaces as $space)

                            <option value="{{ $space->id }}"
                                    @selected((string) $spaceId === (string) $space->id)>

                                {{ $space->name ?: $space->type->label() }}
                            </option>

                        @endforeach
                    </select>
                </div>

                {{-- PERIOD --}}

                <div>
                    <label for="request-period"
                           class="mb-2 block text-xs font-bold text-[#0F2347]">

                        Période de demande
                    </label>

                    <select id="request-period"
                            name="period"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm focus:border-blue-500">

                        <option value="all"
                                @selected($period === 'all')>

                            Toutes les dates
                        </option>

                        <option value="7d"
                                @selected($period === '7d')>

                            7 derniers jours
                        </option>

                        <option value="30d"
                                @selected($period === '30d')>

                            30 derniers jours
                        </option>
                    </select>
                </div>

                {{-- FILTER ACTIONS --}}

                <div class="flex items-end gap-2 md:col-span-2 xl:col-span-1">

                    <button type="submit"
                            class="h-11 rounded-xl bg-[#2563EB] px-4 text-xs font-bold text-white hover:bg-blue-700">

                        Filtrer
                    </button>

                    <a href="{{ route('responsable-conferences.requests.index', ['status' => $status]) }}"
                       class="inline-flex h-11 items-center rounded-xl border border-blue-200 bg-white px-3 text-xs font-bold text-blue-700 hover:bg-blue-50">

                        Effacer
                    </a>
                </div>
            </div>
        </form>

        {{-- REQUESTS TABLE --}}

        <div class="overflow-x-auto">

            <table class="w-full min-w-[1250px] text-left text-sm">

                <thead class="bg-[#EEF4FC] text-xs font-extrabold text-[#0F2347]">

                    <tr>
                        <th scope="col" class="px-4 py-4">#</th>
                        <th scope="col" class="px-4 py-4">Entreprise</th>
                        <th scope="col" class="px-4 py-4">Sujet de la conférence</th>
                        <th scope="col" class="px-4 py-4">Créneaux demandés</th>
                        <th scope="col" class="px-4 py-4">Date de demande</th>
                        <th scope="col" class="px-4 py-4">Statut</th>
                        <th scope="col" class="px-4 py-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($requests as $slotRequest)

                        @php
                            $choices = collect([
                                $slotRequest->firstChoice,
                                $slotRequest->secondChoice,
                            ])->filter()->unique('id');

                            $availableChoices = $choices->filter(
                                fn ($slot) => $slot->status === $availableStatus
                            );

                            $isPending = $slotRequest->status === $pendingStatus;

                            $statusClasses = $slotRequest->status->pillClasses();

                            $detailsId = 'request-details-'.$slotRequest->id;
                            $acceptId = 'request-accept-'.$slotRequest->id;
                            $rejectId = 'request-reject-'.$slotRequest->id;
                        @endphp

                        <tr class="transition hover:bg-blue-50/30">

                            {{-- REFERENCE --}}

                            <td class="px-4 py-5 align-middle">

                                <span class="whitespace-nowrap rounded-lg bg-blue-50 px-2.5 py-1.5 text-xs font-extrabold text-[#0F2347]">

                                    #DR-{{ str_pad((string) $slotRequest->id, 4, '0', STR_PAD_LEFT) }}

                                </span>
                            </td>

                            {{-- COMPANY LOGO --}}

                            <td class="px-4 py-5 align-middle">

                                <div class="flex items-center gap-3">

                                    <div class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                                        @if($slotRequest->exhibitor?->logo_url)

                                            <img
                                                src="{{ $slotRequest->exhibitor->logo_url }}"
                                                alt="Logo {{ $slotRequest->exhibitor->company_name }}"
                                                loading="lazy"
                                                class="h-full w-full object-contain p-1"
                                            >

                                        @else

                                            <span class="text-xs font-black text-blue-700">
                                                {{ $slotRequest->exhibitor?->initials ?? 'EX' }}
                                            </span>

                                        @endif
                                    </div>

                                    <div class="min-w-0">

                                        <p class="max-w-44 truncate font-extrabold text-[#0F2347]">
                                            {{ $slotRequest->exhibitor?->company_name ?? 'Exposant' }}
                                        </p>

                                        <p class="mt-1 max-w-44 truncate text-xs text-slate-500">
                                            {{ $slotRequest->exhibitor?->commercial?->name ?? 'Sans commercial' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- CONFERENCE --}}

                            <td class="px-4 py-5 align-middle">

                                <p class="max-w-52 line-clamp-2 font-bold leading-5 text-[#0F2347]">
                                    {{ $slotRequest->title }}
                                </p>

                                <span @class([
                                    'mt-2 inline-flex rounded-lg px-2.5 py-1 text-[11px] font-bold',
                                    'bg-blue-100 text-blue-700' => $slotRequest->type->value === 'conference',
                                    'bg-violet-100 text-violet-700' => $slotRequest->type->value === 'lounge',
                                ])>

                                    {{ $slotRequest->type->value === 'conference' ? 'Conférence' : 'Lounge' }}

                                </span>
                            </td>

                            {{-- CHOICES --}}

                            <td class="px-4 py-5 align-middle">

                                <div class="space-y-2">

                                    @forelse($choices as $slot)

                                        <div class="flex items-center gap-2">

                                            <span class="rounded-lg bg-blue-50 px-2 py-1 text-[11px] font-bold text-[#0F2347]">
                                                {{ $slot->code }}
                                            </span>

                                            <span class="whitespace-nowrap text-[11px] text-slate-600">
                                                {{ $slot->date->format('d/m/Y') }}
                                                · {{ $slot->time_range }}
                                            </span>
                                        </div>

                                    @empty

                                        <span class="text-xs text-slate-400">
                                            Aucun créneau
                                        </span>

                                    @endforelse

                                </div>
                            </td>

                            {{-- CREATED DATE --}}

                            <td class="whitespace-nowrap px-4 py-5 align-middle">

                                <p class="font-semibold text-[#0F2347]">
                                    {{ $slotRequest->created_at->format('d/m/Y') }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $slotRequest->created_at->format('H:i') }}
                                </p>
                            </td>

                            {{-- STATUS --}}

                            <td class="px-4 py-5 align-middle">

                                <span class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-bold {{ $statusClasses }}">

                                    {{ $slotRequest->status->label() }}
                                </span>
                            </td>

                            {{-- ACTIONS --}}

                            <td class="px-4 py-5 align-middle">

                                <div class="flex items-center justify-end gap-2">

                                    @if($isPending)

                                        <button type="button"
                                                data-dialog-open="{{ $acceptId }}"
                                                @disabled($availableChoices->isEmpty())
                                                class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-40">

                                            ✓ Valider
                                        </button>

                                        <button type="button"
                                                data-dialog-open="{{ $rejectId }}"
                                                class="rounded-lg bg-red-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-red-700">

                                            × Refuser
                                        </button>

                                    @endif

                                    <button type="button"
                                            data-dialog-open="{{ $detailsId }}"
                                            class="rounded-lg border border-blue-200 bg-white px-3 py-2 text-xs font-bold text-blue-700 transition hover:bg-blue-50">

                                        Détails
                                    </button>
                                </div>

                                @if($isPending && $availableChoices->isEmpty())

                                    <p class="mt-2 text-right text-[11px] text-amber-700">
                                        Choix indisponibles
                                    </p>

                                @endif
                            
                        

                        {{-- DETAILS MODAL --}}

                        <dialog id="{{ $detailsId }}"
                                aria-labelledby="{{ $detailsId }}-title"
                                class="request-dialog m-auto w-[calc(100%-2rem)] max-w-2xl rounded-2xl border-0 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/60">

                            <div class="border-b border-slate-100 px-6 py-5">

                                <div class="flex items-start justify-between gap-4">

                                    <div>
                                        <p class="text-xs font-bold text-blue-600">
                                            Demande #DR-{{ str_pad((string) $slotRequest->id, 4, '0', STR_PAD_LEFT) }}
                                        </p>

                                        <h2 id="{{ $detailsId }}-title"
                                            class="mt-2 text-xl font-black text-[#0F2347]">

                                            Détails de réservation
                                        </h2>
                                    </div>

                                    <button type="button"
                                            data-dialog-close
                                            class="rounded-lg px-3 py-2 text-slate-500 hover:bg-slate-100"
                                            aria-label="Fermer">

                                        ✕
                                    </button>
                                </div>
                            </div>

                            <div class="max-h-[70vh] space-y-5 overflow-y-auto p-6">

                                <div class="flex items-center gap-3">

                                    <div class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white">

                                        @if($slotRequest->exhibitor?->logo_url)
                                            <img
                                                src="{{ $slotRequest->exhibitor->logo_url }}"
                                                alt=""
                                                class="h-full w-full object-contain p-1.5"
                                            >
                                        @else
                                            <span class="font-black text-blue-700">
                                                {{ $slotRequest->exhibitor?->initials ?? 'EX' }}
                                            </span>
                                        @endif
                                    </div>

                                    <div>
                                        <p class="font-extrabold text-[#0F2347]">
                                            {{ $slotRequest->exhibitor?->company_name ?? 'Exposant' }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            Commercial :
                                            {{ $slotRequest->exhibitor?->commercial?->name ?? '—' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="rounded-xl bg-slate-50 p-4">

                                    <p class="text-xs font-bold uppercase text-slate-500">
                                        Sujet
                                    </p>

                                    <p class="mt-2 font-bold text-[#0F2347]">
                                        {{ $slotRequest->title }}
                                    </p>

                                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">
                                        {{ $slotRequest->summary ?: 'Aucune description fournie.' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-bold uppercase text-slate-500">
                                        Intervenants
                                    </p>

                                    <p class="mt-2 whitespace-pre-line text-sm text-slate-700">
                                        {{ $slotRequest->speakers ?: 'Non renseignés' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="mb-3 text-sm font-bold text-[#0F2347]">
                                        Créneaux demandés
                                    </p>

                                    <div class="grid gap-3 sm:grid-cols-2">

                                        @foreach($choices as $slot)

                                            <div class="rounded-xl border border-slate-200 p-4">

                                                <p class="text-xs font-bold text-blue-600">
                                                    {{ $loop->first ? 'Premier choix' : 'Deuxième choix' }}
                                                </p>

                                                <p class="mt-2 font-extrabold text-[#0F2347]">
                                                    {{ $slot->code }}
                                                </p>

                                                <p class="mt-1 text-xs text-slate-600">
                                                    {{ $slot->date->format('d/m/Y') }}
                                                    · {{ $slot->time_range }}
                                                </p>

                                                <p class="mt-2 text-xs font-bold {{ $slot->status === $availableStatus ? 'text-emerald-600' : 'text-amber-600' }}">
                                                    {{ $slot->status->label() }}
                                                </p>
                                            </div>

                                        @endforeach
                                    </div>
                                </div>

                                @unless($isPending)

                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">

                                        <p class="font-bold text-[#0F2347]">
                                            Décision enregistrée
                                        </p>

                                        <p class="mt-2 text-slate-600">
                                            Par {{ $slotRequest->processor?->name ?? '—' }}
                                            · {{ $slotRequest->processed_at?->format('d/m/Y à H:i') ?? '—' }}
                                        </p>

                                        @if($slotRequest->assignedSlot)

                                            <p class="mt-2">
                                                Créneau attribué :
                                                <strong>{{ $slotRequest->assignedSlot->code }}</strong>
                                            </p>

                                        @endif

                                        @if($slotRequest->rejection_reason)

                                            <p class="mt-2 whitespace-pre-line">
                                                Motif : {{ $slotRequest->rejection_reason }}
                                            </p>

                                        @endif
                                    </div>

                                @endunless

                            </div>

                            <div class="flex justify-end border-t border-slate-100 p-5">

                                <button type="button"
                                        data-dialog-close
                                        class="rounded-xl bg-[#0F2347] px-5 py-3 text-sm font-bold text-white">

                                    Fermer
                                </button>
                            </div>
                        </dialog>

                        {{-- ACCEPT MODAL --}}

                        @if($isPending)

                            <dialog id="{{ $acceptId }}"
                                    aria-labelledby="{{ $acceptId }}-title"
                                    class="request-dialog m-auto w-[calc(100%-2rem)] max-w-lg rounded-2xl border-0 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/60">

                                <form method="POST"
                                      action="{{ route('responsable-conferences.requests.accept', $slotRequest) }}">

                                    @csrf
                                    @method('PATCH')

                                    <div class="border-b border-slate-100 px-6 py-5">

                                        <h2 id="{{ $acceptId }}-title"
                                            class="text-xl font-black text-[#0F2347]">

                                            Confirmer la réservation
                                        </h2>

                                        <p class="mt-2 text-sm text-slate-500">
                                            {{ $slotRequest->exhibitor?->company_name }}
                                        </p>
                                    </div>

                                    <div class="space-y-4 p-6">

                                        <p class="text-sm leading-6 text-slate-600">
                                            Sélectionnez le créneau à attribuer.
                                            Sa disponibilité sera vérifiée
                                            à nouveau lors de la confirmation.
                                        </p>

                                        <label for="slot-choice-{{ $slotRequest->id }}"
                                               class="block text-sm font-bold text-[#0F2347]">

                                            Créneau disponible
                                        </label>

                                        <select
                                            id="slot-choice-{{ $slotRequest->id }}"
                                            name="slot_id"
                                            required
                                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500">

                                            <option value="">Sélectionner un créneau</option>

                                            @foreach($availableChoices as $slot)

                                                <option value="{{ $slot->id }}">

                                                    {{ $slot->code }} ·
                                                    {{ $slot->date->format('d/m/Y') }} ·
                                                    {{ $slot->time_range }}

                                                </option>

                                            @endforeach
                                        </select>

                                        @if($availableChoices->isEmpty())

                                            <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-700">

                                                Aucun choix disponible.
                                                La demande doit rester en attente
                                                ou être refusée avec un motif.
                                            </p>

                                        @endif
                                    </div>

                                    <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 p-5">

                                        <button type="button"
                                                data-dialog-close
                                                class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600">

                                            Annuler
                                        </button>

                                        <button type="submit"
                                                @disabled($availableChoices->isEmpty())
                                                class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-40">

                                            Confirmer la réservation
                                        </button>
                                    </div>
                                </form>
                            </dialog>

                            {{-- REJECT MODAL --}}

                            <dialog id="{{ $rejectId }}"
                                    aria-labelledby="{{ $rejectId }}-title"
                                    class="request-dialog m-auto w-[calc(100%-2rem)] max-w-lg rounded-2xl border-0 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/60">

                                <form method="POST"
                                      action="{{ route('responsable-conferences.requests.reject', $slotRequest) }}">

                                    @csrf
                                    @method('PATCH')

                                    <div class="border-b border-slate-100 px-6 py-5">

                                        <h2 id="{{ $rejectId }}-title"
                                            class="text-xl font-black text-[#0F2347]">

                                            Refuser la demande
                                        </h2>

                                        <p class="mt-2 text-sm text-slate-500">
                                            {{ $slotRequest->exhibitor?->company_name }}
                                        </p>
                                    </div>

                                    <div class="space-y-4 p-6">

                                        <p class="text-sm text-slate-600">
                                            Indiquez le motif du refus.
                                            Il sera enregistré dans l'historique.
                                        </p>

                                        <label for="rejection-reason-{{ $slotRequest->id }}"
                                               class="block text-sm font-bold text-[#0F2347]">

                                            Motif du refus
                                            <span class="text-red-600">*</span>
                                        </label>

                                        <textarea
                                            id="rejection-reason-{{ $slotRequest->id }}"
                                            name="rejection_reason"
                                            rows="5"
                                            maxlength="1000"
                                            required
                                            placeholder="Expliquez pourquoi la demande est refusée..."
                                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-red-500"></textarea>
                                    </div>

                                    <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 p-5">

                                        <button type="button"
                                                data-dialog-close
                                                class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600">

                                            Annuler
                                        </button>

                                        <button type="submit"
                                                class="rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white hover:bg-red-700">

                                            Confirmer le refus
                                        </button>
                                    </div>
                                </form>
                            </dialog>

                        @endif

                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-5 py-14 text-center">

                                <div class="mx-auto grid size-14 place-items-center rounded-full bg-blue-50 text-blue-600">
                                    <x-admin.icon name="inbox" class="size-7"/>
                                </div>

                                <p class="mt-4 font-bold text-[#0F2347]">
                                    Aucune demande trouvée
                                </p>

                                <p class="mt-2 text-sm text-slate-500">
                                    Modifiez les filtres ou sélectionnez un autre statut.
                                </p>
                            </td>
                        </tr>

                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}

        <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 px-5 py-5">

            <p class="text-xs text-slate-600">

                @if($requests->total() > 0)
                    Affichage de
                    <strong>{{ $requests->firstItem() }}</strong>
                    à
                    <strong>{{ $requests->lastItem() }}</strong>
                    sur
                    <strong>{{ $requests->total() }}</strong>
                    demandes
                @else
                    Aucun résultat
                @endif
            </p>

            <div class="w-full sm:w-auto">
                {{ $requests->links() }}
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[data-dialog-open]').forEach(button => {

        button.addEventListener('click', function () {

            const id = this.dataset.dialogOpen;
            const dialog = document.getElementById(id);

            if (dialog && !dialog.open) {
                dialog.showModal();
            }
        });
    });

    document.querySelectorAll('[data-dialog-close]').forEach(button => {

        button.addEventListener('click', function () {

            const dialog = this.closest('dialog');

            if (dialog) {
                dialog.close();
            }
        });
    });

    document.querySelectorAll('dialog.request-dialog').forEach(dialog => {

        dialog.addEventListener('click', function (event) {

            if (event.target === dialog) {
                dialog.close();
            }
        });
    });
});
</script>

</x-responsable-conferences.layout>
