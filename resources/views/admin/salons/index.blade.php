
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    <div class="space-y-6">

        <!-- PAGE HEADER -->
        <section class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-start gap-4">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                    <x-admin.icon name="calendar" class="size-7" />
                </span>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">
                        Administration / Salons
                    </p>

                    <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-[#0F2347]">
                        Gestion des salons
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Gérez les salons, leurs informations et les inscriptions des exposants.
                    </p>
                </div>
            </div>

            <a href="{{ route('admin.salons.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                <span class="text-xl leading-none">+</span>
                Ajouter un salon
            </a>

        </section>

        <!-- MESSAGES -->
        @if (session('status'))
            <div role="status"
                 class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert"
                 class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- STATISTICS -->
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
                 aria-label="Statistiques des salons">

            <!-- TOTAL -->
            <article class="flex items-center gap-4 rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-5 shadow-sm">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                    <x-admin.icon name="calendar" class="size-7" />
                </span>
                <div>
                    <p class="text-3xl font-extrabold text-[#0F2347]">
                        {{ $totalSalons }}
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-700">
                        Salons
                    </p>
                    <p class="text-xs text-slate-500">Total</p>
                </div>
            </article>

            <!-- ACTIVE -->
            <article class="flex items-center gap-4 rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <svg class="size-7"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         viewBox="0 0 24 24"
                         aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="m10 8 6 4-6 4V8Z"/>
                    </svg>
                </span>
                <div>
                    <p class="text-3xl font-extrabold text-[#0F2347]">
                        {{ $activeSalons }}
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-700">
                        Salons en cours
                    </p>
                </div>
            </article>

            <!-- PLANNED -->
            <article class="flex items-center gap-4 rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-white p-5 shadow-sm">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-violet-100 text-violet-600">
                    <x-admin.icon name="calendar" class="size-7" />
                </span>
                <div>
                    <p class="text-3xl font-extrabold text-[#0F2347]">
                        {{ $plannedSalons }}
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-700">
                        Salons planifiés
                    </p>
                </div>
            </article>

            <!-- COMPLETED -->
            <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-100 to-white p-5 shadow-sm">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-slate-200 text-slate-600">
                    <svg class="size-7"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         viewBox="0 0 24 24"
                         aria-hidden="true">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="m5 12 4 4L19 6"/>
                    </svg>
                </span>
                <div>
                    <p class="text-3xl font-extrabold text-[#0F2347]">
                        {{ $completedSalons }}
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-700">
                        Salons terminés
                    </p>
                </div>
            </article>

        </section>

        <!-- FILTERS -->
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <form method="GET"
                  action="{{ route('admin.salons.index') }}"
                  class="grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_auto_auto] xl:items-end">

                <div>
                    <label for="search"
                           class="mb-2 block text-xs font-semibold text-slate-600">
                        Rechercher un salon
                    </label>

                    <input id="search"
                           type="search"
                           name="search"
                           maxlength="255"
                           value="{{ request('search') }}"
                           placeholder="Nom, édition, ville ou lieu..."
                           class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>

                <div>
                    <label for="status"
                           class="mb-2 block text-xs font-semibold text-slate-600">
                        Statut
                    </label>

                    <select id="status"
                            name="status"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-blue-500 focus:ring-blue-100">

                        <option value="">Tous les statuts</option>

                        <option value="planned"
                            @selected(request('status') === 'planned')>
                            Planifié
                        </option>

                        <option value="active"
                            @selected(request('status') === 'active')>
                            En cours
                        </option>

                        <option value="completed"
                            @selected(request('status') === 'completed')>
                            Terminé
                        </option>

                        <option value="cancelled"
                            @selected(request('status') === 'cancelled')>
                            Annulé
                        </option>
                    </select>
                </div>

                <div>
                    <label for="year"
                           class="mb-2 block text-xs font-semibold text-slate-600">
                        Année
                    </label>

                    <select id="year"
                            name="year"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-blue-500 focus:ring-blue-100">

                        <option value="">Toutes les années</option>

                        @foreach ($years as $year)
                            <option value="{{ $year }}"
                                @selected((string) request('year') === (string) $year)>
                                {{ $year }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <button type="submit"
                        class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                    Filtrer
                </button>

                <a href="{{ route('admin.salons.index') }}"
                   class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Réinitialiser
                </a>

            </form>
        </section>

        <!-- TABLE -->
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-lg font-bold text-[#0F2347]">
                        Liste des salons
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        {{ $registrationOpenCount }} salon(s) avec inscriptions ouvertes
                    </p>
                </div>

                <span class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">
                    {{ $salons->total() }} résultat(s)
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px] text-left text-sm">

                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-4">Salon</th>
                            <th class="px-5 py-4">Lieu</th>
                            <th class="px-5 py-4">Dates</th>
                            <th class="px-5 py-4">Inscriptions</th>
                            <th class="px-5 py-4">Statut</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse ($salons as $salon)

                            @php
                                $canToggle = in_array(
                                    $salon->status,
                                    ['planned', 'active'],
                                    true
                                );
                            @endphp

                            <tr class="transition hover:bg-blue-50/30">

                                <!-- SALON -->
                                <td class="px-5 py-5">
                                    <div class="flex items-center gap-3">

                                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">
                                            <x-admin.icon name="calendar" class="size-5" />
                                        </span>

                                        <div class="min-w-0">
                                            <a href="{{ route('admin.salons.show', $salon) }}"
                                               class="font-bold text-[#0F2347] hover:text-blue-600">
                                                {{ $salon->name }}
                                            </a>

                                            <p class="mt-1 text-xs text-slate-500">
                                                Édition {{ $salon->edition }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- LOCATION -->
                                <td class="px-5 py-5">
                                    <p class="font-semibold text-slate-700">
                                        {{ $salon->city }}
                                    </p>

                                    <p class="mt-1 max-w-40 truncate text-xs text-slate-500"
                                       title="{{ $salon->venue }}">
                                        {{ $salon->venue }}
                                    </p>
                                </td>

                                <!-- DATES -->
                                <td class="whitespace-nowrap px-5 py-5 text-slate-600">
                                    <span class="inline-flex items-center gap-2">
                                        <x-admin.icon name="calendar" class="size-4 text-slate-400" />

                                        {{ $salon->start_date->format('d/m/Y') }}
                                        —
                                        {{ $salon->end_date->format('d/m/Y') }}
                                    </span>
                                </td>

                                <!-- REGISTRATIONS -->
                                <td class="px-5 py-5">
                                    <div class="flex items-center gap-3">

                                        @if ($canToggle)
                                            <form method="POST"
                                                  action="{{ route('admin.salons.registration', $salon) }}"
                                                  onsubmit="return confirm('Confirmer le changement des inscriptions ?')">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit"
                                                        role="switch"
                                                        aria-checked="{{ $salon->registration_open ? 'true' : 'false' }}"
                                                        aria-label="{{ $salon->registration_open ? 'Fermer' : 'Ouvrir' }} les inscriptions du salon {{ $salon->name }}"
                                                        @class([
                                                            'relative inline-flex h-6 w-11 items-center rounded-full transition',
                                                            'bg-blue-600' => $salon->registration_open,
                                                            'bg-slate-300' => !$salon->registration_open,
                                                        ])>
                                                    <span @class([
                                                        'inline-block size-5 transform rounded-full bg-white shadow-sm transition',
                                                        'translate-x-[22px]' => $salon->registration_open,
                                                        'translate-x-0.5' => !$salon->registration_open,
                                                    ])></span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="relative inline-flex h-6 w-11 items-center rounded-full bg-slate-200 opacity-60"
                                                  title="Inscriptions verrouillées">
                                                <span class="ml-0.5 size-5 rounded-full bg-white shadow-sm"></span>
                                            </span>
                                        @endif

                                        <span @class([
                                            'text-xs font-semibold',
                                            'text-emerald-700' => $salon->registration_open,
                                            'text-slate-500' => !$salon->registration_open,
                                        ])>
                                            {{ $salon->registration_open ? 'Ouvertes' : 'Fermées' }}
                                        </span>

                                    </div>
                                </td>

                                <!-- STATUS -->
                                <td class="px-5 py-5">
                                    <span @class([
                                        'inline-flex whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-bold',
                                        'bg-violet-50 text-violet-700' => $salon->status === 'planned',
                                        'bg-emerald-50 text-emerald-700' => $salon->status === 'active',
                                        'bg-slate-100 text-slate-700' => $salon->status === 'completed',
                                        'bg-red-50 text-red-700' => $salon->status === 'cancelled',
                                    ])>
                                        {{ $salon->status_label }}
                                    </span>
                                </td>

                                <!-- ACTIONS -->
                                <td class="px-5 py-5">
                                    <div class="flex items-center justify-end gap-2">

                                        <a href="{{ route('admin.salons.show', $salon) }}"
                                           title="Consulter le salon"
                                           aria-label="Consulter {{ $salon->name }}"
                                           class="grid size-9 place-items-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                                            <svg class="size-4"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="1.8"
                                                 aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>

                                        <a href="{{ route('admin.salons.edit', $salon) }}"
                                           title="Modifier le salon"
                                           aria-label="Modifier {{ $salon->name }}"
                                           class="grid size-9 place-items-center rounded-lg border border-slate-200 text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600">
                                            <svg class="size-4"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="1.8"
                                                 aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="m16 4 4 4-11 11-5 1 1-5L16 4Z"/>
                                            </svg>
                                        </a>

                                        <details class="relative">
                                            <summary title="Plus d'informations"
                                                     aria-label="Plus d'informations sur {{ $salon->name }}"
                                                     class="grid size-9 cursor-pointer list-none place-items-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-50">
                                                <svg class="size-4"
                                                     viewBox="0 0 24 24"
                                                     fill="currentColor"
                                                     aria-hidden="true">
                                                    <circle cx="12" cy="5" r="2"/>
                                                    <circle cx="12" cy="12" r="2"/>
                                                    <circle cx="12" cy="19" r="2"/>
                                                </svg>
                                            </summary>

                                            <div class="absolute right-0 z-10 mt-2 w-48 rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                                                <p class="px-3 py-2 text-xs text-slate-500">
                                                    {{ $salon->status_label }}
                                                </p>
                                                <a href="{{ route('admin.salons.show', $salon) }}"
                                                   class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                                    Voir les détails
                                                </a>
                                                <a href="{{ route('admin.salons.edit', $salon) }}"
                                                   class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                                    Modifier les informations
                                                </a>
                                            </div>
                                        </details>

                                    </div>
                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-blue-50 text-blue-600">
                                        <x-admin.icon name="calendar" class="size-7" />
                                    </div>

                                    <h3 class="mt-4 text-base font-bold text-slate-800">
                                        Aucun salon trouvé
                                    </h3>

                                    <p class="mt-2 text-sm text-slate-500">
                                        Aucun salon ne correspond à votre recherche.
                                    </p>

                                    <a href="{{ route('admin.salons.index') }}"
                                       class="mt-4 inline-block text-sm font-semibold text-blue-600 hover:underline">
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
                    @if ($salons->total() > 0)
                        Affichage de {{ $salons->firstItem() }}
                        à {{ $salons->lastItem() }}
                        sur {{ $salons->total() }} salons
                    @else
                        Aucun résultat
                    @endif
                </p>

                @if ($salons->hasPages())
                    <div>
                        {{ $salons->links() }}
                    </div>
                @endif

            </div>
        </section>

    </div>

</x-admin.layout>
