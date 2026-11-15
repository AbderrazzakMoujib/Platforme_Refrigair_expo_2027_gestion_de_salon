<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercialInitials">
    <div>
        <p class="text-sm font-semibold text-[#2563EB]">Vue globale</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Bonjour, {{ $commercial->name }}</h1>
        <p class="mt-2 text-sm text-slate-500">Voici le suivi de vos exposants et activités.</p>
    </div>

    
<section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
         aria-label="Mes statistiques">

    <x-admin.stat-card
        label="Mes exposants"
        :value="$exhibitorsCount"
        icon="users"
        description="Nombre total de mes exposants."
    />

    <x-admin.stat-card
        label="Badges enregistrés"
        :value="$totalRegisteredBadges"
        icon="badge"
        description="Nombre de badges remplis."
    />

    <x-admin.stat-card
        label="Exposants complets"
        :value="$completedExhibitors"
        icon="badge"
        description="Exposants ayant rempli leur quota."
    />

    <x-admin.stat-card
        label="Exposants en attente"
        :value="$pendingExhibitors"
        icon="inbox"
        description="Exposants sans aucun badge."
    />

</section>


    <section class="mt-6 grid gap-6 xl:grid-cols-5">
        
<article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70 xl:col-span-3">

    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900">
                Progression générale des badges
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Suivi des badges de vos exposants.
            </p>
        </div>

        <span class="rounded-xl bg-blue-50 px-4 py-2 text-lg font-bold text-blue-700">
            {{ $badgeProgress }}%
        </span>
    </div>

    <div class="mt-7 grid grid-cols-3 gap-3 text-center">

        <div class="rounded-xl bg-slate-50 p-4">
            <p class="text-2xl font-bold text-slate-900">
                {{ $totalAuthorizedBadges }}
            </p>
            <p class="mt-1 text-xs text-slate-500">
                Autorisés
            </p>
        </div>

        <div class="rounded-xl bg-emerald-50 p-4">
            <p class="text-2xl font-bold text-emerald-700">
                {{ $totalRegisteredBadges }}
            </p>
            <p class="mt-1 text-xs text-slate-500">
                Enregistrés
            </p>
        </div>

        <div class="rounded-xl bg-amber-50 p-4">
            <p class="text-2xl font-bold text-amber-700">
                {{ $remainingBadges }}
            </p>
            <p class="mt-1 text-xs text-slate-500">
                Restants
            </p>
        </div>

    </div>

    <div class="mt-7">

        <div class="mb-2 flex justify-between text-sm">
            <span class="font-medium text-slate-600">
                Remplissage des badges
            </span>

            <span class="font-bold text-blue-700">
                {{ $badgeProgress }}%
            </span>
        </div>

        <div class="h-3 w-full overflow-hidden rounded-full bg-slate-100">
            <div
                class="h-full rounded-full bg-blue-600 transition-all duration-500"
                style="width: {{ $badgeProgress }}%">
            </div>
        </div>

    </div>

    <div class="mt-6 grid gap-3 sm:grid-cols-3">

        <div class="rounded-lg border border-emerald-100 p-3">
            <p class="text-xs text-slate-500">Complets</p>
            <p class="mt-1 text-xl font-bold text-emerald-600">
                {{ $completedExhibitors }}
            </p>
        </div>

        <div class="rounded-lg border border-amber-100 p-3">
            <p class="text-xs text-slate-500">Partiels</p>
            <p class="mt-1 text-xl font-bold text-amber-600">
                {{ $partialExhibitors }}
            </p>
        </div>

        <div class="rounded-lg border border-red-100 p-3">
            <p class="text-xs text-slate-500">En attente</p>
            <p class="mt-1 text-xl font-bold text-red-600">
                {{ $pendingExhibitors }}
            </p>
        </div>

    </div>

</article>

        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 xl:col-span-2">
            <h2 class="text-base font-bold text-slate-900">Mes derniers exposants</h2>
            <p class="mt-1 text-sm text-slate-500">Les inscriptions récentes qui vous sont attribuées.</p>
            @forelse ($recentExhibitors as $exhibitor)
                <a href="{{ route('commercial.exhibitors.show', $exhibitor) }}" class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-slate-100 p-3 hover:bg-slate-50">
                    
<span class="min-w-0">

    <span class="block truncate text-sm font-semibold text-slate-800">
        {{ $exhibitor->company_name }}
    </span>

    <span class="mt-1 block truncate text-xs text-slate-500">
        {{ $exhibitor->contact_name }} ·
        {{ $exhibitor->created_at?->format('d/m/Y') }}
    </span>

    <!-- Nombre de badges enregistrés -->
    <span class="mt-2 block text-xs font-medium text-blue-600">
        {{ $exhibitor->badges_count }}
        /
        {{ $exhibitor->authorized_badges }}
        badges enregistrés
    </span>

</span>
    
                    <span class="shrink-0 text-sm font-semibold text-[#2563EB]">Voir</span>
                </a>
            @empty
                <x-admin.empty-state class="mt-5" icon="users" title="Aucun exposant disponible" description="Ajoutez votre premier exposant pour commencer le suivi." />
            @endforelse
            <a href="{{ route('commercial.exhibitors.index') }}" class="mt-4 inline-block text-sm font-semibold text-[#2563EB] hover:underline">Voir tous mes exposants</a>
        </article>
    </section>
</x-commercial.layout>
