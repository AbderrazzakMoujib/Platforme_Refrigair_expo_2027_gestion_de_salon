@php
    $fmt = fn ($value) => number_format((float) $value, 0, ',', ' ');
    $money = fn ($value) => number_format((float) $value, 2, ',', ' ').' DH';
    $share = fn ($count) => $totalCount > 0 ? round($count / $totalCount * 100) : 0;

    $statusMeta = [
        'pending' => ['label' => 'En attente', 'pill' => 'bg-amber-100 text-amber-700', 'color' => '#F59E0B', 'dot' => 'bg-amber-500'],
        'approved' => ['label' => 'Approuvée', 'pill' => 'bg-emerald-100 text-emerald-700', 'color' => '#16A34A', 'dot' => 'bg-emerald-600'],
        'rejected' => ['label' => 'Refusée', 'pill' => 'bg-red-100 text-red-700', 'color' => '#DC2626', 'dot' => 'bg-red-600'],
    ];

    $tabs = [
        'all' => ['label' => 'Toutes', 'count' => $totalCount],
        'pending' => ['label' => 'En attente', 'count' => $pendingCount],
        'approved' => ['label' => 'Approuvées', 'count' => $approvedCount],
        'rejected' => ['label' => 'Refusées', 'count' => $rejectedCount],
    ];

    // Donut avec un fin espace blanc entre les segments
    $slices = array_filter([
        ['key' => 'approved', 'count' => $approvedCount],
        ['key' => 'pending', 'count' => $pendingCount],
        ['key' => 'rejected', 'count' => $rejectedCount],
    ], fn ($slice) => $slice['count'] > 0);
    $stops = [];
    $cursor = 0;
    foreach ($slices as $slice) {
        $end = $cursor + $slice['count'] / max(1, $totalCount) * 100;
        $visibleEnd = count($slices) > 1 ? max($cursor, $end - 0.6) : $end;
        $stops[] = $statusMeta[$slice['key']]['color']." {$cursor}% {$visibleEnd}%";
        if ($visibleEnd < $end) {
            $stops[] = "#FFFFFF {$visibleEnd}% {$end}%";
        }
        $cursor = $end;
    }
    $donut = $stops ? 'conic-gradient('.implode(', ', $stops).')' : '#E2E8F0';
@endphp

