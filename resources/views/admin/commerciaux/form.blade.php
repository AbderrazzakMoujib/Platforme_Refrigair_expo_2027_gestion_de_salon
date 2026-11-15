<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">
    <a href="{{ route('admin.commerciaux.index') }}" class="text-sm font-semibold text-[#2563EB] hover:underline">← Retour aux commerciaux</a>
    <h1 class="mt-3 text-2xl font-bold text-slate-900">{{ $pageTitle }}</h1>
    <p class="mt-1 text-sm text-slate-500">Définissez le mot de passe initial du compte commercial.</p>

    <form method="POST" action="{{ $formMethod ? route($formRoute, $commercial) : route($formRoute) }}" class="mt-6 max-w-2xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70">
        @csrf
        @if ($formMethod) @method($formMethod) @endif
        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="name" class="text-sm font-medium text-slate-700">Nom complet</label>
                <input id="name" name="name" value="{{ old('name', $commercial->name) }}" required maxlength="255" class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="text-sm font-medium text-slate-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $commercial->email) }}" required maxlength="255" class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="phone" class="text-sm font-medium text-slate-700">Téléphone</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone', $commercial->phone) }}" maxlength="40" class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            @unless ($formMethod)
                <div>
                    <label for="password" class="text-sm font-medium text-slate-700">Mot de passe initial</label>
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="text-sm font-medium text-slate-700">Confirmer le mot de passe</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            @endunless
            <div class="sm:col-span-2">
                <label for="is_active" class="text-sm font-medium text-slate-700">Statut</label>
                <select id="is_active" name="is_active" required class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="1" @selected((string) old('is_active', (int) $commercial->is_active) === '1')>Actif</option>
                    <option value="0" @selected((string) old('is_active', (int) $commercial->is_active) === '0')>Inactif</option>
                </select>
                @error('is_active') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="mt-6 flex flex-wrap justify-end gap-3">
            <a href="{{ route('admin.commerciaux.index') }}" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Annuler</a>
            <button class="rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Enregistrer</button>
        </div>
    </form>
</x-admin.layout>
