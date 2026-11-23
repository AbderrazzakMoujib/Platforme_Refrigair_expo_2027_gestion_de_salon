
<x-commercial.layout
    :commercial="$commercial"
    :commercial-initials="$commercial->initials"
>
    @php
        $photoUrl = $commercial->profile_photo_url;
        $initials = $commercial->initials;
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">

        {{-- BREADCRUMB --}}
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('commercial.index') }}"
               class="transition hover:text-blue-600">
                Espace commercial
            </a>

            <span>/</span>

            <span class="font-semibold text-[#0F2347]">
                Mon profil
            </span>
        </nav>

        {{-- PAGE HEADER --}}
        <header class="flex items-center gap-4">

            <div class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <svg class="size-7"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 21a8 8 0 0 1 16 0"/>
                </svg>
            </div>

            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                    Mon profil
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Consultez vos informations et gérez la sécurité de votre compte.
                </p>
            </div>

        </header>

        {{-- SUCCESS MESSAGE --}}
        @if (session('status'))
            <div role="status"
                 class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">

                <svg class="size-5 shrink-0"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     stroke-linecap="round"
                     stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m8 12 3 3 5-6"/>
                </svg>

                {{ session('status') }}
            </div>
        @endif

        {{-- VALIDATION ERRORS --}}
        @if ($errors->any())
            <div role="alert"
                 class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">

                <p class="font-bold">
                    Veuillez corriger les erreurs :
                </p>

                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ================================================= --}}
        {{-- PROFILE BANNER --}}
        {{-- ================================================= --}}

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="relative overflow-hidden bg-gradient-to-r from-[#0F2347] via-[#164987] to-[#2563EB] px-6 py-8 sm:px-10 sm:py-10">

                {{-- BACKGROUND DECORATION --}}
                <div class="pointer-events-none absolute -right-16 -top-24 size-80 rounded-full border border-white/15"></div>
                <div class="pointer-events-none absolute -right-4 -top-12 size-64 rounded-full border border-white/15"></div>
                <div class="pointer-events-none absolute bottom-[-100px] right-40 size-60 rounded-full bg-sky-400/10 blur-3xl"></div>

                <div class="relative z-10 flex flex-col gap-5 sm:flex-row sm:items-center">

                    {{-- AVATAR --}}
                    <div class="grid size-24 shrink-0 place-items-center overflow-hidden rounded-full border-4 border-white bg-blue-600 text-white shadow-xl sm:size-28">

                        @if ($photoUrl)
                            <img
                                src="{{ $photoUrl }}"
                                alt="Photo de profil de {{ $commercial->name }}"
                                class="h-full w-full object-cover"
                                data-profile-photo
                            >

                            <span data-profile-fallback
                                  class="hidden text-3xl font-extrabold">
                                {{ $initials }}
                            </span>
                        @else
                            <span class="text-3xl font-extrabold">
                                {{ $initials }}
                            </span>
                        @endif

                    </div>

                    {{-- PROFILE INFORMATION --}}
                    <div class="min-w-0 text-white">

                        <h2 class="break-words text-2xl font-extrabold tracking-tight sm:text-3xl">
                            {{ $commercial->name }}
                        </h2>

                        <p class="mt-2 break-all text-sm text-blue-100">
                            {{ $commercial->email }}
                        </p>

                        <div class="mt-4 flex flex-wrap items-center gap-3">

                            <span class="inline-flex items-center gap-2 rounded-full bg-blue-500 px-4 py-2 text-xs font-bold text-white">

                                <svg class="size-4"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8">
                                    <rect x="3" y="7" width="18" height="14" rx="2"/>
                                    <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>

                                Commercial

                            </span>

                            <span class="text-xs text-blue-100">
                                Compte professionnel
                            </span>

                        </div>

                        @if ($commercial->created_at)
                            <div class="mt-4 flex items-center gap-2 text-xs text-blue-100">

                                <svg class="size-4"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8">
                                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                                    <path d="M7 3v4M17 3v4M3 10h18"/>
                                </svg>

                                Membre depuis le
                                {{ $commercial->created_at->locale('fr')->translatedFormat('d M Y') }}

                            </div>
                        @endif

                    </div>

                </div>

            </div>

        </section>

        {{-- ================================================= --}}
        {{-- MAIN CONTENT --}}
        {{-- ================================================= --}}

        <div class="grid items-start gap-6 lg:grid-cols-2">

            {{-- ============================================= --}}
            {{-- PERSONAL INFORMATION --}}
            {{-- ============================================= --}}

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="flex items-center gap-4 border-b border-slate-100 px-6 py-5">

                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-600">

                        <svg class="size-6"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 21a8 8 0 0 1 16 0"/>
                        </svg>

                    </span>

                    <div>
                        <h2 class="text-lg font-extrabold text-[#0F2347]">
                            Informations personnelles
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Informations associées à votre compte.
                        </p>
                    </div>

                </div>

                <div class="space-y-4 p-6">

                    {{-- NAME --}}
                    <div class="flex flex-col gap-2 rounded-xl bg-[#F5F7FB] p-4 sm:flex-row sm:items-center sm:gap-4">

                        <div class="flex min-w-0 items-center gap-3 sm:w-44 sm:shrink-0">

                            <svg class="size-5 shrink-0 text-slate-500"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <circle cx="12" cy="8" r="4"/>
                                <path d="M4 21a8 8 0 0 1 16 0"/>
                            </svg>

                            <span class="text-sm font-semibold text-[#0F2347]">
                                Nom complet
                            </span>

                        </div>

                        <p class="min-w-0 break-words text-sm text-slate-700">
                            {{ $commercial->name }}
                        </p>

                    </div>

                    {{-- EMAIL --}}
                    <div class="flex flex-col gap-2 rounded-xl bg-[#F5F7FB] p-4 sm:flex-row sm:items-center sm:gap-4">

                        <div class="flex min-w-0 items-center gap-3 sm:w-44 sm:shrink-0">

                            <svg class="size-5 shrink-0 text-slate-500"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <rect x="3" y="5" width="18" height="14" rx="2"/>
                                <path d="m3 7 9 7 9-7"/>
                            </svg>

                            <span class="text-sm font-semibold text-[#0F2347]">
                                Adresse email
                            </span>

                        </div>

                        <p class="min-w-0 break-all text-sm text-slate-700">
                            {{ $commercial->email }}
                        </p>

                    </div>

                    {{-- PHONE --}}
                    <div class="flex flex-col gap-2 rounded-xl bg-[#F5F7FB] p-4 sm:flex-row sm:items-center sm:gap-4">

                        <div class="flex min-w-0 items-center gap-3 sm:w-44 sm:shrink-0">

                            <svg class="size-5 shrink-0 text-slate-500"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <path d="M7 3h4l1 5-3 2a15 15 0 0 0 5 5l2-3 5 1v4a3 3 0 0 1-3 3A15 15 0 0 1 4 6a3 3 0 0 1 3-3Z"/>
                            </svg>

                            <span class="text-sm font-semibold text-[#0F2347]">
                                Téléphone
                            </span>

                        </div>

                        <p class="min-w-0 break-words text-sm text-slate-700">
                            {{ $commercial->phone ?: 'Non renseigné' }}
                        </p>

                    </div>

                    {{-- ROLE --}}
                    <div class="flex flex-col gap-2 rounded-xl bg-[#F5F7FB] p-4 sm:flex-row sm:items-center sm:gap-4">

                        <div class="flex min-w-0 items-center gap-3 sm:w-44 sm:shrink-0">

                            <svg class="size-5 shrink-0 text-slate-500"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <rect x="3" y="7" width="18" height="14" rx="2"/>
                                <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>

                            <span class="text-sm font-semibold text-[#0F2347]">
                                Rôle
                            </span>

                        </div>

                        <div>
                            <span class="inline-flex rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-bold text-blue-700">
                                Commercial
                            </span>
                        </div>

                    </div>

                    {{-- INFO --}}
                    <div class="mt-5 flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50 p-4">

                        <svg class="mt-0.5 size-5 shrink-0 text-blue-600"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 11v5M12 8h.01"/>
                        </svg>

                        <p class="text-sm leading-relaxed text-blue-900">
                            Pour modifier vos informations personnelles,
                            contactez votre administrateur.
                        </p>

                    </div>

                </div>

            </section>

            {{-- ============================================= --}}
            {{-- SECURITY / PASSWORD --}}
            {{-- ============================================= --}}

            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="flex items-center gap-4 border-b border-slate-100 px-6 py-5">

                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-600">

                        <svg class="size-6"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <rect x="5" y="10" width="14" height="11" rx="2"/>
                            <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                        </svg>

                    </span>

                    <div>
                        <h2 class="text-lg font-extrabold text-[#0F2347]">
                            Sécurité du compte
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Modifier votre mot de passe.
                        </p>
                    </div>

                </div>

                <form
                    method="POST"
                    action="{{ route('commercial.profile.update') }}"
                    class="space-y-5 p-6"
                >
                    @csrf
                    @method('PUT')

                    {{-- CURRENT PASSWORD --}}
                    <div>
                        <label for="current_password"
                               class="block text-sm font-bold text-[#0F2347]">

                            Mot de passe actuel
                            <span class="text-red-500">*</span>

                        </label>

                        <div class="relative mt-2">

                            <input
                                id="current_password"
                                name="current_password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="Votre mot de passe actuel"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm transition focus:border-blue-500 focus:ring-blue-500 @error('current_password') border-red-400 @enderror"
                            >

                            <button
                                type="button"
                                data-toggle-password="current_password"
                                aria-label="Afficher le mot de passe actuel"
                                aria-pressed="false"
                                class="absolute inset-y-0 right-0 grid w-12 place-items-center text-slate-400 transition hover:text-blue-600"
                            >
                                <svg class="size-5"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>

                        </div>

                        @error('current_password')
                            <p class="mt-2 text-xs font-medium text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- NEW PASSWORD --}}
                    <div>
                        <label for="password"
                               class="block text-sm font-bold text-[#0F2347]">

                            Nouveau mot de passe
                            <span class="text-red-500">*</span>

                        </label>

                        <div class="relative mt-2">

                            <input
                                id="password"
                                name="password"
                                type="password"
                                minlength="8"
                                required
                                autocomplete="new-password"
                                placeholder="Minimum 8 caractères"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm transition focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-400 @enderror"
                            >

                            <button
                                type="button"
                                data-toggle-password="password"
                                aria-label="Afficher le nouveau mot de passe"
                                aria-pressed="false"
                                class="absolute inset-y-0 right-0 grid w-12 place-items-center text-slate-400 transition hover:text-blue-600"
                            >
                                <svg class="size-5"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>

                        </div>

                        @error('password')
                            <p class="mt-2 text-xs font-medium text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- CONFIRM PASSWORD --}}
                    <div>
                        <label for="password_confirmation"
                               class="block text-sm font-bold text-[#0F2347]">

                            Confirmer le nouveau mot de passe
                            <span class="text-red-500">*</span>

                        </label>

                        <div class="relative mt-2">

                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                minlength="8"
                                required
                                autocomplete="new-password"
                                placeholder="Répétez votre nouveau mot de passe"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 pr-12 text-sm transition focus:border-blue-500 focus:ring-blue-500"
                            >

                            <button
                                type="button"
                                data-toggle-password="password_confirmation"
                                aria-label="Afficher la confirmation"
                                aria-pressed="false"
                                class="absolute inset-y-0 right-0 grid w-12 place-items-center text-slate-400 transition hover:text-blue-600"
                            >
                                <svg class="size-5"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8">
                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>

                        </div>

                    </div>

                    {{-- SECURITY NOTICE --}}
                    <div class="flex items-start gap-3 rounded-xl border border-amber-100 bg-amber-50 p-4">

                        <svg class="mt-0.5 size-5 shrink-0 text-amber-600"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8">
                            <path d="M12 3 2 21h20L12 3Z"/>
                            <path d="M12 9v5M12 18h.01"/>
                        </svg>

                        <p class="text-sm leading-relaxed text-amber-900">
                            Choisissez un mot de passe unique et ne le partagez avec personne.
                        </p>

                    </div>

                    {{-- SUBMIT --}}
                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >

                        <svg class="size-5"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="m5 12 4 4L19 6"/>
                        </svg>

                        Enregistrer le nouveau mot de passe

                    </button>

                </form>

            </section>

        </div>

        {{-- FOOTER --}}
        <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-center gap-3">

                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600">

                    <svg class="size-5"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>

                </span>

                <div>
                    <p class="text-sm font-bold text-[#0F2347]">
                        Compte professionnel
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Refrigair Expo 2027 — Espace commercial
                    </p>
                </div>

            </div>

            <a href="{{ route('commercial.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">

                Retour au tableau de bord

            </a>

        </div>

    </div>

    {{-- JAVASCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // Afficher / masquer les mots de passe
            document.querySelectorAll('[data-toggle-password]')
                .forEach(button => {

                    button.addEventListener('click', () => {

                        const input = document.getElementById(
                            button.dataset.togglePassword
                        );

                        if (!input) return;

                        const visible = input.type === 'password';

                        input.type = visible ? 'text' : 'password';

                        button.setAttribute(
                            'aria-pressed',
                            visible ? 'true' : 'false'
                        );

                        button.setAttribute(
                            'aria-label',
                            visible
                                ? 'Masquer le mot de passe'
                                : 'Afficher le mot de passe'
                        );
                    });

                });

            // Fallback si la photo est indisponible
            document.querySelectorAll('[data-profile-photo]')
                .forEach(image => {

                    function showFallback() {
                        image.classList.add('hidden');

                        const fallback = image.parentElement
                            .querySelector('[data-profile-fallback]');

                        if (fallback) {
                            fallback.classList.remove('hidden');
                        }
                    }

                    image.addEventListener('error', showFallback);

                    if (image.complete && image.naturalWidth === 0) {
                        showFallback();
                    }

                });

        });
    </script>

</x-commercial.layout>
