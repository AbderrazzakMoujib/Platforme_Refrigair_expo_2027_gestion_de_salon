@php
    use App\Enums\SlotStatus;

    $eur = fn ($value) => number_format((float) $value, 0, ',', ' ').' € HT';
    $pct = fn ($value) => rtrim(rtrim(number_format((float) $value, 1, ',', ' '), '0'), ',');

    $slotErrors = $errors->getBag('slot');
    $reopenId = (int) old('slot_id');
    $total = $stats['total'];
@endphp

<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercialInitials">
    <div class="min-w-0 space-y-7">

        {{-- Fil d'Ariane --}}
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500" aria-label="Fil d'Ariane">
            <a href="{{ route('commercial.index') }}" class="transition hover:text-blue-600">Espace commercial</a>
            <span aria-hidden="true">/</span>
            <span class="font-semibold text-[#0F2347]">Conférences & Lounge</span>
        </nav>

        {{-- En-tête --}}
        <header class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-start gap-4">
                <div class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                    <x-admin.icon name="calendar" class="size-7" />
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">Conférences & Lounge des Technologies</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-500">
                        Planning des créneaux de présentation du 2 au 4 février 2027. Cliquez sur un créneau pour l'attribuer, changer son statut ou compléter le programme.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('commercial.slot-requests.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                    Demandes
                    @if ($pendingRequestsCount > 0)
                        <span class="rounded-full bg-white/25 px-2 py-0.5 text-xs">{{ $pendingRequestsCount }}</span>
                    @endif
                </a>
                <a href="{{ route('commercial.slots.export.pdf', 'fr') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Programme PDF (FR)</a>
                <a href="{{ route('commercial.slots.export.pdf', 'en') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Program PDF (EN)</a>
                <a href="{{ route('commercial.slots.export.csv') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Export Excel (CSV)</a>
            </div>
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($slotErrors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <p class="font-semibold">Le créneau n'a pas été modifié :</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($slotErrors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Indicateurs --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Indicateurs des créneaux">
            <x-commercial.stat-card
                label="Créneaux vendus"
                :value="$total['sold'].' / '.$total['total']"
                icon="calendar"
                tone="blue"
                :trend="$pct($total['fill_rate']).' % de remplissage'"
                trend-tone="slate"
                :description="$total['options'].' en option · '.$total['available'].' disponibles'"
            />
            <x-commercial.stat-card
                label="CA réservé + payé"
                :value="$eur($total['revenue'])"
                icon="chart"
                tone="violet"
            />
            <x-commercial.stat-card
                label="CA encaissé"
                :value="$eur($total['collected'])"
                icon="clipboard-check"
                tone="emerald"
                :description="'Reste à encaisser : '.$eur($total['revenue'] - $total['collected'])"
            />
            <x-commercial.stat-card
                label="Demandes en attente"
                :value="$pendingRequestsCount"
                icon="inbox"
                tone="amber"
            />
        </section>

        {{-- Aide : réserver à la place de quelqu'un --}}
        <div class="flex items-start gap-3 rounded-2xl border border-blue-100 bg-blue-50/60 px-5 py-4 text-sm text-slate-700">
            <x-admin.icon name="user-plus" class="mt-0.5 size-5 shrink-0 text-[#2563EB]" />
            <p>
                <span class="font-semibold text-slate-900">Réserver à la place de quelqu'un :</span>
                cliquez sur un créneau libre, puis choisissez <strong>« Un exposant inscrit »</strong> (s'il n'a pas réussi à remplir le formulaire) ou <strong>« Une autre personne »</strong> (société ou intervenant non exposant, avec ses coordonnées). Vous pouvez aussi déposer son support une fois le créneau réservé.
            </p>
        </div>

        {{-- Légende --}}
        <div class="flex flex-wrap items-center gap-3 text-xs font-semibold text-slate-600" aria-label="Légende des statuts">
            @foreach ($statuses as $status)
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 {{ $status->pillClasses() }}">{{ $status->label() }}</span>
            @endforeach
        </div>

        {{-- Planning par jour --}}
        @forelse ($planning as $date => $bySpace)
            @php
                $day = \Illuminate\Support\Carbon::parse($date)->locale('fr');
                $dayStats = collect($stats['days'])->first(fn ($d) => $d['date']->toDateString() === $date);
            @endphp

            <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6" aria-labelledby="day-{{ $date }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 id="day-{{ $date }}" class="text-lg font-bold capitalize text-slate-900">{{ $day->isoFormat('dddd D MMMM YYYY') }}</h2>
                    @if ($dayStats)
                        <span class="rounded-xl bg-blue-50 px-3 py-1.5 text-sm font-bold text-blue-700">
                            {{ $dayStats['sold'] }} / {{ $dayStats['total'] }} vendus · {{ $pct($dayStats['fill_rate']) }} %
                        </span>
                    @endif
                </div>

                <div class="mt-5 grid gap-6 lg:grid-cols-2">
                    @foreach ($spaces as $space)
                        @php $spaceSlots = $bySpace[$space->id] ?? collect(); @endphp

                        <div>
                            <h3 class="flex items-center justify-between text-sm font-bold text-slate-700">
                                {{ $space->type->label() }}
                                <span class="font-semibold text-slate-400">{{ $space->type->value === 'conference' ? '1 h · 1 500 € HT' : '2 h · 1 000 € HT' }}</span>
                            </h3>

                            @if ($spaceSlots->isEmpty())
                                <p class="mt-3 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">Pas de créneau ce jour.</p>
                            @else
                                <ul class="mt-3 space-y-2.5">
                                    @foreach ($spaceSlots as $index => $slot)
                                        @if ($index > 0 && $spaceSlots[$index - 1]->end_label !== $slot->start_label)
                                            <li class="rounded-lg border border-dashed border-slate-200 px-4 py-1.5 text-center text-xs font-semibold text-slate-400">
                                                Pause {{ $spaceSlots[$index - 1]->end_label }} – {{ $slot->start_label }}
                                            </li>
                                        @endif

                                        <li>
                                            <button
                                                type="button"
                                                data-slot-dialog="slot-dialog-{{ $slot->id }}"
                                                class="w-full rounded-xl border-2 px-4 py-3 text-left transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 {{ $slot->status->tileClasses() }}"
                                            >
                                                <span class="flex items-center justify-between gap-3">
                                                    <span class="text-sm font-bold">{{ $slot->time_range }}</span>
                                                    <span class="flex items-center gap-2">
                                                        @if ($slot->support_path)
                                                            <span class="rounded-full bg-white/70 px-2 py-0.5 text-[11px] font-semibold text-slate-600">Support ✓</span>
                                                        @endif
                                                        <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $slot->status->pillClasses() }}">{{ $slot->status->label() }}</span>
                                                    </span>
                                                </span>
                                                <span class="mt-1 block text-xs font-semibold opacity-70">{{ $slot->code }}</span>
                                                <span class="mt-1 block truncate text-sm font-semibold">
                                                    {{ $slot->holder_name ?? 'Libre' }}
                                                    @if ($slot->is_external)
                                                        <span class="font-normal opacity-60">(non exposant)</span>
                                                    @endif
                                                </span>
                                                @if ($slot->title)
                                                    <span class="mt-0.5 block truncate text-xs opacity-80">{{ $slot->title }}</span>
                                                @endif
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <x-admin.empty-state
                title="Aucun créneau"
                description="Lancez « php artisan db:seed --class=ConferenceLoungeSeeder » pour créer les créneaux du salon."
            />
        @endforelse
    </div>

    {{-- Fenêtres d'édition des créneaux --}}
    @foreach ($slots as $slot)
        @php
            $useOld = $reopenId === $slot->id;
            $value = fn (string $field) => $useOld ? old($field) : $slot->{$field};
            $currentStatus = $useOld ? old('status') : $slot->status->value;
            $holderType = $useOld
                ? old('holder_type', 'exhibitor')
                : ($slot->is_external ? 'external' : 'exhibitor');
            $fieldError = fn (string $field) => $useOld ? $slotErrors->first($field) : null;
        @endphp

        <dialog
            id="slot-dialog-{{ $slot->id }}"
            class="m-auto w-[calc(100%-2rem)] max-w-xl rounded-2xl p-0 shadow-2xl backdrop:bg-slate-900/50"
            aria-labelledby="slot-dialog-title-{{ $slot->id }}"
        >
            <form method="POST" action="{{ route('commercial.slots.update', $slot) }}" class="space-y-4 p-6" @if (! $slot->status->isTaken()) data-free-slot @endif>
                @csrf
                @method('PUT')
                <input type="hidden" name="slot_id" value="{{ $slot->id }}">

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 id="slot-dialog-title-{{ $slot->id }}" class="text-lg font-bold text-slate-900">Créneau {{ $slot->code }}</h2>
                        <p class="mt-1 text-sm capitalize text-slate-500">
                            {{ $slot->space->type->label() }} · {{ $slot->date->locale('fr')->isoFormat('dddd D MMMM') }} · {{ $slot->time_range }} · {{ $eur($slot->price_excl_tax) }}
                        </p>
                    </div>
                    <button type="button" data-close-dialog class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Fermer">✕</button>
                </div>

                @unless ($slot->status->isTaken())
                    <p class="rounded-xl bg-blue-50 px-4 py-3 text-sm text-blue-800">
                        Ce créneau est libre. Vous pouvez le réserver directement pour un exposant (par exemple s'il n'a pas réussi à remplir le formulaire) ou pour une autre personne qui n'est pas exposante.
                    </p>
                @endunless

                <label class="block text-sm font-semibold text-slate-700">
                    Statut
                    <select name="status" data-status-select class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($currentStatus === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </label>

                {{-- Pour qui ? --}}
                <fieldset data-holder>
                    <legend class="text-sm font-semibold text-slate-700">Réservé pour</legend>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        @foreach (['exhibitor' => ['Un exposant inscrit', 'Choisi dans la liste des exposants'], 'external' => ['Une autre personne', 'Société ou intervenant non exposant']] as $type => [$label, $hint])
                            <label class="flex cursor-pointer items-start gap-2 rounded-xl border-2 border-slate-200 p-3 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/60">
                                <input type="radio" name="holder_type" value="{{ $type }}" @checked($holderType === $type) class="mt-0.5 text-blue-600">
                                <span>
                                    <span class="block text-sm font-bold text-slate-800">{{ $label }}</span>
                                    <span class="block text-xs font-normal text-slate-500">{{ $hint }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    {{-- Exposant inscrit --}}
                    <div data-holder-panel="exhibitor" @class(['mt-3', 'hidden' => $holderType !== 'exhibitor'])>
                        <label class="block text-sm font-semibold text-slate-700">
                            Exposant
                            <select name="exhibitor_id" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                                <option value="">— Choisir un exposant —</option>
                                @foreach ($exhibitors as $exhibitor)
                                    <option value="{{ $exhibitor->id }}" @selected((int) $value('exhibitor_id') === $exhibitor->id)>{{ $exhibitor->company_name }}</option>
                                @endforeach
                            </select>
                        </label>
                        @if ($fieldError('exhibitor_id'))<p class="mt-1 text-sm text-red-600">{{ $fieldError('exhibitor_id') }}</p>@endif
                        <p class="mt-1 text-xs text-slate-500">L'exposant reçoit la confirmation par e-mail et retrouve le créneau dans son espace.</p>
                    </div>

                    {{-- Personne non exposante --}}
                    <div data-holder-panel="external" @class(['mt-3 grid gap-3 sm:grid-cols-2', 'hidden' => $holderType !== 'external'])>
                        <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">
                            Nom / société
                            <input type="text" name="exhibitor_name" value="{{ $value('exhibitor_name') }}" maxlength="255" placeholder="Ex. : Bureau d'études Atlas Froid" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                            @if ($fieldError('exhibitor_name'))<span class="mt-1 block text-sm font-normal text-red-600">{{ $fieldError('exhibitor_name') }}</span>@endif
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">
                            Personne à contacter
                            <input type="text" name="contact_name" value="{{ $value('contact_name') }}" maxlength="255" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">
                            Téléphone
                            <input type="tel" name="contact_phone" value="{{ $value('contact_phone') }}" maxlength="40" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                        </label>
                        <label class="block text-sm font-semibold text-slate-700 sm:col-span-2">
                            E-mail
                            <input type="email" name="contact_email" value="{{ $value('contact_email') }}" maxlength="255" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                            @if ($fieldError('contact_email'))<span class="mt-1 block text-sm font-normal text-red-600">{{ $fieldError('contact_email') }}</span>@endif
                            <span class="mt-1 block text-xs font-normal text-slate-500">Facultatif, mais nécessaire pour envoyer la confirmation et les rappels (J-7 et veille).</span>
                        </label>
                    </div>
                </fieldset>

                <label class="block text-sm font-semibold text-slate-700">
                    Titre de la présentation
                    <input type="text" name="title" value="{{ $value('title') }}" maxlength="255" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                </label>

                <label class="block text-sm font-semibold text-slate-700">
                    Résumé <span class="font-normal text-slate-400">(5 lignes max.)</span>
                    <textarea name="summary" rows="5" maxlength="1000" class="mt-1 w-full rounded-xl border-slate-300 text-sm">{{ $value('summary') }}</textarea>
                </label>

                <label class="block text-sm font-semibold text-slate-700">
                    Intervenants
                    <input type="text" name="speakers" value="{{ $value('speakers') }}" maxlength="1000" class="mt-1 w-full rounded-xl border-slate-300 text-sm">
                </label>

                <label class="block text-sm font-semibold text-slate-700">
                    Notes internes
                    <textarea name="notes" rows="2" maxlength="2000" class="mt-1 w-full rounded-xl border-slate-300 text-sm">{{ $value('notes') }}</textarea>
                </label>

                <p class="text-xs text-slate-500">
                    Un créneau déjà attribué doit d'abord repasser en « Disponible » avant d'être donné à quelqu'un d'autre. Passer en « Disponible » efface le titulaire et son support.
                </p>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" data-close-dialog class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Annuler</button>
                    <button type="submit" data-submit-label class="rounded-xl bg-[#2563EB] px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-700">
                        {{ $slot->status->isTaken() ? 'Enregistrer' : 'Réserver' }}
                    </button>
                </div>
            </form>

            {{-- Support de présentation : le commercial peut le déposer à la place du titulaire --}}
            @if ($slot->status->isSold())
                <div class="border-t border-slate-100 bg-slate-50 px-6 py-5">
                    <p class="text-sm font-bold text-slate-800">Support de présentation</p>
                    @if ($slot->support_path)
                        <p class="mt-1 text-sm text-slate-600">
                            Reçu le {{ $slot->support_uploaded_at?->format('d/m/Y à H:i') }} :
                            <a href="{{ route('commercial.slots.support', $slot) }}" class="font-semibold text-[#2563EB] hover:underline">{{ $slot->support_original_name ?? 'télécharger' }}</a>
                        </p>
                    @else
                        <p class="mt-1 text-sm text-amber-700">Pas encore reçu. Si le titulaire vous l'a transmis (par e-mail, clé USB…), déposez-le ici.</p>
                    @endif

                    <form method="POST" action="{{ route('commercial.slots.support.store', $slot) }}" enctype="multipart/form-data" class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                        @csrf
                        <input type="hidden" name="slot_id" value="{{ $slot->id }}">
                        <input type="file" name="support" accept=".ppt,.pptx,.pdf" required class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:text-sm file:font-semibold file:text-blue-700 file:shadow-sm file:ring-1 file:ring-blue-200">
                        <button type="submit" class="shrink-0 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-900">
                            {{ $slot->support_path ? 'Remplacer' : 'Déposer' }}
                        </button>
                    </form>
                    <p class="mt-1 text-xs text-slate-500">PPT, PPTX ou PDF — 20 Mo maximum.</p>
                    @if ($useOld && $errors->has('support'))
                        <p class="mt-1 text-sm font-semibold text-red-600">{{ $errors->first('support') }}</p>
                    @endif
                </div>
            @endif
        </dialog>
    @endforeach

    <script>
        document.querySelectorAll('[data-slot-dialog]').forEach((button) => {
            button.addEventListener('click', () => document.getElementById(button.dataset.slotDialog)?.showModal());
        });
        document.querySelectorAll('[data-close-dialog]').forEach((button) => {
            button.addEventListener('click', () => button.closest('dialog')?.close());
        });

        // « Réservé pour » : affiche les champs de l'exposant inscrit ou de la personne externe.
        document.querySelectorAll('[data-holder]').forEach((fieldset) => {
            const sync = () => {
                const type = fieldset.querySelector('input[name="holder_type"]:checked')?.value;
                fieldset.querySelectorAll('[data-holder-panel]').forEach((panel) => {
                    panel.classList.toggle('hidden', panel.dataset.holderPanel !== type);
                });
            };
            fieldset.querySelectorAll('input[name="holder_type"]').forEach((radio) => radio.addEventListener('change', sync));
        });

        // Créneau libre : dès qu'un titulaire est choisi ou saisi, le statut passe à « Réservé ».
        document.querySelectorAll('dialog form[data-free-slot]').forEach((form) => {
            const status = form.querySelector('[data-status-select]');
            const holderInputs = form.querySelectorAll('select[name="exhibitor_id"], input[name="exhibitor_name"]');
            const autoReserve = (event) => {
                if (event.target.value.trim() !== '' && status.value === 'available') {
                    status.value = 'reserved';
                }
            };
            holderInputs.forEach((input) => {
                input.addEventListener('change', autoReserve);
                input.addEventListener('input', autoReserve);
            });
        });

        @if ($reopenId && ($slotErrors->any() || $errors->has('support')))
            document.getElementById('slot-dialog-{{ $reopenId }}')?.showModal();
        @endif
    </script>
</x-commercial.layout>
