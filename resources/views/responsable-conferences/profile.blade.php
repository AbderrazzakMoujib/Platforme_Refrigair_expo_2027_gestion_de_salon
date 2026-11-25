
<x-responsable-conferences.layout :user="$user">

<div class="space-y-6">

    {{-- PAGE HEADER --}}
    <header>
        <p class="text-xs font-bold text-blue-600">
            Conférences & Lounge
            <span class="mx-2 text-slate-300">/</span>
            Mon profil
        </p>

        <div class="mt-4 flex items-center gap-4">
            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <x-admin.icon name="users" class="size-7"/>
            </span>

            <div>
                <h1 class="text-3xl font-black text-[#0F2347]">
                    Mon profil
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Consultez et mettez à jour vos informations
                    personnelles et de sécurité.
                </p>
            </div>
        </div>
    </header>

    {{-- SUCCESS MESSAGES --}}
    @if(session('status'))
        <div role="status"
             class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if(session('password_status'))
        <div role="status"
             class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-700">
            {{ session('password_status') }}
        </div>
    @endif

    @if($errors->default->any())
        <div role="alert"
             class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach($errors->default->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- MAIN GRID --}}
    <div class="grid items-start gap-5 lg:grid-cols-[340px_minmax(0,1fr)]">

        {{-- FORM INFORMATIONS --}}
        <form id="profile-info-form"
              method="POST"
              action="{{ route('responsable-conferences.profile.update') }}"
              enctype="multipart/form-data"
              class="contents">

            @csrf
            @method('PUT')

            {{-- LEFT PROFILE CARD --}}
            <aside class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:row-span-2 lg:self-stretch">

                {{-- COVER --}}
                <div class="relative h-36 overflow-hidden bg-gradient-to-br from-sky-400 via-blue-500 to-[#0F2347]">

                    <div class="absolute -bottom-16 -left-8 size-40 rounded-full border-[28px] border-white/15"></div>

                    <div class="absolute -right-10 -top-14 size-52 rounded-full border-[36px] border-white/10"></div>

                    <div class="absolute bottom-0 left-0 h-10 w-full rounded-[50%_50%_0_0] bg-white/20"></div>
                </div>

                {{-- AVATAR --}}
                <div class="relative -mt-16 flex justify-center">
                    <div class="relative">

                        <img id="responsable-photo-preview"
                             src="{{ $user->profile_photo_url ?: '' }}"
                             alt="Photo de profil"
                             class="{{ $user->profile_photo_url ? '' : 'hidden' }} size-32 rounded-full border-4 border-white bg-white object-cover shadow-lg">

                        <span id="responsable-photo-initials"
                              class="{{ $user->profile_photo_url ? 'hidden' : '' }} grid size-32 place-items-center rounded-full border-4 border-white bg-[#0877F9] text-4xl font-black text-white shadow-lg">
                            {{ $user->initials }}
                        </span>

                        <label for="manager-own-profile-photo"
                               class="absolute bottom-1 right-1 grid size-10 cursor-pointer place-items-center rounded-full border-4 border-white bg-[#2563EB] text-white shadow-md hover:bg-blue-700"
                               title="Modifier la photo">

                            <x-admin.icon name="camera" class="size-4"/>
                        </label>
                    </div>
                </div>

                {{-- PROFILE DATA --}}
                <div class="px-6 pb-6 pt-5 text-center">

                    <h2 class="text-xl font-black text-[#0F2347]">
                        {{ $user->name }}
                    </h2>

                    <span class="mt-3 inline-flex rounded-full bg-blue-100 px-4 py-2 text-xs font-bold text-blue-700">
                        Responsable Conférences & Lounge
                    </span>

                    <div class="mt-6 space-y-4 border-t border-slate-100 pt-5 text-left">

                        {{-- EMAIL --}}
                        <div class="flex items-start gap-3">
                            <x-admin.icon name="inbox"
                                          class="mt-0.5 size-5 shrink-0 text-[#0F2347]"/>

                            <span class="min-w-0 break-all text-sm font-medium text-slate-700">
                                {{ $user->email }}
                            </span>
                        </div>

                        {{-- PHONE --}}
                        <div class="flex items-center gap-3">
                            <x-admin.icon name="users"
                                          class="size-5 shrink-0 text-[#0F2347]"/>

                            <span class="text-sm font-medium text-slate-700">
                                {{ $user->phone ?: 'Téléphone non renseigné' }}
                            </span>
                        </div>

                        {{-- MEMBER SINCE --}}
                        <div class="flex items-center gap-3">
                            <x-admin.icon name="calendar"
                                          class="size-5 shrink-0 text-[#0F2347]"/>

                            <span class="text-sm font-medium text-slate-700">
                                Membre depuis le
                                {{ $user->created_at?->locale('fr')->isoFormat('D MMM YYYY') }}
                            </span>
                        </div>
                    </div>

                    {{-- PHOTO UPLOAD --}}
                    <div class="mt-6 border-t border-slate-100 pt-5">

                        <input type="file"
                               id="manager-own-profile-photo"
                               name="profile_photo"
                               accept="image/jpeg,image/png,image/webp"
                               class="sr-only">

                        <input type="hidden"
                               id="responsable-photo-remove-flag"
                               name="remove_profile_photo"
                               value="0">

                        <label for="manager-own-profile-photo"
                               class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-blue-500 px-4 py-3 text-sm font-bold text-blue-700 transition hover:bg-blue-50">

                            <x-admin.icon name="camera" class="size-5"/>
                            Modifier la photo
                        </label>

                        <button type="button"
                                id="responsable-photo-remove"
                                class="{{ $user->profile_photo_url ? '' : 'hidden' }} mt-3 text-xs font-bold text-red-600 hover:underline">
                            Supprimer la photo
                        </button>

                        <p class="mt-3 text-xs text-slate-400">
                            JPG, PNG ou WebP · 2 Mo maximum
                        </p>

                        <p id="responsable-photo-error"
                           role="alert"
                           class="mt-2 hidden text-xs font-semibold text-red-600">
                        </p>
                    </div>
                </div>
            </aside>

            {{-- RIGHT: PERSONAL INFORMATIONS --}}
            <section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

                <div class="flex items-center gap-3 border-b border-slate-100 pb-5">

                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-600">
                        <x-admin.icon name="settings" class="size-6"/>
                    </span>

                    <div>
                        <h2 class="text-xl font-black text-[#0F2347]">
                            Modifier mes informations
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Mettez à jour vos informations personnelles.
                        </p>
                    </div>
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-2">

                    {{-- NAME --}}
                    <div>
                        <label for="profile-name"
                               class="mb-2 block text-sm font-bold text-[#0F2347]">
                            Nom complet
                            <span class="text-red-500">*</span>
                        </label>

                        <input id="profile-name"
                               name="name"
                               type="text"
                               required
                               maxlength="255"
                               autocomplete="name"
                               value="{{ old('name', $user->name) }}"
                               class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm focus:border-blue-500 focus:ring-blue-200">
                    </div>

                    {{-- EMAIL --}}
                    <div>
                        <label for="profile-email"
                               class="mb-2 block text-sm font-bold text-[#0F2347]">
                            Adresse e-mail
                            <span class="text-red-500">*</span>
                        </label>

                        <input id="profile-email"
                               name="email"
                               type="email"
                               required
                               maxlength="255"
                               autocomplete="email"
                               value="{{ old('email', $user->email) }}"
                               class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm focus:border-blue-500 focus:ring-blue-200">
                    </div>

                    {{-- PHONE --}}
                    <div>
                        <label for="profile-phone"
                               class="mb-2 block text-sm font-bold text-[#0F2347]">
                            Téléphone
                        </label>

                        <input id="profile-phone"
                               name="phone"
                               type="tel"
                               maxlength="30"
                               autocomplete="tel"
                               placeholder="+212 6 00 00 00 00"
                               value="{{ old('phone', $user->phone) }}"
                               class="h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm focus:border-blue-500 focus:ring-blue-200">
                    </div>

                    {{-- ROLE --}}
                    <div>
                        <label for="profile-role"
                               class="mb-2 block text-sm font-bold text-[#0F2347]">
                            Rôle
                        </label>

                        <input id="profile-role"
                               type="text"
                               value="Responsable Conférences & Lounge"
                               disabled
                               class="h-12 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 text-sm font-semibold text-slate-500">
                    </div>
                </div>

                {{-- ACTIONS --}}
                <div class="mt-8 flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-6">

                    <button type="reset"
                            class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-[#0F2347] transition hover:bg-slate-50">
                        Annuler
                    </button>

                    <button type="submit"
                            class="rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                        Enregistrer les modifications
                    </button>
                </div>
            </section>

        </form>

        {{-- SECURITY CARD --}}
        <section class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 lg:col-start-2">

            <div class="flex items-center gap-3 border-b border-slate-100 pb-5">

                <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-600">
                    <x-admin.icon name="settings" class="size-6"/>
                </span>

                <div>
                    <h2 class="text-xl font-black text-[#0F2347]">
                        Sécurité
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Mettez à jour votre mot de passe pour sécuriser votre compte.
                    </p>
                </div>
            </div>

            {{-- PASSWORD ERRORS --}}
            @if($errors->passwordUpdate->any())
                <div role="alert"
                     class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">

                    <ul class="list-disc pl-5">
                        @foreach($errors->passwordUpdate->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- PASSWORD FORM --}}
            <form method="POST"
                  action="{{ route('responsable-conferences.profile.password') }}"
                  class="mt-6">

                @csrf
                @method('PUT')

                <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_250px]">

                    <div class="space-y-5">

                        <div class="grid gap-5 sm:grid-cols-2">

                            {{-- CURRENT PASSWORD --}}
                            <div>
                                <label for="current-password"
                                       class="mb-2 block text-sm font-bold text-[#0F2347]">
                                    Mot de passe actuel
                                    <span class="text-red-500">*</span>
                                </label>

                                <input id="current-password"
                                       name="current_password"
                                       type="password"
                                       required
                                       autocomplete="current-password"
                                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm focus:border-blue-500 focus:ring-blue-200">
                            </div>

                            {{-- NEW PASSWORD --}}
                            <div>
                                <label for="new-password"
                                       class="mb-2 block text-sm font-bold text-[#0F2347]">
                                    Nouveau mot de passe
                                    <span class="text-red-500">*</span>
                                </label>

                                <input id="new-password"
                                       name="password"
                                       type="password"
                                       required
                                       minlength="8"
                                       autocomplete="new-password"
                                       class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm focus:border-blue-500 focus:ring-blue-200">
                            </div>
                        </div>

                        {{-- CONFIRM PASSWORD --}}
                        <div>
                            <label for="confirm-password"
                                   class="mb-2 block text-sm font-bold text-[#0F2347]">
                                Confirmer le nouveau mot de passe
                                <span class="text-red-500">*</span>
                            </label>

                            <input id="confirm-password"
                                   name="password_confirmation"
                                   type="password"
                                   required
                                   minlength="8"
                                   autocomplete="new-password"
                                   class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm focus:border-blue-500 focus:ring-blue-200">
                        </div>
                    </div>

                    {{-- SECURITY TIPS --}}
                    <aside class="rounded-xl bg-blue-50 p-5">

                        <h3 class="text-sm font-bold text-[#0F2347]">
                            Conseils de sécurité
                        </h3>

                        <p class="mt-3 text-xs leading-6 text-slate-600">
                            Utilisez un mot de passe unique d'au moins 8 caractères.
                        </p>

                        <p class="mt-3 text-xs leading-6 text-slate-600">
                            Pour une meilleure sécurité, privilégiez
                            une phrase de passe longue.
                        </p>
                    </aside>
                </div>

                {{-- PASSWORD ACTION --}}
                <div class="mt-7 flex justify-end border-t border-slate-100 pt-5">

                    <button type="submit"
                            class="rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-bold text-white transition hover:bg-blue-700">
                        Mettre à jour le mot de passe
                    </button>
                </div>
            </form>
        </section>

    </div>
