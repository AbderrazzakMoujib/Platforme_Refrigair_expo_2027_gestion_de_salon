<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercialInitials">
    <div class="mx-auto max-w-2xl">
        <p class="text-sm font-semibold text-blue-600">Badges / Vérification</p>
        <h1 class="mt-2 text-3xl font-bold">Vérification du badge</h1>

        <section class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div @class([
                'p-8 text-center',
                'bg-emerald-50' => $badge->is_active,
                'bg-red-50' => ! $badge->is_active,
            ])>
                <div @class([
                    'mx-auto grid size-16 place-items-center rounded-full text-3xl',
                    'bg-emerald-100 text-emerald-700' => $badge->is_active,
                    'bg-red-100 text-red-700' => ! $badge->is_active,
                ])>{{ $badge->is_active ? '✓' : '!' }}</div>
                <h2 @class([
                    'mt-5 text-2xl font-bold',
                    'text-emerald-800' => $badge->is_active,
                    'text-red-800' => ! $badge->is_active,
                ])>{{ $badge->is_active ? 'Badge valide' : 'Badge désactivé' }}</h2>
                <p class="mt-2 text-sm text-slate-600">{{ $badge->reference }}</p>
            </div>

            <div class="space-y-5 p-8">
                <div>
                    <p class="text-xs text-slate-500">Titulaire</p>
                    <p class="mt-1 text-xl font-bold">{{ $badge->full_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Société</p>
                    <p class="mt-1 font-semibold">{{ $badge->exhibitor->company_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Fonction</p>
                    <p class="mt-1 font-semibold">{{ $badge->function }}</p>
                </div>
            </div>
        </section>
    </div>
</x-commercial.layout>
