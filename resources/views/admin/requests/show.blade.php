
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    @php
        $statusLabel = match ($equipmentRequest->status) {
            'approved' => 'Approuvée',
            'rejected' => 'Refusée',
            default => 'En attente',
        };

        $statusClasses = match ($equipmentRequest->status) {
            'approved' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'rejected' => 'border-red-200 bg-red-50 text-red-700',
            default => 'border-amber-200 bg-amber-50 text-amber-700',
        };

        $exhibitor = $equipmentRequest->exhibitor;
        $commercial = $exhibitor?->commercial;
    @endphp

    <div class="space-y-7">

        {{-- BREADCRUMB --}}
        <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('admin.index') }}"
               class="transition hover:text-blue-600">
                Administration
            </a>

            <span>/</span>

            <a href="{{ route('admin.demandes.index') }}"
               class="transition hover:text-blue-600">
                Demandes d'équipements
            </a>

            <span>/</span>

            <span class="font-semibold text-[#0F2347]">
                Demande #{{ $equipmentRequest->id }}
            </span>
        </nav>

        {{-- HEADER --}}
        <header class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-start gap-4">

                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                    <svg class="size-7"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         stroke-linecap="round"
                         stroke-linejoin="round">
                        <rect x="4" y="3" width="16" height="18" rx="2"/>
                        <path d="M8 8h8M8 12h8M8 16h5"/>
                    </svg>
                </span>

                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                        Demande #{{ $equipmentRequest->id }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Envoyée le
                        {{ $equipmentRequest->created_at?->format('d/m/Y à H:i') ?? '—' }}
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Consultation et suivi administratif de la demande.
                    </p>
                </div>

            </div>

            <div class="flex flex-wrap items-center gap-3">

                <span class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold {{ $statusClasses }}">
                    <span class="size-2 rounded-full bg-current"></span>
                    {{ $statusLabel }}
                </span>

                <a href="{{ route('admin.demandes.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="size-4"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2">
                        <path d="m12 19-7-7 7-7M5 12h14"/>
                    </svg>

                    Retour
                </a>

            </div>

        </header>

        {{-- SUCCESS --}}
        @if (session('status'))
            <div role="status"
                 class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- ERRORS --}}
        @if ($errors->any())
            <div role="alert"
                 class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">

                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach

            </div>
        @endif

        {{-- ADMIN READ-ONLY NOTICE --}}
        <div class="flex items-start gap-3 rounded-2xl border border-blue-100 bg-blue-50 p-5">

            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white text-blue-600">
                <svg class="size-5"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 11v5M12 8h.01"/>
                </svg>
            </span>

            <div>
                <h2 class="text-sm font-bold text-[#0F2347]">
                    Suivi administratif
                </h2>

                <p class="mt-1 text-sm leading-6 text-slate-600">
                    Cette demande est traitée par le commercial responsable
                    de l'exposant. L'administration peut consulter son contenu,
                    son statut et la décision finale, sans intervenir
                    dans sa validation.
                </p>
            </div>

        </div>

        {{-- MAIN GRID --}}
        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">

            {{-- LEFT CONTENT --}}
            <div class="min-w-0 space-y-6">

                {{-- REQUESTED EQUIPMENT --}}
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-100 px-6 py-5">

                        <h2 class="text-lg font-extrabold text-[#0F2347]">
                            Équipements demandés
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Liste des articles sélectionnés par l'exposant.
                        </p>

                    </div>

                    <div class="overflow-x-auto">

                        <table class="w-full min-w-[600px] text-left text-sm">

                            <thead class="bg-[#F5F7FB] text-xs font-bold uppercase tracking-wide text-slate-500">

                                <tr>
                                    <th scope="col" class="px-6 py-4">
                                        Équipement
                                    </th>

                                    <th scope="col" class="px-4 py-4 text-center">
                                        Quantité
                                    </th>

                                    <th scope="col" class="px-4 py-4 text-right">
                                        Prix unitaire
                                    </th>

                                    <th scope="col" class="px-6 py-4 text-right">
                                        Sous-total
                                    </th>
                                </tr>

                            </thead>

                            <tbody class="divide-y divide-slate-100">

                                @forelse ($equipmentRequest->items as $item)

                                    <tr class="transition hover:bg-slate-50/60">

                                        <td class="px-6 py-5">

                                            <p class="font-bold text-[#0F2347]">
                                                {{ $item->equipment_name }}
                                            </p>

                                        </td>

                                        <td class="px-4 py-5 text-center">

                                            <span class="inline-flex min-w-9 items-center justify-center rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-bold text-slate-700">
                                                {{ $item->quantity }}
                                            </span>

                                        </td>

                                        <td class="whitespace-nowrap px-4 py-5 text-right text-slate-600">

                                            {{ number_format((float) $item->unit_price, 2, ',', ' ') }}
                                            DH

                                        </td>

                                        <td class="whitespace-nowrap px-6 py-5 text-right font-extrabold text-[#0F2347]">

                                            {{ number_format((float) $item->subtotal, 2, ',', ' ') }}
                                            DH

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="4"
                                            class="px-6 py-12 text-center text-sm text-slate-500">

                                            Aucun équipement dans cette demande.

                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                    {{-- TOTAL --}}
                    <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                        <div>
                            <p class="text-sm font-bold text-[#0F2347]">
                                Montant total de la demande
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                Selon les prix enregistrés au moment de la demande.
                            </p>
                        </div>

                        <p class="text-2xl font-extrabold text-[#2563EB]">
                            {{ number_format((float) $equipmentRequest->total_amount, 2, ',', ' ') }}
                            DH
                        </p>

                    </div>

                </section>

                {{-- EXHIBITOR NOTES --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                    <div class="flex items-center gap-3">

                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">
                            <svg class="size-5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <path d="M4 5h16v13H8l-4 3V5Z"/>
                                <path d="M8 10h8M8 14h5"/>
                            </svg>
                        </span>

                        <div>
                            <h2 class="text-lg font-extrabold text-[#0F2347]">
                                Commentaire de l'exposant
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Informations supplémentaires.
                            </p>
                        </div>

                    </div>

                    <div class="mt-5 break-words whitespace-pre-wrap rounded-xl bg-[#F5F7FB] p-4 text-sm leading-7 text-slate-600">{{ $equipmentRequest->notes ?: 'Aucun commentaire ajouté.' }}</div>

                </section>

                {{-- DECISION --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                    <div class="flex items-center gap-3">

                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">

                            <svg class="size-5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <path d="M4 7h16M4 17h16"/>
                                <circle cx="8" cy="7" r="2"/>
                                <circle cx="16" cy="17" r="2"/>
                            </svg>

                        </span>

                        <div>
                            <h2 class="text-lg font-extrabold text-[#0F2347]">
                                Suivi de la décision
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Décision du commercial responsable.
                            </p>
                        </div>

                    </div>

                    @if ($equipmentRequest->status === 'pending')

                        <div class="mt-5 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-5">

                            <svg class="mt-0.5 size-5 shrink-0 text-amber-600"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v5l3 2"/>
                            </svg>

                            <div>
                                <h3 class="text-sm font-bold text-amber-900">
                                    En attente de validation
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-amber-800">
                                    Le commercial responsable doit examiner
                                    cette demande et choisir de l'approuver
                                    ou de la refuser.
                                </p>

                            </div>

                        </div>

                    @else

                        <div class="mt-5 rounded-xl border p-5 {{ $statusClasses }}">

                            <p class="text-sm font-extrabold">
                                {{ $statusLabel }}
                            </p>

                            <p class="mt-2 text-sm">
                                Traitée par
                                <strong>
                                    {{ $equipmentRequest->reviewer?->name ?? '—' }}
                                </strong>
                            </p>

                            <p class="mt-1 text-sm">
                                Le
                                {{ $equipmentRequest->reviewed_at?->format('d/m/Y à H:i') ?? '—' }}
                            </p>

                        </div>

                        @if ($equipmentRequest->status === 'rejected')

                            <div class="mt-4 rounded-xl border border-red-100 bg-red-50 p-5">

                                <h3 class="text-sm font-bold text-red-800">
                                    Motif du refus
                                </h3>

                                <p class="mt-2 break-words whitespace-pre-wrap text-sm leading-6 text-red-700">{{ $equipmentRequest->rejection_reason ?: 'Aucun motif renseigné.' }}</p>

                            </div>

                        @endif

                    @endif

                </section>

            </div>

            {{-- RIGHT SIDEBAR --}}
            <aside class="min-w-0 space-y-6">

                {{-- EXHIBITOR INFO --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                    <h2 class="text-lg font-extrabold text-[#0F2347]">
                        Informations exposant
                    </h2>

                    <dl class="mt-5 divide-y divide-slate-100 text-sm">

                        <div class="py-3">
                            <dt class="text-xs text-slate-500">
                                Entreprise
                            </dt>

                            <dd class="mt-1 break-words font-bold text-[#0F2347]">
                                {{ $exhibitor?->company_name ?? '—' }}
                            </dd>
                        </div>

                        <div class="py-3">
                            <dt class="text-xs text-slate-500">
                                Contact
                            </dt>

                            <dd class="mt-1 break-words font-medium text-slate-700">
                                {{ $exhibitor?->contact_name ?? '—' }}
                            </dd>
                        </div>

                        <div class="py-3">
                            <dt class="text-xs text-slate-500">
                                Stand
                            </dt>

                            <dd class="mt-1 font-bold text-slate-700">
                                {{ $exhibitor?->stand_number ?: '—' }}
                            </dd>
                        </div>

                        <div class="py-3">
                            <dt class="text-xs text-slate-500">
                                Commercial responsable
                            </dt>

                            <dd class="mt-1 break-words font-bold text-[#0F2347]">
                                {{ $commercial?->name ?? 'Non attribué' }}
                            </dd>
                        </div>

                    </dl>

                    @if ($exhibitor)
                        <a href="{{ route('admin.exposants.show', $exhibitor) }}"
                           class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-blue-600 transition hover:text-blue-700">

                            Voir fiche exposant

                            <svg class="size-4"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="2">
                                <path d="M5 12h14m-6-6 6 6-6 6"/>
                            </svg>

                        </a>
                    @endif

                </section>

                {{-- REQUEST INFORMATION --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                    <h2 class="text-lg font-extrabold text-[#0F2347]">
                        Informations de la demande
                    </h2>

                    <dl class="mt-5 divide-y divide-slate-100 text-sm">

                        <div class="flex items-center justify-between gap-3 py-3">

                            <dt class="text-slate-500">
                                Référence
                            </dt>

                            <dd class="font-bold text-blue-600">
                                #{{ $equipmentRequest->id }}
                            </dd>

                        </div>

                        <div class="flex items-center justify-between gap-3 py-3">

                            <dt class="text-slate-500">
                                Statut
                            </dt>

                            <dd>
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-bold {{ $statusClasses }}">
                                    {{ $statusLabel }}
                                </span>
                            </dd>

                        </div>

                        <div class="flex items-center justify-between gap-3 py-3">

                            <dt class="text-slate-500">
                                Date de création
                            </dt>

                            <dd class="text-right font-semibold text-[#0F2347]">
                                {{ $equipmentRequest->created_at?->format('d/m/Y') ?? '—' }}
                            </dd>

                        </div>

                        <div class="flex items-center justify-between gap-3 py-3">

                            <dt class="text-slate-500">
                                Nombre d'articles
                            </dt>

                            <dd class="font-bold text-[#0F2347]">
                                {{ $equipmentRequest->items->count() }}
                            </dd>

                        </div>

                        <div class="flex items-center justify-between gap-3 py-3">

                            <dt class="text-slate-500">
                                Quantité totale
                            </dt>

                            <dd class="font-bold text-[#0F2347]">
                                {{ $equipmentRequest->items->sum('quantity') }}
                            </dd>

                        </div>

                        <div class="flex items-center justify-between gap-3 py-3">

                            <dt class="text-slate-500">
                                Date de traitement
                            </dt>

                            <dd class="text-right font-semibold text-[#0F2347]">
                                {{ $equipmentRequest->reviewed_at?->format('d/m/Y') ?? '—' }}
                            </dd>

                        </div>

                    </dl>

                </section>

                {{-- PERMISSIONS --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex items-center gap-3">

                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-600">
                            <svg class="size-5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <rect x="5" y="10" width="14" height="11" rx="2"/>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                            </svg>
                        </span>

                        <div>
                            <p class="text-sm font-bold text-[#0F2347]">
                                Consultation uniquement
                            </p>

                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                La validation est réservée au commercial responsable.
                            </p>
                        </div>

                    </div>

                </section>

            </aside>

        </div>

    </div>

</x-admin.layout>
