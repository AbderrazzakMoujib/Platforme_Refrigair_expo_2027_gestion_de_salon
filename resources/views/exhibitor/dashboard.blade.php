
<x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">

    <!-- WELCOME -->
    <div class="mb-8">

        <p class="text-sm font-semibold text-[#2563EB]">
            Vue globale
        </p>

        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
            Bonjour, {{ $user->name }}
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Bienvenue sur votre espace exposant Refrigair Expo 2027.
        </p>

    </div>

    @if ($exhibitor)

        @php
            $authorized = (int) $exhibitor->authorized_badges;
            $registered = (int) $badgeCount;
            $remaining = max(0, $authorized - $registered);

            $progress = $authorized > 0
                ? min(100, round($registered / $authorized * 100))
                : 0;
        @endphp

        <!-- STATISTICS -->
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
                 aria-label="Statistiques de votre participation">

            <!-- STAND -->
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-500">
                            Mon stand
                        </p>

                        <p class="mt-3 text-3xl font-bold text-slate-900">
                            {{ $exhibitor->stand_number ?: '—' }}
                        </p>
                    </div>

                    <span class="rounded-xl bg-blue-50 p-3 text-xl text-blue-600">
                        ▣
                    </span>
                </div>

                <p class="mt-4 text-xs text-slate-500">
                    Numéro du stand attribué.
                </p>
            </article>

            <!-- AUTHORIZED -->
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">

                <p class="text-sm text-slate-500">
                    Badges autorisés
                </p>

                <p class="mt-3 text-3xl font-bold text-slate-900">
                    {{ $authorized }}
                </p>

                <p class="mt-4 text-xs text-slate-500">
                    Quota attribué par votre commercial.
                </p>
            </article>

            <!-- REGISTERED -->
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">

                <p class="text-sm text-slate-500">
                    Badges enregistrés
                </p>

                <p class="mt-3 text-3xl font-bold text-emerald-600">
                    {{ $registered }}
                </p>

                <p class="mt-4 text-xs text-slate-500">
                    Membres de votre équipe inscrits.
                </p>
            </article>

            <!-- REMAINING -->
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">

                <p class="text-sm text-slate-500">
                    Badges restants
                </p>

                <p class="mt-3 text-3xl font-bold text-amber-600">
                    {{ $remaining }}
                </p>

                <p class="mt-4 text-xs text-slate-500">
                    Places encore disponibles.
                </p>
            </article>

        </section>

        <!-- BADGES PROGRESS + OPTIONS -->
        <section class="mt-6 grid gap-6 xl:grid-cols-5">

            <!-- BADGES -->
            <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 xl:col-span-3">

                <div class="flex flex-wrap items-start justify-between gap-4">

                    <div>
                        <h2 class="text-lg font-bold">
                            Gestion des badges
                        </h2>

                        <p class="mt-2 text-sm text-slate-500">
                            Préparez les badges de votre équipe.
                        </p>
                    </div>

                    <span class="rounded-lg bg-blue-50 px-4 py-2 text-lg font-bold text-blue-700">
                        {{ $progress }}%
                    </span>

                </div>

                <div class="mt-8">

                    <div class="mb-3 flex items-center justify-between text-sm">

                        <span class="font-medium text-slate-600">
                            Progression
                        </span>

                        <span class="font-semibold">
                            {{ $registered }} / {{ $authorized }}
                        </span>
                    </div>

                    <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                        <div
                            class="h-full rounded-full bg-emerald-500 transition-all duration-500"
                            style="width: {{ $progress }}%">
                        </div>
                    </div>

                    <p class="mt-4 text-sm text-slate-500">

                        @if ($authorized === 0)
                            Aucun badge autorisé pour le moment.

                        @elseif ($remaining === 0)
                            Tous vos badges sont enregistrés.

                        @else
                            Il vous reste {{ $remaining }} badge(s)
                            à enregistrer.
                        @endif

                    </p>
                </div>

                <div class="mt-7 flex flex-wrap gap-3">

                    <a href="{{ route('exhibitor.badges.index') }}"
                       class="rounded-xl bg-[#2563EB] px-5 py-3 text-center text-sm font-semibold text-white transition hover:bg-blue-700">
                        Gérer mes badges
                    </a>

                    @if ($remaining > 0)
                        <a href="{{ route('exhibitor.badges.create') }}"
                           class="rounded-xl border border-blue-200 px-5 py-3 text-center text-sm font-semibold text-blue-700 transition hover:bg-blue-50">
                            + Ajouter un badge
                        </a>
                    @endif

                </div>

            </article>

            <!-- EQUIPMENT OPTIONS -->
            <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 xl:col-span-2">

                <div class="flex items-center gap-3">

                    <span class="rounded-xl bg-purple-50 p-3 text-purple-600">
                        ▣
                    </span>

                    <h2 class="text-lg font-bold">
                        Options du stand
                    </h2>

                </div>

                <p class="mt-5 text-sm leading-6 text-slate-500">
                    Besoin de mobilier ou d'équipements supplémentaires
                    pour votre stand ?
                </p>

                <div class="mt-6 space-y-3 text-sm text-slate-600">
                    <p>• Tables et chaises</p>
                    <p>• Machines à café</p>
                    <p>• Poubelles et accessoires</p>
                    <p>• Autres équipements</p>
                </div>

                <div class="mt-7 rounded-xl bg-slate-50 px-4 py-3 text-center text-sm font-medium text-slate-500">
                    Catalogue bientôt disponible
                </div>

            </article>

        </section>

        <!-- STAND INFORMATION -->
        <section id="mon-stand"
                 class="mt-6 scroll-mt-24 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">

            <div class="mb-6">
                <h2 class="text-lg font-bold">
                    Informations de mon stand
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Informations de votre participation au salon.
                </p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">

                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Entreprise
                    </p>

                    <p class="mt-2 font-semibold text-slate-900">
                        {{ $exhibitor->company_name }}
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Numéro du stand
                    </p>

                    <p class="mt-2 font-semibold text-slate-900">
                        {{ $exhibitor->stand_number ?: 'Non attribué' }}
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Surface
                    </p>

                    <p class="mt-2 font-semibold text-slate-900">
                        {{ $exhibitor->area_sqm !== null ? $exhibitor->area_sqm . ' m²' : 'Non renseignée' }}
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                        Responsable
                    </p>

                    <p class="mt-2 font-semibold text-slate-900">
                        {{ $exhibitor->contact_name }}
                    </p>
                </div>

            </div>

        </section>

    @else

        <!-- NO EXHIBITOR LINKED -->
        <section class="rounded-2xl border border-amber-200 bg-white p-8 shadow-sm">

            <h2 class="text-lg font-bold">
                Aucune entreprise associée
            </h2>

            <p class="mt-3 text-sm text-slate-500">
                Votre compte n'est lié à aucune fiche exposant.
                Contactez l'organisation du salon.
            </p>

        </section>

    @endif

</x-exhibitor.layout>
