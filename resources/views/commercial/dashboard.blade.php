@php
    $fmt = fn ($value) => number_format((float) $value, 0, ',', ' ');
    $pct = fn ($value) => rtrim(rtrim(number_format((float) $value, 1, ',', ' '), '0'), ',');

    // Statuts badges des exposants
    $statusMeta = [
        'complete' => ['label' => 'Complet', 'pill' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'bar' => 'bg-emerald-500'],
        'partial' => ['label' => 'Partiel', 'pill' => 'bg-amber-50 text-amber-700 ring-amber-200', 'bar' => 'bg-amber-500'],
        'pending' => ['label' => 'En attente', 'pill' => 'bg-red-50 text-red-700 ring-red-200', 'bar' => 'bg-red-500'],
        'no_quota' => ['label' => 'Sans quota', 'pill' => 'bg-slate-100 text-slate-600 ring-slate-200', 'bar' => 'bg-slate-400'],
    ];

    $accessMeta = [
        'active' => ['label' => 'Accès actif', 'class' => 'text-emerald-700'],
        'disabled' => ['label' => 'Accès désactivé', 'class' => 'text-red-700'],
        'none' => ['label' => 'Sans accès', 'class' => 'text-slate-500'],
    ];

    $breakdown = [
        ['key' => 'complete', 'count' => $completedExhibitors, 'hint' => 'Quota de badges atteint'],
        ['key' => 'partial', 'count' => $partialExhibitors, 'hint' => 'Badges en cours de saisie'],
        ['key' => 'pending', 'count' => $pendingExhibitors, 'hint' => 'Aucun badge enregistré'],
        ['key' => 'no_quota', 'count' => $noQuotaExhibitors, 'hint' => 'Aucun badge autorisé'],
    ];

    // Statuts des demandes d'assistance (mêmes couleurs que la page Assistance)
    $ticketMeta = [
        'pending' => ['label' => 'En attente', 'pill' => 'bg-amber-100 text-amber-700', 'color' => '#F59E0B', 'dot' => 'bg-amber-500'],
        'in_progress' => ['label' => 'En cours', 'pill' => 'bg-blue-100 text-blue-700', 'color' => '#2563EB', 'dot' => 'bg-blue-600'],
        'resolved' => ['label' => 'Résolue', 'pill' => 'bg-emerald-100 text-emerald-700', 'color' => '#16A34A', 'dot' => 'bg-emerald-600'],
    ];

    $ticketSlices = [
        ['key' => 'pending', 'count' => $pendingTickets],
        ['key' => 'in_progress', 'count' => $inProgressTickets],
        ['key' => 'resolved', 'count' => $resolvedTickets],
    ];

    // Donut : segments séparés par un fin espace blanc
    $donutStops = [];
    $cursor = 0;
    $gap = 0.6;
    foreach ($ticketSlices as $slice) {
        if ($totalTickets === 0 || $slice['count'] === 0) {
            continue;
        }
        $end = $cursor + $slice['count'] / $totalTickets * 100;
        $visibleEnd = count(array_filter($ticketSlices, fn ($s) => $s['count'] > 0)) > 1 ? max($cursor, $end - $gap) : $end;
        $donutStops[] = $ticketMeta[$slice['key']]['color']." {$cursor}% {$visibleEnd}%";
        if ($visibleEnd < $end) {
            $donutStops[] = "#FFFFFF {$visibleEnd}% {$end}%";
        }
        $cursor = $end;
    }
    $donutBackground = $donutStops ? 'conic-gradient('.implode(', ', $donutStops).')' : '#E2E8F0';

    // Courbe : demandes créées par jour sur 30 jours
    $chartW = 640;
    $chartH = 220;
    $padL = 30;
    $padR = 10;
    $padT = 12;
    $padB = 26;
    $trendMax = (int) $ticketTrend->max('count');
    $yMax = max(4, (int) ceil($trendMax / 4) * 4);
    $lastIndex = max(1, $ticketTrend->count() - 1);
    $step = ($chartW - $padL - $padR) / $lastIndex;
    $xAt = fn (int $i) => round($padL + $i * $step, 2);
    $yAt = fn (int $v) => round($padT + (1 - $v / $yMax) * ($chartH - $padT - $padB), 2);
    $baseline = $chartH - $padB;
    $linePoints = $ticketTrend->values()->map(fn ($day, $i) => $xAt($i).','.$yAt($day['count']))->implode(' ');
    $areaPath = 'M'.$xAt(0).','.$baseline.' L'.str_replace(' ', ' L', $linePoints).' L'.$xAt($lastIndex).','.$baseline.' Z';
    $trendTotal = (int) $ticketTrend->sum('count');
    // Environ 6 dates sur l'axe, quelle que soit la période (7 → chaque jour, 30 → tous les 5 jours, 90 → tous les 15 jours).
    $labelStep = max(1, intdiv($ticketTrend->count(), 6));
