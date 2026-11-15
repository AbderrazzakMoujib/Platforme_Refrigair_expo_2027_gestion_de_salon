<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold text-[#2563EB]">Gestion</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Commerciaux</h1>
            <p class="mt-2 text-sm text-slate-500">Gérez les comptes commerciaux de la plateforme.</p>
        </div>
        <a href="{{ route('admin.commerciaux.create') }}" class="inline-flex items-center justify-center rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Ajouter un commercial</a>
    </div>

    @if (session('status'))
        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <section class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
        <form method="GET" action="{{ route('admin.commerciaux.index') }}" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
            <label class="sr-only" for="search">Rechercher par nom ou email</label>
            <input id="search" name="search" value="{{ request('search') }}" placeholder="Rechercher nom ou email..." class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <label class="sr-only" for="status">Filtrer par statut</label>
            <select id="status" name="status" class="rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Tous les statuts</option>
                <option value="active" @selected(request('status') === 'active')>Actif</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
            </select>
            <button class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Filtrer</button>
        </form>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-[850px] w-full text-left text-sm">
                <thead class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-3 font-semibold">Nom complet</th><th class="px-3 py-3 font-semibold">Email</th><th class="px-3 py-3 font-semibold">Téléphone</th><th class="px-3 py-3 font-semibold">Statut</th><th class="px-3 py-3 font-semibold">Date de création</th><th class="px-3 py-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($commercials as $commercial)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-3 py-4 font-semibold text-slate-800">{{ $commercial->name }}</td>
                            <td class="px-3 py-4 text-slate-600">{{ $commercial->email }}</td>
                            <td class="px-3 py-4 text-slate-600">{{ $commercial->phone ?: '—' }}</td>
                            <td class="px-3 py-4"><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-700' => $commercial->is_active, 'bg-slate-100 text-slate-600' => ! $commercial->is_active])>{{ $commercial->is_active ? 'Actif' : 'Inactif' }}</span></td>
                            <td class="px-3 py-4 text-slate-600">{{ $commercial->created_at?->format('d/m/Y') }}</td>
                            <td class="px-3 py-4 text-right"><a href="{{ route('admin.commerciaux.show', $commercial) }}" class="font-semibold text-[#2563EB] hover:underline">Voir la fiche</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-8"><x-admin.empty-state title="Aucun commercial trouvé" description="Ajoutez un commercial ou ajustez la recherche et les filtres." icon="users" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $commercials->links() }}</div>
    </section>
</x-admin.layout>
