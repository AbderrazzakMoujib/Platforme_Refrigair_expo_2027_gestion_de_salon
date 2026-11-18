
<x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">

@php
    $money = fn ($value) =>
        number_format((float) $value, 2, ',', ' ') . ' DH';

    $statusLabels = [
        'pending' => 'En attente',
        'approved' => 'Approuvée',
        'rejected' => 'Refusée',
    ];

    $statusClasses = [
        'pending' => 'bg-amber-100 text-amber-700',
        'approved' => 'bg-emerald-100 text-emerald-700',
        'rejected' => 'bg-red-100 text-red-700',
    ];

    $selectedStatus = $selectedRequest?->status ?? 'pending';

    $cards = [
        [
            'label' => 'Total des demandes',
            'value' => $totalCount,
            'symbol' => '▤',
            'color' => 'bg-blue-50 text-blue-600',
        ],
        [
            'label' => 'En attente',
            'value' => $pendingCount,
            'symbol' => '◷',
            'color' => 'bg-amber-50 text-amber-600',
        ],
        [
            'label' => 'Approuvées',
            'value' => $approvedCount,
            'symbol' => '✓',
            'color' => 'bg-emerald-50 text-emerald-600',
        ],
        [
            'label' => 'Refusées',
            'value' => $rejectedCount,
            'symbol' => '×',
            'color' => 'bg-red-50 text-red-600',
        ],
    ];
@endphp

