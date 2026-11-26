<x-layouts.guest title="Activation de votre compte | REFRIGAIR EXPO 2027">
    @if ($state === 'valid')
        <h1 class="mt-6 text-2xl font-bold text-[#0F2347]">Activez votre espace {{ $roleLabel }}</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Bonjour {{ $name }}, définissez votre mot de passe pour activer le compte {{ $email }}. Ce lien personnel est à usage unique et expire dans 48 heures.</p>

        @if ($errors->any())
            <div role="alert" class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('account-activation.accept', ['token' => $token]) }}" class="mt-6 space-y-5">
            @csrf
            <div>
                <label for="password" class="block text-sm font-semibold text-[#0F2347]">Nouveau mot de passe</label>
                <div class="relative mt-2">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="10" width="14" height="11" rx="2"/><path stroke-linecap="round" d="M8 10V7a4 4 0 1 1 8 0v3M12 14v3"/></svg>
                    </span>
                    <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" aria-describedby="password-help @error('password') password-error @enderror" @error('password') aria-invalid="true" @enderror class="block min-h-12 w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-12 text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-[#2563EB] focus:outline-none focus:ring-2 focus:ring-[#2563EB]/20">
                    <button type="button" data-password-toggle="password" aria-label="Afficher le nouveau mot de passe" aria-pressed="false" class="absolute inset-y-0 right-0 flex items-center rounded-r-xl px-3 text-slate-500 transition hover:text-[#0F2347] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#2563EB]">
                        <svg data-eye-open class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.2-6 9.5-6 9.5 6 9.5 6-3.2 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                        <svg data-eye-closed class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A9.8 9.8 0 0 1 12 6c6.3 0 9.5 6 9.5 6a16 16 0 0 1-3 3.7M6.2 6.2C3.8 7.8 2.5 12 2.5 12s3.2 6 9.5 6c1 0 1.9-.2 2.7-.5M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                    </button>
                </div>
                <p id="password-help" class="mt-2 text-xs text-slate-500">8 caractères minimum</p>
                @error('password')<p id="password-error" role="alert" class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-[#0F2347]">Confirmer le mot de passe</label>
                <div class="relative mt-2">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="10" width="14" height="11" rx="2"/><path stroke-linecap="round" d="M8 10V7a4 4 0 1 1 8 0v3M12 14v3"/></svg>
                    </span>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" aria-describedby="password-match @error('password_confirmation') password-confirmation-error @enderror" @error('password_confirmation') aria-invalid="true" @enderror class="block min-h-12 w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-12 text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-[#2563EB] focus:outline-none focus:ring-2 focus:ring-[#2563EB]/20">
                    <button type="button" data-password-toggle="password_confirmation" aria-label="Afficher la confirmation du mot de passe" aria-pressed="false" class="absolute inset-y-0 right-0 flex items-center rounded-r-xl px-3 text-slate-500 transition hover:text-[#0F2347] focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#2563EB]">
                        <svg data-eye-open class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.2-6 9.5-6 9.5 6 9.5 6-3.2 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                        <svg data-eye-closed class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.6 6.2A9.8 9.8 0 0 1 12 6c6.3 0 9.5 6 9.5 6a16 16 0 0 1-3 3.7M6.2 6.2C3.8 7.8 2.5 12 2.5 12s3.2 6 9.5 6c1 0 1.9-.2 2.7-.5M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                    </button>
                </div>
                <p id="password-match" class="mt-2 min-h-4 text-xs text-slate-500" aria-live="polite"></p>
                @error('password_confirmation')<p id="password-confirmation-error" role="alert" class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full rounded-xl bg-[#2563EB] px-4 py-3.5 font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#2563EB] focus-visible:ring-offset-2">Activer mon compte</button>
        </form>
        <script>
            (() => {
                const password = document.getElementById('password');
                const confirmation = document.getElementById('password_confirmation');
                const matchMessage = document.getElementById('password-match');

                document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const input = document.getElementById(button.dataset.passwordToggle);
                        const visible = input.type === 'password';
                        input.type = visible ? 'text' : 'password';
                        button.setAttribute('aria-pressed', String(visible));
                        button.setAttribute('aria-label', visible
                            ? (input.id === 'password' ? 'Masquer le nouveau mot de passe' : 'Masquer la confirmation du mot de passe')
                            : (input.id === 'password' ? 'Afficher le nouveau mot de passe' : 'Afficher la confirmation du mot de passe'));
                        button.querySelector('[data-eye-open]').classList.toggle('hidden', visible);
                        button.querySelector('[data-eye-closed]').classList.toggle('hidden', !visible);
                    });
                });

                const updateMatchMessage = () => {
                    if (!confirmation.value) {
                        matchMessage.textContent = '';
                        matchMessage.className = 'mt-2 min-h-4 text-xs text-slate-500';
                    } else if (password.value === confirmation.value) {
                        matchMessage.textContent = 'Les mots de passe correspondent.';
                        matchMessage.className = 'mt-2 min-h-4 text-xs text-emerald-700';
                    } else {
                        matchMessage.textContent = 'Les mots de passe ne correspondent pas encore.';
                        matchMessage.className = 'mt-2 min-h-4 text-xs text-amber-700';
                    }
                };

                password.addEventListener('input', updateMatchMessage);
                confirmation.addEventListener('input', updateMatchMessage);
            })();
        </script>
    @else
        @php
            [$title, $message] = match ($state) {
                'expired' => ['Invitation expirée', 'Ce lien a dépassé sa durée de validité de 48 heures. Contactez la personne qui vous a invité afin qu’elle vous envoie un nouveau lien.'],
                'used' => ['Invitation déjà utilisée', 'Ce lien a déjà servi ou ce compte a déjà été activé. Vous pouvez vous connecter à votre espace.'],
                'revoked' => ['Invitation révoquée', 'Cet accès a été désactivé ou cette invitation remplacée. Contactez la personne qui vous a invité.'],
                'failed' => ['Invitation indisponible', 'L’envoi de cette invitation n’a pas abouti. Contactez la personne qui vous a invité afin qu’elle renvoie le lien.'],
                'unavailable' => ['Invitation en préparation', 'Cette invitation n’est pas encore disponible. Réessayez dans un instant ou contactez la personne qui vous a invité.'],
                default => ['Lien invalide', 'Ce lien ne correspond à aucune invitation active. Contactez la personne qui vous a invité.'],
            };
        @endphp
        <h1 class="mt-6 text-2xl font-bold text-[#0F2347]">{{ $title }}</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $message }}</p>
        <a href="{{ route('login') }}" class="mt-6 inline-block rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white">Aller à la connexion</a>
    @endif
</x-layouts.guest>
