
<x-commercial.layout
    :commercial="$commercial"
    :commercial-initials="$commercialInitials"
>

    @php
        $cards = [
            ['Total demandes', $totalRequests, 'inbox', '#2563EB', '#EFF6FF', '#DBEAFE'],
            ['En attente', $pendingCount, 'calendar', '#D97706', '#FFF7ED', '#FFEDD5'],
            ['Approuvées', $approvedCount, 'clipboard-check', '#059669', '#ECFDF5', '#D1FAE5'],
            ['Refusées', $rejectedCount, 'box', '#DC2626', '#FEF2F2', '#FEE2E2'],
        ];

        $statuses = [
            'pending' => [
                'label' => 'En attente',
                'class' => 'bg-amber-50 text-amber-700',
            ],
            'approved' => [
                'label' => 'Approuvée',
                'class' => 'bg-emerald-50 text-emerald-700',
            ],
            'rejected' => [
                'label' => 'Refusée',
                'class' => 'bg-red-50 text-red-700',
            ],
        ];

        $inputClass = 'h-11 min-w-0 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100';
    @endphp

    <div class="min-w-0 space-y-6">

        {{-- BREADCRUMB --}}

        <nav class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">

            <a href="{{ route('commercial.index') }}"
               class="hover:text-blue-600">
                Vue globale
            </a>

            <span>/</span>

            <span class="font-semibold text-blue-600">
                Demandes d'équipements
            </span>

        </nav>

        {{-- HEADER --}}

        <header class="flex flex-wrap items-center justify-between gap-4">


        <a href="{{ route('commercial.equipment-requests.create') }}"
   class="inline-flex w-fit items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">

    <svg class="size-5"
         viewBox="0 0 24 24"
         fill="none"
         stroke="currentColor"
         stroke-width="2"
         stroke-linecap="round"
         stroke-linejoin="round">

        <path d="M12 5v14M5 12h14"/>

    </svg>

    Nouvelle demande

