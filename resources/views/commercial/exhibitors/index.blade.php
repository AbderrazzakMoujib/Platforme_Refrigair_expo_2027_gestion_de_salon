<x-commercial.layout
    :commercial="$commercial"
    :commercial-initials="$commercialInitials"
>
    @php
        $cards = [
            ['Total exposants', $totalExhibitors, '#2563EB', '#EFF6FF'],
            ['Accès actifs', $activeExhibitors, '#059669', '#ECFDF5'],
            ['Accès désactivés', $inactiveExhibitors, '#DC2626', '#FEF2F2'],
            ['Sans accès', $withoutAccess, '#D97706', '#FFF7ED'],
        ];
    @endphp

    <div class="min-w-0 space-y-6">

        {{-- HEADER --}}

        <section class="flex flex-wrap items-center justify-between gap-4">

            <div>
                <p class="text-xs font-semibold text-blue-600">
                    <a href="{{ route('commercial.index') }}">
                        Vue globale
                    </a>
                    / Mes exposants
                </p>

                <h1 class="mt-2 text-3xl font-extrabold text-[#0F2347]">
                    Mes exposants
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Gérez et suivez les entreprises qui vous sont associées.
                </p>
            </div>

            <a href="{{ route('commercial.exhibitors.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">

                <x-admin.icon name="user-plus" class="size-5"/>

                Ajouter un exposant
            </a>

        </section>

        @if (session('status'))
            <div role="status"
                 class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- STATISTICS --}}

        <section class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-4">

            @foreach ($cards as [$label, $value, $color, $background])

                <article class="rounded-2xl border border-slate-200 p-5 shadow-sm"
                         style="background:linear-gradient(145deg,#fff,{{ $background }})">

                    <div class="flex items-center gap-4">

                        <span class="grid size-12 shrink-0 place-items-center rounded-full"
                              style="background:{{ $background }};color:{{ $color }}">

                            <x-admin.icon name="users" class="size-6"/>

                        </span>

                        <div>
                            <p class="text-sm font-semibold text-[#0F2347]">
                                {{ $label }}
                            </p>

                            <p class="mt-2 text-3xl font-extrabold text-[#0F2347]">{{ $value }}</p>
                        </div>

                    </div>

                </article>

            @endforeach

        </section>

        {{-- LIST --}}

        <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">

                <div>
                    <h2 class="font-bold text-[#0F2347]">
                        Liste des exposants
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Informations et suivi de vos exposants.
                    </p>
                </div>

                <span class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">
                    {{ $exhibitors->total() }} résultat(s)
                </span>

            </div>

            {{-- SEARCH --}}

            <form method="GET"
                  action="{{ route('commercial.exhibitors.index') }}"
                  class="flex flex-col gap-3 border-b border-slate-100 p-5 xl:flex-row">

                <label for="search" class="sr-only">
                    Rechercher un exposant
                </label>

                <input id="search"
                       name="search"
                       type="search"
                       value="{{ request('search') }}"
                       placeholder="Entreprise, responsable, email, stand..."
                       class="h-11 min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm focus:border-blue-500 focus:ring-blue-500">

                <label for="status" class="sr-only">
                    Statut d'accès
                </label>

                <select id="status"
                        name="status"
                        class="h-11 rounded-xl border border-slate-200 px-4 text-sm">

                    <option value="">Tous les statuts</option>

                    <option value="active" @selected(request('status') === 'active')>
                        Accès actif
                    </option>

                    <option value="inactive" @selected(request('status') === 'inactive')>
                        Désactivé
                    </option>

                    <option value="no_access" @selected(request('status') === 'no_access')>
                        Sans accès
                    </option>

                </select>

                <button type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                    Rechercher
                </button>

                @if (request()->filled('search') || request()->filled('status'))

                    <a href="{{ route('commercial.exhibitors.index') }}"
                       class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm text-slate-600 hover:bg-slate-50">
                        Réinitialiser
                    </a>

                @endif

            </form>

            {{-- TABLE --}}

            <div class="overflow-x-auto">

                <table class="w-full min-w-[950px] text-left text-sm">

                    <thead class="bg-slate-50 text-xs font-semibold text-slate-600">

                        <tr>
                            <th scope="col" class="px-5 py-4">Entreprise</th>
                            <th scope="col" class="px-4 py-4">Responsable</th>
                            <th scope="col" class="px-4 py-4">Stand</th>
                            <th scope="col" class="px-4 py-4">Surface</th>
                            <th scope="col" class="px-4 py-4">Badges</th>
                            <th scope="col" class="px-4 py-4">Statut d'accès</th>
                            <th scope="col" class="px-5 py-4 text-right">Actions</th>
                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse ($exhibitors as $exhibitor)

                            @php
                                $quota = (int) $exhibitor->authorized_badges;
                                $used = (int) $exhibitor->badges_count;

                                $progress = $quota > 0
                                    ? min(100, round($used / $quota * 100))
                                    : 0;

                                $status = ! $exhibitor->user
                                    ? 'Sans accès'
                                    : ($exhibitor->user->is_active
                                        ? 'Accès actif'
                                        : 'Désactivé');

                                $statusClass = match ($status) {
                                    'Accès actif' => 'bg-emerald-50 text-emerald-700',
                                    'Désactivé' => 'bg-red-50 text-red-700',
                                    default => 'bg-amber-50 text-amber-700',
                                };
                            @endphp

                            <tr class="hover:bg-slate-50">

                                {{-- LOGO ENTREPRISE --}}

                                <td class="px-5 py-4">

                                    <div class="flex items-center gap-3">

                                        <div class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white">

                                            @if ($exhibitor->logo_url)

                                                <img src="{{ $exhibitor->logo_url }}"
                                                     alt="Logo {{ $exhibitor->company_name }}"
                                                     loading="lazy"
                                                     class="h-full w-full object-contain p-1.5">

                                            @else

                                                <span class="font-bold text-blue-700">
                                                    {{ $exhibitor->initials }}
                                                </span>

                                            @endif

                                        </div>

                                        <div>

                                            <a href="{{ route('commercial.exhibitors.show', $exhibitor) }}"
                                               class="font-bold text-[#0F2347] hover:text-blue-600">

                                                {{ $exhibitor->company_name }}

                                            </a>

                                            <p class="mt-1 text-xs text-slate-400">
                                                #EXP-{{ str_pad((string) $exhibitor->id, 4, '0', STR_PAD_LEFT) }}
                                            </p>

                                        </div>

                                    </div>

                                </td>

                                <td class="px-4 py-4">

                                    <p class="font-semibold text-slate-700">
                                        {{ $exhibitor->contact_name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $exhibitor->email }}
                                    </p>

                                </td>

                                <td class="px-4 py-4">

                                    <span class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700">
                                        {{ $exhibitor->stand_number }}
                                    </span>

                                </td>

                                <td class="whitespace-nowrap px-4 py-4">
                                    {{ $exhibitor->area_sqm }} m²
                                </td>

                                <td class="px-4 py-4">

                                    <div class="flex justify-between gap-3 text-xs">
                                        <span class="font-bold">{{ $used }}/{{ $quota }}</span>
                                        <span>{{ $progress }}%</span>
                                    </div>

                                    <div class="mt-2 h-1.5 min-w-24 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-blue-600"
                                             style="width:{{ $progress }}%">
                                        </div>
                                    </div>

                                </td>

                                <td class="px-4 py-4">

                                    <span class="whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-semibold {{ $statusClass }}">
                                        {{ $status }}
                                    </span>

                                </td>

                                <td class="px-5 py-4">

                                    <div class="flex justify-end gap-2">

                                        <a href="{{ route('commercial.exhibitors.show', $exhibitor) }}"
                                           class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-blue-600 hover:bg-blue-50">
                                            Voir
                                        </a>

                                        <a href="{{ route('commercial.exhibitors.edit', $exhibitor) }}"
                                           class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                                            Modifier
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="p-10">

                                    <x-admin.empty-state
                                        title="Aucun exposant trouvé"
                                        description="Ajoutez un exposant ou modifiez votre recherche."
                                        icon="users"
                                    />

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $exhibitors->links() }}
            </div>

        </section>

        <footer class="border-t border-slate-200 py-4 text-center text-xs text-slate-400">
            © 2027 Refrigair Expo — Espace Commercial
        </footer>

    </div>

</x-commercial.layout>