
<x-commercial.layout
    :commercial="$commercial"
    :commercial-initials="$commercialInitials"
>

    @php
        $company = $equipmentRequest->exhibitor;

        $logoUrl = $company?->logo
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($company->logo)
            : null;

        $status = match ($equipmentRequest->status) {
            'approved' => 'Approuvée',
            'rejected' => 'Refusée',
            default => 'En attente',
        };

        $statusClass = match ($equipmentRequest->status) {
            'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-amber-50 text-amber-700 border-amber-200',
        };

        $totalQuantity = $equipmentRequest->items->sum('quantity');

        $reference = '#DEM-'.str_pad(
            (string) $equipmentRequest->id,
            4,
            '0',
            STR_PAD_LEFT
        );

        $steps = [
            [
                'label' => 'Demande envoyée',
                'description' => $equipmentRequest->created_at?->format('d/m/Y à H:i'),
                'done' => true,
            ],
            [
                'label' => 'Traitement administratif',
                'description' => $equipmentRequest->status === 'pending'
                    ? 'En attente de traitement'
                    : 'Demande traitée',
                'done' => $equipmentRequest->status !== 'pending',
            ],
            [
                'label' => $equipmentRequest->status === 'rejected'
                    ? 'Demande refusée'
                    : 'Demande approuvée',
                'description' => $equipmentRequest->reviewed_at?->format('d/m/Y à H:i')
                    ?? 'En attente de décision',
                'done' => $equipmentRequest->status !== 'pending',
            ],
        ];
    @endphp

    <div class="min-w-0 space-y-6">

        {{-- BREADCRUMB --}}

        <nav class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">

            <a href="{{ route('commercial.index') }}"
               class="hover:text-blue-600">
                Vue globale
            </a>

            <span>/</span>

            <a href="{{ route('commercial.equipment-requests.index') }}"
               class="hover:text-blue-600">
                Demandes d'équipements
            </a>

            <span>/</span>

            <span class="font-semibold text-blue-600">
                {{ $reference }}
            </span>

        </nav>

        {{-- HEADER --}}

        <header class="flex flex-wrap items-center justify-between gap-4">

            <div class="flex items-center gap-4">

                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">

                    <x-admin.icon name="box" class="size-8"/>

                </span>

                <div>

                    <h1 class="text-2xl font-extrabold text-[#0F2347] sm:text-3xl">
                        Demande {{ $reference }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Consultez les détails et le suivi de cette demande.
                    </p>

                </div>

            </div>

            <a href="{{ route('commercial.equipment-requests.index') }}"
               class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">

                ← Retour aux demandes

            </a>

        </header>

        @if (session('status'))

    <div
        role="status"
        class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700"
    >

        {{ session('status') }}

    </div>

@endif

        {{-- EXHIBITOR HEADER --}}

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="h-2 bg-gradient-to-r from-[#0F2347] to-[#2563EB]"></div>

            <div class="flex flex-wrap items-center justify-between gap-5 p-6">

                <div class="flex min-w-0 items-center gap-5">

                    <div class="grid size-24 shrink-0 place-items-center overflow-hidden rounded-2xl border border-slate-200 bg-white">

                        @if ($logoUrl)

                            <img
                                src="{{ $logoUrl }}"
                                alt="Logo {{ $company->company_name }}"
                                class="h-full w-full object-contain p-2"
                                data-request-image
                            >

                            <span class="hidden text-2xl font-extrabold text-blue-600"
                                  data-image-fallback>

                                {{ mb_strtoupper(mb_substr($company->company_name, 0, 2)) }}

                            </span>

                        @else

                            <span class="text-2xl font-extrabold text-blue-600">

                                {{ mb_strtoupper(mb_substr($company?->company_name ?? 'EX', 0, 2)) }}

                            </span>

                        @endif

                    </div>

                    <div class="min-w-0">

                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">
                            Entreprise exposante
                        </p>

                        <h2 class="mt-2 break-words text-2xl font-extrabold text-[#0F2347]">

                            {{ $company?->company_name ?? '—' }}

                        </h2>

                        <p class="mt-2 text-sm text-slate-500">

                            {{ $company?->contact_name ?? '—' }}

                        </p>

                        <div class="mt-3 flex flex-wrap gap-2">

                            <span class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">

                                Stand {{ $company?->stand_number ?: '—' }}

                            </span>

                            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-bold {{ $statusClass }}">

                                <span class="size-2 rounded-full bg-current"></span>

                                {{ $status }}

                            </span>

                        </div>

                    </div>

                </div>

                @if ($company)

                    <a href="{{ route('commercial.exhibitors.show', $company) }}"
                       class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-xs font-semibold text-blue-700 hover:bg-blue-100">

                        Voir la fiche exposant →

                    </a>

                @endif

            </div>

        </section>

        {{-- SUMMARY CARDS --}}

        <section class="grid gap-4 sm:grid-cols-3">

            <article class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">

                <x-admin.icon name="layers" class="size-7 text-blue-600"/>

                <p class="mt-3 text-sm font-semibold text-blue-700">
                    Types d'équipements
                </p>

                <p class="mt-2 text-3xl font-extrabold text-[#0F2347]">{{ $equipmentRequest->items->count() }}</p>

            </article>

            <article class="rounded-2xl border border-violet-100 bg-violet-50 p-5 shadow-sm">

                <x-admin.icon name="box" class="size-7 text-violet-600"/>

                <p class="mt-3 text-sm font-semibold text-violet-700">
                    Quantité totale
                </p>

                <p class="mt-2 text-3xl font-extrabold text-[#0F2347]">{{ $totalQuantity }}</p>

            </article>

            <article class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5 shadow-sm">

                <x-admin.icon name="chart" class="size-7 text-emerald-600"/>

                <p class="mt-3 text-sm font-semibold text-emerald-700">
                    Montant de la demande
                </p>

                <p class="mt-2 text-2xl font-extrabold text-[#0F2347]">

                    {{ number_format((float) $equipmentRequest->total_amount, 2, ',', ' ') }} DH

                </p>

            </article>

        </section>

        {{-- MAIN GRID --}}

        <section class="grid min-w-0 items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">

            <div class="min-w-0 space-y-6">

                {{-- REQUEST ITEMS --}}

                <article class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex items-center gap-3 border-b border-slate-100 p-5">

                        <span class="grid size-11 place-items-center rounded-xl bg-blue-50 text-blue-600">

                            <x-admin.icon name="box" class="size-6"/>

                        </span>

                        <div>

                            <h2 class="font-bold text-[#0F2347]">
                                Équipements demandés
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Liste des articles et quantités.
                            </p>

                        </div>

                    </div>

                    <div class="overflow-x-auto">

                        <table class="w-full min-w-[600px] text-left text-sm">

                            <thead class="bg-slate-50 text-xs font-semibold text-slate-600">

                                <tr>
                                    <th scope="col" class="px-5 py-4">Équipement</th>
                                    <th scope="col" class="px-4 py-4 text-center">Quantité</th>
                                    <th scope="col" class="px-4 py-4">Prix unitaire</th>
                                    <th scope="col" class="px-5 py-4 text-right">Sous-total</th>
                                </tr>

                            </thead>

                            <tbody class="divide-y divide-slate-100">

                                @forelse ($equipmentRequest->items as $item)

                                    @php
                                        $imageUrl = $item->equipment?->image_path
                                            ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->equipment->image_path)
                                            : null;
                                    @endphp

                                    <tr class="hover:bg-slate-50">

                                        <td class="px-5 py-4">

                                            <div class="flex items-center gap-4">

                                                <div class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white">

                                                    @if ($imageUrl)

                                                        <img
                                                            src="{{ $imageUrl }}"
                                                            alt="{{ $item->equipment_name }}"
                                                            class="h-full w-full object-contain p-1.5"
                                                            loading="lazy"
                                                            data-request-image
                                                        >

                                                        <span class="hidden text-slate-400"
                                                              data-image-fallback>

                                                            <x-admin.icon name="box" class="size-7"/>

                                                        </span>

                                                    @else

                                                        <x-admin.icon name="box" class="size-7 text-slate-400"/>

                                                    @endif

                                                </div>

                                                <div class="min-w-0">

                                                    <p class="font-bold text-[#0F2347]">

                                                        {{ $item->equipment_name }}

                                                    </p>

                                                    <p class="mt-1 text-xs text-slate-500">

                                                        {{ $item->equipment?->category ?? 'Équipement' }}

                                                    </p>

                                                </div>

                                            </div>

                                        </td>

                                        <td class="px-4 py-4 text-center">

                                            <span class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700">

                                                {{ $item->quantity }}

                                            </span>

                                        </td>

                                        <td class="whitespace-nowrap px-4 py-4 text-slate-600">

                                            {{ number_format((float) $item->unit_price, 2, ',', ' ') }} DH

                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-right font-bold text-[#0F2347]">

                                            {{ number_format((float) $item->subtotal, 2, ',', ' ') }} DH

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td colspan="4"
                                            class="p-10 text-center text-sm text-slate-500">

                                            Aucun équipement dans cette demande.

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50 p-6">

                        <span class="text-sm font-bold text-slate-600">
                            Montant total
                        </span>

                        <span class="text-2xl font-extrabold text-blue-700">

                            {{ number_format((float) $equipmentRequest->total_amount, 2, ',', ' ') }} DH

                        </span>

                    </div>

                </article>

                {{-- EXHIBITOR NOTES --}}

                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex items-center gap-3">

                        <span class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-600">

                            <x-admin.icon name="inbox" class="size-5"/>

                        </span>

                        <h2 class="font-bold text-[#0F2347]">
                            Notes de l'exposant
                        </h2>

                    </div>

                    <div class="mt-4 break-words whitespace-pre-wrap rounded-xl bg-slate-50 p-4 text-sm leading-7 text-slate-600">{{ $equipmentRequest->notes ?: 'Aucune note supplémentaire.' }}</div>

                </article>

                {{-- REJECTION REASON --}}

                @if ($equipmentRequest->status === 'rejected')

                    <article class="rounded-2xl border border-red-200 bg-red-50 p-5">

                        <h2 class="font-bold text-red-800">
                            Motif de refus
                        </h2>

                        <p class="mt-3 break-words whitespace-pre-wrap text-sm leading-7 text-red-700">{{ $equipmentRequest->rejection_reason ?: 'Aucun motif renseigné.' }}</p>

                    </article>

                @endif

            </div>

            {{-- RIGHT SIDEBAR --}}

            <aside class="min-w-0 space-y-6">

                {{-- REQUEST DETAILS --}}

                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h2 class="font-bold text-[#0F2347]">
                        Informations de la demande
                    </h2>

                    <dl class="mt-4 divide-y divide-slate-100 text-sm">

                        <div class="flex justify-between gap-3 py-4">

                            <dt class="text-slate-500">Référence</dt>

                            <dd class="font-bold text-blue-600">
                                {{ $reference }}
                            </dd>

                        </div>

                        <div class="flex items-center justify-between gap-3 py-4">

                            <dt class="text-slate-500">Statut</dt>

                            <dd class="rounded-full border px-3 py-1 text-xs font-bold {{ $statusClass }}">

                                {{ $status }}

                            </dd>

                        </div>

                        <div class="flex justify-between gap-3 py-4">

                            <dt class="text-slate-500">Créée le</dt>

                            <dd class="text-right font-semibold text-[#0F2347]">

                                {{ $equipmentRequest->created_at?->format('d/m/Y') }}

                                <span class="mt-1 block text-xs font-normal text-slate-400">

                                    {{ $equipmentRequest->created_at?->format('H:i') }}

                                </span>

                            </dd>

                        </div>

                        <div class="flex justify-between gap-3 py-4">

                            <dt class="text-slate-500">Traitée le</dt>

                            <dd class="text-right font-semibold text-[#0F2347]">

                                {{ $equipmentRequest->reviewed_at?->format('d/m/Y à H:i') ?? '—' }}

                            </dd>

                        </div>

                        <div class="flex justify-between gap-3 pt-4">

                            <dt class="text-slate-500">Traitée par</dt>

                            <dd class="text-right font-semibold text-[#0F2347]">

                                {{ $equipmentRequest->reviewer?->name ?? '—' }}

                            </dd>

                        </div>

                    </dl>

                </article>

                {{-- TRACKING TIMELINE --}}

                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h2 class="font-bold text-[#0F2347]">
                        Suivi de la demande
                    </h2>

                    <div class="mt-6 space-y-6">

                        @foreach ($steps as $step)

                            <div class="relative flex items-start gap-3">

                                @unless ($loop->last)

                                    <div class="absolute left-4 top-8 h-full w-px bg-slate-200"></div>

                                @endunless

                                <span @class([
                                    'relative z-10 grid size-8 shrink-0 place-items-center rounded-full',
                                    'bg-red-100 text-red-700' => $step['done'] && $loop->last && $equipmentRequest->status === 'rejected',
                                    'bg-emerald-100 text-emerald-700' => $step['done'] && !($loop->last && $equipmentRequest->status === 'rejected'),
                                    'bg-slate-100 text-slate-400' => !$step['done'],
                                ])>

                                    @if ($step['done'])

                                        @if ($loop->last && $equipmentRequest->status === 'rejected')

                                            <span class="font-bold">×</span>

                                        @else

                                            <svg class="size-4"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="2"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round"
                                                 aria-hidden="true">

                                                <path d="m5 12 4 4L19 6"/>

                                            </svg>

                                        @endif

                                    @else

                                        <span class="size-2 rounded-full bg-current"></span>

                                    @endif

                                </span>

                                <div>

                                    <p class="text-sm font-semibold text-[#0F2347]">

                                        {{ $step['label'] }}

                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">

                                        {{ $step['description'] }}

                                    </p>

                                </div>

                            </div>

                        @endforeach

                    </div>

                </article>

                {{-- INFORMATION --}}

                <article class="rounded-2xl border border-blue-100 bg-blue-50 p-5">

                    <h3 class="text-sm font-bold text-blue-900">
                        Gestion commerciale
                    </h3>

                    <p class="mt-2 text-xs leading-6 text-blue-700">

                        La validation ou le refus de cette demande
                        est effectué par le commercial responsable de l'exposant.

                    </p>

                </article>

            </aside>

        </section>


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


@if ($equipmentRequest->status === 'pending')

<section class="mt-6 grid gap-5 lg:grid-cols-2">

    {{-- APPROUVER --}}
    <div class="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">

        <h3 class="text-lg font-bold text-[#0F2347]">
            Approuver la demande
        </h3>

        <p class="mt-2 text-sm text-slate-500">
            Vérifier le stock et confirmer la demande
            de cet exposant.
        </p>

        <form
            method="POST"
            action="{{ route('commercial.equipment-requests.approve', $equipmentRequest) }}"
            class="mt-5"
        >
            @csrf
            @method('PATCH')

            <button
                type="submit"
                onclick="return confirm('Confirmer la validation de cette demande ?')"
                class="w-full rounded-xl bg-emerald-600 px-5 py-3 font-bold text-white transition hover:bg-emerald-700"
            >
                Approuver la demande
            </button>
        </form>
    </div>

    {{-- REFUSER --}}
    <div class="rounded-2xl border border-red-200 bg-white p-6 shadow-sm">

        <h3 class="text-lg font-bold text-[#0F2347]">
            Refuser la demande
        </h3>

        <p class="mt-2 text-sm text-slate-500">
            Indiquez la raison du refus.
        </p>

        <form
            method="POST"
            action="{{ route('commercial.equipment-requests.reject', $equipmentRequest) }}"
            class="mt-5 space-y-4"
        >
            @csrf
            @method('PATCH')

            <textarea
                name="rejection_reason"
                rows="3"
                maxlength="2000"
                required
                placeholder="Motif du refus..."
                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-red-500 focus:ring-red-500"
            >{{ old('rejection_reason') }}</textarea>

            <button
                type="submit"
                class="w-full rounded-xl bg-red-600 px-5 py-3 font-bold text-white transition hover:bg-red-700"
            >
                Refuser la demande
            </button>
        </form>
    </div>

</section>

@endif


        <footer class="border-t border-slate-200 py-4 text-center text-xs text-slate-400">

            © 2027 Refrigair Expo — Espace Commercial

        </footer>

</x-commercial.layout>
