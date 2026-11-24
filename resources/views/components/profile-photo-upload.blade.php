@props(['user', 'id' => 'profile_photo'])

<div class="flex flex-wrap items-center gap-5 rounded-xl bg-[#F5F7FB] p-4 sm:p-5">
    <div class="shrink-0">
        <img id="{{ $id }}-preview"
             src="{{ $user->profile_photo_url ?: '' }}"
             alt="Aperçu de la photo de profil"
             class="{{ $user->profile_photo ? '' : 'hidden' }} size-20 rounded-full border-4 border-white object-cover shadow-sm">
        <span id="{{ $id }}-placeholder"
              class="{{ $user->profile_photo ? 'hidden' : '' }} grid size-20 place-items-center rounded-full bg-blue-100 text-xl font-bold text-blue-700">
            {{ $user->initials }}
        </span>
    </div>

    <div class="min-w-0 flex-1">
        <p class="text-sm font-bold text-[#0F2347]">Photo de profil <span class="font-normal text-slate-500">(facultatif)</span></p>
        <p class="mt-1 text-xs text-slate-500">JPEG, PNG ou WebP, 2 Mo maximum.</p>
        <div class="mt-3 flex flex-wrap items-center gap-3">
            <label for="{{ $id }}" class="cursor-pointer rounded-lg bg-white px-4 py-2.5 text-xs font-bold text-blue-700 shadow-sm ring-1 ring-blue-200 hover:bg-blue-50">Choisir une photo</label>
            <input id="{{ $id }}" name="profile_photo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="sr-only">
            <input id="{{ $id }}-remove-flag" name="remove_profile_photo" type="hidden" value="0">
            <button id="{{ $id }}-remove" type="button" class="{{ $user->profile_photo ? '' : 'hidden' }} text-xs font-semibold text-red-600 hover:underline">Supprimer la photo</button>
        </div>
        <p id="{{ $id }}-filename" aria-live="polite" class="mt-2 max-w-full truncate text-xs text-slate-500">{{ $user->profile_photo ? basename($user->profile_photo) : 'Aucune photo' }}</p>
        <p id="{{ $id }}-error" class="mt-1 hidden text-xs font-semibold text-red-700" role="alert"></p>
    </div>
</div>

<script>
(() => {
    const input = document.getElementById(@js($id));
    const preview = document.getElementById(@js($id.'-preview'));
    const placeholder = document.getElementById(@js($id.'-placeholder'));
    const removeButton = document.getElementById(@js($id.'-remove'));
    const removeFlag = document.getElementById(@js($id.'-remove-flag'));
    const filename = document.getElementById(@js($id.'-filename'));
    const error = document.getElementById(@js($id.'-error'));
    const originalUrl = @js($user->profile_photo_url);
    const originalName = @js($user->profile_photo ? basename($user->profile_photo) : 'Aucune photo');
    let objectUrl = null;

    const releaseUrl = () => {
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
    };

    const showPhoto = (url, name) => {
        preview.src = url;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
        removeButton.classList.remove('hidden');
        filename.textContent = name;
    };

    const showInitials = () => {
        preview.removeAttribute('src');
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
        removeButton.classList.add('hidden');
        filename.textContent = 'Aucune photo';
    };

    input.addEventListener('change', () => {
        error.classList.add('hidden');
        const file = input.files?.[0];
        if (!file) return;

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
            error.textContent = 'Choisissez une image JPEG, PNG ou WebP de 2 Mo maximum.';
            error.classList.remove('hidden');
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
        showInitials();
    });

    input.closest('form')?.addEventListener('reset', () => {
        setTimeout(() => {
            releaseUrl();
            removeFlag.value = '0';
            error.classList.add('hidden');
            originalUrl ? showPhoto(originalUrl, originalName) : showInitials();
        });
    });
})();
</script>