<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercialInitials">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-[#2563EB]">Gestion</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Demandes d'équipements</h1>
            <p class="mt-2 text-sm text-slate-500">Suivez les demandes d'équipements de vos exposants.</p>
        </div>
        <a href="{{ route('commercial.requests.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
            <span aria-hidden="true" class="text-base leading-none">+</span> Nouvelle demande
        </a>
    </div>

    @if (session('status'))
        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">{{ session('status') }}</div>
    @endif

    <section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Statistiques des demandes">
        <x-admin.stat-card label="Total des demandes" :value="$fmt($totalCount)" icon="box" :description="'+'.$fmt($newThisMonth).' ce mois'" />
        <x-admin.stat-card label="Approuvées" :value="$fmt($approvedCount)" icon="clipboard-check" :description="$share($approvedCount).'% des demandes'" />
        <x-admin.stat-card label="En attente" :value="$fmt($pendingCount)" icon="inbox" :description="$share($pendingCount).'% · en cours de validation par l\'administration'" />
        <x-admin.stat-card label="Refusées" :value="$fmt($rejectedCount)" icon="layers" :description="$share($rejectedCount).'% des demandes'" />
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-4">
        <section class="min-w-0 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6 xl:col-span-3">
            <nav class="-mx-1 flex gap-1 overflow-x-auto border-b border-slate-200" aria-label="Filtrer par statut">
                @foreach ($tabs as $key => $item)
                    <a href="{{ route('commercial.requests.index', array_filter(['status' => $key === 'all' ? null : $key, 'search' => $search ?: null, 'exhibitor_id' => $exhibitorId])) }}"
                       @class([
                           'inline-flex shrink-0 items-center gap-2 border-b-2 px-3 py-2.5 text-sm font-semibold',
                           'border-[#2563EB] text-[#2563EB]' => $status === $key,
                           'border-transparent text-slate-500 hover:text-slate-800' => $status !== $key,
                       ])
                       @if ($status === $key) aria-current="page" @endif>
                        {{ $item['label'] }}
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $fmt($item['count']) }}</span>
                    </a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('commercial.requests.index') }}" class="mt-5 flex flex-col gap-3 sm:flex-row">
                @if ($status !== 'all')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <label for="search" class="sr-only">Rechercher</label>
                <input id="search" name="search" value="{{ $search }}" placeholder="Rechercher un exposant, un stand, un équipement…" class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <label for="exhibitor_id" class="sr-only">Exposant</label>
                <select id="exhibitor_id" name="exhibitor_id" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Tous les exposants</option>
                    @foreach ($exhibitorOptions as $option)
                        <option value="{{ $option->id }}" @selected((int) $exhibitorId === $option->id)>{{ $option->company_name }}</option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Filtrer</button>
                @if ($search !== '' || $exhibitorId)
                    <a href="{{ route('commercial.requests.index', array_filter(['status' => $status === 'all' ? null : $status])) }}" class="self-center text-sm font-semibold text-[#2563EB] hover:underline">Réinitialiser</a>
                @endif
            </form>

            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-500">
                        <tr>
                            <th class="px-3 py-3">#</th>
                            <th class="px-3 py-3">Exposant</th>
                            <th class="px-3 py-3">Équipements demandés</th>
                            <th class="px-3 py-3">Stand</th>
                            <th class="px-3 py-3 text-right">Quantité</th>
                            <th class="px-3 py-3 text-right">Total</th>
                            <th class="px-3 py-3">Statut</th>
                            <th class="px-3 py-3">Date</th>
                            <th class="px-3 py-3 text-right"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($requests as $order)
                            @php $meta = $statusMeta[$order->status] ?? $statusMeta['pending']; @endphp
                            <tr class="align-middle hover:bg-slate-50/70">
                                <td class="px-3 py-3 font-semibold text-slate-500">#{{ $order->id }}</td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2.5">
                                        @if ($order->exhibitor?->logo)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($order->exhibitor->logo) }}" alt="" class="size-8 shrink-0 rounded-lg bg-white object-contain ring-1 ring-slate-200">
                                        @else
                                            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-xs font-bold text-slate-500" aria-hidden="true">{{ mb_strtoupper(mb_substr($order->exhibitor?->company_name ?? '?', 0, 1)) }}</span>
                                        @endif
                                        <span class="font-semibold text-slate-800">{{ $order->exhibitor?->company_name }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-slate-600">
                                    @foreach ($order->items->take(2) as $item)
                                        <p class="truncate">{{ $item->equipment_name }} <span class="text-slate-400">× {{ $item->quantity }}</span></p>
                                    @endforeach
                                    @if ($order->items->count() > 2)
                                        <p class="text-xs text-slate-400">+ {{ $order->items->count() - 2 }} autre(s)</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3"><span class="rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{{ $order->exhibitor?->stand_number ?: '—' }}</span></td>
                                <td class="px-3 py-3 text-right font-semibold text-slate-800">{{ $fmt($order->total_quantity) }}</td>
                                <td class="px-3 py-3 text-right text-slate-700">{{ $money($order->total_amount) }}</td>
                                <td class="px-3 py-3"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $meta['pill'] }}">{{ $meta['label'] }}</span></td>
                                <td class="px-3 py-3 text-xs text-slate-500">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-3 py-3 text-right"><a href="{{ route('commercial.requests.show', $order) }}" class="font-semibold text-[#2563EB] hover:underline">Voir</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-8">
                                    <x-admin.empty-state icon="box" title="Aucune demande trouvée" description="Les demandes de vos exposants apparaîtront ici. Vous pouvez aussi en créer une pour eux." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $requests->links() }}</div>
        </section>

        <aside class="space-y-6">
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                <h2 class="text-base font-bold text-slate-900">Répartition par statut</h2>
                <div class="mt-4 flex items-center gap-5 xl:flex-col 2xl:flex-row">
                    <div class="grid size-32 shrink-0 place-items-center rounded-full" style="background: {{ $donut }};" aria-hidden="true">
                        <div class="grid size-22 place-items-center rounded-full bg-white text-center">
                            <div>
                                <p class="text-xl font-bold text-slate-900">{{ $fmt($totalCount) }}</p>
                                <p class="text-[11px] text-slate-500">Demandes</p>
                            </div>
                        </div>
                    </div>
                    <dl class="w-full space-y-2.5 text-sm">
                        @foreach (['approved' => $approvedCount, 'pending' => $pendingCount, 'rejected' => $rejectedCount] as $key => $count)
                            <div class="flex items-center justify-between gap-2">
                                <dt class="flex items-center gap-2 text-slate-700"><span class="{{ $statusMeta[$key]['dot'] }} size-2.5 shrink-0 rounded-full" aria-hidden="true"></span> {{ $statusMeta[$key]['label'] }} ({{ $fmt($count) }})</dt>
                                <dd class="font-semibold text-slate-500">{{ $share($count) }}%</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </article>

            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                <h2 class="text-base font-bold text-slate-900">Dernières décisions</h2>
                <ul class="mt-3 divide-y divide-slate-100">
                    @forelse ($recentReviews as $review)
                        <li class="py-2.5">
                            <a href="{{ route('commercial.requests.show', $review) }}" class="block hover:text-[#2563EB]">
                                <p class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                                    <span class="{{ ($statusMeta[$review->status] ?? $statusMeta['pending'])['dot'] }} size-2 shrink-0 rounded-full" aria-hidden="true"></span>
                                    Demande #{{ $review->id }} {{ mb_strtolower(($statusMeta[$review->status] ?? $statusMeta['pending'])['label']) }}
                                </p>
                                <p class="mt-0.5 flex justify-between gap-2 text-xs text-slate-500">
                                    <span class="truncate">{{ $review->exhibitor?->company_name }}</span>
                                    <span class="shrink-0">{{ $review->reviewed_at?->format('d/m H:i') }}</span>
                                </p>
                            </a>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-slate-500">Aucune décision de l'administration pour le moment.</li>
                    @endforelse
                </ul>
            </article>
        </aside>
    </div>
</x-commercial.layout>
