@php
    use App\Enums\SlotRequestStatus;

    $eur = fn ($value) => number_format((float) $value, 0, ',', ' ').' €';

    $freeCount = [
        'conference' => count($availableSlots['conference'] ?? []),
        'lounge' => count($availableSlots['lounge'] ?? []),
    ];
    $hasAvailable = array_sum($freeCount) > 0;

    $selectedType = old('type', $freeCount['conference'] > 0 || $freeCount['lounge'] === 0 ? 'conference' : 'lounge');
    $mySlotIds = $mySlots->pluck('id')->all();

    $offers = [
        'conference' => [
            'title' => 'Salle de conférence',
            'tagline' => 'Prenez la parole devant un public de professionnels du froid et de la climatisation.',
            'when' => 'Mercredi 3 et jeudi 4 février 2027',
            'duration' => 'Créneau de 1 heure',
            'price' => 1500,
            'per' => 'par créneau d\'1 h',
            'ideal' => ['Présenter une innovation ou une étude de cas', 'Partager votre expertise technique', 'Gagner en visibilité auprès des décideurs'],
            'accent' => 'blue',
        ],
        'lounge' => [
            'title' => 'Lounge des Technologies',
            'tagline' => 'Un espace ouvert en continu pour faire découvrir vos produits en démonstration.',
            'when' => 'Du mardi 2 au jeudi 4 février 2027',
            'duration' => 'Créneau de 2 heures',
            'price' => 1000,
            'per' => 'par créneau de 2 h',
            'ideal' => ['Démontrer un produit en conditions réelles', 'Échanger librement avec les visiteurs', 'Générer des contacts qualifiés'],
            'accent' => 'violet',
        ],
    ];

    $steps = [
        ['title' => 'Choisissez', 'text' => 'Sélectionnez l\'espace puis le créneau libre qui vous convient dans le planning.'],
        ['title' => 'Décrivez', 'text' => 'Indiquez le titre, un résumé de 5 lignes et les intervenants.'],
        ['title' => 'Confirmation', 'text' => 'L\'équipe Refrigair Expo valide votre demande et vous prévient par e-mail.'],
        ['title' => 'Support', 'text' => 'Envoyez votre présentation (PPT, PPTX ou PDF) au plus tard 7 jours avant.'],
        ['title' => 'Jour J', 'text' => 'Présentez-vous 15 min avant votre créneau pour le test technique.'],
    ];
@endphp

