@props(['user', 'exhibitor' => null])
@php
    $menu = [
        [
            'label' => 'Vue globale',
            'icon' => 'dashboard',
            'url' => route('exposant.index'),
            'active' => request()->routeIs('exposant.index'),
        ],
        [
            'label' => 'Mon entreprise / Mon stand',
            'icon' => 'building',
            'url' => route('exhibitor.company.index'),
            'active' => request()->routeIs('exhibitor.company.*'),
        ],
        [
            'label' => 'Mes badges',
            'icon' => 'badge',
            'url' => route('exhibitor.badges.index'),
            'active' => request()->routeIs('exhibitor.badges.*'),
        ],
        [
            'label' => 'Options du stand',
            'icon' => 'cart',
            'url' => route('exhibitor.stand-options.index'),
            'active' => request()->routeIs('exhibitor.stand-options.*'),
        ],
        [
            'label' => 'Mes demandes',
            'icon' => 'requests',
            'url' => route('exhibitor.requests.index'),
            'active' => request()->routeIs('exhibitor.requests.*'),
        ],
        [
            'label' => 'Conférences & Lounge',
            'icon' => 'calendar',
            'url' => route('exhibitor.slots.index'),
            'active' => request()->routeIs('exhibitor.slots.*', 'exhibitor.conferences.collaboration.*'),
        ],
        [
            'label' => 'Assistance',
            'icon' => 'headset',
            'url' => route('exhibitor.support.index'),
            'active' => request()->routeIs('exhibitor.support.*'),
        ],
        [
            'label' => 'Notifications',
            'icon' => 'bell',
            'url' => route('exhibitor.notifications.index'),
            'active' => request()->routeIs('exhibitor.notifications.*'),
        ],
    ];
    $companyInitials = collect(
        preg_split(
            '/\s+/',
            trim($exhibitor?->company_name ?? $user->name)
        )
    )
        ->filter()
        ->take(2)
        ->map(
            fn ($part) =>
                mb_strtoupper(mb_substr($part, 0, 1))
        )
        ->implode('') ?: 'E';
    $companyLogo = $exhibitor?->logo
        ? \Illuminate\Support\Facades\Storage::disk('public')
            ->url($exhibitor->logo)
        : null;
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
        {{ $title ?? 'Espace Exposant | Refrigair Expo 2027' }}
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
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 flex-col bg-[#0F2347] px-4 py-6 md:flex">
        <!-- LOGO -->
        <a href="{{ route('exposant.index') }}"
           class="flex min-h-20 items-center justify-center rounded-xl px-2"
           aria-label="Refrigair Expo 2027">
            <img
                src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-logo-8.png') }}"
                alt="Refrigair Expo 2027"
                class="max-h-16 w-full max-w-[230px] object-contain"
            >
        </a>
        <!-- NAVIGATION -->
        <nav
            class="mt-8 min-h-0 flex-1 space-y-2 overflow-y-auto pb-5"
            aria-label="Navigation exposant"
        >
            @foreach ($menu as $entry)
                <a href="{{ $entry['url'] }}"
                   @class([
                       'flex items-center gap-3 rounded-xl px-4 py-3.5 text-sm font-semibold transition',
                       'bg-[#2563EB] text-white shadow-sm' => $entry['active'],
                       'text-blue-100 hover:bg-white/10 hover:text-white' => !$entry['active'],
                   ])>
                    <span class="grid size-6 shrink-0 place-items-center">
                        @switch($entry['icon'])
                            @case('dashboard')
                                <svg
                                    class="size-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                                </svg>
                                @break
                            @case('building')
                                <svg
                                    class="size-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <rect x="4" y="3" width="16" height="18" rx="1"/>
                                    <path d="M9 21v-5h6v5M8 7h2m4 0h2M8 11h2m4 0h2"/>
                                </svg>
                                @break
                            @case('badge')
                                <svg
                                    class="size-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <rect x="3" y="4" width="18" height="16" rx="2"/>
                                    <circle cx="9" cy="10" r="2"/>
                                    <path d="M5 17c0-2 2-3 4-3s4 1 4 3M15 9h4m-4 4h4"/>
                                </svg>
                                @break
                            @case('cart')
                                <svg
                                    class="size-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M2 3h2l2.5 12h12L22 7H5"/>
                                    <circle cx="9" cy="20" r="1"/>
                                    <circle cx="18" cy="20" r="1"/>
                                </svg>
                                @break
                            @case('requests')
                                <svg
                                    class="size-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <rect x="5" y="3" width="14" height="18" rx="2"/>
                                    <path d="M9 8h6M9 12h6M9 16h4"/>
                                </svg>
                                @break
                            @case('calendar')
                                <svg
                                    class="size-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                                    <path d="M16 3v4M8 3v4M3 10h18M8 14h3M8 17h6"/>
                                </svg>
                                @break
                            @case('headset')
                                <svg
                                    class="size-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M4 12a8 8 0 0 1 16 0v5a3 3 0 0 1-3 3h-2v-7h5M4 13h5v7H7a3 3 0 0 1-3-3z"/>
                                </svg>
                                @break
                            @case('bell')
                                <svg
                                    class="size-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                                    <path d="M10 21h4"/>
                                </svg>
                                @break
                        @endswitch
                    </span>
                    <span>
                        {{ $entry['label'] }}
                    </span>
                </a>
            @endforeach
        </nav>
        <!-- ASSISTANCE CARD -->
        <div class="shrink-0 rounded-2xl border border-white/10 bg-white/5 p-5">
            <div class="flex items-center gap-3">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-white/10 text-white">
                    <svg
                        class="size-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M4 12a8 8 0 0 1 16 0v5a3 3 0 0 1-3 3h-2v-7h5M4 13h5v7H7a3 3 0 0 1-3-3z"/>
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-bold text-white">
                        Besoin d'aide ?
                    </p>
                    <p class="mt-1 text-xs text-blue-200">
                        Assistance exposants
                    </p>
                </div>
            </div>
            <p class="mt-4 text-xs leading-5 text-blue-100">
                Contactez votre commercial référent pour toute
                question concernant votre participation.
            </p>
            <a href="{{ route('exhibitor.support.index') }}"
               class="mt-4 block rounded-xl bg-[#2563EB] px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-blue-600">
                Contacter mon commercial
            </a>
        </div>
    </aside>
    <!-- MAIN AREA -->
    <div class="min-h-screen md:pl-72">
        <!-- HEADER -->
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur md:px-8">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
                <!-- MOBILE MENU -->
                <details class="relative md:hidden">
                    <summary
                        class="grid size-10 cursor-pointer list-none place-items-center rounded-lg border border-slate-200 text-slate-700"
                        aria-label="Ouvrir le menu">
                        <svg
                            class="size-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            aria-hidden="true"
                        >
                            <path d="M4 7h16M4 12h16M4 17h16"/>
                        </svg>
                    </summary>
                    <nav
                        class="absolute left-0 top-12 z-50 max-h-[80vh] w-72 space-y-1 overflow-y-auto rounded-xl bg-[#0F2347] p-3 shadow-xl"
                        aria-label="Navigation mobile exposant"
                    >
                        @foreach ($menu as $entry)
                            <a href="{{ $entry['url'] }}"
                               @class([
                                   'block rounded-lg px-4 py-3 text-sm font-semibold transition',
                                   'bg-[#2563EB] text-white' => $entry['active'],
                                   'text-blue-100 hover:bg-white/10' => !$entry['active'],
                               ])>
                                {{ $entry['label'] }}
                            </a>
                        @endforeach
                    </nav>
                </details>
                <!-- HEADER TITLE -->
                <p class="hidden text-sm font-medium text-slate-500 sm:block">
                    Espace Exposant · Refrigair Expo 2027
                </p>
                <!-- HEADER RIGHT -->
                <div class="ml-auto flex items-center gap-2 sm:gap-4">
                    <!-- LIVE NOTIFICATIONS -->
                    <x-notifications.live
                        :user="$user"
                        index-route="exhibitor.notifications.index"
                        open-route="exhibitor.notifications.open"
                    />
                    <!-- ASSISTANCE SHORTCUT -->
                    <a href="{{ route('exhibitor.support.index') }}"
                       class="grid size-10 place-items-center rounded-lg text-slate-600 transition hover:bg-blue-50 hover:text-blue-700"
                       title="Assistance"
                       aria-label="Contacter mon commercial">
                        <svg
                            class="size-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M4 12a8 8 0 0 1 16 0v5a3 3 0 0 1-3 3h-2v-7h5M4 13h5v7H7a3 3 0 0 1-3-3z"/>
                        </svg>
                    </a>
                    <!-- COMPANY / USER -->
                    <div class="hidden min-w-0 text-right sm:block">
                        <p class="max-w-48 truncate text-sm font-bold text-[#0F2347]">
                            {{ $exhibitor?->company_name ?? $user->name }}
                        </p>
                        <p class="max-w-48 truncate text-xs text-slate-500">
                            {{ $user->name }}
                        </p>
                    </div>
                    <!-- COMPANY LOGO -->
                    <a href="{{ route('exhibitor.company.index') }}"
                       class="block size-11 shrink-0 overflow-hidden rounded-full border-2 border-white shadow-sm ring-1 ring-slate-200 transition hover:ring-blue-400"
                       title="Mon entreprise"
                       aria-label="Consulter mon entreprise">
                        @if ($companyLogo)
                            <img
                                src="{{ $companyLogo }}"
                                alt="Logo {{ $exhibitor->company_name }}"
                                class="h-full w-full bg-white object-contain p-1"
                            >
                        @else
                            <span class="grid h-full w-full place-items-center bg-[#0F2347] text-sm font-bold text-white">
                                {{ $companyInitials }}
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
                            <svg
                                class="size-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M10 17l5-5-5-5M15 12H3"/>
                                <path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7"/>
                            </svg>
                            <span class="hidden sm:inline">
                                Déconnexion
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </header>
        <!-- PAGE CONTENT -->
        <main class="mx-auto max-w-[1680px] px-4 py-5 sm:px-6 lg:px-7 lg:py-7 2xl:px-9">
            {{ $slot }}
        </main>
    </div>
    <x-ui.toast />
</body>
</html>
