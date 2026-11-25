
@php
    $slot = $reservation->assignedSlot;

    $isManager = auth()->user()->role
        === \App\Enums\UserRole::ResponsableConferences;

    $supportStatus = $reservation->support_review_status
        ?? 'not_submitted';

    $statuses = [
        'not_submitted' => [
            'Non envoyé',
            'bg-slate-100 text-slate-700',
        ],
        'pending' => [
            'En attente de vérification',
            'bg-amber-100 text-amber-800',
        ],
        'approved' => [
            'Approuvé',
            'bg-emerald-100 text-emerald-800',
        ],
        'revision_requested' => [
            'Modification demandée',
            'bg-red-100 text-red-800',
        ],
    ];

    [$statusLabel, $statusClasses] = $statuses[$supportStatus]
        ?? $statuses['not_submitted'];

    $downloadRoute = $isManager
        ? 'responsable-conferences.supports.download'
        : 'exhibitor.conferences.collaboration.download';

    $messageRoute = $isManager
        ? 'responsable-conferences.supports.message'
        : 'exhibitor.conferences.collaboration.message';
@endphp

<section class="overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-sm">

    {{-- CONFIRMED RESERVATION --}}
    <div class="border-b border-emerald-100 bg-emerald-50/60 p-5">
        <div class="flex flex-wrap items-center gap-3">

            <span class="grid size-11 place-items-center rounded-xl bg-emerald-100 text-emerald-700">
                <x-admin.icon name="clipboard-check" class="size-6"/>
            </span>

            <div class="min-w-0 flex-1">
                <h2 class="text-lg font-black text-[#0F2347]">
                    {{ $isManager ? 'Suivi de la conférence' : 'Ma conférence confirmée' }}
                    — DEM-{{ str_pad((string) $reservation->id, 4, '0', STR_PAD_LEFT) }}
                </h2>

                <p class="mt-1 text-sm font-medium text-slate-600">
                    {{ $reservation->title }}
                </p>
            </div>

            <span class="rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-700">
                Confirmée
            </span>
        </div>

        <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-xs font-semibold text-slate-700">
            <span>
                {{ $slot->date?->locale('fr')->isoFormat('D MMM YYYY') }}
            </span>

            <span>{{ $slot->time_range }}</span>

            <span>
                {{ $slot->space?->name ?? $slot->space?->type?->label() }}
            </span>

            @if($isManager)
                <span>
                    {{ $reservation->exhibitor?->company_name }}
                </span>
            @endif
        </div>
    </div>

    @if(session('status'))
        <div class="mx-5 mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mx-5 mt-5 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-5 p-5 xl:grid-cols-2">

        {{-- SUPPORT PANEL --}}
        <div class="min-w-0 rounded-xl border border-slate-200 p-4">

            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="font-black text-[#0F2347]">
                    Support de présentation
                </h3>

                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses }}">
                    {{ $statusLabel }}
                </span>
            </div>

            @if($slot->support_path)

                <div class="mt-5 flex items-center gap-3 rounded-xl bg-slate-50 p-4">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-700">
                        <x-admin.icon name="inbox" class="size-5"/>
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="break-all text-sm font-bold text-[#0F2347]">
                            {{ $slot->support_original_name ?? 'Support de présentation' }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Envoyé le
                            {{ $slot->support_uploaded_at?->format('d/m/Y H:i') ?? '—' }}
                        </p>
                    </div>

                    <a
                        href="{{ route($downloadRoute, $reservation) }}"
                        class="rounded-lg border border-blue-200 bg-white px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-50"
                    >
                        Télécharger
                    </a>
                </div>

            @else

                <div class="mt-5 rounded-xl border border-dashed border-blue-200 bg-blue-50/40 p-6 text-center">
                    <p class="font-bold text-[#0F2347]">
                        Aucun support envoyé
                    </p>

                    <p class="mt-2 text-xs text-slate-500">
                        Document PPT, PPTX ou PDF · 20 Mo maximum.
                    </p>
                </div>

            @endif

            {{-- DETAILS --}}
            @if($reservation->support_details)
                <div class="mt-5">
                    <h4 class="text-sm font-bold text-[#0F2347]">
                        Détails de la présentation
                    </h4>

                    <p class="mt-2 whitespace-pre-line break-words rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-700">{{ $reservation->support_details }}</p>
                </div>
            @endif

            {{-- REVIEW NOTES --}}
            @if($reservation->support_review_note)
                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-sm font-bold text-amber-900">
                        Remarque du Responsable Conférences
                    </p>

                    <p class="mt-2 whitespace-pre-line text-sm text-amber-900">{{ $reservation->support_review_note }}</p>
                </div>
            @endif

            {{-- EXHIBITOR UPLOAD --}}
            @unless($isManager)
                @if($supportStatus !== 'approved')
                    <form
                        method="POST"
                        action="{{ route('exhibitor.conferences.collaboration.support', $reservation) }}"
                        enctype="multipart/form-data"
                        class="mt-5 space-y-4"
                    >
                        @csrf

                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#0F2347]">
                                {{ $slot->support_path ? 'Remplacer le support' : 'Envoyer votre support' }}
                            </label>

                            <input
                                type="file"
                                name="support"
                                required
                                accept=".pdf,.ppt,.pptx"
                                class="block w-full rounded-xl border border-slate-200 p-3 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-blue-100 file:px-3 file:py-2 file:font-bold file:text-blue-700"
                            >
                        </div>

                        <div>
                            <label for="support-details-{{ $reservation->id }}"
                                   class="mb-2 block text-sm font-bold text-[#0F2347]">
                                Détails de la présentation *
                            </label>

                            <textarea
                                id="support-details-{{ $reservation->id }}"
                                name="support_details"
                                rows="5"
                                required
                                minlength="10"
                                maxlength="5000"
                                placeholder="Décrivez les points abordés, les démonstrations prévues, le matériel nécessaire..."
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500"
                            >{{ old('support_details', $reservation->support_details) }}</textarea>
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-bold text-white hover:bg-blue-700"
                        >
                            {{ $slot->support_path ? 'Envoyer la nouvelle version' : 'Envoyer le support' }}
                        </button>
                    </form>
                @else
                    <p class="mt-5 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
                        Votre support a été approuvé.
                        Pour demander une modification, contactez le Responsable via la messagerie.
                    </p>
                @endif
            @endunless

            {{-- MANAGER REVIEW --}}
            @if($isManager && $slot->support_path)
                <form
                    method="POST"
                    action="{{ route('responsable-conferences.supports.review', $reservation) }}"
                    class="mt-5 space-y-4"
                >
                    @csrf

                    <div>
                        <label class="mb-2 block text-sm font-bold text-[#0F2347]">
                            Remarque / demande de modification
                        </label>

                        <textarea
                            name="note"
                            rows="3"
                            maxlength="2000"
                            placeholder="Précisez les corrections demandées si nécessaire..."
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                        ></textarea>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button
                            type="submit"
                            name="decision"
                            value="approved"
                            class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700"
                        >
                            Approuver le support
                        </button>

                        <button
                            type="submit"
                            name="decision"
                            value="revision_requested"
                            class="rounded-xl bg-amber-500 px-5 py-3 text-sm font-bold text-white hover:bg-amber-600"
                        >
                            Demander une modification
                        </button>
                    </div>
                </form>
            @endif
        </div>

        {{-- CHAT PANEL --}}
        <div class="flex min-w-0 flex-col rounded-xl border border-slate-200">

            <div class="border-b border-slate-100 bg-slate-50 p-4">
                <h3 class="font-black text-[#0F2347]">
                    Messagerie avec
                    {{ $isManager ? 'l’exposant' : 'le Responsable Conférences' }}
                </h3>

                <p class="mt-1 text-xs text-slate-500">
                    Conversation privée liée à cette réservation.
                </p>
            </div>

            <div class="max-h-[420px] min-h-64 flex-1 space-y-4 overflow-y-auto p-4"
                 aria-label="Historique des messages">

                @forelse($messages as $message)
                    @php
                        $mine = (int) $message->sender_id === (int) auth()->id();
                    @endphp

                    <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[88%] rounded-2xl px-4 py-3 {{ $mine ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-800' }}">

                            <p class="text-xs font-extrabold">
                                {{ $mine ? 'Vous' : $message->sender?->name }}
                            </p>

                            <p class="mt-2 whitespace-pre-line break-words text-sm leading-6">{{ $message->body }}</p>

                            <p class="mt-2 text-[11px] {{ $mine ? 'text-blue-100' : 'text-slate-500' }}">
                                {{ $message->created_at?->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="grid min-h-52 place-items-center text-center">
                        <div>
                            <p class="font-bold text-[#0F2347]">
                                Aucun message pour le moment
                            </p>

                            <p class="mt-2 text-xs text-slate-500">
                                Vous pouvez démarrer la conversation ici.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>

            <form
                method="POST"
                action="{{ route($messageRoute, $reservation) }}"
                class="border-t border-slate-100 p-4"
            >
                @csrf

                <label for="chat-body-{{ $reservation->id }}"
                       class="mb-2 block text-xs font-bold text-slate-600">
                    Écrire un message
                </label>

                <textarea
                    id="chat-body-{{ $reservation->id }}"
                    name="body"
                    rows="3"
                    maxlength="4000"
                    required
                    placeholder="Écrivez votre message..."
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                ></textarea>

                <button
                    type="submit"
                    class="mt-3 w-full rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-bold text-white hover:bg-blue-700"
                >
                    Envoyer le message
                </button>
            </form>
        </div>
    </div>
</section>
