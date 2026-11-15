@props(['commercial', 'commercialInitials'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('assets/images/icons/favicon_io.png') }}">
        <title>{{ $title ?? 'Espace Commercial | Refrigair Expo 2027' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#F5F7FB] font-sans text-slate-900 antialiased">
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 flex-col bg-[#0F2347] px-5 py-6 md:flex">
            <a href="{{ route('commercial.index') }}" class="flex min-h-16 items-center justify-center rounded-xl px-2 py-2" aria-label="Refrigair Expo 2027">
                <img src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-Sans-titre-4-removebg-preview.png') }}" alt="Refrigair Expo 2027" class="block h-auto max-h-14 w-full max-w-[220px] object-contain">
            </a>
            <nav class="mt-10 space-y-1" aria-label="Navigation Commercial">
                <x-admin.sidebar-link label="Vue globale" icon="overview" :active="request()->routeIs('commercial.index')" :available="true" :href="route('commercial.index')" />
                <x-admin.sidebar-link label="Mes exposants" icon="users" :active="request()->routeIs('commercial.exhibitors.*')" :available="true" :href="route('commercial.exhibitors.index')" />
                <x-admin.sidebar-link label="Badges" icon="badge" />
                <x-admin.sidebar-link label="Demandes d'équipements" icon="box" />
                <x-admin.sidebar-link label="Mon profil" icon="settings" :active="request()->routeIs('commercial.profile.*')" :available="true" :href="route('commercial.profile.edit')" />
            </nav>
            <div class="mt-auto rounded-xl border border-white/10 bg-white/5 p-3 text-xs leading-5 text-blue-100">Les outils de suivi arrivent bientôt.</div>
        </aside>

        <div class="min-h-screen md:pl-72">
            <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 px-4 py-3 backdrop-blur md:px-8">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
                    <details class="relative md:hidden">
                        <summary class="grid size-10 cursor-pointer place-items-center rounded-lg border border-slate-200 text-slate-700 marker:content-none"><x-admin.icon name="menu" /></summary>
                        <div class="absolute left-0 top-12 w-72 rounded-xl bg-[#0F2347] p-3 shadow-xl">
                            <nav class="space-y-1" aria-label="Navigation mobile">
                                <x-admin.sidebar-link label="Vue globale" icon="overview" :active="request()->routeIs('commercial.index')" :available="true" :href="route('commercial.index')" />
                                <x-admin.sidebar-link label="Mes exposants" icon="users" :active="request()->routeIs('commercial.exhibitors.*')" :available="true" :href="route('commercial.exhibitors.index')" />
                                <x-admin.sidebar-link label="Badges" icon="badge" />
                                <x-admin.sidebar-link label="Demandes d'équipements" icon="box" />
                                <x-admin.sidebar-link label="Mon profil" icon="settings" :active="request()->routeIs('commercial.profile.*')" :available="true" :href="route('commercial.profile.edit')" />
                            </nav>
                        </div>
                    </details>
                    <p class="hidden text-sm font-medium text-slate-500 sm:block">Espace Commercial · Refrigair Expo 2027</p>
                    <div class="ml-auto flex items-center gap-2 sm:gap-4">
                        <button type="button" disabled title="Aucune notification disponible" class="grid size-10 cursor-not-allowed place-items-center rounded-lg text-slate-300" aria-label="Notifications bientôt disponibles"><x-admin.icon name="bell" /></button>
                        <div class="hidden text-right sm:block"><p class="text-sm font-semibold text-slate-800">{{ $commercial->name }}</p><p class="text-xs text-slate-500">Commercial</p></div>
                        <span class="grid size-10 place-items-center rounded-full bg-[#0F2347] text-sm font-bold text-white">{{ $commercialInitials }}</span>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-red-50 hover:text-red-700"><x-admin.icon name="logout" class="size-4" /><span class="hidden sm:inline">Logout</span></button></form>
                    </div>
                </div>
            </header>
            <main class="mx-auto max-w-7xl px-4 py-7 md:px-8 md:py-9">{{ $slot }}</main>
        </div>
    </body>
</html>
