
<x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">

@php
    $labels = [
        'pending' => 'En attente',
        'in_progress' => 'En cours',
        'resolved' => 'Résolue',
    ];

    $colors = [
        'pending' => 'bg-amber-100 text-amber-700',
        'in_progress' => 'bg-blue-100 text-blue-700',
        'resolved' => 'bg-emerald-100 text-emerald-700',
    ];

    $input = 'mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500';
@endphp

<div class="space-y-6">

    <header>
        <p class="text-sm font-semibold text-blue-600">
            Espace exposant / Assistance
        </p>

        <h1 class="mt-2 text-3xl font-extrabold text-[#0F2347]">
            Centre d'assistance
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Contactez votre commercial et suivez vos demandes.
        </p>
    </header>

    @if ($errors->any())
        <div role="alert"
             class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(300px,1fr)]">

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h2 class="text-xl font-extrabold text-[#0F2347]">
                Nouvelle demande d'assistance
            </h2>

            <p class="mt-2 text-sm text-slate-500">
                Votre demande sera envoyée à votre commercial référent.
            </p>

            <form method="POST"
                  action="{{ route('exhibitor.support.store') }}"
                  class="mt-6 space-y-5">

                @csrf

                <div>
                    <label for="subject"
                           class="text-sm font-semibold text-slate-700">
                        Sujet *
                    </label>

                    <input id="subject"
                           name="subject"
                           required
                           maxlength="255"
                           value="{{ old('subject') }}"
                           placeholder="Ex : Problème avec mes badges"
                           class="{{ $input }}">
                </div>

                <div>
                    <label for="category"
                           class="text-sm font-semibold text-slate-700">
                        Catégorie *
                    </label>

                    <select id="category"
                            name="category"
                            required
                            class="{{ $input }}">
                        <option value="">Choisir une catégorie</option>

                        @foreach ([
                            'badges' => 'Badges',
                            'stand' => 'Mon stand',
                            'equipment' => 'Équipements',
                            'account' => 'Compte / Accès',
                            'other' => 'Autre',
                        ] as $key => $value)
                            <option value="{{ $key }}"
                                @selected(old('category') === $key)>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="message"
                           class="text-sm font-semibold text-slate-700">
                        Votre message *
                    </label>

                    <textarea id="message"
                              name="message"
                              rows="6"
                              required
                              maxlength="5000"
                              placeholder="Décrivez votre problème..."
                              class="{{ $input }}">{{ old('message') }}</textarea>
                </div>

                <button type="submit"
                        @disabled(!$exhibitor->commercial)
                        class="rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white hover:bg-blue-700 disabled:opacity-50">
                    Envoyer ma demande
                </button>
            </form>

        </section>

        <aside class="space-y-5">

            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="font-extrabold text-[#0F2347]">
                    Mon commercial référent
                </h2>

                @if ($exhibitor->commercial)
                    <div class="mt-5 flex items-center gap-3">

                        @if ($exhibitor->commercial->profile_photo_url)
                            <img src="{{ $exhibitor->commercial->profile_photo_url }}"
                                 alt="Photo du commercial"
                                 class="size-14 rounded-full object-cover">
                        @else
                            <span class="grid size-14 place-items-center rounded-full bg-blue-100 font-bold text-blue-700">
                                {{ $exhibitor->commercial->initials }}
                            </span>
                        @endif

                        <div>
                            <p class="font-bold text-[#0F2347]">
                                {{ $exhibitor->commercial->name }}
                            </p>
                            <p class="text-xs text-slate-500">
                                Responsable de votre dossier
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2">
                        @if ($exhibitor->commercial->email)
                            <a href="mailto:{{ $exhibitor->commercial->email }}"
                               class="rounded-lg border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-700">
                                Email
                            </a>
                        @endif

                        @if ($exhibitor->commercial->phone)
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $exhibitor->commercial->phone) }}"
                               class="rounded-lg border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-700">
                                Appeler
                            </a>
                        @endif
                    </div>
                @else
                    <p class="mt-4 text-sm text-amber-700">
                        Aucun commercial attribué.
                    </p>
                @endif
            </section>

            <section class="rounded-2xl border border-blue-100 bg-blue-50 p-6">
                <h2 class="font-bold text-[#0F2347]">
                    Informations
                </h2>

                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Vous recevrez les réponses dans cette plateforme.
                    Une demande peut être En attente, En cours ou Résolue.
                </p>
            </section>

        </aside>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-xl font-extrabold text-[#0F2347]">
                Mes demandes d'assistance
            </h2>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse ($tickets as $ticket)
                <a href="{{ route('exhibitor.support.show', $ticket) }}"
                   class="flex flex-wrap items-center justify-between gap-4 px-6 py-5 hover:bg-blue-50/50">

                    <div>
                        <h3 class="font-bold text-[#0F2347]">
                            #{{ $ticket->id }} — {{ $ticket->subject }}
                        </h3>

                        <p class="mt-2 text-xs text-slate-500">
                            {{ $ticket->created_at->format('d/m/Y H:i') }}
                            · {{ $ticket->messages_count }} message(s)
                        </p>
                    </div>

                    <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ $colors[$ticket->status] ?? $colors['pending'] }}">
                        {{ $labels[$ticket->status] ?? 'En attente' }}
                    </span>
                </a>
            @empty
                <div class="p-10 text-center text-sm text-slate-500">
                    Aucune demande d'assistance pour le moment.
                </div>
            @endforelse
        </div>

        @if ($tickets->hasPages())
            <div class="border-t border-slate-100 p-5">
                {{ $tickets->links() }}
            </div>
        @endif
    </section>

</div>

</x-exhibitor.layout>
