
@php
    $fmt = fn ($value) => number_format((float) $value, 0, ',', ' ');

    $pct = fn ($value) => rtrim(
        rtrim(number_format((float) $value, 1, ',', ' '), '0'),
        ','
    );

    $statusMeta = [
        'complete' => [
            'label' => 'Complet',
            'pill' => 'bg-emerald-50 text-emerald-700',
            'bar' => 'bg-emerald-500',
            'dot' => 'bg-emerald-500',
        ],
        'partial' => [
            'label' => 'Partiel',
            'pill' => 'bg-amber-50 text-amber-700',
            'bar' => 'bg-amber-500',
            'dot' => 'bg-amber-500',
        ],
        'pending' => [
            'label' => 'En attente',
            'pill' => 'bg-red-50 text-red-700',
            'bar' => 'bg-red-500',
            'dot' => 'bg-red-500',
        ],
        'no_quota' => [
            'label' => 'Sans quota',
            'pill' => 'bg-slate-100 text-slate-600',
            'bar' => 'bg-slate-400',
            'dot' => 'bg-slate-400',
        ],
    ];

    $statusOf = fn ($exhibitor) => match (true) {
        $exhibitor->authorized_badges <= 0 => 'no_quota',
        (int) $exhibitor->badges_count === 0 => 'pending',
        $exhibitor->badges_count >= $exhibitor->authorized_badges => 'complete',
        default => 'partial',
    };

    $activityMeta = [
        'created' => [
            'label' => 'Badge créé',
            'icon' => 'user-plus',
            'color' => 'bg-blue-50 text-blue-600',
        ],
        'updated' => [
            'label' => 'Badge modifié',
            'icon' => 'settings',
            'color' => 'bg-violet-50 text-violet-600',
        ],
        'activated' => [
            'label' => 'Badge réactivé',
            'icon' => 'clipboard-check',
            'color' => 'bg-emerald-50 text-emerald-600',
        ],
        'deactivated' => [
            'label' => 'Badge désactivé',
            'icon' => 'badge',
            'color' => 'bg-red-50 text-red-600',
        ],
        'archived' => [
            'label' => 'Badge archivé',
            'icon' => 'box',
            'color' => 'bg-slate-100 text-slate-600',
        ],
        'printed' => [
            'label' => 'Badge imprimé',
            'icon' => 'badge',
            'color' => 'bg-emerald-50 text-emerald-600',
        ],
    ];

    $tabs = [
        'exhibitors' => [
            'label' => 'Par exposant',
            'icon' => 'users',
        ],
        'badges' => [
            'label' => 'Badges',
            'icon' => 'badge',
        ],
        'history' => [
            'label' => 'Historique',
            'icon' => 'calendar',
        ],
    ];

    $usedPercentage = $totalAuthorized > 0
        ? min(100, $totalBadges / $totalAuthorized * 100)
        : 0;

    $exportParams = array_filter([
        'exhibitor_id' => $filters['exhibitor_id'],
    ]);

    $cards = [
        [
            'label' => 'Total badges',
            'value' => $fmt($totalBadges),
            'description' => '+'.$fmt($newBadgesThisMonth).' ce mois',
            'icon' => 'badge-users',
            'color' => '#2563EB',
            'background' => '#EFF6FF',
            'icon_bg' => '#DBEAFE',
        ],
        [
            'label' => 'Badges imprimés',
            'value' => $fmt($printedBadges),
            'description' => $fmt(max(0, $totalBadges - $printedBadges)).' pas encore imprimés',
            'icon' => 'clipboard-check',
            'color' => '#059669',
            'background' => '#ECFDF5',
            'icon_bg' => '#D1FAE5',
        ],
        [
            'label' => 'Quotas utilisés',
            'value' => $pct($quotaUsage).'%',
            'description' => $fmt($totalBadges).' / '.$fmt($totalAuthorized).' badges',
            'icon' => 'chart',
            'color' => '#7C3AED',
            'background' => '#F5F3FF',
            'icon_bg' => '#EDE9FE',
        ],
        [
            'label' => 'Badges restants',
            'value' => $fmt($remainingBadges),
            'description' => $totalAuthorized > 0
                ? $pct(100 - $quotaUsage).'% du quota'
                : 'Aucun quota attribué',
            'icon' => 'layers',
            'color' => '#D97706',
            'background' => '#FFF7ED',
            'icon_bg' => '#FFEDD5',
        ],
    ];

    $inputClass = 'h-11 min-w-0 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100';
@endphp

<x-commercial.layout
    :commercial="$commercial"
    :commercial-initials="$commercialInitials"
