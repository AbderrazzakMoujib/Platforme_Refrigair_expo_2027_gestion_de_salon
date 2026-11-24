<x-responsable-conferences.layout :user="auth()->user()">
    <div class="space-y-6">
        <header><p class="text-sm font-semibold text-blue-600">Traitement</p><h1 class="mt-1 text-3xl font-extrabold text-[#0F2347]">Demandes de réservation</h1><p class="mt-2 text-sm text-slate-500">Les demandes en attente ne réservent pas les créneaux. La confirmation vérifie la disponibilité au moment de la décision.</p></header>
        @if (session('status'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>@endif
        @if ($errors->getBag('slot-request')->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc pl-5">@foreach ($errors->getBag('slot-request')->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <nav class="flex flex-wrap gap-2">@foreach (['pending'=>'En attente','accepted'=>'Confirmées','rejected'=>'Refusées','all'=>'Toutes'] as $key=>$label)<a href="{{ route('responsable-conferences.requests.index',['status'=>$key]) }}" @class(['rounded-xl px-4 py-2 text-sm font-semibold','bg-blue-600 text-white'=>$status===$key,'bg-white text-slate-700 ring-1 ring-slate-200'=>$status!==$key])>{{ $label }}</a>@endforeach</nav>
        @forelse ($requests as $slotRequest)
            @php
                $choices = collect([$slotRequest->firstChoice, $slotRequest->secondChoice])->filter();
                $availableChoices = $choices->filter(fn ($slot) => $slot->status === \App\Enums\SlotStatus::Available);
            @endphp
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="text-xs font-semibold uppercase text-slate-500">{{ $slotRequest->exhibitor?->company_name }} · {{ $slotRequest->type->label() }} · reçue le {{ $slotRequest->created_at->format('d/m/Y à H:i') }}</p><h2 class="mt-1 text-xl font-bold text-[#0F2347]">{{ $slotRequest->title }}</h2><p class="mt-1 text-sm text-slate-500">Commercial : {{ $slotRequest->exhibitor?->commercial?->name ?? '—' }}</p></div><span class="rounded-full px-3 py-1 text-xs font-bold {{ $slotRequest->status->pillClasses() }}">{{ $slotRequest->status->label() }}</span></div>
                <p class="mt-4 whitespace-pre-line text-sm text-slate-700">{{ $slotRequest->summary }}</p><p class="mt-3 text-sm"><strong>Intervenants :</strong> {{ $slotRequest->speakers }}</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">@foreach ($choices as $slot)<div class="rounded-xl border border-slate-200 p-4"><p class="font-bold">{{ $loop->first ? 'Premier choix' : 'Deuxième choix' }} · {{ $slot->code }}</p><p class="mt-1 text-sm text-slate-600">{{ $slot->date->format('d/m/Y') }} · {{ $slot->time_range }}</p><p class="mt-1 text-xs {{ $slot->status === \App\Enums\SlotStatus::Available ? 'text-emerald-700' : 'text-red-700' }}">{{ $slot->status->label() }}</p></div>@endforeach</div>
                @if ($slotRequest->status === \App\Enums\SlotRequestStatus::Pending)
                    <div class="mt-5 grid gap-4 border-t border-slate-100 pt-5 lg:grid-cols-2">
                        <form method="POST" action="{{ route('responsable-conferences.requests.accept',$slotRequest) }}" class="flex flex-wrap items-end gap-3">@csrf @method('PATCH')<label class="min-w-48 flex-1 text-sm font-semibold">Confirmer sur un choix disponible<select name="slot_id" required class="mt-1 w-full rounded-xl border-slate-300">@foreach ($availableChoices as $slot)<option value="{{ $slot->id }}">{{ $slot->code }} · {{ $slot->date->format('d/m/Y') }} · {{ $slot->time_range }}</option>@endforeach</select></label><button @disabled($availableChoices->isEmpty()) class="rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white disabled:cursor-not-allowed disabled:opacity-50">Confirmer</button></form>
                        <form method="POST" action="{{ route('responsable-conferences.requests.reject',$slotRequest) }}" class="flex flex-wrap items-end gap-3">@csrf @method('PATCH')<label class="min-w-48 flex-1 text-sm font-semibold">Motif du refus<textarea name="rejection_reason" required maxlength="1000" rows="2" class="mt-1 w-full rounded-xl border-slate-300"></textarea></label><button class="rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white">Refuser</button></form>
                    </div>
                    @if ($availableChoices->isEmpty())<p class="mt-3 text-sm font-semibold text-amber-700">Aucun des deux choix n’est encore disponible. La demande reste en attente.</p>@endif
                @else
                    <p class="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-500">Décision {{ $slotRequest->processed_at?->format('d/m/Y à H:i') }} · {{ $slotRequest->processor?->name }} @if ($slotRequest->assignedSlot) · créneau attribué : {{ $slotRequest->assignedSlot->code }} @endif @if ($slotRequest->rejection_reason) · motif : {{ $slotRequest->rejection_reason }} @endif</p>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">Aucune demande dans cette vue.</div>
        @endforelse
        {{ $requests->links() }}
    </div>
</x-responsable-conferences.layout>
