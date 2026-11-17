<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

@php
    $cards = [
        [
            'label' => 'Exposants',
            'value' => $exhibitorsCount,
            'icon' => 'storefront',
            'color' => 'bg-violet-100 text-violet-600',
            'description' => 'Entreprises enregistrées',
        ],
        [
            'label' => 'Équipements',
            'value' => $equipmentCount,
            'icon' => 'box',
            'color' => 'bg-blue-100 text-blue-600',
            'description' => 'Références du catalogue',
        ],
        [
            'label' => 'Demandes',
            'value' => $requestsCount,
            'icon' => 'clipboard-check',
            'color' => 'bg-amber-100 text-amber-600',
            'description' => $periodLabel,
        ],
        [
            'label' => 'Approuvées',
            'value' => $approvedRequests,
            'icon' => 'clipboard-check',
            'color' => 'bg-emerald-100 text-emerald-600',
            'description' => 'Demandes acceptées',
        ],
        [
            'label' => 'Refusées',
            'value' => $rejectedRequests,
            'icon' => 'inbox',
            'color' => 'bg-red-100 text-red-600',
            'description' => 'Demandes refusées',
        ],
    ];

    $pieEndPending = $approvedPercent + $pendingPercent;
@endphp

<div class="space-y-7">

    <!-- BREADCRUMB -->
    <nav class="flex items-center gap-2 text-sm text-slate-500">

        <a href="{{ route('admin.index') }}"
           class="hover:text-blue-600">
            Administration
        </a>

        <span>/</span>

        <span class="font-semibold text-[#0F2347]">
            Statistiques
        </span>

    </nav>

    <!-- HEADER -->
    <header class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">

        <div class="flex items-center gap-4">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <x-admin.icon name="chart" class="size-7"/>
            </span>

            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                    Statistiques
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Suivez les performances du salon et l'activité de la plateforme.
                </p>
            </div>

        </div>

        <form method="GET"
              action="{{ route('admin.statistiques.index') }}"
              class="flex flex-wrap items-center gap-3">

            <label for="period" class="sr-only">
                Choisir une période
            </label>

            <select id="period"
                    name="period"
                    onchange="this.form.submit()"
                    class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-[#0F2347] shadow-sm focus:border-blue-500 focus:ring-blue-500">

                @foreach ($periodLabels as $value => $label)

                    <option value="{{ $value }}"
                            @selected($period === $value)>
                        {{ $label }}
                    </option>

                @endforeach

            </select>

            <button type="submit"
                    class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                Actualiser
            </button>

        </form>

    </header>

    <!-- KPI CARDS -->
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5"
             aria-label="Indicateurs principaux">

        @foreach ($cards as $card)

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-center gap-3">

                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl {{ $card['color'] }}">
                        <x-admin.icon
                            :name="$card['icon']"
                            class="size-6"
                        />
                    </span>

                    <div class="min-w-0">

                        <p class="text-xs font-semibold text-slate-500">
                            {{ $card['label'] }}
                        </p>

                        <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                            {{ number_format($card['value'], 0, ',', ' ') }}
                        </p>

                    </div>

                </div>

                <p class="mt-4 text-xs text-slate-400">
                    {{ $card['description'] }}
                </p>

            </article>

        @endforeach

    </section>

    <!-- CHARTS ROW -->
    <section class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">

        <!-- EVOLUTION CHART -->
        <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="flex flex-wrap items-start justify-between gap-3">

                <div>
                    <h2 class="text-lg font-bold text-[#0F2347]">
                        Évolution des demandes
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Demandes d'équipements par mois — 6 derniers mois.
                    </p>
                </div>

                <span class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700">
                    {{ $periodLabel }}
                </span>

            </div>

            <div class="mt-7">

                <svg viewBox="0 0 700 220"
                     role="img"
                     aria-label="Évolution des demandes sur les six derniers mois"
                     class="w-full">

                    <defs>
                        <linearGradient id="requestChartFill"
                                        x1="0"
                                        y1="0"
                                        x2="0"
                                        y2="1">

                            <stop offset="0%"
                                  stop-color="#2563EB"
                                  stop-opacity="0.24"/>

                            <stop offset="100%"
                                  stop-color="#2563EB"
                                  stop-opacity="0.02"/>

                        </linearGradient>
                    </defs>

                    <!-- GRID -->
                    @foreach ([30, 80, 130, 180] as $y)
                        <line x1="30"
                              y1="{{ $y }}"
                              x2="670"
                              y2="{{ $y }}"
                              stroke="#E2E8F0"
                              stroke-width="1"
                              stroke-dasharray="4 5"/>
                    @endforeach

                    @foreach (range(0, 5) as $index)
                        <line x1="{{ 30 + $index * 128 }}"
                              y1="30"
                              x2="{{ 30 + $index * 128 }}"
                              y2="180"
                              stroke="#EEF2F7"
                              stroke-width="1"/>
                    @endforeach

                    <!-- AREA -->
                    <polygon
                        points="30,180 {{ $chartLine }} 670,180"
                        fill="url(#requestChartFill)"
                    />

                    <!-- LINE -->
                    <polyline
                        points="{{ $chartLine }}"
                        fill="none"
                        stroke="#2563EB"
                        stroke-width="3"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />

                    <!-- DATA POINTS -->
                    @foreach ($chartPoints as $point)

                        <circle cx="{{ $point['x'] }}"
                                cy="{{ $point['y'] }}"
                                r="5"
                                fill="white"
                                stroke="#2563EB"
                                stroke-width="3">

                            <title>
                                {{ $point['label'] }} :
                                {{ $point['value'] }} demande(s)
                            </title>

                        </circle>

                    @endforeach

                    <line x1="30"
                          y1="180"
                          x2="670"
                          y2="180"
                          stroke="#CBD5E1"
                          stroke-width="1.5"/>

                </svg>

                <!-- MONTH LABELS -->
                <div class="mt-2 grid grid-cols-6 gap-1">

                    @foreach ($monthlyStats as $month)
                        <div class="text-center">

                            <p class="text-xs font-bold text-[#0F2347]">
                                {{ $month['requests'] }}
                            </p>

                            <p class="mt-1 text-[10px] text-slate-500 sm:text-xs">
                                {{ $month['label'] }}
                            </p>

                        </div>
                    @endforeach

                </div>

                <p class="mt-5 text-xs text-slate-400">
                    Évolution mensuelle des demandes sur les 6 derniers mois,
                    limitée à la période sélectionnée.
                </p>

            </div>

        </article>

        <!-- DONUT -->
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-bold text-[#0F2347]">
                Répartition des demandes
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                Distribution par statut de traitement.
            </p>

            <div class="mt-9 flex flex-col items-center gap-7">

                <div class="relative size-52 shrink-0 rounded-full"
                     style="background: {{ $requestsCount > 0
                        ? 'conic-gradient(#22C55E 0% '.$approvedPercent.'%, #FBBF24 '.$approvedPercent.'% '.$pieEndPending.'%, #EF4444 '.$pieEndPending.'% 100%)'
                        : '#E2E8F0' }};">

                    <div class="absolute inset-9 flex flex-col items-center justify-center rounded-full bg-white">

                        <span class="text-3xl font-extrabold text-[#0F2347]">
                            {{ $requestsCount }}
                        </span>

                        <span class="mt-1 text-xs font-semibold text-slate-500">
                            Demandes
                        </span>

                    </div>

                </div>

                <div class="w-full divide-y divide-slate-100">

                    @foreach ($requestDistribution as $status)

                        @php
                            $dotColor = match ($status['color']) {
                                'emerald' => 'bg-emerald-500',
                                'red' => 'bg-red-500',
                                default => 'bg-amber-400',
                            };
                        @endphp

                        <div class="flex items-center justify-between gap-3 py-3">

                            <span class="flex items-center gap-2 text-sm font-medium text-slate-700">

                                <span class="size-3 rounded-full {{ $dotColor }}"></span>

                                {{ $status['label'] }}

                            </span>

                            <div class="flex items-center gap-3">

                                <span class="text-sm font-bold text-[#0F2347]">
                                    {{ $status['count'] }}
                                </span>

                                <span class="w-14 text-right text-xs text-slate-500">
                                    {{ number_format($status['percent'], 1, ',', ' ') }}%
                                </span>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

        </article>

    </section>


