<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">
    <a href="{{ route('admin.commerciaux.index') }}" class="text-sm font-semibold text-[#2563EB] hover:underline">← Retour aux commerciaux</a>
    @if (session('status')) <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div> @endif

    <section class="mt-5 max-w-3xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70 sm:p-8">
        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-start">
            <div class="flex items-center gap-4">
                <span class="grid size-14 place-items-center rounded-2xl bg-blue-50 text-lg font-bold text-[#2563EB]">{{ mb_strtoupper(mb_substr($commercial->name, 0, 1)) }}</span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fiche commercial</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $commercial->name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ $commercial->email }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.commerciaux.edit', $commercial) }}" class="rounded-lg bg-[#2563EB] px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-blue-700">Modifier</a>
            </div>
        </div>
        <dl class="mt-8 grid gap-5 border-t border-slate-100 pt-6 sm:grid-cols-2">
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Téléphone</dt><dd class="mt-1 text-sm font-medium text-slate-800">{{ $commercial->phone ?: '—' }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Statut</dt><dd class="mt-1"><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-700' => $commercial->is_active, 'bg-slate-100 text-slate-600' => ! $commercial->is_active])>{{ $commercial->is_active ? 'Actif' : 'Inactif' }}</span></dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Date de création</dt><dd class="mt-1 text-sm font-medium text-slate-800">{{ $commercial->created_at?->format('d/m/Y à H:i') }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Accès plateforme</dt><dd class="mt-1 text-sm font-medium text-amber-700">{{ $accessStatus }}</dd></div>
        </dl>
        @error('invitation') <p class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p> @enderror
    </section>
</x-admin.layout>
