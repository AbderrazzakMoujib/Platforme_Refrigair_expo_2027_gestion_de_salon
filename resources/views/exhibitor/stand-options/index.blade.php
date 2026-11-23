
<x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">

@php
    $categories = $equipment
        ->pluck('category')
        ->filter()
        ->unique()
        ->sort()
        ->values();

    $pendingCount = $requests
        ->where('status', 'pending')
        ->count();

    $formatPrice = fn ($amount) =>
        number_format((float) $amount, 2, ',', ' ') . ' DH';
@endphp

<div class="space-y-6">

    <!-- PAGE HEADER -->
    <header class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">

        <div>
            <nav class="mb-4 flex items-center gap-2 text-sm text-slate-500">

                <a href="{{ route('exposant.index') }}"
                   class="hover:text-blue-600">
                    Espace exposant
                </a>

                <span>/</span>

                <span class="font-semibold text-blue-600">
                    Options du stand
                </span>

            </nav>

            <div class="flex items-center gap-4">

                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-700">

                    <svg class="size-7" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor"
                         stroke-width="1.8" stroke-linecap="round"
                         stroke-linejoin="round" aria-hidden="true">
                        <circle cx="9" cy="20" r="1"/>
                        <circle cx="18" cy="20" r="1"/>
                        <path d="M2 3h2l2.5 12h12L22 7H5"/>
                    </svg>

                </span>

                <div>
                    <h1 class="text-2xl font-extrabold text-[#0F2347] sm:text-3xl">
                        Options du stand
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Sélectionnez les équipements supplémentaires
                        nécessaires pour votre stand.
                    </p>
                </div>

            </div>
        </div>

        <!-- EVENT INFO -->
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 to-white px-5 py-4 shadow-sm">

            <p class="text-base font-extrabold text-[#0F2347]">
                Refrigair Expo 2027
            </p>

            <p class="mt-1 text-sm text-slate-500">
                {{ $exhibitor->company_name }}
            </p>

            <p class="mt-1 text-xs font-semibold text-blue-700">
                Stand : {{ $exhibitor->stand_number ?: 'Non attribué' }}
            </p>

        </div>

    </header>

    <!-- MESSAGES -->
    @if (session('status'))
        <div role="status"
             class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert"
             class="rounded-xl border border-red-200 bg-red-50 p-5">

            <p class="text-sm font-bold text-red-700">
                Veuillez corriger les erreurs :
            </p>

            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>
    @endif

    <!-- STATS -->
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <article class="flex items-center gap-4 rounded-2xl border border-blue-100 bg-blue-50/70 p-5 shadow-sm">

            <span class="grid size-13 shrink-0 place-items-center rounded-xl bg-violet-100 p-3 text-violet-600">
                <svg class="size-6" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor"
                     stroke-width="1.8" aria-hidden="true">
                    <path d="M2 3h2l2.5 12h12L22 7H5"/>
                    <circle cx="9" cy="20" r="1"/>
                    <circle cx="18" cy="20" r="1"/>
                </svg>
            </span>

            <div>
                <p class="text-sm font-medium text-slate-600">
                    Options sélectionnées
                </p>

                <p id="stat-options"
                   aria-live="polite"
                   class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    0
                </p>
            </div>

        </article>

        <article class="flex items-center gap-4 rounded-2xl border border-amber-100 bg-amber-50/70 p-5 shadow-sm">

            <span class="grid size-13 shrink-0 place-items-center rounded-xl bg-amber-100 p-3 text-amber-600">
                <svg class="size-6" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor"
                     stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M8 9h8M8 13h8M8 17h5"/>
                </svg>
            </span>

            <div>
                <p class="text-sm font-medium text-slate-600">
                    Total estimé
                </p>

                <p id="stat-total"
                   aria-live="polite"
                   class="mt-1 text-2xl font-extrabold text-[#0F2347]">
                    0,00 DH
                </p>
            </div>

        </article>

        <article class="flex items-center gap-4 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-5 shadow-sm">

            <span class="grid size-13 shrink-0 place-items-center rounded-xl bg-emerald-100 p-3 text-emerald-600">
                <svg class="size-6" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor"
                     stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 7v5l3 3"/>
                </svg>
            </span>

            <div>
                <p class="text-sm font-medium text-slate-600">
                    Demandes en attente
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ $pendingCount }}
                </p>
            </div>

        </article>

        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <span class="grid size-13 shrink-0 place-items-center rounded-xl bg-blue-100 p-3 text-blue-600">
                <svg class="size-6" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor"
                     stroke-width="1.8" aria-hidden="true">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
            </span>

            <div>
                <p class="text-sm font-medium text-slate-600">
                    Options du catalogue
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ $equipment->count() }}
                </p>
            </div>

        </article>

    </section>

    <!-- ORDER FORM -->
    <form method="POST"
          action="{{ route('exhibitor.stand-options.store') }}"
          id="stand-options-form">

        @csrf

        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.6fr)_minmax(340px,1fr)]">

            <!-- CATALOGUE -->
            <section class="min-w-0 space-y-5">

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                    <div class="flex flex-wrap items-center justify-between gap-3">

                        <div>
                            <h2 class="text-xl font-extrabold text-[#0F2347]">
                                Catalogue des options
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Choisissez vos équipements et quantités.
                            </p>
                        </div>

                        <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">
                            {{ $equipment->count() }} équipement(s)
                        </span>

                    </div>

                    <!-- SEARCH AND CATEGORY -->
                    <div class="mt-5 grid gap-3 sm:grid-cols-[minmax(0,1fr)_190px]">

                        <div class="relative">

                            <label for="equipment-search" class="sr-only">
                                Rechercher un équipement
                            </label>

                            <svg class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-slate-400"
                                 viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="1.8" aria-hidden="true">
                                <circle cx="11" cy="11" r="7"/>
                                <path d="m16 16 5 5"/>
                            </svg>

                            <input id="equipment-search"
                                   type="search"
                                   placeholder="Rechercher un équipement..."
                                   class="w-full rounded-xl border border-slate-200 py-3 pl-10 pr-4 text-sm focus:border-blue-500 focus:ring-blue-500">

                        </div>

                        <div>
                            <label for="category-filter" class="sr-only">
                                Catégorie
                            </label>

                            <select id="category-filter"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm focus:border-blue-500 focus:ring-blue-500">

                                <option value="">
                                    Toutes les catégories
                                </option>

                                @foreach ($categories as $category)
                                    <option value="{{ $category }}">
                                        {{ $category }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                    </div>

                    <!-- PRODUCTS -->
                    <div id="equipment-grid"
                         class="mt-6 grid gap-4 sm:grid-cols-2">

                        @forelse ($equipment as $item)

                            @php
                                $maxQty = min(
                                    100,
                                    max(0, (int) $item->quantity_available)
                                );

                                $imageUrl = $item->image_path
                                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path)
                                    : null;

                                $oldQty = min(
                                    $maxQty,
                                    max(0, (int) old('items.' . $item->id, 0))
                                );
                            @endphp

                            
