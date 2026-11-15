<x-layouts.guest title="Nouveau mot de passe | Refrigair Expo 2027">
    <h1 class="mt-6 text-2xl font-bold">Nouveau mot de passe</h1>

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label for="email" class="text-sm font-medium">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autocomplete="email" class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-sky-600 focus:ring-sky-600">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="text-sm font-medium">Mot de passe (8 caractères minimum)</label>
            <input id="password" name="password" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-sky-600 focus:ring-sky-600">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="text-sm font-medium">Confirmer le mot de passe</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-sky-600 focus:ring-sky-600">
        </div>
        <button type="submit" class="w-full rounded-md bg-sky-700 px-4 py-2 font-semibold text-white hover:bg-sky-800">Beddel mot de passe</button>
    </form>
</x-layouts.guest>
