
@props(['admin', 'adminInitials'])

@php
    $navigation = [
        [
            'label' => 'Vue globale',
            'icon' => 'overview',
            'route' => 'admin.index',
            'active' => request()->routeIs('admin.index'),
        ],
        [
            'label' => 'Salons',
            'icon' => 'calendar',
            'route' => 'admin.salons.index',
            'active' => request()->routeIs('admin.salons.*'),
        ],
        [
            'label' => 'Commerciaux',
            'icon' => 'users',
            'route' => 'admin.commerciaux.index',
            'active' => request()->routeIs('admin.commerciaux.*'),
        ],
        [
            'label' => 'Exposants',
            'icon' => 'users',
            'route' => 'admin.exposants.index',
            'active' => request()->routeIs('admin.exposants.*'),
        ],
        [
            'label' => 'Badges',
            'icon' => 'badge',
            'route' => 'admin.badges.index',
            'active' => request()->routeIs('admin.badges.*'),
        ],
        [
            'label' => 'Équipements',
            'icon' => 'box',
            'route' => 'admin.equipements.index',
            'active' => request()->routeIs('admin.equipements.*'),
        ],
        [
            'label' => 'Demandes d’équipements',
            'icon' => 'inbox',
            'route' => 'admin.demandes.index',
            'active' => request()->routeIs('admin.demandes.*'),
        ],
        [
            'label' => 'Statistiques',
            'icon' => 'chart',
            'route' => 'admin.statistiques.index',
            'active' => request()->routeIs('admin.statistiques.*'),
        ],
        [
            'label' => 'Suivi assistance',
            'icon' => 'inbox',
            'route' => 'admin.support.index',
            'active' => request()->routeIs('admin.support.*'),
        ],
        [
            'label' => 'Notifications',
            'icon' => 'bell',
            'route' => 'admin.notifications.index',
            'active' => request()->routeIs('admin.notifications.*'),
        ],
        [
            'label' => 'Paramètres',
            'icon' => 'settings',
            'route' => 'admin.parametres.index',
            'active' => request()->routeIs('admin.parametres.*'),
        ],
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
        {{ $title ?? 'Administration | Refrigair Expo 2027' }}
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
        <a href="{{ route('admin.index') }}"
           class="flex min-h-16 items-center justify-center rounded-xl px-2 py-2"
           aria-label="Refrigair Expo 2027">

            <img
                src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-logo-8.png') }}"
                alt="Refrigair Expo 2027"
                class="block max-h-14 w-full max-w-[220px] object-contain"
            >

        </a>

        <!-- NAVIGATION -->
        <nav
            class="mt-8 min-h-0 flex-1 space-y-1 overflow-y-auto pb-5"
            aria-label="Navigation administrateur"
        >

            @foreach ($navigation as $item)

                <x-admin.sidebar-link
                    :label="$item['label']"
                    :icon="$item['icon']"
                    :href="route($item['route'])"
                    :active="$item['active']"
                    :available="true"
                />

            @endforeach

        </nav>

        <!-- SIDEBAR FOOTER -->
        <div class="shrink-0 rounded-xl border border-white/10 bg-white/5 p-4">

            <p class="text-sm font-bold text-white">
                Administration
            </p>

            <p class="mt-2 text-xs leading-5 text-blue-100">
                Gestion et supervision de Refrigair Expo 2027.
            </p>

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

                        <x-admin.icon name="menu" class="size-5"/>

                    </summary>

                    <div class="absolute left-0 top-12 z-50 max-h-[80vh] w-72 overflow-y-auto rounded-xl bg-[#0F2347] p-3 shadow-xl">

                        <nav
                            class="space-y-1"
                            aria-label="Navigation mobile administrateur"
                        >

                            @foreach ($navigation as $item)

                                <x-admin.sidebar-link
                                    :label="$item['label']"
                                    :icon="$item['icon']"
                                    :href="route($item['route'])"
                                    :active="$item['active']"
                                    :available="true"
                                />

                            @endforeach

                        </nav>

                    </div>

                </details>

                <!-- HEADER TITLE -->
                <p class="hidden text-sm font-medium text-slate-500 sm:block">
                    Administration Refrigair Expo 2027
                </p>

                <!-- HEADER RIGHT -->
                <div class="ml-auto flex items-center gap-2 sm:gap-4">

                    <!-- LIVE NOTIFICATIONS -->
                    <x-notifications.live
                        :user="$admin"
                        index-route="admin.notifications.index"
                        open-route="admin.notifications.open"
                    />

                    <!-- ADMIN NAME -->
                    <div class="hidden text-right sm:block">

                        <p class="text-sm font-semibold text-slate-800">
                            {{ $admin->name }}
                        </p>

                        <p class="text-xs text-slate-500">
                            Administrateur
                        </p>

                    </div>

                    <!-- PROFILE PHOTO -->
                    <a href="{{ route('admin.parametres.index') }}"
                       class="block size-11 shrink-0 overflow-hidden rounded-full border-2 border-white shadow-sm ring-1 ring-slate-200 transition hover:ring-blue-400"
                       title="Mon profil"
                       aria-label="Accéder à mon profil">

                        @if ($admin->profile_photo_url)

                            <img
                                src="{{ $admin->profile_photo_url }}"
                                alt="Photo de {{ $admin->name }}"
                                class="h-full w-full object-cover"
                            >

                        @else

                            <span class="grid h-full w-full place-items-center bg-[#0F2347] text-sm font-bold text-white">

                                {{ $adminInitials }}

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
