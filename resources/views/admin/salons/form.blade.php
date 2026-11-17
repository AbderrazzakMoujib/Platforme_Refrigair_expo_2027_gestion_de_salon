
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

@php
    $editing = $salon->exists;

    $imageUrl = $salon->cover_image
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($salon->cover_image)
        : null;

    $field = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-50';

    $label = 'mb-2 block text-sm font-semibold text-[#0F2347]';
@endphp

<div class="space-y-6">

    <!-- BREADCRUMB -->
    <nav class="flex flex-wrap items-center gap-2 text-sm text-slate-500"
         aria-label="Fil d'Ariane">

        <a href="{{ route('admin.index') }}"
           class="hover:text-blue-600">
            Administration
        </a>

        <span>/</span>

        <a href="{{ route('admin.salons.index') }}"
           class="hover:text-blue-600">
            Salons
        </a>

        <span>/</span>

        <span class="font-semibold text-[#0F2347]">
            {{ $editing ? 'Modifier un salon' : 'Ajouter un salon' }}
        </span>
    </nav>

    <!-- PAGE HEADER -->
    <header class="flex items-center gap-4">

        <div class="grid size-14 shrink-0 place-items-center rounded-2xl bg-[#2563EB] text-white shadow-sm">
            <x-admin.icon name="calendar" class="size-7" />
        </div>

        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                {{ $editing ? 'Modifier un salon' : 'Ajouter un salon' }}
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                {{ $editing
                    ? 'Modifiez les informations et les paramètres de ce salon.'
                    : 'Créez un nouveau salon et définissez ses informations, dates et paramètres.' }}
            </p>
        </div>

    </header>

    <!-- VALIDATION ERRORS -->
    @if ($errors->any())
        <div role="alert"
             class="rounded-2xl border border-red-200 bg-red-50 p-5">

            <p class="font-bold text-red-800">
                Veuillez corriger les erreurs suivantes :
            </p>

            <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- MAIN FORM -->
    <form id="salon-form"
          method="POST"
          action="{{ $formAction }}"
          enctype="multipart/form-data"
          class="space-y-6">

        @csrf

        @if ($formMethod)
            @method($formMethod)
        @endif

        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.5fr)_minmax(320px,1fr)]">

            <!-- LEFT COLUMN -->
            <div class="space-y-5">

                <!-- INFORMATIONS GENERALES -->
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-5">

                        <div class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-600">
                            <svg class="size-6"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 aria-hidden="true">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round"
                                      d="M12 11v5M12 7h.01"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="font-bold text-[#0F2347]">
                                Informations générales
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Définissez les informations principales du salon.
                            </p>
                        </div>

                    </div>

                    <div class="space-y-5 p-6">

                        <div class="grid gap-5 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">

                            <!-- NAME -->
                            <div>
                                <label for="name" class="{{ $label }}">
                                    Nom du salon
                                    <span class="text-red-500">*</span>
                                </label>

                                <input id="name"
                                       name="name"
                                       type="text"
                                       required
                                       maxlength="255"
                                       autocomplete="off"
                                       value="{{ old('name', $salon->name) }}"
                                       placeholder="Ex : Refrigair Expo"
                                       class="{{ $field }}">

                                @error('name')
                                    <p class="mt-1 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- EDITION -->
                            <div>
                                <label for="edition" class="{{ $label }}">
                                    Édition
                                    <span class="text-red-500">*</span>
                                </label>

                                <input id="edition"
                                       name="edition"
                                       type="text"
                                       required
                                       maxlength="100"
                                       value="{{ old('edition', $salon->edition) }}"
                                       placeholder="Ex : 2027"
                                       class="{{ $field }}">

                                @error('edition')
                                    <p class="mt-1 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>

                        <!-- DESCRIPTION -->
                        <div>
                            <label for="description" class="{{ $label }}">
                                Description
                            </label>

                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-50">

                                <!-- EDITOR TOOLBAR -->
                                <div class="flex flex-wrap items-center gap-1 border-b border-slate-200 bg-slate-50 px-3 py-2"
                                     aria-label="Outils de mise en forme">

                                    <button type="button"
                                            data-format="bold"
                                            title="Gras"
                                            class="editor-tool">
                                        <strong>B</strong>
                                    </button>

                                    <button type="button"
                                            data-format="italic"
                                            title="Italique"
                                            class="editor-tool italic">
                                        I
                                    </button>

                                    <button type="button"
                                            data-format="underline"
                                            title="Souligné"
                                            class="editor-tool underline">
                                        U
                                    </button>

                                    <span class="mx-1 h-6 border-l border-slate-200"></span>

                                    <button type="button"
                                            data-format="bullet"
                                            title="Liste à puces"
                                            class="editor-tool">
                                        ☷
                                    </button>

                                    <button type="button"
                                            data-format="number"
                                            title="Liste numérotée"
                                            class="editor-tool">
                                        1.
                                    </button>

                                    <button type="button"
                                            data-format="link"
                                            title="Insérer un lien"
                                            class="editor-tool text-blue-600">
                                        🔗
                                    </button>

                                </div>

                                <textarea id="description"
                                          name="description"
                                          rows="7"
                                          maxlength="5000"
                                          placeholder="Décrivez brièvement le salon, ses objectifs, ses secteurs et son public cible..."
                                          class="block w-full resize-y border-0 bg-white px-4 py-4 text-sm leading-7 text-slate-700 outline-none placeholder:text-slate-400 focus:ring-0">{{ old('description', $salon->description) }}</textarea>

                                <div class="flex justify-end border-t border-slate-100 px-4 py-2">
                                    <span id="description-counter"
                                          class="text-xs text-slate-400">
                                        0 / 5000
                                    </span>
                                </div>

                            </div>

                            <p class="mt-2 text-xs text-slate-400">
                                Mise en forme Markdown : gras, italique, listes et liens.
                            </p>

                            @error('description')
                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>

                </section>

                <!-- LIEU ET DATES -->
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-5">

                        <div class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-600">
                            <svg class="size-6"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 aria-hidden="true">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/>
                                <circle cx="12" cy="10" r="2.5"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="font-bold text-[#0F2347]">
                                Lieu et dates
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Renseignez le lieu et la période du salon.
                            </p>
                        </div>

                    </div>

                    <div class="grid gap-5 p-6 sm:grid-cols-2">

                        <!-- CITY -->
                        <div>
                            <label for="city" class="{{ $label }}">
                                Ville
                                <span class="text-red-500">*</span>
                            </label>

                            <input id="city"
                                   name="city"
                                   type="text"
                                   required
                                   maxlength="150"
                                   value="{{ old('city', $salon->city) }}"
                                   placeholder="Ex : Casablanca"
                                   class="{{ $field }}">

                            @error('city')
                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- VENUE -->
                        <div>
                            <label for="venue" class="{{ $label }}">
                                Lieu / Palais
                                <span class="text-red-500">*</span>
                            </label>

                            <input id="venue"
                                   name="venue"
                                   type="text"
                                   required
                                   maxlength="255"
                                   value="{{ old('venue', $salon->venue) }}"
                                   placeholder="Ex : OFEC Casablanca"
                                   class="{{ $field }}">

                            @error('venue')
                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- START DATE -->
                        <div>
                            <label for="start_date" class="{{ $label }}">
                                Date de début
                                <span class="text-red-500">*</span>
                            </label>

                            <input id="start_date"
                                   name="start_date"
                                   type="date"
                                   required
                                   value="{{ old('start_date', $salon->start_date?->format('Y-m-d')) }}"
                                   class="{{ $field }}">

                            @error('start_date')
                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- END DATE -->
                        <div>
                            <label for="end_date" class="{{ $label }}">
                                Date de fin
                                <span class="text-red-500">*</span>
                            </label>

                            <input id="end_date"
                                   name="end_date"
                                   type="date"
                                   required
                                   value="{{ old('end_date', $salon->end_date?->format('Y-m-d')) }}"
                                   class="{{ $field }}">

                            @error('end_date')
                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>

                </section>

            </div>

            <!-- RIGHT COLUMN -->
            <div class="space-y-5">

                <!-- IMAGE DU SALON -->
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-5">

                        <div class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-600">
                            <svg class="size-6"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 aria-hidden="true">
                                <rect x="3" y="3" width="18" height="18" rx="3"/>
                                <circle cx="8" cy="8" r="2"/>
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="m4 18 5-5 3 3 3-4 5 6"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="font-bold text-[#0F2347]">
                                Image du salon
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Ajoutez et recadrez votre image de couverture.
                            </p>
                        </div>

                    </div>

                    <div class="space-y-4 p-6">

                        <!-- UPLOAD ZONE -->
                        <label for="cover_image"
                               id="upload-zone"
                               class="flex min-h-48 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-blue-300 bg-blue-50/50 px-5 py-8 text-center transition hover:border-blue-500 hover:bg-blue-50">

                            <svg class="size-11 text-blue-600"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.5"
                                 aria-hidden="true">
                                <rect x="3" y="3" width="18" height="18" rx="3"/>
                                <circle cx="8" cy="8" r="2"/>
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="m4 18 5-5 3 3 3-4 5 6"/>
                            </svg>

                            <span class="mt-3 text-sm font-bold text-[#0F2347]">
                                Déposer une image ici
                            </span>

                            <span class="mt-1 text-sm font-semibold text-blue-600">
                                ou cliquer pour parcourir
                            </span>

                            <span class="mt-3 text-xs text-slate-500">
                                JPG, PNG, WebP — Max : 5 Mo
                            </span>

                            <span class="mt-2 rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                Cadrage libre disponible
                            </span>

                        </label>

                        <!-- FILE INPUT -->
                        <input id="cover_image"
                               name="cover_image"
                               type="file"
                               accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                               class="sr-only">

                        <input id="remove_cover_image"
                               name="remove_cover_image"
                               type="hidden"
                               value="0">

                        <!-- PREVIEW -->
                        <div id="image-preview-wrapper"
                             class="{{ $imageUrl ? '' : 'hidden' }}">

                            <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50">

                                <img id="image-preview"
                                     src="{{ $imageUrl ?? '' }}"
                                     alt="Aperçu de la couverture du salon"
                                     class="h-auto max-h-80 w-full object-contain">

                                <!-- REMOVE IMAGE -->
                                <button id="remove-image"
                                        type="button"
                                        aria-label="Supprimer l'image"
                                        title="Supprimer l'image"
                                        class="absolute right-3 top-3 grid size-9 place-items-center rounded-lg bg-white text-red-600 shadow-md transition hover:bg-red-50">

                                    <svg class="size-5"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8"
                                         aria-hidden="true">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              d="M4 7h16M10 11v6m4-6v6M6 7l1 14h10l1-14M9 7V4h6v3"/>
                                    </svg>

                                </button>

                            </div>

                            <p id="image-file-name"
                               class="mt-2 truncate text-xs text-slate-500">
                                {{ $salon->cover_image ? basename($salon->cover_image) : '' }}
                            </p>

                            <p class="mt-1 text-xs text-emerald-600">
                                Image prête pour l'enregistrement.
                            </p>

                        </div>

                        @error('cover_image')
                            <p class="text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="text-xs leading-5 text-slate-400">
                            Après avoir choisi une image, une fenêtre s'ouvrira
                            pour sélectionner la zone à conserver.
                        </p>

                    </div>

                </section>

                <!-- STATUS -->
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                    <div class="flex items-center gap-3 border-b border-slate-100 px-6 py-5">

                        <div class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-600">
                            <x-admin.icon name="settings" class="size-6" />
                        </div>

                        <div>
                            <h2 class="font-bold text-[#0F2347]">
                                Statut et inscriptions
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Configurez le salon et les inscriptions.
                            </p>
                        </div>

                    </div>

                    <div class="grid gap-5 p-6 sm:grid-cols-2">

                        <!-- STATUS SELECT -->
                        <div>
                            <label for="status" class="{{ $label }}">
                                Statut du salon
                                <span class="text-red-500">*</span>
                            </label>

                            <select id="status"
                                    name="status"
                                    required
                                    class="{{ $field }}">

                                <option value="planned"
                                    @selected(old('status', $salon->status) === 'planned')>
                                    Planifié
                                </option>

                                <option value="active"
                                    @selected(old('status', $salon->status) === 'active')>
                                    En cours
                                </option>

                                <option value="completed"
                                    @selected(old('status', $salon->status) === 'completed')>
                                    Terminé
                                </option>

                                <option value="cancelled"
                                    @selected(old('status', $salon->status) === 'cancelled')>
                                    Annulé
                                </option>

                            </select>

                            @error('status')
                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- REGISTRATIONS -->
                        <div>
                            <label for="registration-switch" class="{{ $label }}">
                                Inscriptions exposants
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="flex min-h-12 items-center gap-3 rounded-xl bg-slate-50 px-3">

                                <input type="hidden"
                                       name="registration_open"
                                       value="0">

                                <input id="registration-switch"
                                       name="registration_open"
                                       type="checkbox"
                                       value="1"
                                       @checked((string) old('registration_open', (int) $salon->registration_open) === '1')
                                       class="peer sr-only">

                                <label for="registration-switch"
                                       class="relative h-6 w-11 shrink-0 cursor-pointer rounded-full bg-slate-300 transition after:absolute after:left-0.5 after:top-0.5 after:size-5 after:rounded-full after:bg-white after:shadow-sm after:transition-all peer-checked:bg-blue-600 peer-checked:after:translate-x-5 peer-focus-visible:ring-4 peer-focus-visible:ring-blue-200 peer-disabled:cursor-not-allowed peer-disabled:opacity-50">
                                </label>

                                <span id="registration-label"
                                      class="text-sm font-semibold text-slate-700">
                                    Fermées
                                </span>

                            </div>

                            @error('registration_open')
                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <p class="text-xs leading-5 text-slate-500 sm:col-span-2">
                            Les inscriptions sont désactivées pour les salons terminés ou annulés.
                        </p>

                    </div>

                </section>

            </div>

        </div>

        <!-- FORM ACTIONS -->
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">

            <a href="{{ route('admin.salons.index') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                <span aria-hidden="true">←</span>
                Retour à la liste
            </a>

            <div class="flex flex-col gap-3 sm:flex-row">

                <button type="reset"
                        class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Réinitialiser
                </button>

                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">

                    <svg class="size-5"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         aria-hidden="true">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M5 3h12l4 4v14H3V3h2Zm2 0v7h10V3M7 21v-8h10v8"/>
                    </svg>

                    {{ $editing
                        ? 'Enregistrer les modifications'
                        : 'Enregistrer le salon' }}

                </button>

            </div>

        </div>

    </form>

