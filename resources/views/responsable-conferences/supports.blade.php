
<x-responsable-conferences.layout :user="auth()->user()">

@php
    $selectedStatus = $filters['status'] ?? 'all';

    $statusLabels = [
        'not_submitted' => 'Non envoyé',
        'pending' => 'À vérifier',
        'approved' => 'Approuvé',
        'revision_requested' => 'Modification demandée',
    ];

    $statusColors = [
        'not_submitted' => 'bg-slate-100 text-slate-700',
        'pending' => 'bg-amber-100 text-amber-800',
        'approved' => 'bg-emerald-100 text-emerald-800',
        'revision_requested' => 'bg-red-100 text-red-700',
    ];
@endphp

<div class="space-y-6">

    <header>
        <p class="text-xs font-bold text-blue-600">
            Conférences & Lounge / Supports
        </p>

        <h1 class="mt-3 text-3xl font-black text-[#0F2347]">
            Supports & Messagerie
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Vérifiez les supports de présentation
            et échangez avec les exposants confirmés.
        </p>
    </header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['Total confirmées', $counts['total']],
            ['À vérifier', $counts['pending']],
            ['Approuvés', $counts['approved']],
            ['Modifications demandées', $counts['revision_requested']],
        ] as $stat)

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">
                    {{ $stat[0] }}
                </p>

                <p class="mt-3 text-3xl font-black text-[#0F2347]">
                    {{ $stat[1] }}
                </p>
            </article>

        @endforeach
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 p-5">

            <form method="GET"
                  action="{{ route('responsable-conferences.supports.index') }}"
                  class="flex flex-wrap items-end gap-3">

                <div class="min-w-48 flex-1">
                    <label class="mb-2 block text-xs font-bold text-[#0F2347]">
                        Rechercher
                    </label>

                    <input
                        type="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Entreprise ou sujet..."
                        class="h-11 w-full rounded-xl border border-slate-300 px-4 text-sm"
                    >
                </div>

                <div class="min-w-48">
                    <label class="mb-2 block text-xs font-bold text-[#0F2347]">
                        Statut du support
                    </label>

                    <select
                        name="status"
                        class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm"
                    >
                        <option value="all" @selected($selectedStatus === 'all')>
                            Tous les statuts
                        </option>

                        @foreach($statusLabels as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected($selectedStatus === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button class="h-11 rounded-xl bg-blue-600 px-5 text-sm font-bold text-white">
                    Filtrer
                </button>
            </form>
        </div>

        <div class="divide-y divide-slate-100">

            @forelse($requests as $item)
                @php
                    $supportStatus = $item->support_review_status
                        ?? 'not_submitted';
                @endphp

                <article class="flex flex-wrap items-center gap-4 p-5 hover:bg-slate-50">

                    @if($item->exhibitor?->logo_url)
                        <img
                            src="{{ $item->exhibitor->logo_url }}"
                            alt=""
                            class="size-12 rounded-xl border border-slate-200 object-contain p-1"
                        >
                    @else
                        <span class="grid size-12 place-items-center rounded-xl bg-blue-100 text-sm font-bold text-blue-700">
                            {{ $item->exhibitor?->initials ?? 'EX' }}
                        </span>
                    @endif

                    <div class="min-w-0 flex-1">

                        <h2 class="font-black text-[#0F2347]">
                            {{ $item->exhibitor?->company_name }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-600">
                            {{ $item->title }}
                        </p>

                        <p class="mt-2 text-xs text-slate-500">
                            {{ $item->assignedSlot?->code }}
                            · {{ $item->assignedSlot?->date?->format('d/m/Y') }}
                            · {{ $item->assignedSlot?->time_range }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">

                        <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ $statusColors[$supportStatus] ?? $statusColors['not_submitted'] }}">
                            {{ $statusLabels[$supportStatus] ?? 'Non envoyé' }}
                        </span>

                        @if($item->unread_messages_count > 0)
                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700">
                                {{ $item->unread_messages_count }}
                                message(s)
                            </span>
                        @endif

                        <a
                            href="{{ route('responsable-conferences.supports.show', $item) }}"
                            class="rounded-xl bg-[#2563EB] px-4 py-3 text-xs font-bold text-white hover:bg-blue-700"
                        >
                            Voir le suivi
                        </a>
                    </div>
                </article>

            @empty

                <div class="p-12 text-center">
                    <p class="font-bold text-[#0F2347]">
                        Aucune conférence trouvée
                    </p>
                </div>

            @endforelse
        </div>

        <div class="border-t border-slate-100 p-5">
            {{ $requests->links() }}
        </div>
    </section>
</div>

</x-responsable-conferences.layout>
