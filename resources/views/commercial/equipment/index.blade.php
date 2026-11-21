
<x-commercial.layout
    :commercial="$commercial"
    :commercial-initials="$commercialInitials"
>
    @php
        $currentStatus = request('status', '');

        $statistics = [
            [
                'label' => 'Catalogue total',
                'value' => $totalEquipment,
                'description' => 'Références disponibles',
                'icon' => 'package',
                'color' => 'bg-blue-50 text-blue-600',
            ],
            [
                'label' => 'Équipements actifs',
                'value' => $activeEquipment,
                'description' => 'Visibles par les exposants',
                'icon' => 'check-circle',
                'color' => 'bg-emerald-50 text-emerald-600',
            ],
            [
                'label' => 'Unités disponibles',
                'value' => $totalQuantity,
                'description' => 'Stock total actif',
                'icon' => 'layers',
                'color' => 'bg-violet-50 text-violet-600',
            ],
            [
                'label' => 'Équipements inactifs',
                'value' => $inactiveEquipment,
                'description' => 'Masqués aux exposants',
                'icon' => 'pause-circle',
                'color' => 'bg-amber-50 text-amber-600',
            ],
        ];
    @endphp

    <div class="min-w-0 space-y-7">

        {{-- BREADCRUMB --}}
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a
                href="{{ route('commercial.index') }}"
                class="transition hover:text-blue-600"
            >
                Espace commercial
            </a>

            <span>/</span>

            <span class="font-semibold text-[#0F2347]">
                Équipements
            </span>
        </nav>

        {{-- HEADER --}}
        <header class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">

            <div class="flex items-start gap-4">

                <div class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                    <svg
                        class="size-7"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M3 7 12 2l9 5-9 5-9-5Z"/>
                        <path d="M3 7v10l9 5 9-5V7"/>
                        <path d="M12 12v10"/>
                    </svg>
                </div>

                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                        Catalogue des équipements
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-500">
                        Gérez le catalogue commun des équipements proposés aux exposants.
                    </p>
                </div>

            </div>

            <a
                href="{{ route('commercial.equipements.create') }}"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700"
            >
                <svg
                    class="size-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M12 5v14M5 12h14"/>
                </svg>

                Ajouter un équipement
            </a>

        </header>

        {{-- NOTIFICATIONS --}}
        @if (session('status'))
            <div
                role="status"
                class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700"
            >
                <svg
                    class="size-5 shrink-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m8 12 3 3 5-6"/>
                </svg>

                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div
                role="alert"
                class="rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-700"
            >
                <p class="font-bold">
                    Veuillez corriger les erreurs :
                </p>

                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- SHARED CATALOG NOTICE --}}
        <div class="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50 px-5 py-4">

            <svg
                class="mt-0.5 size-5 shrink-0 text-blue-600"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
            >
                <circle cx="12" cy="12" r="9"/>
                <path d="M12 11v5M12 8h.01"/>
            </svg>

            <div>
                <p class="text-sm font-bold text-[#0F2347]">
                    Catalogue partagé
                </p>

                <p class="mt-1 text-xs leading-relaxed text-slate-600">
                    Tous les commerciaux et l'administration gèrent le même catalogue.
                    Les équipements actifs sont accessibles aux exposants.
                    Les modifications apportées ici sont partagées sur la plateforme.
                </p>
            </div>

        </div>

        {{-- STATISTICS --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            @foreach ($statistics as $stat)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-500">
                                {{ $stat['label'] }}
                            </p>

                            <p class="mt-3 text-3xl font-extrabold text-[#0F2347]">
                                {{ number_format($stat['value'], 0, ',', ' ') }}
                            </p>
                        </div>

                        <div class="grid size-11 shrink-0 place-items-center rounded-xl {{ $stat['color'] }}">
                            @switch($stat['icon'])

                                @case('check-circle')
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path d="m8 12 3 3 5-6"/>
                                    </svg>
                                    @break

                                @case('layers')
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="m12 2 9 5-9 5-9-5 9-5Z"/>
                                        <path d="m3 12 9 5 9-5M3 17l9 5 9-5"/>
                                    </svg>
                                    @break

                                @case('pause-circle')
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path d="M9 8v8M15 8v8"/>
                                    </svg>
                                    @break

                                @default
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M3 7 12 2l9 5-9 5-9-5Z"/>
                                        <path d="M3 7v10l9 5 9-5V7"/>
                                        <path d="M12 12v10"/>
                                    </svg>

                            @endswitch
                        </div>

                    </div>

                    <p class="mt-2 text-xs text-slate-400">
                        {{ $stat['description'] }}
                    </p>

                </div>
            @endforeach

        </div>

        {{-- FILTERS --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-lg font-extrabold text-[#0F2347]">
                        Rechercher des équipements
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Recherchez dans l'ensemble du catalogue.
                    </p>
                </div>

                <a
                    href="{{ route('commercial.equipements.index') }}"
                    class="text-sm font-semibold text-blue-600 transition hover:text-blue-700"
                >
                    Réinitialiser les filtres
                </a>

            </div>

            <form
                method="GET"
                action="{{ route('commercial.equipements.index') }}"
                class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,1fr)_auto]"
            >

                {{-- SEARCH --}}
                <div>
                    <label
                        for="search"
                        class="mb-2 block text-xs font-bold text-slate-600"
                    >
                        Nom ou description
                    </label>

                    <input
                        id="search"
                        name="search"
                        type="search"
                        value="{{ request('search') }}"
                        placeholder="Rechercher un équipement..."
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                </div>

                {{-- CATEGORY --}}
                <div>
                    <label
                        for="category"
                        class="mb-2 block text-xs font-bold text-slate-600"
                    >
                        Catégorie
                    </label>

                    <select
                        id="category"
                        name="category"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">Toutes les catégories</option>

                        @foreach ($categories as $category)
                            <option
                                value="{{ $category }}"
                                @selected(request('category') === $category)
                            >
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- STATUS --}}
                <div>
                    <label
                        for="status"
                        class="mb-2 block text-xs font-bold text-slate-600"
                    >
                        Statut
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                        <option value="">Tous les statuts</option>
                        <option value="active" @selected($currentStatus === 'active')>
                            Actifs
                        </option>
                        <option value="inactive" @selected($currentStatus === 'inactive')>
                            Inactifs
                        </option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#0F2347] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#173D77]"
                    >
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7"/>
                            <path d="m20 20-4-4"/>
                        </svg>

                        Filtrer
                    </button>
                </div>

            </form>

        </section>

        {{-- EQUIPMENT LIST --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-lg font-extrabold text-[#0F2347]">
                        Catalogue partagé
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Tous les équipements ajoutés par l'administration et les commerciaux.
                    </p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-full bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700">
                    <span class="size-2 rounded-full bg-blue-500"></span>

                    {{ $equipment->total() }}
                    {{ $equipment->total() > 1 ? 'références' : 'référence' }}
                </div>

            </div>

            @if ($equipment->count() > 0)

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1050px] text-left text-sm">

                        <thead class="bg-[#F5F7FB]">
                            <tr class="text-xs font-bold uppercase tracking-wide text-slate-500">

                                <th scope="col" class="px-6 py-4">
                                    Équipement
                                </th>

                                <th scope="col" class="px-4 py-4">
                                    Catégorie
                                </th>

                                <th scope="col" class="px-4 py-4">
                                    Prix
                                </th>

                                <th scope="col" class="px-4 py-4">
                                    Stock
                                </th>

                                <th scope="col" class="px-4 py-4">
                                    Statut
                                </th>

                                <th scope="col" class="px-4 py-4">
                                    Ajouté le
                                </th>

                                <th scope="col" class="px-6 py-4 text-right">
                                    Actions
                                </th>

                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @foreach ($equipment as $item)

                                <tr class="transition hover:bg-slate-50/70">

                                    {{-- EQUIPMENT --}}
                                    <td class="px-6 py-5 align-middle">
                                        <div class="flex min-w-0 items-center gap-4">

                                            <div class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-[#F5F7FB]">

                                                @if ($item->image_path)
                                                    <img
                                                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}"
                                                        alt="{{ $item->name }}"
                                                        loading="lazy"
                                                        class="h-full w-full object-cover"
                                                    >
                                                @else
                                                    <svg
                                                        class="size-7 text-slate-400"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.5"
                                                    >
                                                        <path d="M3 7 12 2l9 5-9 5-9-5Z"/>
                                                        <path d="M3 7v10l9 5 9-5V7"/>
                                                        <path d="M12 12v10"/>
                                                    </svg>
                                                @endif

                                            </div>

                                            <div class="min-w-0">

                                                <a
                                                    href="{{ route('commercial.equipements.edit', $item) }}"
                                                    class="block max-w-xs truncate font-bold text-[#0F2347] transition hover:text-blue-600"
                                                    title="{{ $item->name }}"
                                                >
                                                    {{ $item->name }}
                                                </a>

                                                <p class="mt-1 max-w-xs truncate text-xs text-slate-500"
                                                   title="{{ $item->description ?: 'Aucune description' }}">
                                                    {{ $item->description ?: 'Aucune description' }}
                                                </p>

                                            </div>

                                        </div>
                                    </td>

                                    {{-- CATEGORY --}}
                                    <td class="px-4 py-5 align-middle">
                                        <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
                                            {{ $item->category }}
                                        </span>
                                    </td>

                                    {{-- PRICE --}}
                                    <td class="whitespace-nowrap px-4 py-5 align-middle">
                                        <span class="font-extrabold text-[#0F2347]">
                                            {{ number_format((float) $item->price, 2, ',', ' ') }}
                                        </span>

                                        <span class="ml-1 text-xs text-slate-500">
                                            MAD
                                        </span>
                                    </td>

                                    {{-- STOCK --}}
                                    <td class="px-4 py-5 align-middle">

                                        <p class="font-bold text-[#0F2347]">
                                            {{ number_format($item->quantity_available, 0, ',', ' ') }}
                                        </p>

                                        @if ($item->quantity_available > 0)
                                            <span class="mt-1 inline-block text-xs font-medium text-emerald-600">
                                                Disponible
                                            </span>
                                        @else
                                            <span class="mt-1 inline-block text-xs font-medium text-red-600">
                                                Rupture de stock
                                            </span>
                                        @endif

                                    </td>

                                    {{-- STATUS --}}
                                    <td class="px-4 py-5 align-middle">

                                        @if ($item->is_active)
                                            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                                <span class="size-2 rounded-full bg-emerald-500"></span>
                                                Actif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                                                <span class="size-2 rounded-full bg-red-500"></span>
                                                Inactif
                                            </span>
                                        @endif

                                    </td>

                                    {{-- CREATED AT --}}
                                    <td class="whitespace-nowrap px-4 py-5 align-middle">

                                        <p class="font-semibold text-slate-700">
                                            {{ $item->created_at?->format('d/m/Y') ?? '—' }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ $item->created_at?->format('H:i') ?? '' }}
                                        </p>

                                    </td>

                                    {{-- ACTIONS --}}
                                    <td class="px-6 py-5 align-middle">

                                        <div class="flex items-center justify-end gap-2">

                                            {{-- EDIT --}}
                                            <a
                                                href="{{ route('commercial.equipements.edit', $item) }}"
                                                title="Modifier"
                                                aria-label="Modifier {{ $item->name }}"
                                                class="grid size-10 place-items-center rounded-xl border border-blue-100 text-blue-600 transition hover:bg-blue-50"
                                            >
                                                <svg
                                                    class="size-4"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                >
                                                    <path d="m16 4 4 4-12 12-5 1 1-5L16 4Z"/>
                                                    <path d="m14 6 4 4"/>
                                                </svg>
                                            </a>

                                            {{-- TOGGLE --}}
                                            <form
                                                method="POST"
                                                action="{{ route('commercial.equipements.toggle', $item) }}"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    title="{{ $item->is_active ? 'Désactiver' : 'Activer' }}"
                                                    aria-label="{{ $item->is_active ? 'Désactiver' : 'Activer' }} {{ $item->name }}"
                                                    class="grid size-10 place-items-center rounded-xl border transition {{ $item->is_active
                                                        ? 'border-red-100 text-red-600 hover:bg-red-50'
                                                        : 'border-emerald-100 text-emerald-600 hover:bg-emerald-50' }}"
                                                >

                                                    @if ($item->is_active)
                                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                            <circle cx="12" cy="12" r="9"/>
                                                            <path d="M9 8v8M15 8v8"/>
                                                        </svg>
                                                    @else
                                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                            <circle cx="12" cy="12" r="9"/>
                                                            <path d="m9 7 7 5-7 5V7Z"/>
                                                        </svg>
                                                    @endif

                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>
                </div>

            @else

                {{-- EMPTY STATE --}}
                <div class="flex flex-col items-center px-6 py-16 text-center">

                    <div class="grid size-20 place-items-center rounded-2xl bg-blue-50 text-blue-500">

                        <svg
                            class="size-10"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        >
                            <path d="M3 7 12 2l9 5-9 5-9-5Z"/>
                            <path d="M3 7v10l9 5 9-5V7"/>
                            <path d="M12 12v10"/>
                        </svg>

                    </div>

                    <h3 class="mt-5 text-lg font-extrabold text-[#0F2347]">
                        Aucun équipement trouvé
                    </h3>

                    <p class="mt-2 max-w-md text-sm leading-relaxed text-slate-500">
                        Aucun équipement ne correspond aux filtres sélectionnés.
                        Vous pouvez réinitialiser les filtres ou ajouter un nouvel équipement.
                    </p>

                    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">

                        <a
                            href="{{ route('commercial.equipements.index') }}"
                            class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                        >
                            Réinitialiser
                        </a>

                        <a
                            href="{{ route('commercial.equipements.create') }}"
                            class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700"
                        >
                            Ajouter un équipement
                        </a>

                    </div>

                </div>

            @endif

            {{-- PAGINATION --}}
            @if ($equipment->total() > 0)

                <div class="flex flex-col gap-4 border-t border-slate-100 px-6 py-5">

                    <p class="text-xs text-slate-500">
                        Affichage de
                        <span class="font-bold text-slate-700">
                            {{ $equipment->firstItem() }}
                        </span>
                        à
                        <span class="font-bold text-slate-700">
                            {{ $equipment->lastItem() }}
                        </span>
                        sur
                        <span class="font-bold text-slate-700">
                            {{ $equipment->total() }}
                        </span>
                        équipements
                    </p>

                    @if ($equipment->hasPages())
                        {{ $equipment->links() }}
                    @endif

                </div>

            @endif

        </section>

    </div>

</x-commercial.layout>
