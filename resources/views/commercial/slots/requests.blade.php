@php
    use App\Enums\SlotRequestStatus;

    $tabs = [
        'pending' => 'En attente',
        'accepted' => 'Acceptées',
        'rejected' => 'Refusées',
        'all' => 'Toutes',
    ];
@endphp

<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercialInitials">
    <div class="min-w-0 space-y-7">

        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500" aria-label="Fil d'Ariane">
            <a href="{{ route('commercial.index') }}" class="transition hover:text-blue-600">Espace commercial</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('commercial.slots.index') }}" class="transition hover:text-blue-600">Conférences & Lounge</a>
            <span aria-hidden="true">/</span>
            <span class="font-semibold text-[#0F2347]">Demandes</span>
        </nav>

        <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">Demandes de créneaux</h1>
                <p class="mt-2 text-sm text-slate-500">Acceptez une demande pour attribuer le créneau choisi par l'exposant, ou refusez-la avec un motif.</p>
            </div>
            <a href="{{ route('commercial.slots.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">← Retour au planning</a>
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <nav class="flex flex-wrap gap-2" aria-label="Filtrer par statut">
            @foreach ($tabs as $key => $label)
                @php $count = $key === 'all' ? $counts->sum() : ($counts[$key] ?? 0); @endphp
                <a
                    href="{{ route('commercial.slot-requests.index', ['status' => $key]) }}"
                    @class([
                        'rounded-xl px-4 py-2 text-sm font-semibold transition',
                        'bg-[#2563EB] text-white' => $status === $key,
                        'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => $status !== $key,
                    ])
                >
                    {{ $label }} ({{ $count }})
                </a>
            @endforeach
        </nav>

        @forelse ($requests as $slotRequest)
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            {{ $slotRequest->type->label() }} · reçue le {{ $slotRequest->created_at->format('d/m/Y à H:i') }}
                        </p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">{{ $slotRequest->title }}</h2>
                        <p class="text-sm font-semibold text-[#2563EB]">{{ $slotRequest->exhibitor?->company_name }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $slotRequest->status->pillClasses() }}">{{ $slotRequest->status->label() }}</span>
                </div>

                <p class="mt-4 whitespace-pre-line text-sm text-slate-600">{{ $slotRequest->summary }}</p>
                <p class="mt-2 text-sm text-slate-500"><span class="font-semibold text-slate-700">Intervenants :</span> {{ $slotRequest->speakers }}</p>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach (array_filter(['Créneau demandé' => $slotRequest->firstChoice, '2e choix' => $slotRequest->secondChoice]) ?: ['Créneau demandé' => null] as $label => $choice)
                        <div class="rounded-xl bg-slate-50 px-4 py-3 text-sm">
                            <p class="text-xs font-semibold text-slate-400">{{ $label }}</p>
                            @if ($choice)
                                <p class="mt-0.5 font-semibold capitalize text-slate-800">{{ $choice->full_label }}</p>
                                <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-bold {{ $choice->status->pillClasses() }}">
                                    {{ $choice->status->label() }}{{ $choice->holder_name ? ' · '.$choice->holder_name : '' }}
                                </span>
                            @else
                                <p class="mt-0.5 text-slate-400">—</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($slotRequest->status === SlotRequestStatus::Pending)
                    @php
                        $freeChoices = collect([$slotRequest->firstChoice, $slotRequest->secondChoice])
                            ->filter(fn ($slot) => $slot && ! $slot->status->isTaken());
                    @endphp

                    <div class="mt-5 grid gap-4 border-t border-slate-100 pt-5 lg:grid-cols-2">
                        <form method="POST" action="{{ route('commercial.slot-requests.accept', $slotRequest) }}" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            <p class="text-sm font-semibold text-slate-700">Accepter et attribuer</p>
                            @forelse ($freeChoices as $choice)
                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                    <input type="radio" name="slot_id" value="{{ $choice->id }}" @checked($loop->first) class="text-blue-600">
                                    {{ $choice->code }} · {{ $choice->time_range }}
                                </label>
                            @empty
                                <p class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-700">Le créneau demandé n'est plus disponible. Refusez la demande (l'exposant pourra en choisir un autre) ou attribuez un autre créneau depuis le planning.</p>
                            @endforelse
                            @if ($freeChoices->isNotEmpty())
                                <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">Accepter</button>
                            @endif
                        </form>

                        <form method="POST" action="{{ route('commercial.slot-requests.reject', $slotRequest) }}" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            <label class="block text-sm font-semibold text-slate-700">
                                Motif du refus
                                <textarea name="rejection_reason" rows="2" required maxlength="1000" class="mt-1 w-full rounded-xl border-slate-300 text-sm" placeholder="Ex. : créneaux complets, sujet hors thématique…"></textarea>
                            </label>
                            <button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-bold text-red-700 hover:bg-red-100">Refuser</button>
                        </form>
                    </div>
                @else
                    <p class="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-500">
                        Traitée {{ $slotRequest->processed_at ? 'le '.$slotRequest->processed_at->format('d/m/Y à H:i') : '' }}
                        {{ $slotRequest->processor ? 'par '.$slotRequest->processor->name : '' }}.
                        @if ($slotRequest->assignedSlot)
                            Créneau attribué : <span class="font-semibold text-slate-700">{{ $slotRequest->assignedSlot->code }}</span>.
                        @endif
                        @if ($slotRequest->rejection_reason)
                            Motif : {{ $slotRequest->rejection_reason }}
                        @endif
                    </p>
                @endif
            </article>
        @empty
            <x-admin.empty-state title="Aucune demande" description="Les demandes envoyées par les exposants apparaîtront ici." />
        @endforelse

        {{ $requests->links() }}
    </div>
</x-commercial.layout>
