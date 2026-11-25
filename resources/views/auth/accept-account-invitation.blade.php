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
                <label for="password" class="text-sm font-medium">Nouveau mot de passe (8 caractères minimum)</label>
                <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600">
            </div>
            <div>
                <label for="password_confirmation" class="text-sm font-medium">Confirmer le mot de passe</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600">
            </div>
            <button type="submit" class="w-full rounded-xl bg-[#2563EB] px-4 py-3 font-semibold text-white hover:bg-blue-700">Activer mon compte</button>
        </form>
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
