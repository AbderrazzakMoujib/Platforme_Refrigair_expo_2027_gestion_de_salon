<x-technical.layout :user="auth()->user()" title="Suivi des commerciaux">
    <div class="mb-6"><p class="text-sm font-semibold text-blue-700">Activité commerciale</p><h1 class="mt-1 text-2xl font-black text-[#0F2347]">Commerciaux</h1><p class="mt-2 text-sm text-slate-600">Consultation des comptes et du nombre d’exposants associés.</p></div>
    <form method="GET" class="mb-5 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_220px_auto]">
        <label class="text-sm font-semibold text-slate-700">Recherche<input name="search" value="{{ $filters['search'] }}" class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#2563EB] focus:ring-[#2563EB]" placeholder="Nom ou email"></label>
        <label class="text-sm font-semibold text-slate-700">Compte<select name="status" class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#2563EB] focus:ring-[#2563EB]"><option value="all" @selected($filters['status'] === 'all')>Tous</option><option value="active" @selected($filters['status'] === 'active')>Actifs</option><option value="inactive" @selected($filters['status'] === 'inactive')>Inactifs</option></select></label>
        <button class="self-end rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-bold text-white hover:bg-blue-700">Filtrer</button>
    </form>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-4">Commercial</th><th class="px-5 py-4">Email professionnel</th><th class="px-5 py-4">Compte</th><th class="px-5 py-4">Exposants associés</th><th class="px-5 py-4">Consultation</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse ($commercials as $commercial)
                <tr><td class="whitespace-nowrap px-5 py-4 font-bold text-[#0F2347]">{{ $commercial->name }}</td><td class="px-5 py-4 text-slate-600">{{ $commercial->email }}</td><td class="px-5 py-4"><span @class(['rounded-full px-3 py-1 text-xs font-bold', 'bg-emerald-100 text-emerald-800' => $commercial->is_active, 'bg-slate-100 text-slate-600' => ! $commercial->is_active])>{{ $commercial->is_active ? 'Actif' : 'Inactif' }}</span></td><td class="px-5 py-4 font-semibold">{{ $commercial->exhibitors_count }}</td><td class="px-5 py-4"><a class="font-bold text-blue-700 hover:underline" href="{{ route('responsable-technique.commercial', $commercial) }}">Voir les exposants</a></td></tr>
            @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Aucun commercial ne correspond à ces critères.</td></tr>
            @endforelse
        </tbody></table></div>
    </div>
    <div class="mt-5">{{ $commercials->links() }}</div>
</x-technical.layout>
