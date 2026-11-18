
<x-commercial.layout
    :commercial="$commercial"
    :commercial-initials="$commercialInitials">

@php
    $colors = [
        'pending' => 'bg-amber-100 text-amber-700',
        'in_progress' => 'bg-blue-100 text-blue-700',
        'resolved' => 'bg-emerald-100 text-emerald-700',
    ];
@endphp

<div class="space-y-6">

    <header>
        <p class="text-sm font-semibold text-blue-600">
            Espace Commercial / Assistance
        </p>

        <h1 class="mt-2 text-3xl font-extrabold text-[#0F2347]">
            Assistance de mes exposants
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Consultez et traitez les demandes de vos clients.
        </p>
    </header>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-bold text-[#0F2347]">
                Demandes reçues ({{ $tickets->total() }})
            </h2>
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full text-left text-sm">

                <thead class="bg-blue-50 text-xs font-bold text-[#0F2347]">
                    <tr>
                        <th class="px-5 py-4">N°</th>
                        <th class="px-5 py-4">Entreprise</th>
                        <th class="px-5 py-4">Sujet</th>
                        <th class="px-5 py-4">Date</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($tickets as $ticket)
                        <tr class="hover:bg-slate-50">

                            <td class="px-5 py-4 font-bold">
                                #{{ $ticket->id }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $ticket->exhibitor?->company_name }}
                            </td>

                            <td class="px-5 py-4 font-semibold text-[#0F2347]">
                                {{ $ticket->subject }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-slate-500">
                                {{ $ticket->created_at->format('d/m/Y') }}
                            </td>

                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ $colors[$ticket->status] ?? $colors['pending'] }}">
                                    {{ $ticket->status_label }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('commercial.support.show', $ticket) }}"
                                   class="rounded-lg border border-blue-200 px-4 py-2 text-xs font-bold text-blue-700 hover:bg-blue-50">
                                    Consulter
                                </a>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"
                                class="px-6 py-14 text-center text-slate-500">
                                Aucune demande d'assistance reçue.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        @if ($tickets->hasPages())
            <div class="border-t border-slate-100 p-5">
                {{ $tickets->links() }}
            </div>
        @endif

    </section>

</div>

</x-commercial.layout>
