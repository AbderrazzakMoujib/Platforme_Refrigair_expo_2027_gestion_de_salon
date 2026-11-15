
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    <!-- EN-TÊTE -->
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-[#2563EB]">
                Administration / Vue globale
            </p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">
                Bonjour, {{ $admin->name }}
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Suivi général des exposants de Refrigair Expo 2027.
            </p>
        </div>

        <a href="{{ route('admin.commerciaux.create') }}"
           class="rounded-xl bg-[#2563EB] px-5 py-3 text-center text-sm font-semibold text-white transition hover:bg-blue-700">
            + Ajouter un commercial
        </a>
    </div>

    <!-- STATISTIQUES PRINCIPALES -->
    <section class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-admin.stat-card
            label="Commerciaux"
            :value="$commercialsCount"
            icon="users"
            description="Nombre total de commerciaux."
        />

        <x-admin.stat-card
            label="Exposants"
            :value="$exposantsCount"
            icon="users"
            description="Entreprises enregistrées."
        />

        <x-admin.stat-card
            label="Badges autorisés"
            :value="$totalAuthorizedBadges"
            icon="badge"
            description="Quotas attribués aux exposants."
        />

        <x-admin.stat-card
            label="Badges enregistrés"
            :value="$totalRegisteredBadges"
            icon="badge"
            description="Badges remplis par les exposants."
        />

        <x-admin.stat-card
            label="Badges restants"
            :value="$remainingBadges"
            icon="badge"
            description="Badges encore disponibles."
        />

        <x-admin.stat-card
            label="Exposants complets"
            :value="$completedExhibitors"
            icon="users"
            description="Quota de badges entièrement rempli."
        />

        <x-admin.stat-card
            label="Exposants partiels"
            :value="$partialExhibitors"
            icon="inbox"
            description="Badges partiellement enregistrés."
        />

        <x-admin.stat-card
            label="Exposants en attente"
            :value="$pendingExhibitors"
            icon="inbox"
            description="Aucun badge enregistré."
        />

    </section>

    <!-- PROGRESSION + ACTIVITÉS -->
    <section class="mt-7 grid gap-6 xl:grid-cols-5">

        <!-- PROGRESSION -->
        <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 xl:col-span-3">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        Progression générale des badges
                    </h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Avancement de tous les exposants.
                    </p>
                </div>

                <span class="rounded-xl bg-blue-50 px-4 py-2 text-xl font-bold text-blue-700">
                    {{ $badgeProgress }}%
                </span>
            </div>

            <div class="mt-8 grid grid-cols-3 gap-3 text-center">

                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-2xl font-bold text-slate-900">
                        {{ $totalAuthorizedBadges }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        Autorisés
                    </p>
                </div>

                <div class="rounded-xl bg-emerald-50 p-4">
                    <p class="text-2xl font-bold text-emerald-700">
                        {{ $totalRegisteredBadges }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        Enregistrés
                    </p>
                </div>

                <div class="rounded-xl bg-amber-50 p-4">
                    <p class="text-2xl font-bold text-amber-700">
                        {{ $remainingBadges }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        Restants
                    </p>
                </div>

            </div>

            <div class="mt-8">
                <div class="mb-3 flex items-center justify-between text-sm">
                    <span class="font-medium text-slate-600">
                        Taux de remplissage
                    </span>
                    <span class="font-bold text-blue-700">
                        {{ $badgeProgress }}%
                    </span>
                </div>

                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                    <div
                        class="h-full rounded-full bg-blue-600 transition-all duration-500"
                        style="width: {{ $badgeProgress }}%">
                    </div>
                </div>
            </div>

            <div class="mt-8 grid gap-3 sm:grid-cols-3">

                <div class="rounded-xl border border-emerald-100 p-4">
                    <p class="text-sm text-slate-500">Complets</p>
                    <p class="mt-2 text-2xl font-bold text-emerald-600">
                        {{ $completedExhibitors }}
                    </p>
                </div>

                <div class="rounded-xl border border-amber-100 p-4">
                    <p class="text-sm text-slate-500">Partiels</p>
                    <p class="mt-2 text-2xl font-bold text-amber-600">
                        {{ $partialExhibitors }}
                    </p>
                </div>

                <div class="rounded-xl border border-red-100 p-4">
                    <p class="text-sm text-slate-500">En attente</p>
                    <p class="mt-2 text-2xl font-bold text-red-600">
                        {{ $pendingExhibitors }}
                    </p>
                </div>

            </div>

        </article>

        <!-- ACTIVITÉS RÉCENTES -->
        <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 xl:col-span-2">

            <h2 class="text-lg font-bold text-slate-900">
                Activités récentes
            </h2>

            <p class="mt-2 text-sm text-slate-500">
                Derniers comptes créés dans la plateforme.
            </p>

            <div class="mt-6 space-y-4">

                @forelse ($recentAccounts as $account)

                    <div class="flex items-start gap-3">

                        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-blue-50 font-bold text-blue-700">
                            {{ mb_strtoupper(mb_substr($account->name, 0, 1)) }}
                        </span>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">
                                {{ $account->name }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                Compte {{ $account->role->value }}
                                créé le {{ $account->created_at?->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>

                @empty

                    <p class="rounded-xl bg-slate-50 p-5 text-sm text-slate-500">
                        Aucune activité disponible.
                    </p>

                @endforelse

            </div>

        </article>

    </section>

    <!-- SUIVI PAR COMMERCIAL -->
    <section class="mt-7 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">

        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">

            <div>
                <h2 class="text-lg font-bold text-slate-900">
                    Suivi par commercial
                </h2>
                <p class="mt-2 text-sm text-slate-500">
                    Performance et remplissage des badges par commercial.
                </p>
            </div>

            <a href="{{ route('admin.commerciaux.index') }}"
               class="text-sm font-semibold text-[#2563EB] hover:underline">
                Voir tous les commerciaux
            </a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">

                <thead class="border-b border-slate-100 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-4">Commercial</th>
                        <th class="px-4 py-4">Exposants</th>
                        <th class="px-4 py-4">Complets</th>
                        <th class="px-4 py-4">En attente</th>
                        <th class="px-4 py-4">Badges</th>
                        <th class="px-4 py-4">Progression</th>
                        <th class="px-4 py-4">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse ($commercialStats as $stat)

                        <tr class="transition hover:bg-slate-50">

                            <td class="px-4 py-4">
                                <p class="font-semibold text-slate-800">
                                    {{ $stat['commercial']->name }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $stat['commercial']->email }}
                                </p>
                            </td>

                            <td class="px-4 py-4">
                                {{ $stat['exhibitors'] }}
                            </td>

                            <td class="px-4 py-4 font-semibold text-emerald-600">
                                {{ $stat['complete'] }}
                            </td>

                            <td class="px-4 py-4 font-semibold text-amber-600">
                                {{ $stat['pending'] }}
                            </td>

                            <td class="px-4 py-4">
                                {{ $stat['registered'] }}
                                /
                                {{ $stat['authorized'] }}
                            </td>

                            <td class="px-4 py-4">
                                <div class="min-w-28">
                                    <span class="text-xs font-semibold text-blue-700">
                                        {{ $stat['progress'] }}%
                                    </span>

                                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-blue-600"
                                             style="width: {{ $stat['progress'] }}%">
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <a href="{{ route('admin.commerciaux.show', $stat['commercial']) }}"
                                   class="font-semibold text-blue-700 hover:underline">
                                    Voir
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                                Aucun commercial enregistré.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>
    </section>

    <!-- DERNIERS EXPOSANTS -->
    <section class="mt-7 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">

        <div>
            <h2 class="text-lg font-bold text-slate-900">
                Derniers exposants enregistrés
            </h2>

            <p class="mt-2 text-sm text-slate-500">
                Suivi des dernières entreprises ajoutées par les commerciaux.
            </p>
        </div>

        <div class="mt-6 overflow-x-auto">

            <table class="w-full min-w-[720px] text-left text-sm">

                <thead class="border-b border-slate-100 bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-4">Entreprise</th>
                        <th class="px-4 py-4">Commercial</th>
                        <th class="px-4 py-4">Stand</th>
                        <th class="px-4 py-4">Badges</th>
                        <th class="px-4 py-4">Statut</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse ($recentExhibitors as $exhibitor)

                        @php
                            $quota = (int) $exhibitor->authorized_badges;
                            $filled = (int) $exhibitor->badges_count;

                            $status = $quota === 0
                                ? 'Aucun quota'
                                : ($filled >= $quota
                                    ? 'Complet'
                                    : ($filled > 0
                                        ? 'En cours'
                                        : 'En attente'));
                        @endphp

                        <tr class="hover:bg-slate-50">

                            <td class="px-4 py-4">
                                <p class="font-semibold text-slate-800">
                                    {{ $exhibitor->company_name }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $exhibitor->contact_name }}
                                </p>
                            </td>

                            <td class="px-4 py-4">
                                {{ $exhibitor->commercial?->name ?? '—' }}
                            </td>

                            <td class="px-4 py-4">
                                {{ $exhibitor->stand_number ?: '—' }}
                            </td>

                            <td class="px-4 py-4 font-medium">
                                {{ $filled }} / {{ $quota }}
                            </td>

                            <td class="px-4 py-4">
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-emerald-50 text-emerald-700' => $status === 'Complet',
                                    'bg-amber-50 text-amber-700' => $status === 'En cours',
                                    'bg-red-50 text-red-700' => $status === 'En attente',
                                    'bg-slate-100 text-slate-600' => $status === 'Aucun quota',
                                ])>
                                    {{ $status }}
                                </span>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                                Aucun exposant enregistré.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>

        </div>
    </section>

</x-admin.layout>
