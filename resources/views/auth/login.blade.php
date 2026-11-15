<x-layouts.guest title="Connexion | Refrigair Expo 2027">
    <h1 class="mt-6 text-2xl font-bold">Connexion</h1>
    <p class="mt-2 text-sm text-slate-600">Dkhol b email w mot de passe dyalek.</p>

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
        @csrf
        <div>
            <label for="email" class="text-sm font-medium">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-sky-600 focus:ring-sky-600">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="text-sm font-medium">Mot de passe</label>
                <a href="{{ route('password.request') }}" class="text-sm text-sky-700 hover:underline">Mot de passe oublié ?</a>
            </div>
            <input id="password" name="password" type="password" required autocomplete="current-password" class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-sky-600 focus:ring-sky-600">
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-sky-700 focus:ring-sky-600">
            Rester connecté
        </label>

        <button type="submit" class="w-full rounded-md bg-sky-700 px-4 py-2 font-semibold text-white hover:bg-sky-800">Se connecter</button>
    </form>
</x-layouts.guest>
