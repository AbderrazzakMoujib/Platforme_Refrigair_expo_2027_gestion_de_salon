@props(['admin', 'adminInitials'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('assets/images/icons/favicon_io.png') }}">
        <title>{{ $title ?? 'Admin | Refrigair Expo 2027' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#F5F7FB] font-sans text-slate-900 antialiased">
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 flex-col bg-[#0F2347] px-5 py-6 md:flex">
            <a href="{{ route('admin.index') }}" class="flex min-h-16 items-center justify-center rounded-xl px-2 py-2" aria-label="Refrigair Expo 2027">
                <img src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-logo-8.png') }}" alt="Refrigair Expo 2027" class="block h-auto max-h-14 w-full max-w-[220px] object-contain" />
            </a>

            <nav class="mt-10 space-y-1" aria-label="Navigation principale">
                <x-admin.sidebar-link label="Vue globale" icon="overview" :active="true" :available="true" />
                <x-admin.sidebar-link label="Salons" icon="calendar" />
                <x-admin.sidebar-link label="Commerciaux" icon="users" :href="route('admin.commerciaux.index')" :active="request()->routeIs('admin.commerciaux.*')" :available="true" />
                <x-admin.sidebar-link label="Exposants" icon="users" />
                <x-admin.sidebar-link label="Badges" icon="badge" />
                <x-admin.sidebar-link label="Équipements" icon="box" />
                <x-admin.sidebar-link label="Demandes" icon="inbox" />
                <x-admin.sidebar-link label="Statistiques" icon="chart" />
                <x-admin.sidebar-link label="Notifications" icon="bell" />
                <x-admin.sidebar-link label="Paramètres" icon="settings" />
            </nav>

            <div class="mt-auto rounded-xl border border-white/10 bg-white/5 p-3 text-xs leading-5 text-blue-100">
                Les modules de gestion arrivent bientôt.
            </div>
        </aside>

        <div class="min-h-screen md:pl-72">
            <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 px-4 py-3 backdrop-blur md:px-8">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
                    <details class="relative md:hidden">
                        <summary class="grid size-10 cursor-pointer place-items-center rounded-lg border border-slate-200 text-slate-700 marker:content-none">
                            <x-admin.icon name="menu" class="size-5" />
                        </summary>
                        <div class="absolute left-0 top-12 w-72 rounded-xl bg-[#0F2347] p-3 shadow-xl">
                            <nav class="space-y-1" aria-label="Navigation mobile">
                                <x-admin.sidebar-link
                                 label="Vue globale"
                                 icon="overview"
                                 :href="route('admin.index')"
                                 :active="request()->routeIs('admin.index')"
                                 :available="true"/>
                                <x-admin.sidebar-link label="Salons" icon="calendar" />
                                <x-admin.sidebar-link label="Commerciaux" icon="users" :href="route('admin.commerciaux.index')" :active="request()->routeIs('admin.commerciaux.*')" :available="true" />
                                <x-admin.sidebar-link label="Exposants" icon="users" />
                                <x-admin.sidebar-link label="Badges" icon="badge" />
                                <x-admin.sidebar-link label="Équipements" icon="box" />
                                <x-admin.sidebar-link label="Demandes" icon="inbox" />
                                <x-admin.sidebar-link label="Statistiques" icon="chart" />
                                <x-admin.sidebar-link label="Notifications" icon="bell" />
                                <x-admin.sidebar-link label="Paramètres" icon="settings" />
                            </nav>
                        </div>
                    </details>

                    <p class="hidden text-sm font-medium text-slate-500 sm:block">Administration Refrigair Expo 2027</p>

                    <div class="ml-auto flex items-center gap-2 sm:gap-4">
                        <button type="button" disabled title="Aucune notification disponible" class="relative grid size-10 cursor-not-allowed place-items-center rounded-lg text-slate-400" aria-label="Notifications bientôt disponibles">
                            <x-admin.icon name="bell" class="size-5" />
                            <span class="absolute right-2 top-2 size-1.5 rounded-full bg-slate-300"></span>
                        </button>
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-semibold text-slate-800">{{ $admin->name }}</p>
                            <p class="text-xs text-slate-500">Administrateur</p>
                        </div>
                        <span class="grid size-10 place-items-center rounded-full bg-[#0F2347] text-sm font-bold text-white">{{ $adminInitials }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700">
                                <x-admin.icon name="logout" class="size-4" />
                                <span class="hidden sm:inline">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-7 md:px-8 md:py-9">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
