
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    <a href="{{ route('admin.demandes.index') }}"
       class="text-sm font-semibold text-blue-600 hover:underline">
        ← Retour aux demandes
    </a>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-blue-600">
                Détail de la demande
            </p>
            <h1 class="mt-2 text-3xl font-bold">
                Demande #{{ $equipmentRequest->id }}
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Envoyée le {{ $equipmentRequest->created_at?->format('d/m/Y à H:i') }}
            </p>
        </div>

        <span @class([
            'rounded-xl px-4 py-2 text-sm font-semibold',
            'bg-amber-50 text-amber-700' => $equipmentRequest->status === 'pending',
            'bg-emerald-50 text-emerald-700' => $equipmentRequest->status === 'approved',
            'bg-red-50 text-red-700' => $equipmentRequest->status === 'rejected',
        ])>
            {{ match ($equipmentRequest->status) {
                'approved' => 'Approuvée',
                'rejected' => 'Refusée',
                default => 'En attente',
            } }}
        </span>
    </div>

    @if (session('status'))
        <div role="status"
             class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert"
             class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="mt-7 grid gap-6 lg:grid-cols-3">

        <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-2">

            <h2 class="text-lg font-bold">
                Équipements demandés
            </h2>

            <div class="mt-6 overflow-x-auto">
                <table class="w-full min-w-[480px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Équipement</th>
                            <th class="px-4 py-3">Qté</th>
                            <th class="px-4 py-3">Prix unitaire</th>
                            <th class="px-4 py-3">Sous-total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($equipmentRequest->items as $item)
                            <tr>
                                <td class="px-4 py-4 font-semibold">
                                    {{ $item->equipment_name }}
                                </td>
                                <td class="px-4 py-4">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-4 py-4">
                                    {{ number_format((float) $item->unit_price, 2, ',', ' ') }} DH
                                </td>
                                <td class="px-4 py-4 font-semibold">
                                    {{ number_format((float) $item->subtotal, 2, ',', ' ') }} DH
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                                    Aucun article.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 border-t border-slate-100 pt-5 text-right">
                <p class="text-sm text-slate-500">
                    Montant total
                </p>
                <p class="mt-2 text-3xl font-bold text-blue-700">
                    {{ number_format((float) $equipmentRequest->total_amount, 2, ',', ' ') }} DH
                </p>
            </div>

            @if ($equipmentRequest->notes)
                <div class="mt-6 rounded-xl bg-slate-50 p-4">
                    <p class="text-sm font-semibold">Commentaire de l'exposant</p>
                    <p class="mt-2 whitespace-pre-line text-sm text-slate-600">
                        {{ $equipmentRequest->notes }}
                    </p>
                </div>
            @endif

        </article>

        <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="text-lg font-bold">Informations exposant</h2>

            <dl class="mt-5 space-y-4 text-sm">
                <div>
                    <dt class="text-slate-500">Entreprise</dt>
                    <dd class="mt-1 font-semibold">
                        {{ $equipmentRequest->exhibitor?->company_name ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Contact</dt>
                    <dd class="mt-1 font-medium">
                        {{ $equipmentRequest->exhibitor?->contact_name ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Stand</dt>
                    <dd class="mt-1 font-semibold">
                        {{ $equipmentRequest->exhibitor?->stand_number ?: '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Commercial</dt>
                    <dd class="mt-1 font-medium">
                        {{ $equipmentRequest->exhibitor?->commercial?->name ?? '—' }}
                    </dd>
                </div>
            </dl>

            @if ($equipmentRequest->exhibitor)
                <a href="{{ route('admin.exposants.show', $equipmentRequest->exhibitor) }}"
                   class="mt-5 inline-block text-sm font-semibold text-blue-600 hover:underline">
                    Voir fiche exposant
                </a>
            @endif
        </article>
    </section>

    @if ($equipmentRequest->status === 'pending')
        <section class="mt-6 grid gap-6 lg:grid-cols-2">

            <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-emerald-200">
                <h2 class="text-lg font-bold">
                    Approuver la demande
                </h2>
                <p class="mt-2 text-sm text-slate-500">
                    Le stock sera déduit lors de la validation.
                </p>

                <form method="POST"
                      action="{{ route('admin.demandes.approve', $equipmentRequest) }}"
                      class="mt-5"
                      onsubmit="return confirm('Confirmer la validation de cette demande ?')">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            class="rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700">
                        Approuver la demande
                    </button>
                </form>
            </article>

            <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-red-200">
                <h2 class="text-lg font-bold">
                    Refuser la demande
                </h2>
                <p class="mt-2 text-sm text-slate-500">
                    Indiquez la raison du refus.
                </p>

                <form method="POST"
                      action="{{ route('admin.demandes.reject', $equipmentRequest) }}"
                      class="mt-5 space-y-4">
                    @csrf
                    @method('PATCH')

                    <label for="rejection_reason"
                           class="block text-sm font-semibold">
                        Motif du refus *
                    </label>

                    <textarea name="rejection_reason"
                              id="rejection_reason"
                              rows="3"
                              required
                              maxlength="2000"
                              class="w-full rounded-xl border-slate-300 text-sm focus:border-red-500 focus:ring-red-500"
                              placeholder="Ex : Équipement indisponible...">{{ old('rejection_reason') }}</textarea>

                    <button type="submit"
                            class="rounded-xl bg-red-600 px-6 py-3 text-sm font-semibold text-white hover:bg-red-700">
                        Refuser la demande
                    </button>
                </form>
            </article>
        </section>
    @else
        <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-bold">Décision de l'administration</h2>

            <p class="mt-3 text-sm text-slate-600">
                Traitée par {{ $equipmentRequest->reviewer?->name ?? '—' }}
                le {{ $equipmentRequest->reviewed_at?->format('d/m/Y à H:i') ?? '—' }}.
            </p>

            @if ($equipmentRequest->rejection_reason)
                <p class="mt-4 whitespace-pre-line rounded-xl bg-red-50 p-4 text-sm text-red-700">
                    {{ $equipmentRequest->rejection_reason }}
                </p>
            @endif
        </section>
    @endif

</x-admin.layout>
