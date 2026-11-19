@php
    $fmt = fn ($value) => number_format((float) $value, 0, ',', ' ');
    $pct = fn ($value) => rtrim(rtrim(number_format((float) $value, 1, ',', ' '), '0'), ',');

    $statusMeta = [
        'complete' => ['label' => 'Complet', 'pill' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'bar' => 'bg-emerald-500'],
        'partial' => ['label' => 'Partiel', 'pill' => 'bg-amber-50 text-amber-700 ring-amber-200', 'bar' => 'bg-amber-500'],
        'pending' => ['label' => 'En attente', 'pill' => 'bg-red-50 text-red-700 ring-red-200', 'bar' => 'bg-red-500'],
        'no_quota' => ['label' => 'Sans quota', 'pill' => 'bg-slate-100 text-slate-600 ring-slate-200', 'bar' => 'bg-slate-400'],
    ];

    $statusOf = fn ($exhibitor) => match (true) {
        $exhibitor->authorized_badges <= 0 => 'no_quota',
        $exhibitor->badges_count === 0 => 'pending',
        $exhibitor->badges_count >= $exhibitor->authorized_badges => 'complete',
        default => 'partial',
    };

    $activityMeta = [
        'created' => ['label' => 'Badge créé', 'icon' => 'user-plus'],
        'updated' => ['label' => 'Badge modifié', 'icon' => 'settings'],
        'activated' => ['label' => 'Badge réactivé', 'icon' => 'clipboard-check'],
        'deactivated' => ['label' => 'Badge désactivé', 'icon' => 'badge'],
        'archived' => ['label' => 'Badge archivé', 'icon' => 'box'],
        'printed' => ['label' => 'Badge imprimé', 'icon' => 'badge'],
    ];

    $tabs = [
        'exhibitors' => ['label' => 'Par exposant', 'icon' => 'users'],
        'badges' => ['label' => 'Badges', 'icon' => 'badge'],
        'history' => ['label' => 'Historique', 'icon' => 'calendar'],
    ];

    $usedDeg = $totalAuthorized > 0 ? min(100, $totalBadges / $totalAuthorized * 100) : 0;
    $exportParams = array_filter(['exhibitor_id' => $filters['exhibitor_id']]);
@endphp

