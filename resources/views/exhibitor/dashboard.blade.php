
<x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">

@php
    $fmt = fn ($value) => number_format((int) $value, 0, ',', ' ');

    $standImage = asset(
        'assets/conferences/images/Stand d’exposition bleu et blanc moderne.png'
    );

    $catalogLabels = [
        'completed' => 'Complétée',
        'draft' => 'En cours',
        'empty' => 'Non remplie',
    ];

    $catalogLabel = $catalogLabels[$catalogStatus] ?? 'Non remplie';

    $catalogClasses = match ($catalogStatus) {
        'completed' => 'text-emerald-700',
        'draft' => 'text-amber-700',
        default => 'text-slate-600',
    };

    $catalogBackground = match ($catalogStatus) {
        'completed' => 'bg-emerald-50 border-emerald-100',
        'draft' => 'bg-amber-50 border-amber-100',
        default => 'bg-slate-50 border-slate-200',
    };

    $activityStyles = [
        'catalog' => [
            'icon' => 'clipboard-check',
            'class' => 'bg-emerald-50 text-emerald-600',
        ],
        'equipment' => [
            'icon' => 'box',
            'class' => 'bg-violet-50 text-violet-600',
        ],
        'badge' => [
            'icon' => 'badge',
            'class' => 'bg-orange-50 text-orange-600',
        ],
    ];
@endphp

<div class="space-y-5 pb-8">

    {{-- ======================================== --}}
    {{-- 1. EN-TETE                              --}}
    {{-- ======================================== --}}

    <section class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div class="flex min-w-0 items-center gap-4">

            <div class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-600 text-white shadow-sm">
                <x-admin.icon name="storefront" class="size-7"/>
            </div>

            <div class="min-w-0">

                <p class="text-sm font-medium text-slate-600">
                    Bonjour, {{ $exhibitor?->company_name ?? $user->name }}
                </p>

                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                    Mon tableau de bord
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Vue globale — Suivi de ma participation à REFRIGAIR EXPO 2027.
                </p>

            </div>

        </div>

        
{{-- BANNIÈRE OFFICIELLE REFRIGAIR EXPO 2027 --}}

<div class="w-full min-w-0 overflow-hidden rounded-2xl border border-blue-100 bg-[#DFF2F8] shadow-sm lg:w-[570px] lg:shrink-0">
    <img
        src="{{ asset('assets/images/refrig_air_expo_cover.jpg') }}"
        alt="REFRIGAIR EXPO 2027 — Du 2 au 4 février 2027, OFEC Casablanca"
        class="block h-auto w-full object-contain"
        loading="eager"
        decoding="async"
    >
