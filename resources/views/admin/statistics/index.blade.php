
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-[#2563EB]">
                Administration / Reporting
            </p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                Statistiques du salon
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Analyse globale des inscriptions, des commerciaux
                et des demandes d'équipements.
            </p>
        </div>

        <span class="w-fit rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600">
            Actualisé au {{ now()->format('d/m/Y') }}
        </span>
    </div>

    <!-- Vue générale -->
    <section class="mt-8">
        <h2 class="mb-4 text-lg font-bold text-slate-900">
            Vue générale
        </h2>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-admin.stat-card
                label="Exposants"
                :value="$exhibitorsCount"
                icon="users"
                description="Entreprises enregistrées."
            />

            <x-admin.stat-card
                label="Commerciaux"
                :value="$commercialsCount"
                icon="users"
                :description="$activeCommercials.' compte(s) actif(s).'"
            />

            <x-admin.stat-card
                label="Surface des stands"
                :value="number_format($totalArea, 2, ',', ' ').' m²'"
                icon="chart"
                description="Somme des surfaces attribuées."
            />

            <x-admin.stat-card
                label="Équipements"
                :value="$equipmentCount"
                icon="box"
                :description="$activeEquipment.' équipement(s) actif(s).'"
            />
        </div>
    </section>

    <!-- Demandes -->
    <section class="mt-8">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-bold text-slate-900">
                Suivi des demandes
            </h2>

            <a href="{{ route('admin.demandes.index') }}"
               class="text-sm font-semibold text-blue-700 hover:underline">
                Voir toutes les demandes
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-admin.stat-card
                label="Total demandes"
                :value="$requestsCount"
                icon="inbox"
                description="Toutes les demandes reçues."
            />

            <x-admin.stat-card
                label="En attente"
                :value="$pendingRequests"
                icon="inbox"
                description="Demandes à traiter."
            />

            <x-admin.stat-card
                label="Approuvées"
                :value="$approvedRequests"
                icon="box"
                description="Demandes acceptées."
            />

            <x-admin.stat-card
                label="Refusées"
                :value="$rejectedRequests"
                icon="inbox"
                description="Demandes non acceptées."
            />
        </div>
    </section>

    <!-- Finance -->
    <section class="mt-6 grid gap-4 lg:grid-cols-3">
        <article class="rounded-2xl bg-[#0F2347] p-6 text-white shadow-sm">
            <p class="text-sm font-medium text-blue-100">
                Montant total demandé
            </p>

            <p class="mt-3 text-3xl font-bold">
                {{ number_format($totalRequestedAmount, 2, ',', ' ') }}
                <span class="text-lg text-blue-200">DH</span>
            </p>

            <p class="mt-3 text-xs text-blue-200">
                Somme des demandes, tous statuts confondus.
            </p>
        </article>

        <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-medium text-slate-500">
                Montant des demandes approuvées
            </p>

            <p class="mt-3 text-3xl font-bold text-emerald-700">
                {{ number_format($approvedAmount, 2, ',', ' ') }}
                <span class="text-lg">DH</span>
            </p>

            <p class="mt-3 text-xs text-slate-500">
                Montant accepté, pas nécessairement encaissé.
            </p>
        </article>

        <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-medium text-slate-500">
                Taux d'approbation
            </p>

            <p class="mt-3 text-3xl font-bold text-blue-700">
                {{ number_format($approvalRate, 1, ',', ' ') }}%
            </p>

            <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-blue-600"
                     style="width: {{ $approvalRate }}%">
                </div>
            </div>

            <p class="mt-3 text-xs text-slate-500">
                Demandes approuvées / toutes les demandes.
            </p>
        </article>
    </section>

    <!-- Graphiques -->
    <section class="mt-8 grid gap-6 xl:grid-cols-2">

        <!-- Exposants par mois -->
        <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        Évolution des exposants
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Inscriptions sur les 6 derniers mois
                    </p>
                </div>

                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                    Exposants
                </span>
            </div>

            <div class="mt-8 flex h-56 items-end justify-between gap-3 border-b border-slate-200 pb-3">
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

                    <div class="group flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2">
                        <span class="text-xs font-bold text-slate-600">
                            {{ $month['exhibitors'] }}
                        </span>

                        <div class="relative flex h-44 w-full max-w-14 items-end">
                            <div
                                class="w-full rounded-t-lg bg-blue-600 transition-colors group-hover:bg-blue-700"
                                style="height: {{ $height }}%"
                                title="{{ $month['exhibitors'] }} exposant(s) — {{ $month['label'] }}"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3 flex justify-between gap-3">
                @foreach ($monthlyStats as $month)
                    <span class="min-w-0 flex-1 text-center text-[10px] font-medium text-slate-500 sm:text-xs">
                        {{ $month['label'] }}
                    </span>
                @endforeach
            </div>
        </article>

        <!-- Demandes par mois -->
        <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        Évolution des demandes
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Demandes enregistrées sur les 6 derniers mois
                    </p>
                </div>

                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    Demandes
                </span>
            </div>

            <div class="mt-8 flex h-56 items-end justify-between gap-3 border-b border-slate-200 pb-3">
                @foreach ($monthlyStats as $month)
                    @php
                        $height = $month['requests'] > 0
                            ? max(
                                6,
                                round(
                                    $month['requests'] / $maxMonthly * 100,
                                    1
                                )
                            )
                            : 0;
                    @endphp

                    <div class="group flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2">
                        <span class="text-xs font-bold text-slate-600">
                            {{ $month['requests'] }}
                        </span>

                        <div class="relative flex h-44 w-full max-w-14 items-end">
                            <div
                                class="w-full rounded-t-lg bg-emerald-500 transition-colors group-hover:bg-emerald-600"
                                style="height: {{ $height }}%"
                                title="{{ $month['requests'] }} demande(s) — {{ $month['label'] }}"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3 flex justify-between gap-3">
                @foreach ($monthlyStats as $month)
                    <span class="min-w-0 flex-1 text-center text-[10px] font-medium text-slate-500 sm:text-xs">
                        {{ $month['label'] }}
                    </span>
                @endforeach
            </div>
        </article>
    </section>

    <!-- Commercials + répartition -->
    <section class="mt-6 grid gap-6 xl:grid-cols-2">

        <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold">
                        Performance commerciale
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Classement par nombre d'exposants
                    </p>
                </div>
                <a href="{{ route('admin.commerciaux.index') }}"
                   class="text-xs font-semibold text-blue-700 hover:underline">
                    Commerciaux
                </a>
            </div>

            <div class="mt-7 space-y-6">
                @forelse ($commercialStats as $commercial)
                    @php
                        $progress = round(
                            $commercial->exhibitors_count
                            / $maxCommercialExhibitors * 100,
                            1
                        );
                    @endphp

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-4">
                            <span class="truncate text-sm font-semibold text-slate-700">
                                {{ $commercial->name }}
                            </span>
                            <span class="shrink-0 text-sm font-bold text-blue-700">
                                {{ $commercial->exhibitors_count }}
                            </span>
                        </div>

                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-[#2563EB]"
                                 style="width: {{ $progress }}%">
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="py-10 text-center text-sm text-slate-500">
                        Aucun commercial enregistré.
                    </p>
                @endforelse
            </div>
        </article>

        <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-lg font-bold">
                Répartition des demandes
            </h2>
            <p class="mt-1 text-xs text-slate-500">
                Distribution par statut
            </p>

            <div class="mt-8 space-y-7">
                @foreach ($requestDistribution as $status)
                    @php
                        $barColor = match ($status['color']) {
                            'emerald' => 'bg-emerald-500',
                            'red' => 'bg-red-500',
                            default => 'bg-amber-500',
                        };
                    @endphp

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="text-sm font-semibold text-slate-700">
                                {{ $status['label'] }}
                            </span>
                            <span class="text-sm font-bold text-slate-800">
                                {{ $status['count'] }}
                                <span class="font-normal text-slate-400">
                                    ({{ number_format($status['percent'], 1, ',', ' ') }}%)
                                </span>
                            </span>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full {{ $barColor }}"
                                 style="width: {{ $status['percent'] }}%">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="mt-8 rounded-xl bg-slate-50 p-4 text-xs leading-5 text-slate-500">
                Les statistiques reflètent les demandes enregistrées
                dans la plateforme.
            </p>
        </article>
    </section>

    <!-- Dernières demandes -->
    <section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
            <div>
                <h2 class="text-lg font-bold">
                    Dernières demandes
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Suivi des demandes récentes
                </p>
            </div>

            <a href="{{ route('admin.demandes.index') }}"
               class="text-sm font-semibold text-blue-700 hover:underline">
                Voir toutes les demandes
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Référence</th>
                        <th class="px-5 py-4">Exposant</th>
                        <th class="px-5 py-4">Montant</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4">Date</th>
                        <th class="px-5 py-4">Détails</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentRequests as $order)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-bold">
                                #{{ $order->id }}
                            </td>
                            <td class="px-5 py-4">
                                {{ $order->exhibitor?->company_name ?? '—' }}
                            </td>
                            <td class="px-5 py-4 font-semibold">
                                {{ number_format((float) $order->total_amount, 2, ',', ' ') }} DH
                            </td>
                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
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
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.demandes.show', $order) }}"
                                   class="font-semibold text-blue-700 hover:underline">
                                    Voir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"
                                class="px-5 py-10 text-center text-slate-500">
                                Aucune demande enregistrée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <p class="mt-6 text-xs text-slate-400">
        Données mises à jour lors du chargement de la page.
        Les montants indiqués ne constituent pas un suivi des paiements.
    </p>

</x-admin.layout>
