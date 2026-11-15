<x-layouts.guest title="Mot de passe oublié | Refrigair Expo 2027">
    <h1 class="mt-6 text-2xl font-bold">Mot de passe oublié</h1>
    <p class="mt-2 text-sm text-slate-600">3tina l’email, w ila lcompte actif ghadi twslek lien.</p>

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
        @csrf
        <div>
            <label for="email" class="text-sm font-medium">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="mt-1 w-full rounded-md border-slate-300 shadow-sm focus:border-sky-600 focus:ring-sky-600">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="w-full rounded-md bg-sky-700 px-4 py-2 font-semibold text-white hover:bg-sky-800">Sift lien</button>
    </form>

    <a href="{{ route('login') }}" class="mt-5 inline-block text-sm text-sky-700 hover:underline">Rje3 l connexion</a>
</x-layouts.guest>
