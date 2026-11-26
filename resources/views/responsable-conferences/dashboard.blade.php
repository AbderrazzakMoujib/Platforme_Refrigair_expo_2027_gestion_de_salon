
<x-responsable-conferences.layout :user="auth()->user()">

    <!-- HEADER -->
    <section class="mb-7 flex flex-wrap items-center justify-between gap-4">

        <div>
            <p class="text-sm font-bold text-blue-600">
                Vue d'ensemble
            </p>

            <h1 class="mt-1 text-3xl font-black tracking-tight text-[#0F2347]">
                Tableau de bord
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Bienvenue {{ auth()->user()->name }},
                suivez les demandes et le programme des conférences.
            </p>
        </div>

        <a href="{{ route('responsable-conferences.requests.index') }}"
           class="inline-flex items-center gap-3 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">

            <x-admin.icon name="inbox" class="size-5"/>

            Traiter les demandes

            @if($pendingCount > 0)
                <span class="rounded-full bg-white/20 px-2 py-0.5 text-xs">
                    {{ $pendingCount }}
                </span>
            @endif
        </a>
    </section>

    <!-- STATISTICS -->
    @php
        $statistics = [
            [
                'label' => 'Total des créneaux',
                'value' => $totalSlots,
                'subtitle' => 'Conférences & Lounge',
                'icon' => 'calendar',
                'bg' => 'bg-blue-50',
                'color' => 'text-blue-600',
            ],
            [
                'label' => 'Demandes en attente',
                'value' => $pendingCount,
                'subtitle' => 'À traiter',
                'icon' => 'inbox',
                'bg' => 'bg-amber-50',
                'color' => 'text-amber-600',
            ],
            [
                'label' => 'Demandes confirmées',
                'value' => $confirmedCount,
                'subtitle' => 'Réservations approuvées',
                'icon' => 'clipboard-check',
                'bg' => 'bg-emerald-50',
                'color' => 'text-emerald-600',
            ],
            [
                'label' => "Taux d'occupation",
                'value' => $occupancyRate.'%',
                'subtitle' => $occupiedSlots.' créneaux indisponibles',
                'icon' => 'chart',
                'bg' => 'bg-violet-50',
                'color' => 'text-violet-600',
            ],
        ];
    @endphp

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach($statistics as $stat)

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">

                <div class="flex items-center justify-between">

                    <span class="grid size-12 place-items-center rounded-xl {{ $stat['bg'] }} {{ $stat['color'] }}">

                        <x-admin.icon
                            :name="$stat['icon']"
                            class="size-6"
                        />

                    </span>

                    <span class="size-2 rounded-full bg-blue-400"></span>
                </div>

                <p class="mt-5 text-sm font-semibold text-slate-500">
                    {{ $stat['label'] }}
                </p>

                <h2 class="mt-2 text-3xl font-black text-[#0F2347]">
                    {{ $stat['value'] }}
                </h2>

                <p class="mt-2 text-xs text-slate-400">
                    {{ $stat['subtitle'] }}
                </p>

            </article>

        @endforeach
    </section>

    <!-- MAIN GRID -->
    <div class="mt-6 grid gap-6 xl:grid-cols-3">

        <!-- RECENT REQUESTS -->
        <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">

                <div>
                    <h2 class="text-lg font-extrabold text-[#0F2347]">
                        Demandes récentes
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Dernières demandes en attente de validation
                    </p>
                </div>

                <a href="{{ route('responsable-conferences.requests.index') }}"
                   class="text-sm font-bold text-blue-600 hover:text-blue-800">
                    Voir toutes les demandes
                </a>
            </div>

            <div class="overflow-x-auto">

                <table class="w-full min-w-[760px] text-left text-sm">

                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">

                        <tr>
                            <th class="px-5 py-4">Entreprise</th>
                            <th class="px-4 py-4">Conférence</th>
                            <th class="px-4 py-4">Date</th>
                            <th class="px-4 py-4">Statut</th>
                            <th class="px-5 py-4 text-right">Action</th>
                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse($recentRequests as $item)

                            <tr class="transition hover:bg-blue-50/30">

                                <!-- COMPANY WITH LOGO -->
                                <td class="px-5 py-4">

                                    <div class="flex items-center gap-3">

                                        <div class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                                            @if($item->exhibitor?->logo_url)

                                                <img
                                                    src="{{ $item->exhibitor->logo_url }}"
                                                    alt="Logo {{ $item->exhibitor->company_name }}"
                                                    loading="lazy"
                                                    class="h-full w-full object-contain p-1.5"
                                                >

                                            @else

                                                <span class="text-sm font-extrabold text-blue-700">
                                                    {{ $item->exhibitor?->initials ?? 'EX' }}
                                                </span>

                                            @endif

                                        </div>

                                        <div class="min-w-0">

                                            <p class="max-w-44 truncate font-bold text-[#0F2347]">
                                                {{ $item->exhibitor?->company_name ?? 'Exposant' }}
                                            </p>

                                            <p class="mt-1 max-w-44 truncate text-xs text-slate-400">
                                                {{ $item->exhibitor?->commercial?->name ?? 'Sans commercial' }}
                                            </p>

                                        </div>

                                    </div>
                                </td>

                                <!-- ACTIVITY -->
                                <td class="px-4 py-4">

                                    <p class="max-w-48 truncate font-semibold text-slate-700">
                                        {{ $item->title }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $item->type->label() }}
                                    </p>

                                </td>

                                <!-- DATE -->
                                <td class="whitespace-nowrap px-4 py-4 text-slate-600">

                                    {{ $item->created_at->format('d/m/Y') }}

                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $item->created_at->format('H:i') }}
                                    </p>
                                </td>

                                <!-- STATUS -->
                                <td class="px-4 py-4">

                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">

                                        <span class="size-1.5 rounded-full bg-amber-500"></span>

                                        En attente
                                    </span>
                                </td>

                                <!-- ACTION -->
                                <td class="px-5 py-4 text-right">

                                    <a href="{{ route('responsable-conferences.requests.index', ['status' => 'pending']) }}"
                                       class="inline-flex rounded-lg bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 transition hover:bg-blue-100">

                                        Examiner
                                    </a>
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">

                                    <div class="mx-auto grid size-12 place-items-center rounded-full bg-emerald-50 text-emerald-600">
                                        <x-admin.icon name="clipboard-check" class="size-6"/>
                                    </div>

                                    <p class="mt-3 font-bold text-[#0F2347]">
                                        Aucune demande en attente
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Toutes les demandes ont été traitées.
                                    </p>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>
                </table>

            </div>

            @if($pendingCount > 5)
                <div class="border-t border-slate-100 bg-slate-50 px-5 py-4 text-center">

                    <a href="{{ route('responsable-conferences.requests.index') }}"
                       class="text-sm font-bold text-blue-600 hover:text-blue-800">

                        Voir les {{ $pendingCount }} demandes en attente

                    </a>
                </div>
            @endif

        </section>

        <!-- SLOT AVAILABILITY -->
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <div>
                <h2 class="text-lg font-extrabold text-[#0F2347]">
                    Disponibilité des créneaux
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Répartition par statut
                </p>
            </div>

            @php
                $availablePercentage = $totalSlots > 0
                    ? ($availableSlots / $totalSlots) * 100
                    : 0;
            @endphp

            <!-- DONUT -->
            <div class="mt-8 flex justify-center">

                <div class="relative grid size-44 place-items-center rounded-full"
                     role="img"
                     aria-label="{{ $availableSlots }} créneaux disponibles sur {{ $totalSlots }}"
                     style="background:conic-gradient(#10B981 {{ $availablePercentage }}%, #E2E8F0 {{ $availablePercentage }}%)">

                    <div class="flex size-32 flex-col items-center justify-center rounded-full bg-white">

                        <span class="text-4xl font-black text-[#0F2347]">
                            {{ $availableSlots }}
                        </span>

                        <span class="text-xs font-semibold text-slate-500">
                            Disponibles
                        </span>

                        <span class="mt-1 text-[11px] text-slate-400">
                            sur {{ $totalSlots }}
                        </span>

                    </div>
                </div>
            </div>

            <!-- LEGEND -->
            <div class="mt-8 space-y-4">

                @foreach([
                    ['Disponible', $slotStatusCounts['available'], 'bg-emerald-500'],
                    ['En option', $slotStatusCounts['option'], 'bg-amber-500'],
                    ['Réservé', $slotStatusCounts['reserved'], 'bg-blue-500'],
                    ['Payé', $slotStatusCounts['paid'], 'bg-violet-500'],
                ] as [$label, $count, $color])

                    <div class="flex items-center justify-between">

                        <div class="flex items-center gap-2.5">

                            <span class="size-2.5 rounded-full {{ $color }}"></span>

                            <span class="text-sm text-slate-600">
                                {{ $label }}
                            </span>
                        </div>

                        <span class="text-sm font-bold text-[#0F2347]">
                            {{ $count }}
                        </span>

                    </div>

                @endforeach
            </div>

            <a href="{{ route('responsable-conferences.planning') }}"
               class="mt-7 block rounded-xl bg-slate-50 px-4 py-3 text-center text-sm font-bold text-blue-700 transition hover:bg-blue-50">

                Consulter le planning
            </a>

        </section>
    </div>

    <!-- SECOND GRID -->
    <div class="mt-6 grid gap-6 xl:grid-cols-3">

        <!-- UPCOMING SLOTS -->
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">

            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">

                <div>
                    <h2 class="text-lg font-extrabold text-[#0F2347]">
                        Prochains créneaux
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Programme Conférences & Lounge
                    </p>
                </div>

                <a href="{{ route('responsable-conferences.planning') }}"
                   class="text-sm font-bold text-blue-600 hover:text-blue-800">
                    Planning complet
                </a>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">

                @forelse($upcomingSlots as $slot)

                    <article class="rounded-xl border border-slate-200 p-4 transition hover:border-blue-200 hover:shadow-sm">

                        <div class="flex items-start justify-between gap-3">

                            <div>
                                <p class="font-extrabold text-[#0F2347]">
                                    {{ $slot->code }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $slot->space?->name ?? $slot->space?->type?->label() ?? 'Espace' }}
                                </p>
                            </div>

                            <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold {{ $slot->status->pillClasses() }}">

                                {{ $slot->status->label() }}
                            </span>
                        </div>

                        @if($slot->exhibitor?->company_name)

                            <div class="mt-3 flex items-center gap-2">

                                @if($slot->exhibitor->logo_url)

                                    <img
                                        src="{{ $slot->exhibitor->logo_url }}"
                                        alt=""
                                        loading="lazy"
                                        class="size-7 rounded-lg border border-slate-200 object-contain p-0.5"
                                    >

                                @endif

                                <p class="truncate text-xs font-semibold text-slate-600">
                                    {{ $slot->exhibitor->company_name }}
                                </p>
                            </div>

                        @endif

                        <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">

                            <span class="text-xs text-slate-500">
                                {{ $slot->date->format('d/m/Y') }}
                            </span>

                            <span class="text-xs font-bold text-[#0F2347]">
                                {{ $slot->time_range }}
                            </span>
                        </div>
                    </article>

                @empty

                    <div class="col-span-full rounded-xl border border-dashed border-slate-200 p-8 text-center">

                        <p class="text-sm font-semibold text-slate-500">
                            Aucun créneau à venir.
                        </p>

                    </div>

                @endforelse

            </div>
        </section>

        <!-- DECISION HISTORY -->
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <div class="mb-5 flex items-center justify-between gap-3">

                <h2 class="text-lg font-extrabold text-[#0F2347]">
                    Dernières décisions
                </h2>

                <a href="{{ route('responsable-conferences.history') }}"
                   class="text-xs font-bold text-blue-600 hover:text-blue-800">
                    Historique
                </a>
            </div>

            <div class="space-y-5">

                @forelse($recentDecisions as $decision)

                    @php
                        $accepted = $decision->decision === 'accepted';
                        $cancelled = $decision->decision === 'cancelled';
                    @endphp

                    <article class="flex items-start gap-3">

                        <span @class([
                            'grid size-10 shrink-0 place-items-center rounded-xl text-lg font-bold',
                            'bg-emerald-50 text-emerald-600' => $accepted,
                            'bg-red-50 text-red-600' => !$accepted && !$cancelled,
                            'bg-slate-50 text-slate-600' => $cancelled,
                        ])>

                            {{ $accepted ? '✓' : ($cancelled ? '↺' : '×') }}
                        </span>

                        <div class="min-w-0 flex-1">

                            <p class="truncate text-sm font-bold text-[#0F2347]">
                                {{ $decision->exhibitor_name }}
                            </p>

                            <p class="mt-1 truncate text-xs text-slate-500">
                                {{ $decision->request_title }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ $decision->processed_at?->format('d/m/Y à H:i') ?? '—' }}
                            </p>

                            <p class="mt-1 truncate text-xs text-slate-400">
                                Par {{ $decision->processor_name }}
                            </p>
                        </div>

                        <span @class([
                            'whitespace-nowrap rounded-full px-2 py-1 text-[11px] font-bold',
                            'bg-emerald-50 text-emerald-700' => $accepted,
                            'bg-red-50 text-red-700' => !$accepted && !$cancelled,
                            'bg-slate-50 text-slate-700' => $cancelled,
                        ])>

                            {{ $accepted ? 'Acceptée' : ($cancelled ? 'Annulée' : 'Refusée') }}
                        </span>
                    </article>

                @empty

                    <div class="rounded-xl bg-slate-50 px-4 py-8 text-center">

                        <p class="text-sm font-semibold text-slate-500">
                            Aucune décision enregistrée.
                        </p>

                    </div>

                @endforelse
            </div>
        </section>
    </div>

    <!-- QUICK ACTIONS -->
    <section class="mt-6 rounded-2xl bg-[#0F2347] p-6 text-white shadow-sm">

        <div class="flex flex-wrap items-center justify-between gap-4">

            <div>
                <h2 class="text-lg font-extrabold">
                    Gestion des réservations
                </h2>

                <p class="mt-2 max-w-xl text-sm text-blue-200">
                    Consultez les demandes, contrôlez la disponibilité
                    des créneaux et validez les réservations.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">

                <a href="{{ route('responsable-conferences.planning') }}"
                   class="rounded-xl border border-white/20 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/10">

                    Voir le planning
                </a>

                <a href="{{ route('responsable-conferences.requests.index') }}"
                   class="rounded-xl bg-[#2563EB] px-4 py-3 text-sm font-bold text-white transition hover:bg-blue-500">

                    Gérer les demandes
                </a>
            </div>
        </div>
    </section>

</x-responsable-conferences.layout>
