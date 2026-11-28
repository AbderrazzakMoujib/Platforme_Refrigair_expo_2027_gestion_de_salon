
<x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">

@php
    $initials = collect(
        preg_split('/\s+/', trim($exhibitor->company_name))
    )
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('') ?: 'E';

    $logoUrl = $exhibitor->logo
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($exhibitor->logo)
        : null;

    $commercial = $exhibitor->commercial;

    $formatDate = fn ($date) => $date
        ? $date->format('d/m/Y')
        : '—';
@endphp

<div class="space-y-6">

    <!-- PAGE HEADER -->
    <header class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">

        <div>
            <nav class="mb-4 text-sm text-slate-500">
                <a href="{{ route('exposant.index') }}"
                   class="hover:text-blue-600">
                    Espace exposant
                </a>
                <span class="mx-2">/</span>
                <span class="font-semibold text-blue-600">
                    Mon entreprise / Mon stand
                </span>
            </nav>

            <div class="flex items-center gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                    <svg class="size-7" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor"
                         stroke-width="1.8" aria-hidden="true">
                        <rect x="4" y="3" width="16" height="18" rx="1"/>
                        <path d="M9 21v-5h6v5M8 7h2m4 0h2M8 11h2m4 0h2"/>
                    </svg>
                </span>

                <div>
                    <h1 class="text-2xl font-extrabold text-[#0F2347] sm:text-3xl">
                        Mon entreprise / Mon stand
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Consultez les informations de votre entreprise
                        et les détails de votre participation.
                    </p>
                </div>
            </div>
        </div>

        
{{-- BANNIÈRE OFFICIELLE REFRIGAIR EXPO --}}

<div class="w-full min-w-0 overflow-hidden rounded-2xl border border-blue-100 bg-[#DFF2F8] shadow-sm xl:w-[570px]">

    <img
        src="{{ asset('assets/images/refrig_air_expo_cover.jpg') }}"
        alt="Refrigair Expo 2027 — Du 2 au 4 février 2027 à l'OFEC Casablanca"
        class="block h-auto w-full object-contain"
        loading="eager"
        decoding="async"
    >

</div>


    </header>

    @if (session('status'))
        <div role="status"
             class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert"
             class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p class="font-bold">
                Veuillez corriger les erreurs :
            </p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- TOP CARDS -->
    <div class="grid items-start gap-5 xl:grid-cols-2">

        <!-- INFORMATIONS ENTREPRISE -->
        <section id="entreprise"
                 class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-6">

                <h2 class="text-lg font-extrabold text-[#0F2347]">
                    Informations de l'entreprise
                </h2>

                <a href="{{ route('exhibitor.profile') }}" class="rounded-xl border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50">Modifier le profil</a>

            </div>

            <div class="p-6">

                <!-- COMPANY LOGO -->
                <div class="flex items-center gap-4 rounded-xl bg-[#F5F7FB] p-4">

                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}"
                             alt="Logo {{ $exhibitor->company_name }}"
                             class="size-24 shrink-0 rounded-xl border border-slate-200 bg-white object-contain p-2">
                    @else
                        <span class="grid size-24 shrink-0 place-items-center rounded-xl bg-white text-2xl font-extrabold text-blue-700">
                            {{ $initials }}
                        </span>
                    @endif

                    <div class="min-w-0">
                        <h3 class="break-words text-xl font-extrabold text-[#0F2347]">
                            {{ $exhibitor->company_name }}
                        </h3>

                        <span class="mt-3 inline-flex rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                            Fiche exposant
                        </span>
                    </div>

                </div>

                <!-- COMPANY DETAILS -->
                <dl class="mt-5 divide-y divide-slate-100">

                    <div class="flex flex-wrap justify-between gap-3 py-3 text-sm">
                        <dt class="font-medium text-slate-500">
                            Raison sociale
                        </dt>
                        <dd class="font-bold text-[#0F2347]">
                            {{ $exhibitor->company_name }}
                        </dd>
                    </div>

                    <div class="flex flex-wrap justify-between gap-3 py-3 text-sm">
                        <dt class="font-medium text-slate-500">
                            Responsable
                        </dt>
                        <dd class="font-bold text-[#0F2347]">
                            {{ $exhibitor->contact_name ?: '—' }}
                        </dd>
                    </div>

                    <div class="flex flex-wrap justify-between gap-3 py-3 text-sm">
                        <dt class="font-medium text-slate-500">
                            Téléphone
                        </dt>
                        <dd class="font-bold text-[#0F2347]">
                            {{ $exhibitor->phone ?: '—' }}
                        </dd>
                    </div>

                    <div class="flex flex-wrap justify-between gap-3 py-3 text-sm">
                        <dt class="font-medium text-slate-500">
                            Email
                        </dt>
                        <dd class="break-all font-bold text-[#0F2347]">
                            {{ $exhibitor->email ?: '—' }}
                        </dd>
                    </div>

                </dl>

                <p class="mt-5 rounded-xl bg-blue-50/60 p-4 text-sm text-slate-600">Pour modifier le logo ou les coordonnées de votre entreprise, rendez-vous dans <a href="{{ route('exhibitor.profile') }}" class="font-bold text-blue-700 hover:underline">Mon Profil</a>.</p>

            </div>

        </section>

        <!-- RIGHT COLUMN -->
        <div class="space-y-5">

            
{{-- ======================================== --}}
{{-- DÉTAILS DU STAND                         --}}
{{-- ======================================== --}}

