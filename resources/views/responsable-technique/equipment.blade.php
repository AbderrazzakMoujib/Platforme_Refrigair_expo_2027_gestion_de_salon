<x-technical.layout :user="auth()->user()" title="Catalogue équipements">
    <div class="mb-6"><p class="text-sm font-semibold text-blue-700">Consultation en lecture seule</p><h1 class="mt-1 text-2xl font-black text-[#0F2347]">Catalogue équipements</h1></div>
    <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4">
        <label class="text-sm font-semibold text-slate-700">Recherche<input name="search" value="{{ $filters['search'] }}" class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#2563EB] focus:ring-[#2563EB]" placeholder="Nom, catégorie, description"></label>
        <label class="text-sm font-semibold text-slate-700">Catégorie<select name="category" class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#2563EB] focus:ring-[#2563EB]"><option value="">Toutes</option>@foreach ($categories as $category)<option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>@endforeach</select></label>
        <label class="text-sm font-semibold text-slate-700">État<select name="status" class="mt-1 w-full rounded-xl border-slate-300 focus:border-[#2563EB] focus:ring-[#2563EB]"><option value="all" @selected($filters['status'] === 'all')>Tous</option><option value="active" @selected($filters['status'] === 'active')>Actifs</option><option value="inactive" @selected($filters['status'] === 'inactive')>Inactifs</option></select></label>
        <div class="flex items-end"><button class="w-full rounded-xl bg-[#2563EB] px-4 py-3 text-sm font-bold text-white hover:bg-blue-700">Filtrer</button></div>
    </form>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($equipment as $item)
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                @if ($item->image_path)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="{{ $item->name }}" class="h-48 w-full bg-slate-100 object-cover" loading="lazy">@else<div class="grid h-48 place-items-center bg-slate-100 text-sm font-semibold text-slate-400">Image non disponible</div>@endif
                <div class="p-5"><div class="flex items-start justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-wide text-blue-700">{{ $item->category ?: 'Sans catégorie' }}</p><h2 class="mt-1 text-lg font-extrabold text-[#0F2347]">{{ $item->name }}</h2></div><span @class(['shrink-0 rounded-full px-3 py-1 text-xs font-bold', 'bg-emerald-100 text-emerald-800' => $item->is_active, 'bg-slate-100 text-slate-600' => ! $item->is_active])>{{ $item->is_active ? 'Actif' : 'Inactif' }}</span></div>
                    <p class="mt-3 min-h-10 text-sm leading-5 text-slate-600">{{ $item->description ?: 'Aucune description.' }}</p>
                    <div class="mt-4 flex justify-between border-t border-slate-100 pt-4 text-sm"><span class="text-slate-500">Quantité disponible</span><strong class="text-[#0F2347]">{{ $item->quantity_available }}</strong></div>
                    <div class="mt-2 flex justify-between text-sm"><span class="text-slate-500">Prix</span><strong class="text-[#0F2347]">{{ number_format((float) $item->price, 2, ',', ' ') }}</strong></div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500 sm:col-span-2 xl:col-span-3">Aucun équipement ne correspond à ces critères.</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $equipment->links() }}</div>
</x-technical.layout>
