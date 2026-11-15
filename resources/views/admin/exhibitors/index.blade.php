
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <p class="text-sm font-semibold text-blue-600">
                Administration / Exposants
            </p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">
                Gestion des exposants
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Retrouvez toutes les entreprises inscrites au salon.
            </p>
        </div>

        <span class="w-fit rounded-xl bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700">
            {{ $totalExhibitors }} exposant(s)
        </span>
    </div>

    <!-- Statistiques générales -->
    <section class="mt-7 grid gap-4 sm:grid-cols-3">
        <x-admin.stat-card
            label="Total exposants"
            :value="$totalExhibitors"
            icon="users"
            description="Entreprises inscrites."
        />

        <x-admin.stat-card
            label="Badges autorisés"
            :value="$totalAuthorized"
            icon="badge"
            description="Quota total de badges."
        />

        <x-admin.stat-card
            label="Badges enregistrés"
            :value="$totalRegistered"
            icon="badge"
            description="Badges déjà remplis."
        />
    </section>

    <!-- Recherche et filtres -->
    <section class="mt-7 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">

        <form method="GET"
              action="{{ route('admin.exposants.index') }}"
              class="grid gap-4 lg:grid-cols-4">

            <div class="lg:col-span-2">
                <label for="search" class="mb-2 block text-sm font-semibold text-slate-700">
                    Rechercher
                </label>
                <input
                    id="search"
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Entreprise, responsable, email, stand..."
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div>
                <label for="commercial_id" class="mb-2 block text-sm font-semibold text-slate-700">
                    Commercial
                </label>
                <select
                    id="commercial_id"
                    name="commercial_id"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Tous les commerciaux</option>
                    @foreach ($commercials as $commercial)
                        <option value="{{ $commercial->id }}"
                            @selected(request('commercial_id') == $commercial->id)>
                            {{ $commercial->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="mb-2 block text-sm font-semibold text-slate-700">
                    Statut badges
                </label>
                <select
                    id="status"
                    name="status"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">Tous les statuts</option>
                    <option value="complete" @selected(request('status') === 'complete')>
                        Complet
                    </option>
                    <option value="partial" @selected(request('status') === 'partial')>
                        En cours
                    </option>
                    <option value="pending" @selected(request('status') === 'pending')>
                        En attente
                    </option>
                    <option value="no_quota" @selected(request('status') === 'no_quota')>
                        Aucun quota
                    </option>
                </select>
            </div>

            <div class="flex flex-wrap gap-3 lg:col-span-4">
                <button
                    type="submit"
                    class="rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700"
                >
                    Filtrer
                </button>

                <a
                    href="{{ route('admin.exposants.index') }}"
                    class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    Réinitialiser
                </a>
            </div>
        </form>
    </section>

    <!-- Tableau -->
    <section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-bold text-slate-900">
                Liste des entreprises
            </h2>
            <span class="text-sm text-slate-500">
                {{ $exhibitors->total() }} résultat(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Entreprise</th>
                        <th class="px-5 py-4">Commercial</th>
                        <th class="px-5 py-4">Stand</th>
                        <th class="px-5 py-4">Badges</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4">Accès</th>
                        <th class="px-5 py-4">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse ($exhibitors as $exhibitor)

                        @php
                            $quota = (int) $exhibitor->authorized_badges;
                            $filled = (int) $exhibitor->badges_count;

                            $status = $quota === 0
                                ? 'Aucun quota'
                                : ($filled >= $quota
                                    ? 'Complet'
                                    : ($filled > 0 ? 'En cours' : 'En attente'));
                        @endphp

                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-800">
                                    {{ $exhibitor->company_name }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $exhibitor->contact_name }}
                                </p>
                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                {{ $exhibitor->commercial?->name ?? '—' }}
                            </td>

                            <td class="px-5 py-4 font-semibold text-slate-700">
                                {{ $exhibitor->stand_number ?: '—' }}
                            </td>

                            <td class="px-5 py-4 font-semibold">
                                {{ $filled }} / {{ $quota }}
                            </td>

                            <td class="px-5 py-4">
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

                            <td class="px-5 py-4">
                                @if (!$exhibitor->user)
                                    <span class="text-xs font-semibold text-slate-500">Sans compte</span>
                                @elseif ($exhibitor->user->is_active)
                                    <span class="text-xs font-semibold text-emerald-700">Actif</span>
                                @else
                                    <span class="text-xs font-semibold text-red-600">Désactivé</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <a
                                    href="{{ route('admin.exposants.show', $exhibitor) }}"
                                    class="font-semibold text-blue-700 hover:underline"
                                >
                                    Voir détails
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <p class="font-semibold text-slate-700">
                                    Aucun exposant trouvé
                                </p>
                                <p class="mt-2 text-sm text-slate-500">
                                    Essayez de modifier les filtres.
                                </p>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        @if ($exhibitors->hasPages())
            <div class="border-t border-slate-100 px-6 py-5">
                {{ $exhibitors->links() }}
            </div>
        @endif

    </section>

</x-admin.layout>
