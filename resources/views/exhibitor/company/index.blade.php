
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

        <!-- SALON -->
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 to-white px-5 py-4 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-700">
                    <svg class="size-6" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor"
                         stroke-width="1.8" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M7 3v4M17 3v4M3 10h18"/>
                    </svg>
                </span>

                <div>
                    <h2 class="font-bold text-[#0F2347]">
                        {{ $salon?->name ?? 'Refrigair Expo 2027' }}
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        @if ($salon)
                            {{ $formatDate($salon->start_date) }}
                            —
                            {{ $formatDate($salon->end_date) }}
                            @if ($salon->venue)
                                · {{ $salon->venue }}
                            @endif
                            @if ($salon->city)
                                · {{ $salon->city }}
                            @endif
                        @else
                            Dates et lieu à confirmer
                        @endif
                    </p>
                </div>
            </div>
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

                <button type="button"
                        id="edit-company-button"
                        aria-controls="company-edit-form"
                        aria-expanded="{{ $errors->any() ? 'true' : 'false' }}"
                        class="rounded-xl border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50">
                    Modifier
                </button>

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

                <!-- EDIT CONTACT FORM -->
                <form id="company-edit-form"
                      method="POST"
                      action="{{ route('exhibitor.company.update') }}"
                      class="{{ $errors->any() ? '' : 'hidden' }} mt-6 space-y-4 rounded-xl border border-blue-100 bg-blue-50/40 p-5">

                    @csrf
                    @method('PUT')

                    <div>
                        <h3 class="font-bold text-[#0F2347]">
                            Modifier les coordonnées
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Le nom de l'entreprise et les détails
                            du stand sont gérés par l'organisation.
                        </p>
                    </div>

                    <div>
                        <label for="contact_name"
                               class="block text-sm font-semibold text-slate-700">
                            Responsable *
                        </label>

                        <input id="contact_name"
                               name="contact_name"
                               type="text"
                               required
                               maxlength="255"
                               value="{{ old('contact_name', $exhibitor->contact_name) }}"
                               class="mt-2 block w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">

                        <div>
                            <label for="email"
                                   class="block text-sm font-semibold text-slate-700">
                                Email *
                            </label>

                            <input id="email"
                                   name="email"
                                   type="email"
                                   required
                                   maxlength="255"
                                   value="{{ old('email', $exhibitor->email) }}"
                                   class="mt-2 block w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="phone"
                                   class="block text-sm font-semibold text-slate-700">
                                Téléphone
                            </label>

                            <input id="phone"
                                   name="phone"
                                   type="tel"
                                   maxlength="40"
                                   value="{{ old('phone', $exhibitor->phone) }}"
                                   class="mt-2 block w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button"
                                id="cancel-company-edit"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Annuler
                        </button>

                        <button type="submit"
                                class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                            Enregistrer
                        </button>
                    </div>

                </form>

            </div>

        </section>

        <!-- RIGHT COLUMN -->
        <div class="space-y-5">

            <!-- DETAILS STAND -->
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-extrabold text-[#0F2347]">
                    Détails du stand
                </h2>

                <div class="mt-5 grid grid-cols-2 gap-4 rounded-xl bg-blue-50/60 p-5">

                    <div>
                        <p class="text-sm text-slate-500">
                            Numéro du stand
                        </p>

                        <p class="mt-2 text-3xl font-extrabold text-blue-700">
                            {{ $exhibitor->stand_number ?: '—' }}
                        </p>
                    </div>

                    <div class="border-l border-blue-200 pl-5">
                        <p class="text-sm text-slate-500">
                            Superficie
                        </p>

                        <p class="mt-2 text-2xl font-extrabold text-[#0F2347]">
                            @if ($exhibitor->area_sqm !== null)
                                {{ number_format((float) $exhibitor->area_sqm, 2, ',', ' ') }} m²
                            @else
                                —
                            @endif
                        </p>
                    </div>

                </div>

                <p class="mt-5 text-sm leading-6 text-slate-500">
                    Les informations de votre stand sont définies
                    par l'organisation du salon.
                </p>

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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('edit-company-button');
    const form = document.getElementById('company-edit-form');
    const cancel = document.getElementById('cancel-company-edit');

    if (!button || !form || !cancel) return;

    button.addEventListener('click', () => {
        const isHidden = form.classList.toggle('hidden');

        button.setAttribute(
            'aria-expanded',
            String(!isHidden)
        );

        if (!isHidden) {
            form.querySelector('input')?.focus();
        }
    });

    cancel.addEventListener('click', () => {
        form.classList.add('hidden');
        form.reset();
        button.setAttribute('aria-expanded', 'false');
    });
});
</script>

</x-exhibitor.layout>