<section
    id="details-stand"
    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
>

    {{-- HEADER --}}

    <div class="flex flex-wrap items-center justify-between gap-3 px-6 pt-6">

        <div>
            <h2 class="text-lg font-extrabold text-[#0F2347]">
                Détails du stand
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                Informations relatives à votre espace d'exposition.
            </p>
        </div>

        <span
            class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700"
        >
            <x-admin.icon name="storefront" class="size-4" />
            Stand exposant
        </span>

    </div>

    {{-- INFORMATIONS RÉELLES DU STAND --}}

    <div class="mx-6 mt-5 grid grid-cols-2 gap-4 rounded-xl bg-blue-50/60 p-5">

        <div>
            <p class="text-xs font-medium text-slate-500">
                Numéro du stand
            </p>

            <p class="mt-2 text-2xl font-extrabold text-blue-700 sm:text-3xl">
                {{ $exhibitor->stand_number ?: '—' }}
            </p>
        </div>

        <div class="border-l border-blue-200 pl-5">
            <p class="text-xs font-medium text-slate-500">
                Superficie
            </p>

            <p class="mt-2 text-xl font-extrabold text-[#0F2347] sm:text-2xl">
                @if ($exhibitor->area_sqm !== null)
                    {{ number_format(
                        (float) $exhibitor->area_sqm,
                        2,
                        ',',
                        ' '
                    ) }} m²
                @else
                    —
                @endif
            </p>
        </div>

    </div>

    {{-- IMAGE ILLUSTRATIVE --}}

    <figure class="mx-6 mt-5 overflow-hidden rounded-xl border border-slate-200 bg-gradient-to-br from-blue-50 via-white to-slate-50">

        <div class="relative">

            <img
                src="{{ asset('assets/conferences/images/Stand d’exposition bleu et blanc moderne.png') }}"
                alt="Exemple illustratif d'un stand modulaire bleu et blanc"
                class="mx-auto h-56 w-full object-contain p-3 sm:h-64"
                loading="lazy"
                decoding="async"
            >

            <span
                class="absolute left-3 top-3 rounded-lg border border-blue-100 bg-white/95 px-3 py-1.5 text-[11px] font-bold text-blue-700 shadow-sm"
            >
                MODÈLE ILLUSTRATIF
            </span>

        </div>

        <figcaption class="border-t border-slate-200 bg-white px-4 py-3">

            <p class="text-xs font-bold text-[#0F2347]">
                Exemple de stand modulaire
            </p>

            <p class="mt-1 text-xs leading-5 text-slate-600">
                Image présentée uniquement à titre illustratif.
                Elle ne représente pas votre stand réel.
                Le design, les dimensions et les équipements
                peuvent être différents.
            </p>

        </figcaption>

    </figure>

    {{-- MESSAGE INFORMATIF --}}

    <div class="mx-6 mb-6 mt-5 flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50/60 p-4">

        <x-admin.icon
            name="info"
            class="mt-0.5 size-5 shrink-0 text-blue-600"
        />

        <p class="text-xs leading-5 text-slate-600">
            Les informations de votre stand sont définies
            par l'organisation du salon. Pour toute question
            sur votre emplacement ou votre configuration,
            veuillez contacter votre commercial référent.
        </p>

    </div>

