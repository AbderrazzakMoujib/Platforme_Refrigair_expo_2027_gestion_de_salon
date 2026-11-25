
<x-responsable-conferences.layout :user="auth()->user()">

@php
    $total = max(1, $totalConfirmed);

    $stats = [
        [
            'label' => 'Total confirmées',
            'value' => $totalConfirmed,
            'icon' => 'calendar',
            'bg' => 'bg-blue-100',
            'color' => 'text-blue-700',
            'percentage' => null,
        ],
        [
            'label' => 'Conférences',
            'value' => $conferenceCount,
            'icon' => 'clipboard-check',
            'bg' => 'bg-emerald-100',
            'color' => 'text-emerald-700',
            'percentage' => round($conferenceCount * 100 / $total, 1),
        ],
        [
            'label' => 'Lounge',
            'value' => $loungeCount,
            'icon' => 'box',
            'bg' => 'bg-violet-100',
            'color' => 'text-violet-700',
            'percentage' => round($loungeCount * 100 / $total, 1),
        ],
        [
            'label' => 'Entreprises concernées',
            'value' => $companiesCount,
            'icon' => 'users',
            'bg' => 'bg-blue-100',
            'color' => 'text-blue-700',
            'percentage' => null,
        ],
    ];

    $currentSearch = $filters['search'] ?? '';
    $currentType = $filters['type'] ?? '';
    $currentSpace = $filters['space_id'] ?? '';
    $currentPeriod = $filters['period'] ?? 'all';
@endphp

