<x-exhibitor.layout :user="auth()->user()" :exhibitor="auth()->user()->exhibitorProfile">
    <div class="space-y-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-blue-600">Espace Exposant / Informations pratiques</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-[#0F2347] sm:text-3xl">Plan du Salon — REFRIGAIR EXPO 2027</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Consultez la dernière version du plan publiée par l’équipe Commerciale.</p>
            </div>
            @if ($documentAvailable)
                <a href="{{ route('exhibitor.salon-plan.download') }}" class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                    <x-admin.icon name="download" class="size-4" /> Télécharger le PDF
                </a>
            @endif
        </header>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @if ($documentAvailable)
                <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-blue-50 text-blue-700"><x-admin.icon name="file-pdf" class="size-6" /></span>
                        <div class="min-w-0">
                            <h2 class="truncate font-bold text-[#0F2347]">{{ $plan->original_name }}</h2>
                            <p class="mt-1 text-xs text-slate-500">Mis à jour le {{ $plan->updated_at?->timezone(config('app.timezone'))->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('exhibitor.salon-plan.preview') }}" target="_blank" rel="noopener" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:text-blue-700">
                        <x-admin.icon name="external-link" class="size-4" /> Ouvrir dans un nouvel onglet
                    </a>
                </div>
                <x-salon-plan.pdf-viewer
                    :preview-url="route('exhibitor.salon-plan.preview')"
                    :download-url="route('exhibitor.salon-plan.download')"
                />
            @else
                <div class="grid min-h-80 place-items-center p-8 text-center">
                    <div class="max-w-md">
                        <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-blue-50 text-blue-700"><x-admin.icon name="map" class="size-8" /></span>
                        <h2 class="mt-5 text-lg font-extrabold text-[#0F2347]">Le plan du salon sera disponible prochainement.</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Dès sa publication, vous pourrez le consulter ici ou télécharger le PDF.</p>
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-exhibitor.layout>
