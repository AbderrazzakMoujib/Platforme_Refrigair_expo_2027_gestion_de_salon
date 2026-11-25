<x-layouts.guest
    title="Accès refusé | REFRIGAIR EXPO 2027"
    :toast-messages="[[
        'type' => 'error',
        'title' => 'Accès refusé',
        'message' => 'Votre compte ne dispose pas des autorisations nécessaires pour cette page.',
    ]]"
>
    <div class="mt-8 text-center">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-blue-600">Erreur 403</p>
        <h1 class="mt-3 text-2xl font-extrabold text-[#0F2347]">Cette page est réservée à un autre espace</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">Retournez à votre espace ou connectez-vous avec un compte autorisé.</p>
        <a href="{{ route('login') }}" class="mt-6 inline-flex rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700">Aller à la connexion</a>
    </div>
</x-layouts.guest>