</div>


    </section>

    @if ($exhibitor)

        {{-- ======================================== --}}
        {{-- 2. KPI                                 --}}
        {{-- ======================================== --}}

        <section
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
            aria-label="Indicateurs de participation"
        >

            {{-- FICHE CATALOGUE --}}

            <a
                href="{{ route('exhibitor.catalog-profile.edit') }}"
                class="group flex min-w-0 flex-col justify-between rounded-2xl border {{ $catalogBackground }} p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <div class="flex items-center justify-between gap-3">

                    <span class="grid size-12 place-items-center rounded-xl bg-emerald-100 text-emerald-600">
                        <x-admin.icon name="layers" class="size-6"/>
                    </span>

                    <span class="text-emerald-600 group-hover:translate-x-1">
                        &rarr;
                    </span>

                </div>

                <div class="mt-4">
                    <p class="text-sm font-bold text-[#0F2347]">
                        Fiche catalogue
                    </p>

                    <p class="mt-2 text-2xl font-extrabold {{ $catalogClasses }}">
                        {{ $catalogLabel }}
                    </p>

                    <p class="mt-2 text-xs text-slate-600">
                        {{ $catalogCompleted
                            ? 'Votre fiche est finalisée.'
                            : 'Complétez les informations de votre entreprise.' }}
                    </p>
                </div>
            </a>

            {{-- BADGES --}}

            <a
                href="{{ route('exhibitor.badges.index') }}"
                class="group flex min-w-0 flex-col justify-between rounded-2xl border border-orange-100 bg-orange-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <div class="flex items-center justify-between">

                    <span class="grid size-12 place-items-center rounded-xl bg-orange-100 text-orange-600">
                        <x-admin.icon name="badge" class="size-6"/>
                    </span>

                    <span class="text-orange-600">&rarr;</span>
                </div>

                <div class="mt-4">
                    <p class="text-sm font-bold text-[#0F2347]">
                        Badges enregistrés
                    </p>

                    <p class="mt-2 text-3xl font-extrabold text-[#0F2347]">
                        {{ $fmt($badgeCount) }}
                        <span class="text-lg font-medium text-slate-400">
                            / {{ $fmt($authorizedBadges) }}
                        </span>
                    </p>

                    <p class="mt-2 text-xs text-slate-600">
                        {{ $remainingBadges }} badge(s) restant(s)
                        · {{ $badgeProgress }} %
                    </p>
                </div>
            </a>

            {{-- EQUIPEMENTS --}}

            <a
                href="{{ route('exhibitor.requests.index') }}"
                class="group flex min-w-0 flex-col justify-between rounded-2xl border border-violet-100 bg-violet-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <div class="flex items-center justify-between">

                    <span class="grid size-12 place-items-center rounded-xl bg-violet-100 text-violet-600">
                        <x-admin.icon name="box" class="size-6"/>
                    </span>

                    <span class="text-violet-600">&rarr;</span>
                </div>

                <div class="mt-4">

                    <p class="text-sm font-bold text-[#0F2347]">
                        Demandes d'équipements
                    </p>

                    <p class="mt-2 text-3xl font-extrabold text-[#0F2347]">
                        {{ $fmt($requestsCount) }}
                    </p>

                    <p class="mt-2 text-xs text-slate-600">
                        {{ $pendingRequests }} en attente
                        · {{ $approvedRequests }} approuvée(s)
                    </p>
                </div>
            </a>

            {{-- CONFERENCES --}}

            <a
                href="{{ route('exhibitor.slots.index') }}"
                class="group flex min-w-0 flex-col justify-between rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
            >

                <div class="flex items-center justify-between">

                    <span class="grid size-12 place-items-center rounded-xl bg-blue-100 text-blue-600">
                        <x-admin.icon name="calendar" class="size-6"/>
                    </span>

                    <span class="text-blue-600">&rarr;</span>
                </div>

                <div class="mt-4">
                    <p class="text-sm font-bold text-[#0F2347]">
                        Conférences & Lounge
                    </p>

                    <p class="mt-2 text-3xl font-extrabold text-[#0F2347]">
                        {{ $fmt($reservedSlotsCount) }}
                    </p>

                    <p class="mt-2 text-xs text-slate-600">
                        Créneau(x) réservé(s) ou payé(s)
                    </p>
                </div>
            </a>

        </section>

        {{-- ======================================== --}}
        {{-- 3. DOSSIER + STAND                     --}}
        {{-- ======================================== --}}

        <section class="grid grid-cols-1 items-stretch gap-5 xl:grid-cols-2">

            {{-- PROGRESSION DU DOSSIER --}}

            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <h2 class="flex items-center gap-2 text-lg font-bold text-[#0F2347]">
                    <x-admin.icon name="chart" class="text-blue-600"/>
                    Progression de mon dossier
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Suivi de la fiche catalogue et des badges.
                </p>

                <div class="mt-6 flex flex-col items-center gap-6 sm:flex-row">

                    <div
                        class="grid size-44 shrink-0 place-items-center rounded-full"
                        style="background: conic-gradient(#16A34A {{ $dossierProgress }}%, #E2E8F0 {{ $dossierProgress }}% 100%);"
                        role="progressbar"
                        aria-label="Progression de mon dossier"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-valuenow="{{ $dossierProgress }}"
                    >
                        <div class="grid size-32 place-items-center rounded-full bg-white">

                            <div class="text-center">
                                <p class="text-3xl font-extrabold text-[#0F2347]">
                                    {{ $dossierProgress }} %
                                </p>

                                <p class="text-xs text-slate-500">
                                    Préparation
                                </p>
                            </div>

                        </div>
                    </div>

                    <div class="w-full min-w-0 flex-1 divide-y divide-slate-100">

                        <div class="flex items-center justify-between gap-3 py-3">
                            <span class="text-sm font-semibold text-slate-700">
                                Fiche catalogue
                            </span>
                            <span class="text-xs font-bold {{ $catalogClasses }}">
                                {{ $catalogLabel }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-3 py-3">
                            <span class="text-sm font-semibold text-slate-700">
                                Badges ({{ $badgeCount }}/{{ $authorizedBadges }})
                            </span>
                            <span class="text-xs font-bold text-blue-700">
                                {{ $badgeProgress }} %
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-3 py-3">
                            <span class="text-sm font-semibold text-slate-700">
                                Équipements
                            </span>

                            <span class="text-right text-xs font-semibold text-amber-700">
                                {{ $pendingRequests > 0
                                    ? $pendingRequests . ' demande(s) en attente'
                                    : $requestsCount . ' demande(s)' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-3 py-3">
                            <span class="text-sm font-semibold text-slate-700">
                                Conférences & Lounge
                            </span>

                            <span class="text-right text-xs font-semibold text-blue-700">
                                {{ $reservedSlotsCount }}
                                créneau(x) confirmé(s)
                            </span>
                        </div>

                    </div>

                </div>
            </article>

            {{-- MON STAND --}}

            <article
                id="mon-stand"
                class="min-w-0 scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >

                <div class="px-5 pt-5 sm:px-6 sm:pt-6">

                    <h2 class="flex items-center gap-2 text-lg font-bold text-[#0F2347]">
                        <x-admin.icon name="storefront" class="text-blue-600"/>
                        Mon stand
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Informations de votre espace d'exposition.
                    </p>
                </div>

                <div class="relative mt-4 bg-gradient-to-br from-blue-50 via-white to-slate-50 px-4 pb-4">

                    {{-- IMAGE GENERIQUE DU STAND --}}
                    <img
                        src="{{ $standImage }}"
                        alt="Illustration d'un stand modulaire bleu et blanc"
                        class="mx-auto h-64 w-full object-contain sm:h-72"
                        loading="lazy"
                    >

                    {{-- INFORMATIONS REELLES --}}
                    <div class="absolute left-5 top-4 max-w-[55%] rounded-xl border border-blue-100 bg-white/95 px-4 py-3 shadow-sm backdrop-blur">

                        <p class="text-xs font-semibold text-slate-500">
                            Numéro de stand
                        </p>

                        <p class="mt-1 text-xl font-extrabold text-[#0F2347]">
                            {{ $exhibitor->stand_number ?: 'À attribuer' }}
                        </p>

                        <p class="mt-1 text-xs text-slate-600">
                            {{ $exhibitor->area_sqm !== null
                                ? number_format((float) $exhibitor->area_sqm, 2, ',', ' ') . ' m²'
                                : 'Superficie non renseignée' }}
                        </p>
                    </div>

                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4 sm:px-6">

                    <span class="text-xs text-slate-500">
                        Visuel indicatif — stand modulaire non contractuel.
                    </span>

                    <a
                        href="{{ route('exhibitor.company.index') }}"
                        class="text-sm font-bold text-blue-600 hover:underline"
                    >
                        Mes informations &rarr;
                    </a>

                </div>

            </article>

        </section>

        {{-- ======================================== --}}
        {{-- 4. ACTIVITES + ACTIONS RAPIDES          --}}
        {{-- ======================================== --}}

        <section class="grid grid-cols-1 items-stretch gap-5 xl:grid-cols-2">

            {{-- ACTIVITES RECENTES --}}

            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

                    <h2 class="flex items-center gap-2 text-lg font-bold text-[#0F2347]">
                        <x-admin.icon name="chart" class="text-blue-600"/>
                        Mes dernières activités
                    </h2>

                    <a
                        href="{{ route('exhibitor.notifications.index') }}"
                        class="text-xs font-semibold text-blue-600 hover:underline"
                    >
                        Notifications &rarr;
                    </a>

                </div>

                <div class="divide-y divide-slate-100">

                    @forelse ($activities as $activity)

                        @php
                            $activityStyle = $activityStyles[$activity['type']]
                                ?? $activityStyles['equipment'];
                        @endphp

                        <a
                            href="{{ $activity['url'] }}"
                            class="flex min-w-0 items-center gap-3 py-3 transition hover:bg-slate-50"
                        >

                            <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $activityStyle['class'] }}">
                                <x-admin.icon
                                    :name="$activityStyle['icon']"
                                    class="size-5"
                                />
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-[#0F2347]">
                                    {{ $activity['title'] }}
                                </span>
                            </span>

                            <span class="shrink-0 text-xs text-slate-400">
                                {{ $activity['date']?->locale('fr')->diffForHumans() }}
                            </span>

                        </a>

                    @empty

                        <p class="py-10 text-center text-sm text-slate-500">
                            Aucune activité récente.
                        </p>

                    @endforelse

                </div>

            </article>

            {{-- ACTIONS RAPIDES --}}

            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <h2 class="mb-5 flex items-center gap-2 text-lg font-bold text-[#0F2347]">
                    <x-admin.icon name="zap" class="text-blue-600"/>
                    Actions rapides
                </h2>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

                    <a
                        href="{{ route('exhibitor.catalog-profile.edit') }}"
                        class="flex min-h-32 flex-col items-center justify-center gap-3 rounded-xl bg-emerald-50 p-3 text-center text-xs font-bold text-emerald-800 transition hover:bg-emerald-100"
                    >

                        <span class="grid size-11 place-items-center rounded-xl bg-emerald-100 text-emerald-600">
                            <x-admin.icon name="layers" class="size-6"/>
                        </span>

                        Modifier ma fiche catalogue
                    </a>

                    <a
                        href="{{ route('exhibitor.badges.index') }}"
                        class="flex min-h-32 flex-col items-center justify-center gap-3 rounded-xl bg-orange-50 p-3 text-center text-xs font-bold text-orange-800 transition hover:bg-orange-100"
                    >

                        <span class="grid size-11 place-items-center rounded-xl bg-orange-100 text-orange-600">
                            <x-admin.icon name="badge" class="size-6"/>
                        </span>

                        Gérer mes badges
                    </a>

                    <a
                        href="{{ route('exhibitor.stand-options.index') }}"
                        class="flex min-h-32 flex-col items-center justify-center gap-3 rounded-xl bg-violet-50 p-3 text-center text-xs font-bold text-violet-800 transition hover:bg-violet-100"
                    >

                        <span class="grid size-11 place-items-center rounded-xl bg-violet-100 text-violet-600">
                            <x-admin.icon name="box" class="size-6"/>
                        </span>

                        Demander des équipements
                    </a>

                    <a
                        href="{{ route('exhibitor.slots.index') }}"
                        class="flex min-h-32 flex-col items-center justify-center gap-3 rounded-xl bg-blue-50 p-3 text-center text-xs font-bold text-blue-800 transition hover:bg-blue-100"
                    >

                        <span class="grid size-11 place-items-center rounded-xl bg-blue-100 text-blue-600">
                            <x-admin.icon name="calendar" class="size-6"/>
                        </span>

                        Conférences & Lounge
                    </a>

                </div>

            </article>

        </section>

        {{-- ======================================== --}}
        {{-- 5. ENTREPRISE + DEMANDES RECENTES      --}}
        {{-- ======================================== --}}

        <section class="grid grid-cols-1 gap-5 xl:grid-cols-12">

            
{{-- ======================================== --}}
{{-- MON ENTREPRISE                          --}}
{{-- ======================================== --}}

<article
    class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 xl:col-span-5"
>

    {{-- HEADER --}}

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">

        <div class="flex items-center gap-2">

            <span class="grid size-9 place-items-center rounded-xl bg-blue-50 text-blue-600">
                <x-admin.icon
                    name="storefront"
                    class="size-5"
                />
            </span>

            <h2 class="text-lg font-bold text-[#0F2347]">
                Mon entreprise
            </h2>

        </div>

        <a
            href="{{ route('exhibitor.profile') }}"
            class="text-xs font-semibold text-blue-600 transition hover:underline"
        >
            Mon profil &rarr;
        </a>

    </div>

    {{-- LOGO + NOM ENTREPRISE --}}

    <div
        class="flex min-w-0 items-center gap-4 rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50/80 to-white p-4"
    >

        {{-- LOGO --}}

        <div
            class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-sm sm:size-24"
        >

            @if ($exhibitor->logo)

                <img
                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($exhibitor->logo) }}"
                    alt="Logo de {{ $exhibitor->company_name }}"
                    class="h-full w-full object-contain"
                    loading="lazy"
                >

            @else

                @php
                    $companyInitials = collect(
                        preg_split(
                            '/\s+/',
                            trim($exhibitor->company_name)
                        )
                    )
                        ->filter()
                        ->take(2)
                        ->map(
                            fn ($word) =>
                                mb_strtoupper(mb_substr($word, 0, 1))
                        )
                        ->implode('') ?: 'E';
                @endphp

                <span
                    class="grid h-full w-full place-items-center rounded-xl bg-blue-100 text-2xl font-extrabold text-blue-700"
                >
                    {{ $companyInitials }}
                </span>

            @endif

        </div>

        {{-- NOM --}}

        <div class="min-w-0 flex-1">

            <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                Entreprise exposante
            </p>

            <h3
                class="mt-1 break-words text-lg font-extrabold leading-snug text-[#0F2347] sm:text-xl"
            >
                {{ $exhibitor->company_name }}
            </h3>

            <span
                class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700"
            >
                <span class="size-2 rounded-full bg-emerald-500"></span>

                Exposant
            </span>

        </div>

    </div>

    {{-- INFORMATIONS --}}

    <dl class="mt-5 divide-y divide-slate-100 text-sm">

        {{-- CONTACT --}}

        <div class="flex items-start justify-between gap-4 py-3">

            <dt class="shrink-0 text-slate-500">
                Responsable
            </dt>

            <dd class="min-w-0 break-words text-right font-semibold text-[#0F2347]">
                {{ $exhibitor->contact_name ?: '—' }}
            </dd>

        </div>

        {{-- EMAIL --}}

        <div class="flex items-start justify-between gap-4 py-3">

            <dt class="shrink-0 text-slate-500">
                Email
            </dt>

            <dd class="min-w-0 break-all text-right font-semibold text-[#0F2347]">

                @if ($exhibitor->email)

                    <a
                        href="mailto:{{ $exhibitor->email }}"
                        class="transition hover:text-blue-600 hover:underline"
                    >
                        {{ $exhibitor->email }}
                    </a>

                @else
                    —
                @endif

            </dd>

        </div>

        {{-- TELEPHONE --}}

        <div class="flex items-start justify-between gap-4 py-3">

            <dt class="shrink-0 text-slate-500">
                Téléphone
            </dt>

            <dd class="min-w-0 text-right font-semibold text-[#0F2347]">

                @if ($exhibitor->phone)

                    <a
                        href="tel:{{ preg_replace('/[^\d+]/', '', $exhibitor->phone) }}"
                        class="hover:text-blue-600 hover:underline"
                    >
                        {{ $exhibitor->phone }}
                    </a>

                @else
                    —
                @endif

            </dd>

        </div>

        {{-- COMMERCIAL REFERENT --}}

        <div class="flex items-start justify-between gap-4 py-3">

            <dt class="shrink-0 text-slate-500">
                Commercial référent
            </dt>

            <dd class="min-w-0 break-words text-right font-semibold text-[#0F2347]">
                {{ $exhibitor->commercial?->name ?? 'Non attribué' }}
            </dd>

        </div>

    </dl>

    {{-- BOUTON --}}

    <div class="mt-5 border-t border-slate-100 pt-4">

        <a
            href="{{ route('exhibitor.profile') }}"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-bold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100"
        >
            <x-admin.icon name="settings" class="size-4"/>

            Gérer mon profil

            <span aria-hidden="true">&rarr;</span>
        </a>

    </div>

