
@props(['user'])

@php
    $navigation = [
        [
            'label' => 'Tableau de bord',
            'route' => 'responsable-conferences.dashboard',
            'icon' => 'overview',
        ],
        [
            'label' => 'Planning des conférences',
            'route' => 'responsable-conferences.planning',
            'icon' => 'calendar',
        ],
        [
            'label' => 'Demandes de réservation',
            'route' => 'responsable-conferences.requests.index',
            'icon' => 'inbox',
        ],
        [
            'label' => 'Réservations confirmées',
            'route' => 'responsable-conferences.confirmed',
            'icon' => 'clipboard-check',
        ],
        [
            'label' => 'Historique des décisions',
            'route' => 'responsable-conferences.history',
            'icon' => 'chart',
        ],
        [
            'label' => 'Mon profil',
            'route' => 'responsable-conferences.profile',
            'icon' => 'settings',
        ],

        [
            'label' => 'Notifications',
            'route' => 'responsable-conferences.notifications.index',
            'icon' => 'bell',
        ],


[
    'label' => 'Supports & Messagerie',
    'route' => 'responsable-conferences.supports.index',
    'icon' => 'inbox',
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
        {{ $title ?? 'Responsable Conférences | Refrigair Expo 2027' }}
    </title>

    <link rel="icon"
          type="image/png"
          href="{{ asset('assets/images/icons/favicon_io.png') }}">

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen bg-[#F5F7FB] font-sans text-slate-900 antialiased">

    <!-- SIDEBAR DESKTOP -->
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 flex-col bg-[#0F2347] px-5 py-6 text-white md:flex">

        <!-- LOGO OFFICIEL -->
        <a href="{{ route('responsable-conferences.dashboard') }}"
           class="flex min-h-16 items-center justify-center rounded-xl px-2 py-2"
           aria-label="Refrigair Expo 2027">

            <img
                src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-logo-8.png') }}"
                alt="Logo Refrigair Expo 2027"
                class="block max-h-14 w-full max-w-[220px] object-contain"
            >
        </a>

        <p class="mt-6 px-3 text-[11px] font-bold uppercase tracking-widest text-blue-300">
            Conférences & Lounge
        </p>

        <!-- NAVIGATION -->
        <nav class="mt-4 min-h-0 flex-1 space-y-1 overflow-y-auto pb-5"
             aria-label="Navigation Responsable">

            @foreach($navigation as $item)

                @php
                    $active = request()->routeIs($item['route']);
                @endphp

                <a href="{{ route($item['route']) }}"
                   @if($active) aria-current="page" @endif
                   @class([
                       'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition',
                       'bg-[#2563EB] text-white shadow-md shadow-blue-950/20' => $active,
                       'text-blue-100 hover:bg-white/10 hover:text-white' => !$active,
                   ])>

                    <x-admin.icon
                        :name="$item['icon']"
                        class="size-5 shrink-0"
                    />

                    <span class="flex-1">
                        {{ $item['label'] }}
                    </span>

                </a>

            @endforeach

        </nav>

        <!-- EVENT CARD -->
        <div class="shrink-0 rounded-2xl border border-white/10 bg-white/5 p-4">

            <p class="text-sm font-extrabold">
                Refrigair Expo 2027
            </p>

            <p class="mt-2 text-xs leading-5 text-blue-200">
                02 – 04 Février 2027
                <br>
                OFEC · Casablanca
            </p>

            <div class="mt-3 flex items-center gap-2 text-xs text-emerald-300">
                <span class="size-2 rounded-full bg-emerald-400"></span>
                Espace Responsable
            </div>
        </div>

        <!-- USER -->
        <div class="mt-5 shrink-0 border-t border-white/10 pt-5">

            <a href="{{ route('responsable-conferences.profile') }}"
               class="flex items-center gap-3 rounded-xl p-2 transition hover:bg-white/10">

                <x-profile-avatar
                    :user="$user"
                    class="size-11 shrink-0 text-sm"
                />

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold">
                        {{ $user->name }}
                    </p>

                    <p class="truncate text-xs text-blue-200">
                        Responsable Conférences
                    </p>
                </div>
            </a>

            <form method="POST"
                  action="{{ route('logout') }}"
                  class="mt-3">
                @csrf

                <button type="submit"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-white/15 px-4 py-3 text-sm font-semibold text-blue-100 transition hover:bg-red-500/20 hover:text-white">

                    Se déconnecter
                </button>

            </form>
        </div>
    </aside>

    <!-- MAIN AREA -->
    <div class="min-h-screen md:pl-72">

        <!-- HEADER -->
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 px-4 py-3 backdrop-blur md:px-8">

            <div class="mx-auto flex max-w-[1500px] items-center justify-between gap-3">

                <!-- MOBILE MENU -->
                <details class="relative md:hidden">

                    <summary class="grid size-10 cursor-pointer list-none place-items-center rounded-lg border border-slate-200"
                             aria-label="Ouvrir le menu">

                        <x-admin.icon name="menu" class="size-5"/>
                    </summary>

                    <div class="absolute left-0 top-12 z-50 max-h-[80vh] w-72 overflow-y-auto rounded-xl bg-[#0F2347] p-3 shadow-xl">

                        <img
                            src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-logo-8.png') }}"
                            alt="Refrigair Expo"
                            class="mx-auto mb-5 h-12 w-full object-contain"
                        >

                        <nav class="space-y-1" aria-label="Navigation mobile">

                            @foreach($navigation as $item)

                                <a href="{{ route($item['route']) }}"
                                   @class([
                                       'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold',
                                       'bg-blue-600 text-white' => request()->routeIs($item['route']),
                                       'text-blue-100 hover:bg-white/10' => !request()->routeIs($item['route']),
                                   ])>

                                    <x-admin.icon
                                        :name="$item['icon']"
                                        class="size-5"
                                    />

                                    {{ $item['label'] }}
                                </a>

                            @endforeach

                            <form method="POST"
                                  action="{{ route('logout') }}"
                                  class="border-t border-white/10 pt-3">
                                @csrf

                                <button type="submit"
                                        class="w-full rounded-xl px-4 py-3 text-left text-sm font-semibold text-blue-100 hover:bg-white/10">
                                    Se déconnecter
                                </button>
                            </form>
                        </nav>
                    </div>
                </details>

                <!-- HEADER TITLE -->
                <div class="min-w-0">

                    <p class="text-xs font-semibold text-slate-400">
                        REFRIGAIR EXPO 2027
                    </p>

                    <h2 class="mt-1 truncate text-sm font-bold text-[#0F2347]">
                        Gestion des Conférences & Lounge
                    </h2>
                </div>

                <!-- HEADER RIGHT -->
                <div class="ml-auto flex items-center gap-3">

{{-- LIVE NOTIFICATIONS --}}
<x-notifications.live
    :user="$user"
    index-route="responsable-conferences.notifications.index"
    open-route="responsable-conferences.notifications.open"
/>


                    <span class="hidden items-center gap-2 rounded-full bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 lg:inline-flex">

                        <span class="size-2 rounded-full bg-emerald-500"></span>

                        Espace sécurisé
                    </span>

                    <div class="hidden text-right lg:block">

                        <p class="text-sm font-bold text-slate-800">
                            {{ $user->name }}
                        </p>

                        <p class="text-xs text-slate-500">
                            Responsable Conférences
                        </p>
                    </div>

                    <a href="{{ route('responsable-conferences.profile') }}"
                       class="block shrink-0 rounded-full border-2 border-white shadow-sm ring-1 ring-slate-200 transition hover:ring-blue-400"
                       aria-label="Mon profil">

                        <x-profile-avatar
                            :user="$user"
                            class="size-11 text-sm"
                        />
                    </a>
                </div>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="px-4 py-6 sm:px-6 md:px-8 md:py-8">

            <div class="mx-auto max-w-[1500px]">
                {{ $slot }}
            </div>

        </main>

        <footer class="border-t border-slate-200 px-6 py-5 text-center text-xs text-slate-400">
            © 2027 Refrigair Expo — Espace Responsable Conférences & Lounge
        </footer>

    </div>
    <x-ui.toast />
</body>
</html>
