<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">
    <div class="space-y-5">
        {{-- EN-TÊTE --}}
        <section class="relative overflow-hidden rounded-2xl border border-blue-100 bg-gradient-to-r from-white via-blue-50 to-blue-100/70 px-5 py-6 shadow-sm sm:px-7">
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                        <x-admin.icon name="overview" class="size-7" />
                    </span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-700">Administration / Vue globale</p>
                        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">Tableau de bord administrateur</h1>
                        <p class="mt-2 text-sm text-slate-600">Vue d'ensemble des activités et du suivi de Refrigair Expo 2027.</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3 lg:shrink-0">
                    <a href="{{ route('admin.salons.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                        <span class="text-lg leading-none">+</span> Ajouter un salon
                    </a>
                    <a href="{{ route('admin.statistiques.index') }}" class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-white px-5 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">
                        Consulter les rapports
                    </a>
                </div>
            </div>
        </section>

<!-- SUIVI PAR COMMERCIAL -->
<section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-bold text-[#0F2347]">
            Suivi par commercial
        </h2>

        <a href="{{ route('admin.commerciaux.index') }}"
           class="text-sm font-semibold text-blue-600 hover:underline">
            Voir tous les commerciaux
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[700px] text-left text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="p-3">Commercial</th>
                    <th class="p-3">Exposants</th>
                    <th class="p-3">Badges</th>
                    <th class="p-3">Progression</th>
                    <th class="p-3">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                @forelse ($commercialStats as $stat)
                    <tr>
                        <td class="p-3 font-semibold">
                            {{ $stat['commercial']->name }}
                        </td>

                        <td class="p-3">
                            {{ $stat['exhibitors'] }}
                        </td>

                        <td class="p-3">
                            {{ $stat['registered'] }} /
                            {{ $stat['authorized'] }}
                        </td>

                        <td class="p-3">
                            <div class="w-32">
                                <span class="text-xs font-semibold text-blue-700">
                                    {{ $stat['progress'] }}%
                                </span>
                                <div class="mt-2 h-2 rounded-full bg-slate-100">
                                    <div class="h-2 rounded-full bg-blue-600"
                                         style="width: {{ $stat['progress'] }}%">
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="p-3">
                            <a href="{{ route('admin.commerciaux.show', $stat['commercial']) }}"
                               class="font-semibold text-blue-600 hover:underline">
                                Voir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-slate-500">
                            Aucun commercial enregistré.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

   </section>


        {{-- 6 KPI --}}
        @php
            $kpis = [
                ['label' => 'Salons', 'value' => $salonsCount, 'icon' => 'calendar', 'color' => 'blue'],
                ['label' => 'Commerciaux', 'value' => $commercialsCount, 'icon' => 'users', 'color' => 'green'],
                ['label' => 'Exposants', 'value' => $exposantsCount, 'icon' => 'storefront', 'color' => 'blue'],
                ['label' => 'Badges autorisés', 'value' => $totalAuthorizedBadges, 'icon' => 'badge', 'color' => 'purple'],
                ['label' => 'Badges enregistrés', 'value' => $totalRegisteredBadges, 'icon' => 'badge-users', 'color' => 'green'],
                ['label' => 'Demandes en attente', 'value' => $pendingRequests, 'icon' => 'layers', 'color' => 'amber'],
            ];
            $tones = [
                'blue' => 'bg-blue-50 text-blue-600',
                'green' => 'bg-emerald-50 text-emerald-600',
                'purple' => 'bg-violet-50 text-violet-600',
                'amber' => 'bg-amber-50 text-amber-600',
            ];
        @endphp
        <section class="grid grid-cols-2 gap-3 sm:grid-cols-3 2xl:grid-cols-6" aria-label="Indicateurs clés">
            @foreach ($kpis as $kpi)
                <article class="flex min-w-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl {{ $tones[$kpi['color']] }}">
                        <x-admin.icon :name="$kpi['icon']" class="size-6" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-medium leading-4 text-slate-600">{{ $kpi['label'] }}</p>
                        <p class="mt-1 text-2xl font-extrabold tracking-tight text-[#0F2347]">{{ number_format($kpi['value'], 0, ',', ' ') }}</p>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- PROGRESSION + STATUT DES DEMANDES --}}
        <section class="grid gap-5 xl:grid-cols-5">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-3 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-600"><x-admin.icon name="chart" class="size-6" /></span>
                        <h2 class="text-lg font-bold text-[#0F2347]">Progression des inscriptions</h2>
                    </div>
                    <a href="{{ route('admin.statistiques.index') }}" class="text-sm font-semibold text-blue-600 hover:underline">Consulter les statistiques →</a>
                </div>
                <div class="mt-6 flex flex-col items-center gap-6 md:flex-row">
                    <div class="relative grid size-36 shrink-0 place-items-center rounded-full" style="background: conic-gradient(#2563EB {{ $badgeProgress }}%, #E2E8F0 0);">
                        <div class="grid size-[112px] place-items-center rounded-full bg-white">
                            <span class="text-3xl font-extrabold text-[#0F2347]">{{ $badgeProgress }}%</span>
                        </div>
                    </div>
                    <div class="w-full min-w-0 flex-1">
                        <p class="text-sm text-slate-600"><strong class="text-lg text-[#0F2347]">{{ number_format($totalRegisteredBadges, 0, ',', ' ') }}</strong> badges enregistrés sur <strong class="text-lg text-[#0F2347]">{{ number_format($totalAuthorizedBadges, 0, ',', ' ') }}</strong> autorisés</p>
                        <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-200" role="progressbar" aria-label="Progression des badges" aria-valuenow="{{ $badgeProgress }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="h-full rounded-full bg-blue-600" style="width: {{ $badgeProgress }}%"></div>
                        </div>
                        <div class="mt-5 grid grid-cols-1 gap-2 sm:grid-cols-3">
                            <div class="rounded-xl bg-emerald-50 p-3"><p class="text-xs font-medium text-emerald-800">Exposants complets</p><p class="mt-1 text-2xl font-bold text-emerald-700">{{ $completedExhibitors }}</p></div>
                            <div class="rounded-xl bg-blue-50 p-3"><p class="text-xs font-medium text-blue-800">Exposants partiels</p><p class="mt-1 text-2xl font-bold text-blue-700">{{ $partialExhibitors }}</p></div>
                            <div class="rounded-xl bg-amber-50 p-3"><p class="text-xs font-medium text-amber-800">Exposants en attente</p><p class="mt-1 text-2xl font-bold text-amber-700">{{ $pendingExhibitors }}</p></div>
                        </div>
                    </div>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2 sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-600"><x-admin.icon name="box" class="size-6" /></span>
                    <h2 class="text-lg font-bold text-[#0F2347]">État des demandes</h2>
                </div>
                @php
                    $pendingPct = $totalRequests ? ($pendingRequests / $totalRequests) * 100 : 0;
                    $approvedPct = $totalRequests ? ($approvedRequests / $totalRequests) * 100 : 0;
                    $stopPending = $pendingPct;
                    $stopApproved = $pendingPct + $approvedPct;
                @endphp
                <div class="mt-6 flex flex-col items-center gap-5 sm:flex-row">
                    <div class="relative grid size-36 shrink-0 place-items-center rounded-full" style="background: conic-gradient({{ $totalRequests ? '#F59E0B' : '#E2E8F0' }} 0% {{ $stopPending }}%, {{ $totalRequests ? '#16A34A' : '#E2E8F0' }} {{ $stopPending }}% {{ $stopApproved }}%, {{ $totalRequests ? '#EF4444' : '#E2E8F0' }} {{ $stopApproved }}% 100%);">
                        <div class="flex size-[108px] flex-col items-center justify-center rounded-full bg-white">
                            <strong class="text-3xl font-extrabold text-[#0F2347]">{{ $totalRequests }}</strong>
                            <span class="text-xs text-slate-500">Demandes</span>
                        </div>
                    </div>
                    <div class="w-full space-y-2 text-sm">
                        <div class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2"><span class="size-2.5 rounded-full bg-amber-400"></span><span class="flex-1">En attente</span><strong>{{ $pendingRequests }}</strong><span class="text-slate-500">{{ $requestPercentages['pending'] }}%</span></div>
                        <div class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2"><span class="size-2.5 rounded-full bg-green-500"></span><span class="flex-1">Approuvées</span><strong>{{ $approvedRequests }}</strong><span class="text-slate-500">{{ $requestPercentages['approved'] }}%</span></div>
                        <div class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2"><span class="size-2.5 rounded-full bg-red-500"></span><span class="flex-1">Refusées</span><strong>{{ $rejectedRequests }}</strong><span class="text-slate-500">{{ $requestPercentages['rejected'] }}%</span></div>
                    </div>
                </div>
            </article>
        </section>

        {{-- SALONS + ACTIONS RAPIDES --}}
        <section class="grid gap-5 xl:grid-cols-5">
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-3">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="flex items-center gap-2 text-lg font-bold text-[#0F2347]"><x-admin.icon name="calendar" class="text-blue-600" /> Salons en cours et à venir</h2>
                    <a href="{{ route('admin.salons.index') }}" class="text-sm font-semibold text-blue-600 hover:underline">Gérer les salons →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[540px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs text-slate-600"><tr><th class="rounded-l-lg px-3 py-3">Salon</th><th class="px-3 py-3">Dates</th><th class="px-3 py-3">Lieu</th><th class="rounded-r-lg px-3 py-3">Statut</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($upcomingSalons as $salon)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-3 py-3 font-semibold"><a href="{{ route('admin.salons.show', $salon) }}" class="hover:text-blue-600">{{ $salon->name }} {{ $salon->edition }}</a></td>
                                    <td class="whitespace-nowrap px-3 py-3 text-slate-600">{{ $salon->start_date->format('d/m/Y') }} – {{ $salon->end_date->format('d/m/Y') }}</td>
                                    <td class="px-3 py-3 text-slate-600">{{ $salon->venue }}, {{ $salon->city }}</td>
                                    <td class="px-3 py-3"><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-700' => $salon->status === 'active', 'bg-blue-50 text-blue-700' => $salon->status !== 'active'])>{{ $salon->status_label }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-8 text-center text-slate-500">Aucun salon en cours ou à venir.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
                <h2 class="mb-5 flex items-center gap-2 text-lg font-bold text-[#0F2347]"><x-admin.icon name="zap" class="text-blue-600" /> Actions rapides</h2>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('admin.commerciaux.create') }}" class="flex min-h-28 flex-col items-center justify-center gap-3 rounded-xl bg-blue-50 p-3 text-center text-sm font-semibold text-blue-800 transition hover:bg-blue-100"><x-admin.icon name="user-plus" class="size-7" /> Ajouter un commercial</a>
                    <a href="{{ route('admin.badges.index') }}" class="flex min-h-28 flex-col items-center justify-center gap-3 rounded-xl bg-emerald-50 p-3 text-center text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100"><x-admin.icon name="clipboard-check" class="size-7" /> Consulter les badges</a>
                    <a href="{{ route('admin.equipements.index') }}" class="flex min-h-28 flex-col items-center justify-center gap-3 rounded-xl bg-violet-50 p-3 text-center text-sm font-semibold text-violet-800 transition hover:bg-violet-100"><x-admin.icon name="box" class="size-7" /> Gérer les équipements</a>
                    <a href="{{ route('admin.notifications.index') }}" class="flex min-h-28 flex-col items-center justify-center gap-3 rounded-xl bg-orange-50 p-3 text-center text-sm font-semibold text-orange-800 transition hover:bg-orange-100"><x-admin.icon name="bell" class="size-7" /> Voir les notifications</a>
                </div>
            </article>
        </section>

        {{-- DERNIERS EXPOSANTS + DEMANDES --}}
        <section class="grid gap-5 xl:grid-cols-2">
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-bold text-[#0F2347]">Exposants récemment enregistrés</h2>
                    <a href="{{ route('admin.exposants.index') }}" class="text-sm font-semibold text-blue-600 hover:underline">Voir tous →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[580px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs text-slate-600"><tr><th class="px-3 py-3">Entreprise</th><th class="px-3 py-3">Commercial</th><th class="px-3 py-3">Stand</th><th class="px-3 py-3">Badges</th><th class="px-3 py-3">Statut</th><th class="px-3 py-3">Action</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentExhibitors as $exhibitor)
                                @php
                                    $quota = (int) $exhibitor->authorized_badges;
                                    $registered = (int) $exhibitor->badges_count;
                                    $label = $quota === 0 ? 'Sans quota' : ($registered >= $quota ? 'Complet' : ($registered > 0 ? 'En cours' : 'En attente'));
                                    $color = match ($label) { 'Complet' => 'bg-emerald-50 text-emerald-700', 'En cours' => 'bg-blue-50 text-blue-700', 'En attente' => 'bg-amber-50 text-amber-700', default => 'bg-slate-100 text-slate-600' };
                                @endphp
                                <tr class="hover:bg-slate-50"><td class="px-3 py-3 font-semibold">{{ $exhibitor->company_name }}</td><td class="px-3 py-3 text-slate-600">{{ $exhibitor->commercial?->name ?? '—' }}</td><td class="px-3 py-3">{{ $exhibitor->stand_number ?: '—' }}</td><td class="px-3 py-3 whitespace-nowrap">{{ $registered }}/{{ $quota }}</td><td class="px-3 py-3"><span class="whitespace-nowrap rounded-full px-2 py-1 text-xs font-semibold {{ $color }}">{{ $label }}</span></td><td class="px-3 py-3"><a href="{{ route('admin.exposants.show', $exhibitor) }}" class="font-semibold text-blue-600 hover:underline">Voir</a></td></tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-8 text-center text-slate-500">Aucun exposant enregistré.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-bold text-[#0F2347]">Demandes d'équipements récentes</h2>
                    <a href="{{ route('admin.demandes.index') }}" class="text-sm font-semibold text-blue-600 hover:underline">Voir toutes →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[520px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs text-slate-600"><tr><th class="px-3 py-3">N° demande</th><th class="px-3 py-3">Entreprise</th><th class="px-3 py-3">Montant</th><th class="px-3 py-3">Statut</th><th class="px-3 py-3">Action</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentRequests as $order)
                                @php
                                    $status = match ($order->status) { 'approved' => 'Approuvée', 'rejected' => 'Refusée', default => 'En attente' };
                                    $color = match ($order->status) { 'approved' => 'bg-emerald-50 text-emerald-700', 'rejected' => 'bg-red-50 text-red-700', default => 'bg-amber-50 text-amber-700' };
                                @endphp
                                <tr class="hover:bg-slate-50"><td class="px-3 py-3 font-semibold">DEM-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</td><td class="px-3 py-3">{{ $order->exhibitor?->company_name ?? '—' }}</td><td class="whitespace-nowrap px-3 py-3">{{ number_format($order->total_amount, 2, ',', ' ') }} DH</td><td class="px-3 py-3"><span class="whitespace-nowrap rounded-full px-2 py-1 text-xs font-semibold {{ $color }}">{{ $status }}</span></td><td class="px-3 py-3"><a href="{{ route('admin.demandes.show', $order) }}" class="font-semibold text-blue-600 hover:underline">Consulter</a></td></tr>
                            @empty
                                <tr><td colspan="5" class="px-3 py-8 text-center text-slate-500">Aucune demande enregistrée.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </div>
</x-admin.layout>
