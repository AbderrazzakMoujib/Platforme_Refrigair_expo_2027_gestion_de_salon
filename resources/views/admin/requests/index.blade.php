<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    <div>
        <p class="text-sm font-semibold text-blue-600">
            Administration / Équipements
        </p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">
            Gestion des demandes
        </h1>
        <p class="mt-2 text-sm text-slate-500">
            Consultez et traitez les demandes d'équipements des exposants.
        </p>
    </div>

    @if (session('status'))
        <div role="status"
             class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card label="Total demandes"
            :value="$totalRequests" icon="inbox"
            description="Demandes enregistrées." />
        <x-admin.stat-card label="En attente"
            :value="$pendingCount" icon="inbox"
            description="Demandes à traiter." />
        <x-admin.stat-card label="Approuvées"
            :value="$approvedCount" icon="box"
            description="Demandes validées." />
        <x-admin.stat-card label="Refusées"
            :value="$rejectedCount" icon="inbox"
            description="Demandes refusées." />
    </section>

    <section class="mt-7 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <form method="GET"
              action="{{ route('admin.demandes.index') }}"
              class="grid gap-4 sm:grid-cols-[1fr_220px_auto] sm:items-end">

            <div>
                <label for="search"
                       class="mb-2 block text-sm font-semibold text-slate-700">
                    Recherche
                </label>
                <input id="search" name="search" type="search"
                       value="{{ request('search') }}"
                       placeholder="Entreprise, stand ou numéro..."
                       class="w-full rounded-xl border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="status"
                       class="mb-2 block text-sm font-semibold text-slate-700">
                    Statut
                </label>
                <select id="status" name="status"
                        class="w-full rounded-xl border-slate-300 text-sm">
                    <option value="">Tous les statuts</option>
                    <option value="pending"
                        @selected(request('status') === 'pending')>
                        En attente
                    </option>
                    <option value="approved"
                        @selected(request('status') === 'approved')>
                        Approuvée
                    </option>
                    <option value="rejected"
                        @selected(request('status') === 'rejected')>
                        Refusée
                    </option>
                </select>
            </div>

            <button class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                Filtrer
            </button>
        </form>

        <a href="{{ route('admin.demandes.index') }}"
           class="mt-3 inline-block text-xs font-semibold text-blue-600 hover:underline">
            Réinitialiser les filtres
        </a>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-bold">Liste des demandes</h2>
            <span class="text-sm text-slate-500">
                {{ $requests->total() }} résultat(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Demande</th>
                        <th class="px-5 py-4">Exposant</th>
                        <th class="px-5 py-4">Articles</th>
                        <th class="px-5 py-4">Montant</th>
                        <th class="px-5 py-4">Statut</th>
                        <th class="px-5 py-4">Date</th>
                        <th class="px-5 py-4">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($requests as $order)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 font-bold text-slate-800">
                                #{{ $order->id }}
                            </td>

                            <td class="px-5 py-4">
                                <p class="font-semibold">
                                    {{ $order->exhibitor?->company_name ?? '—' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Stand {{ $order->exhibitor?->stand_number ?: '—' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $order->exhibitor?->commercial?->name ?? '—' }}
                                </p>
                            </td>

                            <td class="px-5 py-4">
                                {{ $order->items_count }}
                            </td>

                            <td class="px-5 py-4 font-bold">
                                {{ number_format((float) $order->total_amount, 2, ',', ' ') }} DH
                            </td>

                            <td class="px-5 py-4">
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-semibold',
                                    'bg-amber-50 text-amber-700' => $order->status === 'pending',
                                    'bg-emerald-50 text-emerald-700' => $order->status === 'approved',
                                    'bg-red-50 text-red-700' => $order->status === 'rejected',
                                ])>
                                    {{ match ($order->status) {
                                        'approved' => 'Approuvée',
                                        'rejected' => 'Refusée',
                                        default => 'En attente',
                                    } }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-slate-500">
                                {{ $order->created_at?->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-5 py-4">
                                <a href="{{ route('admin.demandes.show', $order) }}"
                                   class="font-semibold text-blue-700 hover:underline">
                                    Voir détails
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"
                                class="px-5 py-12 text-center text-slate-500">
                                Aucune demande disponible pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($requests->hasPages())
            <div class="border-t border-slate-100 px-6 py-5">
                {{ $requests->links() }}
            </div>
        @endif
    </section>

</x-admin.layout>