@endphp

<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercialInitials">
    {{-- En-tête --}}
    <div class="min-w-0">
        <p class="text-sm font-semibold text-[#2563EB]">Vue globale</p>
        <h1 class="mt-1 break-words text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Bonjour, {{ $commercial->name }} <span aria-hidden="true">👋</span></h1>
        <p class="mt-2 text-sm text-slate-500">Bienvenue dans votre espace Commercial Refrigair Expo 2027.</p>
    </div>

    {{-- Indicateurs clés --}}
    <section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Indicateurs clés">
        <x-commercial.stat-card
            label="Exposants suivis"
            :value="$fmt($exhibitorsCount)"
            icon="users"
            tone="blue"
            :trend="'+' . $fmt($newExhibitorsThisMonth) . ' ce mois'"
            trend-tone="emerald"
        />
        <x-commercial.stat-card
            label="Demandes actives"
            :value="$fmt($activeTickets)"
            icon="inbox"
            tone="amber"
            :trend="'+' . $fmt($newTicketsToday) . ' aujourd’hui'"
            trend-tone="red"
        />
        <x-commercial.stat-card
            label="Demandes résolues"
            :value="$fmt($resolvedTickets)"
            icon="clipboard-check"
            tone="emerald"
            :trend="'+' . $fmt($resolvedTicketsThisMonth) . ' ce mois'"
            trend-tone="emerald"
        />
        <x-commercial.stat-card
            label="Notifications"
            :value="$fmt($unreadNotificationsCount)"
            icon="bell"
            tone="violet"
            trend="Non lues"
            trend-tone="violet"
        />
    </section>

    {{-- Demandes récentes + accès rapides --}}
    <section class="mt-6 grid gap-6 xl:grid-cols-5">
        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6 xl:col-span-3" aria-labelledby="recent-tickets-title">
            <div class="flex items-center justify-between gap-4">
                <h2 id="recent-tickets-title" class="text-lg font-bold text-slate-900">Demandes d'assistance récentes</h2>
                <a href="{{ route('commercial.support.index') }}" class="shrink-0 text-sm font-semibold text-[#2563EB] hover:underline">Voir tout →</a>
            </div>

            @if ($recentTickets->isNotEmpty())
                <div class="mt-5 hidden grid-cols-12 gap-3 rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-500 md:grid">
                    <span class="col-span-1">#</span>
                    <span class="col-span-3">Exposant</span>
                    <span class="col-span-4">Sujet</span>
                    <span class="col-span-2">Statut</span>
                    <span class="col-span-2">Date</span>
                </div>
                <ul class="mt-4 space-y-3 md:mt-1 md:space-y-0 md:divide-y md:divide-slate-100">
                    @foreach ($recentTickets as $ticket)
                        @php $tMeta = $ticketMeta[$ticket->status] ?? $ticketMeta['pending']; @endphp
                        <li>
                            <a href="{{ route('commercial.support.show', $ticket) }}" class="grid grid-cols-1 gap-2 rounded-xl border border-slate-100 p-3 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 md:grid-cols-12 md:items-center md:gap-3 md:rounded-none md:border-0 md:px-3 md:py-3">
                                <span class="hidden text-sm font-semibold text-slate-500 md:col-span-1 md:block">#{{ $ticket->id }}</span>
                                <span class="flex min-w-0 items-center gap-2 md:col-span-3">
                                    @if ($ticket->exhibitor?->logo)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($ticket->exhibitor->logo) }}" alt="" class="size-7 shrink-0 rounded-md bg-white object-contain ring-1 ring-slate-200">
                                    @else
                                        <span class="grid size-7 shrink-0 place-items-center rounded-md bg-slate-100 text-xs font-bold text-slate-500" aria-hidden="true">{{ mb_strtoupper(mb_substr($ticket->exhibitor?->company_name ?? '?', 0, 1)) }}</span>
                                    @endif
                                    <span class="truncate text-sm font-semibold text-slate-800">{{ $ticket->exhibitor?->company_name ?? 'Exposant' }}</span>
                                </span>
                                <span class="truncate text-sm text-slate-600 md:col-span-4">
                                    <span class="text-slate-400 md:hidden">#{{ $ticket->id }} · </span>{{ $ticket->subject }}
                                </span>
                                <span class="flex items-center justify-between gap-3 md:contents">
                                    <span class="md:col-span-2">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $tMeta['pill'] }}">{{ $tMeta['label'] }}</span>
                                    </span>
                                    <span class="text-xs text-slate-500 md:col-span-2">
                                        @if ($ticket->created_at?->isToday())
                                            Aujourd'hui à {{ $ticket->created_at->format('H:i') }}
                                        @elseif ($ticket->created_at?->isYesterday())
                                            Hier à {{ $ticket->created_at->format('H:i') }}
                                        @else
                                            {{ $ticket->created_at?->format('d/m/Y') }}
                                        @endif
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <x-admin.empty-state class="mt-5" icon="inbox" title="Aucune demande d'assistance" description="Les demandes envoyées par vos exposants apparaîtront ici." />
            @endif
        </article>

        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6 xl:col-span-2" aria-labelledby="quick-actions-title">
            <h2 id="quick-actions-title" class="flex items-center gap-2 text-lg font-bold text-slate-900">
                <x-admin.icon name="zap" class="size-5 text-[#2563EB]" />
                Accès rapides
            </h2>
            <nav class="mt-4 space-y-2.5" aria-label="Accès rapides">
                @foreach ([
                    ['label' => 'Gérer mes exposants', 'icon' => 'users', 'href' => route('commercial.exhibitors.index'), 'count' => null],
                    ['label' => 'Ajouter un exposant', 'icon' => 'user-plus', 'href' => route('commercial.exhibitors.create'), 'count' => null],
                    ['label' => 'Badges de mes exposants', 'icon' => 'badge', 'href' => route('commercial.badges.index'), 'count' => null],
                    ['label' => 'Assistance', 'icon' => 'inbox', 'href' => route('commercial.support.index'), 'count' => $activeTickets],
                    ['label' => 'Notifications', 'icon' => 'bell', 'href' => route('commercial.notifications.index'), 'count' => $unreadNotificationsCount],
                ] as $action)
                    <a href="{{ $action['href'] }}" class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 hover:border-blue-200 hover:bg-blue-50/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                        <x-admin.icon :name="$action['icon']" class="size-5 shrink-0 text-[#2563EB]" />
                        <span class="flex-1">{{ $action['label'] }}</span>
                        @if ($action['count'])
                            <span class="rounded-full bg-red-500 px-2 py-0.5 text-xs font-bold text-white">{{ $fmt($action['count']) }}</span>
                        @endif
                        <span aria-hidden="true" class="text-slate-400">→</span>
                    </a>
                @endforeach
            </nav>
        </article>
    </section>

    {{-- Évolution + répartition des demandes --}}
    <section class="mt-6 grid gap-6 xl:grid-cols-5">
        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6 xl:col-span-3" aria-labelledby="trend-title">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="trend-title" class="text-lg font-bold text-slate-900">Évolution des demandes</h2>
                    <p class="mt-0.5 text-xs font-medium text-slate-500">{{ $trendDays }} derniers jours · {{ $fmt($trendTotal) }} demande{{ $trendTotal > 1 ? 's' : '' }}</p>
                </div>
                <form method="GET" action="{{ route('commercial.index') }}" class="flex items-center gap-2">
                    <label for="period" class="sr-only">Période</label>
                    <select id="period" name="period" onchange="this.form.submit()" class="rounded-lg border-slate-200 py-1.5 text-sm font-medium text-slate-700 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach ($trendPeriods as $days)
                            <option value="{{ $days }}" @selected($trendDays === $days)>{{ $days }} derniers jours</option>
                        @endforeach
                    </select>
                    <noscript><button class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-semibold text-slate-700">OK</button></noscript>
                </form>
            </div>

            <div class="relative mt-4">
                <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" class="h-auto w-full" role="img" aria-label="Nombre de demandes d'assistance créées par jour sur les {{ $trendDays }} derniers jours">
                    @foreach ([0, $yMax / 2, $yMax] as $tick)
                        <line x1="{{ $padL }}" x2="{{ $chartW - $padR }}" y1="{{ $yAt((int) $tick) }}" y2="{{ $yAt((int) $tick) }}" stroke="#E2E8F0" stroke-width="1" />
                        <text x="{{ $padL - 8 }}" y="{{ $yAt((int) $tick) + 4 }}" text-anchor="end" class="fill-slate-400" font-size="11">{{ (int) $tick }}</text>
                    @endforeach

                    <path d="{{ $areaPath }}" fill="#2563EB" fill-opacity="0.08" />
                    <polyline points="{{ $linePoints }}" fill="none" stroke="#2563EB" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />

                    @foreach ($ticketTrend->values() as $i => $day)
                        @if ($i % $labelStep === 0 || $i === $lastIndex)
                            <text x="{{ $xAt($i) }}" y="{{ $chartH - 6 }}" text-anchor="{{ $i === 0 ? 'start' : ($i === $lastIndex ? 'end' : 'middle') }}" class="fill-slate-400" font-size="11">{{ $day['date']->format('d/m') }}</text>
                        @endif
                        <g class="group">
                            <title>{{ $day['date']->format('d/m/Y') }} : {{ $day['count'] }} demande{{ $day['count'] > 1 ? 's' : '' }}</title>
                            <rect x="{{ $xAt($i) - $step / 2 }}" y="{{ $padT }}" width="{{ $step }}" height="{{ $baseline - $padT }}" fill="transparent" />
                            <line x1="{{ $xAt($i) }}" x2="{{ $xAt($i) }}" y1="{{ $padT }}" y2="{{ $baseline }}" stroke="#94A3B8" stroke-width="1" stroke-dasharray="3 3" class="opacity-0 group-hover:opacity-100" />
                            <circle cx="{{ $xAt($i) }}" cy="{{ $yAt($day['count']) }}" r="4.5" fill="#2563EB" stroke="#FFFFFF" stroke-width="2" class="opacity-0 group-hover:opacity-100" />
                        </g>
                    @endforeach
                </svg>

                @if ($trendTotal === 0)
                    <p class="absolute inset-0 grid place-items-center text-sm font-medium text-slate-400">Aucune demande sur cette période</p>
                @endif
            </div>

            <table class="sr-only">
                <caption>Demandes d'assistance par jour</caption>
                <thead><tr><th scope="col">Date</th><th scope="col">Demandes</th></tr></thead>
                <tbody>
                    @foreach ($ticketTrend as $day)
                        <tr><td>{{ $day['date']->format('d/m/Y') }}</td><td>{{ $day['count'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </article>

        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6 xl:col-span-2" aria-labelledby="ticket-split-title">
            <h2 id="ticket-split-title" class="text-lg font-bold text-slate-900">Répartition des demandes</h2>

            <div class="mt-5 flex flex-col items-center gap-6 sm:flex-row xl:flex-col 2xl:flex-row">
                <div class="relative grid size-40 shrink-0 place-items-center rounded-full" style="background: {{ $donutBackground }};" aria-hidden="true">
                    <div class="grid size-28 place-items-center rounded-full bg-white text-center">
                        <div>
                            <p class="text-2xl font-bold text-slate-900">{{ $fmt($totalTickets) }}</p>
                            <p class="text-xs text-slate-500">Demande{{ $totalTickets > 1 ? 's' : '' }}</p>
                        </div>
                    </div>
                </div>

                <dl class="w-full space-y-3">
                    @foreach ($ticketSlices as $slice)
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <dt class="flex items-center gap-2 font-medium text-slate-700">
                                <span class="{{ $ticketMeta[$slice['key']]['dot'] }} size-2.5 shrink-0 rounded-full" aria-hidden="true"></span>
                                {{ $ticketMeta[$slice['key']]['label'] }} ({{ $fmt($slice['count']) }})
                            </dt>
                            <dd class="font-semibold text-slate-500">{{ $totalTickets > 0 ? round($slice['count'] / $totalTickets * 100) : 0 }}%</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </article>
    </section>

    {{-- Suivi des badges + exposants à relancer --}}
    <section class="mt-6 grid gap-6 xl:grid-cols-5">
        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6 xl:col-span-3">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Progression générale des badges</h2>
                    <p class="mt-1 text-sm text-slate-500">Badges enregistrés par rapport au quota autorisé.</p>
                </div>
                <span class="shrink-0 rounded-xl bg-blue-50 px-3 py-1.5 text-lg font-bold text-blue-700">{{ $pct($badgeProgress) }}%</span>
            </div>

            <dl class="mt-6 grid grid-cols-3 gap-3 text-center">
                <div class="rounded-xl bg-slate-50 p-3">
                    <dt class="text-xs text-slate-500">Autorisés</dt>
                    <dd class="mt-1 text-xl font-bold text-slate-900">{{ $fmt($totalAuthorizedBadges) }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <dt class="text-xs text-slate-500">Enregistrés</dt>
                    <dd class="mt-1 text-xl font-bold text-slate-900">{{ $fmt($totalRegisteredBadges) }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-3">
                    <dt class="text-xs text-slate-500">Restants</dt>
                    <dd class="mt-1 text-xl font-bold text-slate-900">{{ $fmt($remainingBadges) }}</dd>
                </div>
            </dl>

            @if ($totalAuthorizedBadges > 0)
                <div class="mt-5 h-3 w-full overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="Remplissage des badges" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $badgeProgress }}">
                    <div class="h-full rounded-full bg-blue-600 transition-all duration-500" style="width: {{ $badgeProgress }}%"></div>
                </div>
            @else
                <p class="mt-5 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                    Aucun quota de badges n'est encore attribué. Définissez le nombre de badges autorisés dans la fiche de chaque exposant.
                </p>
            @endif

            <h3 class="mt-7 text-sm font-semibold text-slate-700">Répartition des exposants</h3>

            @if ($exhibitorsCount > 0)
                <div class="mt-3 flex h-2.5 w-full gap-0.5 overflow-hidden rounded-full" aria-hidden="true">
                    @foreach ($breakdown as $item)
                        @if ($item['count'] > 0)
                            <div class="{{ $statusMeta[$item['key']]['bar'] }} h-full" style="width: {{ $item['count'] / $exhibitorsCount * 100 }}%"></div>
                        @endif
                    @endforeach
                </div>
            @endif

            <dl class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach ($breakdown as $item)
                    <div class="rounded-xl border border-slate-100 p-3">
                        <dt class="flex items-center gap-2 text-xs font-medium text-slate-500">
                            <span class="{{ $statusMeta[$item['key']]['bar'] }} size-2 shrink-0 rounded-full" aria-hidden="true"></span>
                            {{ $statusMeta[$item['key']]['label'] }}
                        </dt>
                        <dd class="mt-1 text-xl font-bold text-slate-900">{{ $fmt($item['count']) }}</dd>
                        <dd class="mt-0.5 text-[11px] leading-4 text-slate-400">{{ $item['hint'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </article>

        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6 xl:col-span-2">
            <h2 class="text-lg font-bold text-slate-900">À relancer</h2>
            <p class="mt-1 text-sm text-slate-500">Exposants sans badge ou sans accès actif.</p>

            @if ($withoutAccessCount > 0 || $disabledAccessCount > 0)
                <p class="mt-4 flex flex-wrap gap-2 text-xs">
                    @if ($withoutAccessCount > 0)
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-600">{{ $fmt($withoutAccessCount) }} sans accès</span>
                    @endif
                    @if ($disabledAccessCount > 0)
                        <span class="rounded-full bg-red-50 px-2.5 py-1 font-semibold text-red-700">{{ $fmt($disabledAccessCount) }} accès désactivé{{ $disabledAccessCount > 1 ? 's' : '' }}</span>
                    @endif
                </p>
            @endif

            <ul class="mt-4 space-y-3">
                @forelse ($followUpExhibitors as $exhibitor)
                    <li>
                        <a href="{{ route('commercial.exhibitors.show', $exhibitor) }}" class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 p-3 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-slate-800">{{ $exhibitor->company_name }}</span>
                                <span class="mt-1 block text-xs text-slate-500">
                                    @if ($exhibitor->access_status !== 'active')
                                        <span class="{{ $accessMeta[$exhibitor->access_status]['class'] }}">{{ $accessMeta[$exhibitor->access_status]['label'] }}</span>
                                    @endif
                                    @if ($exhibitor->access_status !== 'active' && $exhibitor->badge_status === 'pending')
                                        ·
                                    @endif
                                    @if ($exhibitor->badge_status === 'pending')
                                        Aucun badge sur {{ $fmt($exhibitor->authorized_badges) }}
                                    @endif
                                </span>
                            </span>
                            <span class="shrink-0 text-sm font-semibold text-[#2563EB]">Ouvrir</span>
                        </a>
                    </li>
                @empty
                    <li>
                        <x-admin.empty-state icon="badge" title="Rien à relancer" description="{{ $exhibitorsCount > 0 ? 'Tous vos exposants ont un accès actif et ont commencé leurs badges.' : 'Les exposants à relancer apparaîtront ici.' }}" />
                    </li>
                @endforelse
            </ul>
        </article>
    </section>

    {{-- Conférences & Lounge des Technologies --}}
    @php
        $eur = fn ($value) => number_format((float) $value, 0, ',', ' ').' €';
        $slotTotal = $slotStats['total'];
    @endphp
    <section class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6" aria-labelledby="slots-title">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 id="slots-title" class="text-lg font-bold text-slate-900">Conférences & Lounge des Technologies</h2>
                <p class="mt-1 text-sm text-slate-500">Ventes des créneaux de présentation (réservé + payé). Les options ne sont pas comptées.</p>
            </div>
            <a href="{{ route('commercial.slots.index') }}" class="text-sm font-semibold text-[#2563EB] hover:underline">Ouvrir le planning →</a>
        </div>

        @if ($slotTotal['total'] === 0)
            <p class="mt-5 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">Aucun créneau n'est encore configuré.</p>
        @else
            <dl class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($slotStats['spaces'] as $space)
                    <div class="rounded-xl bg-slate-50 p-4">
                        <dt class="text-xs font-semibold text-slate-500">{{ $space['label'] }}</dt>
                        <dd class="mt-1 text-2xl font-bold text-slate-900">{{ $space['sold'] }} <span class="text-base font-semibold text-slate-400">/ {{ $space['total'] }} vendus</span></dd>
                        <dd class="mt-0.5 text-xs text-slate-500">{{ $eur($space['revenue']) }} HT · {{ $space['options'] }} en option</dd>
                    </div>
                @endforeach
                <div class="rounded-xl bg-violet-50 p-4">
                    <dt class="text-xs font-semibold text-violet-700">CA conférences + lounge</dt>
                    <dd class="mt-1 text-2xl font-bold text-violet-900">{{ $eur($slotTotal['revenue']) }} <span class="text-base font-semibold">HT</span></dd>
                    <dd class="mt-0.5 text-xs text-violet-700">Réservé + payé</dd>
                </div>
                <div class="rounded-xl bg-emerald-50 p-4">
                    <dt class="text-xs font-semibold text-emerald-700">CA encaissé</dt>
                    <dd class="mt-1 text-2xl font-bold text-emerald-900">{{ $eur($slotTotal['collected']) }} <span class="text-base font-semibold">HT</span></dd>
                    <dd class="mt-0.5 text-xs text-emerald-700">Créneaux payés</dd>
                </div>
            </dl>

            <h3 class="mt-6 text-sm font-semibold text-slate-700">Taux de remplissage par jour</h3>
            <ul class="mt-3 space-y-3">
                @foreach ($slotStats['days'] as $day)
                    <li>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-semibold capitalize text-slate-700">{{ $day['date']->locale('fr')->isoFormat('dddd D MMMM') }}</span>
                            <span class="text-slate-500">{{ $day['sold'] }} / {{ $day['total'] }} · <span class="font-bold text-slate-900">{{ $pct($day['fill_rate']) }} %</span></span>
                        </div>
                        <div class="mt-1.5 h-2.5 w-full overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="Remplissage du {{ $day['date']->format('d/m') }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $day['fill_rate'] }}">
                            <div class="h-full rounded-full bg-blue-600" style="width: {{ $day['fill_rate'] }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    {{-- Derniers exposants --}}
    <section class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6" aria-labelledby="recent-exhibitors-title">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 id="recent-exhibitors-title" class="text-lg font-bold text-slate-900">Mes derniers exposants</h2>
                <p class="mt-1 text-sm text-slate-500">Les inscriptions récentes qui vous sont attribuées.</p>
            </div>
            @if ($exhibitorsCount > 0)
                <a href="{{ route('commercial.exhibitors.index') }}" class="text-sm font-semibold text-[#2563EB] hover:underline">Voir tous mes exposants ({{ $fmt($exhibitorsCount) }})</a>
            @endif
        </div>

        @if ($recentExhibitors->isNotEmpty())
            <div class="mt-5 hidden grid-cols-12 gap-4 rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-500 md:grid">
                <span class="col-span-4">Exposant</span>
                <span class="col-span-2">Stand</span>
                <span class="col-span-3">Badges</span>
                <span class="col-span-2">Statut</span>
                <span class="col-span-1 text-right">Fiche</span>
            </div>

            <ul class="mt-4 space-y-3 md:mt-1 md:space-y-0 md:divide-y md:divide-slate-100">
                @foreach ($recentExhibitors as $exhibitor)
                    @php $meta = $statusMeta[$exhibitor->badge_status]; @endphp
                    <li class="grid grid-cols-1 gap-3 rounded-xl border border-slate-100 p-4 md:grid-cols-12 md:items-center md:gap-4 md:rounded-none md:border-0 md:px-3 md:py-4">
                        <div class="flex min-w-0 items-start gap-3 md:col-span-4">
                            @if ($exhibitor->logo)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($exhibitor->logo) }}" alt="Logo {{ $exhibitor->company_name }}" class="size-10 shrink-0 rounded-lg bg-white object-contain ring-1 ring-slate-200">
                            @else
                                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-slate-100 text-sm font-bold text-slate-500" aria-hidden="true">{{ mb_strtoupper(mb_substr($exhibitor->company_name, 0, 1)) }}</span>
                            @endif
                            <div class="min-w-0">
                                <a href="{{ route('commercial.exhibitors.show', $exhibitor) }}" class="block truncate text-sm font-semibold text-slate-800 hover:text-[#2563EB]">{{ $exhibitor->company_name }}</a>
                                <p class="mt-1 truncate text-xs text-slate-500">
                                    {{ $exhibitor->contact_name }} · {{ $exhibitor->created_at?->format('d/m/Y') }}
                                </p>
                                <p class="mt-1 text-xs {{ $accessMeta[$exhibitor->access_status]['class'] }}">{{ $accessMeta[$exhibitor->access_status]['label'] }}</p>
                            </div>
                        </div>

                        <p class="text-sm text-slate-600 md:col-span-2">
                            <span class="text-xs text-slate-400 md:hidden">Stand :</span>
                            {{ $exhibitor->stand_number ?: '—' }}
                        </p>

                        <div class="md:col-span-3">
                            <p class="text-xs font-medium text-slate-600">
                                {{ $fmt($exhibitor->badges_count) }} / {{ $fmt($exhibitor->authorized_badges) }} badges enregistrés
                            </p>
                            <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100" aria-hidden="true">
                                <div class="{{ $meta['bar'] }} h-full rounded-full" style="width: {{ $exhibitor->badge_progress }}%"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 md:contents">
                            <span class="md:col-span-2">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $meta['pill'] }}">{{ $meta['label'] }}</span>
                            </span>
                            <a href="{{ route('commercial.exhibitors.show', $exhibitor) }}" class="text-sm font-semibold text-[#2563EB] hover:underline md:col-span-1 md:text-right" aria-label="Voir la fiche de {{ $exhibitor->company_name }}">Voir</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <x-admin.empty-state class="mt-5" icon="users" title="Aucun exposant disponible" description="Ajoutez votre premier exposant pour commencer le suivi de ses badges." />
            <div class="mt-4 text-center">
                <a href="{{ route('commercial.exhibitors.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Ajouter un exposant</a>
            </div>
        @endif
    </section>
</x-commercial.layout>
