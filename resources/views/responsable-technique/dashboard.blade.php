<x-technical.layout :user="auth()->user()" title="Tableau de bord">
    @php
        $nameParts = preg_split('/\s+/u', trim((string) auth()->user()->name), -1, PREG_SPLIT_NO_EMPTY);
        $firstName = $nameParts[0] ?? 'Responsable';
        $heroImage = 'assets/images/Refrigairexpo-February-2-4-2027-OFEC-Casablanca-Morocco-afich_refrigair-expo_2027.jpeg';
    @endphp

    <section class="relative isolate mb-7 overflow-hidden rounded-3xl bg-gradient-to-br from-[#0F2347] via-[#17396E] to-[#2563EB] px-6 py-7 text-white shadow-lg shadow-blue-950/15 sm:px-9 sm:py-9">
        <img src="{{ asset($heroImage) }}" alt="" aria-hidden="true" class="absolute inset-y-0 right-0 -z-20 hidden h-full w-[54%] object-cover object-[center_28%] opacity-45 sm:block">
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-[#0F2347] via-[#123365]/95 to-[#1D4F9A]/50"></div>
        <div class="pointer-events-none absolute -right-16 -top-24 -z-10 size-72 rounded-full border border-white/10 sm:right-[28%] sm:size-96"></div>
        <div class="relative max-w-2xl">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-200">Vue d’ensemble · REFRIGAIR EXPO 2027</p>
            <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Bonjour {{ $firstName }},</h2>
            <p class="mt-3 max-w-xl text-sm leading-6 text-blue-100 sm:text-base">Voici le suivi global des équipements, de l’activité commerciale et des conférences.</p>
            <a href="{{ route('responsable-technique.conferences') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-bold text-[#0F2347] shadow-sm transition hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-blue-800"><x-admin.icon name="calendar" class="size-4" />Consulter le planning</a>
        </div>
    </section>

    @php($cards = [
        ['Équipements au catalogue', $stats['equipment_total'], 'Références enregistrées', 'box', 'bg-blue-50 text-blue-700'],
        ['Équipements actifs', $stats['equipment_active'], 'Actuellement actifs', 'layers', 'bg-emerald-50 text-emerald-700'],
        ['Commerciaux actifs', $stats['active_commercials'], 'Comptes activés', 'users', 'bg-indigo-50 text-indigo-700'],
        ['Exposants inscrits', $stats['exhibitors'], 'Fiches enregistrées', 'storefront', 'bg-cyan-50 text-cyan-700'],
        ['Réservations confirmées', $stats['confirmed_reservations'], 'Créneaux réservés ou payés', 'clipboard-check', 'bg-blue-50 text-blue-700'],
        ['Demandes en attente', $stats['pending_requests'], 'À examiner par le responsable', 'inbox', 'bg-amber-50 text-amber-700'],
        ['Créneaux disponibles', $stats['available_slots'], 'Encore libres au planning', 'calendar', 'bg-emerald-50 text-emerald-700'],
        ['Quantité disponible', $stats['available_quantity'], 'Stock des équipements actifs', 'chart', 'bg-violet-50 text-violet-700'],
    ])
    <section aria-label="Indicateurs clés" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as [$label, $value, $hint, $icon, $tone])
            <article class="group flex h-full min-h-[108px] items-center gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md sm:gap-4">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl {{ $tone }} sm:size-12"><x-admin.icon :name="$icon" class="size-5" /></span>
                <div class="min-w-0"><p class="text-2xl font-black leading-none tracking-tight tabular-nums text-[#0F2347] sm:text-3xl">{{ number_format($value, 0, ',', ' ') }}</p><p class="mt-1.5 line-clamp-2 text-xs font-bold leading-4 text-slate-700">{{ $label }}</p><p class="mt-0.5 line-clamp-1 text-[10px] text-slate-500">{{ $hint }}</p></div>
            </article>
        @endforeach
    </section>

    <div class="mt-8 grid items-stretch gap-5 xl:grid-cols-3">
        <section class="flex h-full flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="category-chart-title">
            <div><p class="text-xs font-bold uppercase tracking-wider text-blue-700">Catalogue</p><h2 id="category-chart-title" class="mt-1 text-lg font-extrabold text-[#0F2347]">Équipements par catégorie</h2><p class="mt-1 text-xs text-slate-500">Répartition de toutes les références enregistrées.</p></div>
            @if ($stats['equipment_total'] > 0)
                @php($circumference = 2 * pi() * 47)
                @php($offset = 0)
                <div class="mt-5 flex flex-1 flex-col items-center justify-center gap-5 sm:flex-row sm:justify-start">
                    <div class="relative size-40 shrink-0" role="img" aria-label="{{ $stats['equipment_total'] }} équipements répartis par catégorie">
                        <svg viewBox="0 0 120 120" class="size-full -rotate-90" aria-hidden="true">
                            <circle cx="60" cy="60" r="47" fill="none" stroke="#E2E8F0" stroke-width="13" />
                            @foreach ($equipmentCategories as $category)
                                @php($segment = ($category['percent'] / 100) * $circumference)
                                @if ($segment > 0)
                                    <circle cx="60" cy="60" r="47" fill="none" stroke="{{ $category['color'] }}" stroke-width="13" stroke-dasharray="{{ $segment }} {{ max(0, $circumference - $segment) }}" stroke-dashoffset="{{ -$offset }}" />
                                    @php($offset += $segment)
                                @endif
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center"><span class="text-3xl font-black text-[#0F2347]">{{ number_format($stats['equipment_total']) }}</span><span class="mt-0.5 text-[11px] font-semibold text-slate-500">équipements</span></div>
                    </div>
                    <ul class="grid w-full gap-x-4 gap-y-2.5 text-sm sm:min-w-0">
                        @foreach ($equipmentCategories as $category)
                            <li class="flex min-w-0 items-center gap-2"><span class="size-2.5 shrink-0 rounded-full" style="background-color: {{ $category['color'] }}"></span><span class="min-w-0 flex-1 truncate text-slate-600" title="{{ $category['label'] }}">{{ $category['label'] }}</span><span class="font-bold tabular-nums text-[#0F2347]">{{ number_format($category['total']) }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="flex flex-1 flex-col items-center justify-center py-10 text-center"><span class="grid size-14 place-items-center rounded-2xl bg-slate-50 text-slate-400"><x-admin.icon name="box" class="size-7" /></span><p class="mt-3 text-sm font-bold text-[#0F2347]">Catalogue vide</p><p class="mt-1 text-xs text-slate-500">Les catégories apparaîtront ici dès l’ajout d’équipements.</p></div>
            @endif
        </section>

        <section class="flex h-full flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="commercial-panel-title">
            <div><p class="text-xs font-bold uppercase tracking-wider text-blue-700">Équipe</p><h2 id="commercial-panel-title" class="mt-1 text-lg font-extrabold text-[#0F2347]">Activité commerciale</h2><p class="mt-1 text-xs text-slate-500">Synthèse actuelle des comptes et des exposants associés.</p></div>
            <div class="mt-5 grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-blue-50 p-4"><p class="text-xs font-semibold text-blue-800">Commerciaux actifs</p><p class="mt-1 text-2xl font-black text-[#0F2347]">{{ number_format($stats['active_commercials']) }}</p></div>
                <div class="rounded-xl bg-cyan-50 p-4"><p class="text-xs font-semibold text-cyan-800">Exposants associés</p><p class="mt-1 text-2xl font-black text-[#0F2347]">{{ number_format($stats['exhibitors']) }}</p></div>
            </div>
            <div class="mt-5 flex-1">
                <div class="mb-2 flex items-center justify-between"><h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Commerciaux avec le plus d’exposants</h3><a href="{{ route('responsable-technique.commercials') }}" class="text-xs font-bold text-blue-700 hover:underline">Tous</a></div>
                <div class="divide-y divide-slate-100">
                    @forelse ($commercialPreview as $commercial)
                        <a href="{{ route('responsable-technique.commercial', $commercial) }}" class="flex items-center gap-3 py-2.5 first:pt-1 last:pb-0 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                            <x-profile-avatar :user="$commercial" class="size-9 shrink-0 text-[11px]" />
                            <span class="min-w-0 flex-1 truncate text-sm font-semibold text-[#0F2347]">{{ $commercial->name }}</span>
                            <span class="rounded-lg bg-slate-50 px-2 py-1 text-xs font-bold tabular-nums text-slate-600">{{ number_format($commercial->exhibitors_count) }}</span>
                        </a>
                    @empty
                        <p class="py-7 text-center text-sm text-slate-500">Aucun commercial actif pour le moment.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="flex h-full flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="conference-panel-title">
            <div class="flex items-start justify-between gap-2"><div><p class="text-xs font-bold uppercase tracking-wider text-blue-700">Programme</p><h2 id="conference-panel-title" class="mt-1 text-lg font-extrabold text-[#0F2347]">Conférences & Lounge</h2><p class="mt-1 text-xs text-slate-500">Demandes, réservations et créneaux suivis séparément.</p></div><a href="{{ route('responsable-technique.conferences') }}" aria-label="Ouvrir le planning Conférences & Lounge" class="grid size-9 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700 transition hover:bg-blue-100"><x-admin.icon name="calendar" class="size-4" /></a></div>
            <div class="mt-5 grid flex-1 grid-cols-2 gap-3">
                @foreach ([['Demandes en attente', $stats['pending_requests'], 'bg-amber-50 text-amber-800'], ['Réservations confirmées', $stats['confirmed_reservations'], 'bg-blue-50 text-blue-800'], ['Réservations directes', $stats['direct_reservations'], 'bg-violet-50 text-violet-800'], ['Créneaux disponibles', $stats['available_slots'], 'bg-emerald-50 text-emerald-800']] as [$label, $value, $tone])
                    <div class="flex min-h-24 flex-col justify-between rounded-xl p-3.5 {{ $tone }}"><p class="text-xs font-semibold">{{ $label }}</p><p class="mt-2 text-2xl font-black tabular-nums">{{ number_format($value) }}</p></div>
                @endforeach
            </div>
            <a href="{{ route('responsable-technique.conferences') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-bold text-blue-700 hover:underline">Voir le planning <span aria-hidden="true">→</span></a>
        </section>
    </div>

    <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm" aria-labelledby="latest-equipment-title">
        <div class="flex flex-wrap items-end justify-between gap-3 border-b border-slate-100 px-5 py-5 sm:px-6"><div><p class="text-xs font-bold uppercase tracking-wider text-blue-700">Catalogue</p><h2 id="latest-equipment-title" class="mt-1 text-lg font-extrabold text-[#0F2347]">Derniers équipements du catalogue</h2><p class="mt-1 text-xs text-slate-500">Les six références ajoutées le plus récemment.</p></div><a href="{{ route('responsable-technique.equipment') }}" class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-bold text-blue-700 transition hover:bg-blue-50">Voir le catalogue <span aria-hidden="true">→</span></a></div>
        <div class="grid gap-3 p-4 sm:grid-cols-2 sm:p-5 xl:grid-cols-3">
            @forelse ($latestEquipment as $item)
                <article class="flex min-w-0 items-center gap-3 rounded-xl border border-slate-100 p-3 transition hover:border-blue-100 hover:bg-slate-50/60">
                    <div class="grid size-[68px] shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-50">@if ($item->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->image_path))<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="{{ $item->name }}" class="size-full object-contain p-1.5" loading="lazy">@else<span class="text-slate-300" role="img" aria-label="Aucune image disponible"><x-admin.icon name="box" class="size-7" /></span>@endif</div>
                    <div class="min-w-0 flex-1"><h3 class="truncate text-sm font-bold text-[#0F2347]" title="{{ $item->name }}">{{ $item->name }}</h3><p class="mt-1 truncate text-xs text-slate-500">{{ $item->category ?: 'Sans catégorie' }}</p><span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold {{ $item->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $item->is_active ? 'Actif' : 'Inactif' }}</span></div>
                </article>
            @empty
                <div class="col-span-full rounded-xl bg-slate-50 px-4 py-10 text-center"><span class="mx-auto grid size-12 place-items-center rounded-xl bg-white text-slate-400"><x-admin.icon name="box" class="size-6" /></span><p class="mt-3 text-sm font-bold text-[#0F2347]">Aucun équipement dans le catalogue</p><p class="mt-1 text-xs text-slate-500">Les équipements apparaîtront ici après leur enregistrement.</p></div>
            @endforelse
        </div>
    </section>
</x-technical.layout>
