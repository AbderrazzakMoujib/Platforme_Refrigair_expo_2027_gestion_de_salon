
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

<div class="space-y-7">

    <!-- BREADCRUMB -->
    <nav class="flex items-center gap-2 text-sm text-slate-500"
         aria-label="Fil d'Ariane">

        <a href="{{ route('admin.index') }}"
           class="transition hover:text-blue-600">
            Administration
        </a>

        <span>/</span>

        <span class="font-semibold text-[#0F2347]">
            Exposants
        </span>
    </nav>

    <!-- PAGE HEADER -->
    <header class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

        <div class="flex items-center gap-4">

            <div class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <x-admin.icon
                    name="storefront"
                    class="size-7"
                />
            </div>

            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                    Gestion des exposants
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Consultez les entreprises, leurs stands et le suivi des badges.
                </p>
            </div>

        </div>

        <div class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-100 bg-blue-50 px-5 py-3 text-sm font-semibold text-blue-700">
            <x-admin.icon name="users" class="size-5" />
            {{ $totalExhibitors }} exposants enregistrés
        </div>
    </header>

    <!-- STATISTICS -->
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
             aria-label="Statistiques des exposants">

        <!-- TOTAL -->
        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <x-admin.icon
                    name="storefront"
                    class="size-7"
                />
            </span>

            <div>
                <p class="text-sm font-medium text-slate-500">
                    Total exposants
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ $totalExhibitors }}
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    Entreprises enregistrées
                </p>
            </div>

        </article>

        <!-- ACTIVE ACCOUNTS -->
        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-emerald-100 text-emerald-600">

                <svg class="size-7"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     aria-hidden="true">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M5 12.5 10 17l9-10"/>
                </svg>

            </span>

            <div>
                <p class="text-sm font-medium text-slate-500">
                    Comptes actifs
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ $activeAccounts }}
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    Accès plateforme activés
                </p>
            </div>

        </article>

        <!-- AUTHORIZED BADGES -->
        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-violet-100 text-violet-600">
                <x-admin.icon
                    name="badge"
                    class="size-7"
                />
            </span>

            <div>
                <p class="text-sm font-medium text-slate-500">
                    Badges autorisés
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ $totalAuthorized }}
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    Quota total attribué
                </p>
            </div>

        </article>

        <!-- REGISTERED BADGES -->
        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-amber-100 text-amber-600">
                <x-admin.icon
                    name="badge-users"
                    class="size-7"
                />
            </span>

            <div>
                <p class="text-sm font-medium text-slate-500">
                    Badges enregistrés
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ $totalRegistered }}
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    {{ $remainingBadges }} places restantes
                </p>
            </div>

        </article>

    </section>

    <!-- MAIN LIST -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <!-- LIST HEADER -->
        <div class="border-b border-slate-100 px-5 py-5 sm:px-6">

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-lg font-bold text-[#0F2347]">
                        Liste des exposants
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Suivez les stands, commerciaux et inscriptions badges.
                    </p>
                </div>

                <span class="w-fit rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">
                    {{ $exhibitors->total() }} résultat(s)
                </span>

            </div>

            <!-- FILTERS -->
            <form method="GET"
                  action="{{ route('admin.exposants.index') }}"
                  class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_auto_auto]">

                <!-- SEARCH -->
                <div class="relative">

                    <label for="search" class="sr-only">
                        Rechercher un exposant
                    </label>

                    <svg class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-slate-400"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         aria-hidden="true">

                        <circle cx="11" cy="11" r="7"/>

                        <path stroke-linecap="round"
                              d="m16 16 5 5"/>

                    </svg>

                    <input id="search"
                           type="search"
                           name="search"
                           maxlength="255"
                           value="{{ request('search') }}"
                           placeholder="Entreprise, contact, stand..."
                           class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-50">
                </div>

                <!-- COMMERCIAL FILTER -->
                <div>
                    <label for="commercial_id" class="sr-only">
                        Commercial responsable
                    </label>

                    <select id="commercial_id"
                            name="commercial_id"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-50">

                        <option value="">
                            Tous les commerciaux
                        </option>

                        @foreach ($commercials as $commercial)
                            <option value="{{ $commercial->id }}"
                                @selected((string) request('commercial_id') === (string) $commercial->id)>
                                {{ $commercial->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <!-- BADGE STATUS -->
                <div>
                    <label for="status" class="sr-only">
                        Statut des badges
                    </label>

                    <select id="status"
                            name="status"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 focus:border-blue-500 focus:ring-blue-50">

                        <option value="">
                            Tous les statuts badges
                        </option>

                        <option value="complete"
                            @selected(request('status') === 'complete')>
                            Complet
                        </option>

                        <option value="partial"
                            @selected(request('status') === 'partial')>
                            En cours
                        </option>

                        <option value="pending"
                            @selected(request('status') === 'pending')>
                            En attente
                        </option>

                        <option value="no_quota"
                            @selected(request('status') === 'no_quota')>
                            Aucun quota
                        </option>

                    </select>
                </div>

                <button type="submit"
                        class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                    Filtrer
                </button>

                <a href="{{ route('admin.exposants.index') }}"
                   class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Réinitialiser
                </a>

                <!-- ACCESS FILTER -->
                <div class="xl:col-span-5">
                    <label for="access"
                           class="mb-2 block text-xs font-semibold text-slate-500">
                        Accès plateforme
                    </label>

                    <select id="access"
                            name="access"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 sm:max-w-xs">

                        <option value="">
                            Tous les accès
                        </option>

                        <option value="active"
                            @selected(request('access') === 'active')>
                            Comptes actifs
                        </option>

                        <option value="inactive"
                            @selected(request('access') === 'inactive')>
                            Comptes désactivés
                        </option>

                        <option value="no_account"
                            @selected(request('access') === 'no_account')>
                            Sans compte
                        </option>

                    </select>
                </div>

            </form>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">

            <table class="w-full min-w-[1100px] text-left text-sm">

                <thead class="bg-[#F5F7FB] text-xs font-semibold uppercase tracking-wide text-slate-500">

                    <tr>
                        <th class="px-5 py-4">Entreprise</th>
                        <th class="px-5 py-4">Contact</th>
                        <th class="px-5 py-4">Commercial</th>
                        <th class="px-5 py-4">Stand</th>
                        <th class="px-5 py-4">Badges</th>
                        <th class="px-5 py-4">Statut badges</th>
                        <th class="px-5 py-4">Accès</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse ($exhibitors as $exhibitor)

                        @php
                            $quota = (int) $exhibitor->authorized_badges;
                            $registered = (int) $exhibitor->badges_count;

                            $progress = $quota > 0
                                ? min(100, round(($registered / $quota) * 100))
                                : 0;

                            $badgeStatus = $quota === 0
                                ? 'Aucun quota'
                                : ($registered >= $quota
                                    ? 'Complet'
                                    : ($registered > 0
                                        ? 'En cours'
                                        : 'En attente'));

                            $companyInitials = collect(
                                preg_split('/\s+/', trim($exhibitor->company_name))
                            )
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                ->implode('');

                            $commercialInitials = $exhibitor->commercial?->initials ?? 'C';
                        @endphp

                        <tr class="transition hover:bg-blue-50/30">

                            <!-- COMPANY -->
                            <td class="px-5 py-5">

                                <div class="flex items-center gap-3">

                                    
@if ($exhibitor->logo)
    <div class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5">
        <img
            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($exhibitor->logo) }}"
            alt="Logo {{ $exhibitor->company_name }}"
            class="max-h-full max-w-full object-contain"
            loading="lazy"
        >
    </div>
