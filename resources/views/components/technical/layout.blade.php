@props(['user'])

@php
    $links = [
        ['label' => 'Tableau de bord', 'route' => 'responsable-technique.dashboard', 'match' => 'responsable-technique.dashboard', 'icon' => 'overview'],
        ['label' => 'Équipements', 'route' => 'responsable-technique.equipment', 'match' => 'responsable-technique.equipment', 'icon' => 'box'],
        ['label' => 'Commerciaux', 'route' => 'responsable-technique.commercials', 'match' => 'responsable-technique.commercial*', 'icon' => 'users'],
        ['label' => 'Conférences & Lounge', 'route' => 'responsable-technique.conferences', 'match' => 'responsable-technique.conferences', 'icon' => 'calendar'],
        ['label' => 'Mon profil', 'route' => 'responsable-technique.profile', 'match' => 'responsable-technique.profile*', 'icon' => 'user'],
    ];
    $currentPage = collect($links)->first(fn (array $link) => request()->routeIs($link['match'])) ?? $links[0];
    $recentNotifications = $user->notifications()->latest()->limit(5)->get();
    $unreadNotificationCount = $user->unreadNotifications()->count();
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? $currentPage['label'] }} | REFRIGAIR EXPO 2027</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/icons/favicon_io.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F5F7FB] font-sans text-slate-900 antialiased">
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-[300px] flex-col bg-[#0F2347] px-5 py-6 text-white md:flex">
        <a href="{{ route('responsable-technique.dashboard') }}" class="flex min-h-16 items-center justify-center rounded-2xl px-2 py-2 transition hover:bg-white/5" aria-label="Refrigair Expo 2027, accueil">
            <img src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-logo-8.png') }}" alt="Logo Refrigair Expo 2027" class="block max-h-14 w-full max-w-[220px] object-contain">
        </a>
        <div class="mt-8 px-3">
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-blue-300">Espace métier</p>
            <p class="mt-1 text-sm font-extrabold tracking-wide">Responsable Technique</p>
        </div>
        <nav class="mt-5 flex-1 space-y-1.5 overflow-y-auto" aria-label="Navigation Responsable Technique">
            @foreach ($links as $link)
                @php($active = request()->routeIs($link['match']))
                <a href="{{ route($link['route']) }}" @if ($active) aria-current="page" @endif @class(['group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-300', 'bg-blue-600 text-white shadow-lg shadow-blue-950/20' => $active, 'text-blue-100 hover:bg-white/10 hover:text-white' => ! $active])>
                    <x-admin.icon :name="$link['icon']" class="size-5 shrink-0 opacity-90" />
                    <span class="flex-1">{{ $link['label'] }}</span>
                    @if ($active)<span class="size-1.5 rounded-full bg-white" aria-hidden="true"></span>@endif
                </a>
            @endforeach
        </nav>
        <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-blue-200">Événement</p>
            <div class="relative mt-3 h-[76px] overflow-hidden rounded-xl border border-white/10 bg-[#102B58]">
                <img src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-afich_refrigair-expo_2027.jpeg') }}" alt="" aria-hidden="true" class="size-full object-cover object-[center_28%] opacity-75">
                <div class="absolute inset-0 bg-gradient-to-r from-[#0F2347]/75 via-[#0F2347]/25 to-transparent"></div>
                <span class="absolute bottom-2 left-3 rounded-full bg-white/15 px-2 py-1 text-[9px] font-bold uppercase tracking-wider text-white ring-1 ring-white/20">HVAC-R · Casablanca</span>
            </div>
            <p class="mt-3 text-sm font-extrabold">Refrigair Expo 2027</p>
            <p class="mt-1 text-xs leading-5 text-blue-200">2–4 février 2027 · OFEC Casablanca</p>
        </div>
        <div class="mt-4 flex items-center gap-3 border-t border-white/10 pt-4">
            <x-profile-avatar :user="$user" class="size-10 shrink-0 text-xs" />
            <div class="min-w-0 flex-1"><p class="truncate text-sm font-bold">{{ $user->name }}</p><p class="text-xs text-blue-200">Responsable Technique</p></div>
        </div>
    </aside>

    <div class="min-h-screen md:pl-[300px]">
        <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/95 px-4 py-3 backdrop-blur md:px-8">
            <div class="mx-auto flex max-w-[1500px] items-center justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                    <details class="relative md:hidden">
                        <summary class="grid size-10 cursor-pointer list-none place-items-center rounded-xl border border-slate-200 text-[#0F2347] hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500" aria-label="Ouvrir la navigation">
                            <x-admin.icon name="menu" class="size-5" />
                        </summary>
                        <nav class="absolute left-0 top-12 z-50 w-[min(19rem,calc(100vw-2rem))] rounded-2xl bg-[#0F2347] p-3 text-white shadow-2xl" aria-label="Navigation mobile">
                            <div class="mb-3 flex items-center gap-3 border-b border-white/10 px-2 pb-3"><img src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-logo-8.png') }}" alt="Refrigair Expo 2027" class="h-9 max-w-40 object-contain"></div>
                            @foreach ($links as $link)
                                @php($active = request()->routeIs($link['match']))
                                <a href="{{ route($link['route']) }}" @if ($active) aria-current="page" @endif @class(['flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold', 'bg-blue-600 text-white' => $active, 'text-blue-100 hover:bg-white/10' => ! $active])><x-admin.icon :name="$link['icon']" class="size-5" />{{ $link['label'] }}</a>
                            @endforeach
                        </nav>
                    </details>
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700"><x-admin.icon name="layers" class="size-5" /></span>
                    <div class="min-w-0">
                        <h1 class="truncate text-base font-extrabold text-[#0F2347] sm:text-lg">Espace Responsable Technique</h1>
                        <p class="hidden truncate text-xs text-slate-500 sm:mt-0.5 sm:block">{{ request()->routeIs('responsable-technique.equipment') ? 'Catalogue des équipements et état des disponibilités' : 'Consultation, suivi des équipements, commerciaux et conférences' }}</p>
                    </div>
                </div>

                <details class="relative shrink-0">
                    <summary class="relative grid size-10 cursor-pointer list-none place-items-center rounded-xl text-slate-600 transition hover:bg-blue-50 hover:text-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500" aria-label="Notifications ({{ $unreadNotificationCount }} non lues)">
                        <x-admin.icon name="bell" class="size-5" />
                        @if ($unreadNotificationCount > 0)<span class="absolute -right-0.5 -top-0.5 grid min-h-4 min-w-4 place-items-center rounded-full bg-rose-600 px-1 text-[9px] font-bold text-white ring-2 ring-white">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>@endif
                    </summary>
                    <div class="absolute right-0 top-12 z-50 w-[min(22rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                        <div class="border-b border-slate-100 px-4 py-3"><h2 class="text-sm font-extrabold text-[#0F2347]">Notifications</h2><p class="mt-0.5 text-xs text-slate-500">{{ $unreadNotificationCount }} non lue(s)</p></div>
                        <div class="max-h-80 overflow-y-auto">
                            @if ($recentNotifications->isEmpty())
                                <p class="px-4 py-8 text-center text-sm text-slate-500">Aucune notification pour le moment.</p>
                            @else
                                @foreach ($recentNotifications as $notification)
                                    <article class="border-b border-slate-100 px-4 py-3 last:border-0 {{ is_null($notification->read_at) ? 'bg-blue-50/50' : '' }}"><p class="text-sm font-bold text-[#0F2347]">{{ data_get($notification->data, 'title', 'Notification') }}</p><p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-600">{{ data_get($notification->data, 'message', '') }}</p><p class="mt-1.5 text-[10px] text-slate-400">{{ optional($notification->created_at)->diffForHumans() }}</p></article>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </details>
                <details class="relative shrink-0">
                    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-xl p-1.5 pr-2 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 sm:gap-3 sm:pr-3">
                        <x-profile-avatar :user="$user" class="size-9 shrink-0 text-xs" />
                        <span class="hidden min-w-0 text-left sm:block"><span class="block max-w-40 truncate text-sm font-bold text-[#0F2347]">{{ $user->name }}</span><span class="block text-xs text-slate-500">Responsable Technique</span></span>
                        <svg class="size-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
                    </summary>
                    <div class="absolute right-0 top-12 z-50 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                        <a href="{{ route('responsable-technique.profile') }}" class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-700"><x-admin.icon name="user" class="size-4" />Mon profil</a>
                        <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-slate-100 pt-1">@csrf
                            <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-red-700 hover:bg-red-50"><x-admin.icon name="logout" class="size-4" />Déconnexion</button>
                        </form>
                    </div>
                </details>
            </div>
        </header>
        <main class="mx-auto max-w-[1500px] px-4 py-6 sm:px-6 md:px-8 md:py-8">{{ $slot }}</main>
        <footer class="mx-auto max-w-[1500px] px-4 pb-6 text-center text-xs text-slate-400 sm:px-6 md:px-8">REFRIGAIR EXPO 2027 · Espace de consultation technique</footer>
    </div>
    <x-ui.toast />
</body>
</html>
