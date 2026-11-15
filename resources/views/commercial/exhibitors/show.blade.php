<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercialInitials">
    <a href="{{ route('commercial.exhibitors.index') }}" class="text-sm font-semibold text-[#2563EB] hover:underline">← Retour à mes exposants</a>
    @if (session('status')) <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div> @endif
    <section class="mt-5 max-w-3xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70 sm:p-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start"><div><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Fiche exposant</p><h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $exhibitor->company_name }}</h1><p class="mt-1 text-sm text-slate-500">{{ $exhibitor->contact_name }}</p></div><a href="{{ route('commercial.exhibitors.edit', $exhibitor) }}" class="rounded-lg bg-[#2563EB] px-4 py-2.5 text-center text-sm font-semibold text-white">Modifier</a></div>
        <dl class="mt-7 grid gap-5 border-t border-slate-100 pt-6 sm:grid-cols-2">
            <div><dt class="text-xs font-semibold uppercase text-slate-500">Accès compte</dt><dd class="mt-1 text-sm font-medium text-slate-800">{{ $accessStatus }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-500">Email</dt><dd class="mt-1 text-sm text-slate-800">{{ $exhibitor->email }}</dd></div><div><dt class="text-xs font-semibold uppercase text-slate-500">Téléphone</dt><dd class="mt-1 text-sm text-slate-800">{{ $exhibitor->phone }}</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-500">Stand</dt><dd class="mt-1 text-sm text-slate-800">{{ $exhibitor->stand_number }}</dd></div><div><dt class="text-xs font-semibold uppercase text-slate-500">Surface</dt><dd class="mt-1 text-sm text-slate-800">{{ $exhibitor->area_sqm }} m²</dd></div>
            <div><dt class="text-xs font-semibold uppercase text-slate-500">Badges autorisés</dt><dd class="mt-1 text-sm text-slate-800">{{ $exhibitor->authorized_badges }}</dd></div><div><dt class="text-xs font-semibold uppercase text-slate-500">Créé le</dt><dd class="mt-1 text-sm text-slate-800">{{ $exhibitor->created_at?->format('d/m/Y') }}</dd></div>
        </dl>
        @if (session('status'))<p class="mt-4 text-sm text-emerald-700">{{ session('status') }}</p>@endif
        @if (! $exhibitor->user)
            <form method="POST" action="{{ route('commercial.exhibitors.access', $exhibitor) }}" class="mt-6 grid gap-3 sm:grid-cols-2">@csrf
                <input name="password" type="password" required minlength="8" placeholder="Password initial" autocomplete="new-password" class="rounded-lg border-slate-300">
                <input name="password_confirmation" type="password" required minlength="8" placeholder="Confirmer password" autocomplete="new-password" class="rounded-lg border-slate-300">
                @error('password')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <button class="rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white">Créer un accès</button>
            </form>
        @else
            <form method="POST" action="{{ route('commercial.exhibitors.access.toggle', $exhibitor) }}" class="mt-6">@csrf @method('PATCH')<button class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold">{{ $exhibitor->user->is_active ? 'Désactiver l’accès' : 'Réactiver l’accès' }}</button></form>
        @endif
    </section>
</x-commercial.layout>
