
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

<div class="space-y-7">

    <!-- HEADER -->
    <nav class="flex gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.index') }}" class="hover:text-blue-600">
            Administration
        </a>
        <span>/</span>
        <span class="font-semibold text-[#0F2347]">
            Commerciaux
        </span>
    </nav>

    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <span class="grid size-14 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <x-admin.icon name="users" class="size-7"/>
            </span>

            <div>
                <h1 class="text-3xl font-bold text-[#0F2347]">
                    Gestion des commerciaux
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Gérez les comptes commerciaux et leurs exposants.
                </p>
            </div>
        </div>

        <a href="{{ route('admin.commerciaux.create') }}"
           class="rounded-xl bg-[#2563EB] px-5 py-3 text-center text-sm font-semibold text-white hover:bg-blue-700">
            + Ajouter un commercial
        </a>
    </header>

    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <!-- STATISTICS -->
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach ([
            ['Total commerciaux', $totalCommercials, 'bg-blue-100 text-blue-700'],
            ['Actifs', $activeCommercials, 'bg-emerald-100 text-emerald-700'],
            ['Inactifs', $inactiveCommercials, 'bg-red-100 text-red-700'],
            ['Exposants gérés', $totalExhibitors, 'bg-violet-100 text-violet-700'],
        ] as $stat)
            <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <span class="grid size-12 place-items-center rounded-xl {{ $stat[2] }}">
                    <x-admin.icon name="users" class="size-6"/>
                </span>
                <div>
                    <p class="text-xs font-semibold text-slate-500">
                        {{ $stat[0] }}
                    </p>
                    <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                        {{ $stat[1] }}
                    </p>
                </div>
            </article>
        @endforeach

    </section>

    <!-- TABLE CARD -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 p-5 sm:p-6">
            <h2 class="text-lg font-bold text-[#0F2347]">
                Liste des commerciaux
            </h2>

            <form method="GET"
                  action="{{ route('admin.commerciaux.index') }}"
                  class="mt-5 flex flex-col gap-3 sm:flex-row">

                <input type="search"
                       name="search"
                       value="{{ request('search') }}"
                       maxlength="255"
                       placeholder="Rechercher nom, email, téléphone..."
                       aria-label="Rechercher un commercial"
                       class="min-w-0 flex-1 rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">

                <select name="status"
                        aria-label="Statut"
                        class="rounded-xl border border-slate-200 px-4 py-3 text-sm">
                    <option value="">Tous les statuts</option>
                    <option value="active" @selected(request('status') === 'active')>
                        Actif
                    </option>
                    <option value="inactive" @selected(request('status') === 'inactive')>
                        Inactif
                    </option>
                </select>

                <button type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                    Filtrer
                </button>

                <a href="{{ route('admin.commerciaux.index') }}"
                   class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Réinitialiser
                </a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">

                <thead class="bg-[#F5F7FB] text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Commercial</th>
                        <th class="px-5 py-4">Email</th>
                        <th class="px-5 py-4">Téléphone</th>
                        <th class="px-5 py-4">Exposants</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse ($commercials as $commercial)
                        <tr class="hover:bg-blue-50/30">

                            <!-- PHOTO + NAME -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">

                                    @if ($commercial->profile_photo_url)
                                        <img src="{{ $commercial->profile_photo_url }}"
                                             alt="Photo de {{ $commercial->name }}"
                                             class="size-12 shrink-0 rounded-full border border-slate-200 object-cover">
                                    @else
                                        <span class="grid size-12 shrink-0 place-items-center rounded-full bg-blue-100 text-sm font-bold text-blue-700">
                                            {{ $commercial->initials }}
                                        </span>
                                    @endif

                                    <div>
                                        <a href="{{ route('admin.commerciaux.show', $commercial) }}"
                                           class="font-bold text-[#0F2347] hover:text-blue-600">
                                            {{ $commercial->name }}
                                        </a>
                                        <p class="mt-1 text-xs text-slate-400">
                                            Commercial #{{ $commercial->id }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                {{ $commercial->email }}
                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                {{ $commercial->phone ?: '—' }}
                            </td>

                            <td class="px-5 py-4">
                                <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">
                                    {{ $commercial->exhibitors_count }}
                                    exposant(s)
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <span @class([
                                    'inline-flex items-center rounded-full px-3 py-1.5 text-xs font-bold',
                                    'bg-emerald-50 text-emerald-700' => $commercial->is_active && $commercial->password !== null,
                                    'bg-amber-50 text-amber-800' => $commercial->is_active && $commercial->password === null,
                                    'bg-red-50 text-red-700' => !$commercial->is_active,
                                ])>
                                    {{ \App\Models\CommercialInvitation::statusFor($commercial) }}
                                </span>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">

                                    <a href="{{ route('admin.commerciaux.show', $commercial) }}"
                                       title="Voir la fiche"
                                       class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-blue-600 hover:bg-blue-50">
                                        Voir
                                    </a>

                                    <a href="{{ route('admin.commerciaux.edit', $commercial) }}"
                                       title="Modifier"
                                       class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                        Modifier
                                    </a>

                                    @if ($commercial->password === null && $commercial->is_active)
                                        <form method="POST" action="{{ route('admin.commerciaux.invitation', $commercial) }}">@csrf<button class="rounded-lg border border-blue-200 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">Renvoyer l’invitation</button></form>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <p class="font-bold text-slate-700">
                                    Aucun commercial trouvé
                                </p>
                                <p class="mt-2 text-sm text-slate-500">
                                    Ajoutez un commercial ou modifiez vos filtres.
                                </p>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 p-5">
            {{ $commercials->links() }}
        </div>

    </section>
</div>

</x-admin.layout>
