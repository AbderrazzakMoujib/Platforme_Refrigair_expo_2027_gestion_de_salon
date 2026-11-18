
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

<div class="mx-auto max-w-4xl space-y-6">

    <a href="{{ route('admin.support.index') }}"
       class="text-sm font-semibold text-blue-700">
        ← Retour aux demandes
    </a>

    <header class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

        <h1 class="text-2xl font-extrabold text-[#0F2347]">
            Demande #{{ $ticket->id }} — {{ $ticket->subject }}
        </h1>

        <p class="mt-3 text-sm text-slate-600">
            Exposant : {{ $ticket->exhibitor?->company_name }}
        </p>

        <p class="mt-1 text-sm text-slate-600">
            Commercial : {{ $ticket->commercial?->name }}
        </p>

        <p class="mt-2 text-sm font-bold text-blue-700">
            {{ $ticket->status_label }}
        </p>
    </header>

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="font-bold text-[#0F2347]">
            Historique de la conversation
        </h2>

        <div class="mt-5 space-y-4">
            @foreach ($ticket->messages as $message)
                <article class="rounded-xl bg-[#F5F7FB] p-4">

                    <div class="flex flex-wrap justify-between gap-3">
                        <strong class="text-sm text-[#0F2347]">
                            {{ $message->user?->name ?? 'Utilisateur' }}
                        </strong>

                        <span class="text-xs text-slate-500">
                            {{ $message->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>

                    <p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-slate-700">{{ $message->message }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <p class="text-sm text-slate-500">
        Consultation uniquement : les réponses et changements de statut
        sont réservés au commercial responsable.
    </p>
</div>

</x-admin.layout>
