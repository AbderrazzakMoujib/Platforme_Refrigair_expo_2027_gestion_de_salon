<x-commercial.layout
    :commercial="$commercial"
    :commercial-initials="$commercialInitials"
>

    @php
        $hasAccount = $exhibitor->user !== null;
        $isActive = $hasAccount && $exhibitor->user->is_active;

        $quota = (int) $exhibitor->authorized_badges;
        $used = (int) $exhibitor->badges_count;
        $remaining = max(0, $quota - $used);

        $progress = $quota > 0
            ? min(100, round($used / $quota * 100))
            : 0;

        $statusClass = match (true) {
            ! $hasAccount => 'bg-amber-50 text-amber-700 border-amber-100',
            $isActive => 'bg-emerald-50 text-emerald-700 border-emerald-100',
            default => 'bg-red-50 text-red-700 border-red-100',
        };

        $stats = [
            ['Surface du stand', $exhibitor->area_sqm . ' m²', 'layers', '#2563EB', '#EFF6FF'],
            ['Badges autorisés', $quota, 'badge', '#7C3AED', '#F5F3FF'],
            ['Badges enregistrés', $used, 'clipboard-check', '#059669', '#ECFDF5'],
            ['Badges disponibles', $remaining, 'badge-users', '#D97706', '#FFF7ED'],
        ];

        $information = [
            ['Nom de l’entreprise', $exhibitor->company_name, 'storefront'],
            ['Responsable', $exhibitor->contact_name, 'users'],
            ['Email', $exhibitor->email, 'inbox'],
            ['Téléphone', $exhibitor->phone, 'users'],
            ['Stand', $exhibitor->stand_number, 'box'],
            ['Surface', $exhibitor->area_sqm . ' m²', 'layers'],
            ['Créé le', $exhibitor->created_at?->format('d/m/Y'), 'calendar'],
            ['Commercial responsable', $commercial->name, 'users'],
        ];
    @endphp

    <div class="min-w-0 space-y-6">

        {{-- BREADCRUMB --}}

        <nav class="flex flex-wrap gap-2 text-xs font-medium text-slate-500">

            <a href="{{ route('commercial.index') }}"
               class="hover:text-blue-600">
                Vue globale
            </a>

            <span>/</span>

            <a href="{{ route('commercial.exhibitors.index') }}"
               class="hover:text-blue-600">
                Mes exposants
            </a>

            <span>/</span>

            <span class="text-blue-600">
                Fiche exposant
            </span>

        </nav>

        {{-- HEADER --}}

        <section class="flex flex-wrap items-center justify-between gap-4">

            <div>

                <h1 class="text-3xl font-extrabold text-[#0F2347]">
                    Fiche exposant
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Consultez et gérez les informations de votre exposant.
                </p>

            </div>

            <div class="flex flex-wrap gap-3">

                <a href="{{ route('commercial.exhibitors.index') }}"
                   class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600">
                    ← Retour à mes exposants
                </a>

                <a href="{{ route('commercial.exhibitors.edit', $exhibitor) }}"
                   class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                    Modifier
                </a>

            </div>

        </section>

        @if (session('status'))

            <div role="status"
                 class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                {{ session('status') }}
            </div>

        @endif

        @if ($errors->any())

            <div role="alert"
                 class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">

                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach

            </div>

        @endif

        {{-- COMPANY HEADER --}}

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="h-2 bg-gradient-to-r from-[#0F2347] to-[#2563EB]"></div>

            <div class="flex flex-wrap items-center justify-between gap-6 p-6">

                <div class="flex min-w-0 items-center gap-5">

                    {{-- LOGO --}}

                    <div class="grid size-24 shrink-0 place-items-center overflow-hidden rounded-2xl border border-slate-200 bg-white">

                        @if ($exhibitor->logo_url)

                            <img src="{{ $exhibitor->logo_url }}"
                                 alt="Logo {{ $exhibitor->company_name }}"
                                 class="h-full w-full object-contain p-2">

                        @else

                            <span class="text-3xl font-extrabold text-blue-700">
                                {{ $exhibitor->initials }}
                            </span>

                        @endif

                    </div>

                    <div class="min-w-0">

                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">
                            Entreprise exposante
                        </p>

                        <h2 class="mt-2 break-words text-2xl font-extrabold text-[#0F2347]">
                            {{ $exhibitor->company_name }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $exhibitor->contact_name }}
                        </p>

                        <div class="mt-3 flex flex-wrap gap-2">

                            <span class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600">
                                #EXP-{{ str_pad((string) $exhibitor->id, 4, '0', STR_PAD_LEFT) }}
                            </span>

                            <span class="rounded-full border px-3 py-2 text-xs font-semibold {{ $statusClass }}">
                                {{ $accessStatus }}
                            </span>

                        </div>

                    </div>

                </div>

                <div class="rounded-xl border border-blue-100 bg-blue-50 px-6 py-4">

                    <p class="text-xs font-semibold text-blue-600">
                        Numéro du stand
                    </p>

                    <p class="mt-2 text-2xl font-extrabold text-[#0F2347]">
                        {{ $exhibitor->stand_number }}
                    </p>

                </div>

            </div>

        </section>

        {{-- STATISTICS --}}

        <section class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-4">

            @foreach ($stats as [$label, $value, $icon, $color, $background])

                <article class="rounded-2xl border border-slate-200 p-5 shadow-sm"
                         style="background:linear-gradient(145deg,#fff,{{ $background }})">

                    <div class="flex items-center gap-4">

                        <span class="grid size-12 shrink-0 place-items-center rounded-full"
                              style="background:{{ $background }};color:{{ $color }}">

                            <x-admin.icon :name="$icon" class="size-6"/>

                        </span>

                        <div>

                            <p class="text-xs font-semibold text-slate-600">
                                {{ $label }}
                            </p>

                            <p class="mt-2 text-2xl font-extrabold text-[#0F2347]">{{ $value }}</p>

                        </div>

                    </div>

                </article>

            @endforeach

        </section>

        <section class="grid min-w-0 items-start gap-6 xl:grid-cols-3">

            {{-- COMPANY INFORMATION --}}

            <article class="rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">

                <div class="flex items-center gap-3 border-b border-slate-100 p-5">

                    <x-admin.icon name="storefront" class="size-6 text-blue-600"/>

                    <div>
                        <h2 class="font-bold text-[#0F2347]">
                            Informations de l'entreprise
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Coordonnées et informations générales.
                        </p>
                    </div>

                </div>

                <dl class="grid gap-4 p-5 sm:grid-cols-2">

                    @foreach ($information as [$label, $value, $icon])

                        <div class="flex min-w-0 items-start gap-3 rounded-xl border border-slate-100 bg-slate-50 p-4">

                            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-white text-blue-600">

                                <x-admin.icon :name="$icon" class="size-4"/>

                            </span>

                            <div class="min-w-0">

                                <dt class="text-xs text-slate-500">
                                    {{ $label }}
                                </dt>

                                <dd class="mt-2 break-words text-sm font-semibold text-[#0F2347]">
                                    {{ $value ?: '—' }}
                                </dd>

                            </div>

                        </div>

                    @endforeach

                </dl>

            </article>

            {{-- ACCESS MANAGEMENT --}}

            <article class="rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="flex items-center gap-3 border-b border-slate-100 p-5">

                    <x-admin.icon name="settings" class="size-6 text-violet-600"/>

                    <h2 class="font-bold text-[#0F2347]">
                        Gestion de l'accès
                    </h2>

                </div>

                <div class="space-y-5 p-5">

                    <div class="rounded-xl border p-4 {{ $statusClass }}">

                        <p class="text-xs">Accès compte</p>

                        <p class="mt-2 font-bold">{{ $accessStatus }}</p>

                        <p class="mt-2 text-xs leading-relaxed">

                            @if (! $hasAccount)
                                Aucun compte n'est associé à cet exposant.
                            @elseif ($isActive)
                                L'exposant peut accéder à son espace personnel.
                            @else
                                L'accès à la plateforme est désactivé.
                            @endif

                        </p>

                    </div>

                    @if (! $hasAccount)

                        <form method="POST"
                              action="{{ route('commercial.exhibitors.access', $exhibitor) }}"
                              class="space-y-4">

                            @csrf

                            <div>

                                <label for="password" class="text-xs font-semibold">
                                    Mot de passe initial
                                </label>

                                <input id="password"
                                       name="password"
                                       type="password"
                                       required
                                       minlength="8"
                                       autocomplete="new-password"
                                       class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm">

                            </div>

                            <div>

                                <label for="password_confirmation" class="text-xs font-semibold">
                                    Confirmer le mot de passe
                                </label>

                                <input id="password_confirmation"
                                       name="password_confirmation"
                                       type="password"
                                       required
                                       minlength="8"
                                       autocomplete="new-password"
                                       class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm">

                            </div>

                            <button type="submit"
                                    class="w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                                Créer un accès
                            </button>

                        </form>

                    @else

                        <div>

                            <p class="text-xs text-slate-500">
                                Identifiant de connexion
                            </p>

                            <p class="mt-2 break-all text-sm font-bold text-[#0F2347]">
                                {{ $exhibitor->user->email }}
                            </p>

                            <p class="mt-4 text-xs text-slate-500">
                                Type de compte : Exposant
                            </p>

                        </div>

                        <form method="POST"
                              action="{{ route('commercial.exhibitors.access.toggle', $exhibitor) }}"
                              onsubmit="return confirm('Confirmer la modification de cet accès ?')">

                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="w-full rounded-xl border px-4 py-3 text-sm font-semibold {{ $isActive
                                        ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'
                                        : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">

                                {{ $isActive ? 'Désactiver l’accès' : 'Réactiver l’accès' }}

                            </button>

                        </form>

                    @endif

                </div>

            </article>

            {{-- BADGES --}}

            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">

                <h2 class="font-bold text-[#0F2347]">
                    Progression des badges
                </h2>

                <div class="mt-6 flex flex-col items-center gap-6 md:flex-row">

                    <div class="grid size-40 shrink-0 place-items-center rounded-full"
                         style="background:conic-gradient(#2563EB {{ $progress }}%,#E2E8F0 0)">

                        <div class="grid size-32 place-content-center rounded-full bg-white text-center">

                            <p class="text-3xl font-extrabold text-[#0F2347]">
                                {{ $progress }}%
                            </p>

                            <p class="text-xs text-slate-500">
                                Quota utilisé
                            </p>

                        </div>

                    </div>

                    <div class="w-full flex-1 space-y-3">

                        @foreach ([
                            ['Badges autorisés', $quota, 'bg-blue-50 text-blue-700'],
                            ['Badges enregistrés', $used, 'bg-emerald-50 text-emerald-700'],
                            ['Badges restants', $remaining, 'bg-amber-50 text-amber-700'],
                        ] as [$label, $number, $class])

                            <div class="flex items-center justify-between rounded-xl p-4 {{ $class }}">

                                <span class="text-sm font-medium">{{ $label }}</span>

                                <span class="text-lg font-bold">{{ $number }}</span>

                            </div>

                        @endforeach

                    </div>

                </div>

            </article>

            {{-- QUICK ACTIONS --}}

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                <h2 class="font-bold text-[#0F2347]">
                    Actions rapides
                </h2>

                <div class="mt-5 space-y-3">

                    <a href="{{ route('commercial.exhibitors.edit', $exhibitor) }}"
                       class="block rounded-xl border border-slate-200 p-4 text-sm font-semibold hover:bg-blue-50">
                        Modifier les informations →
                    </a>

                    @if (\Illuminate\Support\Facades\Route::has('commercial.badges.index'))

                        <a href="{{ route('commercial.badges.index') }}"
                           class="block rounded-xl border border-slate-200 p-4 text-sm font-semibold hover:bg-blue-50">
                            Espace Badges →
                        </a>

                    @endif

                    <a href="{{ route('commercial.support.index') }}"
                       class="block rounded-xl border border-slate-200 p-4 text-sm font-semibold hover:bg-blue-50">
                        Assistance exposants →
                    </a>

                    <a href="{{ route('commercial.exhibitors.index') }}"
                       class="block rounded-xl border border-slate-200 p-4 text-sm font-semibold hover:bg-blue-50">
                        Tous mes exposants →
                    </a>

                </div>

            </article>

        </section>

        <footer class="border-t border-slate-200 py-4 text-center text-xs text-slate-400">
            © 2027 Refrigair Expo — Espace Commercial
        </footer>

    </div>

</x-commercial.layout>