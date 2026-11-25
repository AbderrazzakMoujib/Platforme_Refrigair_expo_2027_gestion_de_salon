
<x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">

<div class="space-y-6">

    <header>
        <a href="{{ route('exhibitor.slots.index') }}"
           class="text-sm font-bold text-blue-600 hover:underline">
            ← Retour aux conférences
        </a>

        <h1 class="mt-4 text-3xl font-black text-[#0F2347]">
            Suivi de ma conférence
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Transmettez votre support et échangez directement
            avec le Responsable Conférences.
        </p>
    </header>

    @include('conferences.partials.collaboration-card')

</div>

</x-exhibitor.layout>
