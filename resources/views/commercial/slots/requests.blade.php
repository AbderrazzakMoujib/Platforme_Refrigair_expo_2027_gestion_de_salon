<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercialInitials">
    <div class="mx-auto max-w-7xl space-y-6">
        <header class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-blue-600">Conférences & Lounge</p>
                <h1 class="mt-1 text-3xl font-extrabold text-[#0F2347]">Mes demandes</h1>
                <p class="mt-2 text-sm text-slate-500">Vous ne voyez que les demandes des exposants qui vous sont rattachés.</p>
            </div>
            <a href="{{ route('commercial.slots.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold">Retour au planning</a>
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="text-lg font-bold text-[#0F2347]">Envoyer une demande pour un de mes exposants</h2>
            <form method="POST" action="{{ route('commercial.slot-requests.store') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <label class="text-sm font-semibold">Exposant
                    <select name="exhibitor_id" required class="mt-1 w-full rounded-xl border-slate-300">
                        <option value="">Choisir un exposant</option>
                        @foreach ($exhibitors as $exhibitor)<option value="{{ $exhibitor->id }}" @selected((string) old('exhibitor_id') === (string) $exhibitor->id)>{{ $exhibitor->company_name }}</option>@endforeach
                    </select>
                </label>
                <label class="text-sm font-semibold">Espace
                    <select name="type" required class="mt-1 w-full rounded-xl border-slate-300">
                        @foreach ($spaceTypes as $type)<option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>@endforeach
                    </select>
                </label>
                <label class="text-sm font-semibold md:col-span-2">Titre
                    <input name="title" required maxlength="150" value="{{ old('title') }}" class="mt-1 w-full rounded-xl border-slate-300">
                </label>
                <label class="text-sm font-semibold md:col-span-2">Résumé (5 lignes maximum)
                    <textarea name="summary" required rows="3" maxlength="800" class="mt-1 w-full rounded-xl border-slate-300">{{ old('summary') }}</textarea>
                </label>
                <label class="text-sm font-semibold md:col-span-2">Intervenants
                    <textarea name="speakers" required rows="2" maxlength="500" class="mt-1 w-full rounded-xl border-slate-300">{{ old('speakers') }}</textarea>
                </label>
                @foreach (['first_choice_slot_id' => 'Premier choix', 'second_choice_slot_id' => 'Deuxième choix'] as $field => $label)
                    <label class="text-sm font-semibold">{{ $label }}
                        <select name="{{ $field }}" required class="mt-1 w-full rounded-xl border-slate-300">
                            <option value="">Choisir un créneau disponible</option>
                            @foreach ($availableSlots as $slot)
                                <option value="{{ $slot->id }}" data-space="{{ $slot->space->type->value }}" @selected((string) old($field) === (string) $slot->id)>{{ $slot->code }} · {{ $slot->space->type->label() }} · {{ $slot->date->format('d/m/Y') }} · {{ $slot->time_range }}</option>
                            @endforeach
                        </select>
                    </label>
                @endforeach
                <div class="md:col-span-2 flex justify-end"><button class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700">Envoyer la demande</button></div>
            </form>
        </section>

        <section class="space-y-3">
            <div class="flex flex-wrap gap-2">
                @foreach (['all' => 'Toutes', 'pending' => 'En attente', 'accepted' => 'Confirmées', 'rejected' => 'Refusées'] as $key => $label)
                    <a href="{{ route('commercial.slot-requests.index', ['status' => $key]) }}" @class(['rounded-xl px-4 py-2 text-sm font-semibold', 'bg-blue-600 text-white' => $status === $key, 'bg-white text-slate-700 ring-1 ring-slate-200' => $status !== $key])>{{ $label }} ({{ $key === 'all' ? $counts->sum() : ($counts[$key] ?? 0) }})</a>
                @endforeach
            </div>

            @forelse ($requests as $slotRequest)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div><p class="text-xs font-semibold uppercase text-slate-500">{{ $slotRequest->exhibitor?->company_name }} · {{ $slotRequest->type->label() }}</p><h2 class="mt-1 text-lg font-bold text-[#0F2347]">{{ $slotRequest->title }}</h2></div>
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $slotRequest->status->pillClasses() }}">{{ $slotRequest->status->label() }}</span>
                    </div>
                    <p class="mt-3 whitespace-pre-line text-sm text-slate-600">{{ $slotRequest->summary }}</p>
                    <p class="mt-2 text-sm text-slate-600">Choix : {{ $slotRequest->firstChoice?->code }} · {{ $slotRequest->secondChoice?->code }}</p>
                    @if ($slotRequest->processed_at)
                        <p class="mt-3 border-t border-slate-100 pt-3 text-sm text-slate-500">Décision le {{ $slotRequest->processed_at->format('d/m/Y à H:i') }}@if ($slotRequest->processor) par {{ $slotRequest->processor->name }}@endif @if ($slotRequest->assignedSlot) · Créneau attribué : {{ $slotRequest->assignedSlot->code }}@endif @if ($slotRequest->rejection_reason) · Motif : {{ $slotRequest->rejection_reason }}@endif</p>
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">Aucune demande dans cette vue.</div>
            @endforelse
            {{ $requests->links() }}
        </section>
    </div>
</x-commercial.layout>
