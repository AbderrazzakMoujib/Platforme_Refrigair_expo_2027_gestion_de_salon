<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercialInitials">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="text-sm font-semibold text-[#2563EB]">Gestion</p><h1 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">Mes exposants</h1><p class="mt-2 text-sm text-slate-500">Gérez les entreprises qui vous sont associées.</p></div>
        <a href="{{ route('commercial.exhibitors.create') }}" class="rounded-lg bg-[#2563EB] px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-blue-700">Ajouter un exposant</a>
    </div>
    @if (session('status')) <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div> @endif
    <section class="mt-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
        <form method="GET" action="{{ route('commercial.exhibitors.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <label for="search" class="sr-only">Rechercher un exposant</label><input id="search" name="search" value="{{ request('search') }}" placeholder="Rechercher entreprise, responsable ou email..." class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <button class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Rechercher</button>
        </form>
        <div class="mt-5 overflow-x-auto"><table class="min-w-[760px] w-full text-left text-sm">
            <thead class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-3 py-3">Entreprise</th><th class="px-3 py-3">Responsable</th><th class="px-3 py-3">Email</th><th class="px-3 py-3">Stand</th><th class="px-3 py-3">Accès</th><th class="px-3 py-3 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($exhibitors as $exhibitor)
                    <tr class="hover:bg-slate-50/70"><td class="px-3 py-4 font-semibold text-slate-800">{{ $exhibitor->company_name }}</td><td class="px-3 py-4 text-slate-600">{{ $exhibitor->contact_name }}</td><td class="px-3 py-4 text-slate-600">{{ $exhibitor->email }}</td><td class="px-3 py-4 text-slate-600">{{ $exhibitor->stand_number }}</td><td class="px-3 py-4 text-slate-600">{{ ! $exhibitor->user ? 'Sans accès' : ($exhibitor->user->is_active ? 'Accès actif' : 'Désactivé') }}</td><td class="px-3 py-4 text-right"><a class="font-semibold text-[#2563EB] hover:underline" href="{{ route('commercial.exhibitors.show', $exhibitor) }}">Voir</a><span class="px-1 text-slate-300">·</span><a class="font-semibold text-[#2563EB] hover:underline" href="{{ route('commercial.exhibitors.edit', $exhibitor) }}">Modifier</a></td></tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-8"><x-admin.empty-state title="Aucun exposant trouvé" description="Ajoutez un exposant ou modifiez votre recherche." icon="users" /></td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="mt-5">{{ $exhibitors->links() }}</div>
    </section>
</x-commercial.layout>
