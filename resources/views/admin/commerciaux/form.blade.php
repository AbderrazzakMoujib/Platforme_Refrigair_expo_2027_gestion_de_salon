
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

@php
    $editing = (bool) $formMethod;

    $field = 'mt-2 block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-50';

    $label = 'block text-sm font-semibold text-[#0F2347]';
@endphp

<div class="space-y-6">

    <nav class="flex gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.index') }}"
           class="hover:text-blue-600">Administration</a>
        <span>/</span>
        <a href="{{ route('admin.commerciaux.index') }}"
           class="hover:text-blue-600">Commerciaux</a>
        <span>/</span>
        <span class="font-semibold text-[#0F2347]">
            {{ $editing ? 'Modifier' : 'Ajouter' }}
        </span>
    </nav>

    <header class="flex items-center gap-4">
        <span class="grid size-14 place-items-center rounded-2xl bg-blue-100 text-blue-600">
            <x-admin.icon name="users" class="size-7"/>
        </span>
        <div>
            <h1 class="text-3xl font-bold text-[#0F2347]">
                {{ $editing ? 'Modifier un commercial' : 'Ajouter un commercial' }}
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Gérez les informations personnelles et l'accès à la plateforme.
            </p>
        </div>
    </header>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p class="font-bold">Veuillez corriger les erreurs :</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="commercial-form"
          action="{{ $editing ? route($formRoute, $commercial) : route($formRoute) }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-6">

        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1.5fr)_minmax(280px,1fr)]">

            <!-- INFORMATIONS -->
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div class="border-b border-slate-100 px-6 py-5">
                    <h2 class="text-lg font-bold text-[#0F2347]">
                        Informations personnelles
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Coordonnées et identification du commercial.
                    </p>
                </div>

                <div class="grid gap-5 p-6 sm:grid-cols-2">

                    <div class="sm:col-span-2">
                        <label for="name" class="{{ $label }}">
                            Nom complet *
                        </label>
                        <input id="name"
                               name="name"
                               required
                               maxlength="255"
                               value="{{ old('name', $commercial->name) }}"
                               placeholder="Nom et prénom"
                               class="{{ $field }}">
                    </div>

                    <div>
                        <label for="email" class="{{ $label }}">
                            Adresse email *
                        </label>
                        <input id="email"
                               name="email"
                               type="email"
                               required
                               maxlength="255"
                               value="{{ old('email', $commercial->email) }}"
                               placeholder="email@entreprise.com"
                               class="{{ $field }}">
                    </div>

                    <div>
                        <label for="phone" class="{{ $label }}">
                            Téléphone
                        </label>
                        <input id="phone"
                               name="phone"
                               type="tel"
                               maxlength="40"
                               value="{{ old('phone', $commercial->phone) }}"
                               placeholder="+212 6..."
                               class="{{ $field }}">
                    </div>

                    @unless ($editing)
                        <div>
                            <label for="password" class="{{ $label }}">
                                Mot de passe initial *
                            </label>
                            <input id="password"
                                   name="password"
                                   type="password"
                                   required
                                   minlength="8"
                                   autocomplete="new-password"
                                   class="{{ $field }}">
                        </div>

                        <div>
                            <label for="password_confirmation"
                                   class="{{ $label }}">
                                Confirmer le mot de passe *
                            </label>
                            <input id="password_confirmation"
                                   name="password_confirmation"
                                   type="password"
                                   required
                                   minlength="8"
                                   autocomplete="new-password"
                                   class="{{ $field }}">
                        </div>
                    @endunless

                    <div class="sm:col-span-2">
                        <label for="is_active" class="{{ $label }}">
                            Statut du compte *
                        </label>

                        <select id="is_active"
                                name="is_active"
                                required
                                class="{{ $field }}">
                            <option value="1"
                                @selected((string) old('is_active', (int) $commercial->is_active) === '1')>
                                Actif
                            </option>
                            <option value="0"
                                @selected((string) old('is_active', (int) $commercial->is_active) === '0')>
                                Inactif
                            </option>
                        </select>
                    </div>

                </div>
            </section>

            <!-- PHOTO -->
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-bold text-[#0F2347]">
                    Photo de profil
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    JPG, PNG ou WebP — Maximum 2 Mo.
                </p>

                <div class="mt-6 flex flex-col items-center gap-4">

                    <div class="relative">

                        <img id="photo-preview"
                             src="{{ $commercial->profile_photo_url ?? '' }}"
                             alt="Aperçu de la photo"
                             class="{{ $commercial->profile_photo ? '' : 'hidden' }} size-32 rounded-full border-4 border-white object-cover shadow-md">

                        <div id="photo-placeholder"
                             class="{{ $commercial->profile_photo ? 'hidden' : '' }} grid size-32 place-items-center rounded-full bg-blue-100 text-3xl font-bold text-blue-700">
                            {{ $commercial->initials }}
                        </div>

                    </div>

                    <label for="profile_photo"
                           class="cursor-pointer rounded-xl border border-blue-200 bg-blue-50 px-5 py-3 text-center text-sm font-semibold text-blue-700 transition hover:bg-blue-100">
                        Choisir une photo
                    </label>

                    <input id="profile_photo"
                           type="file"
                           name="profile_photo"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                           class="sr-only">

                    <input id="remove_profile_photo"
                           name="remove_profile_photo"
                           type="hidden"
                           value="0">

                    <button id="remove-photo-button"
                            type="button"
                            class="{{ $commercial->profile_photo ? '' : 'hidden' }} text-sm font-semibold text-red-600 hover:underline">
                        Supprimer la photo
                    </button>

                    <p id="photo-filename"
                       class="max-w-full truncate text-xs text-slate-500"
                       aria-live="polite">
                        {{ $commercial->profile_photo ? basename($commercial->profile_photo) : 'Aucune photo sélectionnée' }}
                    </p>

                </div>
            </section>

        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <a href="{{ route('admin.commerciaux.index') }}"
               class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                ← Retour à la liste
            </a>

            <button type="submit"
                    class="rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                {{ $editing ? 'Enregistrer les modifications' : 'Créer le commercial' }}
            </button>

        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('profile_photo');
    const preview = document.getElementById('photo-preview');
    const placeholder = document.getElementById('photo-placeholder');
    const removeButton = document.getElementById('remove-photo-button');
    const removeFlag = document.getElementById('remove_profile_photo');
    const filename = document.getElementById('photo-filename');

    let objectUrl = null;

    function releaseUrl() {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
    }

    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (
            !allowedTypes.includes(file.type) ||
            file.size > 2 * 1024 * 1024
        ) {
            alert('Choisissez une image JPG, PNG ou WebP de 2 Mo maximum.');
            input.value = '';
            return;
        }

        releaseUrl();

        objectUrl = URL.createObjectURL(file);

        preview.src = objectUrl;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
        removeButton.classList.remove('hidden');
        removeFlag.value = '0';

        filename.textContent = file.name;
    });

    removeButton.addEventListener('click', () => {
        releaseUrl();

        input.value = '';
        preview.removeAttribute('src');
        preview.classList.add('hidden');

        placeholder.classList.remove('hidden');
        removeButton.classList.add('hidden');

        removeFlag.value = '1';
        filename.textContent = 'Aucune photo sélectionnée';
    });

    window.addEventListener('beforeunload', releaseUrl);
});
</script>

</x-admin.layout>
