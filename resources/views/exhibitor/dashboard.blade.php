
<x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">

<div class="space-y-6">

    <!-- HEADER -->
    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">

        <div>
            <nav class="mb-3 text-sm text-slate-500">
                <span>Espace exposant</span>
                <span class="mx-2">/</span>
                <span class="font-semibold text-[#2563EB]">
                    Vue globale
                </span>
            </nav>

            <div class="flex items-center gap-3">
                <span class="grid size-12 place-items-center rounded-2xl bg-blue-100 text-blue-700">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         class="size-6" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                </span>

                <div>
                    <h1 class="text-2xl font-extrabold text-[#0F2347] sm:text-3xl">
                        Vue globale
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Bonjour, {{ $user->name }}. Bienvenue dans votre espace exposant.
                    </p>
                </div>
            </div>
        </div>

        <!-- EVENT BANNER -->
        <div class="min-w-0 rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 to-white px-5 py-4 shadow-sm xl:min-w-80">
            <div class="flex items-center gap-3">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-700">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         class="size-6" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M7 3v4M17 3v4M3 10h18"/>
                    </svg>
                </span>

                <div class="min-w-0">
                    <p class="font-extrabold text-[#0F2347]">
                        {{ $salon?->name ?? 'Refrigair Expo 2027' }}
                    </p>

                    @if ($salon)
                        <p class="mt-1 text-xs leading-5 text-slate-600">
                            {{ $salon->start_date?->format('d/m/Y') }}
                            —
                            {{ $salon->end_date?->format('d/m/Y') }}

                            @if ($salon->venue)
                                · {{ $salon->venue }}
                            @endif

                            @if ($salon->city)
                                · {{ $salon->city }}
                            @endif
                        </p>
                    @else
                        <p class="mt-1 text-xs text-slate-500">
                            Informations du prochain salon à venir.
                        </p>
                    @endif
                </div>
            </div>
        </div>

    </div>

    @if ($exhibitor)

        @php
            $authorized = (int) $exhibitor->authorized_badges;
            $registered = (int) $badgeCount;
            $remaining = max(0, $authorized - $registered);

            $progress = $authorized > 0
                ? min(100, round($registered / $authorized * 100))
                : 0;

            $companyInitials = collect(
                preg_split('/\s+/', trim($exhibitor->company_name))
            )
                ->filter()
                ->take(2)
                ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                ->implode('') ?: 'E';
        @endphp

        <!-- KPIs -->
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
                 aria-label="Statistiques de votre participation">

            <!-- BADGES -->
            <article class="flex items-center gap-4 rounded-2xl border border-blue-100 bg-blue-50/60 p-5 shadow-sm">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-violet-100 text-violet-600">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         aria-hidden="true">
                        <circle cx="9" cy="8" r="3"/>
                        <path d="M3 20v-2a6 6 0 0 1 12 0v2M17 5a3 3 0 0 1 0 6M17 14a5 5 0 0 1 4 5v1"/>
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-slate-600">Mes badges</p>
                    <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                        {{ $registered }}
                        <span class="text-xl font-medium text-slate-400">/ {{ $authorized }}</span>
                    </p>
                </div>
            </article>

            <!-- ALL REQUESTS -->
            <article class="flex items-center gap-4 rounded-2xl border border-amber-100 bg-amber-50/60 p-5 shadow-sm">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-amber-100 text-amber-600">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         aria-hidden="true">
                        <rect x="4" y="7" width="16" height="14" rx="2"/>
                        <path d="M9 7V5a3 3 0 0 1 6 0v2"/>
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-slate-600">Demandes d'équipements</p>
                    <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                        {{ $requestsCount }}
                    </p>
                </div>
            </article>

            <!-- APPROVED -->
            <article class="flex items-center gap-4 rounded-2xl border border-emerald-100 bg-emerald-50/60 p-5 shadow-sm">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="m7 12 3 3 6-6"/>
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-slate-600">Demandes approuvées</p>
                    <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                        {{ $approvedRequests }}
                    </p>
                </div>
            </article>

            <!-- PENDING -->
            <article class="flex items-center gap-4 rounded-2xl border border-violet-100 bg-violet-50/60 p-5 shadow-sm">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-violet-100 text-violet-600">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v5l3 3"/>
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-slate-600">En attente</p>
                    <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                        {{ $pendingRequests }}
                    </p>
                </div>
            </article>

        </section>

        <!-- COMPANY + STAND + QUOTA -->
        <section class="grid items-stretch gap-5 xl:grid-cols-12">

            <!-- COMPANY -->
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-5">

                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-extrabold text-[#0F2347]">
                        Mon entreprise
                    </h2>

                    <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                        Exposant
                    </span>
                </div>

                <div class="mt-5 flex items-center gap-4 rounded-xl bg-[#F5F7FB] p-4">

                    @if ($exhibitor->logo)
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($exhibitor->logo) }}"
                            alt="Logo {{ $exhibitor->company_name }}"
                            class="size-20 shrink-0 rounded-xl border border-slate-200 bg-white object-contain p-2"
                        >
                    @else
                        <span class="grid size-20 shrink-0 place-items-center rounded-xl bg-white text-xl font-extrabold text-blue-700">
                            {{ $companyInitials }}
                        </span>
                    @endif

                    <div class="min-w-0">
                        <h3 class="break-words text-lg font-extrabold text-[#0F2347]">
                            {{ $exhibitor->company_name }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Entreprise participante
                        </p>
                    </div>
                </div>

                <dl class="mt-5 divide-y divide-slate-100">
                    <div class="flex justify-between gap-3 py-3 text-sm">
                        <dt class="text-slate-500">Responsable</dt>
                        <dd class="text-right font-semibold text-[#0F2347]">
                            {{ $exhibitor->contact_name ?: '—' }}
                        </dd>
                    </div>

                    <div class="flex justify-between gap-3 py-3 text-sm">
                        <dt class="text-slate-500">Téléphone</dt>
                        <dd class="text-right font-semibold text-[#0F2347]">
                            {{ $exhibitor->phone ?: '—' }}
                        </dd>
                    </div>

                    <div class="flex justify-between gap-3 py-3 text-sm">
                        <dt class="text-slate-500">Email</dt>
                        <dd class="min-w-0 break-all text-right font-semibold text-[#0F2347]">
                            {{ $exhibitor->email ?: '—' }}
                        </dd>
                    </div>
                </dl>

            </article>

            <!-- STAND -->
            <article id="mon-stand"
                     class="scroll-mt-28 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-4">

                <h2 class="text-lg font-extrabold text-[#0F2347]">
                    Mon stand
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Informations de mon stand et de ma participation.
                </p>

                <div class="mt-5 grid grid-cols-2 gap-3 rounded-xl border border-blue-100 bg-blue-50/50 p-4">

                    <div>
                        <p class="text-xs text-slate-500">Numéro</p>
                        <p class="mt-2 text-2xl font-extrabold text-blue-700">
                            {{ $exhibitor->stand_number ?: '—' }}
                        </p>
                    </div>

                    <div class="border-l border-blue-200 pl-4">
                        <p class="text-xs text-slate-500">Superficie</p>
                        <p class="mt-2 text-2xl font-extrabold text-[#0F2347]">
                            {{ $exhibitor->area_sqm !== null
                                ? number_format((float) $exhibitor->area_sqm, 2, ',', ' ') . ' m²'
                                : '—' }}
                        </p>
                    </div>

                </div>

                <div class="mt-5 border-t border-slate-100 pt-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Commercial référent
                    </p>

                    @if ($exhibitor->commercial)
                        <div class="mt-3 flex items-center gap-3">

                            @if ($exhibitor->commercial->profile_photo)
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($exhibitor->commercial->profile_photo) }}"
                                    alt="Photo du commercial"
                                    class="size-11 shrink-0 rounded-full object-cover"
                                >
                            @else
                                <span class="grid size-11 shrink-0 place-items-center rounded-full bg-blue-100 font-bold text-blue-700">
                                    {{ mb_strtoupper(mb_substr($exhibitor->commercial->name, 0, 1)) }}
                                </span>
                            @endif

                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-[#0F2347]">
                                    {{ $exhibitor->commercial->name }}
                                </p>
                                <p class="truncate text-xs text-slate-500">
                                    {{ $exhibitor->commercial->email }}
                                </p>
                            </div>

                            @if ($exhibitor->commercial->email)
                                <a href="mailto:{{ $exhibitor->commercial->email }}"
                                   title="Contacter mon commercial"
                                   class="rounded-lg border border-blue-200 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-50">
                                    Email
                                </a>
                            @endif

                        </div>
                    @else
                        <p class="mt-3 text-sm text-slate-500">
                            Aucun commercial attribué.
                        </p>
                    @endif
                </div>

            </article>

            <!-- QUOTA -->
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-3">

                <h2 class="text-lg font-extrabold text-[#0F2347]">
                    Quota de badges
                </h2>

                <div class="mt-6 flex justify-center">
                    <div class="relative grid size-36 place-items-center rounded-full"
                         style="background: conic-gradient(#2563EB {{ $progress }}%, #E2E8F0 {{ $progress }}% 100%);">

                        <div class="grid size-28 place-items-center rounded-full bg-white">
                            <div class="text-center">
                                <p class="text-3xl font-extrabold text-[#0F2347]">
                                    {{ $progress }}%
                                </p>
                                <p class="text-xs text-slate-500">
                                    Utilisé
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                <p class="mt-5 text-center text-sm text-slate-600">
                    <strong class="text-[#0F2347]">{{ $registered }}</strong>
                    badge(s) enregistré(s) sur
                    <strong class="text-[#0F2347]">{{ $authorized }}</strong>
                    autorisés
                </p>

                <p class="mt-2 text-center text-xs text-slate-500">
                    {{ $remaining }} badge(s) restant(s)
                </p>

                <a href="{{ route('exhibitor.badges.index') }}"
                   class="mt-5 block rounded-xl border border-blue-200 px-4 py-3 text-center text-sm font-bold text-blue-700 hover:bg-blue-50">
                    Gérer mes badges
                </a>

                @if ($remaining > 0)
                    <a href="{{ route('exhibitor.badges.create') }}"
                       class="mt-2 block rounded-xl bg-[#2563EB] px-4 py-3 text-center text-sm font-bold text-white hover:bg-blue-700">
                        + Ajouter un badge
                    </a>
                @endif

            </article>

        </section>

        <!-- REQUESTS + COUNTDOWN -->
        <section class="grid items-stretch gap-5 xl:grid-cols-12">

            <!-- RECENT REQUESTS -->
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-8">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-lg font-extrabold text-[#0F2347]">
                            Mes dernières demandes d'équipements
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Suivi de mes demandes récentes
                        </p>
                    </div>

                    <a href="{{ route('exhibitor.stand-options.index', ['demandes' => 1]) }}#mes-demandes"
                       class="text-sm font-bold text-blue-700 hover:underline">
                        Voir toutes les demandes
                    </a>
                </div>

                <div class="divide-y divide-slate-100 px-6">

                    @forelse ($recentRequests as $order)

                        @php
                            $firstItem = $order->items->first();
                            $otherItems = max(0, $order->items->count() - 1);

                            $statusLabel = match ($order->status) {
                                'approved' => 'Approuvée',
                                'rejected' => 'Refusée',
                                default => 'En attente',
                            };

                            $statusColor = match ($order->status) {
                                'approved' => 'bg-emerald-50 text-emerald-700',
                                'rejected' => 'bg-red-50 text-red-700',
                                default => 'bg-amber-50 text-amber-700',
                            };
                        @endphp

                        <div class="flex flex-col gap-4 py-4 sm:flex-row sm:items-center">

                            <div class="flex min-w-0 flex-1 items-center gap-3">

                                @if ($firstItem?->equipment?->image_path)
                                    <img
                                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($firstItem->equipment->image_path) }}"
                                        alt="{{ $firstItem->equipment_name }}"
                                        class="size-12 shrink-0 rounded-lg border border-slate-200 bg-white object-contain p-1"
                                    >
                                @else
                                    <span class="grid size-12 shrink-0 place-items-center rounded-lg bg-blue-50 text-blue-700">
                                        <svg class="size-6" viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor"
                                             stroke-width="1.8" aria-hidden="true">
                                            <path d="m12 2 9 5-9 5-9-5 9-5ZM3 7v10l9 5 9-5V7M12 12v10"/>
                                        </svg>
                                    </span>
                                @endif

                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-[#0F2347]">
                                        {{ $firstItem?->equipment_name ?? 'Demande d’équipements' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        @if ($firstItem)
                                            {{ $firstItem->quantity }} unité(s)
                                            @if ($otherItems > 0)
                                                · +{{ $otherItems }} autre(s) article(s)
                                            @endif
                                        @else
                                            Demande #{{ $order->id }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-4 sm:justify-end">
                                <span class="whitespace-nowrap text-xs text-slate-500">
                                    {{ $order->created_at?->format('d/m/Y') }}
                                </span>

                                <span class="whitespace-nowrap text-sm font-bold text-[#0F2347]">
                                    {{ number_format((float) $order->total_amount, 2, ',', ' ') }} DH
                                </span>

                                <span class="whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-bold {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>

                        </div>

                    @empty

                        <div class="py-12 text-center">
                            <p class="font-semibold text-slate-700">
                                Aucune demande pour le moment
                            </p>
                            <p class="mt-2 text-sm text-slate-500">
                                Consultez les options disponibles pour votre stand.
                            </p>
                        </div>

                    @endforelse

                </div>

                <div class="border-t border-slate-100 p-5">
                    <a href="{{ route('exhibitor.stand-options.index') }}"
                       class="inline-flex rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-bold text-white hover:bg-blue-700">
                        Consulter les options du stand
                    </a>
                </div>

            </article>

            <!-- COUNTDOWN -->
            <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-4">

                <h2 class="text-lg font-extrabold text-[#0F2347]">
                    Jours avant l'événement
                </h2>

                <div class="mt-6 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 px-5 py-9 text-center">
                    @if ($salon && $daysRemaining !== null)

                        <p class="text-6xl font-extrabold text-[#2563EB]">
                            {{ $daysRemaining }}
                        </p>

                        <p class="mt-3 text-sm font-semibold text-[#0F2347]">
                            {{ $daysRemaining === 0
                                ? 'Le salon a commencé ou commence aujourd’hui'
                                : 'jour(s) restant(s)' }}
                        </p>

                    @else

                        <p class="text-2xl font-bold text-[#0F2347]">
                            À venir
                        </p>

                        <p class="mt-3 text-sm text-slate-600">
                            Les dates du prochain salon seront affichées ici.
                        </p>

                    @endif
                </div>

                <p class="mt-5 text-center text-sm text-slate-500">
                    {{ $salon?->name ?? 'Refrigair Expo 2027' }}
                </p>

                @if ($salon?->start_date)
                    <p class="mt-1 text-center text-xs text-slate-500">
                        Début : {{ $salon->start_date->format('d/m/Y') }}
                    </p>
                @endif

            </article>

        </section>

    @else

        <section class="rounded-2xl border border-amber-200 bg-white p-8 shadow-sm">
            <h2 class="text-xl font-bold text-[#0F2347]">
                Aucune entreprise associée
            </h2>

            <p class="mt-3 text-sm text-slate-500">
                Votre compte n'est lié à aucune fiche exposant.
                Contactez l'organisation du salon.
            </p>
        </section>

    @endif

</div>

</x-exhibitor.layout>
