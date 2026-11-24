<x-responsable-conferences.layout :user="auth()->user()">
    <div class="space-y-7">
        <header class="flex flex-wrap items-center gap-4"><x-profile-avatar :user="auth()->user()" class="size-16 shrink-0 text-xl" /><div><p class="text-sm font-semibold text-blue-600">Espace Responsable</p><h1 class="mt-1 text-3xl font-extrabold text-[#0F2347]">Tableau de bord</h1><p class="mt-2 text-sm text-slate-500">Suivi des demandes et du programme Conférences & Lounge.</p></div></header>
        <section class="grid gap-4 sm:grid-cols-3">
            @foreach ([['Demandes en attente',$pendingCount,'bg-amber-100 text-amber-800'],['Réservations confirmées',$confirmedCount,'bg-emerald-100 text-emerald-800'],['Décisions enregistrées',$decisionCount,'bg-blue-100 text-blue-800']] as [$label,$count,$classes])
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-semibold text-slate-500">{{ $label }}</p><p class="mt-2 text-3xl font-extrabold text-[#0F2347]">{{ $count }}</p></article>
            @endforeach
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3"><h2 class="text-lg font-bold text-[#0F2347]">Prochains créneaux</h2><a href="{{ route('responsable-conferences.planning') }}" class="text-sm font-bold text-blue-700">Ouvrir le planning</a></div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">@foreach ($upcomingSlots as $slot)<article class="rounded-xl border border-slate-100 p-4"><p class="font-bold text-[#0F2347]">{{ $slot->code }} · {{ $slot->space->type->label() }}</p><p class="mt-1 text-xs text-slate-500">{{ $slot->date->format('d/m/Y') }} · {{ $slot->time_range }}</p><p class="mt-2 text-sm">{{ $slot->status->label() }}</p></article>@endforeach</div>
        </section>
        <a href="{{ route('responsable-conferences.requests.index') }}" class="inline-flex rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700">Traiter les demandes en attente</a>
    </div>
</x-responsable-conferences.layout>
