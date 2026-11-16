
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-blue-600">
                Administration / Badges
            </p>
            <h1 class="mt-2 text-3xl font-bold">
                Gestion des badges
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Consultez et gérez les badges de tous les exposants.
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.badges.export', request()->only(['exhibitor_id', 'status'])) }}"
               class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Export CSV
            </a>

            <a href="{{ route('admin.badges.create') }}"
               class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                + Ajouter un badge
            </a>
        </div>
    </div>

    @if (session('status'))
        <div role="status"
             class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert"
             class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card
            label="Badges autorisés"
            :value="$totalAuthorized"
            icon="badge"
            description="Total des quotas exposants."
        />
        <x-admin.stat-card
            label="Badges enregistrés"
            :value="$totalBadges"
            icon="badge"
            description="Actifs et désactivés."
        />
        <x-admin.stat-card
            label="Badges restants"
            :value="$remainingBadges"
            icon="badge"
            description="Capacité encore disponible."
        />
        <x-admin.stat-card
            label="Badges actifs"
            :value="$activeBadges"
            icon="badge"
            description="Badges actuellement valides."
        />
    </section>

    <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-bold text-slate-800">Utilisation globale</h2>
            <span class="font-bold text-blue-600">
                {{ $percentage }}%
            </span>
        </div>

        <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full bg-blue-600"
                 style="width: {{ min(100, $percentage) }}%">
            </div>
        </div>

        <p class="mt-3 text-xs text-slate-500">
            {{ $totalBadges }} badges enregistrés sur
            {{ $totalAuthorized }} autorisés.
        </p>
    </section>

    <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <form method="GET"
              action="{{ route('admin.badges.index') }}"
              class="grid gap-4 md:grid-cols-4">

            <div class="md:col-span-2">
                <label for="search"
                       class="mb-2 block text-sm font-semibold">
                    Recherche
                </label>
                <input id="search" name="search" type="search"
                       maxlength="255"
                       value="{{ request('search') }}"
                       placeholder="Nom, email, société, fonction..."
                       class="w-full rounded-xl border-slate-300 text-sm">
            </div>

            <div>
                <label for="exhibitor_id"
                       class="mb-2 block text-sm font-semibold">
                    Exposant
                </label>
                <select id="exhibitor_id" name="exhibitor_id"
                        class="w-full rounded-xl border-slate-300 text-sm">
                    <option value="">Tous les exposants</option>
                    @foreach ($exhibitors as $exhibitor)
                        <option value="{{ $exhibitor->id }}"
                            @selected((string) request('exhibitor_id') === (string) $exhibitor->id)>
                            {{ $exhibitor->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status"
                       class="mb-2 block text-sm font-semibold">
                    Statut
                </label>
                <select id="status" name="status"
                        class="w-full rounded-xl border-slate-300 text-sm">
                    <option value="">Tous</option>
                    <option value="active" @selected(request('status') === 'active')>
                        Actif
                    </option>
                    <option value="inactive" @selected(request('status') === 'inactive')>
                        Inactif
                    </option>
                </select>
            </div>

            <div class="flex flex-wrap items-center gap-3 md:col-span-4">
                <button type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Filtrer
                </button>

                <a href="{{ route('admin.badges.index') }}"
                   class="text-sm font-semibold text-slate-500 hover:underline">
                    Réinitialiser
                </a>
            </div>
        </form>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
            <div>
                <h2 class="text-lg font-bold">Liste des badges</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $badges->total() }} résultat(s)
                </p>
            </div>
        </div>

        <form method="GET"
              action="{{ route('admin.badges.print-selected') }}"
              target="_blank">

            <div class="overflow-x-auto">
                <table class="w-full min-w-[950px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-4">
                                <span class="sr-only">Sélection</span>
                            </th>
                            <th class="px-5 py-4">Badge</th>
                            <th class="px-5 py-4">Société</th>
                            <th class="px-5 py-4">Fonction</th>
                            <th class="px-5 py-4">Statut</th>
                            <th class="px-5 py-4">Création</th>
                            <th class="px-5 py-4">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($badges as $badge)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-4">
                                    <input type="checkbox"
                                           name="ids[]"
                                           value="{{ $badge->id }}"
                                           aria-label="Sélectionner {{ $badge->reference }}"
                                           class="rounded border-slate-300">
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-bold text-slate-800">
                                        {{ $badge->full_name }}
                                    </p>
                                    <p class="mt-1 text-xs text-blue-600">
                                        {{ $badge->reference }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $badge->email }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-700">
                                        {{ $badge->exhibitor?->company_name }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $badge->exhibitor?->commercial?->name }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-slate-600">
                                    {{ $badge->function }}
                                </td>

                                <td class="px-5 py-4">
                                    <span @class([
                                        'rounded-full px-3 py-1 text-xs font-semibold',
                                        'bg-emerald-50 text-emerald-700' => $badge->is_active,
                                        'bg-red-50 text-red-700' => ! $badge->is_active,
                                    ])>
                                        {{ $badge->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-slate-500">
                                    {{ $badge->created_at?->format('d/m/Y') }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex gap-3">
                                        <a href="{{ route('admin.badges.show', $badge) }}"
                                           class="font-semibold text-blue-700 hover:underline">
                                            Détails
                                        </a>
                                        <a href="{{ route('admin.badges.edit', $badge) }}"
                                           class="font-semibold text-slate-600 hover:underline">
                                            Modifier
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7"
                                    class="px-5 py-14 text-center text-slate-500">
                                    Aucun badge trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($badges->count())
                <div class="border-t border-slate-100 px-6 py-4">
                    <button type="submit"
                            class="rounded-xl bg-[#0F2347] px-5 py-3 text-sm font-semibold text-white">
                        Imprimer les badges sélectionnés
                    </button>
                </div>
            @endif
        </form>

        @if ($badges->hasPages())
            <div class="border-t border-slate-100 px-6 py-5">
                {{ $badges->links() }}
            </div>
        @endif
    </section>

    <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h2 class="text-lg font-bold">Gestion des quotas</h2>
        <p class="mt-2 text-sm text-slate-500">
            Choisissez un exposant et définissez son nombre de badges autorisés.
        </p>

        <div class="mt-5 grid gap-4 lg:grid-cols-2">
            @foreach ($exhibitors as $exhibitor)
                <form method="POST"
                      action="{{ route('admin.badges.quota', $exhibitor) }}"
                      class="rounded-xl border border-slate-200 p-4">
                    @csrf
                    @method('PATCH')

                    <p class="font-semibold text-slate-800">
                        {{ $exhibitor->company_name }}
                    </p>

                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <label for="quota-{{ $exhibitor->id }}"
                               class="text-xs text-slate-500">
                            Quota
                        </label>

                        <input id="quota-{{ $exhibitor->id }}"
                               type="number"
                               name="authorized_badges"
                               value="{{ $exhibitor->authorized_badges }}"
                               min="0"
                               max="100000"
                               required
                               class="w-28 rounded-lg border-slate-300 text-sm">

                        <button type="submit"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white">
                            Enregistrer
                        </button>
                    </div>
                </form>
            @endforeach
        </div>
    </section>

</x-admin.layout>
