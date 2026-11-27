<x-technical.layout :user="$user" title="Mon profil">
    <div class="mb-7">
        <p class="text-sm font-bold uppercase tracking-wider text-blue-700">Compte personnel</p>
        <h2 class="mt-1 text-2xl font-black text-[#0F2347]">Mon profil</h2>
        <p class="mt-2 text-sm text-slate-600">Gérez vos coordonnées et la sécurité de votre compte.</p>
    </div>

    @if (session('status'))
        <div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
    @endif
    @if (session('password_status'))
        <div role="status" class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('password_status') }}</div>
    @endif

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(320px,.8fr)]">
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-white px-5 py-5 sm:px-7">
                <x-profile-avatar :user="$user" class="size-16 shrink-0 text-lg" />
                <div class="min-w-0"><h3 class="truncate text-lg font-extrabold text-[#0F2347]">{{ $user->name }}</h3><p class="mt-1 text-sm text-slate-500">Responsable Technique</p></div>
                <span class="ml-auto rounded-full px-3 py-1 text-xs font-bold {{ $user->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $user->is_active ? 'Compte actif' : 'Compte inactif' }}</span>
            </div>
            <form method="POST" action="{{ route('responsable-technique.profile.update') }}" enctype="multipart/form-data" class="space-y-6 p-5 sm:p-7">
                @csrf @method('PUT')
                <x-profile-photo-upload :user="$user" id="technical-profile-photo" />
                @foreach ($errors->getBag('profile')->get('profile_photo') as $error)<p class="text-sm font-medium text-red-700">{{ $error }}</p>@endforeach

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-bold text-slate-700">Nom complet</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        @foreach ($errors->getBag('profile')->get('name') as $error)<p class="mt-1 text-sm text-red-700">{{ $error }}</p>@endforeach
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-bold text-slate-700">Adresse e-mail</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        <p class="mt-1 text-xs text-slate-500">Changer l’adresse e-mail réinitialise son état de vérification.</p>
                        @foreach ($errors->getBag('profile')->get('email') as $error)<p class="mt-1 text-sm text-red-700">{{ $error }}</p>@endforeach
                    </div>
                    <div class="sm:col-span-2">
                        <label for="phone" class="block text-sm font-bold text-slate-700">Téléphone <span class="font-normal text-slate-400">(facultatif)</span></label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" maxlength="40" autocomplete="tel" class="mt-2 block w-full rounded-xl border-slate-300 px-4 py-3 text-sm shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                        @foreach ($errors->getBag('profile')->get('phone') as $error)<p class="mt-1 text-sm text-red-700">{{ $error }}</p>@endforeach
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5">
                    <p class="text-xs text-slate-500">Votre rôle et vos permissions sont gérés par l’administrateur.</p>
                    <button type="submit" class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">Enregistrer le profil</button>
                </div>
            </form>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="mb-5"><span class="grid size-11 place-items-center rounded-xl bg-blue-50 text-blue-700"><x-admin.icon name="lock" class="size-5" /></span><h3 class="mt-4 text-lg font-extrabold text-[#0F2347]">Sécurité du compte</h3><p class="mt-1 text-sm text-slate-500">Choisissez un mot de passe unique d’au moins 8 caractères.</p></div>
            <form method="POST" action="{{ route('responsable-technique.profile.password') }}" class="space-y-4">
                @csrf @method('PUT')
                @foreach ([['current_password','Mot de passe actuel','current-password'],['password','Nouveau mot de passe','new-password'],['password_confirmation','Confirmer le mot de passe','new-password']] as [$field,$label,$autocomplete])
                    <div>
                        <label for="{{ $field }}" class="block text-sm font-bold text-slate-700">{{ $label }}</label>
                        <div class="relative mt-2">
                            <input id="{{ $field }}" name="{{ $field }}" type="password" autocomplete="{{ $autocomplete }}" @if ($field !== 'current_password') minlength="8" @endif required class="block w-full rounded-xl border-slate-300 px-4 py-3 pr-12 text-sm shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB]">
                            <button type="button" data-password-toggle="{{ $field }}" aria-label="Afficher {{ mb_strtolower($label) }}" class="absolute inset-y-0 right-0 grid w-12 place-items-center rounded-r-xl text-slate-500 hover:text-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500"><x-admin.icon name="eye" class="size-5" /></button>
                        </div>
                        @foreach ($errors->getBag('passwordUpdate')->get($field) as $error)<p class="mt-1 text-sm text-red-700">{{ $error }}</p>@endforeach
                    </div>
                @endforeach
                <button type="submit" class="mt-2 w-full rounded-xl border border-[#0F2347] px-5 py-3 text-sm font-bold text-[#0F2347] transition hover:bg-[#0F2347] hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">Mettre à jour le mot de passe</button>
            </form>
        </section>
    </div>

    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.passwordToggle);
                const visible = input.type === 'password';
                input.type = visible ? 'text' : 'password';
                button.setAttribute('aria-label', `${visible ? 'Masquer' : 'Afficher'} le champ ${input.labels?.[0]?.textContent?.trim() ?? 'mot de passe'}`);
            });
        });
    </script>
</x-technical.layout>
