
<x-responsable-conferences.layout :user="auth()->user()">

@php
    $total = max(1, $totalDecisions);

    $stats = [
        [
            'label' => 'Total des décisions',
            'value' => $totalDecisions,
            'icon' => 'overview',
            'bg' => 'bg-blue-100',
            'color' => 'text-blue-700',
            'percent' => null,
        ],
        [
            'label' => 'Demandes validées',
            'value' => $acceptedCount,
            'icon' => 'clipboard-check',
            'bg' => 'bg-emerald-100',
            'color' => 'text-emerald-700',
            'percent' => $acceptedCount * 100 / $total,
        ],
        [
            'label' => 'Demandes refusées',
            'value' => $rejectedCount,
            'icon' => 'inbox',
            'bg' => 'bg-red-100',
            'color' => 'text-red-700',
            'percent' => $rejectedCount * 100 / $total,
        ],
    ];

    $search = $filters['search'] ?? '';
    $selectedDecision = $filters['decision'] ?? '';
    $selectedType = $filters['type'] ?? '';
    $selectedSpace = $filters['space_id'] ?? '';
    $selectedPeriod = $filters['period'] ?? 'all';
@endphp

<div class="space-y-6">

    {{-- PAGE HEADER --}}

    <section>
        <p class="text-xs font-bold text-blue-600">
            Conférences & Lounge
            <span class="mx-2 text-slate-300">/</span>
            Historique des décisions
        </p>

        <div class="mt-4 flex items-center gap-4">
            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-700">
                <x-admin.icon name="chart" class="size-7"/>
            </span>

            <div>
                <h1 class="text-2xl font-black tracking-tight text-[#0F2347] sm:text-3xl">
                    Historique des décisions
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Consultez l'ensemble des décisions prises
                    sur les demandes de réservation.
                </p>
            </div>
        </div>
    </section>

    {{-- KPI CARDS --}}

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach($stats as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <div class="flex items-start justify-between gap-3">
                    <span class="grid size-12 place-items-center rounded-xl {{ $stat['bg'] }} {{ $stat['color'] }}">
                        <x-admin.icon :name="$stat['icon']" class="size-6"/>
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

                    @if($stat['percent'] !== null)
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold {{ $stat['color'] }}">
                            {{ number_format($stat['percent'], 1, ',', ' ') }}%
                        </span>
                    @endif
                </div>
            </article>
        @endforeach

        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <span class="grid size-12 place-items-center rounded-xl bg-blue-100 text-blue-700">
                    <x-admin.icon name="calendar" class="size-6"/>
                </span>

                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-bold text-blue-700">
                    REFRIGAIR 2027
                </span>
            </div>

            <p class="mt-5 text-sm font-bold text-[#0F2347]">
                Période analysée
            </p>

            <p class="mt-2 text-lg font-black text-[#0F2347]">
                {{ $periodLabel }}
            </p>
        </article>
    </section>

    {{-- HISTORY TABLE CONTAINER --}}

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-wrap items-center justify-between gap-4 p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-700">
                    <x-admin.icon name="chart" class="size-6"/>
                </span>

                <div>
                    <h2 class="text-lg font-black text-[#0F2347]">
                        Liste des décisions
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Historique complet des validations et refus,
                        avec les détails et motifs.
                    </p>
                </div>
            </div>

            <a href="{{ route('responsable-conferences.history.export', request()->only(['search', 'decision', 'type', 'space_id', 'period'])) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-blue-500 px-4 py-3 text-sm font-bold text-blue-600 transition hover:bg-blue-50">
                <x-admin.icon name="clipboard-check" class="size-5"/>
                Exporter CSV (Excel)
            </a>
        </div>

        {{-- FILTERS --}}

        <form method="GET"
              action="{{ route('responsable-conferences.history') }}"
              class="border-b border-slate-100 px-4 pb-5 sm:px-6">

            <div class="grid gap-3 rounded-xl bg-[#F1F6FD] p-4 md:grid-cols-2 xl:grid-cols-6">

                <div class="xl:col-span-2">
                    <label for="history-search" class="mb-2 block text-xs font-bold text-[#0F2347]">
                        Rechercher
                    </label>

                    <input id="history-search"
                           name="search"
                           type="search"
                           value="{{ $search }}"
                           maxlength="150"
                           placeholder="Entreprise, sujet, décision..."
                           class="h-11 w-full rounded-lg border border-slate-300 bg-white px-4 text-sm focus:border-blue-500">
                </div>

                <div>
                    <label for="history-decision" class="mb-2 block text-xs font-bold text-[#0F2347]">
                        Décision
                    </label>

                    <select id="history-decision" name="decision"
                            class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm">
                        <option value="">Toutes</option>
                        <option value="accepted" @selected($selectedDecision === 'accepted')>
                            Validées
                        </option>
                        <option value="rejected" @selected($selectedDecision === 'rejected')>
                            Refusées
                        </option>
                    </select>
                </div>

                <div>
                    <label for="history-type" class="mb-2 block text-xs font-bold text-[#0F2347]">
                        Type
                    </label>

                    <select id="history-type" name="type"
                            class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm">
                        <option value="">Tous les types</option>
                        <option value="conference" @selected($selectedType === 'conference')>
                            Conférence
                        </option>
                        <option value="lounge" @selected($selectedType === 'lounge')>
                            Lounge
                        </option>
                    </select>
                </div>

                <div>
                    <label for="history-space" class="mb-2 block text-xs font-bold text-[#0F2347]">
                        Espace
                    </label>

                    <select id="history-space" name="space_id"
                            class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm">
                        <option value="">Tous les espaces</option>

                        @foreach($spaces as $space)
                            <option value="{{ $space->id }}"
                                    @selected((string)$selectedSpace === (string)$space->id)>
                                {{ $space->name ?: $space->type->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="history-period" class="mb-2 block text-xs font-bold text-[#0F2347]">
                        Période
                    </label>

                    <select id="history-period" name="period"
                            class="h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm">
                        <option value="all" @selected($selectedPeriod === 'all')>Toutes les dates</option>
                        <option value="7d" @selected($selectedPeriod === '7d')>7 derniers jours</option>
                        <option value="30d" @selected($selectedPeriod === '30d')>30 derniers jours</option>
                    </select>
                </div>

                <div class="flex flex-wrap items-end gap-2 md:col-span-2 xl:col-span-6">
                    <button type="submit"
                            class="h-10 rounded-lg bg-[#2563EB] px-5 text-xs font-bold text-white hover:bg-blue-700">
                        Filtrer
                    </button>

                    <a href="{{ route('responsable-conferences.history') }}"
                       class="inline-flex h-10 items-center rounded-lg border border-blue-200 bg-white px-4 text-xs font-bold text-blue-700 hover:bg-blue-50">
                        Réinitialiser
                    </a>
                </div>
            </div>
        </form>

        {{-- TABLE --}}

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1350px] text-left text-sm">

                <thead class="bg-[#EEF4FC] text-xs font-extrabold text-[#0F2347]">
                    <tr>
                        <th class="px-4 py-4">#</th>
                        <th class="px-4 py-4">Entreprise</th>
                        <th class="px-4 py-4">Sujet de la conférence</th>
                        <th class="px-4 py-4">Type</th>
                        <th class="px-4 py-4">Décision</th>
                        <th class="px-4 py-4">Date de décision</th>
                        <th class="px-4 py-4">Traité par</th>
                        <th class="px-4 py-4">Motif (si refus)</th>
                        <th class="px-4 py-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($decisions as $decision)
                        @php
                            $exhibitor = $decision->request?->exhibitor
                                ?? $decision->assignedSlot?->exhibitor;

                            $company = $decision->exhibitor_name
                                ?: $exhibitor?->company_name
                                ?: 'Entreprise non renseignée';

                            $commercial = $exhibitor?->commercial?->name
                                ?? $decision->assignedSlot?->commercial?->name
                                ?? 'Sans commercial';

                            $accepted = $decision->decision === 'accepted';

                            $isLounge = $decision->request_type === 'lounge';
                        @endphp

                        <tr class="transition hover:bg-blue-50/30">

                            <td class="px-4 py-5">
                                <span class="whitespace-nowrap rounded-lg bg-blue-50 px-2.5 py-1.5 text-xs font-extrabold text-[#0F2347]">
                                    #DR-{{ str_pad((string)($decision->slot_request_id ?? $decision->id), 4, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>

                            <td class="px-4 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                        @if($exhibitor?->logo_url)
                                            <img src="{{ $exhibitor->logo_url }}"
                                                 alt="Logo {{ $company }}"
                                                 loading="lazy"
                                                 class="h-full w-full object-contain p-1">
                                        @else
                                            <span class="text-xs font-black text-blue-700">
                                                {{ $exhibitor?->initials ?? 'EX' }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <p class="max-w-44 truncate font-extrabold text-[#0F2347]">
                                            {{ $company }}
                                        </p>

                                        <p class="mt-1 max-w-44 truncate text-xs text-slate-500">
                                            {{ $commercial }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-5">
                                <p class="max-w-56 line-clamp-2 font-bold text-[#0F2347]">
                                    {{ $decision->request_title }}
                                </p>
                            </td>

                            <td class="px-4 py-5">
                                <span @class([
                                    'inline-flex rounded-full px-3 py-1.5 text-xs font-bold',
                                    'bg-blue-100 text-blue-700' => !$isLounge,
                                    'bg-violet-100 text-violet-700' => $isLounge,
                                ])>
                                    {{ $isLounge ? 'Lounge' : 'Conférence' }}
                                </span>
                            </td>

                            <td class="px-4 py-5">
                                <span @class([
                                    'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold',
                                    'bg-emerald-100 text-emerald-700' => $accepted,
                                    'bg-red-100 text-red-700' => !$accepted,
                                ])>
                                    {{ $accepted ? '✓ Validée' : '× Refusée' }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-4 py-5">
                                <p class="font-bold text-[#0F2347]">
                                    {{ $decision->processed_at?->format('d/m/Y') ?? '—' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $decision->processed_at?->format('H:i') ?? '—' }}
                                </p>
                            </td>

                            <td class="px-4 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-blue-600 text-xs font-bold text-white">
                                        {{ mb_strtoupper(mb_substr($decision->processor_name ?? 'R', 0, 2)) }}
                                    </span>

                                    <span class="max-w-32 truncate text-xs font-semibold text-[#0F2347]">
                                        {{ $decision->processor_name ?? '—' }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-4 py-5">
                                @if(!$accepted && $decision->reason)
                                    <p class="max-w-52 rounded-xl bg-red-50 px-3 py-2 text-xs leading-5 text-red-700">
                                        {{ \Illuminate\Support\Str::limit($decision->reason, 85) }}
                                    </p>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-5 text-right">
                                <button type="button"
                                        data-history-open="history-details-{{ $decision->id }}"
                                        class="rounded-lg border border-blue-300 bg-white px-4 py-2 text-xs font-bold text-blue-700 hover:bg-blue-50">
                                    Détails
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-14 text-center">
                                <p class="font-bold text-[#0F2347]">
                                    Aucune décision trouvée
                                </p>
                                <p class="mt-2 text-sm text-slate-500">
                                    Modifiez les filtres pour afficher d'autres résultats.
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
                @if($decisions->total())
                    Affichage de
                    <strong>{{ $decisions->firstItem() }}</strong>
                    à
                    <strong>{{ $decisions->lastItem() }}</strong>
                    sur
                    <strong>{{ $decisions->total() }}</strong>
                    décisions
                @else
                    Aucun résultat
                @endif
            </p>

            <div class="w-full sm:w-auto">
                {{ $decisions->links() }}
            </div>
        </div>
    </section>
</div>

{{-- DETAILS DIALOGS: outside table to preserve valid HTML --}}

@foreach($decisions as $decision)
    @php
        $exhibitor = $decision->request?->exhibitor
            ?? $decision->assignedSlot?->exhibitor;

        $company = $decision->exhibitor_name
            ?: $exhibitor?->company_name
            ?: 'Entreprise non renseignée';

        $accepted = $decision->decision === 'accepted';
        $slot = $decision->assignedSlot;
        $dialogId = 'history-details-'.$decision->id;
    @endphp

    <dialog id="{{ $dialogId }}"
            aria-labelledby="{{ $dialogId }}-title"
            class="history-dialog m-auto w-[calc(100%-2rem)] max-w-2xl rounded-2xl border-0 bg-white p-0 text-slate-900 shadow-2xl">

        <div class="flex items-start justify-between gap-4 border-b border-slate-100 p-6">
            <div>
                <p class="text-xs font-bold text-blue-600">
                    Historique · Décision #{{ $decision->id }}
                </p>

                <h2 id="{{ $dialogId }}-title"
                    class="mt-2 text-xl font-black text-[#0F2347]">
                    Détails de la décision
                </h2>
            </div>

            <button type="button"
                    data-history-close
                    aria-label="Fermer"
                    class="rounded-lg px-3 py-2 text-slate-500 hover:bg-slate-100">
                ✕
            </button>
        </div>

        <div class="max-h-[65vh] space-y-5 overflow-y-auto p-6">

            {{-- COMPANY --}}

            <div class="flex items-center gap-3">
                <div class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white">
                    @if($exhibitor?->logo_url)
                        <img src="{{ $exhibitor->logo_url }}"
                             alt=""
                             class="h-full w-full object-contain p-1.5">
                    @else
                        <span class="font-black text-blue-700">
                            {{ $exhibitor?->initials ?? 'EX' }}
                        </span>
                    @endif
                </div>

                <div>
                    <p class="font-extrabold text-[#0F2347]">
                        {{ $company }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $decision->request_title }}
                    </p>
                </div>
            </div>

            {{-- STATUS --}}

            <div @class([
                'rounded-xl border p-4',
                'border-emerald-200 bg-emerald-50' => $accepted,
                'border-red-200 bg-red-50' => !$accepted,
            ])>
                <p @class([
                    'text-sm font-extrabold',
                    'text-emerald-800' => $accepted,
                    'text-red-800' => !$accepted,
                ])>
                    {{ $accepted ? '✓ Demande validée' : '× Demande refusée' }}
                </p>

                <p class="mt-2 text-sm text-slate-700">
                    Traité par :
                    <strong>{{ $decision->processor_name ?? '—' }}</strong>
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    Date :
                    {{ $decision->processed_at?->format('d/m/Y à H:i') ?? '—' }}
                </p>
            </div>

            {{-- DETAILS --}}

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 p-4">
                    <p class="text-xs font-bold text-slate-500">Type</p>
                    <p class="mt-2 text-sm font-bold text-[#0F2347]">
                        {{ $decision->request_type === 'lounge' ? 'Lounge' : 'Conférence' }}
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 p-4">
                    <p class="text-xs font-bold text-slate-500">Référence demande</p>
                    <p class="mt-2 text-sm font-bold text-[#0F2347]">
                        #DR-{{ str_pad((string)($decision->slot_request_id ?? $decision->id), 4, '0', STR_PAD_LEFT) }}
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 p-4">
                    <p class="text-xs font-bold text-slate-500">Créneau attribué</p>
                    <p class="mt-2 text-sm font-bold text-[#0F2347]">
                        {{ $slot?->code ?? 'Aucun créneau attribué' }}
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 p-4">
                    <p class="text-xs font-bold text-slate-500">Date / horaire</p>
                    <p class="mt-2 text-sm font-bold text-[#0F2347]">
                        @if($slot)
                            {{ $slot->date?->format('d/m/Y') }}
                            · {{ $slot->time_range }}
                        @else
                            —
                        @endif
                    </p>
                </div>
            </div>

            @if($slot?->space)
                <div class="rounded-xl bg-[#F1F6FD] p-4">
                    <p class="text-xs font-bold text-slate-500">
                        Espace attribué
                    </p>
                    <p class="mt-2 font-bold text-[#0F2347]">
                        {{ $slot->space->name ?: $slot->space->type->label() }}
                    </p>
                </div>
            @endif

            @if(!$accepted)
                <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                    <p class="text-sm font-extrabold text-red-800">
                        Motif du refus
                    </p>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-red-700">
                        {{ $decision->reason ?: 'Aucun motif enregistré.' }}
                    </p>
                </div>
            @endif
        </div>

        <div class="flex justify-end border-t border-slate-100 p-5">
            <button type="button"
                    data-history-close
                    class="rounded-xl bg-[#0F2347] px-5 py-3 text-sm font-bold text-white">
                Fermer
            </button>
        </div>
    </dialog>
@endforeach

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-history-open]').forEach(button => {
        button.addEventListener('click', () => {
            const dialog = document.getElementById(
                button.dataset.historyOpen
            );

            if (dialog && !dialog.open) {
                dialog.showModal();
            }
        });
    });

    document.querySelectorAll('[data-history-close]').forEach(button => {
        button.addEventListener('click', () => {
            button.closest('dialog')?.close();
        });
    });

    document.querySelectorAll('.history-dialog').forEach(dialog => {
        dialog.addEventListener('click', event => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });
});
</script>

</x-responsable-conferences.layout>
