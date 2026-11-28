<x-commercial.layout :commercial="$commercial" :commercial-initials="$commercial->initials">
    @php($total = max(1, $stats['total']))
    <div class="space-y-6">
        <header><p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Espace Commercial</p><h1 class="mt-1 text-3xl font-bold text-[#0F2347]">Fiches Catalogue — Mes Exposants</h1><p class="mt-2 text-slate-600">Suivi des informations catalogue des exposants qui vous sont attribués.</p></header>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach(['Exposants assignés'=>'total','Fiches complétées'=>'completed','Brouillons'=>'draft','Non remplies'=>'empty'] as $label=>$key)
                <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-1 text-2xl font-bold text-[#0F2347]">{{ $stats[$key] }}</p>@if($key !== 'total')<p class="mt-1 text-xs text-slate-500">{{ round($stats[$key] * 100 / $total) }} % du portefeuille</p>@endif</div>
            @endforeach
        </div>
        <form class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:flex-row"><input name="q" value="{{ request('q') }}" placeholder="Entreprise" class="min-w-0 flex-1 rounded-lg border-slate-300"><select name="status" class="rounded-lg border-slate-300"><option value="">Tous les statuts</option>@foreach(['completed'=>'Complétée','draft'=>'Brouillon','empty'=>'Non remplie'] as $key=>$label)<option value="{{ $key }}" @selected(request('status')===$key)>{{ $label }}</option>@endforeach</select><button class="rounded-lg bg-[#0F2347] px-5 py-2 font-semibold text-white">Filtrer</button></form>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white"><div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-4">Entreprise</th><th class="p-4">Logo</th><th class="p-4">Site web</th><th class="p-4">État de la fiche</th><th class="p-4">Mise à jour</th><th class="p-4"></th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($exhibitors as $exhibitor)
                @php($profile = $exhibitor->catalogProfile)
                <tr><td class="p-4 font-semibold text-slate-800">{{ $exhibitor->company_name }}</td><td class="p-4">@if($profile?->logo_path)<img src="{{ route('commercial.catalog.logo',$exhibitor->id) }}" alt="Logo" class="size-12 rounded-lg border object-contain p-1">@else<span class="text-slate-400">—</span>@endif</td><td class="p-4">@if($profile?->website)<a href="{{ $profile->website }}" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline">Voir le site</a>@else—@endif</td><td class="p-4"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $profile?->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($profile ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">{{ $profile?->status_label ?? 'Non remplie' }}</span></td><td class="p-4 text-slate-600">{{ $profile?->updated_at?->format('d/m/Y') ?? '—' }}</td><td class="p-4 text-right"><a class="font-semibold text-blue-700 hover:underline" href="{{ route('commercial.catalog.show',$exhibitor->id) }}">Voir la fiche</a></td></tr>
            @empty<tr><td colspan="6" class="p-8 text-center text-slate-500">Aucun exposant trouvé.</td></tr>@endforelse
        </tbody></table></div><div class="border-t p-4">{{ $exhibitors->links() }}</div></div>
    </div>
</x-commercial.layout>
