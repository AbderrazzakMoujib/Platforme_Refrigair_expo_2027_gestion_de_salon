<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-[#2563EB]">Vue globale</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Bonjour, {{ $admin->name }}</h1>
            <p class="mt-2 text-sm text-slate-500">Suivez les données disponibles de Refrigair Expo 2027.</p>
        </div>
        <span class="w-fit rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-[#2563EB]">Données en temps réel</span>
    </div>

    <section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Statistiques principales">
        <x-admin.stat-card label="Commerciaux" :value="$commercialsCount" icon="users" description="Comptes commerciaux actifs ou non." />
        <x-admin.stat-card label="Exposants" :value="$exposantsCount" icon="users" description="Comptes exposants créés." />
        <x-admin.stat-card label="Badges enregistrés" value="—" icon="badge" description="Module badges pas encore développé." />
        <x-admin.stat-card label="Demandes d'équipements" value="—" icon="box" description="Module équipements pas encore développé." />
    </section>

    <section class="mt-6 grid gap-6 xl:grid-cols-5">
        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 xl:col-span-3">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Progression générale des badges</h2>
                    <p class="mt-1 text-sm text-slate-500">Avancement des badges enregistrés.</p>
                </div>
                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">Bientôt disponible</span>
            </div>
            <x-admin.empty-state class="mt-5" icon="badge" title="Aucune donnée de badges disponible" description="Le module Badges n'est pas encore développé, aucune progression ne peut être calculée." />
        </article>

        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 xl:col-span-2">
            <h2 class="text-base font-bold text-slate-900">Activités récentes</h2>
            <p class="mt-1 text-sm text-slate-500">Derniers comptes créés.</p>
            <div class="mt-5 space-y-4">
                @forelse ($recentAccounts as $account)
                    <div class="flex gap-3">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-blue-50 text-xs font-bold text-[#2563EB]">{{ mb_strtoupper(mb_substr($account->name, 0, 1)) }}</span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ $account->name }}</p>
                            <p class="text-xs text-slate-500">Compte {{ $account->role->value }} créé le {{ $account->created_at?->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <x-admin.empty-state title="Aucune activité disponible" description="Aucun compte n'a encore été créé." />
                @endforelse
            </div>
        </article>
    </section>

    <section class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
            <div>
                <h2 class="text-base font-bold text-slate-900">Suivi par commercial</h2>
                <p class="mt-1 text-sm text-slate-500">Suivi des exposants, badges et demandes par commercial.</p>
            </div>
            <span class="w-fit rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">Bientôt disponible</span>
        </div>
        <div class="mt-5 overflow-x-auto rounded-xl border border-slate-100">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-semibold">Commercial</th>
                        <th class="px-4 py-3 font-semibold">Exposants suivis</th>
                        <th class="px-4 py-3 font-semibold">Badges</th>
                        <th class="px-4 py-3 font-semibold">Demandes</th>
                        <th class="px-4 py-3 font-semibold">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="px-4 py-8">
                            <x-admin.empty-state icon="users" title="Aucune donnée de suivi disponible" description="Les affectations commerciales ne sont pas encore développées." />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</x-admin.layout>
