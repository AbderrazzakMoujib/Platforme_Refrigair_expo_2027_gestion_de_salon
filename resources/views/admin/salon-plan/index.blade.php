<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">
    <div class="space-y-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="flex items-start gap-4">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-700">
                    <x-admin.icon name="map" class="size-7" />
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">Administration / Documents partagés</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-[#0F2347] sm:text-3xl">Plan du Salon — REFRIGAIR EXPO 2027</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Gérez le PDF unique partagé avec tous les Commerciaux et les Exposants.</p>
                </div>
            </div>
            @if ($documentAvailable)
                <a href="{{ route('admin.salon-plan.download') }}" class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                    <x-admin.icon name="download" class="size-4" /> Télécharger le PDF
                </a>
            @endif
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700"><x-admin.icon name="file-pdf" class="size-6" /></span>
                    <div class="min-w-0">
                        @if ($documentAvailable)
                            <h2 class="truncate font-bold text-[#0F2347]">{{ $plan->original_name }}</h2>
                            <p class="mt-1 text-xs text-slate-500">Dernière modification : {{ $plan->updated_at?->timezone(config('app.timezone'))->format('d/m/Y à H:i') }}@if ($plan->updater) · par {{ $plan->updater->name }}@endif</p>
                        @else
                            <h2 class="font-bold text-[#0F2347]">Aucun document actif</h2>
                            <p class="mt-1 text-sm text-slate-500">Le plan du salon sera disponible prochainement.</p>
                        @endif
                    </div>
                </div>
                @if ($documentAvailable)
                    <a href="{{ route('admin.salon-plan.preview') }}" target="_blank" rel="noopener" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:text-blue-700">
                        <x-admin.icon name="external-link" class="size-4" /> Ouvrir dans un nouvel onglet
                    </a>
                @endif
            </div>

            @if ($documentAvailable)
                <x-salon-plan.pdf-viewer
                    :preview-url="route('admin.salon-plan.preview')"
                    :download-url="route('admin.salon-plan.download')"
                />
            @else
                <div class="grid min-h-72 place-items-center p-8 text-center">
                    <div class="max-w-md">
                        <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-blue-50 text-blue-700"><x-admin.icon name="map" class="size-7" /></span>
                        <p class="mt-4 font-bold text-[#0F2347]">Le plan du salon sera disponible prochainement.</p>
                    </div>
                </div>
            @endif
        </section>

        <section class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-start gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700"><x-admin.icon name="upload" class="size-5" /></span>
                <div>
                    <h2 class="font-bold text-[#0F2347]">{{ $documentAvailable ? 'Remplacer le plan partagé' : 'Publier le plan du salon' }}</h2>
                    <p class="mt-1 text-sm text-slate-600">PDF valide de 20 Mo maximum. Le plan actuel reste actif si l’envoi échoue.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.salon-plan.replace') }}" enctype="multipart/form-data" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
                @csrf
                <div class="min-w-0 flex-1">
                    <label for="admin-salon-plan-document" class="text-sm font-semibold text-slate-700">Fichier PDF</label>
                    <input id="admin-salon-plan-document" name="document" type="file" accept="application/pdf,.pdf" required class="mt-2 block min-h-12 w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:min-h-12 file:border-0 file:bg-blue-50 file:px-4 file:font-bold file:text-blue-800 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <p class="mt-1 text-xs text-slate-500">Format PDF · 20 Mo maximum</p>
                </div>
                <button type="submit" class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-[#0F2347] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#173568] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                    <x-admin.icon name="upload" class="size-4" /> {{ $documentAvailable ? 'Remplacer le PDF' : 'Publier le PDF' }}
                </button>
            </form>
        </section>

        @if ($documentAvailable)
            <section class="flex flex-col gap-3 rounded-2xl border border-red-100 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <div>
                    <h2 class="font-bold text-[#0F2347]">Supprimer le PDF actif</h2>
                    <p class="mt-1 text-sm text-slate-600">Le plan sera retiré des espaces Admin, Commercial et Exposant.</p>
                </div>
                <form method="POST" action="{{ route('admin.salon-plan.destroy') }}" onsubmit="return confirm('Confirmer la suppression du PDF partagé pour tous les utilisateurs ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-red-200 px-5 py-3 text-sm font-bold text-red-700 transition hover:bg-red-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2">Supprimer le PDF actif</button>
                </form>
            </section>
        @endif
    </div>
</x-admin.layout>
