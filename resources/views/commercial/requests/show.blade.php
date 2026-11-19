@php
    $money = fn ($value) => number_format((float) $value, 2, ',', ' ').' DH';

    $statusMeta = [
        'pending' => ['label' => 'En attente', 'pill' => 'bg-amber-100 text-amber-700', 'text' => "Cette demande est en attente de validation par l'administration."],
        'approved' => ['label' => 'Approuvée', 'pill' => 'bg-emerald-100 text-emerald-700', 'text' => "Cette demande a été approuvée par l'administration."],
        'rejected' => ['label' => 'Refusée', 'pill' => 'bg-red-100 text-red-700', 'text' => "Cette demande a été refusée par l'administration."],
    ];
    $meta = $statusMeta[$order->status] ?? $statusMeta['pending'];
    $exhibitor = $order->exhibitor;
@endphp

<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercialInitials">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-3">
            <a href="{{ route('commercial.requests.index') }}" class="mt-1 grid size-10 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-[#2563EB] hover:bg-slate-50" aria-label="Retour aux demandes">←</a>
            <div>
                <p class="text-sm font-semibold text-[#2563EB]">Demande #{{ $order->id }}</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $exhibitor->company_name }}</h1>
                <p class="mt-1 text-sm text-slate-500">Créée le {{ $order->created_at?->format('d/m/Y à H:i') }}</p>
            </div>
        </div>
        <span class="self-start rounded-full px-3 py-1.5 text-sm font-semibold {{ $meta['pill'] }}">{{ $meta['label'] }}</span>
    </div>

    @if (session('status'))
        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">{{ session('status') }}</div>
    @endif

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <section class="min-w-0 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6 xl:col-span-2">
            <h2 class="text-base font-bold text-slate-900">Équipements demandés</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[560px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-500">
                        <tr>
                            <th class="px-3 py-3">Équipement</th>
                            <th class="px-3 py-3 text-right">Prix unitaire</th>
                            <th class="px-3 py-3 text-right">Quantité</th>
                            <th class="px-3 py-3 text-right">Sous-total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-10 shrink-0 place-items-center overflow-hidden rounded-lg bg-slate-50">
                                            @if ($item->equipment?->image_path)
                                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->equipment->image_path) }}" alt="" class="h-full w-full object-contain">
                                            @else
                                                <x-admin.icon name="box" class="size-5 text-slate-300" />
                                            @endif
                                        </span>
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $item->equipment_name }}</p>
                                            @if ($item->equipment?->category)
                                                <p class="text-xs text-slate-500">{{ $item->equipment->category }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-right text-slate-600">{{ $money($item->unit_price) }}</td>
                                <td class="px-3 py-3 text-right font-semibold text-slate-800">{{ $item->quantity }}</td>
                                <td class="px-3 py-3 text-right text-slate-800">{{ $money($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-200">
                            <td colspan="3" class="px-3 py-3 text-right font-semibold text-slate-700">Total</td>
                            <td class="px-3 py-3 text-right text-lg font-bold text-[#2563EB]">{{ $money($order->total_amount) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($order->notes)
                <h3 class="mt-6 text-sm font-semibold text-slate-700">Commentaire</h3>
                <p class="mt-2 whitespace-pre-line rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600">{{ $order->notes }}</p>
            @endif
        </section>

        <aside class="space-y-6">
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                <h2 class="text-base font-bold text-slate-900">Suivi</h2>
                <p class="mt-3 text-sm text-slate-600">{{ $meta['text'] }}</p>
                @if ($order->reviewed_at)
                    <p class="mt-2 text-xs text-slate-500">Le {{ $order->reviewed_at->format('d/m/Y à H:i') }}@if ($order->reviewer) par {{ $order->reviewer->name }}@endif</p>
                @endif
                @if ($order->status === 'rejected' && $order->rejection_reason)
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-red-700">Motif du refus</p>
                        <p class="mt-1 whitespace-pre-line text-sm text-red-800">{{ $order->rejection_reason }}</p>
                    </div>
                @endif
            </article>

            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
                <h2 class="text-base font-bold text-slate-900">Exposant</h2>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Contact</dt><dd class="text-right font-medium text-slate-800">{{ $exhibitor->contact_name }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Email</dt><dd class="truncate text-right font-medium text-slate-800">{{ $exhibitor->email }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Téléphone</dt><dd class="text-right font-medium text-slate-800">{{ $exhibitor->phone ?: '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Stand</dt><dd class="text-right font-medium text-slate-800">{{ $exhibitor->stand_number ?: '—' }}</dd></div>
                </dl>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('commercial.exhibitors.show', $exhibitor) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Fiche exposant</a>
                    <a href="{{ route('commercial.requests.create', ['exhibitor_id' => $exhibitor->id]) }}" class="rounded-lg bg-[#2563EB] px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">Nouvelle demande</a>
                </div>
            </article>
        </aside>
    </div>
</x-commercial.layout>
