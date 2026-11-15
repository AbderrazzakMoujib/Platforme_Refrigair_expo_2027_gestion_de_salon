<x-layouts.guest title="Activer votre accès | Refrigair Expo 2027">
    <h1 class="mt-6 text-2xl font-bold">Activer votre accès</h1>
    <p class="mt-2 text-sm text-slate-600">Créez votre mot de passe pour le compte {{ $email }}. Le lien est à usage unique.</p>

    <form method="POST" action="{{ route('commercial.invitation.accept', $token) }}" class="mt-6 space-y-5">
        @csrf
        <div>
            <label for="password" class="text-sm font-medium">Mot de passe (8 caractères minimum)</label>
            <input id="password" name="password" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-sky-600 focus:ring-sky-600">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="text-sm font-medium">Confirmer le mot de passe</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-sky-600 focus:ring-sky-600">
        </div>
        <button type="submit" class="w-full rounded-md bg-sky-700 px-4 py-2 font-semibold text-white hover:bg-sky-800">Activer mon accès</button>
    </form>
</x-layouts.guest>
