
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mes badges | Refrigair Expo 2027</title>

    <link rel="icon" type="image/png"
          href="{{ asset('assets/images/icons/favicon_io.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#F5F7FB] font-sans text-slate-900">

    <!-- Header -->
    <header class="bg-[#0F2347] px-5 py-4">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4">

            <a href="{{ route('exposant.index') }}">
                <img
                    src="{{ asset('assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-Sans-titre-4-removebg-preview.png') }}"
                    alt="Refrigair Expo 2027"
                    class="h-14 w-48 object-contain sm:w-56"
                >
            </a>

            <a href="{{ route('exposant.index') }}"
               class="text-sm font-semibold text-white hover:text-blue-200">
                Retour au Dashboard
            </a>

        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">

        <!-- Page Title -->
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

            <div>
                <p class="text-sm font-semibold text-blue-600">
                    Espace Exposant
                </p>

                <h1 class="mt-2 text-3xl font-bold">
                    Gestion des badges
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Gérez les badges de votre équipe pour Refrigair Expo 2027.
                </p>
            </div>

            @if ($count < $exhibitor->authorized_badges)

                <a href="{{ route('exhibitor.badges.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">

                    <span class="text-xl leading-none">+</span>
                    Ajouter un badge

                </a>

            @else

                <span class="rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                    Quota de badges atteint
                </span>

            @endif

        </div>

        <!-- Success message -->
        @if (session('status'))

            <div role="status"
                 class="mt-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-700">
                {{ session('status') }}
            </div>

        @endif

        <!-- Statistics -->
        <section class="mt-8 grid gap-4 sm:grid-cols-3">

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">
                    Badges autorisés
                </p>

                <p class="mt-3 text-3xl font-bold text-slate-900">
                    {{ $exhibitor->authorized_badges }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">
                    Badges enregistrés
                </p>

                <p class="mt-3 text-3xl font-bold text-blue-600">
                    {{ $count }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">
                    Badges restants
                </p>

                <p class="mt-3 text-3xl font-bold text-emerald-600">
                    {{ max(0, $exhibitor->authorized_badges - $count) }}
                </p>
            </div>

        </section>

        <!-- Progress -->
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            @php
                $progress = $exhibitor->authorized_badges > 0
                    ? min(100, round(
                        $count / $exhibitor->authorized_badges * 100
                    ))
                    : 0;
            @endphp

            <div class="flex items-center justify-between">
                <h2 class="font-bold">
                    Progression des badges
                </h2>

                <span class="font-bold text-blue-600">
                    {{ $progress }}%
                </span>
            </div>

            <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-100">
                <div
                    class="h-full rounded-full bg-blue-600 transition-all duration-500"
                    style="width: {{ $progress }}%">
                </div>
            </div>

            <p class="mt-3 text-sm text-slate-500">
                {{ $count }} badge(s) enregistré(s) sur
                {{ $exhibitor->authorized_badges }} autorisé(s).
            </p>

        </section>

        <!-- Badges List -->
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="flex items-center justify-between gap-4">

                <div>
                    <h2 class="text-lg font-bold">
                        Liste des badges
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Membres de votre équipe enregistrés.
                    </p>
                </div>

                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                    {{ $count }} badge(s)
                </span>

            </div>

            <div class="mt-6 space-y-4">

                @forelse ($badges as $badge)

                    <article class="rounded-xl border border-slate-200 p-5 transition hover:bg-slate-50">

                        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

                            <div class="min-w-0">

                                <h3 class="text-base font-bold text-slate-900">
                                    {{ $badge->first_name }}
                                    {{ $badge->last_name }}
                                </h3>

                                <p class="mt-1 text-sm text-slate-600">
                                    {{ $badge->function }}
                                </p>

                                <p class="mt-2 break-all text-sm text-slate-500">
                                    {{ $badge->email }}
                                </p>

                                @if ($badge->phone)
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $badge->phone }}
                                    </p>
                                @endif

                            </div>

                            <div class="flex flex-wrap gap-3">

                                <a href="{{ route('exhibitor.badges.edit', $badge) }}"
                                   class="rounded-lg border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                                    Modifier
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('exhibitor.badges.destroy', $badge) }}"
                                    onsubmit="return confirm('Voulez-vous vraiment supprimer ce badge ?')">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">
                                        Supprimer
                                    </button>

                                </form>

                            </div>

                        </div>

                    </article>

                @empty

                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center">

                        <h3 class="text-lg font-bold text-slate-700">
                            Aucun badge enregistré
                        </h3>

                        <p class="mt-2 text-sm text-slate-500">
                            Ajoutez les membres de votre équipe
                            pour préparer leur participation au salon.
                        </p>

                        @if ($exhibitor->authorized_badges > 0)

                            <a href="{{ route('exhibitor.badges.create') }}"
                               class="mt-5 inline-flex rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                                + Créer mon premier badge
                            </a>

                        @else

                            <p class="mt-4 text-sm font-medium text-amber-700">
                                Aucun badge autorisé.
                                Contactez votre commercial.
                            </p>

                        @endif

                    </div>

                @endforelse

            </div>

            <div class="mt-6">
                {{ $badges->links() }}
            </div>

        </section>

    </main>

</body>
</html>