</article>


            {{-- DERNIERES DEMANDES --}}

            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-7">

                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

                    <h2 class="text-lg font-bold text-[#0F2347]">
                        Mes dernières demandes d'équipements
                    </h2>

                    <a
                        href="{{ route('exhibitor.requests.index') }}"
                        class="text-xs font-semibold text-blue-600 hover:underline"
                    >
                        Voir toutes &rarr;
                    </a>

                </div>

                <div class="min-w-0 overflow-x-auto">

                    <table class="w-full min-w-[470px] text-left text-sm">

                        <thead class="bg-slate-50 text-xs text-slate-600">
                            <tr>
                                <th class="px-3 py-3">Demande</th>
                                <th class="px-3 py-3">Date</th>
                                <th class="px-3 py-3">Montant</th>
                                <th class="px-3 py-3">Statut</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @forelse ($recentRequests as $order)

                                @php
                                    $status = match ($order->status) {
                                        'approved' => [
                                            'label' => 'Approuvée',
                                            'class' => 'bg-emerald-50 text-emerald-700',
                                        ],
                                        'rejected' => [
                                            'label' => 'Refusée',
                                            'class' => 'bg-red-50 text-red-700',
                                        ],
                                        default => [
                                            'label' => 'En attente',
                                            'class' => 'bg-amber-50 text-amber-700',
                                        ],
                                    };
                                @endphp

                                <tr>

                                    <td class="px-3 py-4 font-semibold text-[#0F2347]">
                                        #{{ $order->id }}
                                    </td>

                                    <td class="whitespace-nowrap px-3 py-4 text-slate-600">
                                        {{ $order->created_at?->format('d/m/Y') }}
                                    </td>

                                    <td class="whitespace-nowrap px-3 py-4 text-slate-700">
                                        {{ number_format((float) $order->total_amount, 2, ',', ' ') }}
                                        DH
                                    </td>

                                    <td class="px-3 py-4">
                                        <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $status['class'] }}">
                                            {{ $status['label'] }}
                                        </span>
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="4" class="px-3 py-10 text-center text-slate-500">
                                        Aucune demande pour le moment
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </article>

        </section>

        {{-- ======================================== --}}
        {{-- 6. RAPPEL DU SALON                    --}}
        {{-- ======================================== --}}

        @if ($salon)

            <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-[#0F2347] p-5 text-white shadow-sm sm:p-6">

                <div>

                    <h2 class="text-lg font-extrabold">
                        {{ $salon->name }}
                    </h2>

                    <p class="mt-1 text-sm text-blue-100">
                        @if ($daysRemaining !== null)
                            {{ $daysRemaining }} jour(s) avant l'événement
                        @else
                            Dates du salon à consulter
                        @endif
                    </p>

                </div>

                <a
                    href="{{ route('exhibitor.support.index') }}"
                    class="rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-[#0F2347] transition hover:bg-blue-50"
                >
                    Contacter mon commercial
                </a>

            </section>

        @endif

    @else

        {{-- AUCUNE ENTREPRISE ASSOCIEE --}}

        <section class="rounded-2xl border border-amber-200 bg-white p-8 shadow-sm">

            <h2 class="text-xl font-bold text-[#0F2347]">
                Aucune entreprise associée
            </h2>

            <p class="mt-3 text-sm text-slate-500">
                Votre compte n'est lié à aucune fiche exposant.
                Contactez l'organisation du salon.
            </p>

            <a
                href="{{ route('exhibitor.support.index') }}"
                class="mt-5 inline-flex rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white"
            >
                Contacter l'assistance
            </a>

        </section>

    @endif

</div>

</x-exhibitor.layout>
