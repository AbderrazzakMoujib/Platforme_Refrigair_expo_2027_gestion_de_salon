
<x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">

    <div class="mb-8">
        <p class="text-sm font-semibold text-blue-600">
            Espace exposant
        </p>

        <h1 class="mt-2 text-3xl font-bold text-slate-900">
            Options du stand
        </h1>

        <p class="mt-2 text-sm text-slate-500">
            Sélectionnez les équipements supplémentaires
            nécessaires pour votre stand.
        </p>
    </div>

    @if (session('status'))
        <div role="status"
             class="mb-6 rounded-xl border border-emerald-200
                    bg-emerald-50 p-4 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert"
             class="mb-6 rounded-xl border border-red-200
                    bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('exhibitor.stand-options.store') }}"
          id="stand-options-form">

        @csrf

        <div class="grid gap-6 lg:grid-cols-3">

            <section class="space-y-4 lg:col-span-2">

                <div class="rounded-2xl bg-white p-5 shadow-sm
                            ring-1 ring-slate-200">
                    <h2 class="text-lg font-bold text-slate-900">
                        Catalogue des équipements
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Choisissez les quantités souhaitées.
                    </p>
                </div>

                @forelse ($equipment as $item)
                    <article class="flex flex-col gap-5 rounded-2xl
                                    bg-white p-5 shadow-sm ring-1
                                    ring-slate-200 sm:flex-row">

                        <div class="flex h-28 w-full shrink-0 items-center
                                    justify-center overflow-hidden
                                    rounded-xl bg-slate-100 sm:w-32">

                            @if ($item->image_path)
                                <img
                                    src="{{ asset('storage/' . $item->image_path) }}"
                                    alt="{{ $item->name }}"
                                    class="h-full w-full object-contain">
                            @else
                                <span class="text-3xl text-slate-400">
                                    ▣
                                </span>
                            @endif

                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase
                                      tracking-wide text-blue-600">
                                {{ $item->category }}
                            </p>

                            <h3 class="mt-1 text-lg font-bold text-slate-900">
                                {{ $item->name }}
                            </h3>

                            <p class="mt-2 text-sm text-slate-500">
                                {{ $item->description ?: 'Équipement pour votre stand.' }}
                            </p>

                            <div class="mt-4 flex flex-wrap items-end
                                        justify-between gap-4">

                                <div>
                                    <p class="text-xl font-bold text-slate-900">
                                        {{ number_format($item->price, 2, ',', ' ') }}
                                        DH
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Disponible :
                                        {{ $item->quantity_available }}
                                    </p>
                                </div>

                                <div>
                                    <label for="quantity-{{ $item->id }}"
                                           class="block text-xs font-semibold
                                                  text-slate-600">
                                        Quantité
                                    </label>

                                    <input
                                        id="quantity-{{ $item->id }}"
                                        type="number"
                                        name="items[{{ $item->id }}]"
                                        min="0"
                                        max="{{ $item->quantity_available }}"
                                        value="{{ old('items.' . $item->id, 0) }}"
                                        data-price="{{ $item->price }}"
                                        class="equipment-quantity mt-2 w-24
                                               rounded-xl border border-slate-300
                                               px-3 py-2 text-center text-sm
                                               focus:border-blue-500
                                               focus:outline-none"
                                        {{ $item->quantity_available < 1 ? 'disabled' : '' }}
                                    >
                                </div>

                            </div>
                        </div>

                    </article>
                @empty
                    <div class="rounded-2xl bg-white p-10 text-center
                                shadow-sm ring-1 ring-slate-200">
                        <h3 class="font-bold text-slate-900">
                            Aucun équipement disponible
                        </h3>
                        <p class="mt-2 text-sm text-slate-500">
                            Le catalogue sera disponible prochainement.
                        </p>
                    </div>
                @endforelse

            </section>

            <aside class="lg:col-span-1">
                <div class="sticky top-24 rounded-2xl bg-white p-6
                            shadow-sm ring-1 ring-slate-200">

                    <h2 class="text-lg font-bold text-slate-900">
                        Récapitulatif
                    </h2>

                    <div class="mt-6 flex justify-between text-sm">
                        <span class="text-slate-500">
                            Articles sélectionnés
                        </span>
                        <strong id="selected-count">0</strong>
                    </div>

                    <div class="mt-5 border-t border-slate-200 pt-5">
                        <p class="text-sm text-slate-500">
                            Total estimé
                        </p>

                        <p id="estimated-total"
                           aria-live="polite"
                           class="mt-2 text-3xl font-bold text-blue-700">
                            0,00 DH
                        </p>
                    </div>

                    <div class="mt-6">
                        <label for="notes"
                               class="text-sm font-semibold text-slate-700">
                            Commentaire (facultatif)
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            rows="4"
                            maxlength="2000"
                            placeholder="Précisez vos besoins..."
                            class="mt-2 w-full rounded-xl border
                                   border-slate-300 p-3 text-sm
                                   focus:border-blue-500 focus:outline-none"
                        >{{ old('notes') }}</textarea>
                    </div>

                    <button
                        type="submit"
                        id="submit-request"
                        disabled
                        class="mt-6 w-full rounded-xl bg-blue-600
                               px-5 py-3 text-sm font-bold text-white
                               transition hover:bg-blue-700
                               disabled:cursor-not-allowed
                               disabled:opacity-50">
                        Envoyer ma demande
                    </button>

                    <p class="mt-4 text-xs leading-5 text-slate-500">
                        Votre demande sera soumise à validation.
                        Les prix et disponibilités seront
                        vérifiés lors de l'envoi.
                    </p>
                </div>
            </aside>

        </div>
    </form>

    <section id="mes-demandes" class="mt-10 scroll-mt-24">
        <h2 class="mb-5 text-xl font-bold text-slate-900">
            Mes demandes
        </h2>

        <div class="space-y-4">
            @forelse ($requests as $order)
                @php
                    $statusLabel = match ($order->status) {
                        'approved' => 'Approuvée',
                        'rejected' => 'Refusée',
                        default => 'En attente',
                    };

                    $statusClass = match ($order->status) {
                        'approved' => 'bg-emerald-50 text-emerald-700',
                        'rejected' => 'bg-red-50 text-red-700',
                        default => 'bg-amber-50 text-amber-700',
                    };
                @endphp

                <article class="rounded-2xl bg-white p-5 shadow-sm
                                ring-1 ring-slate-200">

                    <div class="flex flex-wrap items-center
                                justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-slate-900">
                                Demande #{{ $order->id }}
                            </h3>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        <span class="rounded-full px-3 py-1 text-xs
                                     font-bold {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <ul class="mt-5 divide-y divide-slate-100">
                        @foreach ($order->items as $item)
                            <li class="flex justify-between gap-3 py-3 text-sm">
                                <span class="text-slate-600">
                                    {{ $item->equipment_name }}
                                    × {{ $item->quantity }}
                                </span>
                                <strong class="text-slate-900">
                                    {{ number_format($item->subtotal, 2, ',', ' ') }}
                                    DH
                                </strong>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4 flex justify-between border-t
                                border-slate-200 pt-4">
                        <span class="font-semibold">Total</span>
                        <strong class="text-blue-700">
                            {{ number_format($order->total_amount, 2, ',', ' ') }}
                            DH
                        </strong>
                    </div>

                    @if ($order->status === 'rejected' && $order->rejection_reason)
                        <p class="mt-4 rounded-xl bg-red-50 p-3
                                  text-sm text-red-700">
                            Motif : {{ $order->rejection_reason }}
                        </p>
                    @endif

                </article>
            @empty
                <div class="rounded-2xl bg-white p-8 text-center
                            text-sm text-slate-500 ring-1 ring-slate-200">
                    Vous n'avez pas encore envoyé de demande.
                </div>
            @endforelse
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputs = document.querySelectorAll('.equipment-quantity');
            const count = document.getElementById('selected-count');
            const total = document.getElementById('estimated-total');
            const button = document.getElementById('submit-request');

            const formatter = new Intl.NumberFormat('fr-MA', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            function updateSummary() {
                let articles = 0;
                let amount = 0;

                inputs.forEach(input => {
                    const quantity = Math.max(0, Number(input.value) || 0);
                    const price = Number(input.dataset.price) || 0;

                    if (!input.disabled && quantity > 0) {
                        articles += quantity;
                        amount += quantity * price;
                    }
                });

                count.textContent = articles;
                total.textContent = `${formatter.format(amount)} DH`;
                button.disabled = articles === 0;
            }

            inputs.forEach(input => {
                input.addEventListener('input', updateSummary);
            });

            updateSummary();
        });
    </script>

</x-exhibitor.layout>
