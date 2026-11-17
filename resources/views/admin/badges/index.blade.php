
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

@php
    $inactiveBadges = max(0, $totalBadges - $activeBadges);

    $currentStatus = request('status', '');

    $badgePercent = min(100, max(0, $percentage));

    $exportParams = request()->only([
        'exhibitor_id',
        'status',
    ]);
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
            Badges
        </span>
    </nav>

    <!-- HEADER -->
    <header class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">

        <div class="flex items-center gap-4">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <x-admin.icon name="badge" class="size-7"/>
            </span>

            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                    Gestion des badges
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Créez, suivez et gérez les badges des exposants.
                </p>
            </div>

        </div>

        <div class="flex flex-wrap gap-3">

            <a href="{{ route('admin.badges.export', $exportParams) }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-200 bg-white px-5 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">

                <svg class="size-5"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round"
                     aria-hidden="true">
                    <path d="M12 3v12m-4-4 4 4 4-4"/>
                    <path d="M4 16v4h16v-4"/>
                </svg>

                Exporter CSV
            </a>

            <a href="{{ route('admin.badges.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">

                <svg class="size-5"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     stroke-linecap="round"
                     aria-hidden="true">
                    <path d="M12 5v14M5 12h14"/>
                </svg>

                Ajouter un badge
            </a>

        </div>
    </header>

    <!-- ALERTS -->
    @if (session('status'))
        <div role="status"
             class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert"
             class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- STATISTICS -->
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
             aria-label="Statistiques des badges">

        <!-- AUTHORIZED -->
        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-violet-100 text-violet-600">
                <x-admin.icon name="badge" class="size-7"/>
            </span>

            <div>
                <p class="text-sm font-medium text-slate-500">
                    Badges autorisés
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ number_format($totalAuthorized, 0, ',', ' ') }}
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    Quota total
                </p>
            </div>

        </article>

        <!-- REGISTERED -->
        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <x-admin.icon name="badge-users" class="size-7"/>
            </span>

            <div>
                <p class="text-sm font-medium text-slate-500">
                    Badges enregistrés
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ number_format($totalBadges, 0, ',', ' ') }}
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    Actifs et inactifs
                </p>
            </div>

        </article>

        <!-- REMAINING -->
        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-amber-100 text-amber-600">
                <x-admin.icon name="layers" class="size-7"/>
            </span>

            <div>
                <p class="text-sm font-medium text-slate-500">
                    Badges restants
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ number_format($remainingBadges, 0, ',', ' ') }}
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    Quota disponible
                </p>
            </div>

        </article>

        <!-- ACTIVE -->
        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-emerald-100 text-emerald-600">

                <svg class="size-7"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     stroke-linecap="round"
                     stroke-linejoin="round"
                     aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m7 12 3 3 7-7"/>
                </svg>

            </span>

            <div>
                <p class="text-sm font-medium text-slate-500">
                    Badges actifs
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ number_format($activeBadges, 0, ',', ' ') }}
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    Badges valides
                </p>
            </div>

        </article>

    </section>

    <!-- MAIN CARD -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <!-- TABS -->
        <div class="border-b border-slate-100 px-5 pt-5 sm:px-6">

            <div class="flex flex-wrap gap-2">

                <a href="{{ route('admin.badges.index', array_merge(request()->except(['status', 'page']), [])) }}"
                   @class([
                       'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                       'bg-[#2563EB] text-white' => $currentStatus === '',
                       'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' => $currentStatus !== '',
                   ])>

                    Tous

                    <span class="text-xs opacity-80">
                        ({{ $totalBadges }})
                    </span>
                </a>

                <a href="{{ route('admin.badges.index', array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}"
                   @class([
                       'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                       'bg-[#2563EB] text-white' => $currentStatus === 'active',
                       'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' => $currentStatus !== 'active',
                   ])>

                    <span class="size-2 rounded-full bg-emerald-500"></span>

                    Actifs ({{ $activeBadges }})
                </a>

                <a href="{{ route('admin.badges.index', array_merge(request()->except(['status', 'page']), ['status' => 'inactive'])) }}"
                   @class([
                       'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                       'bg-[#2563EB] text-white' => $currentStatus === 'inactive',
                       'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' => $currentStatus !== 'inactive',
                   ])>

                    <span class="size-2 rounded-full bg-red-500"></span>

                    Inactifs ({{ $inactiveBadges }})
                </a>

            </div>

            <!-- FILTERS -->
            <form method="GET"
                  action="{{ route('admin.badges.index') }}"
                  class="mt-5 grid gap-3 pb-5 md:grid-cols-2 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_auto_auto]">

                <!-- SEARCH -->
                <div class="relative">

                    <label for="search" class="sr-only">
                        Rechercher un badge
                    </label>

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
                           placeholder="Nom, email, société, fonction..."
                           class="w-full rounded-xl border border-slate-200 py-3 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500">

                </div>

                <!-- EXHIBITOR -->
                <div>

                    <label for="exhibitor_id" class="sr-only">
                        Exposant
                    </label>

                    <select id="exhibitor_id"
                            name="exhibitor_id"
                            class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500">

                        <option value="">
                            Tous les exposants
                        </option>

                        @foreach ($exhibitors as $exhibitor)
                            <option value="{{ $exhibitor->id }}"
                                @selected((string) request('exhibitor_id') === (string) $exhibitor->id)>
                                {{ $exhibitor->company_name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <!-- STATUS -->
                <div>

                    <label for="status" class="sr-only">
                        Statut
                    </label>

                    <select id="status"
                            name="status"
                            class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-500">

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

                <button type="submit"
                        class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                    Filtrer
                </button>

                <a href="{{ route('admin.badges.index') }}"
                   class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Réinitialiser
                </a>

            </form>

        </div>

        <!-- LIST HEADER -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">

            <div>
                <h2 class="text-lg font-bold text-[#0F2347]">
                    Liste des badges
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $badges->total() }} résultat(s)
                </p>
            </div>

            <div class="flex items-center gap-3 text-xs text-slate-500">

                <span class="inline-flex items-center gap-2">
                    <span class="size-2 rounded-full bg-emerald-500"></span>
                    Actif
                </span>

                <span class="inline-flex items-center gap-2">
                    <span class="size-2 rounded-full bg-red-500"></span>
                    Inactif
                </span>

            </div>
        </div>

        <!-- BULK PRINT -->
        <form id="bulk-print-form"
              method="GET"
              action="{{ route('admin.badges.print-selected') }}"
              target="_blank">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[1100px] text-left text-sm">

                    <thead class="bg-[#F5F7FB] text-xs font-semibold uppercase tracking-wide text-slate-500">

                        <tr>
                            <th class="px-5 py-4">
                                <input id="select-all-badges"
                                       type="checkbox"
                                       aria-label="Tout sélectionner sur cette page"
                                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            </th>

                            <th class="px-5 py-4">
                                Titulaire du badge
                            </th>

                            <th class="px-5 py-4">
                                Exposant
                            </th>

                            <th class="px-5 py-4">
                                Fonction
                            </th>

                            <th class="px-5 py-4">
                                Référence
                            </th>

                            <th class="px-5 py-4">
                                Enregistrement
                            </th>

                            <th class="px-5 py-4">
                                Statut
                            </th>

                            <th class="px-5 py-4 text-center">
                                QR Code
                            </th>

                            <th class="px-5 py-4 text-right">
                                Actions
                            </th>
                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse ($badges as $badge)

                            @php
                                $initials = collect([
                                    $badge->first_name,
                                    $badge->last_name,
                                ])
                                    ->filter()
                                    ->map(
                                        fn ($part) => mb_strtoupper(
                                            mb_substr(trim($part), 0, 1)
                                        )
                                    )
                                    ->implode('');

                                $exhibitor = $badge->exhibitor;

                                $companyInitials = collect(
                                    preg_split(
                                        '/\s+/',
                                        trim($exhibitor?->company_name ?? '')
                                    )
                                )
                                    ->filter()
                                    ->take(2)
                                    ->map(
                                        fn ($part) => mb_strtoupper(
                                            mb_substr($part, 0, 1)
                                        )
                                    )
                                    ->implode('');
                            @endphp

                            <tr class="transition hover:bg-blue-50/30">

                                <!-- SELECT -->
                                <td class="px-5 py-5">

                                    <input type="checkbox"
                                           name="ids[]"
                                           value="{{ $badge->id }}"
                                           aria-label="Sélectionner {{ $badge->reference }}"
                                           class="badge-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                                </td>

                                <!-- HOLDER -->
                                <td class="px-5 py-5">

                                    <div class="flex items-center gap-3">

                                        <div class="grid size-11 shrink-0 place-items-center rounded-full bg-blue-100 text-sm font-extrabold text-blue-700">
                                            {{ $initials ?: 'B' }}
                                        </div>

                                        <div class="min-w-0">

                                            <a href="{{ route('admin.badges.show', $badge) }}"
                                               class="font-bold text-[#0F2347] hover:text-blue-600">
                                                {{ $badge->full_name }}
                                            </a>

                                            <p class="mt-1 max-w-48 truncate text-xs text-slate-500"
                                               title="{{ $badge->email }}">
                                                {{ $badge->email }}
                                            </p>

                                        </div>
                                    </div>

                                </td>

                                <!-- COMPANY -->
                                <td class="px-5 py-5">

                                    @if ($exhibitor)

                                        <div class="flex items-center gap-3">

                                            @if ($exhibitor->logo)

                                                <div class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-slate-200 bg-white p-1">

                                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($exhibitor->logo) }}"
                                                         alt="Logo {{ $exhibitor->company_name }}"
                                                         class="max-h-full max-w-full object-contain"
                                                         loading="lazy">

                                                </div>

                                            @else

                                                <div class="grid size-10 shrink-0 place-items-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">
                                                    {{ $companyInitials ?: 'E' }}
                                                </div>

                                            @endif

                                            <div>

                                                <a href="{{ route('admin.exposants.show', $exhibitor) }}"
                                                   class="font-semibold text-slate-700 hover:text-blue-600">
                                                    {{ $exhibitor->company_name }}
                                                </a>

                                                <p class="mt-1 text-xs text-slate-400">
                                                    {{ $exhibitor->commercial?->name ?: 'Commercial non attribué' }}
                                                </p>

                                            </div>

                                        </div>

                                    @else

                                        <span class="text-slate-400">
                                            Exposant indisponible
                                        </span>

                                    @endif

                                </td>

                                <!-- FUNCTION -->
                                <td class="px-5 py-5 text-slate-600">
                                    {{ $badge->function }}
                                </td>

                                <!-- REFERENCE -->
                                <td class="px-5 py-5">

                                    <span class="inline-flex whitespace-nowrap rounded-lg bg-blue-50 px-3 py-2 font-bold text-blue-700">
                                        {{ $badge->reference }}
                                    </span>

                                </td>

                                <!-- DATE -->
                                <td class="px-5 py-5 text-slate-600">

                                    <span class="whitespace-nowrap">
                                        {{ $badge->created_at?->format('d/m/Y') }}
                                    </span>

                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $badge->created_at?->format('H:i') }}
                                    </p>

                                </td>

                                <!-- STATUS -->
                                <td class="px-5 py-5">

                                    <span @class([
                                        'inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold',
                                        'bg-emerald-50 text-emerald-700' => $badge->is_active,
                                        'bg-red-50 text-red-700' => !$badge->is_active,
                                    ])>

                                        <span @class([
                                            'size-2 rounded-full',
                                            'bg-emerald-500' => $badge->is_active,
                                            'bg-red-500' => !$badge->is_active,
                                        ])></span>

                                        {{ $badge->is_active ? 'Actif' : 'Inactif' }}

                                    </span>

                                </td>

                                <!-- QR CODE -->
                                <td class="px-5 py-5">

                                    <div class="flex justify-center">

                                        <a href="{{ route('admin.badges.show', $badge) }}"
                                           title="Afficher le QR Code"
                                           aria-label="QR Code {{ $badge->reference }}"
                                           class="grid size-10 place-items-center rounded-lg border border-blue-100 bg-blue-50 text-blue-700 transition hover:bg-blue-100">

                                            <svg class="size-5"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="1.8"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round"
                                                 aria-hidden="true">

                                                <rect x="3" y="3" width="7" height="7" rx="1"/>
                                                <rect x="14" y="3" width="7" height="7" rx="1"/>
                                                <rect x="3" y="14" width="7" height="7" rx="1"/>

                                                <path d="M5 5h3v3H5zM16 5h3v3h-3zM5 16h3v3H5z"/>
                                                <path d="M14 14h2v2h-2zM19 14h2M18 17v2M15 19h2M21 20h-2"/>

                                            </svg>

                                        </a>

                                    </div>

                                </td>

                                <!-- ACTIONS -->
                                <td class="px-5 py-5">

                                    <div class="flex justify-end gap-2">

                                        <!-- VIEW -->
                                        <a href="{{ route('admin.badges.show', $badge) }}"
                                           title="Voir les détails"
                                           aria-label="Voir {{ $badge->reference }}"
                                           class="grid size-9 place-items-center rounded-lg border border-slate-200 text-blue-600 transition hover:bg-blue-50">

                                            <svg class="size-5"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="1.8"
                                                 aria-hidden="true">
                                                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>

                                        </a>

                                        <!-- PRINT -->
                                        <a href="{{ route('admin.badges.print', $badge) }}"
                                           target="_blank"
                                           rel="noopener"
                                           title="Imprimer le badge"
                                           aria-label="Imprimer {{ $badge->reference }}"
                                           class="grid size-9 place-items-center rounded-lg border border-slate-200 text-blue-600 transition hover:bg-blue-50">

                                            <svg class="size-5"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="1.8"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round"
                                                 aria-hidden="true">
                                                <path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                                                <path d="M6 14h12v7H6zM18 12h.01"/>
                                            </svg>

                                        </a>

                                        <!-- EDIT -->
                                        <a href="{{ route('admin.badges.edit', $badge) }}"
                                           title="Modifier le badge"
                                           aria-label="Modifier {{ $badge->reference }}"
                                           class="grid size-9 place-items-center rounded-lg border border-slate-200 text-blue-600 transition hover:bg-blue-50">

                                            <svg class="size-5"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="1.8"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round"
                                                 aria-hidden="true">
                                                <path d="M12 20h9"/>
                                                <path d="M16 4a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4Z"/>
                                            </svg>

                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="9"
                                    class="px-6 py-16 text-center">

                                    <div class="mx-auto grid size-16 place-items-center rounded-2xl bg-blue-50 text-blue-600">
                                        <x-admin.icon name="badge" class="size-8"/>
                                    </div>

                                    <h3 class="mt-4 text-base font-bold text-[#0F2347]">
                                        Aucun badge trouvé
                                    </h3>

                                    <p class="mt-2 text-sm text-slate-500">
                                        Ajoutez un badge ou modifiez les filtres.
                                    </p>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <!-- BULK PRINT TOOLBAR -->
            @if ($badges->count())

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-[#F8FAFC] px-5 py-4 sm:px-6">

                    <p id="selection-counter"
                       class="text-sm font-medium text-slate-600">
                        0 badge sélectionné
                    </p>

                    <button id="print-selected-button"
                            type="submit"
                            disabled
                            class="inline-flex items-center gap-2 rounded-xl bg-[#0F2347] px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40">

                        <svg class="size-5"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             aria-hidden="true">
                            <path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                            <path d="M6 14h12v7H6z"/>
                        </svg>

                        Imprimer la sélection
                    </button>

                </div>

            @endif

        </form>

        <!-- PAGINATION -->
        <div class="flex flex-col gap-4 border-t border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">

            <p class="text-sm text-slate-500">

                @if ($badges->total())

                    Affichage de {{ $badges->firstItem() }}
                    à {{ $badges->lastItem() }}
                    sur {{ $badges->total() }} badges

                @else

                    Aucun résultat

                @endif

            </p>

            @if ($badges->hasPages())
                <div>
                    {{ $badges->links() }}
                </div>
            @endif

        </div>

    </section>

    <!-- GLOBAL PROGRESS -->
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

        <div class="flex flex-wrap items-start justify-between gap-3">

            <div>
                <h2 class="text-lg font-bold text-[#0F2347]">
                    Utilisation globale des badges
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Progression des inscriptions par rapport aux quotas autorisés.
                </p>
            </div>

            <span class="rounded-lg bg-blue-50 px-4 py-2 text-lg font-extrabold text-blue-700">
                {{ number_format($percentage, 1, ',', ' ') }}%
            </span>

        </div>

        <div class="mt-6 h-3 overflow-hidden rounded-full bg-slate-100">

            <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-blue-700"
                 style="width: {{ $badgePercent }}%">
            </div>

        </div>

        <div class="mt-3 flex flex-wrap justify-between gap-3 text-xs text-slate-500">

            <span>
                {{ $totalBadges }} badges enregistrés
            </span>

            <span>
                {{ $totalAuthorized }} badges autorisés
            </span>

        </div>

    </section>

    <!-- QUOTA MANAGEMENT -->
    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h2 class="text-lg font-bold text-[#0F2347]">
                    Gestion des quotas
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Définissez le nombre de badges autorisés pour chaque exposant.
                </p>
            </div>

            <span class="w-fit rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">
                {{ $exhibitors->count() }} exposant(s)
            </span>

        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-2">

            @forelse ($exhibitors as $exhibitor)

                <form method="POST"
                      action="{{ route('admin.badges.quota', $exhibitor) }}"
                      class="rounded-xl border border-slate-200 bg-[#FAFBFD] p-5">

                    @csrf
                    @method('PATCH')

                    <div class="flex items-start justify-between gap-3">

                        <div>

                            <p class="font-bold text-[#0F2347]">
                                {{ $exhibitor->company_name }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                Quota autorisé
                            </p>

                        </div>

                        <span class="rounded-lg bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700">
                            #{{ $exhibitor->id }}
                        </span>

                    </div>

                    <div class="mt-4 flex flex-wrap items-end gap-3">

                        <div class="flex-1">

                            <label for="quota-{{ $exhibitor->id }}"
                                   class="block text-xs font-semibold text-slate-600">
                                Nombre de badges
                            </label>

                            <input id="quota-{{ $exhibitor->id }}"
                                   type="number"
                                   name="authorized_badges"
                                   value="{{ $exhibitor->authorized_badges }}"
                                   min="0"
                                   max="100000"
                                   required
                                   class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">

                        </div>

                        <button type="submit"
                                class="rounded-xl bg-[#2563EB] px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                            Enregistrer
                        </button>

                    </div>

                </form>

            @empty

                <p class="text-sm text-slate-500">
                    Aucun exposant disponible.
                </p>

            @endforelse

        </div>

    </section>

</div>

<!-- SELECT ALL SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('bulk-print-form');
    const selectAll = document.getElementById('select-all-badges');
    const checkboxes = Array.from(
        document.querySelectorAll('.badge-checkbox')
    );
    const counter = document.getElementById('selection-counter');
    const printButton = document.getElementById('print-selected-button');

    if (!form || !selectAll) return;

    function updateSelection() {
        const selected = checkboxes.filter(
            checkbox => checkbox.checked
        ).length;

        const total = checkboxes.length;

        selectAll.checked = total > 0 && selected === total;
        selectAll.indeterminate = selected > 0 && selected < total;

        if (counter) {
            counter.textContent =
                selected + ' badge' +
                (selected > 1 ? 's' : '') +
                ' sélectionné' +
                (selected > 1 ? 's' : '');
        }

        if (printButton) {
            printButton.disabled = selected === 0;
        }
    }

    selectAll.addEventListener('change', () => {
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });

        updateSelection();
    });

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelection);
    });

    updateSelection();
});
</script>

</x-admin.layout>