<x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">
<div class="min-w-0 space-y-8">

    {{-- ============ EN-TÊTE ============ --}}
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <nav class="mb-3 text-sm text-slate-500" aria-label="Fil d'Ariane">
                <a href="{{ route('exposant.index') }}" class="hover:text-[#2563EB]">Espace exposant</a>
                <span class="mx-2">/</span>
                <span class="font-semibold text-[#2563EB]">Conférences & Lounge</span>
            </nav>
            <div class="flex items-center gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-700">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="size-6" aria-hidden="true">
                        <path d="M12 3v4M8 21h8M12 17v4"/><rect x="3" y="7" width="18" height="10" rx="2"/><path d="m10 10 4 2-4 2z"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl font-extrabold text-[#0F2347] sm:text-3xl">Conférences & Lounge des Technologies</h1>
                    <p class="mt-1 text-sm text-slate-500">Réservez un créneau pour présenter votre entreprise pendant le salon.</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 to-white px-5 py-4 shadow-sm xl:min-w-80">
            <p class="font-extrabold text-[#0F2347]">Refrigair Expo 2027</p>
            <p class="mt-1 text-xs leading-5 text-slate-600">2 → 4 février 2027 · OFEC Casablanca</p>
            <p class="mt-2 text-xs font-semibold text-emerald-700">{{ array_sum($freeCount) }} créneau{{ array_sum($freeCount) > 1 ? 'x' : '' }} encore disponible{{ array_sum($freeCount) > 1 ? 's' : '' }}</p>
        </div>
    </div>

    @if (session('status'))
        <div role="status" class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
            <span aria-hidden="true">✓</span> {{ session('status') }}
        </div>
    @endif

    {{-- ============ LES DEUX OFFRES ============ --}}
    <section aria-labelledby="offers-title">
        <h2 id="offers-title" class="text-lg font-bold text-[#0F2347]">Deux façons de présenter votre entreprise</h2>
        <p class="mt-1 text-sm text-slate-500">Comparez les deux formules, puis choisissez vos créneaux plus bas dans le planning.</p>

        <div class="mt-4 grid gap-5 lg:grid-cols-2">
            @foreach ($offers as $type => $offer)
                @php $isBlue = $offer['accent'] === 'blue'; @endphp
                <article class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70">
                    <div class="{{ $isBlue ? 'bg-gradient-to-br from-blue-600 to-blue-800' : 'bg-gradient-to-br from-violet-600 to-indigo-800' }} p-6 text-white">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-white/70">{{ $offer['when'] }}</p>
                                <h3 class="mt-1 text-xl font-extrabold">{{ $offer['title'] }}</h3>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-extrabold">{{ $eur($offer['price']) }}</p>
                                <p class="text-xs text-white/70">HT {{ $offer['per'] }}</p>
                            </div>
                        </div>
                        <p class="mt-3 text-sm text-white/85">{{ $offer['tagline'] }}</p>
                    </div>

                    <div class="flex flex-1 flex-col gap-5 p-6">
                        @if ($type === 'conference')
                            {{-- Déroulé d'un créneau d'1 h --}}
                            <div>
                                <p class="text-sm font-bold text-slate-800">Déroulé de votre heure</p>
                                <div class="mt-2 flex h-9 overflow-hidden rounded-xl text-xs font-bold text-white" aria-hidden="true">
                                    <div class="flex items-center justify-center bg-blue-600" style="width: 75%">45 min présentation</div>
                                    <div class="flex items-center justify-center bg-sky-400" style="width: 16.67%">10 min Q&R</div>
                                    <div class="flex items-center justify-center bg-slate-300 text-slate-700" style="width: 8.33%">5'</div>
                                </div>
                                <p class="mt-2 text-xs text-slate-500">45 min de présentation, 10 min de questions-réponses avec le public, puis 5 min de changement d'intervenant.</p>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">Horaires proposés (chaque jour)</p>
                                <div class="mt-2 flex flex-wrap gap-1.5 text-xs font-semibold">
                                    @foreach (['09:30', '10:30', '11:30'] as $h)<span class="rounded-lg bg-blue-50 px-2 py-1 text-blue-700">{{ $h }}</span>@endforeach
                                    <span class="rounded-lg bg-slate-100 px-2 py-1 text-slate-500">Pause 12:30 – 14:00</span>
                                    @foreach (['14:00', '15:00', '16:00'] as $h)<span class="rounded-lg bg-blue-50 px-2 py-1 text-blue-700">{{ $h }}</span>@endforeach
                                </div>
                            </div>
                        @else
                            {{-- Ce qui est inclus au lounge --}}
                            <div>
                                <p class="text-sm font-bold text-slate-800">Inclus dans votre créneau</p>
                                <ul class="mt-2 grid gap-2 sm:grid-cols-3">
                                    @foreach ([['🗄️', 'Table ou présentoir'], ['🖥️', 'Écran 55" simple face'], ['📄', 'Fiche produit']] as [$icon, $label])
                                        <li class="flex items-center gap-2 rounded-xl bg-violet-50 px-3 py-2.5 text-xs font-semibold text-violet-800">
                                            <span aria-hidden="true" class="text-base">{{ $icon }}</span>{{ $label }}
                                        </li>
                                    @endforeach
                                </ul>
                                <p class="mt-2 text-xs text-slate-500">Vous disposez de 10 minutes d'installation avant le début de votre créneau.</p>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">Horaires proposés</p>
                                <ul class="mt-2 space-y-1 text-xs text-slate-600">
                                    <li><span class="font-semibold text-violet-700">Mardi 2 :</span> 11:00 · 13:00 · 15:00 (fin 17:00)</li>
                                    <li><span class="font-semibold text-violet-700">Mercredi 3 et jeudi 4 :</span> 09:30 · 11:30 · 13:30 · 15:30 (fin 17:30)</li>
                                </ul>
                            </div>
                        @endif

                        <div>
                            <p class="text-sm font-bold text-slate-800">Idéal pour</p>
                            <ul class="mt-2 space-y-1.5">
                                @foreach ($offer['ideal'] as $item)
                                    <li class="flex items-start gap-2 text-sm text-slate-600">
                                        <span class="mt-0.5 {{ $isBlue ? 'text-blue-600' : 'text-violet-600' }}" aria-hidden="true">✓</span>{{ $item }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="mt-auto flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
                            <p class="text-sm">
                                <span class="text-lg font-extrabold {{ $freeCount[$type] > 0 ? 'text-emerald-600' : 'text-slate-400' }}">{{ $freeCount[$type] }}</span>
                                <span class="text-slate-500">créneau{{ $freeCount[$type] > 1 ? 'x' : '' }} libre{{ $freeCount[$type] > 1 ? 's' : '' }}</span>
                            </p>
                            @if ($freeCount[$type] > 0)
                                <a href="#reserver" data-pick-type="{{ $type }}" class="rounded-xl {{ $isBlue ? 'bg-[#2563EB] hover:bg-blue-700' : 'bg-violet-600 hover:bg-violet-700' }} px-4 py-2.5 text-sm font-bold text-white">
                                    Choisir un créneau →
                                </a>
                            @else
                                <span class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-500">Complet</span>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- ============ COMMENT ÇA MARCHE ============ --}}
    <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70" aria-labelledby="steps-title">
        <h2 id="steps-title" class="text-lg font-bold text-[#0F2347]">Comment ça marche ?</h2>
        <ol class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($steps as $i => $step)
                <li class="relative">
                    <div class="flex items-center gap-3">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-blue-600 text-sm font-extrabold text-white">{{ $i + 1 }}</span>
                        <span class="font-bold text-slate-800">{{ $step['title'] }}</span>
                    </div>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500 lg:pr-3">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- ============ MES CRÉNEAUX ============ --}}
    @if ($mySlots->isNotEmpty())
        <section aria-labelledby="my-slots-title">
            <h2 id="my-slots-title" class="text-lg font-bold text-[#0F2347]">Mes créneaux</h2>

            <div class="mt-4 grid gap-5 xl:grid-cols-2">
                @foreach ($mySlots as $slot)
                    @php
                        $confirmed = $slot->status->isSold();
                        $deadline = $slot->date->copy()->subDays(7);
                        $daysLeft = (int) now()->startOfDay()->diffInDays($deadline, false);
                        $progress = [
                            ['label' => 'Confirmé', 'done' => $confirmed],
                            ['label' => 'Support envoyé', 'done' => (bool) $slot->support_path],
                            ['label' => 'Jour J', 'done' => $slot->date->isPast() && ! $slot->date->isToday()],
                        ];
                    @endphp

                    <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $slot->space->type->label() }} · {{ $slot->code }}</p>
                                <p class="mt-1 text-lg font-extrabold capitalize text-[#0F2347]">{{ $slot->date->locale('fr')->isoFormat('dddd D MMMM') }}</p>
                                <p class="text-sm font-semibold text-slate-600">{{ $slot->time_range }} · {{ $eur($slot->price_excl_tax) }} HT</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $slot->status->pillClasses() }}">{{ $slot->status->label() }}</span>
                        </div>

                        @if ($slot->title)
                            <p class="mt-3 rounded-xl bg-slate-50 px-4 py-3 text-sm"><span class="font-semibold text-slate-800">{{ $slot->title }}</span>
                                @if ($slot->speakers)<span class="block text-xs text-slate-500">{{ $slot->speakers }}</span>@endif
                            </p>
                        @endif

                        {{-- Suivi --}}
                        <ol class="mt-5 grid grid-cols-3 gap-2" aria-label="Suivi du créneau">
                            @foreach ($progress as $item)
                                <li class="text-center">
                                    <div class="h-1.5 rounded-full {{ $item['done'] ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>
                                    <p class="mt-1.5 text-xs font-semibold {{ $item['done'] ? 'text-emerald-700' : 'text-slate-400' }}">{{ $item['done'] ? '✓ ' : '' }}{{ $item['label'] }}</p>
                                </li>
                            @endforeach
                        </ol>

                        @if (! $confirmed)
                            <p class="mt-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                Ce créneau est <strong>en option</strong> : il vous est réservé en attendant la confirmation. Vous pourrez envoyer votre support dès qu'il sera confirmé.
                            </p>
                        @else
                            <div class="mt-5 rounded-xl border border-dashed {{ $slot->support_path ? 'border-emerald-300 bg-emerald-50/50' : 'border-blue-300 bg-blue-50/40' }} p-4">
                                @if ($slot->support_path)
                                    <p class="text-sm font-semibold text-emerald-800">✓ Support reçu le {{ $slot->support_uploaded_at?->format('d/m/Y à H:i') }}</p>
                                    <a href="{{ route('exhibitor.slots.support.download', $slot) }}" class="mt-1 inline-block text-sm font-semibold text-emerald-700 underline">{{ $slot->support_original_name }}</a>
                                    <p class="mt-2 text-xs text-slate-500">Vous pouvez encore le remplacer par une version plus récente.</p>
                                @else
                                    <p class="text-sm font-bold text-[#0F2347]">Envoyez votre support de présentation</p>
                                    <p class="mt-1 text-sm text-slate-600">
                                        À envoyer avant le <strong>{{ $deadline->locale('fr')->isoFormat('D MMMM YYYY') }}</strong>
                                        @if ($daysLeft > 0)
                                            <span class="text-slate-500">(dans {{ $daysLeft }} jour{{ $daysLeft > 1 ? 's' : '' }})</span>
                                        @elseif ($daysLeft === 0)
                                            <span class="font-semibold text-red-600">(aujourd'hui)</span>
                                        @else
                                            <span class="font-semibold text-red-600">(date dépassée, envoyez-le au plus vite)</span>
                                        @endif
                                    </p>
                                @endif

                                <form method="POST" action="{{ route('exhibitor.slots.support.store', $slot) }}" enctype="multipart/form-data" class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center">
                                    @csrf
                                    <input type="file" name="support" accept=".ppt,.pptx,.pdf" required
                                           class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:text-sm file:font-semibold file:text-blue-700 file:shadow-sm file:ring-1 file:ring-blue-200">
                                    <button type="submit" class="shrink-0 rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700">
                                        {{ $slot->support_path ? 'Remplacer' : 'Envoyer' }}
                                    </button>
                                </form>
                                <p class="mt-2 text-xs text-slate-500">Formats acceptés : PPT, PPTX ou PDF — 20 Mo maximum.</p>
                                @error('support')
                                    <p class="mt-1 text-sm font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <p class="mt-4 flex items-start gap-2 text-xs text-slate-500">
                                <span aria-hidden="true">⏰</span>
                                Le jour J, présentez-vous à {{ \Illuminate\Support\Carbon::createFromFormat('H:i', $slot->start_label)->subMinutes(15)->format('H:i') }} (15 min avant) pour le test technique{{ $slot->space->type->value === 'lounge' ? ' et l\'installation' : '' }}.
                            </p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ MES DEMANDES ============ --}}
    @if ($myRequests->isNotEmpty())
        <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70" aria-labelledby="my-requests-title">
            <h2 id="my-requests-title" class="text-lg font-bold text-[#0F2347]">Mes demandes</h2>
            <ul class="mt-4 space-y-3">
                @foreach ($myRequests as $slotRequest)
                    <li class="flex flex-wrap items-start justify-between gap-3 rounded-xl border border-slate-100 px-4 py-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800">{{ $slotRequest->title }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $slotRequest->type->label() }} · envoyée le {{ $slotRequest->created_at->format('d/m/Y') }}
                                · créneau demandé : {{ $slotRequest->firstChoice?->code ?? '—' }}
                            </p>
                            @if ($slotRequest->status === SlotRequestStatus::Pending)
                                <p class="mt-1 text-xs text-amber-700">Votre demande est en cours d'étude. Vous recevrez un e-mail dès qu'elle sera traitée.</p>
                            @elseif ($slotRequest->assignedSlot)
                                <p class="mt-1 text-xs font-semibold text-emerald-700">Créneau attribué : {{ $slotRequest->assignedSlot->code }}</p>
                            @endif
                            @if ($slotRequest->rejection_reason)
                                <p class="mt-1 text-xs text-red-600">Motif : {{ $slotRequest->rejection_reason }}</p>
                            @endif
                        </div>
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $slotRequest->status->pillClasses() }}">{{ $slotRequest->status->label() }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- ============ RÉSERVER ============ --}}
    <section id="reserver" class="scroll-mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70" aria-labelledby="book-title">
        <h2 id="book-title" class="text-lg font-bold text-[#0F2347]">Réserver un créneau</h2>
        <p class="mt-1 text-sm text-slate-500">Trois étapes : choisissez l'espace, cliquez sur le créneau libre qui vous convient, puis décrivez votre présentation.</p>

        @if ($errors->any() && ! $errors->has('support'))
            <div role="alert" class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                Merci de corriger les champs signalés ci-dessous.
            </div>
        @endif

        <form method="POST" action="{{ route('exhibitor.slots.request') }}" id="slot-request-form" class="mt-6 space-y-8">
            @csrf

            {{-- Étape 1 : espace --}}
            <fieldset>
                <legend class="flex items-center gap-2 text-sm font-bold text-slate-800">
                    <span class="grid size-6 place-items-center rounded-full bg-blue-600 text-xs text-white">1</span>
                    Choisissez l'espace
                </legend>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach ($spaceTypes as $type)
                        <label class="relative flex cursor-pointer items-center gap-3 rounded-2xl border-2 border-slate-200 p-4 transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/60">
                            <input type="radio" name="type" value="{{ $type->value }}" @checked($selectedType === $type->value) class="size-4 text-blue-600">
                            <span class="min-w-0 flex-1">
                                <span class="block font-bold text-slate-800">{{ $type->label() }}</span>
                                <span class="block text-xs text-slate-500">
                                    {{ $type->value === 'conference' ? '1 h · 1 500 € HT' : '2 h · 1 000 € HT' }} · {{ $freeCount[$type->value] }} libre{{ $freeCount[$type->value] > 1 ? 's' : '' }}
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('type') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </fieldset>

            {{-- Étape 2 : planning cliquable --}}
            <fieldset>
                <legend class="flex items-center gap-2 text-sm font-bold text-slate-800">
                    <span class="grid size-6 place-items-center rounded-full bg-blue-600 text-xs text-white">2</span>
                    Choisissez votre créneau
                </legend>
                <p class="mt-1 text-xs text-slate-500">Cliquez sur un créneau libre pour le sélectionner. Cliquez sur un autre créneau pour changer, ou à nouveau sur le même pour le retirer.</p>

                <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold" aria-label="Légende">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-emerald-700"><span class="size-2 rounded-full bg-emerald-500"></span>Disponible</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-600 px-3 py-1 text-white">Votre sélection</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-amber-700"><span class="size-2 rounded-full bg-amber-500"></span>Déjà demandé par vous</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-indigo-700"><span class="size-2 rounded-full bg-indigo-500"></span>Votre créneau</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-slate-500"><span class="size-2 rounded-full bg-slate-400"></span>Réservé</span>
                </div>

                @foreach ($spaceTypes as $type)
                    @php $days = $planning[$type->value] ?? collect(); @endphp
                    <div data-planning="{{ $type->value }}" @class(['mt-4 grid gap-4', 'md:grid-cols-2' => $days->count() <= 2, 'md:grid-cols-3' => $days->count() > 2, 'hidden' => $selectedType !== $type->value])>
                        @foreach ($days as $date => $daySlots)
                            <div class="rounded-2xl bg-slate-50 p-3">
                                <p class="px-1 pb-2 text-sm font-bold capitalize text-slate-700">{{ \Illuminate\Support\Carbon::parse($date)->locale('fr')->isoFormat('dddd D MMMM') }}</p>
                                <ul class="space-y-2">
                                    @foreach ($daySlots->values() as $i => $slot)
                                        @php
                                            $isMine = in_array($slot->id, $mySlotIds, true);
                                            $isRequested = in_array($slot->id, $requestedSlotIds, true);
                                            $isFree = ! $slot->status->isTaken() && ! $isRequested;
                                        @endphp

                                        @if ($i > 0 && $daySlots->values()[$i - 1]->end_label !== $slot->start_label)
                                            <li class="py-0.5 text-center text-[11px] font-semibold text-slate-400">— Pause {{ $daySlots->values()[$i - 1]->end_label }} – {{ $slot->start_label }} —</li>
                                        @endif

                                        <li>
                                            @if ($isFree)
                                                <button type="button"
                                                        data-slot-tile="{{ $slot->id }}"
                                                        data-type="{{ $type->value }}"
                                                        aria-pressed="false"
                                                        class="group flex w-full items-center justify-between gap-2 rounded-xl border-2 border-emerald-200 bg-white px-3 py-2.5 text-left transition hover:border-emerald-400 hover:shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 aria-pressed:border-blue-600 aria-pressed:bg-blue-600 aria-pressed:text-white">
                                                    <span>
                                                        <span class="block text-sm font-bold">{{ $slot->time_range }}</span>
                                                        <span class="block text-[11px] font-semibold opacity-60">{{ $slot->code }}</span>
                                                    </span>
                                                    <span data-tile-badge class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700 group-aria-pressed:bg-white group-aria-pressed:text-blue-700">Libre</span>
                                                </button>
                                            @else
                                                <div @class([
                                                    'flex items-center justify-between gap-2 rounded-xl border-2 px-3 py-2.5',
                                                    'border-indigo-200 bg-indigo-50 text-indigo-800' => $isMine,
                                                    'border-amber-200 bg-amber-50 text-amber-800' => ! $isMine && $isRequested,
                                                    'border-transparent bg-slate-100 text-slate-400' => ! $isMine && ! $isRequested,
                                                ])>
                                                    <span>
                                                        <span class="block text-sm font-bold {{ ! $isMine && ! $isRequested ? 'line-through decoration-slate-300' : '' }}">{{ $slot->time_range }}</span>
                                                        <span class="block text-[11px] font-semibold opacity-70">{{ $slot->code }}</span>
                                                    </span>
                                                    <span class="text-[11px] font-bold">{{ $isMine ? 'Votre créneau' : ($isRequested ? 'Demandé' : 'Réservé') }}</span>
                                                </div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                {{-- Créneau sélectionné (liste accessible au clavier) --}}
                <label class="mt-5 block rounded-2xl border border-slate-200 p-4 text-sm font-semibold text-slate-700">
                    Votre créneau
                    <select name="first_choice_slot_id" required data-slot-select class="mt-2 w-full rounded-xl border-slate-300 text-sm font-normal capitalize">
                        <option value="">— Cliquez sur un créneau ou choisissez ici —</option>
                        @foreach ($spaceTypes as $type)
                            @foreach ($availableSlots[$type->value] ?? [] as $slot)
                                @continue(in_array($slot->id, $requestedSlotIds, true))
                                <option value="{{ $slot->id }}" data-type="{{ $type->value }}" @selected((int) old('first_choice_slot_id') === $slot->id)>{{ $slot->full_label }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    @error('first_choice_slot_id') <span class="mt-1 block text-sm font-normal text-red-600">{{ $message }}</span> @enderror
                </label>
            </fieldset>

            {{-- Étape 3 : présentation --}}
            <fieldset class="space-y-4">
                <legend class="flex items-center gap-2 text-sm font-bold text-slate-800">
                    <span class="grid size-6 place-items-center rounded-full bg-blue-600 text-xs text-white">3</span>
                    Décrivez votre présentation
                </legend>

                <label class="block text-sm font-semibold text-slate-700">
                    Titre de la présentation
                    <input type="text" name="title" value="{{ old('title') }}" required maxlength="150" placeholder="Ex. : Les fluides frigorigènes naturels en pratique"
                           class="mt-1 w-full rounded-xl border-slate-300 text-sm font-normal">
                    @error('title') <span class="mt-1 block text-sm font-normal text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="block text-sm font-semibold text-slate-700">
                    <span class="flex items-center justify-between">
                        Résumé
                        <span data-line-counter class="text-xs font-semibold text-slate-400">0 / 5 lignes</span>
                    </span>
                    <textarea name="summary" rows="5" required maxlength="800" data-summary
                              placeholder="En 5 lignes maximum : le sujet, ce que le public va apprendre, ce qui rend votre approche unique…"
                              class="mt-1 w-full rounded-xl border-slate-300 text-sm font-normal">{{ old('summary') }}</textarea>
                    @error('summary') <span class="mt-1 block text-sm font-normal text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="block text-sm font-semibold text-slate-700">
                    Intervenants
                    <input type="text" name="speakers" value="{{ old('speakers') }}" required maxlength="500" placeholder="Ex. : Sara El Amrani, directrice technique"
                           class="mt-1 w-full rounded-xl border-slate-300 text-sm font-normal">
                    <span class="mt-1 block text-xs font-normal text-slate-400">Nom et fonction de chaque intervenant, séparés par une virgule.</span>
                    @error('speakers') <span class="mt-1 block text-sm font-normal text-red-600">{{ $message }}</span> @enderror
                </label>
            </fieldset>

            <div class="flex flex-col gap-4 rounded-2xl bg-slate-50 p-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600">
                    <span class="font-semibold text-slate-800">Votre demande sera étudiée par l'équipe Refrigair Expo.</span>
                    Le créneau ne vous est réservé qu'après confirmation par e-mail.
                </p>
                <button type="submit" @disabled(! $hasAvailable) class="shrink-0 rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300">
                    Envoyer ma demande
                </button>
            </div>
        </form>
    </section>
</div>

<script>
    (() => {
        const form = document.getElementById('slot-request-form');
        if (!form) return;

        const radios = [...form.querySelectorAll('input[name="type"]')];
        const selects = [...form.querySelectorAll('[data-slot-select]')];
        const tiles = [...form.querySelectorAll('[data-slot-tile]')];
        const panels = [...form.querySelectorAll('[data-planning]')];
        const currentType = () => radios.find((r) => r.checked)?.value;

        const render = () => {
            const type = currentType();
            panels.forEach((p) => p.classList.toggle('hidden', p.dataset.planning !== type));

            selects.forEach((select) => {
                select.querySelectorAll('option[data-type]').forEach((option) => {
                    option.hidden = option.dataset.type !== type;
                    if (option.hidden && option.selected) select.value = '';
                });
            });

            const chosen = selects[0].value;
            tiles.forEach((tile) => {
                const selected = tile.dataset.slotTile === chosen;
                tile.setAttribute('aria-pressed', selected ? 'true' : 'false');
                tile.querySelector('[data-tile-badge]').textContent = selected ? '✓ Sélectionné' : 'Libre';
            });
        };

        // Un seul créneau : cliquer sélectionne, recliquer retire.
        tiles.forEach((tile) => tile.addEventListener('click', () => {
            const id = tile.dataset.slotTile;
            selects[0].value = selects[0].value === id ? '' : id;
            render();
        }));

        radios.forEach((radio) => radio.addEventListener('change', render));
        selects.forEach((select) => select.addEventListener('change', render));

        // Boutons « Choisir un créneau » des cartes d'offre.
        document.querySelectorAll('[data-pick-type]').forEach((link) => link.addEventListener('click', () => {
            const radio = radios.find((r) => r.value === link.dataset.pickType);
            if (radio) { radio.checked = true; render(); }
        }));

        // Compteur de lignes du résumé.
        const summary = form.querySelector('[data-summary]');
        const counter = form.querySelector('[data-line-counter]');
        const countLines = () => {
            const lines = summary.value.trim() === '' ? 0 : summary.value.trim().split(/\r?\n/).length;
            counter.textContent = `${lines} / 5 lignes`;
            counter.classList.toggle('text-red-600', lines > 5);
            counter.classList.toggle('text-slate-400', lines <= 5);
        };
        summary.addEventListener('input', countLines);

        countLines();
        render();
    })();
</script>
</x-exhibitor.layout>
