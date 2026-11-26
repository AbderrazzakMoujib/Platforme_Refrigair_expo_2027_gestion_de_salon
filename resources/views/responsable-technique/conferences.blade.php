<x-technical.layout :user="auth()->user()" title="Conférences & Lounge">
    <div class="mb-6"><p class="text-sm font-semibold text-blue-700">Consultation en lecture seule</p><h1 class="mt-1 text-2xl font-black text-[#0F2347]">Conférences & Lounge</h1><p class="mt-2 text-sm text-slate-600">Les demandes sont comptées séparément des réservations et des créneaux.</p></div>

    @php($cards = [
        ['Demandes en attente', $pendingRequests->count(), 'En attente de décision'],
        ['Demandes confirmées', $acceptedRequests->count(), 'Demandes exposants acceptées'],
        ['Réservations directes', $directReservations->count(), 'Créneaux gérés hors demande exposant'],
        ['Disponibles', $slotCounts['available'] ?? 0, 'Créneaux libres'],
        ['En option', $slotCounts['option'] ?? 0, 'Créneaux avec option'],
        ['Réservés', $slotCounts['reserved'] ?? 0, 'Statut Réservé'],
        ['Payés', $slotCounts['paid'] ?? 0, 'Statut Payé'],
    ])
    <div class="mb-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($cards as [$label, $value, $hint])
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ $label }}</p><p class="mt-2 text-2xl font-black text-[#0F2347]">{{ number_format($value) }}</p><p class="mt-1 text-xs text-slate-500">{{ $hint }}</p></article>
        @endforeach
    </div>

    <section class="mb-8"><div class="mb-3"><h2 class="text-xl font-extrabold text-[#0F2347]">Planning et créneaux</h2><p class="mt-1 text-sm text-slate-500">Un créneau apparaît une seule fois, selon son état actuel.</p></div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">Date / heure</th><th class="px-4 py-3">Espace</th><th class="px-4 py-3">État</th><th class="px-4 py-3">Entreprise / titulaire</th><th class="px-4 py-3">Sujet / intervenants</th><th class="px-4 py-3">Origine</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse ($slots as $slot)
                @php($accepted = $slot->assignedRequests->isNotEmpty())
                @php($direct = $directReservations->contains('id', $slot->id))
                <tr class="align-top"><td class="whitespace-nowrap px-4 py-4 font-semibold text-[#0F2347]">{{ $slot->date->format('d/m/Y') }}<span class="mt-1 block text-xs font-normal text-slate-500">{{ $slot->start_label }}–{{ $slot->end_label }}</span></td><td class="px-4 py-4">{{ $slot->space?->name ?? $slot->space?->type?->label() ?? '—' }}</td><td class="px-4 py-4"><span class="whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold {{ $slot->status->pillClasses() }}">{{ $slot->status->label() }}</span></td><td class="px-4 py-4">{{ $slot->holder_name ?: '—' }}@if ($slot->contact_name)<span class="mt-1 block text-xs text-slate-500">{{ $slot->contact_name }}</span>@endif @if ($slot->contact_email)<span class="mt-1 block text-xs text-slate-500">{{ $slot->contact_email }}</span>@endif</td><td class="max-w-sm px-4 py-4"><span class="font-semibold">{{ $slot->title ?: '—' }}</span>@if ($slot->speakers)<span class="mt-1 block whitespace-pre-line text-xs text-slate-500">{{ $slot->speakers }}</span>@endif</td><td class="px-4 py-4 text-xs text-slate-600">{{ $accepted ? 'Demande exposant acceptée' : ($direct ? ($slot->created_by ? 'Réservation directe' : 'Réservation directe (historique)') : ($slot->commercial_id ? 'Réservation commerciale' : ($slot->status->isTaken() ? 'Historique, origine non enregistrée' : 'Créneau du planning'))) }}</td></tr>
            @empty
                <tr><td colspan="6" class="px-4 py-9 text-center text-slate-500">Aucun créneau de planning.</td></tr>
            @endforelse
        </tbody></table></div></div>
    </section>

    <div class="grid gap-8 xl:grid-cols-2">
        <section><h2 class="mb-3 text-xl font-extrabold text-[#0F2347]">Demandes en attente <span class="text-sm font-semibold text-amber-600">({{ $pendingRequests->count() }})</span></h2><div class="space-y-3">
            @forelse ($pendingRequests as $request)
                <article class="rounded-2xl border border-amber-200 bg-white p-4 shadow-sm"><div class="flex flex-wrap items-start justify-between gap-2"><div><h3 class="font-bold text-[#0F2347]">{{ $request->exhibitor?->company_name ?? 'Exposant sans société renseignée' }}</h3><p class="mt-1 text-xs text-slate-500">{{ $request->exhibitor?->commercial?->name ?? 'Commercial non renseigné' }} · {{ $request->created_at?->format('d/m/Y H:i') }}</p></div><span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">En attente</span></div><p class="mt-3 text-sm font-semibold">{{ $request->title }}</p>@if ($request->speakers)<p class="mt-1 whitespace-pre-line text-sm text-slate-600">Intervenants : {{ $request->speakers }}</p>@endif<p class="mt-2 text-xs text-slate-500">Choix proposés : {{ $request->firstChoice?->full_label ?? '—' }}@if ($request->secondChoice) · {{ $request->secondChoice->full_label }}@endif</p></article>
            @empty
                <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-500">Aucune demande en attente.</p>
            @endforelse
        </div></section>
        <section><h2 class="mb-3 text-xl font-extrabold text-[#0F2347]">Demandes confirmées <span class="text-sm font-semibold text-emerald-700">({{ $acceptedRequests->count() }})</span></h2><div class="space-y-3">
            @forelse ($acceptedRequests as $request)
                <article class="rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm"><div class="flex flex-wrap items-start justify-between gap-2"><div><h3 class="font-bold text-[#0F2347]">{{ $request->exhibitor?->company_name ?? 'Exposant sans société renseignée' }}</h3><p class="mt-1 text-xs text-slate-500">Décision : {{ $request->processed_at?->format('d/m/Y H:i') ?? '—' }}</p></div><span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Confirmée</span></div><p class="mt-3 text-sm font-semibold">{{ $request->title }}</p>@if ($request->speakers)<p class="mt-1 whitespace-pre-line text-sm text-slate-600">Intervenants : {{ $request->speakers }}</p>@endif<p class="mt-2 text-xs text-slate-500">Créneau attribué : {{ $request->assignedSlot?->full_label ?? '—' }}</p></article>
            @empty
                <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-500">Aucune demande confirmée.</p>
            @endforelse
        </div></section>
    </div>
</x-technical.layout>
