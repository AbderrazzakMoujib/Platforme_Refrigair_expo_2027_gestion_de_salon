
<x-commercial.layout
    :commercial="$commercial"
    :commercial-initials="$commercialInitials"
>

    @php
        $inputClass = 'h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100';

        $catalogue = $equipment->map(fn ($item) => [
            'id' => (int) $item->id,
            'name' => $item->name,
            'category' => $item->category,
            'price' => (int) round((float) $item->price * 100),
            'stock' => min(100, max(0, (int) $item->quantity_available)),
            'image' => $item->image_path
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path)
                : null,
        ])->values();
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
                Nouvelle demande
            </span>

        </nav>


        {{-- HEADER --}}

        <header class="flex flex-wrap items-center justify-between gap-4">

            <div class="flex items-center gap-4">

                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">

                    <x-admin.icon
                        name="box"
                        class="size-8"
                    />

                </span>

                <div>

                    <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">

                        Nouvelle demande d'équipements

                    </h1>

                    <p class="mt-2 text-sm text-slate-500">

                        Créez une demande d'équipements pour l'un de vos exposants.

                    </p>

                </div>

            </div>

            <a
                href="{{ route('commercial.equipment-requests.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50"
            >

                ← Retour à la liste

            </a>

        </header>


        {{-- VALIDATION ERRORS --}}

        @if ($errors->any())

            <div
                role="alert"
                class="rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-700"
            >

                <p class="font-bold">
                    Veuillez vérifier votre demande.
                </p>

                <ul class="mt-2 list-disc space-y-1 pl-5">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- STEPPER --}}

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <div class="grid grid-cols-3 gap-2 sm:gap-6">

                @foreach ([
                    1 => "Choix de l'exposant",
                    2 => "Sélection des équipements",
                    3 => "Récapitulatif",
                ] as $number => $label)

                    <div class="flex min-w-0 flex-col items-center gap-3 text-center">

                        <div
                            data-step="{{ $number }}"
                            class="grid size-10 place-items-center rounded-full border border-slate-200 bg-slate-50 text-sm font-bold text-slate-500"
                        >

                            {{ $number }}

                        </div>

                        <p class="text-xs font-semibold text-[#0F2347]">

                            {{ $label }}

                        </p>

                    </div>

                @endforeach

            </div>

            <div class="mt-5 h-1.5 overflow-hidden rounded-full bg-slate-100">

                <div
                    id="step-progress"
                    class="h-full rounded-full bg-blue-600 transition-all"
                    style="width: 33.33%"
                ></div>

            </div>

        </section>


        {{-- MAIN FORM --}}

        <form
            method="POST"
            action="{{ route('commercial.equipment-requests.store') }}"
            id="equipment-request-form"
            class="grid min-w-0 items-start gap-6 xl:grid-cols-[minmax(0,1fr)_340px]"
        >

            @csrf


            {{-- LEFT COLUMN --}}

            <div class="min-w-0 space-y-6">


                {{-- SECTION 1 : EXHIBITOR --}}

                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex items-start gap-3 border-b border-slate-100 p-5">

                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-blue-600 text-sm font-bold text-white">

                            1

                        </span>

                        <div>

                            <h2 class="font-extrabold text-[#0F2347]">

                                Exposant concerné

                            </h2>

                            <p class="mt-1 text-xs text-slate-500">

                                Sélectionnez l'entreprise pour laquelle vous créez cette demande.

                            </p>

                        </div>

                    </div>

                    <div class="p-5">

                        <label
                            for="exhibitor_id"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >

                            Entreprise exposante

                            <span class="text-red-500">*</span>

                        </label>

                        <select
                            id="exhibitor_id"
                            name="exhibitor_id"
                            required
                            class="{{ $inputClass }}"
                        >

                            <option value="">

                                Sélectionner un exposant

                            </option>

                            @foreach ($exhibitors as $exhibitor)

                                <option
                                    value="{{ $exhibitor->id }}"
                                    @selected((string) old('exhibitor_id', request('exhibitor_id')) === (string) $exhibitor->id)
                                >

                                    {{ $exhibitor->company_name }}

                                    — Stand {{ $exhibitor->stand_number ?: '—' }}

                                </option>

                            @endforeach

                        </select>

                        @error('exhibitor_id')

                            <p class="mt-2 text-xs text-red-600">

                                {{ $message }}

                            </p>

                        @enderror


                        {{-- SELECTED EXHIBITOR --}}

                        @php
                            $exhibitorData = $exhibitors->map(fn ($exhibitor) => [
                                'id' => (int) $exhibitor->id,
                                'name' => $exhibitor->company_name,
                                'contact' => $exhibitor->contact_name,
                                'stand' => $exhibitor->stand_number,
                                'area' => $exhibitor->area_sqm,
                                'logo' => $exhibitor->logo
                                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($exhibitor->logo)
                                    : null,
                            ])->values();
                        @endphp

                        <div
                            id="selected-exhibitor"
                            class="mt-4 hidden rounded-xl border border-blue-100 bg-blue-50/60 p-4"
                        >

                            <div class="flex items-center gap-4">

                                <div class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white">

                                    <img
                                        id="selected-exhibitor-logo"
                                        alt="Logo de l'exposant"
                                        class="hidden h-full w-full object-contain p-1.5"
                                    >

                                    <span
                                        id="selected-exhibitor-initials"
                                        class="text-xl font-extrabold text-blue-700"
                                    ></span>

                                </div>

                                <div class="min-w-0 flex-1">

                                    <p
                                        id="selected-exhibitor-name"
                                        class="break-words font-bold text-[#0F2347]"
                                    ></p>

                                    <p
                                        id="selected-exhibitor-contact"
                                        class="mt-1 text-xs text-slate-500"
                                    ></p>

                                    <div class="mt-2 flex flex-wrap gap-2">

                                        <span
                                            id="selected-exhibitor-stand"
                                            class="rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-blue-700"
                                        ></span>

                                        <span
                                            id="selected-exhibitor-area"
                                            class="rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-slate-600"
                                        ></span>

                                    </div>

                                </div>

                            </div>

                        </div>

                        @if ($exhibitors->isEmpty())

                            <div class="mt-4 rounded-xl bg-amber-50 p-4 text-sm text-amber-700">

                                Vous n'avez aucun exposant associé.

                                <a
                                    href="{{ route('commercial.exhibitors.create') }}"
                                    class="font-bold underline"
                                >

                                    Ajouter un exposant

                                </a>

                            </div>

                        @endif

                    </div>

                </section>


                {{-- SECTION 2 : EQUIPMENT CATALOGUE --}}

                <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex items-start gap-3 border-b border-slate-100 p-5">

                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-blue-600 text-sm font-bold text-white">

                            2

                        </span>

                        <div>

                            <h2 class="font-extrabold text-[#0F2347]">

                                Sélection des équipements

                            </h2>

                            <p class="mt-1 text-xs text-slate-500">

                                Choisissez les équipements à inclure dans la demande.

                            </p>

                        </div>

                    </div>


                    {{-- SEARCH / FILTER --}}

                    <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row">

                        <div class="relative min-w-0 flex-1">

                            <label for="equipment-search" class="sr-only">

                                Rechercher un équipement

                            </label>

                            <svg
                                class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-slate-400"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >

                                <circle cx="11" cy="11" r="7"/>
                                <path d="m16 16 4 4"/>

                            </svg>

                            <input
                                id="equipment-search"
                                type="search"
                                placeholder="Rechercher un équipement..."
                                class="{{ $inputClass }} pl-11"
                            >

                        </div>

                        <div class="sm:w-52">

                            <label for="equipment-category" class="sr-only">

                                Catégorie

                            </label>

                            <select
                                id="equipment-category"
                                class="{{ $inputClass }}"
                            >

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


                    {{-- CATALOGUE COUNT --}}

                    <div class="flex items-center justify-between px-5 pt-5">

                        <p class="text-xs font-medium text-slate-500">

                            <span id="equipment-count">{{ $equipment->count() }}</span>

                            équipement(s)

                        </p>

                        <span class="text-xs text-slate-400">

                            Prix en DH

                        </span>

                    </div>


                    {{-- EQUIPMENT CARDS --}}

                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 2xl:grid-cols-3">

                        @foreach ($equipment as $item)

                            <article
                                data-equipment-card
                                data-equipment-id="{{ $item->id }}"
                                data-name="{{ mb_strtolower($item->name) }}"
                                data-category="{{ $item->category }}"
                                class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:border-blue-200 hover:shadow-md"
                            >


                                {{-- EQUIPMENT IMAGE --}}

                                <div class="relative grid h-36 place-items-center overflow-hidden border-b border-slate-100 bg-[#F8FAFC]">

                                    @if ($item->image_path)

                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}"
                                            alt="{{ $item->name }}"
                                            loading="lazy"
                                            class="h-full w-full object-contain p-4"
                                            data-catalogue-image
                                        >

                                        <span
                                            data-image-fallback
                                            class="hidden text-slate-300"
                                        >

                                            <x-admin.icon
                                                name="box"
                                                class="size-12"
                                            />

                                        </span>

                                    @else

                                        <x-admin.icon
                                            name="box"
                                            class="size-12 text-slate-300"
                                        />

                                    @endif

                                    <span @class([
                                        'absolute right-3 top-3 rounded-lg px-2.5 py-1 text-[10px] font-bold',
                                        'bg-emerald-100 text-emerald-700' => $item->quantity_available > 0,
                                        'bg-red-100 text-red-700' => $item->quantity_available <= 0,
                                    ])>

                                        {{ $item->quantity_available > 0
                                            ? 'Disponible'
                                            : 'Indisponible' }}

                                    </span>

                                </div>


                                {{-- EQUIPMENT INFORMATION --}}

                                <div class="flex flex-1 flex-col p-4">

                                    <p class="text-[11px] font-medium text-slate-400">

                                        {{ $item->category ?: 'Équipement' }}

                                    </p>

                                    <h3 class="mt-1 break-words text-sm font-bold text-[#0F2347]">

                                        {{ $item->name }}

                                    </h3>

                                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2">

                                        <p class="text-lg font-extrabold text-[#0F2347]">

                                            {{ number_format((float) $item->price, 2, ',', ' ') }}

                                            <span class="text-xs font-semibold text-slate-500">

                                                DH

                                            </span>

                                        </p>

                                    </div>

                                    <p class="mt-2 text-xs font-medium {{ $item->quantity_available > 0 ? 'text-emerald-600' : 'text-red-600' }}">

                                        Stock : {{ $item->quantity_available }}

                                    </p>


                                    {{-- QUANTITY / ADD BUTTON --}}

                                    <div class="mt-auto pt-4">

                                        <div class="flex flex-wrap items-center gap-2">

                                            <div class="flex h-9 items-center rounded-lg border border-slate-200 bg-slate-50">

                                                <button
                                                    type="button"
                                                    data-quantity-minus="{{ $item->id }}"
                                                    aria-label="Diminuer la quantité de {{ $item->name }}"
                                                    class="grid h-full w-8 place-items-center text-slate-600 hover:text-blue-600 disabled:opacity-40"
                                                    @disabled($item->quantity_available <= 0)
                                                >

                                                    −

                                                </button>

                                                <input
                                                    type="number"
                                                    id="equipment-quantity-{{ $item->id }}"
                                                    value="1"
                                                    min="1"
                                                    max="{{ min(100, max(1, $item->quantity_available)) }}"
                                                    aria-label="Quantité de {{ $item->name }}"
                                                    class="h-full w-10 border-0 bg-transparent p-0 text-center text-sm font-bold text-[#0F2347] focus:ring-0"
                                                    @disabled($item->quantity_available <= 0)
                                                >

                                                <button
                                                    type="button"
                                                    data-quantity-plus="{{ $item->id }}"
                                                    aria-label="Augmenter la quantité de {{ $item->name }}"
                                                    class="grid h-full w-8 place-items-center text-slate-600 hover:text-blue-600 disabled:opacity-40"
                                                    @disabled($item->quantity_available <= 0)
                                                >

                                                    +

                                                </button>

                                            </div>

                                            <button
                                                type="button"
                                                data-add-equipment="{{ $item->id }}"
                                                @disabled($item->quantity_available <= 0)
                                                class="inline-flex h-9 flex-1 items-center justify-center gap-1.5 rounded-lg bg-[#2563EB] px-3 text-xs font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                                            >

                                                <svg
                                                    class="size-4"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    aria-hidden="true"
                                                >

                                                    <path d="M3 3h2l3 12h11l3-9H6"/>
                                                    <circle cx="9" cy="20" r="1"/>
                                                    <circle cx="19" cy="20" r="1"/>

                                                </svg>

                                                Ajouter

                                            </button>

                                        </div>

                                        <p
                                            data-added-label="{{ $item->id }}"
                                            class="mt-2 hidden text-xs font-semibold text-emerald-600"
                                        ></p>

                                    </div>

                                </div>

                            </article>

                        @endforeach

                    </div>


                    {{-- EMPTY CATALOGUE --}}

                    <div
                        id="catalogue-empty"
                        class="{{ $equipment->isEmpty() ? '' : 'hidden' }} px-6 py-12 text-center"
                    >

                        <x-admin.icon
                            name="box"
                            class="mx-auto size-12 text-slate-300"
                        />

                        <p class="mt-4 font-bold text-[#0F2347]">

                            Aucun équipement trouvé

                        </p>

                        <p class="mt-2 text-sm text-slate-500">

                            Modifiez votre recherche ou sélectionnez une autre catégorie.

                        </p>

                    </div>

                </section>


                {{-- SECTION 3 : NOTES --}}

                <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex items-start gap-3 border-b border-slate-100 p-5">

                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-blue-600 text-sm font-bold text-white">

                            3

                        </span>

                        <div>

                            <h2 class="font-extrabold text-[#0F2347]">

                                Notes supplémentaires

                            </h2>

                            <p class="mt-1 text-xs text-slate-500">

                                Informations complémentaires pour l'administration.

                            </p>

                        </div>

                    </div>

                    <div class="p-5">

                        <label
                            for="notes"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >

                            Notes

                            <span class="font-normal text-slate-400">

                                (Facultatif)

                            </span>

                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            rows="4"
                            maxlength="2000"
                            placeholder="Ajoutez une note ou une précision concernant cette demande..."
                            class="w-full resize-y rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                        >{{ old('notes') }}</textarea>

                        <p class="mt-2 text-right text-xs text-slate-400">

                            <span id="notes-counter">0</span>

                            / 2000 caractères

                        </p>

                        @error('notes')

                            <p class="mt-2 text-xs text-red-600">

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                </section>

            </div>


            {{-- RIGHT COLUMN : SHOPPING CART --}}

            <aside class="min-w-0 space-y-5 xl:sticky xl:top-24">

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">


                    {{-- CART HEADER --}}

                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5">

                        <div class="flex items-center gap-3">

                            <span class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-600">

                                <svg
                                    class="size-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >

                                    <path d="M3 3h2l3 12h11l3-9H6"/>
                                    <circle cx="9" cy="20" r="1"/>
                                    <circle cx="19" cy="20" r="1"/>

                                </svg>

                            </span>

                            <h2 class="font-extrabold text-[#0F2347]">

                                Ma sélection

                            </h2>

                        </div>

                        <span
                            id="cart-count"
                            class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600"
                        >

                            0

                        </span>

                    </div>


                    {{-- EMPTY CART --}}

                    <div
                        id="empty-cart"
                        class="px-5 py-10 text-center"
                    >

                        <span class="mx-auto grid size-16 place-items-center rounded-full bg-slate-100 text-slate-400">

                            <x-admin.icon
                                name="box"
                                class="size-8"
                            />

                        </span>

                        <p class="mt-4 text-sm font-bold text-[#0F2347]">

                            Votre sélection est vide

                        </p>

                        <p class="mt-2 text-xs leading-5 text-slate-500">

                            Ajoutez des équipements depuis le catalogue.

                        </p>

                    </div>


                    {{-- DYNAMIC CART ITEMS --}}

                    <div
                        id="cart-items"
                        class="max-h-[420px] divide-y divide-slate-100 overflow-y-auto px-5"
                        aria-live="polite"
                    ></div>


                    {{-- HIDDEN INPUTS FOR SERVER --}}

                    <div id="cart-inputs"></div>


                    {{-- CART TOTAL --}}

                    <div class="space-y-4 border-t border-slate-100 p-5">

                        <div class="flex items-center justify-between">

                            <span class="text-xs text-slate-500">

                                Types d'équipements

                            </span>

                            <span
                                id="cart-types"
                                class="text-sm font-bold text-[#0F2347]"
                            >

                                0

                            </span>

                        </div>

                        <div class="flex items-center justify-between">

                            <span class="text-xs text-slate-500">

                                Quantité totale

                            </span>

                            <span
                                id="cart-total-quantity"
                                class="text-sm font-bold text-[#0F2347]"
                            >

                                0

                            </span>

                        </div>

                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-4">

                            <span class="text-sm font-semibold text-slate-600">

                                Sous-total

                            </span>

                            <span
                                id="cart-subtotal"
                                class="text-sm font-bold text-[#0F2347]"
                            >

                                0,00 DH

                            </span>

                        </div>

                        <div class="flex items-center justify-between gap-3 rounded-xl bg-blue-50 p-4">

                            <span class="font-bold text-[#0F2347]">

                                Total

                            </span>

                            <span
                                id="cart-total"
                                class="text-xl font-extrabold text-blue-600"
                            >

                                0,00 DH

                            </span>

                        </div>


                        {{-- ADMIN VALIDATION INFORMATION --}}

                        <div class="flex items-start gap-3 rounded-xl bg-blue-50 p-4">

                            <svg
                                class="size-5 shrink-0 text-blue-600"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                aria-hidden="true"
                            >

                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 11v5M12 8h.01"/>

                            </svg>

                            <p class="text-xs leading-5 text-blue-800">

                                Le traitement de la demande sera effectué par l'administration du salon.

                            </p>

                        </div>

                        <p
                            id="cart-error"
                            role="alert"
                            class="hidden rounded-lg bg-red-50 p-3 text-xs font-semibold text-red-700"
                        ></p>


                        {{-- NEXT BUTTON --}}

                        <button
                            type="button"
                            id="review-request"
                            class="flex w-full items-center justify-center gap-3 rounded-xl bg-[#2563EB] px-5 py-3.5 text-sm font-bold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            disabled
                        >

                            Suivant

                            <svg
                                class="size-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >

                                <path d="M5 12h14m-7-7 7 7-7 7"/>

                            </svg>

                        </button>

                    </div>

                </section>


                {{-- SUPPORT CARD --}}

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <div class="flex items-center gap-3">

                        <span class="grid size-10 place-items-center rounded-xl bg-violet-50 text-violet-600">

                            <x-admin.icon
                                name="inbox"
                                class="size-5"
                            />

                        </span>

                        <div>

                            <h3 class="text-sm font-bold text-[#0F2347]">

                                Besoin d'assistance ?

                            </h3>

                            <p class="mt-1 text-xs text-slate-500">

                                Retrouvez vos demandes d'assistance.

                            </p>

                        </div>

                    </div>

                    <a
                        href="{{ route('commercial.support.index') }}"
                        class="mt-4 flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                    >

                        Assistance exposants →

                    </a>

                </section>

            </aside>


            {{-- CONFIRMATION MODAL --}}

            <dialog
                id="confirmation-modal"
                class="m-auto w-[calc(100%-2rem)] max-w-xl rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-[#0F2347]/50"
                aria-labelledby="confirmation-title"
            >

                <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5">

                    <div class="flex items-center gap-3">

                        <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">

                            <x-admin.icon
                                name="clipboard-check"
                                class="size-6"
                            />

                        </span>

                        <div>

                            <h2
                                id="confirmation-title"
                                class="text-lg font-extrabold text-[#0F2347]"
                            >

                                Récapitulatif de la demande

                            </h2>

                            <p class="mt-1 text-xs text-slate-500">

                                Vérifiez les informations avant l'envoi.

                            </p>

                        </div>

                    </div>

                    <button
                        type="button"
                        data-close-modal
                        aria-label="Fermer"
                        class="grid size-9 shrink-0 place-items-center rounded-lg text-xl text-slate-400 hover:bg-slate-100"
                    >

                        ×

                    </button>

                </div>


                {{-- MODAL CONTENT --}}

                <div class="max-h-[65vh] space-y-5 overflow-y-auto p-5">

                    <div class="rounded-xl bg-slate-50 p-4">

                        <p class="text-xs font-medium text-slate-500">

                            Entreprise exposante

                        </p>

                        <p
                            id="review-exhibitor"
                            class="mt-2 font-bold text-[#0F2347]"
                        ></p>

                    </div>

                    <div>

                        <h3 class="mb-3 text-sm font-bold text-[#0F2347]">

                            Équipements sélectionnés

                        </h3>

                        <div
                            id="review-items"
                            class="divide-y divide-slate-100 rounded-xl border border-slate-200 px-4"
                        ></div>

                    </div>

                    <div>

                        <h3 class="text-sm font-bold text-[#0F2347]">

                            Notes supplémentaires

                        </h3>

                        <p
                            id="review-notes"
                            class="mt-2 break-words whitespace-pre-wrap rounded-xl bg-slate-50 p-3 text-xs text-slate-600"
                        ></p>

                    </div>

                    <div class="flex items-center justify-between gap-3 rounded-xl bg-blue-50 p-5">

                        <span class="font-bold text-[#0F2347]">

                            Montant total

                        </span>

                        <span
                            id="review-total"
                            class="text-xl font-extrabold text-blue-600"
                        ></span>

                    </div>

                    <p class="text-xs leading-5 text-slate-500">

                        La demande sera enregistrée avec le statut « En attente ».
                        Le stock sera déduit uniquement après validation administrative.

                    </p>

                </div>


                {{-- MODAL ACTIONS --}}

                <div class="flex flex-col gap-3 border-t border-slate-100 p-5 sm:flex-row sm:justify-end">

                    <button
                        type="button"
                        data-close-modal
                        class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                    >

                        Modifier

                    </button>

                    <button
                        type="submit"
                        id="submit-request"
                        class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700 disabled:opacity-50"
                    >

                        Envoyer la demande

                    </button>

                </div>

            </dialog>

        </form>


        {{-- FOOTER --}}

        <footer class="border-t border-slate-200 py-4 text-center text-xs text-slate-400">

            © 2027 Refrigair Expo — Espace Commercial

        </footer>

    </div>


    {{-- JAVASCRIPT --}}

    <script>
        (() => {

            const catalogue = {{ \Illuminate\Support\Js::from($catalogue) }};

            const exhibitors = {{ \Illuminate\Support\Js::from($exhibitorData) }};

            const oldItems = {{ \Illuminate\Support\Js::from(old('items', [])) }};

            const products = new Map(
                catalogue.map(item => [Number(item.id), item])
            );

            const cart = new Map();

            const form = document.getElementById('equipment-request-form');

            const exhibitorSelect = document.getElementById('exhibitor_id');

            const notes = document.getElementById('notes');

            const modal = document.getElementById('confirmation-modal');

            const reviewButton = document.getElementById('review-request');

            const submitButton = document.getElementById('submit-request');

            const cartError = document.getElementById('cart-error');

            let confirmed = false;
            let submitting = false;


            /*
            |--------------------------------------------------------------------------
            | HELPERS
            |--------------------------------------------------------------------------
            */

            const money = cents => new Intl.NumberFormat('fr-MA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(cents / 100) + ' DH';

            function element(tag, className = '', text = '') {

                const node = document.createElement(tag);

                node.className = className;

                if (text !== '') {
                    node.textContent = text;
                }

                return node;

            }

            function setText(id, value) {

                document.getElementById(id).textContent = value;

            }

            function showError(message) {

                cartError.textContent = message;

                cartError.classList.toggle('hidden', !message);

            }

            function currentExhibitor() {

                return exhibitors.find(item =>
                    Number(item.id) === Number(exhibitorSelect.value)
                );

            }


            /*
            |--------------------------------------------------------------------------
            | STEPPER
            |--------------------------------------------------------------------------
            */

            function updateSteps(step = null) {

                const activeStep = step ?? (
                    !currentExhibitor() ? 1 : 2
                );

                document.querySelectorAll('[data-step]')
                    .forEach(node => {

                        const active = Number(node.dataset.step)
                            <= activeStep;

                        node.classList.toggle('bg-blue-600', active);
                        node.classList.toggle('text-white', active);
                        node.classList.toggle('border-blue-600', active);

                        node.classList.toggle('bg-slate-50', !active);
                        node.classList.toggle('text-slate-500', !active);
                        node.classList.toggle('border-slate-200', !active);

                    });

                document.getElementById('step-progress')
                    .style.width = `${activeStep / 3 * 100}%`;

            }


            /*
            |--------------------------------------------------------------------------
            | SELECTED EXHIBITOR
            |--------------------------------------------------------------------------
            */

            function updateExhibitor() {

                const exhibitor = currentExhibitor();

                const card = document.getElementById('selected-exhibitor');

                const logo = document.getElementById(
                    'selected-exhibitor-logo'
                );

                const initials = document.getElementById(
                    'selected-exhibitor-initials'
                );

                card.classList.toggle('hidden', !exhibitor);

                if (exhibitor) {

                    setText('selected-exhibitor-name', exhibitor.name);

                    setText(
                        'selected-exhibitor-contact',
                        exhibitor.contact || '—'
                    );

                    setText(
                        'selected-exhibitor-stand',
                        `Stand ${exhibitor.stand || '—'}`
                    );

                    setText(
                        'selected-exhibitor-area',
                        `${exhibitor.area || 0} m²`
                    );

                    initials.textContent = exhibitor.name
                        .trim()
                        .slice(0, 2)
                        .toUpperCase();

                    if (exhibitor.logo) {

                        logo.classList.remove('hidden');
                        initials.classList.add('hidden');

                        logo.onerror = () => {

                            logo.classList.add('hidden');
                            initials.classList.remove('hidden');

                        };

                        logo.src = exhibitor.logo;

                    } else {

                        logo.removeAttribute('src');

                        logo.classList.add('hidden');
                        initials.classList.remove('hidden');

                    }

                }

                confirmed = false;

                showError('');

                renderCart();

                updateSteps();

            }

            exhibitorSelect.addEventListener(
                'change',
                updateExhibitor
            );


            /*
            |--------------------------------------------------------------------------
            | SEARCH AND CATEGORY FILTER
            |--------------------------------------------------------------------------
            */

            const searchInput = document.getElementById(
                'equipment-search'
            );

            const categoryInput = document.getElementById(
                'equipment-category'
            );

            function filterCatalogue() {

                const term = searchInput.value
                    .trim()
                    .toLowerCase();

                const category = categoryInput.value;

                let visible = 0;

                document.querySelectorAll('[data-equipment-card]')
                    .forEach(card => {

                        const matchesSearch =
                            card.dataset.name.includes(term) ||
                            card.dataset.category.toLowerCase().includes(term);

                        const matchesCategory =
                            !category ||
                            card.dataset.category === category;

                        const show = matchesSearch && matchesCategory;

                        card.classList.toggle('hidden', !show);

                        if (show) visible++;

                    });

                setText('equipment-count', visible);

                document.getElementById('catalogue-empty')
                    .classList.toggle('hidden', visible > 0);

            }

            searchInput.addEventListener('input', filterCatalogue);

            categoryInput.addEventListener('change', filterCatalogue);


            /*
            |--------------------------------------------------------------------------
            | QUANTITY CONTROLS
            |--------------------------------------------------------------------------
            */

            function getQuantityInput(id) {

                return document.getElementById(
                    `equipment-quantity-${id}`
                );

            }

            function normalizeQuantity(input) {

                const max = Math.max(1, Number(input.max) || 1);

                return Math.max(
                    1,
                    Math.min(
                        max,
                        Math.floor(Number(input.value) || 1)
                    )
                );

            }

            document.querySelectorAll('[data-quantity-minus]')
                .forEach(button => {

                    button.addEventListener('click', () => {

                        const input = getQuantityInput(
                            button.dataset.quantityMinus
                        );

                        input.value = Math.max(
                            1,
                            normalizeQuantity(input) - 1
                        );

                    });

                });

            document.querySelectorAll('[data-quantity-plus]')
                .forEach(button => {

                    button.addEventListener('click', () => {

                        const input = getQuantityInput(
                            button.dataset.quantityPlus
                        );

                        input.value = Math.min(
                            Number(input.max),
                            normalizeQuantity(input) + 1
                        );

                    });

                });

            document.querySelectorAll('[id^="equipment-quantity-"]')
                .forEach(input => {

                    input.addEventListener('change', () => {

                        input.value = normalizeQuantity(input);

                    });

                });


            /*
            |--------------------------------------------------------------------------
            | ADD EQUIPMENT
            |--------------------------------------------------------------------------
            */

            document.querySelectorAll('[data-add-equipment]')
                .forEach(button => {

                    button.addEventListener('click', () => {

                        const id = Number(
                            button.dataset.addEquipment
                        );

                        const product = products.get(id);

                        if (!product || product.stock <= 0) return;

                        const quantity = normalizeQuantity(
                            getQuantityInput(id)
                        );

                        const currentQuantity = cart.get(id) || 0;

                        if (
                            !cart.has(id) &&
                            cart.size >= 100
                        ) {

                            showError(
                                'Vous pouvez sélectionner au maximum 100 équipements différents.'
                            );

                            return;

                        }

                        if (currentQuantity + quantity > product.stock) {

                            showError(
                                `Quantité maximale disponible pour ${product.name} : ${product.stock}.`
                            );

                            return;

                        }

                        cart.set(id, currentQuantity + quantity);

                        showError('');

                        renderCart();

                    });

                });


            /*
            |--------------------------------------------------------------------------
            | CART ITEMS
            |--------------------------------------------------------------------------
            */

            function changeCartQuantity(id, amount) {

                const product = products.get(id);

                const quantity = (cart.get(id) || 0) + amount;

                if (!product) return;

                if (quantity <= 0) {

                    cart.delete(id);

                } else if (quantity > product.stock) {

                    showError(
                        `Stock maximum disponible : ${product.stock}.`
                    );

                    return;

                } else {

                    cart.set(id, quantity);

                }

                showError('');

                renderCart();

            }


            function createCartImage(product) {

                const wrapper = element(
                    'div',
                    'grid size-14 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-100 bg-slate-50'
                );

                const fallback = element(
                    'span',
                    'text-xl font-bold text-slate-400',
                    product.name.slice(0, 2).toUpperCase()
                );

                if (product.image) {

                    const image = element(
                        'img',
                        'h-full w-full object-contain p-1.5'
                    );

                    image.alt = product.name;

                    fallback.classList.add('hidden');

                    image.onerror = () => {

                        image.classList.add('hidden');
                        fallback.classList.remove('hidden');

                    };

                    image.src = product.image;

                    wrapper.append(image);

                }

                wrapper.append(fallback);

                return wrapper;

            }


            function createCartItem(product, quantity) {

                const row = element(
                    'div',
                    'flex items-start gap-3 py-4'
                );

                row.append(createCartImage(product));

                const content = element('div', 'min-w-0 flex-1');

                const name = element(
                    'p',
                    'break-words text-xs font-bold text-[#0F2347]',
                    product.name
                );

                const price = element(
                    'p',
                    'mt-1 text-xs text-slate-500',
                    money(product.price) + ' / unité'
                );

                content.append(name, price);

                const actions = element(
                    'div',
                    'mt-3 flex flex-wrap items-center justify-between gap-2'
                );

                const controls = element(
                    'div',
                    'flex h-8 items-center rounded-lg border border-slate-200'
                );

                const minus = element(
                    'button',
                    'h-full w-7 text-sm text-slate-600 hover:bg-slate-100',
                    '−'
                );

                minus.type = 'button';

                minus.setAttribute(
                    'aria-label',
                    `Diminuer ${product.name}`
                );

                minus.addEventListener('click', () =>
                    changeCartQuantity(product.id, -1)
                );

                const current = element(
                    'span',
                    'min-w-7 text-center text-xs font-bold text-[#0F2347]',
                    String(quantity)
                );

                const plus = element(
                    'button',
                    'h-full w-7 text-sm text-slate-600 hover:bg-slate-100 disabled:opacity-30',
                    '+'
                );

                plus.type = 'button';

                plus.disabled = quantity >= product.stock;

                plus.setAttribute(
                    'aria-label',
                    `Augmenter ${product.name}`
                );

                plus.addEventListener('click', () =>
                    changeCartQuantity(product.id, 1)
                );

                controls.append(minus, current, plus);

                const subtotal = element(
                    'span',
                    'text-xs font-bold text-blue-600',
                    money(product.price * quantity)
                );

                actions.append(controls, subtotal);

                content.append(actions);

                const remove = element(
                    'button',
                    'grid size-7 shrink-0 place-items-center rounded-lg text-lg text-red-500 hover:bg-red-50',
                    '×'
                );

                remove.type = 'button';

                remove.setAttribute(
                    'aria-label',
                    `Retirer ${product.name}`
                );

                remove.addEventListener('click', () => {

                    cart.delete(product.id);

                    showError('');

                    renderCart();

                });

                row.append(content, remove);

                return row;

            }


            /*
            |--------------------------------------------------------------------------
            | RENDER CART
            |--------------------------------------------------------------------------
            */

            function renderCart() {

                confirmed = false;

                const container = document.getElementById('cart-items');

                const inputs = document.getElementById('cart-inputs');

                container.replaceChildren();

                inputs.replaceChildren();

                let total = 0;

                let quantityTotal = 0;

                cart.forEach((quantity, id) => {

                    const product = products.get(id);

                    if (!product) return;

                    total += product.price * quantity;

                    quantityTotal += quantity;

                    container.append(
                        createCartItem(product, quantity)
                    );

                    const input = document.createElement('input');

                    input.type = 'hidden';

                    input.name = `items[${id}]`;

                    input.value = String(quantity);

                    inputs.append(input);

                });

                document.getElementById('empty-cart')
                    .classList.toggle('hidden', cart.size > 0);

                setText('cart-count', cart.size);

                setText('cart-types', cart.size);

                setText('cart-total-quantity', quantityTotal);

                setText('cart-subtotal', money(total));

                setText('cart-total', money(total));

                reviewButton.disabled =
                    !currentExhibitor() ||
                    cart.size === 0 ||
                    submitting;


                // Mettre à jour les cartes du catalogue.

                document.querySelectorAll('[data-added-label]')
                    .forEach(label => {

                        const id = Number(label.dataset.addedLabel);

                        const quantity = cart.get(id) || 0;

                        label.textContent =
                            `${quantity} ajouté(s) à la sélection`;

                        label.classList.toggle(
                            'hidden',
                            quantity === 0
                        );

                    });

            }


            /*
            |--------------------------------------------------------------------------
            | NOTES COUNTER
            |--------------------------------------------------------------------------
            */

            function updateNotes() {

                setText('notes-counter', notes.value.length);

                confirmed = false;

            }

            notes.addEventListener('input', updateNotes);


            /*
            |--------------------------------------------------------------------------
            | REVIEW MODAL
            |--------------------------------------------------------------------------
            */

            reviewButton.addEventListener('click', () => {

                const exhibitor = currentExhibitor();

                if (!exhibitor || cart.size === 0) {

                    showError(
                        'Sélectionnez un exposant et au moins un équipement.'
                    );

                    return;

                }

                if (!form.reportValidity()) return;

                setText(
                    'review-exhibitor',
                    `${exhibitor.name} — Stand ${exhibitor.stand || '—'}`
                );

                setText(
                    'review-notes',
                    notes.value.trim() || 'Aucune note.'
                );

                const reviewItems = document.getElementById(
                    'review-items'
                );

                reviewItems.replaceChildren();

                let total = 0;

                cart.forEach((quantity, id) => {

                    const product = products.get(id);

                    total += product.price * quantity;

                    const row = element(
                        'div',
                        'flex items-center justify-between gap-3 py-3'
                    );

                    const left = element('div', 'min-w-0 flex-1');

                    left.append(
                        element(
                            'p',
                            'break-words text-sm font-semibold text-[#0F2347]',
                            product.name
                        ),
                        element(
                            'p',
                            'mt-1 text-xs text-slate-500',
                            `${quantity} × ${money(product.price)}`
                        )
                    );

                    row.append(
                        left,
                        element(
                            'span',
                            'shrink-0 text-xs font-bold text-blue-600',
                            money(quantity * product.price)
                        )
                    );

                    reviewItems.append(row);

                });

                setText('review-total', money(total));

                confirmed = true;

                updateSteps(3);

                modal.showModal();

            });


            /*
            |--------------------------------------------------------------------------
            | CLOSE MODAL
            |--------------------------------------------------------------------------
            */

            document.querySelectorAll('[data-close-modal]')
                .forEach(button => {

                    button.addEventListener('click', () => {

                        modal.close();

                    });

                });

            modal.addEventListener('close', () => {

                confirmed = false;

                updateSteps();

            });


            /*
            |--------------------------------------------------------------------------
            | SUBMIT REQUEST
            |--------------------------------------------------------------------------
            */

            form.addEventListener('submit', event => {

                if (
                    !confirmed ||
                    !modal.open ||
                    submitting ||
                    !currentExhibitor() ||
                    cart.size === 0
                ) {

                    event.preventDefault();

                    return;

                }

                if (event.defaultPrevented) return;

                submitting = true;

                submitButton.disabled = true;

                reviewButton.disabled = true;

                submitButton.textContent = 'Envoi en cours...';

                form.setAttribute('aria-busy', 'true');

            });

            window.addEventListener('pageshow', () => {

                submitting = false;

                submitButton.disabled = false;

                submitButton.textContent = 'Envoyer la demande';

                form.removeAttribute('aria-busy');

                if (modal.open) {
                    modal.close();
                }

                renderCart();

            });


            /*
            |--------------------------------------------------------------------------
            | IMAGE FALLBACK
            |--------------------------------------------------------------------------
            */

            document.querySelectorAll('[data-catalogue-image]')
                .forEach(image => {

                    const fallback = () => {

                        image.classList.add('hidden');

                        image.parentElement
                            .querySelector('[data-image-fallback]')
                            ?.classList.remove('hidden');

                    };

                    image.addEventListener('error', fallback);

                    if (
                        image.complete &&
                        image.naturalWidth === 0
                    ) {

                        fallback();

                    }

                });


            /*
            |--------------------------------------------------------------------------
            | RESTORE CART AFTER VALIDATION ERROR
            |--------------------------------------------------------------------------
            */

            let adjusted = false;

            Object.entries(oldItems || {})
                .forEach(([id, value]) => {

                    const product = products.get(Number(id));

                    const quantity = Math.floor(Number(value));

                    if (
                        !product ||
                        !Number.isFinite(quantity) ||
                        quantity < 1 ||
                        product.stock <= 0 ||
                        cart.size >= 100
                    ) {

                        adjusted = true;

                        return;

                    }

                    const restored = Math.min(
                        quantity,
                        product.stock
                    );

                    if (restored !== quantity) {

                        adjusted = true;

                    }

                    cart.set(Number(id), restored);

                });


            /*
            |--------------------------------------------------------------------------
            | INITIALIZATION
            |--------------------------------------------------------------------------
            */

            updateExhibitor();

            updateNotes();

            filterCatalogue();

            renderCart();

            updateSteps();

            if (adjusted) {

                showError(
                    'Votre sélection a été ajustée selon les disponibilités actuelles.'
                );

            }

        })();
    </script>

</x-commercial.layout>