<div class="space-y-6">

    <!-- BREADCRUMB -->
    <nav class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('exposant.index') }}"
           class="hover:text-blue-600">
            Espace exposant
        </a>
        <span>/</span>
        <span class="font-semibold text-blue-600">
            Mes demandes
        </span>
    </nav>

    <!-- PAGE HEADER -->
    <header class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">

        <div class="flex items-center gap-4">
            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <svg class="size-7"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round"
                     aria-hidden="true">
                    <rect x="5" y="3" width="14" height="18" rx="2"/>
                    <path d="M9 8h6M9 12h6M9 16h4"/>
                </svg>
            </span>

            <div>
                <h1 class="text-2xl font-extrabold text-[#0F2347] sm:text-3xl">
                    Mes demandes
                </h1>
                <p class="mt-2 text-sm text-slate-500">
                    Suivez l'état de vos demandes d'équipements
                    pour votre stand.
                </p>
            </div>
        </div>

        <!-- EVENT -->
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 to-white px-5 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-700">
                    <svg class="size-6"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         aria-hidden="true">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M7 3v4M17 3v4M3 10h18"/>
                    </svg>
                </span>

                <div>
                    <p class="font-extrabold text-[#0F2347]">
                        {{ $salon?->name ?? 'Refrigair Expo 2027' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        @if ($salon)
                            {{ $salon->start_date?->format('d/m/Y') }}
                            —
                            {{ $salon->end_date?->format('d/m/Y') }}
                            @if ($salon->venue)
                                · {{ $salon->venue }}
                            @endif
                            @if ($salon->city)
                                · {{ $salon->city }}
                            @endif
                        @else
                            Informations du salon à venir
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </header>

    @if (session('status'))
        <div role="status"
             class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <!-- STATISTICS -->
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach ($cards as $card)
            <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl text-3xl font-bold {{ $card['color'] }}">
                    {{ $card['symbol'] }}
                </span>

                <div>
                    <p class="text-sm font-semibold text-slate-600">
                        {{ $card['label'] }}
                    </p>
                    <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                        {{ $card['value'] }}
                    </p>
                </div>
            </article>
        @endforeach

    </section>

    <!-- LIST + DETAILS -->
    <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.1fr)_minmax(360px,1fr)]">

        <!-- REQUEST LIST -->
        <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-100 p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">

                    <div>
                        <h2 class="text-lg font-extrabold text-[#0F2347]">
                            Liste de mes demandes
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $requests->total() }} résultat(s)
                        </p>
                    </div>

                    <a href="{{ route('exhibitor.stand-options.index') }}"
                       class="rounded-xl bg-[#2563EB] px-4 py-3 text-sm font-bold text-white transition hover:bg-blue-700">
                        + Nouvelle demande
                    </a>
                </div>

                <!-- FILTERS -->
                <form method="GET"
                      action="{{ route('exhibitor.requests.index') }}"
                      class="mt-5 grid gap-3 sm:grid-cols-[minmax(0,1fr)_165px_auto]">

                    <div class="relative">
                        <label for="search" class="sr-only">
                            Rechercher une demande
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
                               type="search"
                               name="search"
                               value="{{ $search }}"
                               maxlength="255"
                               placeholder="N° ou équipement..."
                               class="w-full rounded-xl border border-slate-200 py-3 pl-10 pr-3 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="status" class="sr-only">
                            Statut de la demande
                        </label>

                        <select id="status"
                                name="status"
                                class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">

                            <option value="all" @selected($status === 'all')>
                                Tous les statuts
                            </option>

                            <option value="pending" @selected($status === 'pending')>
                                En attente
                            </option>

                            <option value="approved" @selected($status === 'approved')>
                                Approuvées
                            </option>

                            <option value="rejected" @selected($status === 'rejected')>
                                Refusées
                            </option>
                        </select>
                    </div>

                    <button type="submit"
                            class="rounded-xl border border-blue-200 px-4 py-3 text-sm font-bold text-blue-700 hover:bg-blue-50">
                        Filtrer
                    </button>
                </form>

                @if ($search !== '' || $status !== 'all')
                    <a href="{{ route('exhibitor.requests.index') }}"
                       class="mt-3 inline-block text-xs font-semibold text-blue-700 hover:underline">
                        Réinitialiser les filtres
                    </a>
                @endif
            </div>

            <!-- TABLE -->
            <div class="overflow-x-auto">

                <table class="min-w-full text-left text-sm">

                    <thead class="bg-[#F0F5FF] text-xs font-bold text-[#0F2347]">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-4">N° demande</th>
                            <th class="whitespace-nowrap px-4 py-4">Date</th>
                            <th class="whitespace-nowrap px-4 py-4">Options</th>
                            <th class="whitespace-nowrap px-4 py-4">Total</th>
                            <th class="whitespace-nowrap px-4 py-4">Statut</th>
                            <th class="px-4 py-4 text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse ($requests as $order)
                            @php
                                $isSelected = $selectedRequest
                                    && $selectedRequest->id === $order->id;
                            @endphp

                            <tr @class([
                                'transition hover:bg-blue-50/50',
                                'bg-blue-50' => $isSelected,
                            ])>
                                <td class="whitespace-nowrap px-4 py-4 font-extrabold text-[#0F2347]">
                                    #{{ str_pad((string) $order->id, 4, '0', STR_PAD_LEFT) }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 text-xs text-slate-600">
                                    {{ $order->created_at?->format('d/m/Y') }}
                                    <span class="block text-slate-400">
                                        {{ $order->created_at?->format('H:i') }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 font-semibold text-slate-700">
                                    {{ $order->items_count }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-4 font-bold text-[#0F2347]">
                                    {{ $money($order->total_amount) }}
                                </td>

                                <td class="px-4 py-4">
                                    <span class="inline-flex whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-bold {{ $statusClasses[$order->status] ?? $statusClasses['pending'] }}">
                                        {{ $statusLabels[$order->status] ?? 'En attente' }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <a href="{{ route(
                                        'exhibitor.requests.index',
                                        array_merge(
                                            request()->except(['selected']),
                                            ['selected' => $order->id]
                                        )
                                    ) }}"
                                       class="inline-flex rounded-lg border border-blue-200 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-50"
                                       aria-label="Voir la demande {{ $order->id }}">
                                        Voir
                                    </a>
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center">
                                    <p class="font-bold text-[#0F2347]">
                                        Aucune demande trouvée
                                    </p>
                                    <p class="mt-2 text-sm text-slate-500">
                                        Vos demandes d'équipements apparaîtront ici.
                                    </p>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div class="border-t border-slate-100 px-5 py-4">

                <p class="mb-3 text-xs text-slate-500">
                    @if ($requests->total() > 0)
                        Affichage de {{ $requests->firstItem() }}
                        à {{ $requests->lastItem() }}
                        sur {{ $requests->total() }} demandes
                    @else
                        Aucun résultat
                    @endif
                </p>

                @if ($requests->hasPages())
                    {{ $requests->links() }}
                @endif
            </div>
        </section>

        <!-- DETAILS -->
        <aside id="details"
               class="min-w-0 scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5">
                <h2 class="text-lg font-extrabold text-[#0F2347]">
                    @if ($selectedRequest)
                        Détails de la demande
                        #{{ str_pad((string) $selectedRequest->id, 4, '0', STR_PAD_LEFT) }}
                    @else
                        Détails de la demande
                    @endif
                </h2>

                <a href="{{ route('exhibitor.requests.index', request()->except('selected')) }}"
                   class="text-slate-400 hover:text-slate-700"
                   title="Retour à la liste">
                    <svg class="size-5" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor"
                         stroke-width="1.8" aria-hidden="true">
                        <path d="M18 6 6 18M6 6l12 12"/>
                    </svg>
                </a>
            </div>

            @if ($selectedRequest)

                <div class="space-y-5 p-5">

                    <!-- STATUS -->
                    <div @class([
                        'rounded-xl p-4',
                        'bg-emerald-50' => $selectedStatus === 'approved',
                        'bg-red-50' => $selectedStatus === 'rejected',
                        'bg-amber-50' => $selectedStatus === 'pending',
                    ])>
                        <p @class([
                            'font-extrabold',
                            'text-emerald-700' => $selectedStatus === 'approved',
                            'text-red-700' => $selectedStatus === 'rejected',
                            'text-amber-700' => $selectedStatus === 'pending',
                        ])>
                            Demande {{ strtolower($statusLabels[$selectedStatus] ?? 'En attente') }}
                        </p>

                        <p class="mt-2 text-xs text-slate-600">
                            @if ($selectedStatus === 'approved')
                                Demande approuvée
                                @if ($selectedRequest->reviewed_at)
                                    le {{ $selectedRequest->reviewed_at->format('d/m/Y à H:i') }}
                                @endif
                            @elseif ($selectedStatus === 'rejected')
                                Demande refusée
                                @if ($selectedRequest->reviewed_at)
                                    le {{ $selectedRequest->reviewed_at->format('d/m/Y à H:i') }}
                                @endif
                            @else
                                Votre demande est en attente de validation.
                            @endif
                        </p>
                    </div>

                    <!-- METADATA -->
                    <div class="grid grid-cols-2 gap-3 text-sm">

                        <div class="rounded-xl bg-[#F5F7FB] p-4">
                            <p class="text-xs text-slate-500">
                                Date de demande
                            </p>
                            <p class="mt-2 font-bold text-[#0F2347]">
                                {{ $selectedRequest->created_at?->format('d/m/Y à H:i') }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-[#F5F7FB] p-4">
                            <p class="text-xs text-slate-500">
                                Nombre d'options
                            </p>
                            <p class="mt-2 font-bold text-[#0F2347]">
                                {{ $selectedRequest->items->count() }}
                            </p>
                        </div>

                    </div>

                    <!-- ITEMS -->
                    <div>
                        <h3 class="font-extrabold text-[#0F2347]">
                            Équipements demandés
                        </h3>

                        <div class="mt-3 overflow-hidden rounded-xl border border-slate-200">

                            <div class="divide-y divide-slate-100">

                                @forelse ($selectedRequest->items as $item)

                                    @php
                                        $itemImage = $item->equipment?->image_path
                                            ? \Illuminate\Support\Facades\Storage::disk('public')->url(
                                                $item->equipment->image_path
                                            )
                                            : null;
                                    @endphp

                                    <div class="flex items-center gap-3 p-3">

                                        @if ($itemImage)
                                            <img src="{{ $itemImage }}"
                                                 alt="{{ $item->equipment_name }}"
                                                 class="size-14 shrink-0 rounded-lg bg-slate-50 object-contain p-1">
                                        @else
                                            <span class="grid size-14 shrink-0 place-items-center rounded-lg bg-blue-50 font-bold text-blue-700">
                                                ▣
                                            </span>
                                        @endif

                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-bold text-[#0F2347]">
                                                {{ $item->equipment_name }}
                                            </p>

                                            <p class="mt-1 text-xs text-slate-500">
                                                {{ $item->quantity }} × {{ $money($item->unit_price) }}
                                            </p>
                                        </div>

                                        <span class="text-right text-xs font-bold text-[#0F2347]">
                                            {{ $money($item->subtotal) }}
                                        </span>

                                    </div>

                                @empty
                                    <p class="p-4 text-sm text-slate-500">
                                        Aucun équipement enregistré.
                                    </p>
                                @endforelse

                            </div>

                        </div>
                    </div>

                    <!-- TOTAL -->
                    <div class="flex items-center justify-between gap-3 rounded-xl bg-blue-50 p-4">
                        <span class="text-sm font-bold text-[#0F2347]">
                            Montant total
                        </span>
                        <strong class="text-lg font-extrabold text-blue-700">
                            {{ $money($selectedRequest->total_amount) }}
                        </strong>
                    </div>

                    <p class="text-xs text-slate-500">
                        Montant enregistré lors de la demande.
                        TVA non détaillée.
                    </p>

                    <!-- NOTES -->
                    @if ($selectedRequest->notes)
                        <div class="rounded-xl bg-[#F5F7FB] p-4">
                            <h3 class="text-sm font-bold text-[#0F2347]">
                                Commentaire de la demande
                            </h3>

                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $selectedRequest->notes }}</p>
                        </div>
                    @endif

                    <!-- REJECTION -->
                    @if (
                        $selectedStatus === 'rejected'
                        && $selectedRequest->rejection_reason
                    )
                        <div class="rounded-xl border border-red-100 bg-red-50 p-4">
                            <h3 class="text-sm font-bold text-red-700">
                                Motif du refus
                            </h3>

                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-red-700">{{ $selectedRequest->rejection_reason }}</p>
                        </div>
                    @endif

                    <a href="{{ route('exhibitor.stand-options.index') }}"
                       class="block rounded-xl border border-blue-200 px-5 py-3 text-center text-sm font-bold text-blue-700 hover:bg-blue-50">
                        Consulter les options du stand
                    </a>

                </div>

            @else
                <div class="px-6 py-16 text-center">
                    <p class="font-bold text-[#0F2347]">
                        Aucune demande sélectionnée
                    </p>
                    <p class="mt-2 text-sm text-slate-500">
                        Sélectionnez une demande pour voir ses détails.
                    </p>
                </div>
            @endif

        </aside>

    </div>

</div>

</x-exhibitor.layout>
