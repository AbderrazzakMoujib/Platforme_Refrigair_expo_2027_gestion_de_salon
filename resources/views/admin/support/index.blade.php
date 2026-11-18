
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-extrabold text-[#0F2347]">
            Suivi de l'assistance
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Supervision des demandes prises en charge par les commerciaux.
        </p>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-blue-50 font-bold text-[#0F2347]">
                    <tr>
                        <th class="px-5 py-4">N°</th>
                        <th class="px-5 py-4">Exposant</th>
                        <th class="px-5 py-4">Commercial</th>
                        <th class="px-5 py-4">Sujet</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td class="px-5 py-4 font-bold">
                                #{{ $ticket->id }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $ticket->exhibitor?->company_name }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $ticket->commercial?->name }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $ticket->subject }}
                            </td>

                            <td class="px-5 py-4 font-semibold">
                                {{ $ticket->status_label }}
                            </td>

                            <td class="px-5 py-4">
                                <a href="{{ route('admin.support.show', $ticket) }}"
                                   class="font-bold text-blue-700 hover:underline">
                                    Voir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-slate-500">
                                Aucune demande d'assistance.
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

</x-admin.layout>
