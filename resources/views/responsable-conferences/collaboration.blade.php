
<x-responsable-conferences.layout :user="auth()->user()">

<div class="space-y-6">

    <header>
        <a href="{{ route('responsable-conferences.supports.index') }}"
           class="text-sm font-bold text-blue-600 hover:underline">
            ← Retour aux supports
        </a>

        <h1 class="mt-4 text-3xl font-black text-[#0F2347]">
            Suivi de la présentation
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Consultez le support, demandez des modifications
            et communiquez avec l'exposant.
        </p>
    </header>

    @include('conferences.partials.collaboration-card')

</div>

</x-responsable-conferences.layout>
