@php
    $eur = fn ($value) => number_format((float) $value, 0, ',', ' ').' € HT';
    $pct = fn ($value) => rtrim(rtrim(number_format((float) $value, 1, ',', ' '), '0'), ',');
    $total = $stats['total'];
    $hasFilters = collect($filters)->filter(fn ($v) => filled($v))->isNotEmpty();
@endphp

<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">
    <div class="min-w-0 space-y-7">

        {{-- En-tête --}}
        <header class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-sm font-semibold text-[#2563EB]">Refrigair Expo 2027 · 2 au 4 février 2027</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Conférences & Lounge des Technologies</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-500">
                    Qui a réservé quel créneau, statut de paiement et chiffre d'affaires. Consultation uniquement : l'attribution et les demandes sont gérées par les commerciaux.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.slots.export.pdf', 'fr') }}" class="inline-flex items-center rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700">Programme PDF (FR)</a>
                <a href="{{ route('admin.slots.export.pdf', 'en') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Program PDF (EN)</a>
                <a href="{{ route('admin.slots.export.csv') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Export Excel (CSV)</a>
            </div>
        </header>

        {{-- Indicateurs --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Indicateurs">
            <x-admin.stat-card label="Créneaux vendus" :value="$total['sold'].' / '.$total['total']" icon="calendar"
                :description="$pct($total['fill_rate']).' % de remplissage · '.$total['options'].' en option · '.$total['available'].' disponibles'" />
            <x-admin.stat-card label="CA réservé + payé" :value="$eur($total['revenue'])" icon="chart"
                description="Les options ne sont pas comptées" />
            <x-admin.stat-card label="CA encaissé" :value="$eur($total['collected'])" icon="clipboard-check"
                :description="'Reste à encaisser : '.$eur($total['revenue'] - $total['collected'])" />
            <x-admin.stat-card label="Demandes en attente" :value="$pendingRequestsCount" icon="inbox"
                description="Demandes d'exposants à traiter par les commerciaux" />
        </section>

        {{-- Par espace + par jour --}}
        <section class="grid gap-6 xl:grid-cols-2">
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6">
                <h2 class="text-lg font-bold text-slate-900">Ventes par espace</h2>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($stats['spaces'] as $space)
                        <div class="rounded-xl bg-slate-50 p-4">
                            <dt class="text-xs font-semibold text-slate-500">{{ $space['label'] }}</dt>
                            <dd class="mt-1 text-2xl font-bold text-slate-900">{{ $space['sold'] }} <span class="text-base font-semibold text-slate-400">/ {{ $space['total'] }}</span></dd>
                            <dd class="mt-0.5 text-xs text-slate-500">{{ $eur($space['revenue']) }} · encaissé {{ $eur($space['collected']) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </article>

            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6">
                <h2 class="text-lg font-bold text-slate-900">Remplissage par jour</h2>
                <ul class="mt-4 space-y-3">
                    @forelse ($stats['days'] as $day)
                        <li>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold capitalize text-slate-700">{{ $day['date']->locale('fr')->isoFormat('dddd D MMMM') }}</span>
                                <span class="text-slate-500">{{ $day['sold'] }} / {{ $day['total'] }} · <span class="font-bold text-slate-900">{{ $pct($day['fill_rate']) }} %</span></span>
                            </div>
                            <div class="mt-1.5 h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-blue-600" style="width: {{ $day['fill_rate'] }}%"></div>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Aucun créneau configuré.</li>
                    @endforelse
                </ul>
            </article>
        </section>

        {{-- Qui a réservé --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6" aria-labelledby="holders-title">
            <h2 id="holders-title" class="text-lg font-bold text-slate-900">Qui a réservé ?</h2>
            <p class="mt-1 text-sm text-slate-500">Récapitulatif par exposant de tous les créneaux pris (réservés, payés ou en option).</p>

            @if ($holders->isEmpty())
                <p class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">Aucun créneau réservé pour le moment.</p>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead class="text-xs uppercase text-slate-400">
                            <tr class="border-b border-slate-200">
                                <th class="py-2 pr-3 font-semibold">Exposant</th>
                                <th class="py-2 pr-3 font-semibold">Créneaux</th>
                                <th class="py-2 pr-3 text-center font-semibold">Conférence</th>
                                <th class="py-2 pr-3 text-center font-semibold">Lounge</th>
                                <th class="py-2 pr-3 text-right font-semibold">CA HT</th>
                                <th class="py-2 text-right font-semibold">Encaissé</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($holders as $holder)
                                <tr>
                                    <td class="py-2.5 pr-3 font-semibold text-slate-800">
                                        {{ $holder['name'] }}
                                        @unless ($holder['in_database'])
                                            <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500">non exposant</span>
                                        @endunless
                                    </td>
                                    <td class="py-2.5 pr-3 text-slate-600">{{ implode(', ', $holder['codes']) }}</td>
                                    <td class="py-2.5 pr-3 text-center">{{ $holder['conference'] }}</td>
                                    <td class="py-2.5 pr-3 text-center">{{ $holder['lounge'] }}</td>
                                    <td class="py-2.5 pr-3 text-right font-semibold text-slate-900">
                                        {{ $eur($holder['revenue']) }}
                                        @if ($holder['options'] > 0)
                                            <span class="block text-xs font-normal text-amber-600">+ {{ $holder['options'] }} en option</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 text-right text-emerald-700">{{ $eur($holder['collected']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        {{-- Tous les créneaux --}}
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6" aria-labelledby="slots-list-title">
            <h2 id="slots-list-title" class="text-lg font-bold text-slate-900">Détail des créneaux</h2>

            <form method="GET" action="{{ route('admin.slots.index') }}" class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="Exposant, code, titre…" class="rounded-xl border-slate-300 text-sm xl:col-span-2">
                <select name="space" class="rounded-xl border-slate-300 text-sm">
                    <option value="">Tous les espaces</option>
                    @foreach ($spaceTypes as $type)
                        <option value="{{ $type->value }}" @selected(($filters['space'] ?? '') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
                <select name="date" class="rounded-xl border-slate-300 text-sm">
                    <option value="">Tous les jours</option>
                    @foreach ($days as $day)
                        <option value="{{ $day->toDateString() }}" @selected(($filters['date'] ?? '') === $day->toDateString()) class="capitalize">{{ $day->locale('fr')->isoFormat('dddd D MMMM') }}</option>
                    @endforeach
                </select>
                <select name="status" class="rounded-xl border-slate-300 text-sm">
                    <option value="">Tous les statuts</option>
                    <option value="taken" @selected(($filters['status'] ?? '') === 'taken')>Pris (réservé, payé, option)</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2 sm:col-span-2 xl:col-span-5">
                    <button type="submit" class="rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700">Filtrer</button>
                    @if ($hasFilters)
                        <a href="{{ route('admin.slots.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Réinitialiser</a>
                    @endif
                </div>
            </form>

            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="text-xs uppercase text-slate-400">
                        <tr class="border-b border-slate-200">
                            <th class="py-2 pr-3 font-semibold">Code</th>
                            <th class="py-2 pr-3 font-semibold">Jour & horaire</th>
                            <th class="py-2 pr-3 font-semibold">Espace</th>
                            <th class="py-2 pr-3 font-semibold">Exposant</th>
                            <th class="py-2 pr-3 font-semibold">Présentation</th>
                            <th class="py-2 pr-3 font-semibold">Statut</th>
                            <th class="py-2 pr-3 text-right font-semibold">Prix</th>
                            <th class="py-2 font-semibold">Support</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($slots as $slot)
                            <tr class="align-top">
                                <td class="py-3 pr-3 font-bold text-slate-900">{{ $slot->code }}</td>
                                <td class="py-3 pr-3 text-slate-600">
                                    <span class="capitalize">{{ $slot->date->locale('fr')->isoFormat('ddd D MMM') }}</span><br>{{ $slot->time_range }}
                                </td>
                                <td class="py-3 pr-3 text-slate-600">{{ $slot->space->type->label() }}</td>
                                <td class="py-3 pr-3">
                                    @if ($slot->holder_name)
                                        <span class="font-semibold text-slate-800">{{ $slot->holder_name }}</span>
                                        @if ($slot->exhibitor?->commercial)
                                            <span class="block text-xs text-slate-400">Commercial : {{ $slot->exhibitor->commercial->name }}</span>
                                        @elseif ($slot->is_external)
                                            <span class="block text-xs text-slate-400">Non exposant</span>
                                            @if ($slot->contact_name || $slot->contact_email || $slot->contact_phone)
                                                <span class="block text-xs text-slate-500">
                                                    {{ collect([$slot->contact_name, $slot->contact_email, $slot->contact_phone])->filter()->implode(' · ') }}
                                                </span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-3 text-slate-600">
                                    {{ $slot->title ?? '—' }}
                                    @if ($slot->speakers)
                                        <span class="block text-xs text-slate-400">{{ $slot->speakers }}</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $slot->status->pillClasses() }}">{{ $slot->status->label() }}</span>
                                </td>
                                <td class="py-3 pr-3 text-right whitespace-nowrap text-slate-700">{{ $eur($slot->price_excl_tax) }}</td>
                                <td class="py-3">
                                    @if ($slot->support_path)
                                        <a href="{{ route('admin.slots.support', $slot) }}" class="font-semibold text-[#2563EB] hover:underline">Télécharger</a>
                                    @elseif ($slot->status->isSold())
                                        <span class="text-xs text-amber-600">En attente</span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-6 text-center text-sm text-slate-500">Aucun créneau ne correspond à ces filtres.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-admin.layout>
