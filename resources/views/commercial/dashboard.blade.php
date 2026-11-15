<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercialInitials">
    <div>
        <p class="text-sm font-semibold text-[#2563EB]">Vue globale</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Bonjour, {{ $commercial->name }}</h1>
        <p class="mt-2 text-sm text-slate-500">Voici le suivi de vos exposants et activités.</p>
    </div>

    <section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Mes statistiques">
        <x-admin.stat-card label="Mes exposants" :value="$exhibitorsCount" icon="users" description="Exposants associés à votre compte." />
        <x-admin.stat-card label="Badges complets" value="—" icon="badge" description="Les données de badges ne sont pas encore disponibles." />
        <x-admin.stat-card label="Exposants en attente" value="—" icon="inbox" description="Les données de suivi ne sont pas encore disponibles." />
        <x-admin.stat-card label="Demandes d'équipements" value="—" icon="box" description="Le module Équipements arrive bientôt." />
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-5">
        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 xl:col-span-3">
            <h2 class="text-base font-bold text-slate-900">Progression générale des badges</h2>
            <p class="mt-1 text-sm text-slate-500">Avancement des badges de vos exposants.</p>
            <x-admin.empty-state class="mt-5" icon="badge" title="Aucune donnée de badges disponible" description="La progression s'affichera quand les modules Exposants et Badges seront disponibles." />
        </article>
        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 xl:col-span-2">
            <h2 class="text-base font-bold text-slate-900">Mes derniers exposants</h2>
            <p class="mt-1 text-sm text-slate-500">Les inscriptions récentes qui vous sont attribuées.</p>
            @forelse ($recentExhibitors as $exhibitor)
                <a href="{{ route('commercial.exhibitors.show', $exhibitor) }}" class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-slate-100 p-3 hover:bg-slate-50">
                    <span class="min-w-0"><span class="block truncate text-sm font-semibold text-slate-800">{{ $exhibitor->company_name }}</span><span class="mt-1 block truncate text-xs text-slate-500">{{ $exhibitor->contact_name }} · {{ $exhibitor->created_at?->format('d/m/Y') }}</span></span>
                    <span class="shrink-0 text-sm font-semibold text-[#2563EB]">Voir</span>
                </a>
            @empty
                <x-admin.empty-state class="mt-5" icon="users" title="Aucun exposant disponible" description="Ajoutez votre premier exposant pour commencer le suivi." />
            @endforelse
            <a href="{{ route('commercial.exhibitors.index') }}" class="mt-4 inline-block text-sm font-semibold text-[#2563EB] hover:underline">Voir tous mes exposants</a>
        </article>
    </section>
</x-commercial.layout>