<article
    class="equipment-card flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md"
    data-id="{{ $item->id }}"
    data-name="{{ $item->name }}"
    data-category="{{ $item->category }}"
    data-description="{{ $item->description }}"
    data-price="{{ $item->price }}"
    data-max="{{ $maxQty }}"
    data-image="{{ $imageUrl ?? '' }}"
>

    {{-- PRODUCT IMAGE --}}
    <div
        class="relative w-full shrink-0 overflow-hidden border-b border-slate-100 bg-[#F5F7FB]"
        style="height: 180px;"
    >

        @if ($imageUrl)
            <img
                src="{{ $imageUrl }}"
                alt="{{ $item->name }}"
                loading="lazy"
                class="absolute inset-0 block h-full w-full object-contain p-3"
                style="width: 100%; height: 100%; object-fit: contain;"
            >
        @else
            <div class="absolute inset-0 flex items-center justify-center">

                <div class="flex size-16 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">

                    <svg
                        class="size-9"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        aria-hidden="true"
                    >
                        <path d="m12 2 9 5-9 5-9-5 9-5Z"/>
                        <path d="M3 7v10l9 5 9-5V7"/>
                        <path d="M12 12v10"/>
                    </svg>

                </div>

            </div>
        @endif

    </div>

    {{-- PRODUCT INFORMATION --}}
    <div class="flex min-w-0 flex-1 flex-col p-4">

        {{-- CATEGORY --}}
        <div class="mb-3">
            <span class="inline-flex max-w-full rounded-lg bg-blue-50 px-3 py-1.5 text-[11px] font-bold text-blue-700">
                {{ $item->category ?: 'Autres' }}
            </span>
        </div>

        {{-- PRODUCT NAME --}}
        <h3 class="line-clamp-2 min-h-[48px] break-words text-base font-extrabold leading-6 text-[#0F2347]">
            {{ $item->name }}
        </h3>

        {{-- DESCRIPTION --}}
        <p class="mt-2 line-clamp-2 min-h-[40px] break-words text-xs leading-5 text-slate-500">
            {{ $item->description ?: 'Équipement supplémentaire pour votre stand.' }}
        </p>

        {{-- PRICE / STOCK / BUTTON --}}
        <div class="mt-auto pt-4">

            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">

                <p class="text-lg font-extrabold text-[#2563EB]">
                    {{ $formatPrice($item->price) }}
                </p>

                <span @class([
                    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold',
                    'bg-emerald-50 text-emerald-700' => $maxQty > 0,
                    'bg-red-50 text-red-700' => $maxQty === 0,
                ])>
                    <span @class([
                        'size-1.5 rounded-full',
                        'bg-emerald-500' => $maxQty > 0,
                        'bg-red-500' => $maxQty === 0,
                    ])></span>

                    {{ $maxQty > 0
                        ? 'Disponible : ' . $item->quantity_available
                        : 'Indisponible' }}
                </span>

            </div>

            {{-- QUANTITY: KEEP FOR EXISTING CART JS --}}
            <input
                type="hidden"
                name="items[{{ $item->id }}]"
                value="{{ $oldQty }}"
                data-equipment-quantity
            >

            {{-- ADD TO CART --}}
            <button
                type="button"
                data-add-item
                @disabled($maxQty === 0)
                class="flex w-full items-center justify-center gap-2 rounded-xl border border-blue-300 bg-white px-4 py-3 text-sm font-bold text-[#2563EB] transition hover:border-blue-600 hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-50"
            >
                + Ajouter
            </button>

        </div>

    </div>

