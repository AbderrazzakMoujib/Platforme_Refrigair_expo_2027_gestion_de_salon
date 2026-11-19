
@props(['commercial', 'commercialInitials'])

@php
    $navigation = [
        [
            'label' => 'Vue globale',
            'icon' => 'overview',
            'route' => 'commercial.index',
            'active' => request()->routeIs('commercial.index'),
        ],
        [
            'label' => 'Mes exposants',
            'icon' => 'users',
            'route' => 'commercial.exhibitors.index',
            'active' => request()->routeIs('commercial.exhibitors.*'),
        ],
        [
            'label' => 'Assistance exposants',
            'icon' => 'inbox',
            'route' => 'commercial.support.index',
            'active' => request()->routeIs('commercial.support.*'),
        ],
        [
            'label' => 'Notifications',
            'icon' => 'bell',
            'route' => 'commercial.notifications.index',
            'active' => request()->routeIs('commercial.notifications.*'),
        ],
        [
            'label' => 'Mon profil',
            'icon' => 'settings',
            'route' => 'commercial.profile.edit',
            'active' => request()->routeIs('commercial.profile.*'),
        ],
    ];
@endphp

@php
    // Compteurs affichés dans la navigation (données du commercial connecté uniquement).
    $navCounts = [
        'commercial.support.index' => \App\Models\SupportTicket::query()
            ->where('commercial_id', $commercial->id)
            ->whereHas('exhibitor', fn ($query) => $query->where('commercial_id', $commercial->id))
            ->where('status', 'pending')
            ->count(),
        'commercial.notifications.index' => $commercial->unreadNotifications()->count(),
    ];
    $navCountLabels = [
        'commercial.support.index' => 'demande(s) en attente',
        'commercial.notifications.index' => 'notification(s) non lue(s)',
    ];
