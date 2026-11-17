
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

@php
    $currentStatus = request('status', '');

    $statuses = [
        'pending' => [
            'label' => 'En attente',
            'color' => 'bg-amber-50 text-amber-700',
            'dot' => 'bg-amber-500',
        ],
        'approved' => [
            'label' => 'Approuvée',
            'color' => 'bg-emerald-50 text-emerald-700',
            'dot' => 'bg-emerald-500',
        ],
        'rejected' => [
            'label' => 'Refusée',
            'color' => 'bg-red-50 text-red-700',
            'dot' => 'bg-red-500',
        ],
    ];
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
            Demandes d'équipements
        </span>
    </nav>

    <!-- HEADER -->
    <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div class="flex items-center gap-4">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <x-admin.icon name="clipboard-check" class="size-7"/>
            </span>

            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                    Demandes d'équipements
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Suivez, traitez et validez les demandes d'équipements des exposants.
                </p>
            </div>
        </div>

        <span class="inline-flex w-fit items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold text-amber-700">
            <span class="size-2 rounded-full bg-amber-500"></span>
            {{ $pendingCount }} demande(s) à traiter
        </span>

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
             class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- STATISTICS -->
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5"
             aria-label="Statistiques des demandes">

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-violet-100 text-violet-600">
                    <x-admin.icon name="clipboard-check" class="size-6"/>
                </span>
                <div>
                    <p class="text-xs font-medium text-slate-500">Total demandes</p>
                    <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                        {{ number_format($totalRequests, 0, ',', ' ') }}
                    </p>
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-600">
                    <x-admin.icon name="inbox" class="size-6"/>
                </span>
                <div>
                    <p class="text-xs font-medium text-slate-500">En attente</p>
                    <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                        {{ number_format($pendingCount, 0, ',', ' ') }}
                    </p>
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-600">
                    <x-admin.icon name="clipboard-check" class="size-6"/>
                </span>
                <div>
                    <p class="text-xs font-medium text-slate-500">Approuvées</p>
                    <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                        {{ number_format($approvedCount, 0, ',', ' ') }}
                    </p>
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-red-100 text-red-600">
                    <x-admin.icon name="inbox" class="size-6"/>
                </span>
                <div>
                    <p class="text-xs font-medium text-slate-500">Refusées</p>
                    <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                        {{ number_format($rejectedCount, 0, ',', ' ') }}
                    </p>
                </div>
            </div>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-600">
                    <x-admin.icon name="box" class="size-6"/>
                </span>
                <div>
                    <p class="text-xs font-medium text-slate-500">Unités demandées</p>
                    <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                        {{ number_format($totalRequestedQuantity, 0, ',', ' ') }}
                    </p>
                </div>
            </div>
        </article>

    </section>

    <!-- REQUESTS CARD -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <!-- TABS -->
        <div class="border-b border-slate-100 px-5 pt-5 sm:px-6">

            <div class="flex flex-wrap gap-2">

                @foreach ([
                    ['value' => '', 'label' => 'Toutes', 'count' => $totalRequests],
                    ['value' => 'pending', 'label' => 'En attente', 'count' => $pendingCount],
                    ['value' => 'approved', 'label' => 'Approuvées', 'count' => $approvedCount],
                    ['value' => 'rejected', 'label' => 'Refusées', 'count' => $rejectedCount],
                ] as $tab)

                    <a href="{{ route(
                            'admin.demandes.index',
                            array_merge(
                                request()->except(['status', 'page']),
                                $tab['value'] !== '' ? ['status' => $tab['value']] : []
                            )
                        ) }}"
                       @class([
                           'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                           'bg-[#2563EB] text-white' => $currentStatus === $tab['value'],
                           'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' => $currentStatus !== $tab['value'],
                       ])>

                        @if ($tab['value'] === 'pending')
                            <span class="size-2 rounded-full bg-amber-500"></span>
                        @elseif ($tab['value'] === 'approved')
                            <span class="size-2 rounded-full bg-emerald-500"></span>
                        @elseif ($tab['value'] === 'rejected')
                            <span class="size-2 rounded-full bg-red-500"></span>
                        @endif

                        {{ $tab['label'] }} ({{ $tab['count'] }})

                    </a>

                @endforeach

            </div>

            <!-- FILTERS -->
            <form method="GET"
                  action="{{ route('admin.demandes.index') }}"
                  class="mt-5 grid gap-3 pb-5 md:grid-cols-2 xl:grid-cols-6">

                <!-- SEARCH -->
                <div class="relative xl:col-span-2">

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
                           maxlength="255"
                           value="{{ request('search') }}"
                           placeholder="Exposant, équipement, numéro..."
                           class="w-full rounded-xl border border-slate-200 py-3 pl-10 pr-4 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- EXHIBITOR -->
                <div>
                    <label for="exhibitor_id" class="sr-only">Exposant</label>

                    <select id="exhibitor_id"
                            name="exhibitor_id"
                            class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">

                        <option value="">Tous les exposants</option>

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
                    <label for="status" class="sr-only">Statut</label>

                    <select id="status"
                            name="status"
                            class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">

                        <option value="">Tous les statuts</option>

                        <option value="pending"
                            @selected($currentStatus === 'pending')>
                            En attente
                        </option>

                        <option value="approved"
                            @selected($currentStatus === 'approved')>
                            Approuvée
                        </option>

                        <option value="rejected"
                            @selected($currentStatus === 'rejected')>
                            Refusée
                        </option>

                    </select>
                </div>

                <!-- DATE FROM -->
                <div>
                    <label for="date_from"
                           class="mb-1 block text-xs font-medium text-slate-500">
                        Du
                    </label>

                    <input id="date_from"
                           name="date_from"
                           type="date"
                           value="{{ request('date_from') }}"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                </div>

                <!-- DATE TO -->
                <div>
                    <label for="date_to"
                           class="mb-1 block text-xs font-medium text-slate-500">
                        Au
                    </label>

                    <input id="date_to"
                           name="date_to"
                           type="date"
                           value="{{ request('date_to') }}"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                </div>

                <div class="flex flex-wrap gap-3 xl:col-span-6">

                    <button type="submit"
                            class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                        Filtrer
                    </button>

                    <a href="{{ route('admin.demandes.index') }}"
                       class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Réinitialiser
                    </a>

                </div>

            </form>
        </div>

        <!-- LIST HEADER -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-5 sm:px-6">

            <div>
                <h2 class="text-lg font-bold text-[#0F2347]">
                    Liste des demandes
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Demandes reçues des exposants
                </p>
            </div>

            <span class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">
                {{ $requests->total() }} résultat(s)
            </span>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] text-left text-sm">

                <thead class="bg-[#F5F7FB] text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Référence</th>
                        <th class="px-5 py-4">Exposant</th>
                        <th class="px-5 py-4">Équipements demandés</th>
                        <th class="px-5 py-4">Quantité</th>
                        <th class="px-5 py-4">Montant</th>
                        <th class="px-5 py-4">Date</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse ($requests as $order)

                        @php
                            $exhibitor = $order->exhibitor;
                            $firstItem = $order->items->first();

                            $otherItems = max(
                                0,
                                $order->items_count - 1
                            );

                            $status = $statuses[$order->status]
                                ?? $statuses['pending'];

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

                            <!-- REFERENCE -->
                            <td class="px-5 py-5">
                                <a href="{{ route('admin.demandes.show', $order) }}"
                                   class="whitespace-nowrap font-extrabold text-blue-700 hover:underline">
                                    DM-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>

                            <!-- EXHIBITOR -->
                            <td class="px-5 py-5">

                                @if ($exhibitor)

                                    <div class="flex items-center gap-3">

                                        @if ($exhibitor->logo)

                                            <div class="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white p-1">

                                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($exhibitor->logo) }}"
                                                     alt="Logo {{ $exhibitor->company_name }}"
                                                     class="max-h-full max-w-full object-contain"
                                                     loading="lazy">
                                            </div>

                                        @else

                                            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-xs font-bold text-blue-700">
                                                {{ $companyInitials ?: 'E' }}
                                            </span>

                                        @endif

                                        <div class="min-w-0">

                                            <a href="{{ route('admin.exposants.show', $exhibitor) }}"
                                               class="font-bold text-[#0F2347] hover:text-blue-600">
                                                {{ $exhibitor->company_name }}
                                            </a>

                                            <p class="mt-1 text-xs text-slate-500">
                                                Stand {{ $exhibitor->stand_number ?: 'Non attribué' }}
                                            </p>

                                            <p class="mt-1 text-xs text-slate-400">
                                                {{ $exhibitor->commercial?->name ?: 'Sans commercial' }}
                                            </p>

                                        </div>

                                    </div>

                                @else

                                    <span class="text-slate-400">
                                        Exposant indisponible
                                    </span>

                                @endif
                            </td>

                            <!-- EQUIPMENT -->
                            <td class="px-5 py-5">

                                @if ($firstItem)

                                    <div class="flex items-center gap-3">

                                        @if ($firstItem->equipment?->image_path)

                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($firstItem->equipment->image_path) }}"
                                                 alt="{{ $firstItem->equipment_name }}"
                                                 class="size-11 shrink-0 rounded-lg border border-slate-200 bg-white object-contain p-1"
                                                 loading="lazy">

                                        @else

                                            <span class="grid size-11 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-500">
                                                <x-admin.icon name="box" class="size-5"/>
                                            </span>

                                        @endif

                                        <div>
                                            <p class="font-semibold text-slate-700">
                                                {{ $firstItem->equipment_name }}
                                            </p>

                                            @if ($otherItems > 0)
                                                <span class="mt-1 inline-flex rounded-lg bg-blue-50 px-2 py-1 text-xs font-bold text-blue-600">
                                                    + {{ $otherItems }} autre(s)
                                                </span>
                                            @endif
                                        </div>

                                    </div>

                                @else

                                    <span class="text-slate-400">
                                        Aucun équipement
                                    </span>

                                @endif

                            </td>

                            <!-- QUANTITY -->
                            <td class="px-5 py-5">
                                <span class="font-bold text-[#0F2347]">
                                    {{ $order->requested_quantity ?? 0 }}
                                </span>
                            </td>

                            <!-- AMOUNT -->
                            <td class="px-5 py-5">
                                <span class="whitespace-nowrap font-bold text-slate-700">
                                    {{ number_format((float) $order->total_amount, 2, ',', ' ') }} DH
                                </span>
                            </td>

                            <!-- DATE -->
                            <td class="px-5 py-5 text-slate-600">
                                <span class="whitespace-nowrap">
                                    {{ $order->created_at?->format('d/m/Y') }}
                                </span>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $order->created_at?->format('H:i') }}
                                </p>
                            </td>

                            <!-- STATUS -->
                            <td class="px-5 py-5">

                                <span class="inline-flex items-center gap-2 whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-bold {{ $status['color'] }}">

                                    <span class="size-2 rounded-full {{ $status['dot'] }}"></span>

                                    {{ $status['label'] }}

                                </span>

                            </td>

                            <!-- ACTIONS -->
                            <td class="px-5 py-5">

                                <div class="flex justify-end gap-2">

                                    <!-- VIEW -->
                                    <a href="{{ route('admin.demandes.show', $order) }}"
                                       title="Voir les détails"
                                       aria-label="Voir demande {{ $order->id }}"
                                       class="grid size-10 place-items-center rounded-lg border border-slate-200 text-blue-600 hover:bg-blue-50">

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

                                    @if ($order->status === 'pending')

                                        <!-- APPROVE -->
                                        <form method="POST"
                                              action="{{ route('admin.demandes.approve', $order) }}"
                                              onsubmit="return confirm('Approuver cette demande ? Le stock sera déduit automatiquement.')">

                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    title="Approuver"
                                                    aria-label="Approuver demande {{ $order->id }}"
                                                    class="grid size-10 place-items-center rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50">

                                                <svg class="size-5"
                                                     viewBox="0 0 24 24"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     stroke-width="1.8"
                                                     stroke-linecap="round"
                                                     stroke-linejoin="round"
                                                     aria-hidden="true">
                                                    <circle cx="12" cy="12" r="9"/>
                                                    <path d="m7 12 3 3 5-6"/>
                                                </svg>

                                            </button>

                                        </form>

                                        <!-- REJECT / DETAIL -->
                                        <a href="{{ route('admin.demandes.show', $order) }}#refuser-demande"
                                           title="Refuser la demande"
                                           aria-label="Refuser demande {{ $order->id }}"
                                           class="grid size-10 place-items-center rounded-lg border border-red-200 text-red-600 hover:bg-red-50">

                                            <svg class="size-5"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="1.8"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round"
                                                 aria-hidden="true">
                                                <circle cx="12" cy="12" r="9"/>
                                                <path d="m9 9 6 6m0-6-6 6"/>
                                            </svg>

                                        </a>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">

                                <div class="mx-auto grid size-16 place-items-center rounded-2xl bg-blue-50 text-blue-600">
                                    <x-admin.icon name="clipboard-check" class="size-8"/>
                                </div>

                                <h3 class="mt-4 font-bold text-[#0F2347]">
                                    Aucune demande trouvée
                                </h3>

                                <p class="mt-2 text-sm text-slate-500">
                                    Modifiez les filtres pour voir d'autres demandes.
                                </p>

                                <a href="{{ route('admin.demandes.index') }}"
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

</div>

</x-admin.layout>
