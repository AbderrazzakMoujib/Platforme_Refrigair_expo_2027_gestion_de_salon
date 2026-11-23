
<x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">

    @php
        $statusClasses = match ($ticket->status) {
            'resolved' => 'bg-emerald-100 text-emerald-700',
            'in_progress' => 'bg-blue-100 text-blue-700',
            default => 'bg-amber-100 text-amber-700',
        };

        $categories = [
            'badges' => 'Badges',
            'stand' => 'Stand',
            'equipment' => 'Équipements',
            'account' => 'Mon compte',
            'other' => 'Autre',
        ];

        $commercial = $ticket->commercial;

        $messages = $ticket->messages->sortBy('id');

        $lastMessage = $messages->last();

        $exhibitorLogo = $exhibitor->logo
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($exhibitor->logo)
            : null;

        $exhibitorInitials = mb_strtoupper(
            mb_substr(trim($exhibitor->company_name ?? ''), 0, 2)
        ) ?: 'EX';
    @endphp

    <div class="min-w-0 space-y-6">

        {{-- BREADCRUMB --}}

        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500"
             aria-label="Fil d'Ariane">

            <a href="{{ route('exposant.index') }}"
               class="transition hover:text-blue-600">
                Vue globale
            </a>

            <span>/</span>

            <a href="{{ route('exhibitor.support.index') }}"
               class="transition hover:text-blue-600">
                Assistance
            </a>

            <span>/</span>

            <span class="font-semibold text-[#0F2347]">
                Demande #{{ $ticket->id }}
            </span>

        </nav>


        {{-- PAGE HEADER --}}

        <header class="flex flex-wrap items-center justify-between gap-4">

            <div class="flex items-center gap-4">

                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-[#2563EB]">

                    <svg class="size-8"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         stroke-linecap="round"
                         stroke-linejoin="round"
                         aria-hidden="true">

                        <path d="M3 14v-3a9 9 0 0 1 18 0v3"/>
                        <rect x="3" y="12" width="5" height="8" rx="2"/>
                        <rect x="16" y="12" width="5" height="8" rx="2"/>
                        <path d="M21 18v1a3 3 0 0 1-3 3h-4"/>

                    </svg>

                </span>

                <div>

                    <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                        Demande #{{ $ticket->id }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Échangez avec votre commercial concernant votre demande.
                    </p>

                </div>

            </div>

            <a href="{{ route('exhibitor.support.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">

                <svg class="size-5"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round"
                     aria-hidden="true">

                    <path d="m12 19-7-7 7-7M5 12h14"/>

                </svg>

                Retour

            </a>

        </header>


        {{-- SUCCESS MESSAGE --}}

        @if (session('status'))

            <div role="status"
                 class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">

                {{ session('status') }}

            </div>

        @endif


        {{-- VALIDATION ERRORS --}}

        @if ($errors->any())

            <div role="alert"
                 class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">

                @foreach ($errors->all() as $error)

                    <p>{{ $error }}</p>

                @endforeach

            </div>

        @endif


        {{-- MAIN GRID --}}

        <div class="grid min-w-0 items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">


            {{-- CONVERSATION --}}

            <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">


                {{-- CONVERSATION HEADER --}}

                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 px-5 py-5 sm:px-6">

                    <div class="flex min-w-0 items-center gap-3">

                        {{-- COMMERCIAL PHOTO --}}

                        <div class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-full border border-blue-100 bg-blue-50 font-bold text-blue-700">

                            @if ($commercial?->profile_photo_url)

                                <img
                                    src="{{ $commercial->profile_photo_url }}"
                                    alt="{{ $commercial->name }}"
                                    class="h-full w-full object-cover"
                                    data-support-avatar
                                >

                                <span class="hidden"
                                      data-avatar-fallback>

                                    {{ $commercial->initials }}

                                </span>

                            @else

                                {{ $commercial?->initials ?? 'C' }}

                            @endif

                        </div>

                        <div class="min-w-0">

                            <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-400">
                                Objet de la demande
                            </p>

                            <h2 class="break-words text-lg font-extrabold text-[#0F2347]">

                                {{ $ticket->subject }}

                            </h2>

                            @if ($commercial)

                                <p class="mt-1 text-xs text-slate-500">

                                    {{ $commercial->name }} — Commercial

                                </p>

                            @endif

                        </div>

                    </div>

                    <span class="inline-flex shrink-0 items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold {{ $statusClasses }}">

                        <span class="size-2 rounded-full bg-current"></span>

                        {{ $ticket->status_label }}

                    </span>

                </div>


                {{-- CONVERSATION MESSAGES --}}

                <div
                    id="support-conversation"
                    role="log"
                    aria-label="Conversation"
                    class="min-h-[380px] max-h-[570px] space-y-6 overflow-y-auto bg-slate-50/40 px-4 py-6 sm:px-6"
                >

                    @forelse ($messages as $message)

                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | Identification de l'expéditeur réel
                            |--------------------------------------------------------------------------
                            */

                            $sender = $message->user;

                            // Position du message uniquement.
                            $isMine = (int) $message->user_id
                                === (int) $user->id;

                            // Vérifier si l'expéditeur est l'exposant.
                            $isExhibitor = $exhibitor->user_id !== null
                                && (int) $message->user_id
                                    === (int) $exhibitor->user_id;

                            // Récupérer l'image de l'expéditeur réel.
                            $avatarUrl = $isExhibitor
                                ? $exhibitorLogo
                                : $sender?->profile_photo_url;

                            // Nom réel de l'expéditeur.
                            $avatarName = $isExhibitor
                                ? $exhibitor->company_name
                                : ($sender?->name ?? 'Utilisateur');

                            // Initiales si aucune image.
                            $avatarInitials = $isExhibitor
                                ? $exhibitorInitials
                                : ($sender?->initials ?? '?');

                            $senderRole = $isExhibitor
                                ? 'Exposant'
                                : 'Commercial';

                        @endphp


                        {{-- MESSAGE ROW --}}

                        <article @class([
                            'flex items-start gap-3',
                            'flex-row-reverse' => $isMine,
                        ])>


                            {{-- SENDER AVATAR --}}

                            <div @class([
                                'grid size-11 shrink-0 place-items-center overflow-hidden rounded-full border font-bold shadow-sm sm:size-12',
                                'border-blue-100 bg-white text-blue-700' => $isExhibitor,
                                'border-slate-200 bg-blue-100 text-blue-700' => ! $isExhibitor,
                            ])>

                                @if ($avatarUrl)

                                    <img
                                        src="{{ $avatarUrl }}"
                                        alt="{{ $avatarName }}"
                                        title="{{ $avatarName }}"
                                        data-support-avatar
                                        @class([
                                            'h-full w-full',
                                            'object-contain p-1' => $isExhibitor,
                                            'object-cover' => ! $isExhibitor,
                                        ])
                                    >

                                    {{-- FALLBACK IMAGE --}}

                                    <span
                                        class="hidden text-sm font-bold text-blue-700"
                                        data-avatar-fallback
                                    >

                                        {{ $avatarInitials }}

                                    </span>

                                @else

                                    <span class="text-sm font-bold text-blue-700"
                                          title="{{ $avatarName }}">

                                        {{ $avatarInitials }}

                                    </span>

                                @endif

                            </div>


                            {{-- MESSAGE CONTENT --}}

                            <div @class([
                                'flex min-w-0 max-w-[82%] flex-col sm:max-w-[78%]',
                                'items-end' => $isMine,
                                'items-start' => ! $isMine,
                            ])>


                                {{-- SENDER NAME --}}

                                <div @class([
                                    'mb-2 flex flex-wrap items-center gap-2',
                                    'justify-end' => $isMine,
                                ])>

                                    <span class="break-words text-xs font-bold text-[#0F2347]">

                                        {{ $avatarName }}

                                        @if ($isMine)

                                            <span class="font-normal text-slate-400">
                                                (Vous)
                                            </span>

                                        @endif

                                    </span>

                                    <span @class([
                                        'rounded-md px-2 py-0.5 text-[10px] font-semibold',
                                        'bg-violet-50 text-violet-600' => $isExhibitor,
                                        'bg-blue-50 text-blue-600' => ! $isExhibitor,
                                    ])>

                                        {{ $senderRole }}

                                    </span>

                                </div>


                                {{-- MESSAGE BUBBLE --}}

                                <div @class([
                                    'max-w-full break-words whitespace-pre-wrap rounded-2xl px-4 py-3 text-sm leading-7 sm:px-5',
                                    'rounded-tr-sm bg-[#2563EB] text-white shadow-sm' => $isMine,
                                    'rounded-tl-sm border border-slate-200 bg-white text-slate-700 shadow-sm' => ! $isMine,
                                ])>{{ $message->message }}</div>


                                {{-- MESSAGE DATE --}}

                                <p @class([
                                    'mt-2 text-[11px] text-slate-400',
                                    'text-right' => $isMine,
                                ])>

                                    {{ $message->created_at?->format('d/m/Y à H:i') }}

                                </p>

                            </div>

                        </article>

                    @empty


                        {{-- EMPTY CONVERSATION --}}

                        <div class="grid min-h-[300px] place-items-center text-center">

                            <div>

                                <span class="mx-auto grid size-16 place-items-center rounded-full bg-blue-50 text-blue-600">

                                    <svg class="size-8"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8"
                                         stroke-linecap="round"
                                         stroke-linejoin="round"
                                         aria-hidden="true">

                                        <path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5Z"/>

                                    </svg>

                                </span>

                                <p class="mt-4 font-bold text-[#0F2347]">
                                    Aucun message
                                </p>

                                <p class="mt-2 text-sm text-slate-500">
                                    Votre conversation apparaîtra ici.
                                </p>

                            </div>

                        </div>

                    @endforelse

                </div>


                {{-- REPLY AREA --}}

                @if ($ticket->status !== 'resolved')

                    <form
                        method="POST"
                        action="{{ route('exhibitor.support.reply', $ticket) }}"
                        class="border-t border-slate-200 bg-white p-4 sm:p-6"
                        id="support-reply-form"
                    >

                        @csrf


                        {{-- CONNECTED EXHIBITOR --}}

                        <div class="mb-4 flex items-center gap-3">

                            <div class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-full border border-blue-100 bg-white font-bold text-blue-600">

                                @if ($exhibitorLogo)

                                    <img
                                        src="{{ $exhibitorLogo }}"
                                        alt="Logo {{ $exhibitor->company_name }}"
                                        class="h-full w-full object-contain p-1"
                                        data-support-avatar
                                    >

                                    <span class="hidden"
                                          data-avatar-fallback>

                                        {{ $exhibitorInitials }}

                                    </span>

                                @else

                                    {{ $exhibitorInitials }}

                                @endif

                            </div>

                            <div class="min-w-0">

                                <p class="break-words text-sm font-bold text-[#0F2347]">

                                    {{ $exhibitor->company_name }}

                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Vous — Exposant
                                </p>

                            </div>

                        </div>


                        <label for="support-message"
                               class="mb-2 block text-sm font-bold text-[#0F2347]">

                            Votre message

                        </label>

                        <textarea
                            id="support-message"
                            name="message"
                            rows="3"
                            maxlength="5000"
                            required
                            placeholder="Écrivez votre message ici..."
                            class="w-full resize-y rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                        >{{ old('message') }}</textarea>

                        @error('message')

                            <p class="mt-2 text-xs font-semibold text-red-600">

                                {{ $message }}

                            </p>

                        @enderror


                        {{-- SEND BUTTON --}}

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">

                            <p class="text-xs text-slate-400">

                                <span id="support-character-count">0</span>

                                / 5000 caractères

                            </p>

                            <button
                                id="support-send-button"
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >

                                <svg class="size-5"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8"
                                     stroke-linecap="round"
                                     stroke-linejoin="round"
                                     aria-hidden="true">

                                    <path d="m22 2-7 20-4-9-9-4Z"/>
                                    <path d="M22 2 11 13"/>

                                </svg>

                                <span id="support-send-label">
                                    Envoyer
                                </span>

                            </button>

                        </div>

                    </form>

                @else


                    {{-- RESOLVED TICKET --}}

                    <div class="border-t border-emerald-100 bg-emerald-50 p-6">

                        <div class="flex items-start gap-3">

                            <span class="grid size-10 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-700">

                                <svg class="size-6"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="2"
                                     stroke-linecap="round"
                                     stroke-linejoin="round"
                                     aria-hidden="true">

                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="m8 12 3 3 5-6"/>

                                </svg>

                            </span>

                            <div>

                                <h3 class="font-bold text-emerald-800">
                                    Cette demande est résolue.
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-emerald-700">

                                    Vous pouvez consulter votre conversation
                                    ou créer une nouvelle demande d'assistance.

                                </p>

                                <a href="{{ route('exhibitor.support.index') }}"
                                   class="mt-3 inline-block text-sm font-bold text-emerald-800 underline">

                                    Nouvelle demande

                                </a>

                            </div>

                        </div>

                    </div>

                @endif

            </section>


            {{-- RIGHT SIDEBAR --}}

            <aside class="min-w-0 space-y-5">


                {{-- TICKET DETAILS --}}

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex items-center gap-3">

                        <span class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-600">

                            <svg class="size-5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 stroke-linecap="round"
                                 aria-hidden="true">

                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 11v5M12 8h.01"/>

                            </svg>

                        </span>

                        <h2 class="font-extrabold text-[#0F2347]">
                            Détails de la demande
                        </h2>

                    </div>

                    <dl class="mt-5 divide-y divide-slate-100 text-sm">

                        <div class="flex items-center justify-between gap-3 py-4">

                            <dt class="text-slate-500">
                                N° de demande
                            </dt>

                            <dd class="font-bold text-[#0F2347]">
                                #{{ $ticket->id }}
                            </dd>

                        </div>

                        <div class="flex items-center justify-between gap-3 py-4">

                            <dt class="text-slate-500">
                                Statut
                            </dt>

                            <dd class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses }}">

                                {{ $ticket->status_label }}

                            </dd>

                        </div>

                        <div class="flex items-center justify-between gap-3 py-4">

                            <dt class="text-slate-500">
                                Catégorie
                            </dt>

                            <dd class="font-semibold text-[#0F2347]">

                                {{ $categories[$ticket->category] ?? 'Autre' }}

                            </dd>

                        </div>

                        <div class="flex items-start justify-between gap-3 py-4">

                            <dt class="text-slate-500">
                                Créée le
                            </dt>

                            <dd class="text-right font-semibold text-[#0F2347]">

                                {{ $ticket->created_at?->format('d/m/Y') }}

                                <span class="mt-1 block text-xs font-normal text-slate-400">

                                    {{ $ticket->created_at?->format('H:i') }}

                                </span>

                            </dd>

                        </div>

                        <div class="flex items-start justify-between gap-3 py-4">

                            <dt class="text-slate-500">
                                Dernier message
                            </dt>

                            <dd class="text-right font-semibold text-[#0F2347]">

                                {{ $lastMessage?->created_at?->format('d/m/Y') ?? '—' }}

                                <span class="mt-1 block text-xs font-normal text-slate-400">

                                    {{ $lastMessage?->created_at?->format('H:i') }}

                                </span>

                            </dd>

                        </div>

                        <div class="flex items-center justify-between gap-3 pt-4">

                            <dt class="text-slate-500">
                                Messages
                            </dt>

                            <dd class="font-bold text-[#0F2347]">{{ $messages->count() }}</dd>

                        </div>

                    </dl>

                </section>


                {{-- COMMERCIAL CARD --}}

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h2 class="font-extrabold text-[#0F2347]">
                        Votre commercial
                    </h2>

                    @if ($commercial)

                        <div class="mt-5 flex items-center gap-3">

                            <div class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-full border border-blue-100 bg-blue-50 font-bold text-blue-700">

                                @if ($commercial->profile_photo_url)

                                    <img
                                        src="{{ $commercial->profile_photo_url }}"
                                        alt="{{ $commercial->name }}"
                                        class="h-full w-full object-cover"
                                        data-support-avatar
                                    >

                                    <span class="hidden"
                                          data-avatar-fallback>

                                        {{ $commercial->initials }}

                                    </span>

                                @else

                                    {{ $commercial->initials }}

                                @endif

                            </div>

                            <div class="min-w-0">

                                <p class="break-words text-sm font-bold text-[#0F2347]">

                                    {{ $commercial->name }}

                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Commercial référent
                                </p>

                            </div>

                        </div>

                        <div class="mt-5 space-y-3">

                            @if ($commercial->email)

                                <a href="mailto:{{ $commercial->email }}"
                                   class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 text-xs text-slate-600 transition hover:border-blue-200 hover:bg-blue-50">

                                    <svg class="size-5 shrink-0 text-blue-600"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8"
                                         aria-hidden="true">

                                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                                        <path d="m3 7 9 6 9-6"/>

                                    </svg>

                                    <span class="min-w-0 break-all">

                                        {{ $commercial->email }}

                                    </span>

                                </a>

                            @endif

                            @if ($commercial->phone)

                                <a href="tel:{{ $commercial->phone }}"
                                   class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 text-xs text-slate-600 transition hover:border-blue-200 hover:bg-blue-50">

                                    <svg class="size-5 shrink-0 text-blue-600"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8"
                                         stroke-linecap="round"
                                         stroke-linejoin="round"
                                         aria-hidden="true">

                                        <path d="M22 16.9v3a2 2 0 0 1-2.2 2 18 18 0 0 1-8-2.8 18 18 0 0 1-5.5-5.5 18 18 0 0 1-2.8-8A2 2 0 0 1 5.7 3h3a2 2 0 0 1 2 1.7l.5 3a2 2 0 0 1-.6 2.1L9.3 11a14 14 0 0 0 4 4l1.3-1.3a2 2 0 0 1 2.1-.6l3 .5a2 2 0 0 1 2.3 3.3Z"/>

                                    </svg>

                                    {{ $commercial->phone }}

                                </a>

                            @endif

                        </div>

                    @else

                        <p class="mt-4 text-sm text-slate-500">
                            Aucun commercial attribué.
                        </p>

                    @endif

                </section>


                {{-- EXHIBITOR CARD --}}

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h2 class="font-extrabold text-[#0F2347]">
                        Votre entreprise
                    </h2>

                    <div class="mt-5 flex items-center gap-3">

                        <div class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white font-bold text-blue-600">

                            @if ($exhibitorLogo)

                                <img
                                    src="{{ $exhibitorLogo }}"
                                    alt="Logo {{ $exhibitor->company_name }}"
                                    class="h-full w-full object-contain p-2"
                                    data-support-avatar
                                >

                                <span class="hidden"
                                      data-avatar-fallback>

                                    {{ $exhibitorInitials }}

                                </span>

                            @else

                                <span class="text-xl">

                                    {{ $exhibitorInitials }}

                                </span>

                            @endif

                        </div>

                        <div class="min-w-0">

                            <p class="break-words text-sm font-bold text-[#0F2347]">

                                {{ $exhibitor->company_name }}

                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                Entreprise exposante
                            </p>

                            <span class="mt-2 inline-flex rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">

                                Stand {{ $exhibitor->stand_number ?: '—' }}

                            </span>

                        </div>

                    </div>

                </section>


                {{-- INFORMATION CARD --}}

                <section class="rounded-2xl border border-blue-100 bg-blue-50 p-5">

                    <div class="flex items-start gap-3">

                        <svg class="mt-0.5 size-6 shrink-0 text-blue-600"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             aria-hidden="true">

                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 11v5M12 8h.01"/>

                        </svg>

                        <div>

                            <h3 class="font-bold text-[#0F2347]">
                                Bon à savoir
                            </h3>

                            <p class="mt-2 text-xs leading-6 text-slate-600">

                                Vous recevrez une notification dès que
                                votre commercial répondra à votre demande.

                            </p>

                        </div>

                    </div>

                </section>

            </aside>

        </div>

    </div>


    {{-- CONVERSATION JAVASCRIPT --}}

    <script>
        (() => {

            /*
            |--------------------------------------------------------------------------
            | Gestion des images indisponibles
            |--------------------------------------------------------------------------
            */

            document.querySelectorAll('[data-support-avatar]')
                .forEach(image => {

                    const showFallback = () => {

                        image.classList.add('hidden');

                        const fallback = image.parentElement
                            .querySelector('[data-avatar-fallback]');

                        if (fallback) {
                            fallback.classList.remove('hidden');
                        }

                    };

                    image.addEventListener('error', showFallback);

                    if (
                        image.complete &&
                        image.naturalWidth === 0
                    ) {
                        showFallback();
                    }

                });


            /*
            |--------------------------------------------------------------------------
            | Conversation elements
            |--------------------------------------------------------------------------
            */

            const conversation = document.getElementById(
                'support-conversation'
            );

            const textarea = document.getElementById(
                'support-message'
            );

            const counter = document.getElementById(
                'support-character-count'
            );

            const button = document.getElementById(
                'support-send-button'
            );

            const label = document.getElementById(
                'support-send-label'
            );

            const form = document.getElementById(
                'support-reply-form'
            );


            /*
            |--------------------------------------------------------------------------
            | Scroll automatique
            |--------------------------------------------------------------------------
            */

            function scrollToBottom() {

                if (conversation) {

                    conversation.scrollTop =
                        conversation.scrollHeight;

                }

            }

            scrollToBottom();

            window.addEventListener(
                'load',
                scrollToBottom,
                { once: true }
            );


            /*
            |--------------------------------------------------------------------------
            | Vérification du formulaire
            |--------------------------------------------------------------------------
            */

            if (
                !textarea ||
                !counter ||
                !button ||
                !form ||
                !label
            ) {
                return;
            }

            let isSending = false;


            /*
            |--------------------------------------------------------------------------
            | Compteur de caractères
            |--------------------------------------------------------------------------
            */

            function updateComposer() {

                const length = textarea.value.length;

                counter.textContent = length;

                counter.classList.toggle(
                    'text-red-600',
                    length >= 5000
                );

                button.disabled =
                    isSending ||
                    textarea.value.trim().length === 0 ||
                    length > 5000;

            }

            textarea.addEventListener(
                'input',
                updateComposer
            );


            /*
            |--------------------------------------------------------------------------
            | Envoi du message
            |--------------------------------------------------------------------------
            */

            form.addEventListener('submit', event => {

                if (
                    isSending ||
                    !textarea.value.trim() ||
                    textarea.value.length > 5000
                ) {

                    event.preventDefault();

                    return;

                }

                if (event.defaultPrevented) {
                    return;
                }

                isSending = true;

                button.disabled = true;

                label.textContent = 'Envoi en cours...';

                form.setAttribute('aria-busy', 'true');

            });


            /*
            |--------------------------------------------------------------------------
            | Réactivation après navigation
            |--------------------------------------------------------------------------
            */

            window.addEventListener('pageshow', () => {

                isSending = false;

                label.textContent = 'Envoyer';

                form.removeAttribute('aria-busy');

                updateComposer();

            });

            updateComposer();

        })();
    </script>

</x-exhibitor.layout>