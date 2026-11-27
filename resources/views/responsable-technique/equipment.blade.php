<x-technical.layout :user="auth()->user()" title="Équipements">
    <div class="mb-5 flex items-center gap-2 text-xs font-semibold text-slate-500" aria-label="Fil d’Ariane"><a href="{{ route('responsable-technique.dashboard') }}" class="hover:text-blue-700">Accueil</a><span aria-hidden="true">/</span><span class="text-[#0F2347]">Équipements</span></div>

    <div class="mb-6 flex flex-wrap items-center gap-4">
        <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-700"><x-admin.icon name="box" class="size-7" /></span>
        <div class="min-w-0 flex-1"><p class="text-xs font-bold uppercase tracking-[0.15em] text-blue-700">Catalogue en lecture seule</p><h2 class="mt-1 text-2xl font-black tracking-tight text-[#0F2347] sm:text-3xl">Équipements</h2><p class="mt-1 text-sm text-slate-600">Consultation du catalogue, des catégories, des prix et des quantités disponibles.</p></div>
        <span class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-800">{{ number_format($stats['total']) }} références</span>
    </div>

    <section aria-label="Statistiques des équipements" class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([['Équipements au catalogue', $stats['total'], 'box', 'bg-blue-50 text-blue-700'], ['Équipements actifs', $stats['active'], 'zap', 'bg-emerald-50 text-emerald-700'], ['Quantité disponible', $stats['quantity'], 'layers', 'bg-violet-50 text-violet-700'], ['Catégories', $stats['categories'], 'inbox', 'bg-amber-50 text-amber-700']] as [$label, $value, $icon, $tone])
            <article class="flex min-h-[104px] items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5"><span class="grid size-12 shrink-0 place-items-center rounded-xl {{ $tone }}"><x-admin.icon :name="$icon" class="size-5" /></span><div class="min-w-0"><p class="text-xs font-semibold leading-5 text-slate-500">{{ $label }}</p><p class="mt-0.5 text-2xl font-black tracking-tight text-[#0F2347]">{{ number_format($value, 0, ',', ' ') }}</p></div></article>
        @endforeach
    </section>

    <form method="GET" action="{{ route('responsable-technique.equipment') }}" class="mb-5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5">
        <input type="hidden" name="sort" value="{{ $filters['sort'] }}"><input type="hidden" name="view" value="{{ $filters['view'] }}"><input type="hidden" name="per_page" value="{{ $filters['per_page'] }}">
        <div class="grid items-end gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(220px,1fr)_220px_190px_auto]">
            <label for="equipment-search" class="text-xs font-bold text-slate-600">Recherche
                <span class="relative mt-1.5 block"><span class="pointer-events-none absolute inset-y-0 left-3 grid place-items-center text-slate-400"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg></span><input id="equipment-search" name="search" value="{{ $filters['search'] }}" class="block w-full rounded-xl border-slate-300 py-3 pl-10 pr-3 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]" placeholder="Rechercher un équipement (nom, catégorie, ...)" autocomplete="off"></span>
            </label>
            <label for="equipment-category" class="text-xs font-bold text-slate-600">Catégorie
                <select id="equipment-category" name="category" class="mt-1.5 block w-full rounded-xl border-slate-300 px-3.5 py-3 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]"><option value="">Toutes les catégories</option>@foreach ($categories as $category)<option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>@endforeach</select>
            </label>
            <label for="equipment-status" class="text-xs font-bold text-slate-600">Statut
                <select id="equipment-status" name="status" class="mt-1.5 block w-full rounded-xl border-slate-300 px-3.5 py-3 text-sm focus:border-[#2563EB] focus:ring-[#2563EB]"><option value="all" @selected($filters['status'] === 'all')>Tous les statuts</option><option value="active" @selected($filters['status'] === 'active')>Actif</option><option value="inactive" @selected($filters['status'] === 'inactive')>Inactif</option></select>
            </label>
            <div class="flex items-center gap-2"><button type="submit" class="inline-flex min-h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 5h16M7 12h10m-7 7h4"/></svg>Filtrer</button><a href="{{ route('responsable-technique.equipment') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Effacer</a></div>
        </div>
    </form>

    <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:px-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex rounded-xl bg-slate-100 p-1" role="group" aria-label="Mode d’affichage">
                <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}" @if ($filters['view'] === 'grid') aria-current="page" @endif class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition {{ $filters['view'] === 'grid' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>Grille</a>
                <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}" @if ($filters['view'] === 'list') aria-current="page" @endif class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-bold transition {{ $filters['view'] === 'list' ? 'bg-white text-blue-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}"><svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6h12M9 12h12M9 18h12"/><circle cx="4" cy="6" r=".7" fill="currentColor"/><circle cx="4" cy="12" r=".7" fill="currentColor"/><circle cx="4" cy="18" r=".7" fill="currentColor"/></svg>Liste</a>
            </div>
            <p class="text-sm font-semibold text-slate-600"><span class="font-black text-[#0F2347]">{{ number_format($equipment->total()) }}</span> équipements trouvés</p>
        </div>
        <form method="GET" action="{{ route('responsable-technique.equipment') }}" class="flex flex-wrap items-center gap-2">
            @foreach (['search', 'category', 'status', 'view', 'per_page'] as $field)<input type="hidden" name="{{ $field }}" value="{{ $field === 'per_page' ? $filters[$field] : ($filters[$field] ?? '') }}">@endforeach
            <label for="equipment-sort" class="text-xs font-semibold text-slate-500">Trier par :</label>
            <select id="equipment-sort" name="sort" onchange="this.form.submit()" class="min-w-36 rounded-lg border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 focus:border-[#2563EB] focus:ring-[#2563EB]"><option value="name_asc" @selected($filters['sort'] === 'name_asc')>Nom (A → Z)</option><option value="name_desc" @selected($filters['sort'] === 'name_desc')>Nom (Z → A)</option><option value="newest" @selected($filters['sort'] === 'newest')>Plus récents</option><option value="price_asc" @selected($filters['sort'] === 'price_asc')>Prix croissant</option><option value="price_desc" @selected($filters['sort'] === 'price_desc')>Prix décroissant</option></select>
        </form>
    </div>

    @if ($filters['view'] === 'grid')
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($equipment as $item)
                <article class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="relative grid h-48 place-items-center bg-gradient-to-br from-slate-50 to-blue-50/40 p-5">@if ($item->image_path)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="{{ $item->name }}" class="h-full w-full object-contain transition duration-300 group-hover:scale-[1.03]" loading="lazy">@else<span class="grid size-16 place-items-center rounded-2xl bg-white text-slate-300 shadow-sm"><x-admin.icon name="box" class="size-8" /></span>@endif<span class="absolute right-3 top-3 inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[11px] font-bold {{ $item->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}"><span class="size-1.5 rounded-full {{ $item->is_active ? 'bg-emerald-600' : 'bg-slate-500' }}"></span>{{ $item->is_active ? 'Actif' : 'Inactif' }}</span></div>
                    <div class="flex flex-1 flex-col p-5"><p class="text-[11px] font-bold uppercase tracking-wider text-blue-700">{{ $item->category ?: 'Sans catégorie' }}</p><h3 class="mt-1 line-clamp-1 text-lg font-extrabold text-[#0F2347]" title="{{ $item->name }}">{{ $item->name }}</h3><p class="mt-2 min-h-10 line-clamp-2 text-sm leading-5 text-slate-600">{{ $item->description ?: 'Aucune description renseignée.' }}</p><div class="mt-auto grid grid-cols-2 gap-2 border-t border-slate-100 pt-4"><div class="rounded-xl bg-slate-50 p-3"><p class="text-[10px] font-semibold text-slate-500">Quantité disponible</p><p class="mt-1 text-lg font-black tabular-nums text-[#0F2347]">{{ number_format($item->quantity_available, 0, ',', ' ') }}</p></div><div class="rounded-xl bg-slate-50 p-3"><p class="text-[10px] font-semibold text-slate-500">Prix (HT)</p><p class="mt-1 truncate text-lg font-black tabular-nums text-[#0F2347]">{{ number_format((float) $item->price, 2, ',', ' ') }} <span class="text-xs font-bold text-slate-500">DH</span></p></div></div></div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center sm:col-span-2 xl:col-span-3"><span class="mx-auto grid size-12 place-items-center rounded-2xl bg-slate-50 text-slate-400"><x-admin.icon name="box" class="size-6" /></span><p class="mt-3 font-bold text-[#0F2347]">Aucun équipement trouvé</p><p class="mt-1 text-sm text-slate-500">Essayez une autre recherche ou réinitialisez les filtres.</p></div>
            @endforelse
        </div>
    @else
        <div class="space-y-3">
            @forelse ($equipment as $item)
                <article class="flex min-w-0 flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:shadow-md sm:flex-row sm:items-center"><div class="relative grid h-36 shrink-0 place-items-center overflow-hidden rounded-xl bg-gradient-to-br from-slate-50 to-blue-50/40 p-4 sm:w-44">@if ($item->image_path)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="{{ $item->name }}" class="size-full object-contain" loading="lazy">@else<x-admin.icon name="box" class="size-8 text-slate-300" />@endif</div><div class="min-w-0 flex-1"><div class="flex flex-wrap items-center gap-2"><p class="text-[11px] font-bold uppercase tracking-wider text-blue-700">{{ $item->category ?: 'Sans catégorie' }}</p><span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $item->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $item->is_active ? 'Actif' : 'Inactif' }}</span></div><h3 class="mt-1 truncate text-lg font-extrabold text-[#0F2347]">{{ $item->name }}</h3><p class="mt-1 line-clamp-2 text-sm text-slate-600">{{ $item->description ?: 'Aucune description renseignée.' }}</p></div><div class="grid grid-cols-2 gap-2 sm:w-64"><div class="rounded-xl bg-slate-50 p-3"><p class="text-[10px] font-semibold text-slate-500">Quantité disponible</p><p class="mt-1 text-lg font-black tabular-nums text-[#0F2347]">{{ number_format($item->quantity_available, 0, ',', ' ') }}</p></div><div class="rounded-xl bg-slate-50 p-3"><p class="text-[10px] font-semibold text-slate-500">Prix (HT)</p><p class="mt-1 truncate text-lg font-black tabular-nums text-[#0F2347]">{{ number_format((float) $item->price, 2, ',', ' ') }} DH</p></div></div></article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center"><p class="font-bold text-[#0F2347]">Aucun équipement trouvé</p><p class="mt-1 text-sm text-slate-500">Essayez une autre recherche ou réinitialisez les filtres.</p></div>
            @endforelse
        </div>
    @endif

    <div class="mt-6 flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm lg:flex-row lg:items-center lg:justify-between">
        <p class="text-xs font-medium text-slate-500">Affichage de <strong class="text-[#0F2347]">{{ $equipment->firstItem() ?? 0 }}</strong> à <strong class="text-[#0F2347]">{{ $equipment->lastItem() ?? 0 }}</strong> sur <strong class="text-[#0F2347]">{{ number_format($equipment->total()) }}</strong> équipements</p>
        <div class="flex justify-center">{{ $equipment->onEachSide(1)->links() }}</div>
        <form method="GET" action="{{ route('responsable-technique.equipment') }}" class="flex items-center justify-center gap-2">
            @foreach (['search', 'category', 'status', 'sort', 'view'] as $field)<input type="hidden" name="{{ $field }}" value="{{ $filters[$field] ?? '' }}">@endforeach
            <label for="equipment-per-page" class="whitespace-nowrap text-xs font-semibold text-slate-500">Éléments par page :</label><select id="equipment-per-page" name="per_page" onchange="this.form.submit()" class="rounded-lg border-slate-200 px-2.5 py-2 text-xs font-bold text-[#0F2347] focus:border-[#2563EB] focus:ring-[#2563EB]">@foreach ([6, 12, 18, 24] as $size)<option value="{{ $size }}" @selected($filters['per_page'] === $size)>{{ $size }}</option>@endforeach</select>
        </form>
    </div>
</x-technical.layout>
