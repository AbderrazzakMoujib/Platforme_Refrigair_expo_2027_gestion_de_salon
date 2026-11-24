<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">
    <div class="mx-auto max-w-7xl space-y-6">
        <header class="flex flex-wrap items-center justify-between gap-4">
            <div><p class="text-sm font-semibold text-blue-600">Administration</p><h1 class="mt-1 text-3xl font-extrabold text-[#0F2347]">Responsables Conférences & Lounge</h1><p class="mt-2 text-sm text-slate-500">Création, accès et sécurité des comptes responsables.</p></div>
            <a href="{{ route('admin.responsables-conferences.create') }}" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700">Créer un compte</a>
        </header>
        @if (session('status'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>@endif
        <form method="GET" class="flex gap-3 rounded-2xl border border-slate-200 bg-white p-4"><input type="search" name="search" value="{{ request('search') }}" placeholder="Rechercher un nom ou e-mail" class="min-w-0 flex-1 rounded-xl border-slate-300"><button class="rounded-xl bg-[#0F2347] px-5 py-2 text-sm font-semibold text-white">Rechercher</button></form>
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="w-full min-w-[700px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-4">Responsable</th><th class="p-4">E-mail</th><th class="p-4">Accès</th><th class="p-4">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($managers as $manager)
                        <tr><td class="p-4"><div class="flex items-center gap-3"><x-profile-avatar :user="$manager" class="size-11 shrink-0 text-sm" /><span class="font-semibold text-[#0F2347]">{{ $manager->name }}</span></div></td><td class="p-4">{{ $manager->email }}</td><td class="p-4"><span @class(['rounded-full px-3 py-1 text-xs font-bold', 'bg-emerald-100 text-emerald-800' => $manager->is_active, 'bg-red-100 text-red-800' => ! $manager->is_active])>{{ $manager->is_active ? 'Actif' : 'Accès révoqué' }}</span></td><td class="space-y-2 p-4">
                            <a href="{{ route('admin.responsables-conferences.edit', $manager) }}" class="font-semibold text-blue-700">Modifier</a>
                            <form method="POST" action="{{ route('admin.responsables-conferences.access', $manager) }}">@csrf @method('PATCH')<button class="font-semibold {{ $manager->is_active ? 'text-red-700' : 'text-emerald-700' }}">{{ $manager->is_active ? 'Révoquer l’accès' : 'Réactiver' }}</button></form>
                            <details><summary class="cursor-pointer font-semibold text-slate-700">Réinitialiser le mot de passe</summary><form method="POST" action="{{ route('admin.responsables-conferences.password', $manager) }}" class="mt-2 grid gap-2">@csrf @method('PUT')<input type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Nouveau mot de passe" class="rounded-lg border-slate-300 text-sm"><input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Confirmer" class="rounded-lg border-slate-300 text-sm"><button class="rounded-lg bg-slate-800 px-3 py-2 text-xs font-bold text-white">Enregistrer le mot de passe</button></form></details>
                        </td></tr>
                    @empty
                        <tr><td colspan="4" class="p-8 text-center text-slate-500">Aucun responsable trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table></div>
            <div class="border-t border-slate-100 p-4">{{ $managers->links() }}</div>
        </section>
    </div>
</x-admin.layout>
