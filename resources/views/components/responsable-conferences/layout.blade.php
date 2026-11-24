@props(['user'])
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Responsable Conférences & Lounge | Refrigair Expo 2027' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/icons/favicon_io.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F5F7FB] font-sans text-slate-900 antialiased">
    <div class="min-h-screen md:flex">
        <aside class="bg-[#0F2347] p-5 text-white md:fixed md:inset-y-0 md:w-72 md:overflow-y-auto">
            <a href="{{ route('responsable-conferences.dashboard') }}" class="block rounded-xl px-3 py-4 text-center text-lg font-extrabold">REFRIGAIR EXPO 2027</a>
            <p class="px-3 pb-4 text-center text-xs font-semibold uppercase tracking-wider text-blue-200">Conférences & Lounge</p>
            <nav class="grid gap-1" aria-label="Navigation Responsable">
                @foreach ([['Tableau de bord','responsable-conferences.dashboard'],['Planning','responsable-conferences.planning'],['Demandes','responsable-conferences.requests.index'],['Réservations confirmées','responsable-conferences.confirmed'],['Historique des décisions','responsable-conferences.history'],['Mon profil','responsable-conferences.profile']] as [$label,$route])
                    <a href="{{ route($route) }}" @class(['rounded-xl px-4 py-3 text-sm font-semibold transition', 'bg-white/15 text-white' => request()->routeIs($route), 'text-blue-100 hover:bg-white/10' => ! request()->routeIs($route)])>{{ $label }}</a>
                @endforeach
            </nav>
            <div class="mt-8 flex items-center gap-3 border-t border-white/10 pt-5"><x-profile-avatar :user="$user" class="size-11 shrink-0 text-sm" /><div class="min-w-0"><p class="truncate text-sm font-bold">{{ $user->name }}</p><p class="truncate text-xs text-blue-200">{{ $user->email }}</p></div></div><form method="POST" action="{{ route('logout') }}" class="mt-4">@csrf<button class="w-full rounded-xl border border-white/20 px-4 py-2 text-sm font-semibold hover:bg-white/10">Se déconnecter</button></form>
        </aside>
        <main class="min-w-0 flex-1 p-4 sm:p-6 md:ml-72 md:p-8"><div class="mx-auto max-w-7xl">{{ $slot }}</div></main>
    </div>
</body>
</html>
