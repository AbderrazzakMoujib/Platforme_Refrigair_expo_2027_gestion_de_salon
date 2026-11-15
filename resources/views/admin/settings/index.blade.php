
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">
    <div>
        <p class="text-sm font-semibold text-blue-600">
            Administration / Configuration
        </p>

        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
            Paramètres du compte
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Gérez vos informations personnelles et la sécurité de votre compte administrateur.
        </p>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        <!-- Profile summary -->
        <aside class="xl:col-span-1">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center gap-4">
                    <div class="grid size-16 shrink-0 place-items-center rounded-2xl bg-[#0F2347] text-xl font-bold text-white">
                        {{ $adminInitials }}
                    </div>

                    <div class="min-w-0">
                        <h2 class="truncate text-lg font-bold text-slate-900">
                            {{ $admin->name }}
                        </h2>
                        <p class="truncate text-sm text-slate-500">
                            {{ $admin->email }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 border-t border-slate-100 pt-5">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm text-slate-500">Rôle</span>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                            Administrateur
                        </span>
                    </div>

                    <div class="mt-5 flex items-center justify-between gap-3">
                        <span class="text-sm text-slate-500">Compte</span>
                        <span class="text-sm font-semibold text-emerald-700">
                            {{ $admin->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>

                    <div class="mt-5 flex items-center justify-between gap-3">
                        <span class="text-sm text-slate-500">Téléphone</span>
                        <span class="text-sm font-semibold text-slate-700">
                            {{ $admin->phone ?: 'Non renseigné' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-5 rounded-2xl bg-[#0F2347] p-6 text-white">
                <div class="flex items-center gap-3">
                    <x-admin.icon name="settings" class="size-6" />
                    <h3 class="font-bold">Sécurité du compte</h3>
                </div>

                <p class="mt-3 text-sm leading-6 text-blue-100">
                    Utilisez un mot de passe unique et évitez de partager
                    votre accès administrateur.
                </p>
            </div>
        </aside>

        <div class="space-y-6 xl:col-span-2">

            <!-- Profile -->
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        Informations personnelles
                    </h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Modifiez les informations de votre profil.
                    </p>
                </div>

                @if (session('profile_status'))
                    <div role="status"
                         class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">
                        {{ session('profile_status') }}
                    </div>
                @endif

                @if ($errors->profile->any())
                    <div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        Veuillez corriger les informations indiquées.
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('admin.parametres.profil') }}"
                      class="mt-7 space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name"
                               class="mb-2 block text-sm font-semibold text-slate-700">
                            Nom complet *
                        </label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            required
                            maxlength="255"
                            autocomplete="name"
                            value="{{ old('name', $admin->name) }}"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('name', 'profile')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email"
                               class="mb-2 block text-sm font-semibold text-slate-700">
                            Adresse email *
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            required
                            maxlength="255"
                            autocomplete="email"
                            value="{{ old('email', $admin->email) }}"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('email', 'profile')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone"
                               class="mb-2 block text-sm font-semibold text-slate-700">
                            Téléphone
                        </label>
                        <input
                            id="phone"
                            name="phone"
                            type="tel"
                            maxlength="40"
                            autocomplete="tel"
                            value="{{ old('phone', $admin->phone) }}"
                            placeholder="+212 6..."
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('phone', 'profile')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end border-t border-slate-100 pt-6">
                        <button
                            type="submit"
                            class="rounded-xl bg-[#2563EB] px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </section>

            <!-- Password -->
            <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        Modifier le mot de passe
                    </h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Saisissez votre mot de passe actuel pour sécuriser la modification.
                    </p>
                </div>

                @if (session('password_status'))
                    <div role="status"
                         class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">
                        {{ session('password_status') }}
                    </div>
                @endif

                @if ($errors->password->any())
                    <div role="alert"
                         class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                        Veuillez vérifier les mots de passe saisis.
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('admin.parametres.password') }}"
                      class="mt-7 space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password"
                               class="mb-2 block text-sm font-semibold text-slate-700">
                            Mot de passe actuel *
                        </label>
                        <input
                            id="current_password"
                            name="current_password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('current_password', 'password')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password"
                               class="mb-2 block text-sm font-semibold text-slate-700">
                            Nouveau mot de passe *
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                        @error('password', 'password')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation"
                               class="mb-2 block text-sm font-semibold text-slate-700">
                            Confirmer le nouveau mot de passe *
                        </label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                    </div>

                    <div class="flex justify-end border-t border-slate-100 pt-6">
                        <button
                            type="submit"
                            class="rounded-xl bg-[#0F2347] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#173866]">
                            Mettre à jour le mot de passe
                        </button>
                    </div>
                </form>
            </section>

        </div>
    </div>
</x-admin.layout>
