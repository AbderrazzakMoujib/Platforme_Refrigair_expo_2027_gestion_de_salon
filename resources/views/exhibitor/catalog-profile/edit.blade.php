<x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">
    <div class="mx-auto max-w-6xl space-y-6">
        <header class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Espace exposant</p><h1 class="mt-1 text-3xl font-bold text-[#0F2347]">Fiche Catalogue de l'entreprise</h1><p class="mt-2 max-w-2xl text-slate-600">Complétez les informations de votre entreprise qui seront utilisées dans le catalogue officiel du salon.</p></div><div class="rounded-xl border border-slate-200 bg-white px-4 py-3"><p class="text-xs uppercase text-slate-500">Statut actuel</p><p class="mt-1 font-semibold {{ $profile?->status === 'completed' ? 'text-emerald-700' : ($profile ? 'text-amber-700' : 'text-rose-700') }}">{{ $profile?->status_label ?? 'Non remplie' }}</p><p class="mt-1 text-xs text-slate-500">Dernière mise à jour : {{ $profile?->updated_at?->format('d/m/Y à H:i') ?? '—' }}</p></div></header>
        <form method="POST" action="{{ route('exhibitor.catalog-profile.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')
            <div class="grid gap-5 lg:grid-cols-2">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8"><h2 class="text-xl font-bold text-[#0F2347]">Informations de l'entreprise</h2><p class="mt-1 text-sm text-slate-500">Coordonnées à afficher dans le catalogue.</p>
                    <div class="mt-6 space-y-5">
                        <label class="block text-sm font-semibold text-slate-700">Raison sociale
                            <input value="{{ $exhibitor->company_name }}" readonly class="mt-2 w-full rounded-xl border-slate-200 bg-slate-100 text-slate-600">
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">Site web
                            <input type="url" name="website" value="{{ old('website', $profile?->website) }}" placeholder="https://exemple.com" class="mt-2 w-full rounded-xl border-slate-300 focus:border-blue-600 focus:ring-blue-600">
                            @error('website')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">E-mail professionnel
                            <input type="email" name="email" value="{{ old('email', $profile?->email ?? $exhibitor->email) }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-blue-600 focus:ring-blue-600">
                            @error('email')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="block text-sm font-semibold text-slate-700">Téléphone professionnel
                            <input type="tel" name="phone" value="{{ old('phone', $profile?->phone ?? $exhibitor->phone) }}" class="mt-2 w-full rounded-xl border-slate-300 focus:border-blue-600 focus:ring-blue-600">
                            @error('phone')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <div class="text-sm font-semibold text-slate-700">Logo de l'entreprise
                            <div class="mt-2 flex flex-wrap items-center gap-4"><div class="grid size-28 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50" id="catalog-logo-preview">
                                @if($profile?->logo_path)<img src="{{ route('exhibitor.catalog-profile.logo') }}" alt="Logo actuel" class="h-full w-full object-contain">@elseif(!$profile && $exhibitor->logo_url)<img src="{{ $exhibitor->logo_url }}" alt="Logo actuel" class="h-full w-full object-contain">@else<span class="text-2xl font-bold text-slate-400">{{ mb_strtoupper(mb_substr($exhibitor->company_name,0,2)) }}</span>@endif
                            </div><div class="min-w-0 flex-1"><label for="catalog-logo" class="sr-only">Choisir un logo</label><input id="catalog-logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:font-semibold file:text-blue-700"><p class="mt-1 text-xs font-normal text-slate-500">JPG, PNG ou WebP · 2 Mo maximum</p>@error('logo')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror
                                @if($profile?->logo_path || $exhibitor->logo)<label class="mt-2 flex items-center gap-2 text-sm font-medium text-red-700"><input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300"> Retirer le logo de la fiche catalogue</label>@endif
                            </div></div>
                        </div>
                    </div>
                </section>
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8"><h2 class="text-xl font-bold text-[#0F2347]">Description de l'entreprise</h2><p class="mt-1 text-sm text-slate-500">Présentez votre activité aux visiteurs du salon.</p><label for="company-description" class="sr-only">Description</label><textarea id="company-description" name="company_description" rows="12" maxlength="1000" class="mt-6 min-h-72 w-full rounded-xl border-slate-300 focus:border-blue-600 focus:ring-blue-600" placeholder="Décrivez votre entreprise, vos produits et votre expertise…">{{ old('company_description', $profile?->company_description) }}</textarea><div class="mt-2 flex items-center justify-between"><span class="text-xs text-slate-500">1000 caractères maximum</span><span id="description-count" class="text-xs text-slate-500">0 / 1000</span></div>@error('company_description')<span class="mt-1 block text-sm text-red-600">{{ $message }}</span>@enderror</section>
            </div>
            @error('action')<p class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $message }}</p>@enderror
            <div class="flex flex-col-reverse justify-end gap-3 sm:flex-row"><button name="action" value="draft" class="rounded-xl border border-slate-300 bg-white px-5 py-3 font-semibold text-slate-700 hover:bg-slate-50">Enregistrer comme brouillon</button><button name="action" value="complete" class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white shadow-sm hover:bg-blue-700">Soumettre ma fiche</button></div>
        </form>
    </div>
    <script>
        const description = document.getElementById('company-description');
        const counter = document.getElementById('description-count');
        const updateCount = () => { counter.textContent = `${description.value.length} / 1000`; };
        updateCount(); description.addEventListener('input', updateCount);
        document.getElementById('catalog-logo')?.addEventListener('change', function (event) {
            const file = event.target.files?.[0]; if (!file) return;
            const preview = document.getElementById('catalog-logo-preview'); preview.replaceChildren();
            const image = document.createElement('img'); image.src = URL.createObjectURL(file); image.alt = 'Aperçu du nouveau logo'; image.className = 'h-full w-full object-contain'; preview.append(image);
        });
    </script>
</x-exhibitor.layout>
