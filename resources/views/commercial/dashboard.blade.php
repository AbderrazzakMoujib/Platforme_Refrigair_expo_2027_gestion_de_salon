
<x-commercial.layout
    :commercial="$commercial"
    :commercial-initials="$commercialInitials"
>
    @php
        $cards = [
            [
                'label' => 'Exposants suivis',
                'value' => $exhibitorsCount,
                'icon' => 'users',
                'background' => '#EFF6FF',
                'icon_bg' => '#DBEAFE',
                'color' => '#2563EB',
                'description' => '+' . $newExhibitorsThisMonth . ' ce mois',
                'description_color' => '#059669',
            ],
            [
                'label' => 'Demandes actives',
                'value' => $activeTickets,
                'icon' => 'inbox',
                'background' => '#FFF7ED',
                'icon_bg' => '#FFEDD5',
                'color' => '#D97706',
                'description' => '+' . $newTicketsToday . ' aujourd’hui',
                'description_color' => '#D97706',
            ],
            [
                'label' => 'Demandes résolues',
                'value' => $resolvedTickets,
                'icon' => 'badge',
                'background' => '#ECFDF5',
                'icon_bg' => '#D1FAE5',
                'color' => '#059669',
                'description' => '+' . $resolvedTicketsThisMonth . ' ce mois',
                'description_color' => '#059669',
            ],
            [
                'label' => 'Notifications',
                'value' => $unreadNotifications,
                'icon' => 'bell',
                'background' => '#F5F3FF',
                'icon_bg' => '#EDE9FE',
                'color' => '#7C3AED',
                'description' => 'Non lues',
                'description_color' => '#7C3AED',
            ],
        ];

        $quickActions = [
            [
                'label' => 'Gérer mes exposants',
                'icon' => 'users',
                'route' => 'commercial.exhibitors.index',
                'color' => '#2563EB',
            ],
            [
                'label' => 'Voir les demandes',
                'icon' => 'inbox',
                'route' => 'commercial.support.index',
                'color' => '#2563EB',
            ],
            [
                'label' => 'Mon profil',
                'icon' => 'settings',
                'route' => 'commercial.profile.edit',
                'color' => '#7C3AED',
            ],
            [
                'label' => 'Notifications',
                'icon' => 'bell',
                'route' => 'commercial.notifications.index',
                'color' => '#7C3AED',
            ],
        ];

        // Données du graphique SVG.
        $chartMax = max(4, (int) ceil(max($chartValues) / 4) * 4);

        $chartPoints = [];

        foreach ($chartValues as $index => $value) {
            $x = 48 + (722 * $index / max(1, count($chartValues) - 1));
            $y = 210 - ($value / $chartMax * 180);

            $chartPoints[] = [
                'x' => round($x, 2),
                'y' => round($y, 2),
                'value' => $value,
                'label' => $chartLabels[$index],
            ];
        }

        $linePoints = collect($chartPoints)
            ->map(fn ($point) => $point['x'] . ',' . $point['y'])
            ->implode(' ');

        $areaPoints = '48,210 ' . $linePoints . ' 770,210';

        $labelStep = max(1, (int) ceil(count($chartLabels) / 10));

        // Répartition des demandes.
        $gradientParts = [];
        $offset = 0;

        foreach ($ticketDistribution as $item) {
            $percentage = $totalTickets > 0
                ? ($item['count'] / $totalTickets) * 100
                : 0;

            if ($percentage > 0) {
                $gradientParts[] = $item['color']
                    . ' ' . $offset . '% '
                    . ($offset + $percentage) . '%';
            }

            $offset += $percentage;
        }

        $donutBackground = $totalTickets > 0
            ? 'conic-gradient(' . implode(', ', $gradientParts) . ')'
            : '#E2E8F0';
    @endphp

    <div class="space-y-6">

        {{-- HEADER --}}
        <section class="flex flex-wrap items-center justify-between gap-4">

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">
                    Espace commercial
                </p>

                <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                    Bonjour, {{ $commercial->name }}
                    <span aria-hidden="true">👋</span>
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Bienvenue dans votre espace Commercial Refrigair Expo 2027.
                </p>
            </div>

            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs font-semibold text-slate-600">

                <svg class="size-4 text-blue-600"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     aria-hidden="true">
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path d="M16 3v4M8 3v4M3 10h18"/>
                </svg>

                {{ now()->format('d/m/Y') }}

            </div>

        </section>


        {{-- STATISTICS --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-4">

            @foreach ($cards as $card)

                <article
                    class="min-w-0 rounded-2xl border border-slate-200/80 p-5 shadow-sm"
                    style="background: linear-gradient(145deg, #FFFFFF, {{ $card['background'] }})"
                >

                    <div class="flex items-start gap-4">

                        <span
                            class="grid size-14 shrink-0 place-items-center rounded-full"
                            style="background: {{ $card['icon_bg'] }}; color: {{ $card['color'] }}"
                        >

                            <x-admin.icon
                                :name="$card['icon']"
                                class="size-7"
                            />

                        </span>

                        <div class="min-w-0">

                            <p class="text-sm font-semibold text-[#0F2347]">
                                {{ $card['label'] }}
                            </p>

                            <p class="mt-2 text-3xl font-extrabold tracking-tight text-[#0F2347]">{{ number_format($card['value'], 0, ',', ' ') }}</p>

                            <p
                                class="mt-2 text-xs font-semibold"
                                style="color: {{ $card['description_color'] }}"
                            >
                                {{ $card['description'] }}
                            </p>

                        </div>

                    </div>

                </article>

            @endforeach

        </section>


        {{-- MAIN DASHBOARD GRID --}}
        <section class="grid min-w-0 gap-6 xl:grid-cols-3">

            {{-- RECENT SUPPORT TICKETS --}}
            <article class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">

                    <div class="flex items-center gap-3">

                        <x-admin.icon
                            name="inbox"
                            class="size-6 shrink-0 text-blue-600"
                        />

                        <div>
                            <h2 class="text-base font-bold text-[#0F2347]">
                                Demandes d'assistance récentes
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Dernières demandes de vos exposants.
                            </p>
                        </div>

                    </div>

                    <a
                        href="{{ route('commercial.support.index') }}"
                        class="text-xs font-bold text-blue-600 hover:underline"
                    >
                        Voir tout →
                    </a>

                </div>

                <div class="overflow-x-auto">

                    <table class="w-full min-w-[580px] text-left text-sm">

                        <thead class="bg-slate-50 text-xs font-semibold text-slate-600">

                            <tr>
                                <th scope="col" class="px-5 py-4">#</th>
                                <th scope="col" class="px-4 py-4">Exposant</th>
                                <th scope="col" class="px-4 py-4">Sujet</th>
                                <th scope="col" class="px-4 py-4">Statut</th>
                                <th scope="col" class="px-4 py-4">Date</th>
                            </tr>

                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            @forelse ($recentTickets as $ticket)

                                @php
                                    $statusClass = match ($ticket->status) {
                                        'resolved' => 'bg-emerald-100 text-emerald-700',
                                        'in_progress' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-violet-100 text-violet-700',
                                    };
                                @endphp

                                <tr class="transition hover:bg-slate-50">

                                    <td class="px-5 py-4 font-semibold text-slate-500">
                                        #{{ $ticket->id }}
                                    </td>

                                    <td class="px-4 py-4">

                                        <div class="flex items-center gap-2">

                                            <span class="grid size-8 shrink-0 place-items-center rounded-full bg-blue-50 text-blue-600">

                                                <x-admin.icon
                                                    name="users"
                                                    class="size-4"
                                                />

                                            </span>

                                            <span class="font-semibold text-[#0F2347]">
                                                {{ $ticket->exhibitor?->company_name ?? 'Exposant' }}
                                            </span>

                                        </div>

                                    </td>

                                    <td class="px-4 py-4">

                                        <a
                                            href="{{ route('commercial.support.show', $ticket) }}"
                                            class="font-medium text-slate-700 hover:text-blue-600"
                                        >
                                            {{ \Illuminate\Support\Str::limit($ticket->subject, 42) }}
                                        </a>

                                    </td>

                                    <td class="px-4 py-4">

                                        <span class="inline-flex whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-semibold {{ $statusClass }}">
                                            {{ $ticket->status_label }}
                                        </span>

                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-xs text-slate-500">

                                        {{ $ticket->created_at?->format('d/m/Y') }}

                                        <span class="mt-1 block">
                                            {{ $ticket->created_at?->format('H:i') }}
                                        </span>

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="5" class="p-10 text-center">

                                        <span class="mx-auto grid size-14 place-items-center rounded-full bg-blue-50 text-blue-600">

                                            <x-admin.icon
                                                name="inbox"
                                                class="size-7"
                                            />

                                        </span>

                                        <p class="mt-4 text-sm font-bold text-slate-800">
                                            Aucune demande récente
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            Les demandes de vos exposants apparaîtront ici.
                                        </p>

                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </article>


            {{-- QUICK ACTIONS --}}
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-center gap-3">

                    <svg class="size-6 text-blue-600"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="m13 2-9 12h7l-1 8 10-12h-7l1-8Z"/>
                    </svg>

                    <h2 class="text-base font-bold text-[#0F2347]">
                        Accès rapides
                    </h2>

                </div>

                <div class="mt-5 space-y-3">

                    @foreach ($quickActions as $action)

                        <a
                            href="{{ route($action['route']) }}"
                            class="group flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-4 transition hover:border-blue-200 hover:bg-blue-50"
                        >

                            <span style="color: {{ $action['color'] }}">

                                <x-admin.icon
                                    :name="$action['icon']"
                                    class="size-5"
                                />

                            </span>

                            <span class="min-w-0 flex-1 text-sm font-semibold text-[#0F2347]">
                                {{ $action['label'] }}
                            </span>

                            @if (
                                $action['route'] === 'commercial.notifications.index'
                                && $unreadNotifications > 0
                            )

                                <span class="grid min-h-6 min-w-6 place-items-center rounded-full bg-red-500 px-1.5 text-xs font-bold text-white">
                                    {{ $unreadNotifications }}
                                </span>

                            @endif

                            <svg class="size-4 shrink-0 text-slate-400 group-hover:text-blue-600"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2"
                                 stroke-linecap="round"
                                 stroke-linejoin="round"
                                 aria-hidden="true">
                                <path d="M5 12h14m-6-6 6 6-6 6"/>
                            </svg>

                        </a>

                    @endforeach

                </div>

            </article>


            {{-- ACTIVITY CHART --}}
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">

                <div class="flex flex-wrap items-center justify-between gap-4">

                    <div class="flex items-center gap-3">

                        <svg class="size-6 text-blue-600"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round"
                             aria-hidden="true">
                            <path d="M3 3v18h18M8 16v-4m5 4V6m5 10v-7"/>
                        </svg>

                        <div>
                            <h2 class="text-base font-bold text-[#0F2347]">
                                Évolution des demandes
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Nombre de tickets créés par jour.
                            </p>
                        </div>

                    </div>

                    <form method="GET" action="{{ route('commercial.index') }}">

                        <label for="chart-period" class="sr-only">
                            Période du graphique
                        </label>

                        <select
                            id="chart-period"
                            name="period"
                            onchange="this.form.submit()"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-medium text-slate-700"
                        >
                            @foreach ([7, 15, 30] as $days)

                                <option
                                    value="{{ $days }}"
                                    @selected($period === $days)
                                >
                                    {{ $days }} derniers jours
                                </option>

                            @endforeach
                        </select>

                        <noscript>
                            <button type="submit" class="text-sm text-blue-600">
                                Appliquer
                            </button>
                        </noscript>

                    </form>

                </div>

                <div class="mt-6 overflow-x-auto">

                    <svg
                        viewBox="0 0 800 250"
                        class="h-auto w-full min-w-[450px]"
                        role="img"
                        aria-label="Évolution quotidienne des demandes d'assistance"
                    >

                        <defs>

                            <linearGradient id="commercial-chart-fill"
                                            x1="0" y1="0"
                                            x2="0" y2="1">

                                <stop offset="0%"
                                      stop-color="#3B82F6"
                                      stop-opacity=".28"/>

                                <stop offset="100%"
                                      stop-color="#3B82F6"
                                      stop-opacity=".02"/>

                            </linearGradient>

                        </defs>

                        {{-- Grid --}}
                        @for ($i = 0; $i <= 4; $i++)

                            @php
                                $y = 30 + ($i * 45);
                                $tick = $chartMax - ($i * $chartMax / 4);
                            @endphp

                            <line
                                x1="48" y1="{{ $y }}"
                                x2="770" y2="{{ $y }}"
                                stroke="#E2E8F0"
                                stroke-width="1"
                            />

                            <text
                                x="32"
                                y="{{ $y + 4 }}"
                                fill="#64748B"
                                font-size="11"
                                text-anchor="end"
                            >
                                {{ $tick }}
                            </text>

                        @endfor

                        {{-- Area --}}
                        <polygon
                            points="{{ $areaPoints }}"
                            fill="url(#commercial-chart-fill)"
                        />

                        {{-- Line --}}
                        <polyline
                            points="{{ $linePoints }}"
                            fill="none"
                            stroke="#2563EB"
                            stroke-width="3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />

                        {{-- Data points --}}
                        @foreach ($chartPoints as $index => $point)

                            <circle
                                cx="{{ $point['x'] }}"
                                cy="{{ $point['y'] }}"
                                r="3"
                                fill="#FFFFFF"
                                stroke="#2563EB"
                                stroke-width="1.5"
                            >
                                <title>
                                    {{ $point['label'] }} :
                                    {{ $point['value'] }} demande(s)
                                </title>
                            </circle>

                            @if ($index % $labelStep === 0 || $loop->last)

                                <text
                                    x="{{ $point['x'] }}"
                                    y="236"
                                    fill="#64748B"
                                    font-size="10"
                                    text-anchor="middle"
                                >
                                    {{ $point['label'] }}
                                </text>

                            @endif

                        @endforeach

                    </svg>

                </div>

                <details class="mt-3 text-xs text-slate-500">
                    <summary class="cursor-pointer">
                        Voir les données du graphique
                    </summary>

                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @foreach ($chartPoints as $point)
                            <p>
                                {{ $point['label'] }} :
                                {{ $point['value'] }}
                            </p>
                        @endforeach
                    </div>
                </details>

            </article>


            {{-- DONUT CHART --}}
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-center gap-3">

                    <svg class="size-6 text-violet-600"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         aria-hidden="true">
                        <path d="M12 2v10h10A10 10 0 0 0 12 2Z"/>
                        <path d="M9 3a10 10 0 1 0 12 12"/>
                    </svg>

                    <h2 class="text-base font-bold text-[#0F2347]">
                        Répartition des demandes
                    </h2>

                </div>

                <p class="mt-2 text-xs text-slate-500">
                    Tous vos tickets d'assistance.
                </p>

                <div class="mt-6 flex flex-col items-center gap-6">

                    <div
                        class="relative grid size-40 shrink-0 place-items-center rounded-full"
                        style="background: {{ $donutBackground }}"
                        aria-hidden="true"
                    >

                        <div class="grid size-32 place-content-center rounded-full bg-white text-center">

                            <span class="text-3xl font-extrabold text-[#0F2347]">
                                {{ $totalTickets }}
                            </span>

                            <span class="mt-1 text-xs text-slate-500">
                                Demandes
                            </span>

                        </div>

                    </div>

                    <div class="w-full space-y-4">

                        @foreach ($ticketDistribution as $item)

                            @php
                                $percentage = $totalTickets > 0
                                    ? round(($item['count'] / $totalTickets) * 100)
                                    : 0;
                            @endphp

                            <div class="flex items-center justify-between gap-3 text-xs">

                                <div class="flex items-center gap-2">

                                    <span
                                        class="size-2.5 shrink-0 rounded-full"
                                        style="background: {{ $item['color'] }}"
                                    ></span>

                                    <span class="font-medium text-slate-700">
                                        {{ $item['label'] }}
                                        ({{ $item['count'] }})
                                    </span>

                                </div>

                                <span class="font-bold text-[#0F2347]">
                                    {{ $percentage }}%
                                </span>

                            </div>

                        @endforeach

                    </div>

                </div>

            </article>


            {{-- BADGE PROGRESS --}}
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">

                <div class="flex items-center justify-between gap-3">

                    <div class="flex items-center gap-3">

                        <x-admin.icon
                            name="badge"
                            class="size-6 text-blue-600"
                        />

                        <h2 class="text-base font-bold text-[#0F2347]">
                            Progression générale des badges
                        </h2>

                    </div>

                    <span class="rounded-lg bg-blue-50 px-3 py-2 text-sm font-bold text-blue-700">
                        {{ $badgeProgress }}%
                    </span>

                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">

                    <div class="rounded-xl bg-blue-50 p-4">
                        <p class="text-xs font-medium text-blue-700">
                            Badges autorisés
                        </p>
                        <p class="mt-2 text-2xl font-bold text-blue-800">
                            {{ $totalAuthorizedBadges }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-emerald-50 p-4">
                        <p class="text-xs font-medium text-emerald-700">
                            Enregistrés
                        </p>
                        <p class="mt-2 text-2xl font-bold text-emerald-800">
                            {{ $totalRegisteredBadges }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-amber-50 p-4">
                        <p class="text-xs font-medium text-amber-700">
                            Restants
                        </p>
                        <p class="mt-2 text-2xl font-bold text-amber-800">
                            {{ $remainingBadges }}
                        </p>
                    </div>

                </div>

                <div
                    class="mt-6 h-3 overflow-hidden rounded-full bg-slate-100"
                    role="progressbar"
                    aria-label="Progression des badges"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="{{ $badgeProgress }}"
                >

                    <div
                        class="h-full rounded-full bg-blue-600 transition-all"
                        style="width: {{ $badgeProgress }}%"
                    ></div>

                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">

                    <div class="rounded-xl border border-emerald-100 p-3">
                        <p class="text-xs text-slate-500">
                            Exposants complets
                        </p>
                        <p class="mt-1 text-xl font-bold text-emerald-600">
                            {{ $completedExhibitors }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-amber-100 p-3">
                        <p class="text-xs text-slate-500">
                            Exposants partiels
                        </p>
                        <p class="mt-1 text-xl font-bold text-amber-600">
                            {{ $partialExhibitors }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-red-100 p-3">
                        <p class="text-xs text-slate-500">
                            Exposants en attente
                        </p>
                        <p class="mt-1 text-xl font-bold text-red-600">
                            {{ $pendingExhibitors }}
                        </p>
                    </div>

                </div>

                @if (\Illuminate\Support\Facades\Route::has('commercial.badges.index'))

                    <a
                        href="{{ route('commercial.badges.index') }}"
                        class="mt-5 inline-flex text-sm font-semibold text-blue-600 hover:underline"
                    >
                        Consulter les badges →
                    </a>

                @endif

            </article>


            {{-- RECENT EXHIBITORS --}}
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-center gap-3">

                    <x-admin.icon
                        name="users"
                        class="size-6 text-blue-600"
                    />

                    <h2 class="text-base font-bold text-[#0F2347]">
                        Mes derniers exposants
                    </h2>

                </div>

                <div class="mt-4 divide-y divide-slate-100">

                    @forelse ($recentExhibitors as $exhibitor)

                        <a
                            href="{{ route('commercial.exhibitors.show', $exhibitor) }}"
                            class="flex items-center gap-3 py-4 transition hover:bg-slate-50"
                        >

                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-sm font-bold text-blue-700">

                                {{ mb_strtoupper(mb_substr($exhibitor->company_name, 0, 2)) }}

                            </span>

                            <div class="min-w-0 flex-1">

                                <p class="truncate text-sm font-bold text-[#0F2347]">
                                    {{ $exhibitor->company_name }}
                                </p>

                                <p class="mt-1 truncate text-xs text-slate-500">
                                    Stand {{ $exhibitor->stand_number ?: '—' }}
                                </p>

                            </div>

                            <span class="shrink-0 rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-600">
                                {{ $exhibitor->badges_count }}/{{ $exhibitor->authorized_badges }}
                            </span>

                        </a>

                    @empty

                        <p class="py-8 text-center text-sm text-slate-500">
                            Aucun exposant disponible.
                        </p>

                    @endforelse

                </div>

                <a
                    href="{{ route('commercial.exhibitors.index') }}"
                    class="mt-3 inline-flex text-sm font-semibold text-blue-600 hover:underline"
                >
                    Voir tous mes exposants →
                </a>

            </article>

        </section>

        {{-- FOOTER --}}
        <footer class="border-t border-slate-200 py-4 text-center text-xs text-slate-400">
            © 2027 Refrigair Expo — Espace Commercial
        </footer>

    </div>

</x-commercial.layout>
