
<x-exhibitor.layout :user="$user" :exhibitor="auth()->user()->exhibitorProfile">

@php
    $isEdit = (bool) $method;

    $inputClass = 'mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-[#0F2347] shadow-sm focus:border-blue-500 focus:ring-blue-500';

    $labelClass = 'block text-sm font-semibold text-[#0F2347]';

    $exhibitor = auth()->user()->exhibitorProfile;

    $authorized = (int) ($exhibitor?->authorized_badges ?? 0);

    $registered = $exhibitor
        ? $exhibitor->badges()->count()
        : 0;

    $remaining = max(0, $authorized - $registered);
@endphp

<div class="space-y-6">

    <!-- BREADCRUMB -->
    <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">

        <a href="{{ route('exposant.index') }}"
           class="hover:text-blue-600">
            Espace exposant
        </a>

        <span>/</span>

        <a href="{{ route('exhibitor.badges.index') }}"
           class="hover:text-blue-600">
            Mes badges
        </a>

        <span>/</span>

        <span class="font-semibold text-[#2563EB]">
            {{ $isEdit ? 'Modifier un badge' : 'Ajouter un badge' }}
        </span>

    </nav>

    <!-- PAGE HEADER -->
    <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div class="flex items-center gap-4">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">

                <svg class="size-7"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round"
                     aria-hidden="true">

                    <rect x="3" y="4" width="18" height="16" rx="2"/>
                    <circle cx="9" cy="10" r="2"/>
                    <path d="M5 17c0-2 2-3 4-3s4 1 4 3M15 9h4m-4 4h4"/>

                </svg>

            </span>

            <div>

                <h1 class="text-2xl font-extrabold text-[#0F2347] sm:text-3xl">
                    {{ $isEdit ? 'Modifier un badge' : 'Ajouter un badge' }}
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    {{ $isEdit
                        ? 'Mettez à jour les informations du titulaire.'
                        : 'Enregistrez un nouveau membre de votre équipe.' }}
                </p>

            </div>

        </div>

        <a href="{{ route('exhibitor.badges.index') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">

            <svg class="size-5"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.8"
                 aria-hidden="true">
                <path d="m15 18-6-6 6-6"/>
            </svg>

            Retour aux badges
        </a>

    </header>

    <!-- MAIN LAYOUT -->
    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">

        <!-- FORM CARD -->
        <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <!-- CARD HEADER -->
            <div class="border-b border-slate-100 px-6 py-5 sm:px-8">

                <div class="flex items-center gap-3">

                    <span class="grid size-11 place-items-center rounded-xl bg-blue-50 text-blue-600">

                        <svg class="size-6"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             aria-hidden="true">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 21v-2a8 8 0 0 1 16 0v2"/>
                        </svg>

                    </span>

                    <div>
                        <h2 class="text-lg font-extrabold text-[#0F2347]">
                            Informations du titulaire
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Les champs marqués * sont obligatoires.
                        </p>
                    </div>

                </div>

            </div>

            <div class="p-6 sm:p-8">

                @if (session('status'))

                    <div role="status"
                         class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">

                        {{ session('status') }}

                    </div>

                @endif

                @if ($errors->any())

                    <div role="alert"
                         class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">

                        <p class="text-sm font-bold text-red-700">
                            Veuillez corriger les erreurs suivantes :
                        </p>

                        <ul class="mt-2 list-disc space-y-1 pl-5 text-xs text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>

                    </div>

                @endif

                <!-- FORM -->
                <form method="POST"
                      action="{{ $action }}"
                      class="space-y-6">

                    @csrf

                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    <!-- NAME FIELDS -->
                    <div class="grid gap-5 sm:grid-cols-2">

                        <!-- FIRST NAME -->
                        <div>

                            <label for="first_name"
                                   class="{{ $labelClass }}">
                                Prénom *
                            </label>

                            <input id="first_name"
                                   name="first_name"
                                   type="text"
                                   required
                                   maxlength="255"
                                   autocomplete="given-name"
                                   value="{{ old('first_name', $badge->first_name) }}"
                                   placeholder="Ex : Mohamed"
                                   class="{{ $inputClass }}">

                            @error('first_name')
                                <p class="mt-2 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        <!-- LAST NAME -->
                        <div>

                            <label for="last_name"
                                   class="{{ $labelClass }}">
                                Nom *
                            </label>

                            <input id="last_name"
                                   name="last_name"
                                   type="text"
                                   required
                                   maxlength="255"
                                   autocomplete="family-name"
                                   value="{{ old('last_name', $badge->last_name) }}"
                                   placeholder="Ex : Alami"
                                   class="{{ $inputClass }}">

                            @error('last_name')
                                <p class="mt-2 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

                    <!-- FUNCTION -->
                    <div>

                        <label for="function"
                               class="{{ $labelClass }}">
                            Fonction / Poste *
                        </label>

                        <input id="function"
                               name="function"
                               type="text"
                               required
                               maxlength="255"
                               value="{{ old('function', $badge->function) }}"
                               placeholder="Ex : Directeur Commercial"
                               class="{{ $inputClass }}">

                        @error('function')
                            <p class="mt-2 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <!-- EMAIL -->
                    <div>

                        <label for="email"
                               class="{{ $labelClass }}">
                            Adresse email *
                        </label>

                        <div class="relative">

                            <svg class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-slate-400"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 aria-hidden="true">
                                <rect x="3" y="5" width="18" height="14" rx="2"/>
                                <path d="m3 7 9 6 9-6"/>
                            </svg>

                            <input id="email"
                                   name="email"
                                   type="email"
                                   required
                                   maxlength="255"
                                   autocomplete="email"
                                   value="{{ old('email', $badge->email) }}"
                                   placeholder="exemple@entreprise.com"
                                   class="{{ $inputClass }} pl-12">

                        </div>

                        @error('email')
                            <p class="mt-2 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <!-- PHONE -->
                    <div>

                        <label for="phone"
                               class="{{ $labelClass }}">
                            Téléphone
                            <span class="font-normal text-slate-400">
                                (optionnel)
                            </span>
                        </label>

                        <div class="relative">

                            <svg class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-slate-400"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 aria-hidden="true">
                                <path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7l.5 3a2 2 0 0 1-.6 1.8L7.1 10a16 16 0 0 0 6.9 6.9l1.5-1.9a2 2 0 0 1 1.8-.6l3 .5A2 2 0 0 1 22 16.9z"/>
                            </svg>

                            <input id="phone"
                                   name="phone"
                                   type="tel"
                                   maxlength="40"
                                   autocomplete="tel"
                                   value="{{ old('phone', $badge->phone) }}"
                                   placeholder="+212 6 00 00 00 00"
                                   class="{{ $inputClass }} pl-12">

                        </div>

                        @error('phone')
                            <p class="mt-2 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    <!-- ACTIONS -->
                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">

                        <a href="{{ route('exhibitor.badges.index') }}"
                           class="rounded-xl border border-slate-200 px-6 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Annuler
                        </a>

                        <button type="submit"
                                class="rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white transition hover:bg-blue-700">

                            {{ $isEdit
                                ? 'Enregistrer les modifications'
                                : 'Ajouter le badge' }}

                        </button>

                    </div>

                </form>

            </div>

        </section>

        <!-- RIGHT SIDEBAR -->
        <aside class="space-y-5">

            <!-- QUOTA INFO -->
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h2 class="font-extrabold text-[#0F2347]">
                    Quota de badges
                </h2>

                <div class="mt-5 rounded-xl bg-blue-50 p-5 text-center">

                    <p class="text-3xl font-extrabold text-[#2563EB]">
                        {{ $registered }} / {{ $authorized }}
                    </p>

                    <p class="mt-2 text-sm text-slate-600">
                        Badges enregistrés
                    </p>

                </div>

                <div class="mt-4 rounded-xl bg-emerald-50 px-4 py-3 text-center">

                    <p class="font-bold text-emerald-700">
                        {{ $remaining }} badge(s) restant(s)
                    </p>

                </div>

                @if (!$isEdit && $remaining <= 0)
                    <p class="mt-4 text-xs font-semibold text-amber-700">
                        Quota atteint. Contactez votre commercial
                        pour obtenir des badges supplémentaires.
                    </p>
                @endif

            </section>

            <!-- COMPANY INFO -->
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h2 class="font-extrabold text-[#0F2347]">
                    Entreprise associée
                </h2>

                <p class="mt-4 text-lg font-bold text-blue-700">
                    {{ $exhibitor?->company_name ?? 'Non renseignée' }}
                </p>

                <p class="mt-3 text-sm text-slate-500">
                    Chaque badge créé est automatiquement
                    associé à votre entreprise.
                </p>

                @if ($exhibitor?->stand_number)
                    <div class="mt-5 rounded-xl bg-[#F5F7FB] p-4">

                        <p class="text-xs text-slate-500">
                            Numéro du stand
                        </p>

                        <p class="mt-1 text-xl font-extrabold text-[#0F2347]">
                            {{ $exhibitor->stand_number }}
                        </p>

                    </div>
                @endif

            </section>

            <!-- HELP -->
            <section class="rounded-2xl border border-blue-100 bg-blue-50 p-6">

                <div class="flex items-center gap-3">

                    <svg class="size-6 shrink-0 text-blue-700"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 11v5M12 7h.01"/>
                    </svg>

                    <h2 class="font-bold text-blue-900">
                        Informations importantes
                    </h2>

                </div>

                <p class="mt-4 text-sm leading-6 text-blue-800">
                    Vérifiez l'orthographe du prénom, du nom
                    et de la fonction du titulaire avant d'enregistrer.
                    Ces informations seront utilisées pour son badge.
                </p>

            </section>

        </aside>

    </div>

</div>

</x-exhibitor.layout>