</section>


            <!-- COMMERCIAL -->
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-extrabold text-[#0F2347]">
                    Commercial référent
                </h2>

                @if ($commercial)

                    <div class="mt-5 flex flex-wrap items-center gap-4">

                        @if ($commercial->profile_photo_url)
                            <img src="{{ $commercial->profile_photo_url }}"
                                 alt="Photo de {{ $commercial->name }}"
                                 class="size-16 rounded-full object-cover">
                        @else
                            <span class="grid size-16 shrink-0 place-items-center rounded-full bg-blue-100 text-xl font-bold text-blue-700">
                                {{ $commercial->initials }}
                            </span>
                        @endif

                        <div class="min-w-0 flex-1">
                            <p class="font-extrabold text-[#0F2347]">
                                {{ $commercial->name }}
                            </p>

                            <p class="mt-1 text-sm text-slate-500">
                                Commercial — Refrigair Expo
                            </p>
                        </div>

                    </div>

                    <div class="mt-5 flex flex-wrap gap-3">

                        @if ($commercial->phone)
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $commercial->phone) }}"
                               class="rounded-xl border border-blue-200 px-5 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                                Appeler
                            </a>
                        @endif

                        @if ($commercial->email)
                            <a href="mailto:{{ $commercial->email }}"
                               class="rounded-xl border border-blue-200 px-5 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                                Email
                            </a>
                        @endif

                    </div>

                @else

                    <p class="mt-5 text-sm text-slate-500">
                        Aucun commercial référent attribué.
                    </p>

                @endif

            </section>

        </div>

    </div>

    <!-- BOTTOM CARDS -->
    <div class="grid gap-5 xl:grid-cols-2">

        <!-- BADGES -->
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-extrabold text-[#0F2347]">
                    Quota de badges
                </h2>

                <a href="{{ route('exhibitor.badges.index') }}"
                   class="text-sm font-semibold text-blue-700 hover:underline">
                    Gérer mes badges
                </a>
            </div>

            <div class="mt-6 flex flex-col items-center gap-6 sm:flex-row">

                <div class="relative grid size-40 shrink-0 place-items-center rounded-full"
                     style="background: conic-gradient(#2563EB {{ $progress }}%, #E2E8F0 {{ $progress }}% 100%);">

                    <div class="grid size-32 place-items-center rounded-full bg-white">
                        <span class="text-3xl font-extrabold text-[#0F2347]">
                            {{ $progress }}%
                        </span>
                    </div>

                </div>

                <div class="w-full flex-1 space-y-3">

                    <div class="flex justify-between rounded-xl border border-slate-100 p-4">
                        <span class="text-sm text-slate-600">
                            Badges utilisés
                        </span>
                        <strong class="text-[#0F2347]">
                            {{ $registered }}
                        </strong>
                    </div>

                    <div class="flex justify-between rounded-xl border border-slate-100 p-4">
                        <span class="text-sm text-slate-600">
                            Badges autorisés
                        </span>
                        <strong class="text-[#0F2347]">
                            {{ $authorized }}
                        </strong>
                    </div>

                    <div class="flex justify-between rounded-xl bg-emerald-50 p-4">
                        <span class="text-sm font-semibold text-emerald-700">
                            Badges restants
                        </span>
                        <strong class="text-emerald-700">
                            {{ $remaining }}
                        </strong>
                    </div>

                </div>

            </div>

        </section>

        <!-- PARTICIPATION -->
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-extrabold text-[#0F2347]">
                Informations du salon
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                Informations générales du prochain salon programmé.
            </p>

            <div class="mt-6 grid gap-3 sm:grid-cols-2">

                <div class="rounded-xl bg-blue-50 p-5">
                    <p class="text-sm font-semibold text-blue-700">
                        Dates du salon
                    </p>

                    <p class="mt-3 font-bold text-[#0F2347]">
                        @if ($salon)
                            {{ $formatDate($salon->start_date) }}
                            —
                            {{ $formatDate($salon->end_date) }}
                        @else
                            À confirmer
                        @endif
                    </p>
                </div>

                <div class="rounded-xl bg-violet-50 p-5">
                    <p class="text-sm font-semibold text-violet-700">
                        Lieu
                    </p>

                    <p class="mt-3 font-bold text-[#0F2347]">
                        @if ($salon)
                            {{ collect([$salon->venue, $salon->city])->filter()->implode(' · ') ?: 'À confirmer' }}
                        @else
                            À confirmer
                        @endif
                    </p>
                </div>

            </div>

            <div class="mt-4 rounded-xl border border-blue-100 bg-[#F5F7FB] p-4">
                <p class="text-sm text-slate-600">
                    Votre numéro de stand :
                    <strong class="text-[#0F2347]">
                        {{ $exhibitor->stand_number ?: 'Non attribué' }}
                    </strong>
                </p>
            </div>

            <p class="mt-3 text-xs text-slate-500">
                Les données du salon sont affichées à titre informatif.
            </p>

        </section>

    </div>

</div>



</x-exhibitor.layout>