<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercialInitials">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-[#2563EB]">Gestion</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Gestion des badges</h1>
            <p class="mt-2 text-sm text-slate-500">Suivez les quotas et les badges de vos exposants.</p>
        </div>
        <a href="{{ route('commercial.badges.export', $exportParams) }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Exporter (CSV)
        </a>
    </div>

    @if (session('status'))
        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800" role="alert">{{ $errors->first() }}</div>
    @endif

    {{-- Indicateurs --}}
    <section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Statistiques des badges">
        <x-admin.stat-card label="Total badges" :value="$fmt($totalBadges)" icon="badge-users" :description="'+'.$fmt($newBadgesThisMonth).' ce mois'" />
        <x-admin.stat-card label="Badges imprimés" :value="$fmt($printedBadges)" icon="badge" :description="$fmt(max(0, $totalBadges - $printedBadges)).' pas encore imprimés'" />
        <x-admin.stat-card label="Quotas utilisés" :value="$pct($quotaUsage).'%'" icon="chart" :description="$fmt($totalBadges).' / '.$fmt($totalAuthorized).' badges'" />
        <x-admin.stat-card label="Badges restants" :value="$fmt($remainingBadges)" icon="inbox" :description="$totalAuthorized > 0 ? $pct(100 - $quotaUsage).'% du quota' : 'Aucun quota attribué'" />
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-4">
        {{-- Contenu principal --}}
        <section class="min-w-0 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6 xl:col-span-3">
            <nav class="-mx-1 flex gap-1 overflow-x-auto border-b border-slate-200" aria-label="Onglets badges">
                @foreach ($tabs as $key => $item)
                    <a href="{{ route('commercial.badges.index', ['tab' => $key]) }}"
                       @class([
                           'inline-flex shrink-0 items-center gap-2 border-b-2 px-3 py-2.5 text-sm font-semibold',
                           'border-[#2563EB] text-[#2563EB]' => $tab === $key,
                           'border-transparent text-slate-500 hover:text-slate-800' => $tab !== $key,
                       ])
                       @if ($tab === $key) aria-current="page" @endif>
                        <x-admin.icon :name="$item['icon']" class="size-4" />
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            @if ($tab === 'exhibitors')
                <form method="GET" action="{{ route('commercial.badges.index') }}" class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <input type="hidden" name="tab" value="exhibitors">
                    <label for="search" class="sr-only">Rechercher un exposant</label>
                    <input id="search" name="search" value="{{ $filters['search'] }}" placeholder="Rechercher un exposant (nom, email, stand…)" class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <label for="status" class="sr-only">Statut</label>
                    <select id="status" name="status" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tous les statuts</option>
                        @foreach ($statusMeta as $key => $meta)
                            <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filtrer</button>
                    @if ($filters['search'] !== '' || $filters['status'])
                        <a href="{{ route('commercial.badges.index') }}" class="self-center text-sm font-semibold text-[#2563EB] hover:underline">Réinitialiser</a>
                    @endif
                </form>

                <div class="mt-5 overflow-x-auto">
                    <table class="w-full min-w-[880px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold text-slate-500">
                            <tr>
                                <th class="px-3 py-3">Exposant</th>
                                <th class="px-3 py-3">Stand</th>
                                <th class="px-3 py-3">Quota</th>
                                <th class="px-3 py-3 text-right">Utilisés</th>
                                <th class="px-3 py-3 text-right">Restants</th>
                                <th class="px-3 py-3">Taux</th>
                                <th class="px-3 py-3">Statut</th>
                                <th class="px-3 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($exhibitors as $exhibitor)
                                @php
                                    $meta = $statusMeta[$statusOf($exhibitor)];
                                    $rate = $exhibitor->authorized_badges > 0 ? min(100, round($exhibitor->badges_count / $exhibitor->authorized_badges * 100)) : 0;
                                @endphp
                                <tr class="align-middle hover:bg-slate-50/70">
                                    <td class="px-3 py-3">
                                        <div class="flex min-w-0 items-center gap-3">
                                            @if ($exhibitor->logo)
                                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($exhibitor->logo) }}" alt="" class="size-9 shrink-0 rounded-lg bg-white object-contain ring-1 ring-slate-200">
                                            @else
                                                <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-slate-100 text-sm font-bold text-slate-500" aria-hidden="true">{{ mb_strtoupper(mb_substr($exhibitor->company_name, 0, 1)) }}</span>
                                            @endif
                                            <div class="min-w-0">
                                                <a href="{{ route('commercial.exhibitors.show', $exhibitor) }}" class="block truncate font-semibold text-slate-800 hover:text-[#2563EB]">{{ $exhibitor->company_name }}</a>
                                                <p class="truncate text-xs text-slate-500">{{ $exhibitor->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3"><span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{{ $exhibitor->stand_number ?: '—' }}</span></td>
                                    <td class="px-3 py-3">
                                        <form method="POST" action="{{ route('commercial.badges.quota', $exhibitor) }}" class="flex items-center gap-1.5">
                                            @csrf
                                            @method('PATCH')
                                            <label for="quota-{{ $exhibitor->id }}" class="sr-only">Quota de {{ $exhibitor->company_name }}</label>
                                            <input id="quota-{{ $exhibitor->id }}" type="number" name="authorized_badges" min="{{ $exhibitor->badges_count }}" max="100000" value="{{ $exhibitor->authorized_badges }}" class="w-20 rounded-lg border-slate-300 py-1 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <button class="rounded-lg border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50" title="Enregistrer le quota">OK</button>
                                        </form>
                                    </td>
                                    <td class="px-3 py-3 text-right font-semibold text-slate-800">{{ $fmt($exhibitor->badges_count) }}</td>
                                    <td class="px-3 py-3 text-right text-slate-600">{{ $fmt(max(0, $exhibitor->authorized_badges - $exhibitor->badges_count)) }}</td>
                                    <td class="px-3 py-3">
                                        <p class="text-xs font-semibold text-slate-700">{{ $rate }}%</p>
                                        <div class="mt-1 h-1.5 w-24 overflow-hidden rounded-full bg-slate-100" aria-hidden="true">
                                            <div class="{{ $meta['bar'] }} h-full rounded-full" style="width: {{ $rate }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $meta['pill'] }}">{{ $meta['label'] }}</span></td>
                                    <td class="px-3 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('commercial.badges.index', ['tab' => 'badges', 'exhibitor_id' => $exhibitor->id]) }}" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Badges</a>
                                            @if ($exhibitor->active_badges_count > 0)
                                                <form method="POST" action="{{ route('commercial.badges.print-exhibitor', $exhibitor) }}" target="_blank">
                                                    @csrf
                                                    <button class="rounded-lg bg-[#2563EB] px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-blue-700" title="Imprimer les {{ $exhibitor->active_badges_count }} badges actifs avec QR code">Imprimer ({{ $exhibitor->active_badges_count }})</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-3 py-8"><x-admin.empty-state icon="users" title="Aucun exposant trouvé" description="Ajoutez un exposant ou modifiez vos filtres." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-5">{{ $exhibitors->links() }}</div>
            @elseif ($tab === 'badges')
                <form method="GET" action="{{ route('commercial.badges.index') }}" class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <input type="hidden" name="tab" value="badges">
                    <label for="badge-search" class="sr-only">Rechercher un badge</label>
                    <input id="badge-search" name="search" value="{{ $filters['search'] }}" placeholder="Rechercher (nom, email, fonction…)" class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <label for="exhibitor_id" class="sr-only">Exposant</label>
                    <select id="exhibitor_id" name="exhibitor_id" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tous les exposants</option>
                        @foreach ($exhibitorOptions as $option)
                            <option value="{{ $option->id }}" @selected((int) $filters['exhibitor_id'] === $option->id)>{{ $option->company_name }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filtrer</button>
                    @if ($filters['search'] !== '' || $filters['exhibitor_id'])
                        <a href="{{ route('commercial.badges.index', ['tab' => 'badges']) }}" class="self-center text-sm font-semibold text-[#2563EB] hover:underline">Réinitialiser</a>
                    @endif
                </form>

                <ul class="mt-5 divide-y divide-slate-100">
                    @forelse ($badges as $badge)
                        <li class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-800">{{ $badge->full_name }} <span class="ml-1 text-xs font-medium text-slate-400">{{ $badge->reference }}</span></p>
                                <p class="mt-0.5 truncate text-xs text-slate-500">{{ $badge->function }} · {{ $badge->exhibitor?->company_name }} · Stand {{ $badge->exhibitor?->stand_number ?: '—' }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($badge->status === 'active')
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">Actif</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">Inactif</span>
                                @endif
                                @if ($badge->last_printed_at)
                                    <span class="text-xs text-slate-500">Imprimé le {{ \Illuminate\Support\Carbon::parse($badge->last_printed_at)->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-xs text-amber-700">Jamais imprimé</span>
                                @endif
                                @if ($badge->status === 'active')
                                    <form method="POST" action="{{ route('commercial.badges.print', $badge) }}" target="_blank">
                                        @csrf
                                        <button class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50" title="Imprimer le badge avec son QR code">Imprimer</button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="py-6"><x-admin.empty-state icon="badge" title="Aucun badge trouvé" description="Les badges enregistrés par vos exposants apparaîtront ici." /></li>
                    @endforelse
                </ul>
                <div class="mt-5">{{ $badges->links() }}</div>
            @else
                <ul class="mt-5 divide-y divide-slate-100">
                    @forelse ($activities as $activity)
                        @php $aMeta = $activityMeta[$activity->action] ?? ['label' => ucfirst($activity->action), 'icon' => 'badge']; @endphp
                        <li class="flex items-start gap-3 py-4">
                            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-blue-50 text-[#2563EB]"><x-admin.icon :name="$aMeta['icon']" class="size-4" /></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-800">{{ $aMeta['label'] }} · {{ $activity->badge?->full_name }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $activity->badge?->exhibitor?->company_name }} · par {{ $activity->user?->name ?? 'Système' }}</p>
                            </div>
                            <time class="shrink-0 text-xs text-slate-400" datetime="{{ $activity->created_at?->toIso8601String() }}">{{ $activity->created_at?->format('d/m/Y H:i') }}</time>
                        </li>
                    @empty
                        <li class="py-6"><x-admin.empty-state icon="calendar" title="Aucune activité" description="Les impressions et modifications de badges apparaîtront ici." /></li>
                    @endforelse
                </ul>
                <div class="mt-5">{{ $activities->links() }}</div>
            @endif
        </section>

        {{-- Colonne latérale --}}
        <aside class="space-y-6">
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                <h2 class="flex items-center gap-2 text-base font-bold text-slate-900"><x-admin.icon name="zap" class="size-5 text-[#2563EB]" /> Actions rapides</h2>
                <nav class="mt-4 space-y-2" aria-label="Actions rapides badges">
                    <a href="{{ route('commercial.badges.index', ['tab' => 'exhibitors', 'status' => 'pending']) }}" class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Exposants sans badge <span aria-hidden="true" class="text-slate-400">→</span></a>
                    <a href="{{ route('commercial.badges.index', ['tab' => 'badges']) }}" class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Imprimer des badges <span aria-hidden="true" class="text-slate-400">→</span></a>
                    <a href="{{ route('commercial.badges.export') }}" class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Exporter tous les badges <span aria-hidden="true" class="text-slate-400">→</span></a>
                </nav>
            </article>

            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                <h2 class="text-base font-bold text-slate-900">Répartition des quotas</h2>
                <div class="mt-4 flex items-center gap-5 xl:flex-col 2xl:flex-row">
                    <div class="grid size-32 shrink-0 place-items-center rounded-full" style="background: conic-gradient(#2563EB 0% {{ $usedDeg }}%, #E2E8F0 {{ $usedDeg }}% 100%);" aria-hidden="true">
                        <div class="grid size-22 place-items-center rounded-full bg-white text-center">
                            <div>
                                <p class="text-xl font-bold text-slate-900">{{ $fmt($totalAuthorized) }}</p>
                                <p class="text-[11px] text-slate-500">Badges</p>
                            </div>
                        </div>
                    </div>
                    <dl class="w-full space-y-2.5 text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <dt class="flex items-center gap-2 text-slate-700"><span class="size-2.5 rounded-full bg-[#2563EB]" aria-hidden="true"></span> Utilisés ({{ $fmt($totalBadges) }})</dt>
                            <dd class="font-semibold text-slate-500">{{ $pct($quotaUsage) }}%</dd>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <dt class="flex items-center gap-2 text-slate-700"><span class="size-2.5 rounded-full bg-slate-300" aria-hidden="true"></span> Restants ({{ $fmt($remainingBadges) }})</dt>
                            <dd class="font-semibold text-slate-500">{{ $totalAuthorized > 0 ? $pct(100 - $quotaUsage) : 0 }}%</dd>
                        </div>
                    </dl>
                </div>
            </article>

            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                <h2 class="text-base font-bold text-slate-900">Dernières activités</h2>
                <ul class="mt-3 divide-y divide-slate-100">
                    @forelse ($recentActivities as $activity)
                        <li class="py-2.5">
                            <p class="text-sm font-semibold text-slate-800">{{ ($activityMeta[$activity->action] ?? ['label' => ucfirst($activity->action)])['label'] }}</p>
                            <p class="mt-0.5 flex justify-between gap-2 text-xs text-slate-500">
                                <span class="truncate">{{ $activity->badge?->exhibitor?->company_name }}</span>
                                <span class="shrink-0">{{ $activity->created_at?->format('d/m H:i') }}</span>
                            </p>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-slate-500">Aucune activité récente.</li>
                    @endforelse
                </ul>
                <a href="{{ route('commercial.badges.index', ['tab' => 'history']) }}" class="mt-3 inline-block text-sm font-semibold text-[#2563EB] hover:underline">Voir tout l'historique →</a>
            </article>
        </aside>
    </div>
</x-commercial.layout>