</article>


                        @empty

                            <div class="col-span-full rounded-xl border border-dashed border-slate-200 p-10 text-center">

                                <h3 class="font-bold text-[#0F2347]">
                                    Aucun équipement disponible
                                </h3>

                                <p class="mt-2 text-sm text-slate-500">
                                    Le catalogue sera disponible prochainement.
                                </p>

                            </div>

                        @endforelse

                    </div>

                    <p id="equipment-no-results"
                       class="mt-5 hidden rounded-xl bg-slate-50 p-5 text-center text-sm text-slate-500"
                       role="status">
                        Aucun équipement ne correspond à votre recherche.
                    </p>

                </div>

                <!-- IMPORTANT INFORMATION -->
                <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 shadow-sm">

                    <div class="flex items-center gap-3">

                        <span class="grid size-10 place-items-center rounded-xl bg-white text-blue-700">
                            <svg class="size-6" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="1.8" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 11v5M12 7h.01"/>
                            </svg>
                        </span>

                        <h2 class="font-extrabold text-[#0F2347]">
                            Informations importantes
                        </h2>

                    </div>

                    <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-6 text-slate-700">

                        <li>
                            Les demandes d'équipements sont soumises
                            à validation par l'organisation.
                        </li>

                        <li>
                            Les prix affichés proviennent du catalogue.
                            Le traitement fiscal n'est pas précisé ici.
                        </li>

                        <li>
                            Les quantités sont limitées par le stock
                            disponible au moment de l'envoi.
                        </li>

                    </ul>

                </div>

            </section>

            <!-- CART -->
            <aside class="min-w-0">

                <div class="sticky top-24 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-100 p-5">

                        <div class="flex items-center justify-between gap-3">

                            <h2 class="text-lg font-extrabold text-[#0F2347]">
                                Mes options sélectionnées
                            </h2>

                            <span id="cart-type-count"
                                  class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                                0
                            </span>

                        </div>

                        <p class="mt-2 text-xs text-slate-500">
                            Modifiez les quantités avant l'envoi.
                        </p>

                    </div>

                    <!-- DYNAMIC CART -->
                    <div id="cart-items"
                         class="max-h-[430px] space-y-3 overflow-y-auto p-5"
                         aria-live="polite">
                    </div>

                    <div id="empty-cart"
                         class="px-6 py-8 text-center">

                        <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-blue-50 text-blue-600">
                            <svg class="size-7" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="1.8" aria-hidden="true">
                                <path d="M2 3h2l2.5 12h12L22 7H5"/>
                                <circle cx="9" cy="20" r="1"/>
                                <circle cx="18" cy="20" r="1"/>
                            </svg>
                        </span>

                        <p class="mt-4 font-bold text-[#0F2347]">
                            Votre sélection est vide
                        </p>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Ajoutez des équipements depuis le catalogue.
                        </p>

                    </div>

                    <!-- TOTAL -->
                    <div class="border-t border-slate-100 p-5">

                        <div class="flex items-center justify-between gap-3 text-sm">

                            <span class="text-slate-500">
                                Quantité totale
                            </span>

                            <strong id="cart-quantity"
                                    class="text-[#0F2347]">
                                0
                            </strong>

                        </div>

                        <div class="mt-4 flex items-center justify-between gap-3">

                            <span class="font-bold text-[#0F2347]">
                                Total estimé
                            </span>

                            <strong id="cart-total"
                                    aria-live="polite"
                                    class="text-xl font-extrabold text-blue-700">
                                0,00 DH
                            </strong>

                        </div>

                        <p class="mt-2 text-xs text-slate-500">
                            Calculé selon les prix du catalogue,
                            sans ajout de TVA.
                        </p>

                        <!-- NOTES -->
                        <div class="mt-5">

                            <label for="notes"
                                   class="block text-sm font-semibold text-[#0F2347]">
                                Commentaire (facultatif)
                            </label>

                            <textarea id="notes"
                                      name="notes"
                                      rows="3"
                                      maxlength="2000"
                                      placeholder="Précisez vos besoins..."
                                      class="mt-2 w-full rounded-xl border border-slate-200 p-3 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>

                        </div>

                        <!-- SUBMIT -->
                        <button type="submit"
                                id="submit-request"
                                disabled
                                class="mt-5 flex w-full items-center justify-center rounded-xl bg-[#2563EB] px-5 py-3.5 text-sm font-bold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">

                            Soumettre ma demande

                        </button>

                        <button type="button"
                                id="clear-cart"
                                disabled
                                class="mt-3 w-full rounded-xl border border-blue-200 px-5 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-50">

                            Vider la sélection

                        </button>

                        <p class="mt-4 text-center text-xs leading-5 text-slate-500">
                            Votre demande sera envoyée
                            à l'organisation pour validation.
                        </p>

                    </div>

                </div>

            </aside>

        </div>

    </form>

    <!-- EXISTING REQUESTS -->
    <section id="mes-demandes"
             class="scroll-mt-28 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">

            <div>
                <h2 class="text-xl font-extrabold text-[#0F2347]">
                    Mes demandes
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Historique de vos demandes d'équipements.
                </p>
            </div>

            <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">
                {{ $requests->count() }} demande(s)
            </span>

        </div>

        <div class="divide-y divide-slate-100">

            @forelse ($requests as $order)

                @php
                    $statusLabel = match ($order->status) {
                        'approved' => 'Approuvée',
                        'rejected' => 'Refusée',
                        default => 'En attente',
                    };

                    $statusClass = match ($order->status) {
                        'approved' => 'bg-emerald-50 text-emerald-700',
                        'rejected' => 'bg-red-50 text-red-700',
                        default => 'bg-amber-50 text-amber-700',
                    };
                @endphp

                <article class="p-6">

                    <div class="flex flex-wrap items-center justify-between gap-3">

                        <div>
                            <h3 class="font-bold text-[#0F2347]">
                                Demande #{{ $order->id }}
                            </h3>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ $order->created_at?->format('d/m/Y à H:i') }}
                            </p>
                        </div>

                        <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>

                    </div>

                    <div class="mt-5 divide-y divide-slate-100 rounded-xl bg-slate-50 px-4">

                        @foreach ($order->items as $line)

                            <div class="flex flex-wrap items-center justify-between gap-3 py-3 text-sm">

                                <div>
                                    <p class="font-semibold text-[#0F2347]">
                                        {{ $line->equipment_name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Quantité : {{ $line->quantity }}
                                    </p>
                                </div>

                                <span class="font-bold text-[#0F2347]">
                                    {{ $formatPrice($line->subtotal) }}
                                </span>

                            </div>

                        @endforeach

                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">

                        <span class="font-semibold text-slate-600">
                            Montant de la demande
                        </span>

                        <strong class="text-lg text-blue-700">
                            {{ $formatPrice($order->total_amount) }}
                        </strong>

                    </div>

                    @if ($order->notes)
                        <p class="mt-4 rounded-xl bg-blue-50 p-3 text-sm text-blue-800">
                            <strong>Commentaire :</strong>
                            {{ $order->notes }}
                        </p>
                    @endif

                    @if ($order->status === 'rejected' && $order->rejection_reason)
                        <p class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">
                            <strong>Motif du refus :</strong>
                            {{ $order->rejection_reason }}
                        </p>
                    @endif

                </article>

            @empty

                <div class="p-10 text-center">

                    <h3 class="font-bold text-[#0F2347]">
                        Aucune demande enregistrée
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        Vos prochaines demandes apparaîtront ici.
                    </p>

                </div>

            @endforelse

        </div>

    </section>

</div>

<!-- CART JAVASCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('stand-options-form');

    if (!form) return;

    const cards = Array.from(
        document.querySelectorAll('.equipment-card')
    );

    const cart = document.getElementById('cart-items');
    const emptyCart = document.getElementById('empty-cart');

    const search = document.getElementById('equipment-search');
    const category = document.getElementById('category-filter');
    const noResults = document.getElementById('equipment-no-results');

    const statOptions = document.getElementById('stat-options');
    const statTotal = document.getElementById('stat-total');

    const cartTypeCount = document.getElementById('cart-type-count');
    const cartQuantity = document.getElementById('cart-quantity');
    const cartTotal = document.getElementById('cart-total');

    const submit = document.getElementById('submit-request');
    const clear = document.getElementById('clear-cart');

    const formatter = new Intl.NumberFormat('fr-MA', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    const money = cents =>
        `${formatter.format(cents / 100)} DH`;

    const items = cards.map(card => {
        const input = card.querySelector('[data-equipment-quantity]');

        const max = Math.max(
            0,
            Math.min(100, Number(card.dataset.max) || 0)
        );

        const quantity = Math.max(
            0,
            Math.min(max, Number.parseInt(input.value, 10) || 0)
        );

        input.value = quantity;

        return {
            id: card.dataset.id,
            name: card.dataset.name || '',
            category: card.dataset.category || '',
            description: card.dataset.description || '',
            image: card.dataset.image || '',
            priceCents: Math.round((Number(card.dataset.price) || 0) * 100),
            max,
            input,
            card,
            quantity,
        };
    });

    function makeButton(label, action, disabled = false) {
        const button = document.createElement('button');

        button.type = 'button';
        button.textContent = label;
        button.disabled = disabled;

        button.className =
            'grid size-8 shrink-0 place-items-center rounded-lg ' +
            'border border-blue-200 text-sm font-bold text-blue-700 ' +
            'hover:bg-blue-50 disabled:cursor-not-allowed ' +
            'disabled:opacity-40';

        button.addEventListener('click', action);

        return button;
    }

    function setQuantity(item, nextQuantity) {
        const value = Number(nextQuantity);

        item.quantity = Number.isFinite(value)
            ? Math.max(0, Math.min(item.max, Math.trunc(value)))
            : 0;

        item.input.value = item.quantity;

        render();
    }

    function createCartRow(item) {
        const row = document.createElement('div');

        row.className =
            'rounded-xl border border-slate-200 bg-white p-3';

        const top = document.createElement('div');
        top.className = 'flex items-start gap-3';

        // IMAGE
        const visual = document.createElement('div');

        visual.className =
            'grid size-14 shrink-0 place-items-center overflow-hidden ' +
            'rounded-lg bg-[#F5F7FB] text-blue-600';

        if (item.image) {
            const img = document.createElement('img');

            img.src = item.image;
            img.alt = item.name;
            img.className = 'h-full w-full object-contain p-1';

            visual.appendChild(img);
        } else {
            visual.textContent = '▣';
        }

        // DESCRIPTION
        const info = document.createElement('div');
        info.className = 'min-w-0 flex-1';

        const title = document.createElement('p');
        title.className = 'text-sm font-bold text-[#0F2347]';
        title.textContent = item.name;

        const unitPrice = document.createElement('p');
        unitPrice.className = 'mt-1 text-xs font-semibold text-blue-700';
        unitPrice.textContent = money(item.priceCents);

        info.append(title, unitPrice);

        // DELETE
        
        // REMOVE PRODUCT FROM CART
        const remove = document.createElement('button');

        remove.type = 'button';

        remove.className =
            'grid size-9 shrink-0 place-items-center rounded-xl ' +
            'border border-red-200 bg-red-50 text-red-600 ' +
            'transition hover:border-red-300 hover:bg-red-100 ' +
            'focus:outline-none focus:ring-2 focus:ring-red-400';

        remove.innerHTML = `
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <path d="M3 6h18"/>
                <path d="M8 6V4h8v2"/>
                <path d="M5 6l1 15h12l1-15"/>
                <path d="M10 10v7"/>
                <path d="M14 10v7"/>
            </svg>
        `;

        remove.setAttribute(
            'aria-label',
            `Supprimer ${item.name} du panier`
        );

        remove.title = 'Retirer du panier';

        remove.addEventListener('click', () => {
            setQuantity(item, 0);
        });


        top.append(visual, info, remove);

        // QUANTITY CONTROL
        const bottom = document.createElement('div');

        bottom.className =
            'mt-3 flex flex-wrap items-center justify-between gap-3';

        const controls = document.createElement('div');

        controls.className =
            'flex items-center gap-2';

        const minus = makeButton(
            '−',
            () => setQuantity(item, item.quantity - 1),
            item.quantity <= 0
        );

        minus.setAttribute(
            'aria-label',
            `Diminuer la quantité de ${item.name}`
        );

        const quantity = document.createElement('span');

        quantity.className =
            'min-w-7 text-center text-sm font-bold text-[#0F2347]';

        quantity.textContent = item.quantity;

        const plus = makeButton(
            '+',
            () => setQuantity(item, item.quantity + 1),
            item.quantity >= item.max
        );

        plus.setAttribute(
            'aria-label',
            `Augmenter la quantité de ${item.name}`
        );

        controls.append(minus, quantity, plus);

        const subtotal = document.createElement('strong');

        subtotal.className =
            'text-sm font-extrabold text-[#0F2347]';

        subtotal.textContent = money(
            item.priceCents * item.quantity
        );

        bottom.append(controls, subtotal);

        row.append(top, bottom);

        return row;
    }

    function render() {
        const selected = items.filter(item => item.quantity > 0);

        const units = selected.reduce(
            (sum, item) => sum + item.quantity,
            0
        );

        const totalCents = selected.reduce(
            (sum, item) =>
                sum + (item.quantity * item.priceCents),
            0
        );

        const fragment = document.createDocumentFragment();

        selected.forEach(item => {
            fragment.appendChild(createCartRow(item));
        });

        cart.replaceChildren(fragment);

        emptyCart.classList.toggle(
            'hidden',
            selected.length > 0
        );

        statOptions.textContent = selected.length;
        statTotal.textContent = money(totalCents);

        cartTypeCount.textContent = selected.length;
        cartQuantity.textContent = units;
        cartTotal.textContent = money(totalCents);

        submit.disabled = selected.length === 0;
        clear.disabled = selected.length === 0;

        // Visual state of catalogue buttons.
        items.forEach(item => {
            const button = item.card.querySelector('[data-add-item]');

            if (!button) return;

            if (item.quantity >= item.max && item.max > 0) {
                button.textContent = `Maximum atteint (${item.quantity})`;
                button.disabled = true;
            } else if (item.quantity > 0) {
                button.textContent = `+ Ajouter (${item.quantity})`;
                button.disabled = false;
            } else {
                button.textContent = '+ Ajouter';
                button.disabled = item.max <= 0;
            }
        });
    }

    function filterProducts() {
        const query = (search.value || '')
            .trim()
            .toLocaleLowerCase();

        const selectedCategory = category.value;

        let visible = 0;

        items.forEach(item => {
            const haystack = [
                item.name,
                item.category,
                item.description,
            ].join(' ').toLocaleLowerCase();

            const matchesSearch = !query || haystack.includes(query);
            const matchesCategory =
                !selectedCategory ||
                item.category === selectedCategory;

            const show = matchesSearch && matchesCategory;

            item.card.classList.toggle('hidden', !show);

            if (show) visible++;
        });

        noResults.classList.toggle(
            'hidden',
            visible !== 0 || items.length === 0
        );
    }

    items.forEach(item => {
        const button = item.card.querySelector('[data-add-item]');

        button?.addEventListener('click', () => {
            setQuantity(item, item.quantity + 1);
        });
    });

    clear?.addEventListener('click', () => {
        items.forEach(item => {
            item.quantity = 0;
            item.input.value = 0;
        });

        render();
    });

    search?.addEventListener('input', filterProducts);
    category?.addEventListener('change', filterProducts);

    form.addEventListener('submit', event => {
        if (!items.some(item => item.quantity > 0)) {
            event.preventDefault();
            return;
        }

        submit.disabled = true;
        submit.textContent = 'Envoi en cours...';
    });

    render();
    filterProducts();
});
</script>

</x-exhibitor.layout>