@else
    <div class="grid size-14 shrink-0 place-items-center rounded-xl border border-blue-100 bg-blue-50 text-sm font-extrabold text-blue-700">
        {{ $companyInitials ?: 'E' }}
    </div>
@endif


                                    <div class="min-w-0">

                                        <a href="{{ route('admin.exposants.show', $exhibitor) }}"
                                           class="font-bold text-[#0F2347] transition hover:text-blue-600">
                                            {{ $exhibitor->company_name }}
                                        </a>

                                        <p class="mt-1 text-xs text-slate-400">
                                            Exposant #{{ $exhibitor->id }}
                                        </p>

                                    </div>

                                </div>

                            </td>

                            <!-- CONTACT -->
                            <td class="px-5 py-5">

                                <p class="font-semibold text-slate-700">
                                    {{ $exhibitor->contact_name }}
                                </p>

                                <p class="mt-1 max-w-48 truncate text-xs text-slate-500"
                                   title="{{ $exhibitor->email }}">
                                    {{ $exhibitor->email }}
                                </p>

                            </td>

                            <!-- COMMERCIAL -->
                            <td class="px-5 py-5">

                                @if ($exhibitor->commercial)

                                    <div class="flex items-center gap-2">

                                        @if ($exhibitor->commercial->profile_photo_url)

                                            <img src="{{ $exhibitor->commercial->profile_photo_url }}"
                                                 alt="Photo de {{ $exhibitor->commercial->name }}"
                                                 class="size-9 shrink-0 rounded-full border border-slate-200 object-cover">

                                        @else

                                            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-violet-100 text-xs font-bold text-violet-700">
                                                {{ $commercialInitials }}
                                            </span>

                                        @endif

                                        <a href="{{ route('admin.commerciaux.show', $exhibitor->commercial) }}"
                                           class="font-semibold text-slate-700 hover:text-blue-600">
                                            {{ $exhibitor->commercial->name }}
                                        </a>

                                    </div>

                                @else

                                    <span class="text-slate-400">
                                        Non attribué
                                    </span>

                                @endif

                            </td>

                            <!-- STAND -->
                            <td class="px-5 py-5">

                                <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">
                                    {{ $exhibitor->stand_number ?: 'Non attribué' }}
                                </span>

                                <p class="mt-2 text-xs text-slate-500">
                                    {{ $exhibitor->area_sqm !== null
                                        ? number_format((float) $exhibitor->area_sqm, 2, ',', ' ') . ' m²'
                                        : 'Surface non renseignée' }}
                                </p>

                            </td>

                            <!-- BADGE PROGRESS -->
                            <td class="px-5 py-5">

                                <div class="min-w-24">

                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-xs font-bold text-[#0F2347]">
                                            {{ $registered }} / {{ $quota }}
                                        </span>

                                        <span class="text-xs text-slate-400">
                                            {{ $progress }}%
                                        </span>
                                    </div>

                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">

                                        <div class="h-full rounded-full bg-blue-600"
                                             style="width: {{ $progress }}%">
                                        </div>

                                    </div>

                                </div>

                            </td>

                            <!-- BADGE STATUS -->
                            <td class="px-5 py-5">

                                <span @class([
                                    'inline-flex whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-bold',
                                    'bg-emerald-50 text-emerald-700' => $badgeStatus === 'Complet',
                                    'bg-amber-50 text-amber-700' => $badgeStatus === 'En cours',
                                    'bg-red-50 text-red-700' => $badgeStatus === 'En attente',
                                    'bg-slate-100 text-slate-600' => $badgeStatus === 'Aucun quota',
                                ])>
                                    {{ $badgeStatus }}
                                </span>

                            </td>

                            <!-- ACCOUNT STATUS -->
                            <td class="px-5 py-5">

                                @if (!$exhibitor->user)

                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                                        Sans compte
                                    </span>

                                @elseif ($exhibitor->user->is_active)

                                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                        <span class="size-2 rounded-full bg-emerald-500"></span>
                                        Actif
                                    </span>

                                @else

                                    <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                                        <span class="size-2 rounded-full bg-red-500"></span>
                                        Désactivé
                                    </span>

                                @endif

                            </td>

                            <!-- ACTIONS -->
                            <td class="px-5 py-5">

                                <div class="flex justify-end">

                                    <a href="{{ route('admin.exposants.show', $exhibitor) }}"
                                       title="Voir la fiche"
                                       aria-label="Voir {{ $exhibitor->company_name }}"
                                       class="grid size-10 place-items-center rounded-lg border border-slate-200 text-blue-600 transition hover:border-blue-200 hover:bg-blue-50">

                                        <svg class="size-5"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="1.8"
                                             aria-hidden="true">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>

                                            <circle cx="12" cy="12" r="3"/>

                                        </svg>

                                    </a>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">

                                <div class="mx-auto grid size-16 place-items-center rounded-2xl bg-blue-50 text-blue-600">
                                    <x-admin.icon name="storefront" class="size-8"/>
                                </div>

                                <h3 class="mt-4 text-base font-bold text-[#0F2347]">
                                    Aucun exposant trouvé
                                </h3>

                                <p class="mt-2 text-sm text-slate-500">
                                    Modifiez les filtres pour afficher d'autres exposants.
                                </p>

                                <a href="{{ route('admin.exposants.index') }}"
                                   class="mt-4 inline-block font-semibold text-blue-600 hover:underline">
                                    Réinitialiser les filtres
                                </a>

                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>

        </div>

        <!-- PAGINATION -->
        <div class="flex flex-col gap-4 border-t border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between">

            <p class="text-sm text-slate-500">

                @if ($exhibitors->total() > 0)

                    Affichage de {{ $exhibitors->firstItem() }}
                    à {{ $exhibitors->lastItem() }}
                    sur {{ $exhibitors->total() }} exposants

                @else

                    Aucun résultat

                @endif

            </p>

            @if ($exhibitors->hasPages())
                <div>
                    {{ $exhibitors->links() }}
                </div>
            @endif

        </div>

    </section>

</div>

</x-admin.layout>
