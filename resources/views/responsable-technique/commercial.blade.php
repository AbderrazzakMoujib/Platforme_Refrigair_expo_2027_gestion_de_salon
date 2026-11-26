<x-technical.layout :user="auth()->user()" title="Exposants du commercial">
    <div class="mb-6"><a class="text-sm font-semibold text-blue-700 hover:underline" href="{{ route('responsable-technique.commercials') }}">← Retour aux commerciaux</a><h1 class="mt-3 text-2xl font-black text-[#0F2347]">{{ $commercial->name }}</h1><p class="mt-1 text-sm text-slate-600">{{ $commercial->email }} · {{ $commercial->is_active ? 'Compte actif' : 'Compte inactif' }}</p></div>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-4">Entreprise</th><th class="px-5 py-4">Contact</th><th class="px-5 py-4">Email</th><th class="px-5 py-4">Stand</th></tr></thead><tbody class="divide-y divide-slate-100">
        @forelse ($exhibitors as $exhibitor)
            <tr><td class="px-5 py-4 font-bold text-[#0F2347]">{{ $exhibitor->company_name }}</td><td class="px-5 py-4">{{ $exhibitor->contact_name ?: '—' }}</td><td class="px-5 py-4">{{ $exhibitor->email ?: '—' }}</td><td class="px-5 py-4">{{ $exhibitor->stand_number ?: '—' }}</td></tr>
        @empty
            <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">Aucun exposant associé à ce commercial.</td></tr>
        @endforelse
    </tbody></table></div></div>
    <div class="mt-5">{{ $exhibitors->links() }}</div>
</x-technical.layout>