>

    <div class="min-w-0 space-y-6">

        {{-- BREADCRUMB --}}

        <nav class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500"
             aria-label="Fil d'Ariane">

            <a href="{{ route('commercial.index') }}"
               class="transition hover:text-blue-600">

                Vue globale

            </a>

            <span>/</span>

            <span class="font-semibold text-blue-600">

                Gestion des badges

            </span>

        </nav>


        {{-- PAGE HEADER --}}

        <header class="flex flex-wrap items-center justify-between gap-4">

            <div class="flex items-center gap-4">

                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-[#2563EB]">

                    <x-admin.icon
                        name="badge"
                        class="size-8"
                    />

                </span>

                <div>

                    <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">

                        Gestion des badges

                    </h1>

                    <p class="mt-2 text-sm text-slate-500">

                        Suivez les quotas et les badges de vos exposants.

                    </p>

                </div>

            </div>

            <a
                href="{{ route('commercial.badges.export', $exportParams) }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
            >

                <svg
                    class="size-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >

                    <path d="M12 3v12m-5-5 5 5 5-5"/>
                    <path d="M4 16v5h16v-5"/>

                </svg>

                Exporter (CSV)

            </a>

        </header>


        {{-- SUCCESS MESSAGE --}}

        @if (session('status'))

            <div
                role="status"
                class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700"
            >

                <x-admin.icon
                    name="clipboard-check"
                    class="size-5 shrink-0"
                />

                {{ session('status') }}

            </div>

        @endif


        {{-- VALIDATION ERRORS --}}

        @if ($errors->any())

            <div
                role="alert"
                class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"
            >

                @foreach ($errors->all() as $error)

                    <p>{{ $error }}</p>

                @endforeach

            </div>

        @endif


        {{-- STATISTICS CARDS --}}

        <section
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-4"
            aria-label="Statistiques des badges"
        >

            @foreach ($cards as $card)

                <article
                    class="min-w-0 rounded-2xl border border-slate-200/80 p-5 shadow-sm"
                    style="background: linear-gradient(145deg, #FFFFFF, {{ $card['background'] }})"
                >

                    <div class="flex items-start gap-4">

                        <span
                            class="grid size-12 shrink-0 place-items-center rounded-full"
                            style="background: {{ $card['icon_bg'] }}; color: {{ $card['color'] }}"
                        >

                            <x-admin.icon
                                :name="$card['icon']"
                                class="size-6"
                            />

                        </span>

                        <div class="min-w-0">

                            <p class="text-sm font-semibold text-[#0F2347]">

                                {{ $card['label'] }}

                            </p>

                            <p class="mt-2 text-3xl font-extrabold tracking-tight text-[#0F2347]">{{ $card['value'] }}</p>

                            <p
                                class="mt-2 text-xs font-medium leading-relaxed"
                                style="color: {{ $card['color'] }}"
                            >

                                {{ $card['description'] }}

                            </p>

                        </div>

                    </div>

                </article>

            @endforeach

        </section>


        {{-- MAIN BADGES SECTION --}}

        <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">


            {{-- SECTION HEADER --}}

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">

                <div class="flex items-center gap-3">

                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">

                        <x-admin.icon
                            name="badge-users"
                            class="size-6"
                        />

                    </span>

                    <div>

                        <h2 class="text-base font-bold text-[#0F2347]">

                            Suivi des badges

                        </h2>

                        <p class="mt-1 text-xs text-slate-500">

                            Gérez les quotas, les impressions et les activités.

                        </p>

                    </div>

                </div>

                @if ($tab === 'exhibitors')

                    <span class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">

                        {{ $exhibitors->total() }} exposant(s)

                    </span>

                @elseif ($tab === 'badges')

                    <span class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">

                        {{ $badges->total() }} badge(s)

                    </span>

                @endif

            </div>


            {{-- TABS --}}

            <div class="border-b border-slate-100 px-5 pt-4">

                <nav
                    class="flex gap-2 overflow-x-auto"
                    aria-label="Onglets badges"
                >

                    @foreach ($tabs as $key => $item)

                        <a
                            href="{{ route('commercial.badges.index', ['tab' => $key]) }}"
                            @class([
                                'inline-flex shrink-0 items-center justify-center gap-2 rounded-t-xl border-b-2 px-5 py-3 text-sm font-semibold transition',
                                'border-blue-600 bg-blue-50 text-blue-700' => $tab === $key,
                                'border-transparent text-slate-500 hover:bg-slate-50 hover:text-blue-600' => $tab !== $key,
                            ])
                            @if ($tab === $key)
                                aria-current="page"
                            @endif
                        >

                            <x-admin.icon
                                :name="$item['icon']"
                                class="size-5"
                            />

                            {{ $item['label'] }}

                        </a>

                    @endforeach

                </nav>

            </div>


            {{-- ===================================================== --}}
            {{-- TAB 1 : PAR EXPOSANT --}}
            {{-- ===================================================== --}}

            @if ($tab === 'exhibitors')


                {{-- SEARCH AND FILTERS --}}

                <div class="border-b border-slate-100 p-5">

                    <form
                        method="GET"
                        action="{{ route('commercial.badges.index') }}"
                        class="flex flex-col gap-3 lg:flex-row"
                    >

                        <input
                            type="hidden"
                            name="tab"
                            value="exhibitors"
                        >

                        <div class="relative min-w-0 flex-1">

                            <label for="search" class="sr-only">

                                Rechercher un exposant

                            </label>

                            <svg
                                class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-slate-400"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                aria-hidden="true"
                            >

                                <circle cx="11" cy="11" r="7"/>
                                <path d="m16 16 4 4"/>

                            </svg>

                            <input
                                id="search"
                                type="search"
                                name="search"
                                value="{{ $filters['search'] }}"
                                placeholder="Rechercher un exposant (nom, email, stand…)"
                                class="{{ $inputClass }} w-full pl-11"
                            >

                        </div>

                        <label for="status" class="sr-only">

                            Statut

                        </label>

                        <select
                            id="status"
                            name="status"
                            class="{{ $inputClass }} lg:w-48"
                        >

                            <option value="">

                                Tous les statuts

                            </option>

                            @foreach ($statusMeta as $key => $meta)

                                <option
                                    value="{{ $key }}"
                                    @selected($filters['status'] === $key)
                                >

                                    {{ $meta['label'] }}

                                </option>

                            @endforeach

                        </select>

                        <button
                            type="submit"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 text-sm font-semibold text-white transition hover:bg-blue-700"
                        >

                            <svg
                                class="size-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >

                                <path d="M4 4h16l-6 8v6l-4 2v-8L4 4Z"/>

                            </svg>

                            Filtrer

                        </button>

                        @if ($filters['search'] !== '' || $filters['status'])

                            <a
                                href="{{ route('commercial.badges.index') }}"
                                class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                            >

                                Réinitialiser

                            </a>

                        @endif

                    </form>

                </div>


                {{-- EXHIBITORS TABLE --}}

                <div class="overflow-x-auto">

                    <table class="w-full min-w-[980px] text-left text-sm">

                        <thead class="border-b border-slate-200 bg-[#F8FAFC] text-xs font-semibold text-slate-600">

                            <tr>

                                <th scope="col" class="px-5 py-4">

                                    Exposant

                                </th>

                                <th scope="col" class="px-4 py-4">

                                    Stand

                                </th>

                                <th scope="col" class="px-4 py-4">

                                    Quota

                                </th>

                                <th scope="col" class="px-4 py-4 text-center">

                                    Utilisés

                                </th>

                                <th scope="col" class="px-4 py-4 text-center">

                                    Restants

                                </th>

                                <th scope="col" class="px-4 py-4">

                                    Progression

                                </th>

                                <th scope="col" class="px-4 py-4">

                                    Statut

                                </th>

                                <th scope="col" class="px-5 py-4 text-right">

                                    Actions

                                </th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @forelse ($exhibitors as $exhibitor)

                                @php
                                    $meta = $statusMeta[$statusOf($exhibitor)];

                                    $used = (int) $exhibitor->badges_count;
                                    $quota = (int) $exhibitor->authorized_badges;
                                    $remaining = max(0, $quota - $used);

                                    $rate = $quota > 0
                                        ? min(100, round($used / $quota * 100))
                                        : 0;

                                    $logoUrl = $exhibitor->logo
                                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($exhibitor->logo)
                                        : null;

                                    $initials = mb_strtoupper(
                                        mb_substr($exhibitor->company_name, 0, 2)
                                    );
                                @endphp

                                <tr class="align-middle transition hover:bg-slate-50/80">


                                    {{-- EXHIBITOR LOGO --}}

                                    <td class="px-5 py-4">

                                        <div class="flex min-w-0 items-center gap-3">

                                            <div class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white">

                                                @if ($logoUrl)

                                                    <img
                                                        src="{{ $logoUrl }}"
                                                        alt="Logo {{ $exhibitor->company_name }}"
                                                        class="h-full w-full object-contain p-1.5"
                                                        loading="lazy"
                                                        data-badge-avatar
                                                    >

                                                    <span
                                                        data-avatar-fallback
                                                        class="hidden text-sm font-bold text-blue-700"
                                                    >

                                                        {{ $initials }}

                                                    </span>

                                                @else

                                                    <span class="text-sm font-bold text-blue-700">

                                                        {{ $initials }}

                                                    </span>

                                                @endif

                                            </div>

                                            <div class="min-w-0">

                                                <a
                                                    href="{{ route('commercial.exhibitors.show', $exhibitor) }}"
                                                    class="block max-w-[180px] truncate font-bold text-[#0F2347] hover:text-blue-600"
                                                    title="{{ $exhibitor->company_name }}"
                                                >

                                                    {{ $exhibitor->company_name }}

                                                </a>

                                                <p class="mt-1 max-w-[180px] truncate text-xs text-slate-500">

                                                    {{ $exhibitor->email }}

                                                </p>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- STAND --}}

                                    <td class="px-4 py-4">

                                        <span class="inline-flex whitespace-nowrap rounded-lg border border-blue-100 bg-blue-50 px-2.5 py-1.5 text-xs font-bold text-blue-700">

                                            {{ $exhibitor->stand_number ?: '—' }}

                                        </span>

                                    </td>


                                    {{-- EDIT QUOTA --}}

                                    <td class="px-4 py-4">

                                        <form
                                            method="POST"
                                            action="{{ route('commercial.badges.quota', $exhibitor) }}"
                                            class="flex items-center gap-2"
                                        >

                                            @csrf
                                            @method('PATCH')

                                            <label
                                                for="quota-{{ $exhibitor->id }}"
                                                class="sr-only"
                                            >

                                                Quota de {{ $exhibitor->company_name }}

                                            </label>

                                            <input
                                                id="quota-{{ $exhibitor->id }}"
                                                type="number"
                                                name="authorized_badges"
                                                min="{{ $used }}"
                                                max="100000"
                                                step="1"
                                                value="{{ $quota }}"
                                                required
                                                class="h-9 w-20 rounded-lg border border-slate-200 bg-slate-50 px-2 text-center text-sm font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-blue-100"
                                            >

                                            <button
                                                type="submit"
                                                title="Enregistrer le quota"
                                                class="grid size-9 shrink-0 place-items-center rounded-lg border border-blue-100 bg-blue-50 text-blue-600 transition hover:bg-blue-600 hover:text-white"
                                            >

                                                <svg
                                                    class="size-4"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    aria-hidden="true"
                                                >

                                                    <path d="m5 12 4 4L19 6"/>

                                                </svg>

                                                <span class="sr-only">

                                                    OK

                                                </span>

                                            </button>

                                        </form>

                                    </td>


                                    {{-- USED BADGES --}}

                                    <td class="px-4 py-4 text-center">

                                        <span class="font-bold text-[#0F2347]">{{ $fmt($used) }}</span>

                                    </td>


                                    {{-- REMAINING BADGES --}}

                                    <td class="px-4 py-4 text-center">

                                        <span class="font-semibold text-slate-600">{{ $fmt($remaining) }}</span>

                                    </td>


                                    {{-- PROGRESS BAR --}}

                                    <td class="px-4 py-4">

                                        <div class="min-w-[100px]">

                                            <div class="flex items-center justify-between gap-2">

                                                <span class="text-xs font-bold text-[#0F2347]">

                                                    {{ $rate }}%

                                                </span>

                                                <span class="text-[10px] text-slate-400">

                                                    {{ $used }}/{{ $quota }}

                                                </span>

                                            </div>

                                            <div
                                                class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"
                                                role="progressbar"
                                                aria-label="Progression des badges de {{ $exhibitor->company_name }}"
                                                aria-valuemin="0"
                                                aria-valuemax="100"
                                                aria-valuenow="{{ $rate }}"
                                            >

                                                <div
                                                    class="h-full rounded-full {{ $meta['bar'] }}"
                                                    style="width: {{ $rate }}%"
                                                ></div>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- STATUS --}}

                                    <td class="px-4 py-4">

                                        <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-semibold {{ $meta['pill'] }}">

                                            <span class="size-1.5 rounded-full {{ $meta['dot'] }}"></span>

                                            {{ $meta['label'] }}

                                        </span>

                                    </td>


                                    {{-- ACTIONS --}}

                                    <td class="px-5 py-4">

                                        <div class="flex items-center justify-end gap-2">

                                            <a
                                                href="{{ route('commercial.badges.index', [
                                                    'tab' => 'badges',
                                                    'exhibitor_id' => $exhibitor->id,
                                                ]) }}"
                                                class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600"
                                            >

                                                <x-admin.icon
                                                    name="badge"
                                                    class="size-4"
                                                />

                                                Badges

                                            </a>

                                            @if ($exhibitor->active_badges_count > 0)

                                                <form
                                                    method="POST"
                                                    action="{{ route('commercial.badges.print-exhibitor', $exhibitor) }}"
                                                    target="_blank"
                                                >

                                                    @csrf

                                                    <button
                                                        type="submit"
                                                        title="Imprimer les {{ $exhibitor->active_badges_count }} badges actifs avec QR code"
                                                        class="inline-flex h-9 items-center gap-1.5 whitespace-nowrap rounded-lg bg-[#2563EB] px-3 text-xs font-semibold text-white transition hover:bg-blue-700"
                                                    >

                                                        <svg
                                                            class="size-4"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.8"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            aria-hidden="true"
                                                        >

                                                            <path d="M6 9V3h12v6"/>
                                                            <rect x="6" y="14" width="12" height="7" rx="1"/>
                                                            <path d="M6 17H3a1 1 0 0 1-1-1v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a1 1 0 0 1-1 1h-3"/>
                                                            <path d="M17 12h1"/>

                                                        </svg>

                                                        Imprimer ({{ $exhibitor->active_badges_count }})

                                                    </button>

                                                </form>

                                            @endif

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="8" class="p-10">

                                        <x-admin.empty-state
                                            icon="users"
                                            title="Aucun exposant trouvé"
                                            description="Ajoutez un exposant ou modifiez vos filtres."
                                        />

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- EXHIBITORS PAGINATION --}}

                <div class="space-y-4 border-t border-slate-100 px-5 py-4">

                    @if ($exhibitors->total() > 0)

                        <p class="text-xs text-slate-500">

                            Affichage de {{ $exhibitors->firstItem() }}

                            à {{ $exhibitors->lastItem() }}

                            sur {{ $exhibitors->total() }} exposants.

                        </p>

                    @endif

                    {{ $exhibitors->links() }}

                </div>


            {{-- ===================================================== --}}
            {{-- TAB 2 : BADGES --}}
            {{-- ===================================================== --}}

            @elseif ($tab === 'badges')


                {{-- BADGE SEARCH --}}

                <div class="border-b border-slate-100 p-5">

                    <form
                        method="GET"
                        action="{{ route('commercial.badges.index') }}"
                        class="flex flex-col gap-3 lg:flex-row"
                    >

                        <input
                            type="hidden"
                            name="tab"
                            value="badges"
                        >

                        <div class="relative min-w-0 flex-1">

                            <label for="badge-search" class="sr-only">

                                Rechercher un badge

                            </label>

                            <svg
                                class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-slate-400"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >

                                <circle cx="11" cy="11" r="7"/>
                                <path d="m16 16 4 4"/>

                            </svg>

                            <input
                                id="badge-search"
                                name="search"
                                type="search"
                                value="{{ $filters['search'] }}"
                                placeholder="Rechercher (nom, email, fonction…)"
                                class="{{ $inputClass }} w-full pl-11"
                            >

                        </div>

                        <label for="exhibitor_id" class="sr-only">

                            Exposant

                        </label>

                        <select
                            id="exhibitor_id"
                            name="exhibitor_id"
                            class="{{ $inputClass }} lg:w-56"
                        >

                            <option value="">

                                Tous les exposants

                            </option>

                            @foreach ($exhibitorOptions as $option)

                                <option
                                    value="{{ $option->id }}"
                                    @selected((int) $filters['exhibitor_id'] === $option->id)
                                >

                                    {{ $option->company_name }}

                                </option>

                            @endforeach

                        </select>

                        <button
                            type="submit"
                            class="h-11 rounded-xl bg-[#2563EB] px-5 text-sm font-semibold text-white hover:bg-blue-700"
                        >

                            Filtrer

                        </button>

                        @if ($filters['search'] !== '' || $filters['exhibitor_id'])

                            <a
                                href="{{ route('commercial.badges.index', ['tab' => 'badges']) }}"
                                class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                            >

                                Réinitialiser

                            </a>

                        @endif

                    </form>

                </div>


                {{-- BADGES TABLE --}}

                <div class="overflow-x-auto">

                    <table class="w-full min-w-[850px] text-left text-sm">

                        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold text-slate-600">

                            <tr>

                                <th scope="col" class="px-5 py-4">

                                    Titulaire du badge

                                </th>

                                <th scope="col" class="px-4 py-4">

                                    Entreprise

                                </th>

                                <th scope="col" class="px-4 py-4">

                                    Stand

                                </th>

                                <th scope="col" class="px-4 py-4">

                                    Statut

                                </th>

                                <th scope="col" class="px-4 py-4">

                                    Impression

                                </th>

                                <th scope="col" class="px-5 py-4 text-right">

                                    Actions

                                </th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @forelse ($badges as $badge)

                                @php
                                    $company = $badge->exhibitor;

                                    $companyLogo = $company?->logo
                                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($company->logo)
                                        : null;

                                    $companyInitials = mb_strtoupper(
                                        mb_substr($company?->company_name ?? 'EX', 0, 2)
                                    );

                                    $isActive = $badge->status === 'active';
                                @endphp

                                <tr class="transition hover:bg-slate-50/80">


                                    {{-- BADGE HOLDER --}}

                                    <td class="px-5 py-4">

                                        <div class="flex items-center gap-3">

                                            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">

                                                <x-admin.icon
                                                    name="badge"
                                                    class="size-6"
                                                />

                                            </span>

                                            <div class="min-w-0">

                                                <p class="font-bold text-[#0F2347]">

                                                    {{ $badge->full_name }}

                                                </p>

                                                <p class="mt-1 text-xs text-slate-500">

                                                    {{ $badge->function ?: '—' }}

                                                </p>

                                                <p class="mt-1 text-[10px] font-semibold text-blue-600">

                                                    {{ $badge->reference }}

                                                </p>

                                            </div>

                                        </div>

                                    </td>


                                    {{-- COMPANY LOGO --}}

                                    <td class="px-4 py-4">

                                        <div class="flex items-center gap-3">

                                            <div class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white">

                                                @if ($companyLogo)

                                                    <img
                                                        src="{{ $companyLogo }}"
                                                        alt="Logo {{ $company->company_name }}"
                                                        class="h-full w-full object-contain p-1"
                                                        loading="lazy"
                                                        data-badge-avatar
                                                    >

                                                    <span
                                                        class="hidden text-xs font-bold text-blue-700"
                                                        data-avatar-fallback
                                                    >

                                                        {{ $companyInitials }}

                                                    </span>

                                                @else

                                                    <span class="text-xs font-bold text-blue-700">

                                                        {{ $companyInitials }}

                                                    </span>

                                                @endif

                                            </div>

                                            @if ($company)

                                                <a
                                                    href="{{ route('commercial.exhibitors.show', $company) }}"
                                                    class="max-w-[180px] truncate font-semibold text-[#0F2347] hover:text-blue-600"
                                                    title="{{ $company->company_name }}"
                                                >

                                                    {{ $company->company_name }}

                                                </a>

                                            @else

                                                <span class="text-slate-400">

                                                    —

                                                </span>

                                            @endif

                                        </div>

                                    </td>


                                    {{-- STAND --}}

                                    <td class="px-4 py-4">

                                        <span class="whitespace-nowrap rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">

                                            {{ $company?->stand_number ?: '—' }}

                                        </span>

                                    </td>


                                    {{-- BADGE STATUS --}}

                                    <td class="px-4 py-4">

                                        <span @class([
                                            'inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold',
                                            'bg-emerald-50 text-emerald-700' => $isActive,
                                            'bg-slate-100 text-slate-600' => ! $isActive,
                                        ])>

                                            <span @class([
                                                'size-1.5 rounded-full',
                                                'bg-emerald-500' => $isActive,
                                                'bg-slate-400' => ! $isActive,
                                            ])></span>

                                            {{ $isActive ? 'Actif' : 'Inactif' }}

                                        </span>

                                    </td>


                                    {{-- PRINT STATUS --}}

                                    <td class="px-4 py-4">

                                        @if ($badge->last_printed_at)

                                            <div>

                                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">

                                                    <x-admin.icon
                                                        name="clipboard-check"
                                                        class="size-4"
                                                    />

                                                    Imprimé

                                                </span>

                                                <p class="mt-1 whitespace-nowrap text-[11px] text-slate-500">

                                                    Imprimé le {{ \Illuminate\Support\Carbon::parse($badge->last_printed_at)->format('d/m/Y') }}

                                                </p>

                                            </div>

                                        @else

                                            <span class="rounded-lg bg-amber-50 px-2.5 py-1.5 text-xs font-semibold text-amber-700">

                                                Jamais imprimé

                                            </span>

                                        @endif

                                    </td>


                                    {{-- PRINT ACTION --}}

                                    <td class="px-5 py-4 text-right">

                                        @if ($isActive)

                                            <form
                                                method="POST"
                                                action="{{ route('commercial.badges.print', $badge) }}"
                                                target="_blank"
                                            >

                                                @csrf

                                                <button
                                                    type="submit"
                                                    title="Imprimer le badge avec son QR code"
                                                    class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-600 hover:text-white"
                                                >

                                                    <svg
                                                        class="size-4"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        aria-hidden="true"
                                                    >

                                                        <path d="M6 9V3h12v6"/>
                                                        <rect x="6" y="14" width="12" height="7" rx="1"/>
                                                        <path d="M6 17H3a1 1 0 0 1-1-1v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a1 1 0 0 1-1 1h-3"/>
                                                        <path d="M17 12h1"/>

                                                    </svg>

                                                    Imprimer

                                                </button>

                                            </form>

                                        @else

                                            <span class="text-xs text-slate-400">

                                                Non disponible

                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6" class="p-12">

                                        <x-admin.empty-state
                                            icon="badge"
                                            title="Aucun badge trouvé"
                                            description="Les badges enregistrés par vos exposants apparaîtront ici."
                                        />

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- BADGES PAGINATION --}}

                <div class="space-y-4 border-t border-slate-100 p-5">

                    @if ($badges->total() > 0)

                        <p class="text-xs text-slate-500">

                            Affichage de {{ $badges->firstItem() }}

                            à {{ $badges->lastItem() }}

                            sur {{ $badges->total() }} badges.

                        </p>

                    @endif

                    {{ $badges->links() }}

                </div>


            {{-- ===================================================== --}}
            {{-- TAB 3 : HISTORIQUE --}}
            {{-- ===================================================== --}}

            @else

                <div class="border-b border-slate-100 p-5">

                    <h3 class="text-sm font-bold text-[#0F2347]">

                        Historique des activités

                    </h3>

                    <p class="mt-1 text-xs text-slate-500">

                        Retrouvez toutes les opérations effectuées sur les badges.

                    </p>

                </div>

                <div class="divide-y divide-slate-100">

                    @forelse ($activities as $activity)

                        @php
                            $aMeta = $activityMeta[$activity->action] ?? [
                                'label' => ucfirst($activity->action),
                                'icon' => 'badge',
                                'color' => 'bg-blue-50 text-blue-600',
                            ];

                            $activityCompany = $activity->badge?->exhibitor;

                            $activityLogo = $activityCompany?->logo
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($activityCompany->logo)
                                : null;
                        @endphp

                        <article class="flex flex-wrap items-center gap-4 px-5 py-5 transition hover:bg-slate-50 sm:flex-nowrap">


                            {{-- ACTIVITY COMPANY LOGO --}}

                            <div class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white">

                                @if ($activityLogo)

                                    <img
                                        src="{{ $activityLogo }}"
                                        alt="{{ $activityCompany->company_name }}"
                                        class="h-full w-full object-contain p-1.5"
                                        loading="lazy"
                                        data-badge-avatar
                                    >

                                    <span
                                        class="hidden text-sm font-bold text-blue-600"
                                        data-avatar-fallback
                                    >

                                        {{ mb_strtoupper(mb_substr($activityCompany->company_name, 0, 2)) }}

                                    </span>

                                @elseif ($activityCompany)

                                    <span class="text-sm font-bold text-blue-600">

                                        {{ mb_strtoupper(mb_substr($activityCompany->company_name, 0, 2)) }}

                                    </span>

                                @else

                                    <x-admin.icon
                                        :name="$aMeta['icon']"
                                        class="size-6 text-blue-600"
                                    />

                                @endif

                            </div>


                            {{-- ACTIVITY DETAILS --}}

                            <div class="min-w-0 flex-1">

                                <div class="flex flex-wrap items-center gap-2">

                                    <span class="rounded-lg px-2.5 py-1 text-xs font-semibold {{ $aMeta['color'] }}">

                                        {{ $aMeta['label'] }}

                                    </span>

                                    <p class="text-sm font-bold text-[#0F2347]">

                                        {{ $activity->badge?->full_name ?? '—' }}

                                    </p>

                                </div>

                                <p class="mt-2 text-xs text-slate-500">

                                    {{ $activityCompany?->company_name ?? '—' }}

                                    · par {{ $activity->user?->name ?? 'Système' }}

                                </p>

                            </div>


                            {{-- ACTIVITY DATE --}}

                            <div class="shrink-0 text-right">

                                <time
                                    class="text-xs font-semibold text-slate-600"
                                    datetime="{{ $activity->created_at?->toIso8601String() }}"
                                >

                                    {{ $activity->created_at?->format('d/m/Y') }}

                                </time>

                                <p class="mt-1 text-[11px] text-slate-400">

                                    {{ $activity->created_at?->format('H:i') }}

                                </p>

                            </div>

                        </article>

                    @empty

                        <div class="p-10">

                            <x-admin.empty-state
                                icon="calendar"
                                title="Aucune activité"
                                description="Les impressions et modifications de badges apparaîtront ici."
                            />

                        </div>

                    @endforelse

                </div>


                {{-- HISTORY PAGINATION --}}

                <div class="border-t border-slate-100 p-5">

                    {{ $activities->links() }}

                </div>

            @endif

        </section>


        {{-- ===================================================== --}}
        {{-- BOTTOM DASHBOARD --}}
        {{-- ===================================================== --}}

        <section class="grid min-w-0 items-start gap-6 xl:grid-cols-3">


            {{-- QUOTA DISTRIBUTION --}}

            <article class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="flex items-center gap-3 border-b border-slate-100 p-5">

                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-violet-50 text-violet-600">

                        <x-admin.icon
                            name="chart"
                            class="size-6"
                        />

                    </span>

                    <div>

                        <h2 class="text-base font-bold text-[#0F2347]">

                            Répartition des quotas

                        </h2>

                        <p class="mt-1 text-xs text-slate-500">

                            Utilisation globale des badges.

                        </p>

                    </div>

                </div>

                <div class="p-5">


                    {{-- DONUT CHART --}}

                    <div class="flex justify-center py-4">

                        <div
                            class="grid size-40 shrink-0 place-items-center rounded-full"
                            style="background: conic-gradient(#2563EB 0% {{ $usedPercentage }}%, #E2E8F0 {{ $usedPercentage }}% 100%)"
                        >

                            <div class="grid size-32 place-content-center rounded-full bg-white text-center">

                                <p class="text-3xl font-extrabold text-[#0F2347]">

                                    {{ $fmt($totalAuthorized) }}

                                </p>

                                <p class="mt-1 text-xs text-slate-500">

                                    Badges autorisés

                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- LEGEND --}}

                    <dl class="mt-4 space-y-3">

                        <div class="flex items-center justify-between gap-3 rounded-xl bg-blue-50 p-3">

                            <dt class="flex items-center gap-2 text-xs font-semibold text-blue-700">

                                <span class="size-2.5 rounded-full bg-blue-600"></span>

                                Utilisés

                            </dt>

                            <dd class="font-bold text-blue-800">

                                {{ $fmt($totalBadges) }}

                            </dd>

                        </div>

                        <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-100 p-3">

                            <dt class="flex items-center gap-2 text-xs font-semibold text-slate-600">

                                <span class="size-2.5 rounded-full bg-slate-400"></span>

                                Restants

                            </dt>

                            <dd class="font-bold text-slate-700">

                                {{ $fmt($remainingBadges) }}

                            </dd>

                        </div>

                    </dl>

                    <div class="mt-5">

                        <div class="mb-2 flex justify-between text-xs">

                            <span class="font-medium text-slate-500">

                                Quota utilisé

                            </span>

                            <span class="font-bold text-blue-600">

                                {{ $pct($quotaUsage) }}%

                            </span>

                        </div>

                        <div
                            class="h-2.5 overflow-hidden rounded-full bg-slate-100"
                            role="progressbar"
                            aria-label="Utilisation globale des quotas"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="{{ round($usedPercentage) }}"
                        >

                            <div
                                class="h-full rounded-full bg-blue-600"
                                style="width: {{ $usedPercentage }}%"
                            ></div>

                        </div>

                    </div>

                </div>

            </article>


            {{-- QUICK ACTIONS --}}

            <article class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="flex items-center gap-3 border-b border-slate-100 p-5">

                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">

                        <x-admin.icon
                            name="zap"
                            class="size-6"
                        />

                    </span>

                    <div>

                        <h2 class="text-base font-bold text-[#0F2347]">

                            Actions rapides

                        </h2>

                        <p class="mt-1 text-xs text-slate-500">

                            Accédez aux fonctionnalités principales.

                        </p>

                    </div>

                </div>

                <nav
                    class="space-y-3 p-5"
                    aria-label="Actions rapides badges"
                >

                    {{-- PENDING EXHIBITORS --}}

                    <a
                        href="{{ route('commercial.badges.index', [
                            'tab' => 'exhibitors',
                            'status' => 'pending',
                        ]) }}"
                        class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4 transition hover:border-blue-200 hover:bg-blue-50"
                    >

                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-600">

                            <x-admin.icon
                                name="users"
                                class="size-5"
                            />

                        </span>

                        <span class="min-w-0 flex-1">

                            <span class="block text-sm font-semibold text-[#0F2347]">

                                Exposants sans badge

                            </span>

                            <span class="mt-1 block text-[11px] text-slate-500">

                                Consultez les quotas en attente.

                            </span>

                        </span>

                        <span class="text-slate-400">→</span>

                    </a>


                    {{-- PRINT BADGES --}}

                    <a
                        href="{{ route('commercial.badges.index', ['tab' => 'badges']) }}"
                        class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4 transition hover:border-blue-200 hover:bg-blue-50"
                    >

                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600">

                            <x-admin.icon
                                name="badge"
                                class="size-5"
                            />

                        </span>

                        <span class="min-w-0 flex-1">

                            <span class="block text-sm font-semibold text-[#0F2347]">

                                Imprimer des badges

                            </span>

                            <span class="mt-1 block text-[11px] text-slate-500">

                                Accédez aux badges enregistrés.

                            </span>

                        </span>

                        <span class="text-slate-400">→</span>

                    </a>


                    {{-- EXPORT CSV --}}

                    <a
                        href="{{ route('commercial.badges.export') }}"
                        class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4 transition hover:border-blue-200 hover:bg-blue-50"
                    >

                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-violet-50 text-violet-600">

                            <svg
                                class="size-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >

                                <path d="M12 3v12m-5-5 5 5 5-5"/>
                                <path d="M4 16v5h16v-5"/>

                            </svg>

                        </span>

                        <span class="min-w-0 flex-1">

                            <span class="block text-sm font-semibold text-[#0F2347]">

                                Exporter tous les badges

                            </span>

                            <span class="mt-1 block text-[11px] text-slate-500">

                                Télécharger le fichier CSV.

                            </span>

                        </span>

                        <span class="text-slate-400">→</span>

                    </a>


                    {{-- HISTORY --}}

                    <a
                        href="{{ route('commercial.badges.index', ['tab' => 'history']) }}"
                        class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4 transition hover:border-blue-200 hover:bg-blue-50"
                    >

                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">

                            <x-admin.icon
                                name="calendar"
                                class="size-5"
                            />

                        </span>

                        <span class="min-w-0 flex-1">

                            <span class="block text-sm font-semibold text-[#0F2347]">

                                Consulter l'historique

                            </span>

                            <span class="mt-1 block text-[11px] text-slate-500">

                                Suivez les dernières opérations.

                            </span>

                        </span>

                        <span class="text-slate-400">→</span>

                    </a>

                </nav>

            </article>


            {{-- RECENT ACTIVITIES --}}

            <article class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="flex items-center gap-3 border-b border-slate-100 p-5">

                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600">

                        <x-admin.icon
                            name="calendar"
                            class="size-6"
                        />

                    </span>

                    <div>

                        <h2 class="text-base font-bold text-[#0F2347]">

                            Dernières activités

                        </h2>

                        <p class="mt-1 text-xs text-slate-500">

                            Suivi récent des badges.

                        </p>

                    </div>

                </div>

                <ul class="divide-y divide-slate-100 px-5">

                    @forelse ($recentActivities as $activity)

                        @php
                            $recentMeta = $activityMeta[$activity->action] ?? [
                                'label' => ucfirst($activity->action),
                                'icon' => 'badge',
                                'color' => 'bg-blue-50 text-blue-600',
                            ];

                            $recentCompany = $activity->badge?->exhibitor;

                            $recentLogo = $recentCompany?->logo
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($recentCompany->logo)
                                : null;
                        @endphp

                        <li class="flex items-center gap-3 py-4">

                            <div class="grid size-10 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white">

                                @if ($recentLogo)

                                    <img
                                        src="{{ $recentLogo }}"
                                        alt="{{ $recentCompany->company_name }}"
                                        class="h-full w-full object-contain p-1"
                                        loading="lazy"
                                        data-badge-avatar
                                    >

                                    <span
                                        class="hidden text-xs font-bold text-blue-600"
                                        data-avatar-fallback
                                    >

                                        {{ mb_strtoupper(mb_substr($recentCompany->company_name, 0, 2)) }}

                                    </span>

                                @elseif ($recentCompany)

                                    <span class="text-xs font-bold text-blue-600">

                                        {{ mb_strtoupper(mb_substr($recentCompany->company_name, 0, 2)) }}

                                    </span>

                                @else

                                    <x-admin.icon
                                        :name="$recentMeta['icon']"
                                        class="size-5 text-blue-600"
                                    />

                                @endif

                            </div>

                            <div class="min-w-0 flex-1">

                                <p class="truncate text-xs font-bold text-[#0F2347]">

                                    {{ $recentMeta['label'] }}

                                </p>

                                <p class="mt-1 truncate text-xs text-slate-500">

                                    {{ $recentCompany?->company_name ?? '—' }}

                                </p>

                            </div>

                            <span class="shrink-0 text-[10px] text-slate-400">

                                {{ $activity->created_at?->format('d/m H:i') }}

                            </span>

                        </li>

                    @empty

                        <li class="py-8 text-center">

                            <x-admin.icon
                                name="calendar"
                                class="mx-auto size-8 text-slate-300"
                            />

                            <p class="mt-3 text-sm text-slate-500">

                                Aucune activité récente.

                            </p>

                        </li>

                    @endforelse

                </ul>

                <div class="border-t border-slate-100 p-5">

                    <a
                        href="{{ route('commercial.badges.index', ['tab' => 'history']) }}"
                        class="inline-flex items-center gap-2 text-xs font-semibold text-blue-600 hover:underline"
                    >

                        Voir tout l'historique →

                    </a>

                </div>

            </article>

        </section>


        {{-- FOOTER --}}

        <footer class="border-t border-slate-200 py-4 text-center text-xs text-slate-400">

            © 2027 Refrigair Expo — Espace Commercial

        </footer>

    </div>


    {{-- IMAGE FALLBACK --}}

    <script>

        (() => {

            document.querySelectorAll('[data-badge-avatar]')
                .forEach(image => {

                    function showFallback() {

                        image.classList.add('hidden');

                        const fallback = image.parentElement
                            .querySelector('[data-avatar-fallback]');

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

        })();

    </script>

</x-commercial.layout>
