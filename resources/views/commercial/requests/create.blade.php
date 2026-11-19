@php
    $money = fn ($value) => number_format((float) $value, 2, ',', ' ').' DH';
    $categories = $equipment->pluck('category')->filter()->unique()->values();
    $oldItems = old('items', []);
@endphp

<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercialInitials">
    <div class="flex items-start gap-3">
        <a href="{{ route('commercial.requests.index') }}" class="mt-1 grid size-10 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-[#2563EB] hover:bg-slate-50" aria-label="Retour aux demandes">←</a>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Nouvelle demande d'équipements</h1>
            <p class="mt-1 text-sm text-slate-500">Créer une demande d'équipements pour un de vos exposants. Elle sera validée par l'administration.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800" role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if ($exhibitors->isEmpty())
        <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/70">
            <x-admin.empty-state icon="users" title="Aucun exposant" description="Ajoutez d'abord un exposant pour pouvoir créer une demande d'équipements." />
            <div class="mt-4 text-center"><a href="{{ route('commercial.exhibitors.create') }}" class="inline-flex rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Ajouter un exposant</a></div>
        </div>
    @else
        <form method="POST" action="{{ route('commercial.requests.store') }}" id="request-form" class="mt-6 grid gap-6 xl:grid-cols-3">
            @csrf
            <div class="min-w-0 space-y-6 xl:col-span-2">
                {{-- 1. Exposant --}}
                <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6">
                    <h2 class="flex items-center gap-2 text-base font-bold text-slate-900"><span class="grid size-6 place-items-center rounded-full bg-[#2563EB] text-xs text-white">1</span> Exposant concerné</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div class="sm:col-span-1">
                            <label for="exhibitor_id" class="text-sm font-medium text-slate-700">Exposant <span class="text-red-600">*</span></label>
                            <select id="exhibitor_id" name="exhibitor_id" required class="mt-1 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Sélectionner…</option>
                                @foreach ($exhibitors as $exhibitor)
                                    <option value="{{ $exhibitor->id }}"
                                            data-email="{{ $exhibitor->email }}"
                                            data-phone="{{ $exhibitor->phone }}"
                                            data-stand="{{ $exhibitor->stand_number }}"
                                            data-area="{{ $exhibitor->area_sqm !== null ? rtrim(rtrim(number_format((float) $exhibitor->area_sqm, 2, ',', ' '), '0'), ',') : '' }}"
                                            @selected($selectedExhibitor?->id === $exhibitor->id)>{{ $exhibitor->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-700">Email</p>
                            <p class="mt-1 truncate rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600" data-field="email">{{ $selectedExhibitor?->email ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-700">Téléphone</p>
                            <p class="mt-1 truncate rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600" data-field="phone">{{ $selectedExhibitor?->phone ?? '—' }}</p>
                        </div>
                    </div>
                </section>

                {{-- 2. Stand --}}
                <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6">
                    <h2 class="flex items-center gap-2 text-base font-bold text-slate-900"><span class="grid size-6 place-items-center rounded-full bg-[#2563EB] text-xs text-white">2</span> Informations du stand</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-sm font-medium text-slate-700">Numéro du stand</p>
                            <p class="mt-1 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600" data-field="stand">{{ $selectedExhibitor?->stand_number ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-700">Surface (m²)</p>
                            <p class="mt-1 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600" data-field="area">{{ $selectedExhibitor?->area_sqm ? rtrim(rtrim(number_format((float) $selectedExhibitor->area_sqm, 2, ',', ' '), '0'), ',') : '—' }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-400">Pour modifier le stand, passez par la fiche de l'exposant.</p>
                </section>

                {{-- 3. Équipements --}}
                <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6">
                    <h2 class="flex items-center gap-2 text-base font-bold text-slate-900"><span class="grid size-6 place-items-center rounded-full bg-[#2563EB] text-xs text-white">3</span> Sélectionner les équipements</h2>

                    @if ($equipment->isEmpty())
                        <x-admin.empty-state class="mt-4" icon="box" title="Aucun équipement disponible" description="Le catalogue d'équipements est vide pour le moment." />
                    @else
                        @if ($categories->count() > 1)
                            <div class="mt-4 flex flex-wrap gap-2" role="group" aria-label="Filtrer par catégorie">
                                <button type="button" data-category="" class="category-filter rounded-full bg-[#2563EB] px-3 py-1.5 text-xs font-semibold text-white">Tous</button>
                                @foreach ($categories as $category)
                                    <button type="button" data-category="{{ $category }}" class="category-filter rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-200">{{ $category }}</button>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($equipment as $item)
                                @php $max = min(100, (int) $item->quantity_available); @endphp
                                <label data-equipment-card data-category="{{ $item->category }}" @class([
                                    'flex flex-col rounded-xl border p-3 transition has-[input:focus]:ring-2 has-[input:focus]:ring-blue-500',
                                    'border-slate-200' => $max > 0,
                                    'border-slate-100 opacity-60' => $max === 0,
                                ])>
                                    <span class="grid h-28 place-items-center overflow-hidden rounded-lg bg-slate-50">
                                        @if ($item->image_path)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="" class="h-full w-full object-contain">
                                        @else
                                            <x-admin.icon name="box" class="size-8 text-slate-300" />
                                        @endif
                                    </span>
                                    <span class="mt-3 text-sm font-semibold text-slate-800">{{ $item->name }}</span>
                                    <span class="text-xs text-slate-500">{{ $item->category }}</span>
                                    <span class="mt-1 text-sm font-bold text-[#2563EB]">{{ $money($item->price) }} <span class="text-xs font-medium text-slate-400">/ unité</span></span>
                                    <span class="mt-0.5 text-xs {{ $max > 0 ? 'text-slate-500' : 'text-red-600' }}">{{ $max > 0 ? $item->quantity_available.' disponible(s)' : 'Rupture de stock' }}</span>
                                    <span class="mt-3 flex items-center justify-between gap-2">
                                        <span class="text-xs font-medium text-slate-600">Quantité</span>
                                        <input type="number" name="items[{{ $item->id }}]" min="0" max="{{ $max }}" step="1"
                                               value="{{ (int) ($oldItems[$item->id] ?? 0) }}"
                                               data-name="{{ $item->name }}" data-price="{{ (float) $item->price }}"
                                               @disabled($max === 0)
                                               class="equipment-qty w-20 rounded-lg border-slate-300 py-1 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                               aria-label="Quantité de {{ $item->name }}">
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </section>

                {{-- 4. Commentaire --}}
                <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6">
                    <label for="notes" class="flex items-center gap-2 text-base font-bold text-slate-900"><span class="grid size-6 place-items-center rounded-full bg-[#2563EB] text-xs text-white">4</span> Commentaire <span class="text-sm font-normal text-slate-400">(facultatif)</span></label>
                    <textarea id="notes" name="notes" rows="3" maxlength="2000" placeholder="Précision sur la demande (emplacement, date d'installation, besoins spécifiques…)" class="mt-4 w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                </section>
            </div>

            {{-- Récapitulatif --}}
            <aside class="xl:sticky xl:top-24 xl:self-start">
                <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200/70 sm:p-6">
                    <h2 class="text-base font-bold text-slate-900">Récapitulatif de la demande</h2>
                    <ul id="summary-items" class="mt-4 space-y-2 text-sm">
                        <li class="text-slate-500" data-empty>Aucun équipement sélectionné.</li>
                    </ul>
                    <div class="mt-5 flex items-center justify-between rounded-xl bg-blue-50 px-4 py-3">
                        <span class="text-sm font-semibold text-slate-700">Total estimé</span>
                        <span id="summary-total" class="text-xl font-bold text-[#2563EB]">0,00 DH</span>
                    </div>
                    <p class="mt-2 text-xs text-slate-400">Le montant final est calculé à l'envoi selon les prix du catalogue.</p>
                    <button type="submit" class="mt-5 w-full rounded-xl bg-[#2563EB] px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60" @disabled($equipment->isEmpty())>Envoyer la demande</button>
                    <a href="{{ route('commercial.requests.index') }}" class="mt-2 block w-full rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Annuler</a>
                </section>
            </aside>
        </form>

        <script>
            (() => {
                const form = document.getElementById('request-form');
                const select = document.getElementById('exhibitor_id');
                const list = document.getElementById('summary-items');
                const total = document.getElementById('summary-total');
                const money = (value) => value.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' DH';

                const fillExhibitor = () => {
                    const option = select.selectedOptions[0];
                    ['email', 'phone', 'stand', 'area'].forEach((field) => {
                        const target = form.querySelector(`[data-field="${field}"]`);
                        if (target) target.textContent = (option && option.dataset[field]) || '—';
                    });
                };

                const refreshSummary = () => {
                    let sum = 0;
                    list.replaceChildren();
                    form.querySelectorAll('.equipment-qty').forEach((input) => {
                        const qty = Math.max(0, parseInt(input.value, 10) || 0);
                        if (!qty) return;
                        const subtotal = qty * parseFloat(input.dataset.price);
                        sum += subtotal;
                        const li = document.createElement('li');
                        li.className = 'flex justify-between gap-3';
                        const label = document.createElement('span');
                        label.className = 'text-slate-700';
                        label.textContent = `${input.dataset.name} × ${qty}`;
                        const amount = document.createElement('span');
                        amount.className = 'shrink-0 font-semibold text-slate-800';
                        amount.textContent = money(subtotal);
                        li.append(label, amount);
                        list.append(li);
                        input.closest('[data-equipment-card]')?.classList.add('border-blue-400', 'bg-blue-50/40');
                    });
                    form.querySelectorAll('.equipment-qty').forEach((input) => {
                        if (!(parseInt(input.value, 10) > 0)) input.closest('[data-equipment-card]')?.classList.remove('border-blue-400', 'bg-blue-50/40');
                    });
                    if (!list.children.length) {
                        const li = document.createElement('li');
                        li.className = 'text-slate-500';
                        li.textContent = 'Aucun équipement sélectionné.';
                        list.append(li);
                    }
                    total.textContent = money(sum);
                };

                document.querySelectorAll('.category-filter').forEach((button) => {
                    button.addEventListener('click', () => {
                        const category = button.dataset.category;
                        document.querySelectorAll('.category-filter').forEach((b) => {
                            const active = b === button;
                            b.classList.toggle('bg-[#2563EB]', active);
                            b.classList.toggle('text-white', active);
                            b.classList.toggle('bg-slate-100', !active);
                            b.classList.toggle('text-slate-600', !active);
                        });
                        document.querySelectorAll('[data-equipment-card]').forEach((card) => {
                            card.hidden = category !== '' && card.dataset.category !== category;
                        });
                    });
                });

                select.addEventListener('change', fillExhibitor);
                form.addEventListener('input', (event) => {
                    if (event.target.classList.contains('equipment-qty')) refreshSummary();
                });
                refreshSummary();
            })();
        </script>
    @endif
</x-commercial.layout>
