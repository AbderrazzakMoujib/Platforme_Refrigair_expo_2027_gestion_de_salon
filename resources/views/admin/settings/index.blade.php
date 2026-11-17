
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

@php
    $inputClass = 'mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500';

    $labelClass = 'block text-sm font-semibold text-[#0F2347]';
@endphp

<div class="space-y-7">

    <!-- BREADCRUMB -->
    <nav class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.index') }}"
           class="hover:text-blue-600">
            Administration
        </a>
        <span>/</span>
        <span class="font-semibold text-[#0F2347]">
            Paramètres
        </span>
    </nav>

    <!-- HEADER -->
    <header class="flex items-center gap-4">
        <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
            <x-admin.icon name="settings" class="size-7"/>
        </span>

        <div>
            <h1 class="text-2xl font-extrabold text-[#0F2347] sm:text-3xl">
                Paramètres
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Gérez votre profil administrateur et la sécurité de votre compte.
            </p>
        </div>
    </header>

    <!-- MAIN LAYOUT -->
    <div class="grid items-start gap-6 xl:grid-cols-[300px_minmax(0,1fr)]">

        <!-- LEFT SIDEBAR -->
        <aside class="space-y-5">

            <!-- PROFILE CARD -->
            <section class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm">

                <div class="flex justify-center">

                    @if ($admin->profile_photo_url)
                        <img
                            src="{{ $admin->profile_photo_url }}"
                            alt="Photo du profil administrateur"
                            class="size-24 rounded-full border-4 border-blue-50 object-cover shadow-sm"
                        >
                    @else
                        <span class="grid size-24 place-items-center rounded-full bg-[#0F2347] text-3xl font-extrabold text-white">
                            {{ $adminInitials }}
                        </span>
                    @endif

                </div>

                <h2 class="mt-4 break-words text-lg font-extrabold text-[#0F2347]">
                    {{ $admin->name }}
                </h2>

                <p class="mt-1 break-all text-sm text-slate-500">
                    {{ $admin->email }}
                </p>

                <span class="mt-4 inline-flex rounded-full bg-blue-50 px-4 py-1.5 text-xs font-bold text-blue-700">
                    Administrateur
                </span>

                <div class="mt-6 space-y-4 border-t border-slate-100 pt-5 text-left">

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm text-slate-500">
                            Compte
                        </span>

                        <span @class([
                            'rounded-full px-3 py-1 text-xs font-bold',
                            'bg-emerald-50 text-emerald-700' => $admin->is_active,
                            'bg-red-50 text-red-700' => !$admin->is_active,
                        ])>
                            {{ $admin->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm text-slate-500">
                            Téléphone
                        </span>

                        <span class="text-right text-sm font-semibold text-slate-700">
                            {{ $admin->phone ?: '—' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm text-slate-500">
                            Créé le
                        </span>

                        <span class="text-sm font-semibold text-slate-700">
                            {{ $admin->created_at?->format('d/m/Y') }}
                        </span>
                    </div>

                </div>
            </section>

            <!-- SETTINGS MENU -->
            <nav class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm"
                 aria-label="Sections des paramètres">

                <a href="#profil"
                   class="flex items-center gap-3 rounded-xl bg-blue-50 px-4 py-3 text-sm font-bold text-blue-700 hover:bg-blue-100">

                    <x-admin.icon name="users" class="size-5"/>

                    Mon profil
                </a>

                <a href="#securite"
                   class="mt-2 flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">

                    <svg class="size-5"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         stroke-linecap="round"
                         stroke-linejoin="round"
                         aria-hidden="true">
                        <rect x="5" y="10" width="14" height="11" rx="2"/>
                        <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                    </svg>

                    Sécurité
                </a>

                <a href="{{ route('admin.salons.index') }}"
                   class="mt-2 flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">

                    <x-admin.icon name="calendar" class="size-5"/>

                    Gestion des salons
                </a>

            </nav>

            <!-- SECURITY ADVICE -->
            <div class="rounded-2xl bg-[#0F2347] p-6 text-white">

                <div class="flex items-center gap-3">
                    <x-admin.icon name="settings" class="size-6"/>

                    <h3 class="font-bold">
                        Sécurité du compte
                    </h3>
                </div>

                <p class="mt-3 text-sm leading-6 text-blue-100">
                    Utilisez un mot de passe unique et ne partagez jamais
                    vos identifiants administrateur.
                </p>
            </div>

        </aside>

        <!-- RIGHT CONTENT -->
        <main class="min-w-0 space-y-6">

            <!-- PROFILE SECTION -->
            <section id="profil"
                     class="scroll-mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-100 px-6 py-5">

                    <h2 class="text-xl font-bold text-[#0F2347]">
                        Informations personnelles
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Modifiez vos coordonnées et votre photo de profil.
                    </p>

                </div>

                <div class="p-6">

                    @if (session('profile_status'))
                        <div role="status"
                             class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
                            {{ session('profile_status') }}
                        </div>
                    @endif

                    @if ($errors->profile->any())
                        <div role="alert"
                             class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">

                            <p class="font-semibold">
                                Veuillez corriger les erreurs :
                            </p>

                            <ul class="mt-2 list-disc pl-5">
                                @foreach ($errors->profile->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('admin.parametres.profil') }}"
                          enctype="multipart/form-data"
                          class="space-y-6">

                        @csrf
                        @method('PUT')

                        <!-- PHOTO UPLOAD -->
                        <div class="flex flex-wrap items-center gap-5 rounded-xl bg-[#F5F7FB] p-5">

                            <div class="shrink-0">

                                <img id="admin-photo-preview"
                                     src="{{ $admin->profile_photo_url ?: '' }}"
                                     alt="Aperçu photo"
                                     class="{{ $admin->profile_photo ? '' : 'hidden' }} size-24 rounded-full border-4 border-white object-cover shadow-sm">

                                <span id="admin-photo-placeholder"
                                      class="{{ $admin->profile_photo ? 'hidden' : '' }} grid size-24 place-items-center rounded-full bg-blue-100 text-2xl font-bold text-blue-700">
                                    {{ $adminInitials }}
                                </span>

                            </div>

                            <div class="min-w-0 flex-1">

                                <p class="text-sm font-bold text-[#0F2347]">
                                    Photo de profil
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    JPG, PNG ou WebP — 2 Mo maximum.
                                </p>

                                <div class="mt-3 flex flex-wrap items-center gap-3">

                                    <label for="profile_photo"
                                           class="cursor-pointer rounded-lg bg-white px-4 py-2.5 text-xs font-bold text-blue-700 shadow-sm ring-1 ring-blue-200 hover:bg-blue-50">
                                        Changer la photo
                                    </label>

                                    <input id="profile_photo"
                                           name="profile_photo"
                                           type="file"
                                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                           class="sr-only">

                                    <input id="remove_profile_photo"
                                           name="remove_profile_photo"
                                           type="hidden"
                                           value="0">

                                    <button id="admin-remove-photo"
                                            type="button"
                                            class="{{ $admin->profile_photo ? '' : 'hidden' }} text-xs font-semibold text-red-600 hover:underline">
                                        Supprimer
                                    </button>

                                </div>

                                <p id="admin-photo-name"
                                   aria-live="polite"
                                   class="mt-2 max-w-full truncate text-xs text-slate-500">
                                    {{ $admin->profile_photo ? basename($admin->profile_photo) : 'Aucune photo' }}
                                </p>

                            </div>

                        </div>

                        <!-- PROFILE FIELDS -->
                        <div class="grid gap-5 sm:grid-cols-2">

                            <div class="sm:col-span-2">

                                <label for="name" class="{{ $labelClass }}">
                                    Nom complet *
                                </label>

                                <input id="name"
                                       name="name"
                                       type="text"
                                       required
                                       maxlength="255"
                                       autocomplete="name"
                                       value="{{ old('name', $admin->name) }}"
                                       class="{{ $inputClass }}">

                                @error('name', 'profile')
                                    <p class="mt-2 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            <div>

                                <label for="email" class="{{ $labelClass }}">
                                    Adresse email *
                                </label>

                                <input id="email"
                                       name="email"
                                       type="email"
                                       required
                                       maxlength="255"
                                       autocomplete="email"
                                       value="{{ old('email', $admin->email) }}"
                                       class="{{ $inputClass }}">

                                @error('email', 'profile')
                                    <p class="mt-2 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            <div>

                                <label for="phone" class="{{ $labelClass }}">
                                    Téléphone
                                </label>

                                <input id="phone"
                                       name="phone"
                                       type="tel"
                                       maxlength="40"
                                       autocomplete="tel"
                                       placeholder="+212 6..."
                                       value="{{ old('phone', $admin->phone) }}"
                                       class="{{ $inputClass }}">

                                @error('phone', 'profile')
                                    <p class="mt-2 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                        <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-6">

                            <button type="reset"
                                    class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                                Réinitialiser les champs
                            </button>

                            <button type="submit"
                                    class="rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                                Enregistrer les modifications
                            </button>

                        </div>

                    </form>

                </div>

            </section>

            <!-- PASSWORD SECTION -->
            <section id="securite"
                     class="scroll-mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-100 px-6 py-5">

                    <div class="flex items-center gap-3">

                        <span class="grid size-11 place-items-center rounded-xl bg-blue-50 text-blue-700">

                            <svg class="size-6"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 stroke-linecap="round"
                                 stroke-linejoin="round"
                                 aria-hidden="true">

                                <rect x="5" y="10" width="14" height="11" rx="2"/>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"/>

                            </svg>

                        </span>

                        <div>
                            <h2 class="text-xl font-bold text-[#0F2347]">
                                Modifier le mot de passe
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Protégez votre accès administrateur.
                            </p>
                        </div>

                    </div>

                </div>

                <div class="p-6">

                    @if (session('password_status'))
                        <div role="status"
                             class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
                            {{ session('password_status') }}
                        </div>
                    @endif

                    @if ($errors->password->any())
                        <div role="alert"
                             class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">

                            <ul class="list-disc pl-5">
                                @foreach ($errors->password->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>

                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('admin.parametres.password') }}"
                          class="space-y-5">

                        @csrf
                        @method('PUT')

                        <div>

                            <label for="current_password"
                                   class="{{ $labelClass }}">
                                Mot de passe actuel *
                            </label>

                            <input id="current_password"
                                   name="current_password"
                                   type="password"
                                   required
                                   autocomplete="current-password"
                                   class="{{ $inputClass }}">

                            @error('current_password', 'password')
                                <p class="mt-2 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">

                            <div>

                                <label for="password"
                                       class="{{ $labelClass }}">
                                    Nouveau mot de passe *
                                </label>

                                <input id="password"
                                       name="password"
                                       type="password"
                                       required
                                       minlength="8"
                                       autocomplete="new-password"
                                       class="{{ $inputClass }}">

                                @error('password', 'password')
                                    <p class="mt-2 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            <div>

                                <label for="password_confirmation"
                                       class="{{ $labelClass }}">
                                    Confirmer le mot de passe *
                                </label>

                                <input id="password_confirmation"
                                       name="password_confirmation"
                                       type="password"
                                       required
                                       minlength="8"
                                       autocomplete="new-password"
                                       class="{{ $inputClass }}">

                            </div>

                        </div>

                        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">

                            <div class="flex items-start gap-3">

                                <svg class="mt-0.5 size-5 shrink-0 text-blue-600"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8"
                                     stroke-linecap="round"
                                     aria-hidden="true">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="M12 11v5M12 7h.01"/>
                                </svg>

                                <p class="text-xs leading-6 text-blue-800">
                                    Utilisez au moins 8 caractères. Un mot de passe unique et difficile à deviner est recommandé.
                                </p>

                            </div>

                        </div>

                        <div class="flex justify-end border-t border-slate-100 pt-6">

                            <button type="submit"
                                    class="rounded-xl bg-[#0F2347] px-6 py-3 text-sm font-semibold text-white hover:bg-[#173866]">
                                Mettre à jour le mot de passe
                            </button>

                        </div>

                    </form>

                </div>

            </section>

            <!-- SALON SHORTCUT -->
            <section class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-start gap-3">

                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700">
                        <x-admin.icon name="calendar" class="size-6"/>
                    </span>

                    <div>
                        <h2 class="font-bold text-[#0F2347]">
                            Configuration des salons
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Gérez les dates, informations et statuts des salons.
                        </p>
                    </div>

                </div>

                <a href="{{ route('admin.salons.index') }}"
                   class="shrink-0 rounded-xl border border-blue-200 px-5 py-3 text-center text-sm font-semibold text-blue-700 hover:bg-blue-50">
                    Gérer les salons
                </a>

            </section>

        </main>

    </div>

</div>

<!-- PHOTO PREVIEW -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('profile_photo');
    const preview = document.getElementById('admin-photo-preview');
    const placeholder = document.getElementById('admin-photo-placeholder');
    const removeButton = document.getElementById('admin-remove-photo');
    const removeFlag = document.getElementById('remove_profile_photo');
    const filename = document.getElementById('admin-photo-name');

    if (!input || !preview) return;

    const originalPhoto = @json($admin->profile_photo_url);
    const originalName = @json(
        $admin->profile_photo ? basename($admin->profile_photo) : 'Aucune photo'
    );

    let objectUrl = null;

    function releaseUrl() {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
    }

    function showPhoto(url, name) {
        preview.src = url;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
        removeButton.classList.remove('hidden');
        filename.textContent = name;
    }

    function hidePhoto() {
        preview.removeAttribute('src');
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
        removeButton.classList.add('hidden');
        filename.textContent = 'Aucune photo';
    }

    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;

        if (
            !['image/jpeg', 'image/png', 'image/webp'].includes(file.type)
            || file.size > 2 * 1024 * 1024
        ) {
            alert('Choisissez une photo JPG, PNG ou WebP de 2 Mo maximum.');
            input.value = '';
            return;
        }

        releaseUrl();

        objectUrl = URL.createObjectURL(file);
        removeFlag.value = '0';

        showPhoto(objectUrl, file.name);
    });

    removeButton.addEventListener('click', () => {
        releaseUrl();
        input.value = '';
        removeFlag.value = '1';
        hidePhoto();
    });

    const form = input.closest('form');

    form?.addEventListener('reset', () => {
        setTimeout(() => {
            releaseUrl();
            removeFlag.value = '0';

            if (originalPhoto) {
                showPhoto(originalPhoto, originalName);
            } else {
                hidePhoto();
            }
        }, 0);
    });

    window.addEventListener('beforeunload', releaseUrl);
});
</script>

</x-admin.layout>