</div>

<!-- ================================================= -->
<!-- IMAGE CROPPER MODAL                               -->
<!-- ================================================= -->

<div id="crop-modal"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/80 p-3 sm:p-6"
     role="dialog"
     aria-modal="true"
     aria-labelledby="crop-title">

    <div class="flex max-h-[95vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">

        <!-- MODAL HEADER -->
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">

            <div>
                <h2 id="crop-title"
                    class="text-lg font-bold text-[#0F2347]">
                    Cadrage de l'image
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Déplacez et redimensionnez le cadre pour choisir
                    la zone à conserver.
                </p>
            </div>

            <button id="crop-close"
                    type="button"
                    aria-label="Fermer la fenêtre"
                    class="grid size-9 place-items-center rounded-lg text-slate-500 hover:bg-slate-100">
                ✕
            </button>

        </div>

        <!-- IMAGE WORKSPACE -->
        <div class="min-h-0 flex-1 overflow-auto bg-[#101827] p-4">

            <div class="mx-auto max-w-full">
                <img id="crop-image"
                     alt="Image à recadrer"
                     class="block max-h-[55vh] max-w-full">
            </div>

        </div>

        <!-- MODAL FOOTER -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4">

            <!-- TOOLS -->
            <div class="flex flex-wrap items-center gap-2">

                <button id="crop-zoom-in"
                        type="button"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-blue-50">
                    Zoom +
                </button>

                <button id="crop-zoom-out"
                        type="button"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-blue-50">
                    Zoom −
                </button>

                <button id="crop-reset"
                        type="button"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-blue-50">
                    Réinitialiser
                </button>

            </div>

            <!-- MODAL ACTIONS -->
            <div class="flex items-center gap-2">

                <button id="crop-cancel"
                        type="button"
                        class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                    Annuler
                </button>

                <button id="crop-apply"
                        type="button"
                        class="rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60">
                    Valider le cadrage
                </button>

            </div>

        </div>

    </div>

