<x-commercial.layout :commercial="auth()->user()" :commercial-initials="auth()->user()->initials">
    <div class="space-y-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-blue-600">Espace Commercial / Documents partagés</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-[#0F2347] sm:text-3xl">Plan du Salon</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Un document commun aux Commerciaux et aux Exposants. Le remplacement publie la nouvelle version pour tous.</p>
            </div>
            @if ($documentAvailable)
                <a href="{{ route('commercial.salon-plan.download') }}" class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                    <x-admin.icon name="download" class="size-4" />
                    Télécharger le PDF
                </a>
            @endif
        </header>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700"><x-admin.icon name="file-pdf" class="size-6" /></span>
                    <div class="min-w-0">
                        <h2 class="font-bold text-[#0F2347]">Document partagé</h2>
                        @if ($documentAvailable)
                            <p class="mt-1 truncate text-sm text-slate-600">{{ $plan->original_name }}</p>
                            <p class="mt-1 text-xs text-slate-500">Mis à jour le {{ $plan->updated_at?->timezone(config('app.timezone'))->format('d/m/Y à H:i') }}@if ($plan->updater) par {{ $plan->updater->name }}@endif</p>
                        @else
                            <p class="mt-1 text-sm text-slate-500">Aucun plan du salon n’a encore été publié.</p>
                        @endif
                    </div>
                </div>
                @if ($documentAvailable)
                    <a href="{{ route('commercial.salon-plan.preview') }}" target="_blank" rel="noopener" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:text-blue-700">
                        <x-admin.icon name="external-link" class="size-4" /> Ouvrir dans un nouvel onglet
                    </a>
                @endif
            </div>

            @if ($documentAvailable)
                <x-salon-plan.pdf-viewer
                    :preview-url="route('commercial.salon-plan.preview')"
                    :download-url="route('commercial.salon-plan.download')"
                />
            @else
                <div class="grid min-h-72 place-items-center p-8 text-center">
                    <div class="max-w-md">
                        <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-400"><x-admin.icon name="map" class="size-7" /></span>
                        <p class="mt-4 font-bold text-[#0F2347]">Le plan du salon sera disponible prochainement.</p>
                        <p class="mt-2 text-sm text-slate-500">Importez le PDF ci-dessous pour le partager avec tous les Commerciaux et les Exposants.</p>
                    </div>
                </div>
            @endif
        </section>

        <section class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-start gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700"><x-admin.icon name="upload" class="size-5" /></span>
                <div>
                    <h2 class="font-bold text-[#0F2347]">{{ $documentAvailable ? 'Remplacer le plan partagé' : 'Publier le plan du salon' }}</h2>
                    <p class="mt-1 text-sm text-slate-600">Choisissez un PDF réel de 20 Mo maximum. Le document actuel reste disponible si le nouvel envoi échoue.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('commercial.salon-plan.replace') }}" enctype="multipart/form-data" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
                @csrf
                <div class="min-w-0 flex-1">
                    <label for="salon-plan-document" class="text-sm font-semibold text-slate-700">Fichier PDF</label>
                    <input id="salon-plan-document" name="document" type="file" accept="application/pdf,.pdf" required class="mt-2 block min-h-12 w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:min-h-12 file:border-0 file:bg-blue-50 file:px-4 file:font-bold file:text-blue-800 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <p class="mt-1 text-xs text-slate-500">Format PDF · 20 Mo maximum</p>
                    @error('document') <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="inline-flex min-h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-[#0F2347] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#173568] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                    <x-admin.icon name="upload" class="size-4" /> {{ $documentAvailable ? 'Remplacer le PDF' : 'Publier le PDF' }}
                </button>
            </form>
        </section>
    </div>
</x-commercial.layout>