</a>

            <div class="flex items-center gap-4">

                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">

                    <x-admin.icon name="box" class="size-8"/>

                </span>

                <div>

                    <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                        Demandes d'équipements
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Suivez les demandes d'équipements de vos exposants.
                    </p>

                </div>

            </div>

        </header>

        @if ($errors->any())

            <div role="alert"
                 class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">

                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach

            </div>

        @endif

        {{-- STATISTICS --}}

        <section class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-4">

            @foreach ($cards as [$label, $value, $icon, $color, $background, $iconBg])

                <article
                    class="min-w-0 rounded-2xl border border-slate-200 p-5 shadow-sm"
                    style="background:linear-gradient(145deg,#FFFFFF,{{ $background }})"
                >

                    <div class="flex items-center gap-4">

                        <span
                            class="grid size-12 shrink-0 place-items-center rounded-full"
                            style="background:{{ $iconBg }};color:{{ $color }}"
                        >

                            <x-admin.icon
                                :name="$icon"
                                class="size-6"
                            />

                        </span>

                        <div class="min-w-0">

                            <p class="text-sm font-semibold text-[#0F2347]">
                                {{ $label }}
                            </p>

                            <p class="mt-2 text-3xl font-extrabold text-[#0F2347]">{{ $value }}</p>

                        </div>

                    </div>

                </article>

            @endforeach

        </section>

        {{-- REQUESTS LIST --}}

        <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- SECTION HEADER --}}

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">

                <div class="flex items-center gap-3">

                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">

                        <x-admin.icon name="box" class="size-6"/>

                    </span>

                    <div>

                        <h2 class="font-bold text-[#0F2347]">
                            Liste des demandes
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Consultez les équipements demandés par vos exposants.
                        </p>

                    </div>

                </div>

                <span class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">
                    {{ $requests->total() }} demande(s)
                </span>

            </div>

            {{-- SEARCH AND FILTERS --}}

            <form
                method="GET"
                action="{{ route('commercial.equipment-requests.index') }}"
                class="flex flex-col gap-3 border-b border-slate-100 p-5 xl:flex-row"
            >

                <div class="relative min-w-0 flex-1">

                    <label for="search" class="sr-only">
                        Rechercher une demande
                    </label>

                    <svg class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-slate-400"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         stroke-linecap="round"
                         aria-hidden="true">

                        <circle cx="11" cy="11" r="7"/>
                        <path d="m16 16 4 4"/>

                    </svg>

                    <input
                        id="search"
                        name="search"
                        type="search"
                        value="{{ request('search') }}"
                        placeholder="Exposant, stand, équipement..."
                        class="{{ $inputClass }} w-full pl-11"
                    >

                </div>

                <label for="exhibitor_id" class="sr-only">
                    Exposant
                </label>

                <select
                    name="exhibitor_id"
                    id="exhibitor_id"
                    class="{{ $inputClass }} xl:max-w-52"
                >

                    <option value="">
                        Tous les exposants
                    </option>

                    @foreach ($exhibitors as $exhibitor)

                        <option
                            value="{{ $exhibitor->id }}"
                            @selected((int) request('exhibitor_id') === $exhibitor->id)
                        >
                            {{ $exhibitor->company_name }}
                        </option>

                    @endforeach

                </select>

                <label for="status" class="sr-only">
                    Statut
                </label>

                <select
                    name="status"
                    id="status"
                    class="{{ $inputClass }}"
                >

                    <option value="">
                        Tous les statuts
                    </option>

                    <option value="pending"
                            @selected(request('status') === 'pending')>
                        En attente
                    </option>

                    <option value="approved"
                            @selected(request('status') === 'approved')>
                        Approuvées
                    </option>

                    <option value="rejected"
                            @selected(request('status') === 'rejected')>
                        Refusées
                    </option>

                </select>

                <button
                    type="submit"
                    class="h-11 rounded-xl bg-[#2563EB] px-5 text-sm font-semibold text-white transition hover:bg-blue-700"
                >
                    Filtrer
                </button>

                @if (request()->filled('search') || request()->filled('status') || request()->filled('exhibitor_id'))

                    <a href="{{ route('commercial.equipment-requests.index') }}"
                       class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 px-4 text-xs font-semibold text-slate-600 hover:bg-slate-50">

                        Réinitialiser

                    </a>

                @endif

            </form>

            {{-- REQUESTS TABLE --}}

            <div class="overflow-x-auto">

                <table class="w-full min-w-[1050px] text-left text-sm">

                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold text-slate-600">

                        <tr>

                            <th scope="col" class="px-5 py-4">
                                Demande
                            </th>

                            <th scope="col" class="px-4 py-4">
                                Exposant
                            </th>

                            <th scope="col" class="px-4 py-4">
                                Équipements
                            </th>

                            <th scope="col" class="px-4 py-4 text-center">
                                Quantité
                            </th>

                            <th scope="col" class="px-4 py-4">
                                Montant
                            </th>

                            <th scope="col" class="px-4 py-4">
                                Statut
                            </th>

                            <th scope="col" class="px-4 py-4">
                                Date
                            </th>

                            <th scope="col" class="px-5 py-4 text-right">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse ($requests as $equipmentRequest)

                            @php
                                $company = $equipmentRequest->exhibitor;

                                $logoUrl = $company?->logo
                                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($company->logo)
                                    : null;

                                $initials = mb_strtoupper(
                                    mb_substr($company?->company_name ?? 'EX', 0, 2)
                                );

                                $status = $statuses[$equipmentRequest->status] ?? [
                                    'label' => 'Inconnu',
                                    'class' => 'bg-slate-100 text-slate-600',
                                ];

                                $firstItem = $equipmentRequest->items->first();

                                $equipmentImage = $firstItem?->equipment?->image_path
                                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($firstItem->equipment->image_path)
                                    : null;
                            @endphp

                            <tr class="transition hover:bg-slate-50/80">

                                {{-- REQUEST ID --}}

                                <td class="px-5 py-4">

                                    <a
                                        href="{{ route('commercial.equipment-requests.show', $equipmentRequest) }}"
                                        class="whitespace-nowrap text-sm font-bold text-blue-600 hover:underline"
                                    >

                                        #DEM-{{ str_pad((string) $equipmentRequest->id, 4, '0', STR_PAD_LEFT) }}

                                    </a>

                                </td>

                                {{-- EXHIBITOR LOGO --}}

                                <td class="px-4 py-4">

                                    <div class="flex items-center gap-3">

                                        <div class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white">

                                            @if ($logoUrl)

                                                <img
                                                    src="{{ $logoUrl }}"
                                                    alt="Logo {{ $company->company_name }}"
                                                    class="h-full w-full object-contain p-1"
                                                    loading="lazy"
                                                    data-request-image
                                                >

                                                <span class="hidden text-sm font-bold text-blue-700"
                                                      data-image-fallback>
                                                    {{ $initials }}
                                                </span>

                                            @else

                                                <span class="text-sm font-bold text-blue-700">
                                                    {{ $initials }}
                                                </span>

                                            @endif

                                        </div>

                                        <div class="min-w-0">

                                            @if ($company)

                                                <a
                                                    href="{{ route('commercial.exhibitors.show', $company) }}"
                                                    class="block max-w-[160px] truncate font-bold text-[#0F2347] hover:text-blue-600"
                                                >
                                                    {{ $company->company_name }}
                                                </a>

                                            @else

                                                <p>—</p>

                                            @endif

                                            <p class="mt-1 text-xs text-slate-500">

                                                Stand {{ $company?->stand_number ?: '—' }}

                                            </p>

                                        </div>

                                    </div>

                                </td>

                                {{-- EQUIPMENT IMAGE --}}

                                <td class="px-4 py-4">

                                    <div class="flex items-center gap-3">

                                        <div class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50">

                                            @if ($equipmentImage)

                                                <img
                                                    src="{{ $equipmentImage }}"
                                                    alt="{{ $firstItem->equipment_name }}"
                                                    class="h-full w-full object-contain p-1"
                                                    loading="lazy"
                                                    data-request-image
                                                >

                                                <span class="hidden text-slate-400"
                                                      data-image-fallback>

                                                    <x-admin.icon name="box" class="size-6"/>

                                                </span>

                                            @else

                                                <x-admin.icon name="box" class="size-6 text-slate-400"/>

                                            @endif

                                        </div>

                                        <div class="min-w-0">

                                            <p class="max-w-[170px] truncate text-xs font-semibold text-[#0F2347]">

                                                {{ $firstItem?->equipment_name ?? 'Aucun équipement' }}

                                            </p>

                                            @if ($equipmentRequest->items_count > 1)

                                                <p class="mt-1 text-xs font-medium text-blue-600">

                                                    +{{ $equipmentRequest->items_count - 1 }} autre(s)

                                                </p>

                                            @endif

                                        </div>

                                    </div>

                                </td>

                                {{-- QUANTITY --}}

                                <td class="px-4 py-4 text-center">

                                    <span class="font-bold text-[#0F2347]">{{ (int) $equipmentRequest->requested_quantity }}</span>

                                </td>

                                {{-- TOTAL --}}

                                <td class="whitespace-nowrap px-4 py-4">

                                    <span class="font-bold text-[#0F2347]">

                                        {{ number_format((float) $equipmentRequest->total_amount, 2, ',', ' ') }} DH

                                    </span>

                                </td>

                                {{-- STATUS --}}

                                <td class="px-4 py-4">

                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-semibold {{ $status['class'] }}">

                                        <span class="size-1.5 rounded-full bg-current"></span>

                                        {{ $status['label'] }}

                                    </span>

                                </td>

                                {{-- DATE --}}

                                <td class="whitespace-nowrap px-4 py-4">

                                    <p class="text-xs font-semibold text-slate-600">

                                        {{ $equipmentRequest->created_at?->format('d/m/Y') }}

                                    </p>

                                    <p class="mt-1 text-[11px] text-slate-400">

                                        {{ $equipmentRequest->created_at?->format('H:i') }}

                                    </p>

                                </td>

                                {{-- DETAILS --}}

                                <td class="px-5 py-4 text-right">

                                    <a
                                        href="{{ route('commercial.equipment-requests.show', $equipmentRequest) }}"
                                        class="inline-flex items-center gap-2 whitespace-nowrap rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-600 hover:text-white"
                                    >

                                        <svg class="size-4"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="1.8"
                                             aria-hidden="true">

                                            <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z"/>
                                            <circle cx="12" cy="12" r="3"/>

                                        </svg>

                                        Voir

                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="p-12">

                                    <x-admin.empty-state
                                        name="box"
                                        icon="box"
                                        title="Aucune demande trouvée"
                                        description="Les demandes d'équipements de vos exposants apparaîtront ici."
                                    />

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- PAGINATION --}}

            <div class="space-y-4 border-t border-slate-100 p-5">

                @if ($requests->total() > 0)

                    <p class="text-xs text-slate-500">

                        Affichage de {{ $requests->firstItem() }}

                        à {{ $requests->lastItem() }}

                        sur {{ $requests->total() }} demandes.

                    </p>

                @endif

                {{ $requests->links() }}

            </div>

        </section>

        {{-- INFORMATION --}}

        <section class="flex items-start gap-4 rounded-2xl border border-blue-100 bg-blue-50 p-5">

            <svg class="size-6 shrink-0 text-blue-600"
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

                <h3 class="text-sm font-bold text-blue-900">
                    Suivi des demandes
                </h3>

                <p class="mt-1 text-xs leading-6 text-blue-700">
                    Les demandes sont traitées par l'administration du salon.
                    Vous pouvez suivre leur statut et consulter les équipements demandés.
                </p>

            </div>

        </section>

        <footer class="border-t border-slate-200 py-4 text-center text-xs text-slate-400">

            © 2027 Refrigair Expo — Espace Commercial

        </footer>

    </div>

    <script>
        (() => {

            document.querySelectorAll('[data-request-image]')
                .forEach(image => {

                    const fallback = () => {

                        image.classList.add('hidden');

                        image.parentElement
                            .querySelector('[data-image-fallback]')
                            ?.classList.remove('hidden');

                    };

                    image.addEventListener('error', fallback);

                    if (image.complete && image.naturalWidth === 0) {
                        fallback();
                    }

                });

        })();
    </script>

</x-commercial.layout>
