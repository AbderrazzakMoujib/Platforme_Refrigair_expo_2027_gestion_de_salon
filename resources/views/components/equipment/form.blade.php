
@props([
    'equipmentItem',
    'categories',
    'pageTitle',
    'formAction',
    'formMethod' => null,
    'routePrefix',
    'dashboardRoute',
    'spaceLabel',
])

@php
    $isEdit = $equipmentItem->exists;

    $imageUrl = $equipmentItem->image_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($equipmentItem->image_path)
        : null;

    $inputClass = 'mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-blue-500';

    $labelClass = 'text-sm font-semibold text-slate-700';

    $isActive = (string) old('is_active', (int) $equipmentItem->is_active) === '1';
@endphp

<div class="min-w-0 space-y-7">

    {{-- BREADCRUMB --}}

    <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500">

        <a href="{{ route($dashboardRoute) }}"
           class="transition hover:text-blue-600">

            {{ $spaceLabel }}

        </a>

        <span>/</span>

        <a href="{{ route($routePrefix . '.index') }}"
           class="transition hover:text-blue-600">

            Équipements

        </a>

        <span>/</span>

        <span class="font-semibold text-[#0F2347]">

            {{ $isEdit ? 'Modifier' : 'Ajouter' }}

        </span>

    </nav>


    {{-- PAGE HEADER --}}

    <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div class="flex items-center gap-4">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">

                <x-admin.icon name="box" class="size-7"/>

            </span>

            <div>

                <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">

                    {{ $pageTitle }}

                </h1>

                <p class="mt-2 text-sm text-slate-500">

                    {{ $isEdit
                        ? 'Modifiez les informations de votre équipement.'
                        : 'Complétez les informations du nouvel équipement.' }}

                </p>

            </div>

        </div>

        <a href="{{ route($routePrefix . '.index') }}"
           class="inline-flex w-fit items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">

            <svg class="size-5"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.8"
                 stroke-linecap="round"
                 stroke-linejoin="round">

                <path d="m12 19-7-7 7-7M5 12h14"/>

            </svg>

            Retour aux équipements

        </a>

    </header>


    {{-- VALIDATION ERRORS --}}

    @if ($errors->any())

        <div role="alert"
             class="rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-700">

            <p class="font-bold">

                Veuillez corriger les erreurs suivantes :

            </p>

            <ul class="mt-2 list-disc space-y-1 pl-5">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- MAIN FORM --}}

    <form id="equipment-form"
          method="POST"
          action="{{ $formAction }}"
          enctype="multipart/form-data">

        @csrf

        @if ($formMethod)
            @method($formMethod)
        @endif

        <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">


            {{-- LEFT COLUMN --}}

            <div class="min-w-0 space-y-6">


                {{-- GENERAL INFORMATION --}}

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-5">

                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-600">

                            <svg class="size-5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 stroke-linecap="round"
                                 stroke-linejoin="round">

                                <rect x="5" y="4" width="14" height="17" rx="2"/>
                                <path d="M9 9h6M9 13h6M9 17h4"/>

                            </svg>

                        </span>

                        <div>

                            <h2 class="text-lg font-bold text-[#0F2347]">

                                Informations générales

                            </h2>

                            <p class="mt-1 text-xs text-slate-500">

                                Identification et description de l'équipement.

                            </p>

                        </div>

                    </div>

                    <div class="space-y-6 p-6">


                        {{-- NAME --}}

                        <div>

                            <label for="name" class="{{ $labelClass }}">

                                Nom de l'équipement

                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name', $equipmentItem->name) }}"
                                maxlength="255"
                                required
                                placeholder="Ex : Chaise de conférence"
                                class="{{ $inputClass }} @error('name') border-red-400 @enderror"
                            >

                            @error('name')

                                <p class="mt-2 text-xs text-red-600">

                                    {{ $message }}

                                </p>

                            @enderror

                        </div>


                        {{-- CATEGORY --}}

                        <div>

                            <label for="category" class="{{ $labelClass }}">

                                Catégorie

                                <span class="text-red-500">*</span>

                            </label>

                            <select
                                id="category"
                                name="category"
                                required
                                class="{{ $inputClass }} @error('category') border-red-400 @enderror"
                            >

                                <option value="">

                                    Sélectionnez une catégorie

                                </option>

                                @foreach ($categories as $category)

                                    <option value="{{ $category }}"
                                        @selected(old('category', $equipmentItem->category) === $category)>

                                        {{ $category }}

                                    </option>

                                @endforeach

                            </select>

                            @error('category')

                                <p class="mt-2 text-xs text-red-600">

                                    {{ $message }}

                                </p>

                            @enderror

                        </div>


                        {{-- DESCRIPTION --}}

                        <div>

                            <label for="description" class="{{ $labelClass }}">

                                Description

                            </label>

                            <textarea
                                id="description"
                                name="description"
                                rows="6"
                                maxlength="5000"
                                placeholder="Présentez les caractéristiques, dimensions et informations utiles de cet équipement..."
                                class="{{ $inputClass }} resize-y @error('description') border-red-400 @enderror"
                            >{{ old('description', $equipmentItem->description) }}</textarea>

                            <div class="mt-2 flex items-center justify-between gap-3">

                                <p class="text-xs text-slate-400">

                                    Informations utiles pour les exposants.

                                </p>

                                <span id="description-counter"
                                      class="text-xs text-slate-400">

                                    0 / 5000

                                </span>

                            </div>

                            @error('description')

                                <p class="mt-2 text-xs text-red-600">

                                    {{ $message }}

                                </p>

                            @enderror

                        </div>

                    </div>

                </section>


                {{-- PRICING AND STOCK --}}

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-5">

                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600">

                            <svg class="size-5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 stroke-linecap="round"
                                 stroke-linejoin="round">

                                <rect x="3" y="5" width="18" height="14" rx="2"/>
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M7 12h.01M17 12h.01"/>

                            </svg>

                        </span>

                        <div>

                            <h2 class="text-lg font-bold text-[#0F2347]">

                                Tarification et stock

                            </h2>

                            <p class="mt-1 text-xs text-slate-500">

                                Définissez le prix et la quantité disponible.

                            </p>

                        </div>

                    </div>

                    <div class="grid gap-6 p-6 sm:grid-cols-2">


                        {{-- PRICE --}}

                        <div>

                            <label for="price" class="{{ $labelClass }}">

                                Prix unitaire

                                <span class="text-red-500">*</span>

                            </label>

                            <div class="relative mt-2">

                                <input
                                    type="number"
                                    id="price"
                                    name="price"
                                    min="0"
                                    max="99999999.99"
                                    step="0.01"
                                    required
                                    value="{{ old('price', $equipmentItem->price) }}"
                                    placeholder="0.00"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-3 pr-16 text-sm focus:border-blue-500 focus:ring-blue-500 @error('price') border-red-400 @enderror"
                                >

                                <span class="pointer-events-none absolute right-0 top-0 flex h-full items-center rounded-r-xl border-l border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-500">

                                    DH

                                </span>

                            </div>

                            <p class="mt-2 text-xs text-slate-400">

                                Prix en dirhams marocains.

                            </p>

                            @error('price')

                                <p class="mt-2 text-xs text-red-600">

                                    {{ $message }}

                                </p>

                            @enderror

                        </div>


                        {{-- QUANTITY --}}

                        <div>

                            <label for="quantity_available" class="{{ $labelClass }}">

                                Quantité disponible

                                <span class="text-red-500">*</span>

                            </label>

                            <input
                                type="number"
                                id="quantity_available"
                                name="quantity_available"
                                value="{{ old('quantity_available', $equipmentItem->quantity_available) }}"
                                min="0"
                                max="1000000"
                                step="1"
                                required
                                placeholder="0"
                                class="{{ $inputClass }} @error('quantity_available') border-red-400 @enderror"
                            >

                            <p class="mt-2 text-xs text-slate-400">

                                Nombre d'unités disponibles.

                            </p>

                            @error('quantity_available')

                                <p class="mt-2 text-xs text-red-600">

                                    {{ $message }}

                                </p>

                            @enderror

                        </div>


                        {{-- STOCK INDICATOR --}}

                        <div class="sm:col-span-2">

                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-blue-100 bg-blue-50 p-4">

                                <div class="flex items-center gap-3">

                                    <x-admin.icon
                                        name="box"
                                        class="size-6 text-blue-600"
                                    />

                                    <div>

                                        <p class="text-sm font-semibold text-[#0F2347]">

                                            État du stock

                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">

                                            Selon la quantité renseignée.

                                        </p>

                                    </div>

                                </div>

                                <span id="stock-indicator"
                                      class="rounded-full bg-white px-3 py-2 text-xs font-bold text-slate-600">

                                    En attente

                                </span>

                            </div>

                        </div>

                    </div>

                </section>

            </div>


            {{-- RIGHT COLUMN --}}

            <aside class="min-w-0 space-y-6">


                {{-- EQUIPMENT IMAGE --}}

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-100 px-5 py-5">

                        <h2 class="font-bold text-[#0F2347]">

                            Photo de l'équipement

                        </h2>

                        <p class="mt-1 text-xs text-slate-500">

                            Image principale du catalogue.

                        </p>

                    </div>

                    <div class="p-5">

                        <input
                            type="file"
                            id="image"
                            name="image"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            class="peer sr-only"
                        >

                        <label id="upload-zone"
                               for="image"
                               class="group flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-200 bg-[#F8FAFC] p-4 text-center transition hover:border-blue-400 hover:bg-blue-50 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500">


                            {{-- PLACEHOLDER --}}

                            <div id="upload-placeholder"
                                 class="{{ $imageUrl ? 'hidden' : '' }} py-7">

                                <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-blue-100 text-blue-600">

                                    <svg class="size-7"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8"
                                         stroke-linecap="round"
                                         stroke-linejoin="round">

                                        <rect x="3" y="3" width="18" height="18" rx="3"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <path d="m21 15-5-5L5 21"/>

                                    </svg>

                                </span>

                                <p class="mt-4 text-sm font-bold text-blue-600">

                                    Choisir une image

                                </p>

                                <p class="mt-2 text-xs text-slate-500">

                                    Ou glissez votre image ici

                                </p>

                            </div>


                            {{-- IMAGE PREVIEW --}}

                            <img
                                id="image-preview"
                                @if ($imageUrl)
                                    src="{{ $imageUrl }}"
                                @endif
                                alt="Aperçu de l'équipement"
                                class="{{ $imageUrl ? '' : 'hidden' }} aspect-square w-full rounded-lg bg-white object-contain"
                            >

                        </label>

                        <p id="image-filename"
                           class="mt-3 truncate text-center text-xs font-medium text-slate-500">

                            {{ $imageUrl
                                ? 'Image actuelle'
                                : 'Aucun fichier sélectionné' }}

                        </p>

                        <p id="image-error"
                           role="alert"
                           class="mt-2 hidden text-xs font-semibold text-red-600">

                        </p>

                        <button type="button"
                                id="reset-image"
                                class="mt-3 hidden w-full rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">

                            Annuler la nouvelle image

                        </button>

                        <div class="mt-4 rounded-lg bg-slate-50 p-3">

                            <p class="text-xs text-slate-500">

                                Formats : JPG, JPEG, PNG et WebP.

                            </p>

                            <p class="mt-1 text-xs text-slate-500">

                                Taille maximale : 2 Mo.

                            </p>

                            @if ($isEdit)

                                <p class="mt-2 text-xs text-slate-500">

                                    Sans nouvelle image, la photo actuelle sera conservée.

                                </p>

                            @endif

                        </div>

                        @error('image')

                            <p class="mt-2 text-xs text-red-600">

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                </section>


                {{-- STATUS --}}

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

                    <h2 class="font-bold text-[#0F2347]">

                        Disponibilité

                    </h2>

                    <p class="mt-1 text-xs text-slate-500">

                        Gérez le statut de cet équipement.

                    </p>

                    <input
                        type="hidden"
                        name="is_active"
                        id="is_active"
                        value="{{ $isActive ? '1' : '0' }}"
                    >

                    <div class="mt-5 flex items-center justify-between gap-4 rounded-xl border border-slate-200 p-4">

                        <div>

                            <p id="status-label"
                               class="text-sm font-bold text-[#0F2347]">

                                {{ $isActive
                                    ? 'Équipement actif'
                                    : 'Équipement inactif' }}

                            </p>

                            <p id="status-description"
                               class="mt-1 text-xs text-slate-500">

                                {{ $isActive
                                    ? 'Disponible dans le catalogue.'
                                    : 'Non proposé dans le catalogue.' }}

                            </p>

                        </div>

                        <button type="button"
                                id="status-toggle"
                                role="switch"
                                aria-checked="{{ $isActive ? 'true' : 'false' }}"
                                aria-label="Activer ou désactiver l'équipement"
                                class="relative inline-flex h-7 w-12 shrink-0 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $isActive ? 'bg-emerald-500' : 'bg-slate-300' }}">

                            <span id="status-thumb"
                                  class="inline-block size-5 transform rounded-full bg-white shadow-sm transition-transform {{ $isActive ? 'translate-x-6' : 'translate-x-1' }}">

                            </span>

                        </button>

                    </div>

                    @error('is_active')

                        <p class="mt-2 text-xs text-red-600">

                            {{ $message }}

                        </p>

                    @enderror

                </section>


                {{-- LIVE CATALOGUE PREVIEW --}}

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="border-b border-slate-100 px-5 py-4">

                        <h2 class="font-bold text-[#0F2347]">

                            Aperçu catalogue

                        </h2>

                        <p class="mt-1 text-xs text-slate-500">

                            Prévisualisation en temps réel.

                        </p>

                    </div>

                    <div class="p-5">

                        <div class="overflow-hidden rounded-xl border border-slate-200">

                            <div class="relative grid aspect-[4/3] place-items-center overflow-hidden bg-slate-50">

                                <img
                                    id="catalogue-image"
                                    @if ($imageUrl)
                                        src="{{ $imageUrl }}"
                                    @endif
                                    alt="Aperçu catalogue"
                                    class="{{ $imageUrl ? '' : 'hidden' }} h-full w-full object-contain p-3"
                                >

                                <div id="catalogue-placeholder"
                                     class="{{ $imageUrl ? 'hidden' : '' }} text-center text-slate-300">

                                    <x-admin.icon
                                        name="box"
                                        class="mx-auto size-14"
                                    />

                                    <p class="mt-2 text-xs">

                                        Aucune image

                                    </p>

                                </div>

                                <span id="catalogue-status"
                                      class="absolute right-3 top-3 rounded-full bg-white px-3 py-1.5 text-xs font-bold shadow-sm {{ $isActive ? 'text-emerald-600' : 'text-red-600' }}">

                                    {{ $isActive ? 'Actif' : 'Inactif' }}

                                </span>

                            </div>

                            <div class="space-y-3 border-t border-slate-100 p-4">

                                <span id="catalogue-category"
                                      class="inline-flex rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-600">

                                    Catégorie

                                </span>

                                <h3 id="catalogue-name"
                                    class="break-words font-bold text-[#0F2347]">

                                    Nom de l'équipement

                                </h3>

                                <p id="catalogue-description"
                                   class="line-clamp-2 break-words text-xs leading-relaxed text-slate-500">

                                    Description de l'équipement.

                                </p>

                                <div class="border-t border-slate-100 pt-3">

                                    <p id="catalogue-price"
                                       class="text-xl font-extrabold text-[#2563EB]">

                                        0,00 DH

                                    </p>

                                    <p id="catalogue-stock"
                                       class="mt-2 text-xs font-semibold text-slate-500">

                                        0 unité disponible

                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </section>

            </aside>

        </div>


        {{-- FORM ACTIONS --}}

        <div class="mt-6 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">

            <p class="text-xs text-slate-500">

                Les champs marqués d'un
                <span class="font-bold text-red-500">*</span>
                sont obligatoires.

            </p>

            <div class="flex flex-wrap justify-end gap-3">

                <a href="{{ route($routePrefix . '.index') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">

                    Annuler

                </a>

                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">

                    <svg class="size-5"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         stroke-linecap="round"
                         stroke-linejoin="round">

                        <path d="m5 12 4 4L19 6"/>

                    </svg>

                    {{ $isEdit
                        ? 'Enregistrer les modifications'
                        : 'Ajouter l’équipement' }}

                </button>

            </div>

        </div>

    </form>

