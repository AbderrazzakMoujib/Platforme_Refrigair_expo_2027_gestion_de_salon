
<x-commercial.layout
    :commercial="$commercial"
    :commercial-initials="$commercialInitials"
>

    @php
        $currentStatus = request('status', '');

        $statistics = [
            [
                'label' => 'Mes équipements',
                'value' => $totalEquipment,
                'icon' => 'box',
                'color' => 'bg-violet-100 text-violet-600',
                'description' => 'Références du catalogue',
            ],
            [
                'label' => 'Équipements actifs',
                'value' => $activeEquipment,
                'icon' => 'layers',
                'color' => 'bg-emerald-100 text-emerald-600',
                'description' => 'Visibles dans le catalogue',
            ],
            [
                'label' => 'Unités disponibles',
                'value' => $totalQuantity,
                'icon' => 'inbox',
                'color' => 'bg-blue-100 text-blue-600',
                'description' => 'Quantité totale disponible',
            ],
            [
                'label' => 'Équipements inactifs',
                'value' => $inactiveEquipment,
                'icon' => 'box',
                'color' => 'bg-red-100 text-red-600',
                'description' => 'Non proposés aux exposants',
            ],
        ];
    @endphp

    <div class="min-w-0 space-y-7">

        {{-- BREADCRUMB --}}

        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">

            <a href="{{ route('commercial.index') }}"
               class="transition hover:text-blue-600">

                Espace commercial

            </a>

            <span>/</span>

            <span class="font-semibold text-[#0F2347]">

                Équipements

            </span>

        </nav>


        {{-- HEADER --}}

        <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-center gap-4">

                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">

                    <x-admin.icon
                        name="box"
                        class="size-7"
                    />

                </span>

                <div>

                    <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">

                        Gestion des équipements

                    </h1>

                    <p class="mt-2 text-sm text-slate-500">

                        Gérez votre catalogue, vos tarifs et vos stocks.

                    </p>

                </div>

            </div>

            <a href="{{ route('commercial.equipements.create') }}"
               class="inline-flex w-fit items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">

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

        </header>


        {{-- SUCCESS MESSAGE --}}

        @if (session('status'))

            <div role="status"
                 class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">

                <svg class="size-5 shrink-0"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     stroke-linecap="round"
                     stroke-linejoin="round">

                    <circle cx="12" cy="12" r="9"/>
                    <path d="m8 12 3 3 5-6"/>

                </svg>

                {{ session('status') }}

            </div>

        @endif


        {{-- ERROR MESSAGE --}}

        @if ($errors->any())

            <div role="alert"
                 class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">

                {{ $errors->first() }}

            </div>

        @endif


        {{-- ===================================================== --}}
        {{-- STATISTICS --}}
        {{-- ===================================================== --}}

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            @foreach ($statistics as $stat)

                <article class="flex min-w-0 items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <span class="grid size-14 shrink-0 place-items-center rounded-2xl {{ $stat['color'] }}">

                        <x-admin.icon
                            :name="$stat['icon']"
                            class="size-7"
                        />

                    </span>

                    <div class="min-w-0">

                        <p class="text-sm font-medium text-slate-500">

                            {{ $stat['label'] }}

                        </p>

                        <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">

                            {{ number_format($stat['value'], 0, ',', ' ') }}

                        </p>

                        <p class="mt-1 text-xs text-slate-400">

                            {{ $stat['description'] }}

                        </p>

                    </div>

                </article>

            @endforeach

        </section>


        {{-- ===================================================== --}}
        {{-- CATALOGUE --}}
        {{-- ===================================================== --}}

        <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">


            {{-- STATUS TABS --}}

            <div class="border-b border-slate-100 px-5 pt-5 sm:px-6">

                <div class="flex flex-wrap gap-2">

                    <a href="{{ route('commercial.equipements.index', request()->except(['status', 'page'])) }}"
                       @class([
                           'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                           'bg-[#2563EB] text-white' => $currentStatus === '',
                           'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' => $currentStatus !== '',
                       ])>

                        Tous ({{ $totalEquipment }})

                    </a>

                    <a href="{{ route('commercial.equipements.index', array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}"
                       @class([
                           'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                           'bg-[#2563EB] text-white' => $currentStatus === 'active',
                           'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' => $currentStatus !== 'active',
                       ])>

                        <span class="size-2 rounded-full bg-emerald-500"></span>

                        Actifs ({{ $activeEquipment }})

                    </a>

                    <a href="{{ route('commercial.equipements.index', array_merge(request()->except(['status', 'page']), ['status' => 'inactive'])) }}"
                       @class([
                           'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                           'bg-[#2563EB] text-white' => $currentStatus === 'inactive',
                           'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' => $currentStatus !== 'inactive',
                       ])>

                        <span class="size-2 rounded-full bg-red-500"></span>

                        Inactifs ({{ $inactiveEquipment }})

                    </a>

                </div>


                {{-- FILTERS --}}

                <form method="GET"
                      action="{{ route('commercial.equipements.index') }}"
                      class="mt-5 grid gap-3 pb-5 md:grid-cols-2 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_auto_auto]">


                    {{-- SEARCH --}}

                    <div class="relative">

                        <label for="search" class="sr-only">

                            Rechercher un équipement

                        </label>

                        <svg class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-slate-400"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8">

                            <circle cx="11" cy="11" r="7"/>
                            <path d="m16 16 5 5"/>

                        </svg>

                        <input type="search"
                               id="search"
                               name="search"
                               value="{{ request('search') }}"
                               maxlength="255"
                               placeholder="Rechercher un équipement..."
                               class="w-full rounded-xl border border-slate-200 py-3 pl-10 pr-4 text-sm focus:border-blue-500 focus:ring-blue-500">

                    </div>


                    {{-- CATEGORY --}}

                    <div>

                        <label for="category" class="sr-only">

                            Catégorie

                        </label>

                        <select id="category"
                                name="category"
                                class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">

                            <option value="">

                                Toutes les catégories

                            </option>

                            @foreach ($categories as $category)

                                <option value="{{ $category }}"
                                    @selected(request('category') === $category)>

                                    {{ $category }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- STATUS --}}

                    <div>

                        <label for="status" class="sr-only">

                            Statut

                        </label>

                        <select id="status"
                                name="status"
                                class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">

                            <option value="">

                                Tous les statuts

                            </option>

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


                    {{-- FILTER BUTTON --}}

                    <button type="submit"
                            class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">

                        Filtrer

                    </button>


                    {{-- RESET BUTTON --}}

                    <a href="{{ route('commercial.equipements.index') }}"
                       class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-50">

                        Réinitialiser

                    </a>

                </form>

            </div>


            {{-- LIST HEADER --}}

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-5 sm:px-6">

                <div>

                    <h2 class="text-lg font-bold text-[#0F2347]">

                        Mon catalogue

                    </h2>

                    <p class="mt-1 text-sm text-slate-500">

                        {{ $equipment->total() }} équipement(s)

                    </p>

                </div>

                <span class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">

                    <x-admin.icon
                        name="box"
                        class="size-4"
                    />

                    Mes équipements

                </span>

            </div>


            {{-- ===================================================== --}}
            {{-- EQUIPMENT TABLE --}}
            {{-- ===================================================== --}}

            <div class="overflow-x-auto">

                <table class="w-full min-w-[950px] text-left text-sm">

                    <thead class="bg-[#F5F7FB] text-xs font-semibold uppercase tracking-wide text-slate-500">

                        <tr>

                            <th scope="col" class="px-5 py-4">

                                Équipement

                            </th>

                            <th scope="col" class="px-5 py-4">

                                Catégorie

                            </th>

                            <th scope="col" class="px-5 py-4">

                                Prix unitaire

                            </th>

                            <th scope="col" class="px-5 py-4">

                                Stock disponible

                            </th>

                            <th scope="col" class="px-5 py-4">

                                Statut

                            </th>

                            <th scope="col" class="px-5 py-4">

                                Date d'ajout

                            </th>

                            <th scope="col" class="px-5 py-4 text-right">

                                Actions

                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse ($equipment as $item)

                            <tr class="transition hover:bg-blue-50/30">


                                {{-- EQUIPMENT PHOTO AND NAME --}}

                                <td class="px-5 py-5">

                                    <div class="flex items-center gap-4">

                                        <div class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50">

                                            @if ($item->image_path)

                                                <img
                                                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}"
                                                    alt="{{ $item->name }}"
                                                    class="h-full w-full object-contain p-1.5"
                                                    loading="lazy"
                                                    data-equipment-image
                                                >

                                                <span data-image-fallback
                                                      class="hidden text-slate-400">

                                                    <x-admin.icon
                                                        name="box"
                                                        class="size-7"
                                                    />

                                                </span>

                                            @else

                                                <x-admin.icon
                                                    name="box"
                                                    class="size-7 text-slate-400"
                                                />

                                            @endif

                                        </div>

                                        <div class="min-w-0">

                                            <a href="{{ route('commercial.equipements.edit', $item) }}"
                                               class="font-bold text-[#0F2347] transition hover:text-blue-600">

                                                {{ $item->name }}

                                            </a>

                                            <p class="mt-1 max-w-56 truncate text-xs text-slate-400">

                                                {{ $item->description ?: 'Aucune description' }}

                                            </p>

                                        </div>

                                    </div>

                                </td>


                                {{-- CATEGORY --}}

                                <td class="px-5 py-5">

                                    <span class="inline-flex rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">

                                        {{ $item->category }}

                                    </span>

                                </td>


                                {{-- PRICE --}}

                                <td class="whitespace-nowrap px-5 py-5">

                                    <p class="font-extrabold text-[#0F2347]">

                                        {{ number_format($item->price, 2, ',', ' ') }}

                                        <span class="text-xs font-medium text-slate-500">

                                            DH

                                        </span>

                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">

                                        Par unité

                                    </p>

                                </td>


                                {{-- STOCK --}}

                                <td class="px-5 py-5">

                                    <p class="font-bold text-[#0F2347]">

                                        {{ number_format($item->quantity_available, 0, ',', ' ') }}

                                    </p>

                                    <p @class([
                                        'mt-1 text-xs font-semibold',
                                        'text-emerald-600' => $item->quantity_available > 0,
                                        'text-red-600' => $item->quantity_available === 0,
                                    ])>

                                        {{ $item->quantity_available > 0
                                            ? 'En stock'
                                            : 'Rupture de stock' }}

                                    </p>

                                </td>


                                {{-- STATUS --}}

                                <td class="px-5 py-5">

                                    <span @class([
                                        'inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold',
                                        'bg-emerald-50 text-emerald-700' => $item->is_active,
                                        'bg-red-50 text-red-700' => !$item->is_active,
                                    ])>

                                        <span @class([
                                            'size-2 rounded-full',
                                            'bg-emerald-500' => $item->is_active,
                                            'bg-red-500' => !$item->is_active,
                                        ])></span>

                                        {{ $item->is_active ? 'Actif' : 'Inactif' }}

                                    </span>

                                </td>


                                {{-- DATE --}}

                                <td class="whitespace-nowrap px-5 py-5 text-slate-600">

                                    {{ $item->created_at?->format('d/m/Y') }}

                                    <p class="mt-1 text-xs text-slate-400">

                                        {{ $item->created_at?->format('H:i') }}

                                    </p>

                                </td>


                                {{-- ACTIONS --}}

                                <td class="px-5 py-5">

                                    <div class="flex items-center justify-end gap-2">


                                        {{-- EDIT --}}

                                        <a href="{{ route('commercial.equipements.edit', $item) }}"
                                           title="Modifier l'équipement"
                                           aria-label="Modifier {{ $item->name }}"
                                           class="grid size-10 place-items-center rounded-xl border border-slate-200 text-blue-600 transition hover:bg-blue-50">

                                            <svg class="size-5"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="1.8"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round">

                                                <path d="M12 20h9"/>

                                                <path d="M16 4a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4Z"/>

                                            </svg>

                                        </a>


                                        {{-- ACTIVATE / DEACTIVATE --}}

                                        <form method="POST"
                                              action="{{ route('commercial.equipements.toggle', $item) }}">

                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    title="{{ $item->is_active ? 'Désactiver' : 'Activer' }}"
                                                    aria-label="{{ $item->is_active ? 'Désactiver' : 'Activer' }} {{ $item->name }}"
                                                    @class([
                                                        'grid size-10 place-items-center rounded-xl border transition',
                                                        'border-red-100 text-red-600 hover:bg-red-50' => $item->is_active,
                                                        'border-emerald-100 text-emerald-600 hover:bg-emerald-50' => !$item->is_active,
                                                    ])>

                                                <svg class="size-5"
                                                     viewBox="0 0 24 24"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     stroke-width="1.8"
                                                     stroke-linecap="round">

                                                    <path d="M12 3v9"/>
                                                    <path d="M6 5.5a9 9 0 1 0 12 0"/>

                                                </svg>

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty


                            {{-- EMPTY STATE --}}

                            <tr>

                                <td colspan="7"
                                    class="px-6 py-16 text-center">

                                    <div class="mx-auto grid size-20 place-items-center rounded-2xl bg-blue-50 text-blue-600">

                                        <x-admin.icon
                                            name="box"
                                            class="size-10"
                                        />

                                    </div>

                                    <h3 class="mt-5 text-lg font-bold text-[#0F2347]">

                                        Aucun équipement trouvé

                                    </h3>

                                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">

                                        Ajoutez un équipement à votre catalogue
                                        ou modifiez les filtres de recherche.

                                    </p>

                                    <a href="{{ route('commercial.equipements.create') }}"
                                       class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">

                                        <svg class="size-5"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="2"
                                             stroke-linecap="round">

                                            <path d="M12 5v14M5 12h14"/>

                                        </svg>

                                        Ajouter un équipement

                                    </a>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION --}}

            <div class="flex flex-col gap-4 border-t border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">

                <p class="text-sm text-slate-500">

                    @if ($equipment->total())

                        Affichage de {{ $equipment->firstItem() }}

                        à {{ $equipment->lastItem() }}

                        sur {{ $equipment->total() }} équipements

                    @else

                        Aucun résultat

                    @endif

                </p>

                @if ($equipment->hasPages())

                    <div>

                        {{ $equipment->links() }}

                    </div>

                @endif

            </div>

        </section>


        {{-- INFORMATION --}}

        <section class="flex items-start gap-4 rounded-2xl border border-blue-100 bg-blue-50 p-5">

            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-600">

                <svg class="size-5"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round">

                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 11v5M12 8h.01"/>

                </svg>

            </span>

            <div>

                <h3 class="text-sm font-bold text-[#0F2347]">

                    Gestion de votre catalogue

                </h3>

                <p class="mt-1 text-sm leading-relaxed text-slate-600">

                    Vous pouvez ajouter de nouveaux équipements,
                    modifier leurs tarifs, gérer les quantités disponibles
                    et activer ou désactiver les produits de votre catalogue.

                </p>

            </div>

        </section>

    </div>


    {{-- IMAGE FALLBACK SCRIPT --}}

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            document.querySelectorAll('[data-equipment-image]')
                .forEach(image => {

                    function showFallback() {

                        image.classList.add('hidden');

                        const fallback = image.parentElement
                            .querySelector('[data-image-fallback]');

                        if (fallback) {

                            fallback.classList.remove('hidden');

                        }

                    }

                    image.addEventListener(
                        'error',
                        showFallback
                    );

                    if (
                        image.complete &&
                        image.naturalWidth === 0
                    ) {

                        showFallback();

                    }

                });

        });
    </script>

</x-commercial.layout>
