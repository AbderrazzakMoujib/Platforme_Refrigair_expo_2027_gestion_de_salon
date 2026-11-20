
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

@php
    $currentStatus = request('status', '');
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
            Équipements
        </span>
    </nav>

    <!-- HEADER -->
    <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div class="flex items-center gap-4">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <x-admin.icon name="box" class="size-7"/>
            </span>

            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                    Gestion des équipements
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Consultez et gérez les équipements proposés aux exposants.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">

    <span class="inline-flex w-fit items-center gap-2 rounded-xl border border-blue-100 bg-blue-50 px-5 py-3 text-sm font-semibold text-blue-700">

        <x-admin.icon name="users" class="size-5"/>

        Catalogue des commerciaux

    </span>

    <a href="{{ route('admin.equipements.create') }}"
       class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">

        <svg class="size-5"
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="2"
             stroke-linecap="round"
             stroke-linejoin="round">

            <path d="M12 5v14M5 12h14"/>

        </svg>

        Ajouter un équipement

    </a>

</div>

    </header>

    @if (session('status'))
        <div role="status"
             class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <!-- STATISTICS -->
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-violet-100 text-violet-600">
                <x-admin.icon name="box" class="size-7"/>
            </span>
            <div>
                <p class="text-sm text-slate-500">Équipements totaux</p>
                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ number_format($totalEquipment, 0, ',', ' ') }}
                </p>
                <p class="mt-1 text-xs text-slate-400">Références du catalogue</p>
            </div>
        </article>

        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <x-admin.icon name="layers" class="size-7"/>
            </span>
            <div>
                <p class="text-sm text-slate-500">Équipements actifs</p>
                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ number_format($activeEquipment, 0, ',', ' ') }}
                </p>
                <p class="mt-1 text-xs text-slate-400">Visibles dans le catalogue</p>
            </div>
        </article>

        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-amber-100 text-amber-600">
                <x-admin.icon name="inbox" class="size-7"/>
            </span>
            <div>
                <p class="text-sm text-slate-500">Unités disponibles</p>
                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ number_format($totalQuantity, 0, ',', ' ') }}
                </p>
                <p class="mt-1 text-xs text-slate-400">Somme des quantités actives</p>
            </div>
        </article>

        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-red-100 text-red-600">
                <x-admin.icon name="box" class="size-7"/>
            </span>
            <div>
                <p class="text-sm text-slate-500">Équipements inactifs</p>
                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ number_format($inactiveEquipment, 0, ',', ' ') }}
                </p>
                <p class="mt-1 text-xs text-slate-400">Non proposés aux exposants</p>
            </div>
        </article>

    </section>

    <!-- CATALOGUE -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <!-- TABS -->
        <div class="border-b border-slate-100 px-5 pt-5 sm:px-6">

            <div class="flex flex-wrap gap-2">

                <a href="{{ route('admin.equipements.index', request()->except(['status', 'page'])) }}"
                   @class([
                       'rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                       'bg-[#2563EB] text-white' => $currentStatus === '',
                       'border border-slate-200 text-slate-600 hover:bg-slate-50' => $currentStatus !== '',
                   ])>
                    Tous ({{ $totalEquipment }})
                </a>

                <a href="{{ route('admin.equipements.index', array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}"
                   @class([
                       'rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                       'bg-[#2563EB] text-white' => $currentStatus === 'active',
                       'border border-slate-200 text-slate-600 hover:bg-slate-50' => $currentStatus !== 'active',
                   ])>
                    Actifs ({{ $activeEquipment }})
                </a>

                <a href="{{ route('admin.equipements.index', array_merge(request()->except(['status', 'page']), ['status' => 'inactive'])) }}"
                   @class([
                       'rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                       'bg-[#2563EB] text-white' => $currentStatus === 'inactive',
                       'border border-slate-200 text-slate-600 hover:bg-slate-50' => $currentStatus !== 'inactive',
                   ])>
                    Inactifs ({{ $inactiveEquipment }})
                </a>

            </div>

            <!-- FILTERS -->
            <form method="GET"
                  action="{{ route('admin.equipements.index') }}"
                  class="mt-5 grid gap-3 pb-5 md:grid-cols-2 xl:grid-cols-[minmax(0,2fr)_repeat(3,minmax(0,1fr))_auto]">

                <div class="relative">
                    <label for="search" class="sr-only">Recherche</label>

                    <svg class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-slate-400"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m16 16 5 5"/>
                    </svg>

                    <input id="search"
                           name="search"
                           type="search"
                           maxlength="255"
                           value="{{ request('search') }}"
                           placeholder="Équipement, description, commercial..."
                           class="w-full rounded-xl border border-slate-200 py-3 pl-10 pr-4 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="category" class="sr-only">Catégorie</label>

                    <select id="category"
                            name="category"
                            class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
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
                    <label for="commercial_id" class="sr-only">
                        Commercial
                    </label>

                    <select id="commercial_id"
                            name="commercial_id"
                            class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
                        <option value="">Tous les commerciaux</option>

                        @foreach ($commercials as $commercial)
                            <option value="{{ $commercial->id }}"
                                @selected((string) request('commercial_id') === (string) $commercial->id)>
                                {{ $commercial->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="sr-only">Statut</label>

                    <select id="status"
                            name="status"
                            class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">
                        <option value="">Tous les statuts</option>
                        <option value="active"
                            @selected($currentStatus === 'active')>
                            Actif
                        </option>
                        <option value="inactive"
                            @selected($currentStatus === 'inactive')>
                            Inactif
                        </option>
                    </select>
                </div>

                <button type="submit"
                        class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                    Filtrer
                </button>

                <a href="{{ route('admin.equipements.index') }}"
                   class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50 xl:col-span-5 xl:justify-self-start">
                    Réinitialiser
                </a>

            </form>

        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-5 sm:px-6">
            <div>
                <h2 class="text-lg font-bold text-[#0F2347]">
                    Catalogue des équipements
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Liste de tous les équipements enregistrés.
                </p>
            </div>

            <span class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">
                {{ $equipment->total() }} résultat(s)
            </span>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">

            <table class="w-full min-w-[1100px] text-left text-sm">

                <thead class="bg-[#F5F7FB] text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Équipement</th>
                        <th class="px-5 py-4">Catégorie</th>
                        <th class="px-5 py-4">Commercial</th>
                        <th class="px-5 py-4">Prix</th>
                        <th class="px-5 py-4">Quantité</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse ($equipment as $item)

                        <tr class="transition hover:bg-blue-50/30">

                            <!-- EQUIPMENT -->
                            <td class="px-5 py-5">

                                <div class="flex items-center gap-3">

                                    @if ($item->image_path)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}"
                                             alt="{{ $item->name }}"
                                             class="size-14 shrink-0 rounded-xl border border-slate-200 bg-white object-contain p-1"
                                             loading="lazy">
                                    @else
                                        <span class="grid size-14 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">
                                            <x-admin.icon name="box" class="size-7"/>
                                        </span>
                                    @endif

                                    <div class="min-w-0">
                                        <p class="font-bold text-[#0F2347]">
                                            {{ $item->name }}
                                        </p>

                                        <p class="mt-1 max-w-52 truncate text-xs text-slate-500"
                                           title="{{ $item->description }}">
                                            {{ $item->description ?: 'Sans description' }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-400">
                                            ID #{{ $item->id }}
                                        </p>
                                    </div>

                                </div>

                            </td>

                            <!-- CATEGORY -->
                            <td class="px-5 py-5">
                                <span class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                                    {{ $item->category }}
                                </span>
                            </td>

                            <!-- COMMERCIAL -->
                            <td class="px-5 py-5">

                                @if ($item->commercial)

                                    <div class="flex items-center gap-2">

                                        @if ($item->commercial->profile_photo_url)
                                            <img src="{{ $item->commercial->profile_photo_url }}"
                                                 alt="{{ $item->commercial->name }}"
                                                 class="size-9 shrink-0 rounded-full border border-slate-200 object-cover">
                                        @else
                                            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-violet-100 text-xs font-bold text-violet-700">
                                                {{ $item->commercial->initials }}
                                            </span>
                                        @endif

                                        <a href="{{ route('admin.commerciaux.show', $item->commercial) }}"
                                           class="font-semibold text-slate-700 hover:text-blue-600">
                                            {{ $item->commercial->name }}
                                        </a>

                                    </div>

                                @else

                                    <span class="text-xs font-semibold text-slate-400">
                                        Non attribué
                                    </span>

                                @endif

                            </td>

                            <!-- PRICE -->
                            <td class="px-5 py-5">
                                <span class="whitespace-nowrap font-bold text-[#0F2347]">
                                    {{ number_format((float) $item->price, 2, ',', ' ') }} DH
                                </span>
                            </td>

                            <!-- QUANTITY -->
                            <td class="px-5 py-5">

                                <span @class([
                                    'inline-flex min-w-12 justify-center rounded-lg px-3 py-1.5 text-xs font-bold',
                                    'bg-emerald-50 text-emerald-700' => $item->quantity_available > 0,
                                    'bg-red-50 text-red-700' => $item->quantity_available === 0,
                                ])>
                                    {{ $item->quantity_available }}
                                </span>

                            </td>

                            <!-- STATUS -->
                            <td class="px-5 py-5">
                                <span @class([
                                    'inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold',
                                    'bg-emerald-50 text-emerald-700' => $item->is_active,
                                    'bg-red-50 text-red-700' => ! $item->is_active,
                                ])>
                                    <span @class([
                                        'size-2 rounded-full',
                                        'bg-emerald-500' => $item->is_active,
                                        'bg-red-500' => ! $item->is_active,
                                    ])></span>

                                    {{ $item->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>

                            <!-- ACTIONS -->
                            <td class="px-5 py-5">

                                <div class="flex flex-wrap items-center gap-2">

                                    <a href="{{ route('admin.equipements.edit', $item) }}"
                                       title="Modifier"
                                       class="rounded-lg border border-blue-100 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">
                                        Modifier
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.equipements.toggle', $item) }}"
                                          onsubmit="return confirm('Confirmer le changement de statut de cet équipement ?')">

                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                @class([
                                                    'rounded-lg border px-3 py-2 text-xs font-semibold transition',
                                                    'border-red-200 text-red-700 hover:bg-red-50' => $item->is_active,
                                                    'border-emerald-200 text-emerald-700 hover:bg-emerald-50' => ! $item->is_active,
                                                ])>
                                            {{ $item->is_active ? 'Désactiver' : 'Activer' }}
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="mx-auto grid size-16 place-items-center rounded-2xl bg-blue-50 text-blue-600">
                                    <x-admin.icon name="box" class="size-8"/>
                                </div>

                                <p class="mt-4 font-bold text-[#0F2347]">
                                    Aucun équipement trouvé
                                </p>

                                <p class="mt-2 text-sm text-slate-500">
                                    Aucun équipement ne correspond aux filtres sélectionnés.
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <!-- PAGINATION -->
        <div class="flex flex-col gap-4 border-t border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">

            <p class="text-sm text-slate-500">
                @if ($equipment->total() > 0)
                    Affichage de {{ $equipment->firstItem() }}
                    à {{ $equipment->lastItem() }}
                    sur {{ $equipment->total() }} équipements
                @else
                    Aucun résultat
                @endif
            </p>

            @if ($equipment->hasPages())
                {{ $equipment->links() }}
            @endif

        </div>

    </section>

</div>

</x-admin.layout>
