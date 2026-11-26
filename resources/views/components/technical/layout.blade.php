@props(['user'])

@php
    $links = [
        ['label' => 'Tableau de bord', 'route' => 'responsable-technique.dashboard'],
        ['label' => 'Équipements', 'route' => 'responsable-technique.equipment'],
        ['label' => 'Commerciaux', 'route' => 'responsable-technique.commercials'],
        ['label' => 'Conférences & Lounge', 'route' => 'responsable-technique.conferences'],
    ];
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Espace Responsable Technique | Refrigair Expo 2027' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/icons/favicon_io.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F5F7FB] font-sans text-slate-900 antialiased">
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 flex-col bg-[#0F2347] px-5 py-6 text-white md:flex">
        <a href="{{ route('responsable-technique.dashboard') }}" class="flex min-h-16 items-center justify-center rounded-xl px-2 py-2" aria-label="Refrigair Expo 2027">
            <img src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-logo-8.png') }}" alt="Logo Refrigair Expo 2027" class="block max-h-14 w-full max-w-[220px] object-contain">
        </a>
        <p class="mt-6 px-3 text-[11px] font-bold uppercase tracking-widest text-blue-300">Espace Responsable Technique</p>
        <nav class="mt-4 flex-1 space-y-1 overflow-y-auto" aria-label="Navigation Responsable Technique">
            @foreach ($links as $link)
                @php($active = request()->routeIs($link['route']))
                <a href="{{ route($link['route']) }}" @if ($active) aria-current="page" @endif @class(['block rounded-xl px-4 py-3 text-sm font-semibold transition', 'bg-[#2563EB] text-white shadow-md' => $active, 'text-blue-100 hover:bg-white/10' => ! $active])>
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
            <p class="text-sm font-extrabold">Refrigair Expo 2027</p>
            <p class="mt-1 text-xs text-blue-200">Consultation en lecture seule</p>
        </div>
    </aside>

    <div class="md:pl-72">
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur md:px-8">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-bold text-[#0F2347]">{{ $title ?? 'Espace Responsable Technique' }}</p>
                    <p class="truncate text-xs text-slate-500">{{ $user->name }} · {{ $user->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Déconnexion</button>
                </form>
                <nav class="flex w-full gap-2 overflow-x-auto pb-1 md:hidden" aria-label="Navigation mobile">
                    @foreach ($links as $link)
                        <a href="{{ route($link['route']) }}" @class(['shrink-0 rounded-lg px-3 py-2 text-xs font-bold', 'bg-blue-600 text-white' => request()->routeIs($link['route']), 'bg-slate-100 text-slate-700' => ! request()->routeIs($link['route'])])>{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-4 py-7 md:px-8 md:py-9">{{ $slot }}</main>
    </div>
    <x-ui.toast />
</body>
</html>
