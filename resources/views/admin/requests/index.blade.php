
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

@php
    $currentStatus = request('status', '');

    $statuses = [
        'pending' => [
            'label' => 'En attente',
            'color' => 'bg-amber-50 text-amber-700',
        ],
        'approved' => [
            'label' => 'Approuvée',
            'color' => 'bg-emerald-50 text-emerald-700',
        ],
        'rejected' => [
            'label' => 'Refusée',
            'color' => 'bg-red-50 text-red-700',
        ],
    ];

    $stats = [
        ['label' => 'Total demandes', 'value' => $totalRequests],
        ['label' => 'En attente', 'value' => $pendingCount],
        ['label' => 'Approuvées', 'value' => $approvedCount],
        ['label' => 'Refusées', 'value' => $rejectedCount],
        ['label' => 'Unités demandées', 'value' => $totalRequestedQuantity],
    ];
@endphp

<div class="space-y-7">

    {{-- BREADCRUMB --}}
    <nav class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.index') }}"
           class="hover:text-blue-600">
            Administration
        </a>

        <span>/</span>

        <span class="font-semibold text-[#0F2347]">
            Demandes d'équipements
        </span>
    </nav>

    {{-- HEADER --}}
    <header class="flex flex-wrap items-center justify-between gap-4">

        <div>
            <h1 class="text-2xl font-extrabold text-[#0F2347] sm:text-3xl">
                Demandes d'équipements
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Consultez et suivez les demandes d'équipements
                des exposants.
            </p>
        </div>

        <span class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-bold text-blue-700">
            Suivi administratif
        </span>

    </header>

    {{-- SUCCESS --}}
    @if (session('status'))
        <div role="status"
             class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    {{-- ERRORS --}}
    @if ($errors->any())
        <div role="alert"
             class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- READ ONLY INFORMATION --}}
    <section class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
        <div class="flex items-start gap-3">
            <span class="text-xl text-blue-600">ⓘ</span>

            <div>
                <h2 class="font-bold text-[#0F2347]">
                    Consultation uniquement
                </h2>

                <p class="mt-1 text-sm text-slate-600">
                    Les demandes sont approuvées ou refusées par
                    le commercial responsable de chaque exposant.
                    L'administration peut suivre les décisions.
                </p>
            </div>
        </div>
    </section>

    {{-- STATISTICS --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">

        @foreach ($stats as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <p class="text-xs font-semibold text-slate-500">
                    {{ $stat['label'] }}
                </p>

                <p class="mt-3 text-3xl font-extrabold text-[#0F2347]">
                    {{ number_format($stat['value'], 0, ',', ' ') }}
                </p>

            </article>
        @endforeach

    </section>

    {{-- STATUS TABS --}}
    <div class="flex flex-wrap gap-2">

        @foreach ([
            ['value' => '', 'label' => 'Toutes', 'count' => $totalRequests],
            ['value' => 'pending', 'label' => 'En attente', 'count' => $pendingCount],
            ['value' => 'approved', 'label' => 'Approuvées', 'count' => $approvedCount],
            ['value' => 'rejected', 'label' => 'Refusées', 'count' => $rejectedCount],
        ] as $tab)

            <a
                href="{{ route(
                    'admin.demandes.index',
                    array_merge(
                        request()->except(['status', 'page']),
                        $tab['value'] !== '' ? ['status' => $tab['value']] : []
                    )
                ) }}"
                @class([
                    'rounded-xl px-4 py-2.5 text-xs font-bold transition',
                    'bg-[#2563EB] text-white' => $currentStatus === $tab['value'],
                    'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' => $currentStatus !== $tab['value'],
                ])
            >
                {{ $tab['label'] }} ({{ $tab['count'] }})
            </a>

        @endforeach

    </div>

    {{-- FILTERS --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

        <form method="GET"
              action="{{ route('admin.demandes.index') }}"
              class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">

            <div>
                <label for="search"
                       class="mb-2 block text-xs font-bold text-slate-600">
                    Recherche
                </label>

                <input
                    id="search"
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Entreprise, équipement..."
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                >
            </div>

            <div>
                <label for="exhibitor_id"
                       class="mb-2 block text-xs font-bold text-slate-600">
                    Exposant
                </label>

                <select id="exhibitor_id"
                        name="exhibitor_id"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">

                    <option value="">Tous les exposants</option>

                    @foreach ($exhibitors as $exhibitor)
                        <option value="{{ $exhibitor->id }}"
                            @selected((string) request('exhibitor_id') === (string) $exhibitor->id)>
                            {{ $exhibitor->company_name }}
                        </option>
                    @endforeach

                </select>
            </div>

            <div>
                <label for="status"
                       class="mb-2 block text-xs font-bold text-slate-600">
                    Statut
                </label>

                <select id="status"
                        name="status"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">

                    <option value="">Tous</option>
                    <option value="pending" @selected($currentStatus === 'pending')>
                        En attente
                    </option>
                    <option value="approved" @selected($currentStatus === 'approved')>
                        Approuvée
                    </option>
                    <option value="rejected" @selected($currentStatus === 'rejected')>
                        Refusée
                    </option>

                </select>
            </div>

            <div>
                <label for="date_from"
                       class="mb-2 block text-xs font-bold text-slate-600">
                    Date début
                </label>

                <input
                    id="date_from"
                    type="date"
                    name="date_from"
                    value="{{ request('date_from') }}"
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                >
            </div>

            <div>
                <label for="date_to"
                       class="mb-2 block text-xs font-bold text-slate-600">
                    Date fin
                </label>

                <input
                    id="date_to"
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                >
            </div>

            <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-5">

                <button
                    type="submit"
                    class="rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white hover:bg-blue-700"
                >
                    Filtrer
                </button>

                <a
                    href="{{ route('admin.demandes.index') }}"
                    class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50"
                >
                    Réinitialiser
                </a>

            </div>

        </form>

    </section>

    {{-- REQUESTS TABLE --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">

            <div>
                <h2 class="text-lg font-extrabold text-[#0F2347]">
                    Liste des demandes
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Demandes de tous les exposants.
                </p>
            </div>

            <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">
                {{ $requests->total() }} résultat(s)
            </span>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full min-w-[1050px] text-left text-sm">

                <thead class="bg-[#F5F7FB] text-xs font-bold uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Référence</th>
                        <th class="px-5 py-4">Exposant</th>
                        <th class="px-5 py-4">Équipements</th>
                        <th class="px-5 py-4">Quantité</th>
                        <th class="px-5 py-4">Montant</th>
                        <th class="px-5 py-4">Date</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse ($requests as $order)

                        @php
                            $exhibitor = $order->exhibitor;
                            $firstItem = $order->items->first();
                            $status = $statuses[$order->status]
                                ?? $statuses['pending'];
                        @endphp

                        <tr class="transition hover:bg-slate-50">

                            {{-- REFERENCE --}}
                            <td class="px-5 py-5">
                                <a href="{{ route('admin.demandes.show', $order) }}"
                                   class="font-bold text-blue-600 hover:underline">
                                    DM-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>

                            {{-- EXHIBITOR --}}
                            <td class="px-5 py-5">

                                <p class="font-bold text-[#0F2347]">
                                    {{ $exhibitor?->company_name ?? 'Exposant supprimé' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Stand {{ $exhibitor?->stand_number ?: 'Non attribué' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Commercial :
                                    {{ $exhibitor?->commercial?->name ?? 'Non attribué' }}
                                </p>

                            </td>

                            {{-- EQUIPMENT --}}
                            <td class="px-5 py-5">

                                <p class="font-semibold text-[#0F2347]">
                                    {{ $firstItem?->equipment_name ?? '—' }}
                                </p>

                                @if ($order->items_count > 1)
                                    <p class="mt-1 text-xs text-slate-500">
                                        + {{ $order->items_count - 1 }} autre(s)
                                    </p>
                                @endif

                            </td>

                            {{-- QUANTITY --}}
                            <td class="px-5 py-5 font-semibold text-[#0F2347]">
                                {{ $order->requested_quantity ?? 0 }}
                            </td>

                            {{-- TOTAL --}}
                            <td class="whitespace-nowrap px-5 py-5 font-extrabold text-[#0F2347]">
                                {{ number_format((float) $order->total_amount, 2, ',', ' ') }}
                                DH
                            </td>

                            {{-- DATE --}}
                            <td class="whitespace-nowrap px-5 py-5">
                                <p class="font-semibold text-slate-700">
                                    {{ $order->created_at?->format('d/m/Y') }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $order->created_at?->format('H:i') }}
                                </p>
                            </td>

                            {{-- STATUS --}}
                            <td class="px-5 py-5">
                                <span class="inline-flex rounded-full px-3 py-1.5 text-xs font-bold {{ $status['color'] }}">
                                    {{ $status['label'] }}
                                </span>
                            </td>

                            {{-- VIEW ONLY --}}
                            <td class="px-5 py-5 text-right">

                                <a
                                    href="{{ route('admin.demandes.show', $order) }}"
                                    title="Consulter la demande"
                                    aria-label="Consulter la demande {{ $order->id }}"
                                    class="inline-flex items-center gap-2 rounded-xl border border-blue-200 px-4 py-2.5 text-xs font-bold text-blue-700 transition hover:bg-blue-50"
                                >
                                    <svg class="size-4"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8">
                                        <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>

                                    Consulter
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center">

                                <p class="font-bold text-[#0F2347]">
                                    Aucune demande trouvée
                                </p>

                                <p class="mt-2 text-sm text-slate-500">
                                    Aucune demande ne correspond à votre recherche.
                                </p>

                                <a href="{{ route('admin.demandes.index') }}"
                                   class="mt-4 inline-block font-semibold text-blue-600 hover:underline">
                                    Réinitialiser
                                </a>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        @if ($requests->total() > 0)

            <div class="border-t border-slate-100 px-6 py-5">

                <p class="mb-4 text-xs text-slate-500">
                    Affichage de {{ $requests->firstItem() }}
                    à {{ $requests->lastItem() }}
                    sur {{ $requests->total() }} demandes
                </p>

                @if ($requests->hasPages())
                    {{ $requests->links() }}
                @endif

            </div>

        @endif

    </section>

</div>

</x-admin.layout>