@endphp

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>
        {{ $title ?? 'Espace Commercial | Refrigair Expo 2027' }}
    </title>

    <link
        rel="icon"
        type="image/png"
        href="{{ asset('assets/images/icons/favicon_io.png') }}"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#F5F7FB] font-sans text-slate-900 antialiased">

    <!-- DESKTOP SIDEBAR -->
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 flex-col bg-[#0F2347] px-5 py-6 md:flex">

        <!-- LOGO -->
        <a href="{{ route('commercial.index') }}"
           class="flex min-h-16 items-center justify-center rounded-xl px-2 py-2"
           aria-label="Refrigair Expo 2027">

            <img
                src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-Sans-titre-4-removebg-preview.png') }}"
                alt="Refrigair Expo 2027"
                class="max-h-14 w-full max-w-[220px] object-contain"
            >

        </a>

        <!-- NAVIGATION -->
        <nav
            class="mt-8 min-h-0 flex-1 space-y-1 overflow-y-auto pb-5"
            aria-label="Navigation commerciale"
        >

            @foreach ($navigation as $item)

                <div class="relative">
                    <x-admin.sidebar-link
                        :label="$item['label']"
                        :icon="$item['icon']"
                        :href="route($item['route'])"
                        :active="$item['active']"
                        :available="true"
                    />
                    @if (($navCounts[$item['route']] ?? 0) > 0)
                        <span class="pointer-events-none absolute right-3 top-1/2 min-w-5 -translate-y-1/2 rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[11px] font-bold leading-4 text-white" data-nav-count="{{ $item['route'] }}">
                            {{ $navCounts[$item['route']] > 99 ? '99+' : $navCounts[$item['route']] }}<span class="sr-only"> {{ $navCountLabels[$item['route']] }}</span>
                        </span>
                    @endif
                </div>

                @if ($item['route'] === 'commercial.exhibitors.index')

                    <x-admin.sidebar-link
                        label="Badges"
                        icon="badge"
                    />

                    <x-admin.sidebar-link
                        label="Demandes d'équipements"
                        icon="box"
                    />

                @endif

            @endforeach

        </nav>

        <!-- SIDEBAR FOOTER -->
        <div class="shrink-0 rounded-xl border border-white/10 bg-white/5 p-4">

            <div class="flex items-center gap-3">

                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/10 text-white">

                    <x-admin.icon
                        name="inbox"
                        class="size-5"
                    />

                </span>

                <div>

                    <p class="text-sm font-bold text-white">
                        Assistance exposants
                    </p>

                    <p class="mt-1 text-xs text-blue-200">
                        Suivi de vos clients
                    </p>

                </div>

            </div>

            <a href="{{ route('commercial.support.index') }}"
               class="mt-4 block rounded-xl bg-[#2563EB] px-4 py-3 text-center text-sm font-bold text-white hover:bg-blue-600">

                Voir les demandes

            </a>

        </div>

    </aside>

    <!-- MAIN AREA -->
    <div class="min-h-screen md:pl-72">

        <!-- HEADER -->
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 px-4 py-3 backdrop-blur md:px-8">

            <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">

                <!-- MOBILE MENU -->
                <details class="relative md:hidden">

                    <summary
                        class="grid size-10 cursor-pointer list-none place-items-center rounded-lg border border-slate-200 text-slate-700"
                        aria-label="Ouvrir le menu">

                        <x-admin.icon
                            name="menu"
                            class="size-5"
                        />

                    </summary>

                    <div class="absolute left-0 top-12 z-50 max-h-[80vh] w-72 overflow-y-auto rounded-xl bg-[#0F2347] p-3 shadow-xl">

                        <nav
                            class="space-y-1"
                            aria-label="Navigation mobile commerciale"
                        >

                            @foreach ($navigation as $item)

                                <div class="relative">
                                    <x-admin.sidebar-link
                                        :label="$item['label']"
                                        :icon="$item['icon']"
                                        :href="route($item['route'])"
                                        :active="$item['active']"
                                        :available="true"
                                    />
                                    @if (($navCounts[$item['route']] ?? 0) > 0)
                                        <span class="pointer-events-none absolute right-3 top-1/2 min-w-5 -translate-y-1/2 rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[11px] font-bold leading-4 text-white">
                                            {{ $navCounts[$item['route']] > 99 ? '99+' : $navCounts[$item['route']] }}<span class="sr-only"> {{ $navCountLabels[$item['route']] }}</span>
                                        </span>
                                    @endif
                                </div>

                                @if ($item['route'] === 'commercial.exhibitors.index')

                                    <x-admin.sidebar-link
                                        label="Badges"
                                        icon="badge"
                                    />

                                    <x-admin.sidebar-link
                                        label="Demandes d'équipements"
                                        icon="box"
                                    />

                                @endif

                            @endforeach

                        </nav>

                    </div>

                </details>

                <!-- HEADER TITLE -->
                <p class="hidden text-sm font-medium text-slate-500 sm:block">
                    Espace Commercial · Refrigair Expo 2027
                </p>

                <!-- HEADER RIGHT -->
                <div class="ml-auto flex items-center gap-2 sm:gap-4">

                    <!-- LIVE NOTIFICATIONS -->
                    <x-notifications.live
                        :user="$commercial"
                        index-route="commercial.notifications.index"
                        open-route="commercial.notifications.open"
                    />

                    <!-- ASSISTANCE SHORTCUT -->
                    <a href="{{ route('commercial.support.index') }}"
                       class="grid size-10 place-items-center rounded-lg text-slate-600 transition hover:bg-blue-50 hover:text-blue-700"
                       title="Assistance exposants"
                       aria-label="Assistance exposants">

                        <x-admin.icon
                            name="inbox"
                            class="size-5"
                        />

                    </a>

                    <!-- COMMERCIAL NAME -->
                    <div class="hidden text-right sm:block">

                        <p class="text-sm font-semibold text-slate-800">
                            {{ $commercial->name }}
                        </p>

                        <p class="text-xs text-slate-500">
                            Commercial
                        </p>

                    </div>

                    <!-- PROFILE PHOTO -->
                    <a href="{{ route('commercial.profile.edit') }}"
                       class="block size-11 shrink-0 overflow-hidden rounded-full border-2 border-white shadow-sm ring-1 ring-slate-200 transition hover:ring-blue-400"
                       title="Mon profil"
                       aria-label="Accéder à mon profil">

                        @if ($commercial->profile_photo_url)

                            <img
                                src="{{ $commercial->profile_photo_url }}"
                                alt="Photo de {{ $commercial->name }}"
                                class="h-full w-full object-cover"
                            >

                        @else

                            <span class="grid h-full w-full place-items-center bg-[#0F2347] text-sm font-bold text-white">

                                {{ $commercialInitials }}

                            </span>

                        @endif

                    </a>

                    <!-- LOGOUT -->
                    <form method="POST"
                          action="{{ route('logout') }}">

                        @csrf

                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700"
                        >

                            <x-admin.icon
                                name="logout"
                                class="size-4"
                            />

                            <span class="hidden sm:inline">
                                Déconnexion
                            </span>

                        </button>

                    </form>

                </div>

            </div>

        </header>

        <!-- CONTENT -->
        <main class="mx-auto max-w-7xl px-4 py-7 md:px-8 md:py-9">

            {{ $slot }}

        </main>

    </div>

</body>
</html>