</div>

{{-- PHOTO PREVIEW SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('profile-info-form');
    const input = document.getElementById('manager-own-profile-photo');
    const preview = document.getElementById('responsable-photo-preview');
    const initials = document.getElementById('responsable-photo-initials');
    const remove = document.getElementById('responsable-photo-remove');
    const removeFlag = document.getElementById('responsable-photo-remove-flag');
    const error = document.getElementById('responsable-photo-error');

    const originalUrl = @js($user->profile_photo_url);
    let objectUrl = null;

    function clearObjectUrl() {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
    }

    function showPhoto(url) {
        preview.src = url;
        preview.classList.remove('hidden');
        initials.classList.add('hidden');
        remove.classList.remove('hidden');
    }

    function showInitials() {
        preview.removeAttribute('src');
        preview.classList.add('hidden');
        initials.classList.remove('hidden');
        remove.classList.add('hidden');
    }

    input.addEventListener('change', () => {
        const file = input.files?.[0];

        if (!file) return;

        error.classList.add('hidden');

        const validTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (
            !validTypes.includes(file.type) ||
            file.size > 2 * 1024 * 1024
        ) {
            input.value = '';

            error.textContent =
                'Image JPG, PNG ou WebP, 2 Mo maximum.';

            error.classList.remove('hidden');
            return;
        }

        clearObjectUrl();

        objectUrl = URL.createObjectURL(file);
        removeFlag.value = '0';

        showPhoto(objectUrl);
    });

    remove.addEventListener('click', () => {
        clearObjectUrl();

        input.value = '';
        removeFlag.value = '1';

        showInitials();
    });

    form.addEventListener('reset', () => {
        setTimeout(() => {
            clearObjectUrl();

            removeFlag.value = '0';
            error.classList.add('hidden');

            if (originalUrl) {
                showPhoto(originalUrl);
            } else {
                showInitials();
            }
        }, 0);
    });

    window.addEventListener('pagehide', clearObjectUrl);
});
</script>

</x-responsable-conferences.layout>
