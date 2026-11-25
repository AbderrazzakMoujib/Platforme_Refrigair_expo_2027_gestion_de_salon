<x-commercial.layout
    :commercial="$commercial"
    :commercial-initials="$commercialInitials"
>

    @php
        $isEdit = (bool) $formMethod;

        $cancelUrl = $isEdit
            ? route('commercial.exhibitors.show', $exhibitor)
            : route('commercial.exhibitors.index');

        $inputClass = 'h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-800 outline-none focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100';

        $companyFields = [
            ['company_name', 'Nom de l’entreprise', 'text', 'Ex. Atlas Froid', 255],
            ['contact_name', 'Nom du responsable', 'text', 'Nom et prénom', 255],
            ['email', 'Adresse email', 'email', 'contact@entreprise.ma', 255],
            ['phone', 'Téléphone', 'tel', '+212 600 000 000', 40],
        ];
    @endphp

    <div class="min-w-0 space-y-6">

        {{-- BREADCRUMB --}}

        <nav class="flex flex-wrap gap-2 text-xs font-medium text-slate-500">

            <a href="{{ route('commercial.index') }}"
               class="hover:text-blue-600">
                Vue globale
            </a>

            <span>/</span>

            <a href="{{ route('commercial.exhibitors.index') }}"
               class="hover:text-blue-600">
                Mes exposants
            </a>

            <span>/</span>

            <span class="text-blue-600">
                {{ $isEdit ? 'Modifier' : 'Ajouter' }}
            </span>

        </nav>

        {{-- HEADER --}}

        <section class="flex flex-wrap items-center justify-between gap-4">

            <div>

                <h1 class="text-3xl font-extrabold text-[#0F2347]">
                    {{ $pageTitle }}
                </h1>

                <p class="mt-2 text-sm text-slate-500">

                    {{ $isEdit
                        ? 'Modifiez les informations de votre exposant.'
                        : 'Enregistrez une nouvelle entreprise exposante.' }}

                </p>

            </div>

            <a href="{{ route('commercial.exhibitors.index') }}"
               class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">

                ← Retour à mes exposants

            </a>

        </section>

        @if ($errors->any())

            <div role="alert"
                 class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">

                <p class="font-bold">
                    Veuillez vérifier les informations saisies.
                </p>

                <ul class="mt-2 list-disc space-y-1 pl-5">

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        @endif

        {{-- MAIN FORM --}}

        <form method="POST"
              action="{{ $formAction }}"
              enctype="multipart/form-data"
              id="exhibitor-form"
              class="grid min-w-0 items-start gap-6 xl:grid-cols-3">

            @csrf

            @if ($formMethod)
                @method($formMethod)
            @endif

            {{-- LEFT COLUMN --}}

            <div class="min-w-0 space-y-6 xl:col-span-2">

                {{-- LOGO UPLOAD --}}

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex items-center gap-3 border-b border-slate-100 p-5">

                        <span class="grid size-11 place-items-center rounded-xl bg-blue-50 text-blue-600">

                            <x-admin.icon name="storefront" class="size-6"/>

                        </span>

                        <div>

                            <h2 class="font-bold text-[#0F2347]">
                                Logo de l'entreprise
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Personnalisez l'identité visuelle de l'exposant.
                            </p>

                        </div>

                    </div>

                    <div class="flex flex-col items-center gap-6 p-6 sm:flex-row">

                        {{-- IMAGE PREVIEW --}}

                        <div class="relative grid size-32 shrink-0 place-items-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">

                            <img
                                id="logo-preview"
                                @if ($exhibitor->logo_url)
                                    src="{{ $exhibitor->logo_url }}"
                                @endif
                                alt="Aperçu du logo de l'entreprise"
                                class="h-full w-full object-contain p-3 {{ $exhibitor->logo_url ? '' : 'hidden' }}"
                            >

                            <div id="logo-placeholder"
                                 class="flex flex-col items-center justify-center gap-2 text-slate-400 {{ $exhibitor->logo_url ? 'hidden' : '' }}">

                                <svg class="size-10"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.5"
                                     aria-hidden="true">

                                    <rect x="3" y="3" width="18" height="18" rx="3"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <path d="m21 15-5-5L5 21"/>

                                </svg>

                                <span class="text-xs">
                                    Aucun logo
                                </span>

                            </div>

                        </div>

                        {{-- UPLOAD INPUT --}}

                        <div class="min-w-0 flex-1">

                            <h3 class="text-sm font-bold text-[#0F2347]">
                                {{ $exhibitor->logo_url
                                    ? 'Modifier le logo'
                                    : 'Ajouter un logo' }}
                            </h3>

                            <p class="mt-2 text-xs leading-relaxed text-slate-500">
                                Formats acceptés : JPG, JPEG, PNG et WebP.
                                Taille maximale : 2 Mo.
                            </p>

                            <div class="mt-4 flex flex-wrap gap-3">

                                <label for="logo"
                                       class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700 focus-within:ring-2 focus-within:ring-blue-400">

                                    <svg class="size-5"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8"
                                         stroke-linecap="round"
                                         stroke-linejoin="round"
                                         aria-hidden="true">

                                        <path d="M12 16V4m-5 5 5-5 5 5"/>
                                        <path d="M4 16v4h16v-4"/>

                                    </svg>

                                    Choisir une image

                                    <input
                                        id="logo"
                                        name="logo"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="sr-only"
                                    >

                                </label>

                                <button type="button"
                                        id="cancel-logo-selection"
                                        class="hidden rounded-xl border border-slate-200 px-4 py-3 text-xs font-semibold text-slate-600 hover:bg-slate-50">

                                    Annuler la sélection

                                </button>

                            </div>

                            <p id="logo-filename"
                               class="mt-3 break-all text-xs text-slate-500"
                               aria-live="polite">
                            </p>

                            <p id="logo-error"
                               class="mt-2 hidden text-xs font-medium text-red-600"
                               role="alert">
                            </p>

                            @error('logo')

                                <p class="mt-2 text-xs text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                </section>

                {{-- COMPANY INFORMATION --}}

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex items-center gap-3 border-b border-slate-100 p-5">

                        <span class="grid size-11 place-items-center rounded-xl bg-blue-50 text-blue-600">

                            <x-admin.icon name="users" class="size-6"/>

                        </span>

                        <div>

                            <h2 class="font-bold text-[#0F2347]">
                                Informations de l'entreprise
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Identité et coordonnées de l'exposant.
                            </p>

                        </div>

                    </div>

                    <div class="grid gap-5 p-6 sm:grid-cols-2">

                        @foreach ($companyFields as [$field, $label, $type, $placeholder, $maxLength])

                            <div class="min-w-0">

                                <label for="{{ $field }}"
                                       class="mb-2 block text-sm font-semibold text-slate-700">

                                    {{ $label }}

                                    <span class="text-red-500">*</span>

                                </label>

                                <input id="{{ $field }}"
                                       name="{{ $field }}"
                                       type="{{ $type }}"
                                       value="{{ old($field, $exhibitor->{$field}) }}"
                                       placeholder="{{ $placeholder }}"
                                       maxlength="{{ $maxLength }}"
                                       required
                                       class="{{ $inputClass }}">

                                @error($field)

                                    <p class="mt-2 text-xs text-red-600">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                        @endforeach

                    </div>

                </section>

                {{-- STAND INFORMATION --}}

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex items-center gap-3 border-b border-slate-100 p-5">

                        <span class="grid size-11 place-items-center rounded-xl bg-violet-50 text-violet-600">

                            <x-admin.icon name="layers" class="size-6"/>

                        </span>

                        <div>

                            <h2 class="font-bold text-[#0F2347]">
                                Informations du stand
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Emplacement, superficie et quota de badges.
                            </p>

                        </div>

                    </div>

                    <div class="space-y-5 p-6">

                        <div class="grid gap-5 sm:grid-cols-2">

                            <div>

                                <label for="stand_number"
                                       class="mb-2 block text-sm font-semibold text-slate-700">

                                    Numéro du stand

                                    <span class="text-red-500">*</span>

                                </label>

                                <input id="stand_number"
                                       name="stand_number"
                                       value="{{ old('stand_number', $exhibitor->stand_number) }}"
                                       placeholder="Ex. A12"
                                       maxlength="50"
                                       required
                                       class="{{ $inputClass }}">

                                @error('stand_number')

                                    <p class="mt-2 text-xs text-red-600">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                            <div>

                                <label for="area_sqm"
                                       class="mb-2 block text-sm font-semibold text-slate-700">

                                    Surface en m²

                                    <span class="text-red-500">*</span>

                                </label>

                                <input id="area_sqm"
                                       name="area_sqm"
                                       type="number"
                                       min="0.01"
                                       max="999999.99"
                                       step="0.01"
                                       value="{{ old('area_sqm', $exhibitor->area_sqm) }}"
                                       placeholder="Ex. 36"
                                       required
                                       class="{{ $inputClass }}">

                                @error('area_sqm')

                                    <p class="mt-2 text-xs text-red-600">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                        </div>

                        <div>

                            <label for="authorized_badges"
                                   class="mb-2 block text-sm font-semibold text-slate-700">

                                Nombre de badges autorisés

                                <span class="text-red-500">*</span>

                            </label>

                            <input id="authorized_badges"
                                   name="authorized_badges"
                                   type="number"
                                   min="{{ $isEdit ? ($exhibitor->badges_count ?? 0) : 0 }}"
                                   max="100000"
                                   step="1"
                                   value="{{ old('authorized_badges', $exhibitor->authorized_badges) }}"
                                   placeholder="Ex. 10"
                                   required
                                   class="{{ $inputClass }}">

                            @error('authorized_badges')

                                <p class="mt-2 text-xs text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                        <div class="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50 p-4">

                            <x-admin.icon name="badge" class="size-5 shrink-0 text-blue-600"/>

                            <div>

                                <p class="text-xs font-bold text-blue-800">
                                    Gestion du quota
                                </p>

                                <p class="mt-1 text-xs leading-relaxed text-blue-700">

                                    @if ($isEdit)

                                        Le quota ne peut pas être inférieur au nombre de badges déjà enregistrés.

                                        Badges enregistrés : {{ $exhibitor->badges_count ?? 0 }}.

                                    @else

                                        Ce quota sera disponible dans l'espace personnel de l'exposant.

                                    @endif

                                </p>

                            </div>

                        </div>

                    </div>

                </section>

                {{-- ACCOUNT CREATION --}}

                @if (false && ! $isEdit)

                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                        <div class="flex items-center gap-3 border-b border-slate-100 p-5">

                            <span class="grid size-11 place-items-center rounded-xl bg-emerald-50 text-emerald-600">

                                <x-admin.icon name="user-plus" class="size-6"/>

                            </span>

                            <div>

                                <h2 class="font-bold text-[#0F2347]">
                                    Configuration du compte
                                </h2>

                                <p class="mt-1 text-xs text-slate-500">
                                    Identifiants de connexion de l'exposant.
                                </p>

                            </div>

                        </div>

                        <div class="grid gap-5 p-6 sm:grid-cols-2">

                            @foreach ([
                                ['password', 'Mot de passe initial'],
                                ['password_confirmation', 'Confirmer le mot de passe'],
                            ] as [$field, $label])

                                <div>

                                    <label for="{{ $field }}"
                                           class="mb-2 block text-sm font-semibold text-slate-700">

                                        {{ $label }}

                                        <span class="text-red-500">*</span>

                                    </label>

                                    <div class="relative">

                                        <input id="{{ $field }}"
                                               name="{{ $field }}"
                                               type="password"
                                               required
                                               minlength="8"
                                               autocomplete="new-password"
                                               placeholder="Minimum 8 caractères"
                                               class="{{ $inputClass }} pr-12">

                                        <button type="button"
                                                data-password-toggle="{{ $field }}"
                                                aria-label="Afficher le mot de passe"
                                                aria-pressed="false"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-blue-600">

                                            <svg class="size-5"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="1.8"
                                                 aria-hidden="true">

                                                <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z"/>
                                                <circle cx="12" cy="12" r="3"/>

                                            </svg>

                                        </button>

                                    </div>

                                    @error($field)

                                        <p class="mt-2 text-xs text-red-600">
                                            {{ $message }}
                                        </p>

                                    @enderror

                                </div>

                            @endforeach

                        </div>

                    </section>

                @endif

            </div>

            {{-- RIGHT COLUMN --}}

            @if (! $isEdit)
                <p class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm leading-6 text-blue-900">Après la création du dossier, l’exposant recevra une invitation par courriel et choisira lui-même son mot de passe.</p>
            @endif

            <aside class="min-w-0 space-y-6">

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex items-center gap-3 border-b border-slate-100 p-5">

                        <x-admin.icon name="clipboard-check" class="size-6 text-blue-600"/>

                        <h2 class="font-bold text-[#0F2347]">
                            Récapitulatif
                        </h2>

                    </div>

                    <div class="space-y-4 p-5">

                        {{-- SUMMARY LOGO --}}

                        <div class="flex flex-col items-center border-b border-slate-100 pb-5">

                            <div class="grid size-24 place-items-center overflow-hidden rounded-2xl border border-slate-200 bg-white">

                                <img id="summary-logo"
                                     @if ($exhibitor->logo_url)
                                         src="{{ $exhibitor->logo_url }}"
                                     @endif
                                     alt="Logo de l'exposant"
                                     class="h-full w-full object-contain p-2 {{ $exhibitor->logo_url ? '' : 'hidden' }}">

                                <span id="summary-initials"
                                      class="text-2xl font-extrabold text-blue-600 {{ $exhibitor->logo_url ? 'hidden' : '' }}">

                                    {{ $exhibitor->initials }}

                                </span>

                            </div>

                            <p id="summary-company"
                               class="mt-3 break-all text-center font-bold text-[#0F2347]">

                                {{ old('company_name', $exhibitor->company_name) ?: 'Nouvel exposant' }}

                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                Entreprise exposante
                            </p>

                        </div>

                        <div class="rounded-xl bg-blue-50 p-4">

                            <p class="text-xs font-semibold text-blue-700">
                                Numéro du stand
                            </p>

                            <p id="summary-stand"
                               class="mt-2 break-words text-xl font-bold text-blue-800">

                                {{ old('stand_number', $exhibitor->stand_number) ?: '—' }}

                            </p>

                        </div>

                        <div class="rounded-xl bg-violet-50 p-4">

                            <p class="text-xs font-semibold text-violet-700">
                                Surface
                            </p>

                            <p class="mt-2 text-xl font-bold text-violet-800">

                                <span id="summary-area">{{ old('area_sqm', $exhibitor->area_sqm) ?? 0 }}</span>

                                <span class="text-sm">m²</span>

                            </p>

                        </div>

                        <div class="rounded-xl bg-emerald-50 p-4">

                            <p class="text-xs font-semibold text-emerald-700">
                                Badges autorisés
                            </p>

                            <p id="summary-badges"
                               class="mt-2 text-xl font-bold text-emerald-800">{{ old('authorized_badges', $exhibitor->authorized_badges) ?? 0 }}</p>

                        </div>

                    </div>

                </section>

                <section class="rounded-2xl border border-blue-100 bg-blue-50 p-5">

                    <h2 class="text-sm font-bold text-blue-900">
                        Informations importantes
                    </h2>

                    <ul class="mt-3 list-disc space-y-3 pl-4 text-xs leading-relaxed text-blue-800">

                        <li>Les champs marqués d'un astérisque sont obligatoires.</li>

                        <li>Utilisez un logo de bonne qualité, de préférence sur fond transparent.</li>

                        <li>Vérifiez les informations du stand et le quota de badges.</li>

                        @if ($isEdit)
                            <li>Le logo actuel est conservé si aucune nouvelle image n'est sélectionnée.</li>
                        @else
                            <li>Un compte exposant sera créé après validation.</li>
                        @endif

                    </ul>

                </section>

            </aside>

            {{-- FORM ACTIONS --}}

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-3">

                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

                    <div>

                        <p class="text-sm font-bold text-[#0F2347]">
                            {{ $isEdit ? 'Enregistrer les modifications' : 'Finaliser la création' }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Vérifiez les informations avant de continuer.
                        </p>

                    </div>

                    <div class="flex flex-wrap gap-3">

                        <a href="{{ $cancelUrl }}"
                           class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Annuler
                        </a>

                        <button id="save-exhibitor"
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60">

                            <x-admin.icon name="clipboard-check" class="size-5"/>

                            <span id="save-button-label">

                                {{ $isEdit
                                    ? 'Enregistrer les modifications'
                                    : 'Créer l’exposant' }}

                            </span>

                        </button>

                    </div>

                </div>

            </section>

        </form>

        <footer class="border-t border-slate-200 py-4 text-center text-xs text-slate-400">
            © 2027 Refrigair Expo — Espace Commercial
        </footer>

    </div>

    {{-- JAVASCRIPT --}}

    <script>
        (() => {

            const form = document.getElementById('exhibitor-form');

            const logoInput = document.getElementById('logo');
            const preview = document.getElementById('logo-preview');
            const placeholder = document.getElementById('logo-placeholder');

            const summaryLogo = document.getElementById('summary-logo');
            const summaryInitials = document.getElementById('summary-initials');

            const filename = document.getElementById('logo-filename');
            const error = document.getElementById('logo-error');

            const cancelLogo = document.getElementById(
                'cancel-logo-selection'
            );

            const originalLogo = @json($exhibitor->logo_url);

            let objectUrl = null;

            // Affichage du logo.

            function displayLogo(url) {

                [preview, summaryLogo].forEach(image => {

                    if (url) {
                        image.src = url;
                        image.classList.remove('hidden');
                    } else {
                        image.removeAttribute('src');
                        image.classList.add('hidden');
                    }

                });

                placeholder.classList.toggle('hidden', Boolean(url));

                summaryInitials.classList.toggle(
                    'hidden',
                    Boolean(url)
                );

            }

            // Annuler la nouvelle sélection.

            function resetLogo() {

                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                }

                logoInput.value = '';

                displayLogo(originalLogo);

                filename.textContent = '';

                error.textContent = '';

                error.classList.add('hidden');

                cancelLogo.classList.add('hidden');

            }

            // Validation de l'image.

            logoInput.addEventListener('change', () => {

                const file = logoInput.files[0];

                error.classList.add('hidden');

                if (!file) {
                    resetLogo();
                    return;
                }

                const allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                if (!allowedTypes.includes(file.type)) {

                    resetLogo();

                    error.textContent =
                        'Formats acceptés : JPG, PNG et WebP.';

                    error.classList.remove('hidden');

                    return;

                }

                if (file.size > 2 * 1024 * 1024) {

                    resetLogo();

                    error.textContent =
                        'La taille du logo ne doit pas dépasser 2 Mo.';

                    error.classList.remove('hidden');

                    return;

                }

                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                }

                objectUrl = URL.createObjectURL(file);

                displayLogo(objectUrl);

                filename.textContent = file.name;

                cancelLogo.classList.remove('hidden');

            });

            cancelLogo.addEventListener('click', resetLogo);

            // Récapitulatif en temps réel.

            const fields = [
                ['company_name', 'summary-company', 'Nouvel exposant'],
                ['stand_number', 'summary-stand', '—'],
                ['area_sqm', 'summary-area', '0'],
                ['authorized_badges', 'summary-badges', '0']
            ];

            fields.forEach(([inputId, summaryId, fallback]) => {

                const input = document.getElementById(inputId);

                const summary = document.getElementById(summaryId);

                const update = () => {

                    summary.textContent =
                        input.value.trim() || fallback;

                    if (inputId === 'company_name') {

                        summaryInitials.textContent =
                            input.value.trim().slice(0, 2).toUpperCase()
                            || 'EX';

                    }

                };

                input.addEventListener('input', update);

                update();

            });

            // Afficher / masquer les mots de passe.

            document.querySelectorAll('[data-password-toggle]')
                .forEach(button => {

                    button.addEventListener('click', () => {

                        const input = document.getElementById(
                            button.dataset.passwordToggle
                        );

                        const visible = input.type === 'password';

                        input.type = visible ? 'text' : 'password';

                        button.setAttribute(
                            'aria-pressed',
                            String(visible)
                        );

                        button.setAttribute(
                            'aria-label',
                            visible
                                ? 'Masquer le mot de passe'
                                : 'Afficher le mot de passe'
                        );

                    });

                });

            // Confirmation du mot de passe.

            const password = document.getElementById('password');

            const confirmation = document.getElementById(
                'password_confirmation'
            );

            if (password && confirmation) {

                const validate = () => {

                    confirmation.setCustomValidity(
                        confirmation.value !== password.value
                            ? 'Les mots de passe ne correspondent pas.'
                            : ''
                    );

                };

                password.addEventListener('input', validate);

                confirmation.addEventListener('input', validate);

            }

            // Empêcher les doubles soumissions.

            const saveButton = document.getElementById('save-exhibitor');

            const saveLabel = document.getElementById('save-button-label');

            const originalLabel = saveLabel.textContent.trim();

            form.addEventListener('submit', event => {

                if (saveButton.disabled) {

                    event.preventDefault();

                    return;

                }

                if (event.defaultPrevented) return;

                saveButton.disabled = true;

                saveLabel.textContent = 'Enregistrement en cours...';

            });

            window.addEventListener('pageshow', () => {

                saveButton.disabled = false;

                saveLabel.textContent = originalLabel;

            });

        })();
    </script>

</x-commercial.layout>
