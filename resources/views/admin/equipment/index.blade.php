
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-blue-600">
                Administration / Catalogue
            </p>
            <h1 class="mt-2 text-3xl font-bold">
                Gestion des équipements
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Gérez les options et équipements supplémentaires du salon.
            </p>
        </div>

        <a href="{{ route('admin.equipements.create') }}"
           class="rounded-xl bg-[#2563EB] px-5 py-3 text-center text-sm font-semibold text-white hover:bg-blue-700">
            + Ajouter un équipement
        </a>
    </div>

    @if (session('status'))
        <div role="status"
             class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <section class="mt-7 grid gap-4 sm:grid-cols-3">
        <x-admin.stat-card
            label="Total équipements"
            :value="$totalEquipment"
            icon="box"
            description="Références du catalogue."
        />
        <x-admin.stat-card
            label="Équipements actifs"
            :value="$activeEquipment"
            icon="box"
            description="Disponibles dans le catalogue."
        />
        <x-admin.stat-card
            label="Équipements inactifs"
            :value="$inactiveEquipment"
            icon="box"
            description="Références désactivées."
        />
    </section>

    <section class="mt-7 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">

        <form method="GET"
              action="{{ route('admin.equipements.index') }}"
              class="grid gap-3 md:grid-cols-4">

            <div class="md:col-span-2">
                <label for="search" class="mb-2 block text-sm font-medium">
                    Recherche
                </label>
                <input id="search" name="search" type="search"
                       value="{{ request('search') }}"
                       placeholder="Nom ou description..."
                       class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="category" class="mb-2 block text-sm font-medium">
                    Catégorie
                </label>
                <select id="category" name="category"
                        class="w-full rounded-xl border-slate-300 text-sm">
                    <option value="">Toutes les catégories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}"
                                @selected(request('category') === $category)>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="mb-2 block text-sm font-medium">
                    Statut
                </label>
                <select id="status" name="status"
                        class="w-full rounded-xl border-slate-300 text-sm">
                    <option value="">Tous les statuts</option>
                    <option value="active"
                            @selected(request('status') === 'active')>
                        Actif
                    </option>
                    <option value="inactive"
                            @selected(request('status') === 'inactive')>
                        Inactif
                    </option>
                </select>
            </div>

            <div class="flex flex-wrap gap-3 md:col-span-4">
                <button type="submit"
                        class="rounded-xl bg-[#2563EB] px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Filtrer
                </button>
                <a href="{{ route('admin.equipements.index') }}"
                   class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold hover:bg-slate-50">
                    Réinitialiser
                </a>
            </div>

        </form>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-bold">Catalogue des équipements</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ $equipment->total() }} résultat(s)
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-left text-sm">

                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Équipement</th>
                        <th class="px-5 py-4">Catégorie</th>
                        <th class="px-5 py-4">Prix</th>
                        <th class="px-5 py-4">Quantité</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($equipment as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($item->image_path)
                                        <img
                                            src="{{ asset('storage/'.$item->image_path) }}"
                                            alt="{{ $item->name }}"
                                            class="size-14 rounded-xl border border-slate-200 object-cover"
                                        >
                                    @else
                                        <div class="grid size-14 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">
                                            <x-admin.icon name="box" class="size-6" />
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-800">
                                            {{ $item->name }}
                                        </p>
                                        <p class="mt-1 max-w-60 truncate text-xs text-slate-500">
                                            {{ $item->description ?: 'Sans description' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                {{ $item->category }}
                            </td>

                            <td class="px-5 py-4 font-semibold">
                                {{ number_format((float) $item->price, 2, ',', ' ') }} DH
                            </td>

                            <td class="px-5 py-4">
                                {{ $item->quantity_available }}
                            </td>

                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-emerald-50 text-emerald-700' => $item->is_active,
                                    'bg-slate-100 text-slate-600' => !$item->is_active,
                                ])>
                                    {{ $item->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('admin.equipements.edit', $item) }}"
                                       class="font-semibold text-blue-700 hover:underline">
                                        Modifier
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.equipements.toggle', $item) }}">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="font-semibold text-slate-600 hover:underline">
                                            {{ $item->is_active ? 'Désactiver' : 'Activer' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <p class="font-semibold text-slate-700">
                                    Aucun équipement trouvé
                                </p>
                                <p class="mt-2 text-sm text-slate-500">
                                    Ajoutez votre premier équipement.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($equipment->hasPages())
            <div class="border-t border-slate-100 px-6 py-5">
                {{ $equipment->links() }}
            </div>
        @endif

    </section>
</x-admin.layout>