<div class="space-y-6">

    {{-- HEADER --}}

    <section>
        <div class="flex items-center gap-2 text-xs font-bold text-blue-600">
            <span>Conférences & Lounge</span>
            <span class="text-slate-300">/</span>
            <span>Réservations confirmées</span>
        </div>

        <div class="mt-4 flex items-center gap-4">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <x-admin.icon
                    name="clipboard-check"
                    class="size-7"
                />
            </span>

            <div>
                <h1 class="text-2xl font-black tracking-tight text-[#0F2347] sm:text-3xl">
                    Réservations confirmées
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Liste des réservations validées et attribuées
                    aux créneaux du salon REFRIGAIR EXPO 2027.
                </p>
            </div>
        </div>
    </section>

    {{-- STATISTICS --}}

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach($stats as $stat)

            <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">

                <div class="flex items-start justify-between gap-3">

                    <span class="grid size-12 place-items-center rounded-xl {{ $stat['bg'] }} {{ $stat['color'] }}">

                        <x-admin.icon
                            :name="$stat['icon']"
                            class="size-6"
                        />

                    </span>

                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-bold text-blue-700">
                        REFRIGAIR 2027
                    </span>
                </div>

                <p class="mt-5 text-sm font-bold text-[#0F2347]">
                    {{ $stat['label'] }}
                </p>

                <div class="mt-2 flex items-center gap-3">

                    <span class="text-3xl font-black text-[#0F2347]">
                        {{ $stat['value'] }}
                    </span>

                    @if($stat['percentage'] !== null)

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold {{ $stat['color'] }}">
                            {{ number_format($stat['percentage'], 1, ',', ' ') }}%
                        </span>

                    @endif
                </div>
            </article>

        @endforeach
    </section>

    {{-- RESERVATIONS TABLE --}}

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        {{-- SECTION HEADER --}}

        <div class="flex flex-wrap items-center justify-between gap-4 p-5 sm:p-6">

            <div class="flex items-center gap-3">

                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-600">

                    <x-admin.icon
                        name="clipboard-check"
                        class="size-6"
                    />
                </span>

                <div>
                    <h2 class="text-lg font-black text-[#0F2347]">
                        Liste des réservations confirmées
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Consultez les créneaux attribués et
                        les entreprises bénéficiaires.
                    </p>
                </div>
            </div>

            <a href="{{ route('responsable-conferences.confirmed.export', request()->only([
                    'search',
                    'type',
                    'space_id',
                    'period',
                ])) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-blue-500 bg-white px-4 py-3 text-sm font-bold text-blue-600 transition hover:bg-blue-50">

                <x-admin.icon name="clipboard-check" class="size-5"/>

                Exporter CSV (Excel)
            </a>
        </div>

        {{-- FILTERS --}}

        <form method="GET"
              action="{{ route('responsable-conferences.confirmed') }}"
              class="border-b border-slate-100 px-4 pb-5 sm:px-6">

            <div class="grid gap-3 rounded-xl bg-[#F1F6FD] p-4 md:grid-cols-2 xl:grid-cols-[1.4fr_1fr_1fr_1fr_auto]">

                <div>
                    <label for="confirmed-search"
                           class="mb-2 block text-xs font-bold text-[#0F2347]">
                        Rechercher
                    </label>

                    <input
                        id="confirmed-search"
                        name="search"
                        type="search"
                        maxlength="150"
                        value="{{ $currentSearch }}"
                        placeholder="Entreprise, sujet, commercial..."
                        class="h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm focus:border-blue-500 focus:ring-blue-200"
                    >
                </div>

                <div>
                    <label for="confirmed-type"
                           class="mb-2 block text-xs font-bold text-[#0F2347]">
                        Type
                    </label>

                    <select id="confirmed-type"
                            name="type"
                            class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-blue-500">

                        <option value="">Tous les types</option>

                        <option value="conference"
                                @selected($currentType === 'conference')>
                            Conférence
                        </option>

                        <option value="lounge"
                                @selected($currentType === 'lounge')>
                            Lounge
                        </option>
                    </select>
                </div>

                <div>
                    <label for="confirmed-space"
                           class="mb-2 block text-xs font-bold text-[#0F2347]">
                        Espace
                    </label>

                    <select id="confirmed-space"
                            name="space_id"
                            class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-blue-500">

                        <option value="">Tous les espaces</option>

                        @foreach($spaces as $space)

                            <option
                                value="{{ $space->id }}"
                                @selected((string) $currentSpace === (string) $space->id)>

                                {{ $space->name ?: $space->type->label() }}
                            </option>

                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="confirmed-period"
                           class="mb-2 block text-xs font-bold text-[#0F2347]">
                        Période
                    </label>

                    <select id="confirmed-period"
                            name="period"
                            class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm focus:border-blue-500">

                        <option value="all"
                                @selected($currentPeriod === 'all')>
                            Toutes les dates
                        </option>

                        <option value="7d"
                                @selected($currentPeriod === '7d')>
                            7 derniers jours
                        </option>

                        <option value="30d"
                                @selected($currentPeriod === '30d')>
                            30 derniers jours
                        </option>
                    </select>
                </div>

                <div class="flex items-end gap-2 md:col-span-2 xl:col-span-1">

                    <button type="submit"
                            class="h-11 rounded-xl bg-[#2563EB] px-4 text-xs font-bold text-white transition hover:bg-blue-700">

                        Filtrer
                    </button>

                    <a href="{{ route('responsable-conferences.confirmed') }}"
                       class="inline-flex h-11 items-center rounded-xl border border-blue-200 bg-white px-3 text-xs font-bold text-blue-700 hover:bg-blue-50">

                        Effacer
                    </a>
                </div>
            </div>
        </form>

        {{-- TABLE --}}

        <div class="overflow-x-auto">

            <table class="w-full min-w-[1250px] text-left text-sm">

                <thead class="bg-[#EEF4FC] text-xs font-extrabold text-[#0F2347]">

                    <tr>
                        <th scope="col" class="px-4 py-4">#</th>
                        <th scope="col" class="px-4 py-4">Entreprise</th>
                        <th scope="col" class="px-4 py-4">Sujet de la conférence</th>
                        <th scope="col" class="px-4 py-4">Espace</th>
                        <th scope="col" class="px-4 py-4">Créneau attribué</th>
                        <th scope="col" class="px-4 py-4">Type</th>
                        <th scope="col" class="px-4 py-4">Date de validation</th>
                        <th scope="col" class="px-4 py-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($confirmations as $confirmation)

                        @php
                            $slot = $confirmation->assignedSlot;

                            $exhibitor = $confirmation->request?->exhibitor
                                ?? $slot?->exhibitor;

                            $companyName = $confirmation->exhibitor_name
                                ?: $exhibitor?->company_name
                                ?: 'Entreprise non renseignée';

                            $commercialName = $exhibitor?->commercial?->name
                                ?? $slot?->commercial?->name
                                ?? 'Non renseigné';

                            $spaceName = $slot?->space?->name
                                ?: ($slot?->space?->type?->label() ?? 'Espace indisponible');

                            $isLounge = $confirmation->request_type === 'lounge';

                            $typeLabel = $isLounge ? 'Lounge' : 'Conférence';

                            $detailsId = 'confirmed-details-'.$confirmation->id;
                        @endphp

                        <tr class="transition hover:bg-blue-50/30">

                            {{-- REFERENCE --}}

                            <td class="px-4 py-5 align-middle">

                                <span class="whitespace-nowrap rounded-lg bg-blue-50 px-2.5 py-1.5 text-xs font-extrabold text-[#0F2347]">

                                    #RC-{{ str_pad((string) $confirmation->id, 4, '0', STR_PAD_LEFT) }}

                                </span>
                            </td>

                            {{-- COMPANY --}}

                            <td class="px-4 py-5 align-middle">

                                <div class="flex items-center gap-3">

                                    <div class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                                        @if($exhibitor?->logo_url)

                                            <img
                                                src="{{ $exhibitor->logo_url }}"
                                                alt="Logo {{ $companyName }}"
                                                loading="lazy"
                                                class="h-full w-full object-contain p-1"
                                            >

                                        @else

                                            <span class="text-xs font-black text-blue-700">
                                                {{ $exhibitor?->initials ?? 'EX' }}
                                            </span>

                                        @endif
                                    </div>

                                    <div class="min-w-0">

                                        <p class="max-w-44 truncate font-extrabold text-[#0F2347]">
                                            {{ $companyName }}
                                        </p>

                                        <p class="mt-1 max-w-44 truncate text-xs text-slate-500">
                                            {{ $commercialName }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- SUBJECT --}}

                            <td class="px-4 py-5 align-middle">

                                <p class="max-w-56 line-clamp-2 font-bold leading-5 text-[#0F2347]">

                                    {{ $confirmation->request_title }}

                                </p>
                            </td>

                            {{-- SPACE --}}

                            <td class="px-4 py-5 align-middle">

                                <span class="inline-flex max-w-44 items-center rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700">

                                    {{ $spaceName }}
                                </span>
                            </td>

                            {{-- ASSIGNED SLOT --}}

                            <td class="whitespace-nowrap px-4 py-5 align-middle">

                                @if($slot)

                                    <p class="font-bold text-[#0F2347]">
                                        {{ $slot->date?->format('d/m/Y') ?? '—' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-600">
                                        {{ $slot->time_range }}
                                    </p>

                                    <p class="mt-1 text-[11px] text-blue-600">
                                        {{ $slot->code }}
                                    </p>

                                @else

                                    <span class="text-xs text-red-600">
                                        Créneau indisponible
                                    </span>

                                @endif
                            </td>

                            {{-- TYPE --}}

                            <td class="px-4 py-5 align-middle">

                                <span @class([
                                    'inline-flex rounded-full px-3 py-1.5 text-xs font-bold',
                                    'bg-blue-100 text-blue-700' => !$isLounge,
                                    'bg-violet-100 text-violet-700' => $isLounge,
                                ])>

                                    {{ $typeLabel }}
                                </span>
                            </td>

                            {{-- CONFIRMED DATE --}}

                            <td class="whitespace-nowrap px-4 py-5 align-middle">

                                <p class="font-bold text-[#0F2347]">
                                    {{ $confirmation->processed_at?->format('d/m/Y') ?? '—' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $confirmation->processed_at?->format('H:i') ?? '—' }}
                                </p>
                            </td>

                            {{-- ACTION --}}

                            <td class="px-4 py-5 text-right align-middle">

                                <button type="button"
                                        data-confirmed-open="{{ $detailsId }}"
                                        class="rounded-lg border border-blue-300 bg-white px-4 py-2 text-xs font-bold text-blue-700 transition hover:bg-blue-50">

                                    Détails
                                </button>
                            </td>
                        </tr>

                        {{-- DETAILS DIALOG --}}

                        <tr class="hidden" aria-hidden="true">
                            <td colspan="8" class="p-0">

                                <dialog
                                    id="{{ $detailsId }}"
                                    aria-labelledby="{{ $detailsId }}-title"
                                    class="confirmed-dialog m-auto w-[calc(100%-2rem)] max-w-2xl rounded-2xl border-0 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/60">

                                    {{-- DIALOG HEADER --}}

                                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">

                                        <div>
                                            <p class="text-xs font-bold text-blue-600">
                                                Réservation confirmée
                                            </p>

                                            <h2
                                                id="{{ $detailsId }}-title"
                                                class="mt-2 text-xl font-black text-[#0F2347]">

                                                #RC-{{ str_pad((string) $confirmation->id, 4, '0', STR_PAD_LEFT) }}

                                            </h2>
                                        </div>

                                        <button type="button"
                                                data-confirmed-close
                                                aria-label="Fermer"
                                                class="rounded-lg px-3 py-2 text-slate-500 hover:bg-slate-100">

                                            ✕
                                        </button>
                                    </div>

                                    {{-- DIALOG BODY --}}

                                    <div class="max-h-[65vh] space-y-5 overflow-y-auto p-6">

                                        <div class="flex items-center gap-3">

                                            <div class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white">

                                                @if($exhibitor?->logo_url)

                                                    <img
                                                        src="{{ $exhibitor->logo_url }}"
                                                        alt=""
                                                        class="h-full w-full object-contain p-1"
                                                    >

                                                @else

                                                    <span class="font-black text-blue-700">
                                                        {{ $exhibitor?->initials ?? 'EX' }}
                                                    </span>

                                                @endif
                                            </div>

                                            <div>
                                                <p class="font-extrabold text-[#0F2347]">
                                                    {{ $companyName }}
                                                </p>

                                                <p class="mt-1 text-xs text-slate-500">
                                                    Commercial : {{ $commercialName }}
                                                </p>
                                            </div>
                                        </div>

                                        {{-- SUBJECT --}}

                                        <div class="rounded-xl bg-[#F1F6FD] p-4">

                                            <p class="text-xs font-bold uppercase text-slate-500">
                                                Sujet
                                            </p>

                                            <p class="mt-2 text-sm font-extrabold text-[#0F2347]">
                                                {{ $confirmation->request_title }}
                                            </p>

                                            @if($slot?->summary)

                                                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">
                                                    {{ $slot->summary }}
                                                </p>

                                            @endif
                                        </div>

                                        {{-- DETAILS GRID --}}

                                        <div class="grid gap-4 sm:grid-cols-2">

                                            <div class="rounded-xl border border-slate-200 p-4">

                                                <p class="text-xs font-bold text-slate-500">
                                                    Espace
                                                </p>

                                                <p class="mt-2 text-sm font-bold text-[#0F2347]">
                                                    {{ $spaceName }}
                                                </p>
                                            </div>

                                            <div class="rounded-xl border border-slate-200 p-4">

                                                <p class="text-xs font-bold text-slate-500">
                                                    Type
                                                </p>

                                                <p class="mt-2 text-sm font-bold text-[#0F2347]">
                                                    {{ $typeLabel }}
                                                </p>
                                            </div>

                                            <div class="rounded-xl border border-slate-200 p-4">

                                                <p class="text-xs font-bold text-slate-500">
                                                    Créneau attribué
                                                </p>

                                                <p class="mt-2 text-sm font-bold text-[#0F2347]">
                                                    {{ $slot?->code ?? 'Indisponible' }}
                                                </p>
                                            </div>

                                            <div class="rounded-xl border border-slate-200 p-4">

                                                <p class="text-xs font-bold text-slate-500">
                                                    Date et horaire
                                                </p>

                                                <p class="mt-2 text-sm font-bold text-[#0F2347]">
                                                    {{ $slot?->date?->format('d/m/Y') ?? '—' }}
                                                    · {{ $slot?->time_range ?? '—' }}
                                                </p>
                                            </div>
                                        </div>

                                        @if($slot?->speakers)

                                            <div>
                                                <p class="text-xs font-bold text-slate-500">
                                                    Intervenants
                                                </p>

                                                <p class="mt-2 whitespace-pre-line text-sm text-slate-700">
                                                    {{ $slot->speakers }}
                                                </p>
                                            </div>

                                        @endif

                                        {{-- VALIDATION INFO --}}

                                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">

                                            <p class="text-sm font-extrabold text-emerald-800">
                                                Réservation validée
                                            </p>

                                            <p class="mt-2 text-sm text-emerald-700">
                                                Validée par :
                                                <strong>{{ $confirmation->processor_name }}</strong>
                                            </p>

                                            <p class="mt-1 text-sm text-emerald-700">
                                                Date :
                                                {{ $confirmation->processed_at?->format('d/m/Y à H:i') ?? '—' }}
                                            </p>
                                        </div>
                                    </div>

                                    {{-- DIALOG FOOTER --}}

                                    <div class="flex justify-end border-t border-slate-100 p-5">

                                        <button type="button"
                                                data-confirmed-close
                                                class="rounded-xl bg-[#0F2347] px-5 py-3 text-sm font-bold text-white">

                                            Fermer
                                        </button>
                                    </div>
                                </dialog>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="px-5 py-14 text-center">

                                <div class="mx-auto grid size-14 place-items-center rounded-full bg-blue-50 text-blue-600">

                                    <x-admin.icon
                                        name="clipboard-check"
                                        class="size-7"
                                    />
                                </div>

                                <p class="mt-4 font-bold text-[#0F2347]">
                                    Aucune réservation confirmée
                                </p>

                                <p class="mt-2 text-sm text-slate-500">
                                    Aucune réservation ne correspond aux filtres sélectionnés.
                                </p>
                            </td>
                        </tr>

                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}

        <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 px-5 py-5">

            <p class="text-xs text-slate-600">

                @if($confirmations->total() > 0)

                    Affichage de
                    <strong>{{ $confirmations->firstItem() }}</strong>
                    à
                    <strong>{{ $confirmations->lastItem() }}</strong>
                    sur
                    <strong>{{ $confirmations->total() }}</strong>
                    réservations

                @else

                    Aucun résultat

                @endif
            </p>

            <div class="w-full sm:w-auto">
                {{ $confirmations->links() }}
            </div>
        </div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[data-confirmed-open]')
        .forEach(button => {

            button.addEventListener('click', function () {
                const dialog = document.getElementById(
                    this.dataset.confirmedOpen
                );

                if (dialog && !dialog.open) {
                    dialog.showModal();
                }
            });
        });

        document.querySelectorAll('.confirmed-dialog')
    .forEach(dialog => {
        document.body.appendChild(dialog);
    });

    document.querySelectorAll('[data-confirmed-close]')
        .forEach(button => {

            button.addEventListener('click', function () {
                const dialog = this.closest('dialog');

                if (dialog) {
                    dialog.close();
                }
            });
        });

    document.querySelectorAll('.confirmed-dialog')
        .forEach(dialog => {

            dialog.addEventListener('click', function (event) {
                if (event.target === dialog) {
                    dialog.close();
                }
            });
        });

});
</script>

</x-responsable-conferences.layout>
