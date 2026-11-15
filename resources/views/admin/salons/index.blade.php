
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-blue-600">
                Administration / Événements
            </p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">
                Gestion des salons
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Gérez les éditions, les dates et les inscriptions.
            </p>
        </div>

        <a href="{{ route('admin.salons.create') }}"
           class="rounded-xl bg-[#2563EB] px-5 py-3 text-center text-sm font-semibold text-white hover:bg-blue-700">
            + Créer un salon
        </a>
    </div>

    @if (session('status'))
        <div role="status"
             class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card
            label="Total salons"
            :value="$totalSalons"
            icon="calendar"
            description="Éditions enregistrées."
        />
        <x-admin.stat-card
            label="En cours"
            :value="$activeSalons"
            icon="calendar"
            description="Salons actifs."
        />
        <x-admin.stat-card
            label="Planifiés"
            :value="$plannedSalons"
            icon="calendar"
            description="Éditions à venir ou en préparation."
        />
        <x-admin.stat-card
            label="Inscriptions ouvertes"
            :value="$registrationOpenCount"
            icon="users"
            description="Ouvertures configurées."
        />
    </section>

    <section class="mt-7 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <form method="GET"
              action="{{ route('admin.salons.index') }}"
              class="grid gap-4 md:grid-cols-[1fr_220px_auto] md:items-end">
            <div>
                <label for="search"
                       class="mb-2 block text-sm font-semibold text-slate-700">
                    Recherche
                </label>
                <input id="search" type="search" name="search"
                       value="{{ request('search') }}"
                       placeholder="Salon, édition, ville..."
                       class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="status"
                       class="mb-2 block text-sm font-semibold text-slate-700">
                    Statut
                </label>
                <select id="status" name="status"
                        class="w-full rounded-xl border-slate-300 text-sm">
                    <option value="">Tous les statuts</option>
                    <option value="planned" @selected(request('status') === 'planned')>
                        Planifié
                    </option>
                    <option value="active" @selected(request('status') === 'active')>
                        En cours
                    </option>
                    <option value="completed" @selected(request('status') === 'completed')>
                        Terminé
                    </option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>
                        Annulé
                    </option>
                </select>
            </div>

            <button type="submit"
                    class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                Filtrer
            </button>
        </form>

        <a href="{{ route('admin.salons.index') }}"
           class="mt-3 inline-block text-xs font-semibold text-blue-600 hover:underline">
            Réinitialiser les filtres
        </a>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-bold">Liste des salons</h2>
            <span class="text-sm text-slate-500">
                {{ $salons->total() }} résultat(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Salon</th>
                        <th class="px-5 py-4">Lieu</th>
                        <th class="px-5 py-4">Dates</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4">Inscriptions</th>
                        <th class="px-5 py-4">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($salons as $salon)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="font-bold text-slate-800">
                                    {{ $salon->name }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Édition {{ $salon->edition }}
                                </p>
                            </td>

                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-700">
                                    {{ $salon->city }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $salon->venue }}
                                </p>
                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                {{ $salon->start_date->format('d/m/Y') }}
                                <span class="text-slate-400">—</span>
                                {{ $salon->end_date->format('d/m/Y') }}
                            </td>

                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-slate-100 text-slate-700' => $salon->status === 'planned',
                                    'bg-blue-50 text-blue-700' => $salon->status === 'active',
                                    'bg-emerald-50 text-emerald-700' => $salon->status === 'completed',
                                    'bg-red-50 text-red-700' => $salon->status === 'cancelled',
                                ])>
                                    {{ $salon->status_label }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <span @class([
                                    'text-xs font-semibold',
                                    'text-emerald-700' => $salon->registration_open,
                                    'text-slate-500' => !$salon->registration_open,
                                ])>
                                    {{ $salon->registration_open ? 'Ouvertes' : 'Fermées' }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex gap-4">
                                    <a href="{{ route('admin.salons.show', $salon) }}"
                                       class="font-semibold text-blue-700 hover:underline">
                                        Détails
                                    </a>
                                    <a href="{{ route('admin.salons.edit', $salon) }}"
                                       class="font-semibold text-slate-600 hover:underline">
                                        Modifier
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center">
                                <p class="font-semibold text-slate-700">
                                    Aucun salon enregistré
                                </p>
                                <p class="mt-2 text-sm text-slate-500">
                                    Créez votre premier salon pour commencer.
                                </p>
                                <a href="{{ route('admin.salons.create') }}"
                                   class="mt-4 inline-block font-semibold text-blue-600 hover:underline">
                                    Créer un salon
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($salons->hasPages())
            <div class="border-t border-slate-100 px-6 py-5">
                {{ $salons->links() }}
            </div>
        @endif
    </section>
</x-admin.layout>