<!-- EVOLUTION DES EXPOSANTS -->
<section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-[#0F2347]">
                Évolution des exposants
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                Inscriptions des exposants sur les 6 derniers mois.
            </p>
        </div>

        <span class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">
            Exposants
        </span>
    </div>

    <div class="mt-7 grid h-60 grid-cols-6 items-end gap-3 border-b border-slate-200 pb-3">

        @foreach ($monthlyStats as $month)

            @php
                $height = $month['exhibitors'] > 0
                    ? max(
                        6,
                        round(
                            $month['exhibitors'] / $maxMonthly * 100,
                            1
                        )
                    )
                    : 0;
            @endphp

            <div class="flex h-full flex-col items-center justify-end gap-2">

                <span class="text-xs font-bold text-slate-700">
                    {{ $month['exhibitors'] }}
                </span>

                <div class="flex h-44 w-full max-w-16 items-end">

                    <div
                        class="w-full rounded-t-lg bg-[#2563EB] transition hover:bg-blue-700"
                        style="height: {{ $height }}%">
                    </div>

                </div>

            </div>

        @endforeach

    </div>

    <div class="mt-3 grid grid-cols-6 gap-3">

        @foreach ($monthlyStats as $month)

            <span class="text-center text-[10px] font-medium text-slate-500 sm:text-xs">
                {{ $month['label'] }}
            </span>

        @endforeach

    </div>

</section>


    <!-- TOP 3 SECTIONS -->
    <section class="grid gap-6 xl:grid-cols-3">

        <!-- TOP EQUIPMENT -->
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-bold text-[#0F2347]">
                Équipements les plus demandés
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                Top 5 par quantité demandée.
            </p>

            <div class="mt-6 space-y-5">

                @forelse ($topEquipment as $item)

                    @php
                        $progress = round(
                            (int) $item->total_quantity
                            / $maxEquipmentQuantity * 100,
                            1
                        );
                    @endphp

                    <div class="flex items-center gap-3">

                        @if ($item->image_path)

                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}"
                                alt="{{ $item->equipment_name }}"
                                class="size-12 shrink-0 rounded-lg border border-slate-200 bg-white object-contain p-1"
                                loading="lazy"
                            >

                        @else

                            <span class="grid size-12 shrink-0 place-items-center rounded-lg bg-blue-50 text-blue-600">
                                <x-admin.icon name="box" class="size-6"/>
                            </span>

                        @endif

                        <div class="min-w-0 flex-1">

                            <div class="flex items-center justify-between gap-3">

                                <span class="truncate text-sm font-semibold text-[#0F2347]"
                                      title="{{ $item->equipment_name }}">
                                    {{ $item->equipment_name }}
                                </span>

                                <span class="shrink-0 text-sm font-extrabold text-slate-700">
                                    {{ $item->total_quantity }}
                                </span>

                            </div>

                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">

                                <div class="h-full rounded-full bg-[#2563EB]"
                                     style="width: {{ $progress }}%">
                                </div>

                            </div>

                        </div>

                    </div>

                @empty

                    <p class="py-8 text-center text-sm text-slate-500">
                        Aucun équipement demandé pour cette période.
                    </p>

                @endforelse

            </div>

        </article>

        <!-- TOP EXHIBITORS -->
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-bold text-[#0F2347]">
                Top des exposants
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                Entreprises ayant le plus de demandes.
            </p>

            <div class="mt-6 divide-y divide-slate-100">

                @forelse ($topExhibitors as $index => $item)

                    @php
                        $exhibitor = $item->exhibitor;

                        $companyInitials = collect(
                            preg_split(
                                '/\s+/',
                                trim($exhibitor?->company_name ?? '')
                            )
                        )
                            ->filter()
                            ->take(2)
                            ->map(
                                fn ($word) => mb_strtoupper(
                                    mb_substr($word, 0, 1)
                                )
                            )
                            ->implode('');
                    @endphp

                    <div class="flex items-center gap-3 py-3">

                        <!-- RANK -->
                        <span @class([
                            'grid size-8 shrink-0 place-items-center rounded-full text-xs font-extrabold',
                            'bg-amber-100 text-amber-700' => $index === 0,
                            'bg-slate-200 text-slate-600' => $index === 1,
                            'bg-orange-100 text-orange-700' => $index === 2,
                            'bg-blue-50 text-blue-700' => $index > 2,
                        ])>
                            {{ $index + 1 }}
                        </span>

                        <!-- LOGO -->
                        @if ($exhibitor?->logo)

                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($exhibitor->logo) }}"
                                alt="{{ $exhibitor->company_name }}"
                                class="size-10 shrink-0 rounded-lg border border-slate-200 bg-white object-contain p-1"
                                loading="lazy"
                            >

                        @else

                            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-blue-50 text-xs font-bold text-blue-700">
                                {{ $companyInitials ?: 'E' }}
                            </span>

                        @endif

                        <div class="min-w-0 flex-1">

                            @if ($exhibitor)
                                <a
                                    href="{{ route('admin.exposants.show', $exhibitor) }}"
                                    class="block truncate text-sm font-bold text-[#0F2347] hover:text-blue-600">

                                    {{ $exhibitor->company_name }}

                                </a>
                            @else
                                <p class="text-sm text-slate-400">
                                    Exposant indisponible
                                </p>
                            @endif

                        </div>

                        <span class="shrink-0 text-xs font-semibold text-slate-600">
                            {{ $item->requests_count }} demandes
                        </span>

                    </div>

                @empty

                    <p class="py-8 text-center text-sm text-slate-500">
                        Aucune demande enregistrée.
                    </p>

                @endforelse

            </div>

        </article>

        <!-- CATEGORIES -->
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-bold text-[#0F2347]">
                Demandes par catégorie
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                Répartition des unités demandées.
            </p>

            <div class="mt-7 space-y-6">

                @forelse ($categoryStats as $category)

                    @php
                        $percentage = round(
                            (int) $category->total_quantity
                            / $totalCategoryQuantity * 100,
                            1
                        );
                    @endphp

                    <div>

                        <div class="mb-2 flex items-center justify-between gap-3">

                            <span class="text-sm font-semibold text-[#0F2347]">
                                {{ $category->category }}
                            </span>

                            <span class="text-xs font-bold text-slate-600">
                                {{ number_format($percentage, 1, ',', ' ') }}%
                            </span>

                        </div>

                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">

                            <div class="h-full rounded-full bg-[#2563EB]"
                                 style="width: {{ $percentage }}%">
                            </div>

                        </div>

                        <p class="mt-1 text-right text-xs text-slate-400">
                            {{ $category->total_quantity }} unité(s)
                        </p>

                    </div>

                @empty

                    <p class="py-8 text-center text-sm text-slate-500">
                        Aucune donnée pour cette période.
                    </p>

                @endforelse

            </div>

        </article>

    </section>

    <!-- GENERAL OPERATIONS -->
    <section class="grid gap-6 lg:grid-cols-2">

        <!-- PLATFORM -->
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-bold text-[#0F2347]">
                Vue générale de la plateforme
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                Indicateurs cumulés depuis le lancement.
            </p>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">

                @foreach ([
                    ['Commerciaux', $commercialsCount],
                    ['Commerciaux actifs', $activeCommercials],
                    ['Équipements actifs', $activeEquipment],
                    ['Badges autorisés', $totalAuthorizedBadges],
                    ['Badges enregistrés', $registeredBadges],
                ] as $metric)

                    <div class="rounded-xl bg-[#F5F7FB] p-4">

                        <p class="text-xs font-medium text-slate-500">
                            {{ $metric[0] }}
                        </p>

                        <p class="mt-2 text-2xl font-extrabold text-[#0F2347]">
                            {{ number_format($metric[1], 0, ',', ' ') }}
                        </p>

                    </div>

                @endforeach

                <div class="rounded-xl bg-[#F5F7FB] p-4">

                    <p class="text-xs font-medium text-slate-500">
                        Surface totale des stands
                    </p>

                    <p class="mt-2 text-2xl font-extrabold text-[#0F2347]">
                        {{ number_format($totalArea, 2, ',', ' ') }}
                        <span class="text-sm">m²</span>
                    </p>

                </div>

            </div>

        </article>

        <!-- FINANCIAL -->
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-bold text-[#0F2347]">
                Analyse des demandes
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                Montants et validation — {{ $periodLabel }}.
            </p>

            <div class="mt-6 space-y-4">

                <div class="rounded-xl bg-[#0F2347] p-5 text-white">

                    <p class="text-sm text-blue-100">
                        Montant total demandé
                    </p>

                    <p class="mt-2 text-2xl font-extrabold">
                        {{ number_format($totalRequestedAmount, 2, ',', ' ') }}
                        <span class="text-sm">DH</span>
                    </p>

                </div>

                <div class="rounded-xl bg-emerald-50 p-5">

                    <p class="text-sm text-emerald-700">
                        Montant des demandes approuvées
                    </p>

                    <p class="mt-2 text-2xl font-extrabold text-emerald-700">
                        {{ number_format($approvedAmount, 2, ',', ' ') }}
                        <span class="text-sm">DH</span>
                    </p>

                </div>

                <div class="rounded-xl bg-blue-50 p-5">

                    <div class="flex items-center justify-between gap-3">

                        <p class="text-sm font-semibold text-blue-700">
                            Taux d'approbation
                        </p>

                        <span class="text-xl font-extrabold text-blue-700">
                            {{ number_format($approvalRate, 1, ',', ' ') }}%
                        </span>

                    </div>

                    <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-blue-100">

                        <div class="h-full rounded-full bg-blue-600"
                             style="width: {{ $approvalRate }}%">
                        </div>

                    </div>

                </div>

                <p class="text-xs text-slate-400">
                    Les montants approuvés ne représentent pas nécessairement des paiements encaissés.
                </p>

            </div>

        </article>

    </section>

    <!-- COMMERCIAL PERFORMANCE -->
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

        <div class="flex flex-wrap items-center justify-between gap-3">

            <div>
                <h2 class="text-lg font-bold text-[#0F2347]">
                    Performance commerciale
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Classement des commerciaux par nombre d'exposants gérés.
                </p>
            </div>

            <a href="{{ route('admin.commerciaux.index') }}"
               class="text-sm font-semibold text-blue-700 hover:underline">
                Voir les commerciaux
            </a>

        </div>

        <div class="mt-6 grid gap-5 lg:grid-cols-2">

            @forelse ($commercialStats as $commercial)

                @php
                    $progress = round(
                        $commercial->exhibitors_count
                        / $maxCommercialExhibitors * 100,
                        1
                    );
                @endphp

                <div class="rounded-xl border border-slate-100 p-4">

                    <div class="flex items-center gap-3">

                        @if ($commercial->profile_photo_url)

                            <img
                                src="{{ $commercial->profile_photo_url }}"
                                alt="{{ $commercial->name }}"
                                class="size-11 shrink-0 rounded-full object-cover"
                            >

                        @else

                            <span class="grid size-11 shrink-0 place-items-center rounded-full bg-blue-100 text-sm font-bold text-blue-700">
                                {{ $commercial->initials }}
                            </span>

                        @endif

                        <div class="min-w-0 flex-1">

                            <a href="{{ route('admin.commerciaux.show', $commercial) }}"
                               class="block truncate text-sm font-bold text-[#0F2347] hover:text-blue-600">
                                {{ $commercial->name }}
                            </a>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ $commercial->exhibitors_count }} exposant(s)
                            </p>

                        </div>

                    </div>

                    <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-100">

                        <div class="h-full rounded-full bg-blue-600"
                             style="width: {{ $progress }}%">
                        </div>

                    </div>

                </div>

            @empty

                <p class="py-8 text-sm text-slate-500">
                    Aucun commercial enregistré.
                </p>

            @endforelse

        </div>

    </section>

    <!-- RECENT REQUESTS -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">

            <div>
                <h2 class="text-lg font-bold text-[#0F2347]">
                    Dernières demandes
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Suivi des demandes récentes.
                </p>
            </div>

            <a href="{{ route('admin.demandes.index') }}"
               class="text-sm font-semibold text-blue-700 hover:underline">
                Voir toutes les demandes
            </a>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full min-w-[700px] text-left text-sm">

                <thead class="bg-[#F5F7FB] text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Référence</th>
                        <th class="px-5 py-4">Exposant</th>
                        <th class="px-5 py-4">Montant</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4">Date</th>
                        <th class="px-5 py-4 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse ($recentRequests as $order)

                        <tr class="hover:bg-blue-50/30">

                            <td class="px-5 py-4 font-bold text-blue-700">
                                DM-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}
                            </td>

                            <td class="px-5 py-4 font-semibold text-slate-700">
                                {{ $order->exhibitor?->company_name ?? '—' }}
                            </td>

                            <td class="px-5 py-4 font-bold text-[#0F2347]">
                                {{ number_format((float) $order->total_amount, 2, ',', ' ') }} DH
                            </td>

                            <td class="px-5 py-4">

                                <span @class([
                                    'rounded-full px-3 py-1.5 text-xs font-bold',
                                    'bg-amber-50 text-amber-700' => $order->status === 'pending',
                                    'bg-emerald-50 text-emerald-700' => $order->status === 'approved',
                                    'bg-red-50 text-red-700' => $order->status === 'rejected',
                                ])>

                                    {{ match ($order->status) {
                                        'approved' => 'Approuvée',
                                        'rejected' => 'Refusée',
                                        default => 'En attente',
                                    } }}

                                </span>

                            </td>

                            <td class="px-5 py-4 text-slate-500">
                                {{ $order->created_at?->format('d/m/Y') }}
                            </td>

                            <td class="px-5 py-4 text-right">

                                <a href="{{ route('admin.demandes.show', $order) }}"
                                   class="font-semibold text-blue-700 hover:underline">
                                    Voir
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6"
                                class="px-6 py-12 text-center text-slate-500">
                                Aucune demande enregistrée pour cette période.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </section>

    <p class="text-xs text-slate-400">
        Statistiques calculées à partir des données de la plateforme.
        Les indicateurs généraux sont cumulés ; les demandes suivent la période sélectionnée.
    </p>

</div>

</x-admin.layout>
