<x-layouts.guest title="Mon profil | Refrigair Expo 2027">
    <h1 class="mt-6 text-2xl font-bold">Mon profil</h1>
    <p class="mt-2 text-sm text-slate-600">{{ $commercial->name }} · {{ $commercial->email }}</p>
    @if (session('status')) <p class="mt-5 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</p> @endif

    <form method="POST" action="{{ route('commercial.profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('PUT')
        <div>
            <label for="current_password" class="text-sm font-medium">Mot de passe actuel</label>
            <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-sky-600 focus:ring-sky-600">
            @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="text-sm font-medium">Nouveau mot de passe</label>
            <input id="password" name="password" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-sky-600 focus:ring-sky-600">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="text-sm font-medium">Confirmer le nouveau mot de passe</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-sky-600 focus:ring-sky-600">
        </div>
        <button class="w-full rounded-md bg-sky-700 px-4 py-2 font-semibold text-white hover:bg-sky-800">Modifier le mot de passe</button>
    </form>
</x-layouts.guest>
