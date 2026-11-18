
<x-commercial.layout
    :commercial="$commercial"
    :commercial-initials="$commercialInitials">

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
            'account' => 'Compte exposant',
            'other' => 'Autre',
        ];

        $exhibitor = $ticket->exhibitor;

        $messages = $ticket->messages->sortBy('id');

        $lastMessage = $messages->last();

        $companyInitials = collect(
            preg_split('/\s+/', trim($exhibitor?->company_name ?? 'Exposant'))
        )
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: 'E';
    @endphp

    <div class="space-y-6">

        <!-- BREADCRUMB -->
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">

            <a href="{{ route('commercial.index') }}"
               class="transition hover:text-blue-600">
                Vue globale
            </a>

            <span>/</span>

            <a href="{{ route('commercial.support.index') }}"
               class="transition hover:text-blue-600">
                Assistance exposants
            </a>

            <span>/</span>

            <span class="font-semibold text-[#0F2347]">
                Demande #{{ $ticket->id }}
            </span>

        </nav>

        <!-- PAGE HEADER -->
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
                        Consultez et traitez la demande de votre exposant.
                    </p>

                </div>

            </div>

            <a href="{{ route('commercial.support.index') }}"
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

        <!-- SUCCESS MESSAGE -->
        @if (session('status'))

            <div role="status"
                 class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">

                <svg class="size-5 shrink-0"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     aria-hidden="true">

                    <circle cx="12" cy="12" r="9"/>
                    <path d="m8 12 3 3 5-6"/>

                </svg>

                {{ session('status') }}

            </div>

        @endif

        <!-- VALIDATION ERRORS -->
        @if ($errors->any())

            <div role="alert"
                 class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">

                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach

            </div>

        @endif

        <!-- MAIN GRID -->
        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">

            <!-- CONVERSATION -->
            <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <!-- CONVERSATION HEADER -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-5 sm:px-6">

                    <div class="min-w-0">

                        <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Objet de la demande
                        </p>

                        <h2 class="break-words text-lg font-extrabold text-[#0F2347]">
                            {{ $ticket->subject }}
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ $exhibitor?->company_name ?? 'Exposant' }}
                        </p>

                    </div>

                    <span class="inline-flex shrink-0 items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold {{ $statusClasses }}">

                        <span class="size-2 rounded-full bg-current"></span>

                        {{ $ticket->status_label }}

                    </span>

                </div>

                <!-- MESSAGES -->
                <div id="support-conversation"
                     role="log"
                     aria-label="Conversation"
                     class="min-h-[380px] max-h-[570px] space-y-6 overflow-y-auto bg-slate-50/40 px-4 py-6 sm:px-6">

                    @forelse ($messages as $message)

                        @php
                            $isMine = (int) $message->user_id
                                === (int) $commercial->id;

                            $sender = $message->user;

                            $initials = $sender?->initials ?? '?';
                        @endphp

                        <article @class([
                            'flex items-start gap-3',
                            'flex-row-reverse' => $isMine,
                        ])>

                            <!-- AVATAR -->
                            <div @class([
                                'grid size-10 shrink-0 place-items-center overflow-hidden rounded-full text-xs font-bold sm:size-11',
                                'bg-[#2563EB] text-white' => $isMine,
                                'bg-blue-100 text-blue-700' => !$isMine,
                            ])>

                                @if ($sender?->profile_photo_url)

                                    <img
                                        src="{{ $sender->profile_photo_url }}"
                                        alt="{{ $sender->name }}"
                                        class="h-full w-full object-cover"
                                    >

                                @else

                                    {{ $initials }}

                                @endif

                            </div>

                            <!-- MESSAGE BODY -->
                            <div class="min-w-0 max-w-[82%] sm:max-w-[78%]">

                                <!-- SENDER -->
                                <div @class([
                                    'mb-2 flex flex-wrap items-center gap-2',
                                    'justify-end' => $isMine,
                                ])>

                                    <span class="text-xs font-bold text-[#0F2347]">

                                        {{ $isMine
                                            ? 'Vous'
                                            : ($sender?->name ?? 'Exposant') }}

                                    </span>

                                    @unless ($isMine)

                                        <span class="rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-600">
                                            Participant
                                        </span>

                                    @endunless

                                </div>

                                <!-- BUBBLE -->
                                <div @class([
                                    'break-words whitespace-pre-wrap rounded-2xl px-4 py-3 text-sm leading-7 sm:px-5',
                                    'rounded-tr-sm bg-[#2563EB] text-white' => $isMine,
                                    'rounded-tl-sm border border-slate-200 bg-white text-slate-700 shadow-sm' => !$isMine,
                                ])>{{ $message->message }}</div>

                                <!-- MESSAGE DATE -->
                                <p @class([
                                    'mt-2 text-[11px] text-slate-400',
                                    'text-right' => $isMine,
                                ])>

                                    {{ $message->created_at?->format('d/m/Y à H:i') }}

                                </p>

                            </div>

                        </article>

                    @empty

                        <div class="grid min-h-[300px] place-items-center text-center">

                            <div>

                                <p class="font-bold text-[#0F2347]">
                                    Aucun message
                                </p>

                                <p class="mt-2 text-sm text-slate-500">
                                    Les messages apparaîtront ici.
                                </p>

                            </div>

                        </div>

                    @endforelse

                </div>

                <!-- REPLY FORM -->
                @if ($ticket->status !== 'resolved')

                    <form
                        method="POST"
                        action="{{ route('commercial.support.reply', $ticket) }}"
                        class="border-t border-slate-200 bg-white p-4 sm:p-6"
                        id="support-reply-form"
                    >

                        @csrf

                        <label for="support-message"
                               class="mb-2 block text-sm font-bold text-[#0F2347]">

                            Votre réponse

                        </label>

                        <textarea
                            id="support-message"
                            name="message"
                            rows="3"
                            maxlength="5000"
                            required
                            placeholder="Écrivez votre réponse à l'exposant..."
                            class="w-full resize-y rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                        >{{ old('message') }}</textarea>

                        @error('message')

                            <p class="mt-2 text-xs font-semibold text-red-600">
                                {{ $message }}
                            </p>

                        @enderror

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

                                <span>Envoyer la réponse</span>

                            </button>

                        </div>

                    </form>

                @else

                    <!-- RESOLVED MESSAGE -->
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

                                    Réouvrez cette demande si vous souhaitez
                                    poursuivre la conversation avec l'exposant.

                                </p>

                                <form method="POST"
                                      action="{{ route('commercial.support.status', $ticket) }}"
                                      class="mt-4">

                                    @csrf
                                    @method('PATCH')

                                    <input type="hidden"
                                           name="status"
                                           value="in_progress">

                                    <button type="submit"
                                            class="rounded-xl border border-emerald-300 bg-white px-4 py-2.5 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100">

                                        Réouvrir la demande

                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                @endif

            </section>

            <!-- RIGHT SIDEBAR -->
            <aside class="min-w-0 space-y-5">

                <!-- STATUS MANAGEMENT -->
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex items-center gap-3">

                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-600">

                            <svg class="size-5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 stroke-linecap="round"
                                 aria-hidden="true">

                                <path d="M4 7h16M4 17h16"/>
                                <circle cx="8" cy="7" r="2" fill="white"/>
                                <circle cx="16" cy="17" r="2" fill="white"/>

                            </svg>

                        </span>

                        <h2 class="font-extrabold text-[#0F2347]">
                            Gestion du statut
                        </h2>

                    </div>

                    <form method="POST"
                          action="{{ route('commercial.support.status', $ticket) }}"
                          class="mt-5 space-y-4">

                        @csrf
                        @method('PATCH')

                        <div>

                            <label for="ticket-status"
                                   class="mb-2 block text-sm font-semibold text-slate-600">

                                Statut de la demande

                            </label>

                            <select
                                id="ticket-status"
                                name="status"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-[#0F2347] focus:border-blue-500 focus:ring-blue-100"
                            >

                                <option value="pending"
                                    @selected(old('status', $ticket->status) === 'pending')>
                                    En attente
                                </option>

                                <option value="in_progress"
                                    @selected(old('status', $ticket->status) === 'in_progress')>
                                    En cours
                                </option>

                                <option value="resolved"
                                    @selected(old('status', $ticket->status) === 'resolved')>
                                    Résolue
                                </option>

                            </select>

                        </div>

                        <button type="submit"
                                class="w-full rounded-xl bg-[#2563EB] px-4 py-3 text-sm font-bold text-white transition hover:bg-blue-700">

                            Mettre à jour le statut

                        </button>

                    </form>

                    @if ($ticket->status !== 'resolved')

                        <form method="POST"
                              action="{{ route('commercial.support.status', $ticket) }}"
                              class="mt-3">

                            @csrf
                            @method('PATCH')

                            <input type="hidden"
                                   name="status"
                                   value="resolved">

                            <button type="submit"
                                    onclick="return confirm('Voulez-vous marquer cette demande comme résolue ?')"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100">

                                <svg class="size-5"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="2"
                                     stroke-linecap="round"
                                     aria-hidden="true">

                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="m8 12 3 3 5-6"/>

                                </svg>

                                Marquer comme résolue

                            </button>

                        </form>

                    @endif

                    <p class="mt-4 text-xs leading-5 text-slate-400">

                        L'exposant sera automatiquement notifié
                        en cas de modification du statut.

                    </p>

                </section>

                <!-- EXHIBITOR INFORMATION -->
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h2 class="font-extrabold text-[#0F2347]">
                        Informations exposant
                    </h2>

                    @if ($exhibitor)

                        <div class="mt-5 flex items-center gap-3">

                            <div class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-xl bg-blue-100 text-lg font-bold text-blue-700">

                                @if ($exhibitor->logo)

                                    <img
                                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($exhibitor->logo) }}"
                                        alt="{{ $exhibitor->company_name }}"
                                        class="h-full w-full bg-white object-contain p-1"
                                    >

                                @else

                                    {{ $companyInitials }}

                                @endif

                            </div>

                            <div class="min-w-0">

                                <p class="break-words text-sm font-extrabold text-[#0F2347]">
                                    {{ $exhibitor->company_name }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Exposant Refrigair Expo 2027
                                </p>

                            </div>

                        </div>

                        <dl class="mt-5 space-y-4 text-sm">

                            <div>

                                <dt class="text-xs text-slate-400">
                                    Responsable
                                </dt>

                                <dd class="mt-1 font-semibold text-[#0F2347]">
                                    {{ $exhibitor->contact_name ?? '—' }}
                                </dd>

                            </div>

                            <div>

                                <dt class="text-xs text-slate-400">
                                    Adresse email
                                </dt>

                                <dd class="mt-1 break-all font-medium text-blue-600">

                                    @if ($exhibitor->email)

                                        <a href="mailto:{{ $exhibitor->email }}"
                                           class="hover:underline">
                                            {{ $exhibitor->email }}
                                        </a>

                                    @else
                                        —
                                    @endif

                                </dd>

                            </div>

                            <div>

                                <dt class="text-xs text-slate-400">
                                    Téléphone
                                </dt>

                                <dd class="mt-1 font-medium text-blue-600">

                                    @if ($exhibitor->phone)

                                        <a href="tel:{{ $exhibitor->phone }}"
                                           class="hover:underline">
                                            {{ $exhibitor->phone }}
                                        </a>

                                    @else
                                        —
                                    @endif

                                </dd>

                            </div>

                            <div class="grid grid-cols-2 gap-3">

                                <div class="rounded-xl bg-slate-50 p-3">

                                    <dt class="text-xs text-slate-500">
                                        Stand
                                    </dt>

                                    <dd class="mt-1 font-extrabold text-[#0F2347]">
                                        {{ $exhibitor->stand_number ?? '—' }}
                                    </dd>

                                </div>

                                <div class="rounded-xl bg-slate-50 p-3">

                                    <dt class="text-xs text-slate-500">
                                        Surface
                                    </dt>

                                    <dd class="mt-1 font-extrabold text-[#0F2347]">

                                        {{ $exhibitor->area_sqm !== null
                                            ? $exhibitor->area_sqm . ' m²'
                                            : '—' }}

                                    </dd>

                                </div>

                            </div>

                        </dl>

                        <a href="{{ route('commercial.exhibitors.show', $exhibitor) }}"
                           class="mt-5 block rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-center text-sm font-bold text-blue-700 transition hover:bg-blue-100">

                            Voir la fiche exposant

                        </a>

                    @else

                        <p class="mt-4 text-sm text-slate-500">
                            Informations indisponibles.
                        </p>

                    @endif

                </section>

                <!-- TICKET DETAILS -->
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex items-center gap-3">

                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">

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

                        <div class="flex items-center justify-between gap-3 py-3">

                            <dt class="text-slate-500">
                                N° de demande
                            </dt>

                            <dd class="font-bold text-[#0F2347]">
                                #{{ $ticket->id }}
                            </dd>

                        </div>

                        <div class="flex items-center justify-between gap-3 py-3">

                            <dt class="text-slate-500">
                                Statut
                            </dt>

                            <dd class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses }}">
                                {{ $ticket->status_label }}
                            </dd>

                        </div>

                        <div class="flex items-center justify-between gap-3 py-3">

                            <dt class="text-slate-500">
                                Catégorie
                            </dt>

                            <dd class="font-semibold text-[#0F2347]">

                                {{ $categories[$ticket->category] ?? 'Autre' }}

                            </dd>

                        </div>

                        <div class="flex items-start justify-between gap-3 py-3">

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

                        <div class="flex items-start justify-between gap-3 py-3">

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

                        @if ($ticket->resolved_at)

                            <div class="flex items-start justify-between gap-3 py-3">

                                <dt class="text-slate-500">
                                    Résolue le
                                </dt>

                                <dd class="text-right font-semibold text-emerald-700">

                                    {{ $ticket->resolved_at->format('d/m/Y') }}

                                    <span class="mt-1 block text-xs font-normal">
                                        {{ $ticket->resolved_at->format('H:i') }}
                                    </span>

                                </dd>

                            </div>

                        @endif

                        <div class="flex items-center justify-between gap-3 pt-3">

                            <dt class="text-slate-500">
                                Messages
                            </dt>

                            <dd class="font-bold text-[#0F2347]">
                                {{ $messages->count() }}
                            </dd>

                        </div>

                    </dl>

                </section>

            </aside>

        </div>

    </div>

    <!-- CONVERSATION SCRIPT -->
    <script>
        (() => {
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

            const form = document.getElementById(
                'support-reply-form'
            );

            if (conversation) {
                conversation.scrollTop = conversation.scrollHeight;
            }

            if (!textarea || !counter || !button || !form) {
                return;
            }

            function updateComposer() {
                counter.textContent = textarea.value.length;

                button.disabled =
                    textarea.value.trim().length === 0
                    || textarea.value.length > 5000;
            }

            textarea.addEventListener(
                'input',
                updateComposer
            );

            form.addEventListener('submit', event => {
                if (
                    !textarea.value.trim()
                    || textarea.value.length > 5000
                ) {
                    event.preventDefault();
                    return;
                }

                button.disabled = true;

                button.querySelector('span').textContent =
                    'Envoi en cours...';
            });

            updateComposer();
        })();
    </script>

</x-commercial.layout>
