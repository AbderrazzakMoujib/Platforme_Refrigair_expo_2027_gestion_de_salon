
@props(['user', 'exhibitor' => null])

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Espace Exposant | Refrigair Expo 2027' }}</title>

    <link
        rel="icon"
        type="image/png"
        href="{{ asset('assets/images/icons/favicon_io.png') }}"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#F5F7FB] font-sans text-slate-900 antialiased">

    <!-- SIDEBAR DESKTOP -->
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 flex-col bg-[#0F2347] px-5 py-6 md:flex">

        <!-- LOGO -->
        <a
            href="{{ route('exposant.index') }}"
            class="flex min-h-16 items-center justify-center px-2"
        >
            <img
                src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-Sans-titre-4-removebg-preview.png') }}"
                alt="Refrigair Expo 2027"
                class="max-h-16 w-full max-w-[230px] object-contain"
            >
        </a>

        <!-- NAVIGATION -->
        <nav class="mt-12 space-y-2" aria-label="Navigation Exposant">

            <!-- VUE GLOBALE -->
            <a
                href="{{ route('exposant.index') }}"
                @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition',
                    'bg-white/15 text-white' => request()->routeIs('exposant.index'),
                    'text-blue-100 hover:bg-white/10' => !request()->routeIs('exposant.index'),
                ])
            >
                <span aria-hidden="true">▦</span>
                <span>Vue globale</span>
            </a>

            <!-- MES BADGES -->
            <a
                href="{{ route('exhibitor.badges.index') }}"
                @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition',
                    'bg-white/15 text-white' => request()->routeIs('exhibitor.badges.*'),
                    'text-blue-100 hover:bg-white/10' => !request()->routeIs('exhibitor.badges.*'),
                ])
            >
                <span aria-hidden="true">▤</span>
                <span>Mes badges</span>
            </a>

            <!-- MON STAND -->
            <a
                href="{{ route('exposant.index') }}#mon-stand"
                class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-blue-100 transition hover:bg-white/10"
            >
                <span aria-hidden="true">▣</span>
                <span>Mon stand</span>
            </a>

            <!-- OPTIONS DU STAND -->
            <a
                href="{{ route('exhibitor.stand-options.index') }}"
                @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition',
                    'bg-white/15 text-white' => request()->routeIs('exhibitor.stand-options.*') && !request()->has('demandes'),
                    'text-blue-100 hover:bg-white/10' => !request()->routeIs('exhibitor.stand-options.*') || request()->has('demandes'),
                ])
            >
                <span aria-hidden="true">◈</span>
                <span>Options du stand</span>
            </a>

            <!-- MES DEMANDES -->
            <a
                href="{{ route('exhibitor.stand-options.index', ['demandes' => 1]) }}#mes-demandes"
                @class([
                    'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition',
                    'bg-white/15 text-white' => request()->routeIs('exhibitor.stand-options.index') && request()->has('demandes'),
                    'text-blue-100 hover:bg-white/10' => !request()->routeIs('exhibitor.stand-options.index') || !request()->has('demandes'),
                ])
            >
                <span aria-hidden="true">▤</span>
                <span>Mes demandes</span>
            </a>

        </nav>

        <!-- HELP SECTION -->
        <div class="mt-auto rounded-xl border border-white/10 bg-white/5 p-4">
            <p class="text-sm font-semibold text-white">
                Besoin d'aide ?
            </p>

            <p class="mt-2 text-xs leading-5 text-blue-200">
                Contactez l'organisation de Refrigair Expo.
            </p>
        </div>

    </aside>

    <!-- MAIN AREA -->
    <div class="min-h-screen md:pl-72">

        <!-- HEADER -->
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur md:px-8">

            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">

                <!-- MOBILE MENU -->
                <details class="relative md:hidden">
                    <summary
                        class="flex size-10 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200 text-xl"
                        aria-label="Ouvrir le menu de navigation"
                    >
                        ☰
                    </summary>

                    <nav
                        class="absolute left-0 top-12 z-50 w-64 space-y-2 rounded-xl bg-[#0F2347] p-4 text-sm text-white shadow-xl"
                        aria-label="Navigation mobile Exposant"
                    >

                        <!-- VUE GLOBALE -->
                        <a
                            href="{{ route('exposant.index') }}"
                            class="block rounded-lg px-3 py-3 hover:bg-white/10"
                        >
                            Vue globale
                        </a>

                        <!-- MES BADGES -->
                        <a
                            href="{{ route('exhibitor.badges.index') }}"
                            class="block rounded-lg px-3 py-3 hover:bg-white/10"
                        >
                            Mes badges
                        </a>

                        <!-- MON STAND -->
                        <a
                            href="{{ route('exposant.index') }}#mon-stand"
                            class="block rounded-lg px-3 py-3 hover:bg-white/10"
                        >
                            Mon stand
                        </a>

                        <!-- OPTIONS DU STAND -->
                        <a
                            href="{{ route('exhibitor.stand-options.index') }}"
                            class="block rounded-lg px-3 py-3 hover:bg-white/10"
                        >
                            Options du stand
                        </a>

                        <!-- MES DEMANDES -->
                        <a
                            href="{{ route('exhibitor.stand-options.index', ['demandes' => 1]) }}#mes-demandes"
                            class="block rounded-lg px-3 py-3 hover:bg-white/10"
                        >
                            Mes demandes
                        </a>

                    </nav>
                </details>

                <!-- PAGE NAME -->
                <p class="hidden text-sm font-medium text-slate-500 sm:block">
                    Espace Exposant · Refrigair Expo 2027
                </p>

                <!-- USER AREA -->
                <div class="ml-auto flex items-center gap-3 sm:gap-5">

                    <!-- USER DETAILS -->
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-slate-800">
                            {{ $user->name }}
                        </p>

                        <p class="max-w-48 truncate text-xs text-slate-500">
                            {{ $exhibitor?->company_name ?? 'Exposant' }}
                        </p>
                    </div>

                    <!-- USER AVATAR -->
                    <span
                        class="flex size-10 items-center justify-center rounded-full bg-[#0F2347] text-sm font-bold text-white"
                        aria-label="Profil utilisateur"
                    >
                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                    </span>

                    <!-- LOGOUT -->
                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-red-50 hover:text-red-600"
                        >
                            Déconnexion
                        </button>
                    </form>

                </div>

            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="mx-auto max-w-7xl px-4 py-7 md:px-8 md:py-9">
            {{ $slot }}
        </main>

    </div>

</body>
</html>