</div>


{{-- ===================================================== --}}
{{-- JAVASCRIPT --}}
{{-- ===================================================== --}}

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const form = document.getElementById('equipment-form');

        if (!form) return;

        const name = document.getElementById('name');
        const category = document.getElementById('category');
        const description = document.getElementById('description');
        const price = document.getElementById('price');
        const quantity = document.getElementById('quantity_available');

        const descriptionCounter = document.getElementById('description-counter');

        const statusInput = document.getElementById('is_active');
        const statusToggle = document.getElementById('status-toggle');
        const statusThumb = document.getElementById('status-thumb');
        const statusLabel = document.getElementById('status-label');
        const statusDescription = document.getElementById('status-description');

        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('image-preview');
        const imageFilename = document.getElementById('image-filename');
        const imageError = document.getElementById('image-error');
        const uploadZone = document.getElementById('upload-zone');
        const uploadPlaceholder = document.getElementById('upload-placeholder');
        const resetImageButton = document.getElementById('reset-image');

        const catalogueImage = document.getElementById('catalogue-image');
        const cataloguePlaceholder = document.getElementById('catalogue-placeholder');
        const catalogueName = document.getElementById('catalogue-name');
        const catalogueCategory = document.getElementById('catalogue-category');
        const catalogueDescription = document.getElementById('catalogue-description');
        const cataloguePrice = document.getElementById('catalogue-price');
        const catalogueStock = document.getElementById('catalogue-stock');
        const catalogueStatus = document.getElementById('catalogue-status');

        const stockIndicator = document.getElementById('stock-indicator');

        const originalImage = @json($imageUrl);

        let objectUrl = null;


        /*
        |--------------------------------------------------------------------------
        | FORMAT PRICE
        |--------------------------------------------------------------------------
        */

        const priceFormatter = new Intl.NumberFormat('fr-MA', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });


        /*
        |--------------------------------------------------------------------------
        | LIVE PREVIEW
        |--------------------------------------------------------------------------
        */

        function updatePreview() {

            const currentName = name.value.trim();

            const currentCategory = category.value;

            const currentDescription = description.value.trim();

            const currentPrice = Math.max(
                0,
                Number(price.value) || 0
            );

            const currentQuantity = Math.max(
                0,
                parseInt(quantity.value, 10) || 0
            );

            const active = statusInput.value === '1';

            catalogueName.textContent =
                currentName || 'Nom de l’équipement';

            catalogueCategory.textContent =
                currentCategory || 'Catégorie';

            catalogueDescription.textContent =
                currentDescription || 'Description de l’équipement.';

            cataloguePrice.textContent =
                priceFormatter.format(currentPrice) + ' DH';

            descriptionCounter.textContent =
                description.value.length + ' / 5000';

            catalogueStock.textContent =
                currentQuantity + (
                    currentQuantity > 1
                        ? ' unités disponibles'
                        : ' unité disponible'
                );

            catalogueStock.className =
                'mt-2 text-xs font-semibold ' +
                (
                    currentQuantity > 0
                        ? 'text-emerald-600'
                        : 'text-red-600'
                );

            stockIndicator.textContent =
                currentQuantity > 0
                    ? 'En stock'
                    : 'Rupture de stock';

            stockIndicator.className =
                'rounded-full px-3 py-2 text-xs font-bold ' +
                (
                    currentQuantity > 0
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-red-100 text-red-700'
                );

            catalogueStatus.textContent =
                active ? 'Actif' : 'Inactif';

            catalogueStatus.className =
                'absolute right-3 top-3 rounded-full bg-white px-3 py-1.5 text-xs font-bold shadow-sm ' +
                (
                    active
                        ? 'text-emerald-600'
                        : 'text-red-600'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | ACTIVE / INACTIVE SWITCH
        |--------------------------------------------------------------------------
        */

        function updateStatus() {

            const active = statusInput.value === '1';

            statusToggle.setAttribute(
                'aria-checked',
                active ? 'true' : 'false'
            );

            statusToggle.classList.toggle(
                'bg-emerald-500',
                active
            );

            statusToggle.classList.toggle(
                'bg-slate-300',
                !active
            );

            statusThumb.classList.toggle(
                'translate-x-6',
                active
            );

            statusThumb.classList.toggle(
                'translate-x-1',
                !active
            );

            statusLabel.textContent =
                active
                    ? 'Équipement actif'
                    : 'Équipement inactif';

            statusDescription.textContent =
                active
                    ? 'Disponible dans le catalogue.'
                    : 'Non proposé dans le catalogue.';

            updatePreview();

        }

        statusToggle.addEventListener('click', () => {

            statusInput.value =
                statusInput.value === '1' ? '0' : '1';

            updateStatus();

        });


        /*
        |--------------------------------------------------------------------------
        | DISPLAY IMAGE
        |--------------------------------------------------------------------------
        */

        function displayImage(url) {

            if (url) {

                imagePreview.src = url;
                catalogueImage.src = url;

                imagePreview.classList.remove('hidden');
                catalogueImage.classList.remove('hidden');

                uploadPlaceholder.classList.add('hidden');
                cataloguePlaceholder.classList.add('hidden');

            } else {

                imagePreview.removeAttribute('src');
                catalogueImage.removeAttribute('src');

                imagePreview.classList.add('hidden');
                catalogueImage.classList.add('hidden');

                uploadPlaceholder.classList.remove('hidden');
                cataloguePlaceholder.classList.remove('hidden');

            }

        }


        /*
        |--------------------------------------------------------------------------
        | IMAGE VALIDATION
        |--------------------------------------------------------------------------
        */

        function validateImage(file) {

            const validTypes = [
                'image/jpeg',
                'image/png',
                'image/webp',
            ];

            if (!validTypes.includes(file.type)) {

                return 'Veuillez sélectionner une image JPG, PNG ou WebP.';

            }

            if (file.size > 2 * 1024 * 1024) {

                return 'La taille de l’image ne doit pas dépasser 2 Mo.';

            }

            return null;

        }


        /*
        |--------------------------------------------------------------------------
        | IMAGE ERROR
        |--------------------------------------------------------------------------
        */

        function showImageError(message) {

            imageError.textContent = message;

            imageError.classList.remove('hidden');

        }

        function clearImageError() {

            imageError.textContent = '';

            imageError.classList.add('hidden');

        }


        /*
        |--------------------------------------------------------------------------
        | RESET IMAGE
        |--------------------------------------------------------------------------
        */

        function resetImage() {

            if (objectUrl) {

                URL.revokeObjectURL(objectUrl);

                objectUrl = null;

            }

            imageInput.value = '';

            displayImage(originalImage);

            imageFilename.textContent =
                originalImage
                    ? 'Image actuelle'
                    : 'Aucun fichier sélectionné';

            resetImageButton.classList.add('hidden');

            clearImageError();

        }


        /*
        |--------------------------------------------------------------------------
        | HANDLE SELECTED IMAGE
        |--------------------------------------------------------------------------
        */

        function handleImage(file) {

            if (!file) return;

            clearImageError();

            const validationError = validateImage(file);

            if (validationError) {

                resetImage();

                showImageError(validationError);

                return;

            }

            if (objectUrl) {

                URL.revokeObjectURL(objectUrl);

            }

            objectUrl = URL.createObjectURL(file);

            displayImage(objectUrl);

            imageFilename.textContent = file.name;

            resetImageButton.classList.remove('hidden');

        }

        imageInput.addEventListener('change', () => {

            const file = imageInput.files[0];

            if (file) {

                handleImage(file);

            } else {

                resetImage();

            }

        });

        resetImageButton.addEventListener(
            'click',
            resetImage
        );


        /*
        |--------------------------------------------------------------------------
        | DRAG AND DROP
        |--------------------------------------------------------------------------
        */

        uploadZone.addEventListener('dragover', event => {

            event.preventDefault();

            uploadZone.classList.add(
                'border-blue-500',
                'bg-blue-50'
            );

        });

        uploadZone.addEventListener('dragleave', () => {

            uploadZone.classList.remove(
                'border-blue-500',
                'bg-blue-50'
            );

        });

        uploadZone.addEventListener('drop', event => {

            event.preventDefault();

            uploadZone.classList.remove(
                'border-blue-500',
                'bg-blue-50'
            );

            const file = event.dataTransfer.files[0];

            if (!file) return;

            clearImageError();

            const validationError = validateImage(file);

            if (validationError) {

                showImageError(validationError);

                return;

            }

            const transfer = new DataTransfer();

            transfer.items.add(file);

            imageInput.files = transfer.files;

            handleImage(file);

        });


        /*
        |--------------------------------------------------------------------------
        | BROKEN IMAGE FALLBACK
        |--------------------------------------------------------------------------
        */

        imagePreview.addEventListener('error', () => {

            imagePreview.classList.add('hidden');

            uploadPlaceholder.classList.remove('hidden');

        });

        catalogueImage.addEventListener('error', () => {

            catalogueImage.classList.add('hidden');

            cataloguePlaceholder.classList.remove('hidden');

        });

        if (
            originalImage &&
            imagePreview.complete &&
            imagePreview.naturalWidth === 0
        ) {

            imagePreview.classList.add('hidden');

            uploadPlaceholder.classList.remove('hidden');

        }

        if (
            originalImage &&
            catalogueImage.complete &&
            catalogueImage.naturalWidth === 0
        ) {

            catalogueImage.classList.add('hidden');

            cataloguePlaceholder.classList.remove('hidden');

        }


        /*
        |--------------------------------------------------------------------------
        | FIELD LISTENERS
        |--------------------------------------------------------------------------
        */

        [
            name,
            category,
            description,
            price,
            quantity,
        ].forEach(field => {

            field.addEventListener(
                'input',
                updatePreview
            );

            field.addEventListener(
                'change',
                updatePreview
            );

        });


        /*
        |--------------------------------------------------------------------------
        | FORM SUBMISSION
        |--------------------------------------------------------------------------
        */

        form.addEventListener('submit', event => {

            const file = imageInput.files[0];

            if (file) {

                const validationError = validateImage(file);

                if (validationError) {

                    event.preventDefault();

                    showImageError(validationError);

                    uploadZone.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                    });

                }

            }

        });


        /*
        |--------------------------------------------------------------------------
        | INITIALIZATION
        |--------------------------------------------------------------------------
        */

        updateStatus();

        window.addEventListener('pagehide', event => {

            if (!event.persisted && objectUrl) {

                URL.revokeObjectURL(objectUrl);

            }

        });

    });
</script>