</div>

<!-- ================================================= -->
<!-- EDITOR STYLES                                     -->
<!-- ================================================= -->

<style>
    .editor-tool {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 8px;
        border-radius: 8px;
        font-size: 14px;
        color: #475569;
        transition: background .15s;
    }

    .editor-tool:hover {
        background: #dbeafe;
        color: #2563eb;
    }

    .editor-tool:focus-visible {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }
</style>

<!-- ================================================= -->
<!-- GENERAL FORM JAVASCRIPT                           -->
<!-- ================================================= -->

<script>
document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('salon-form');

    if (!form) return;

    // DESCRIPTION COUNTER
    const description = document.getElementById('description');
    const counter = document.getElementById('description-counter');

    const updateCounter = () => {
        counter.textContent = `${description.value.length} / 5000`;
    };

    description.addEventListener('input', updateCounter);
    updateCounter();

    // DESCRIPTION TOOLBAR
    document.querySelectorAll('[data-format]').forEach(button => {

        button.addEventListener('click', () => {

            const action = button.dataset.format;

            const start = description.selectionStart;
            const end = description.selectionEnd;

            const selected = description.value.slice(start, end);

            let value = '';
            let cursorStart = 0;
            let cursorEnd = 0;

            if (action === 'bold') {

                value = `**${selected || 'texte'}**`;
                cursorStart = 2;
                cursorEnd = value.length - 2;

            } else if (action === 'italic') {

                value = `*${selected || 'texte'}*`;
                cursorStart = 1;
                cursorEnd = value.length - 1;

            } else if (action === 'underline') {

                value = `<u>${selected || 'texte'}</u>`;
                cursorStart = 3;
                cursorEnd = value.length - 4;

            } else if (action === 'bullet') {

                value = '- ' + (selected || 'Élément');
                cursorStart = 2;
                cursorEnd = value.length;

            } else if (action === 'number') {

                value = '1. ' + (selected || 'Élément');
                cursorStart = 3;
                cursorEnd = value.length;

            } else if (action === 'link') {

                value = `[${selected || 'Lien'}](https://example.com)`;
                cursorStart = 1;
                cursorEnd = 1 + (selected || 'Lien').length;

            }

            description.setRangeText(value, start, end, 'select');
            description.focus();

            description.setSelectionRange(
                start + cursorStart,
                start + cursorEnd
            );

            description.dispatchEvent(new Event('input'));
        });
    });

    // DATE VALIDATION
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    const updateDates = () => {
        endDate.min = startDate.value || '';
    };

    startDate.addEventListener('change', updateDates);
    updateDates();

    // REGISTRATIONS
    const status = document.getElementById('status');
    const registration = document.getElementById('registration-switch');
    const registrationLabel = document.getElementById('registration-label');

    const updateRegistration = () => {

        const locked = [
            'completed',
            'cancelled'
        ].includes(status.value);

        if (locked) {
            registration.checked = false;
        }

        registration.disabled = locked;

        registrationLabel.textContent =
            registration.checked ? 'Ouvertes' : 'Fermées';

        registrationLabel.classList.toggle(
            'text-emerald-700',
            registration.checked
        );
    };

    status.addEventListener('change', updateRegistration);
    registration.addEventListener('change', updateRegistration);

    updateRegistration();

    // RESET GENERAL FORM FIELDS
    form.addEventListener('reset', () => {

        window.setTimeout(() => {
            updateCounter();
            updateDates();
            updateRegistration();
        }, 0);

    });

});
</script>

<!-- CROPPER JAVASCRIPT -->
@vite('resources/js/salon-crop.js')

</x-admin.layout>
