@props(['previewUrl', 'downloadUrl'])

<section data-pdf-viewer data-pdf-url="{{ $previewUrl }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-label="Visualiseur du plan du salon">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-white p-3 sm:px-4">
        <div class="flex flex-wrap items-center gap-2" role="toolbar" aria-label="Commandes du visualiseur PDF">
            <div class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 p-1">
                <button type="button" data-pdf-action="zoom-out" disabled aria-label="Zoom arrière" title="Zoom arrière" class="grid size-9 place-items-center rounded-lg text-[#0F2347] transition hover:bg-white disabled:cursor-wait disabled:opacity-40">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4m-8-5h6"/></svg>
                </button>
                <span data-pdf-zoom-value class="min-w-[3.5rem] text-center text-xs font-bold tabular-nums text-[#0F2347]" aria-live="polite">—</span>
                <button type="button" data-pdf-action="zoom-in" disabled aria-label="Zoom avant" title="Zoom avant" class="grid size-9 place-items-center rounded-lg text-[#0F2347] transition hover:bg-white disabled:cursor-wait disabled:opacity-40">
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4m-5-8v6m-3-3h6"/></svg>
                </button>
            </div>
            <button type="button" data-pdf-action="fit-width" disabled class="min-h-10 rounded-xl border border-slate-200 px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-300 hover:text-blue-700 disabled:opacity-40">Ajuster à la largeur</button>
            <button type="button" data-pdf-action="fit-page" disabled class="min-h-10 rounded-xl border border-slate-200 px-3 text-xs font-semibold text-slate-700 transition hover:border-blue-300 hover:text-blue-700 disabled:opacity-40">Ajuster à la page</button>
            <span data-pdf-page-count class="px-1 text-xs font-medium text-slate-500" aria-live="polite"></span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" data-pdf-action="fullscreen" disabled aria-label="Plein écran" title="Plein écran" class="grid size-10 place-items-center rounded-xl border border-slate-200 text-[#0F2347] transition hover:border-blue-300 hover:text-blue-700 disabled:opacity-40">
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3m13-5h3a2 2 0 0 1 2 2v3M3 16v3a2 2 0 0 0 2 2h3m13-5v3a2 2 0 0 1-2 2h-3"/></svg>
            </button>
            <a href="{{ $downloadUrl }}" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-[#2563EB] px-3.5 text-xs font-bold text-white transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                <x-admin.icon name="download" class="size-4" />
                <span class="hidden sm:inline">Télécharger le PDF original</span><span class="sm:hidden">PDF</span>
            </a>
        </div>
    </div>

    <div data-pdf-stage tabindex="0" class="relative h-[62vh] min-h-[360px] overflow-auto bg-slate-100 p-3 touch-pan-x touch-pan-y sm:h-[72vh] sm:min-h-[520px] sm:p-6" aria-label="Page du plan. Après zoom, faites glisser la page ou utilisez le défilement pour la déplacer.">
        <div data-pdf-loading role="status" class="absolute inset-0 z-10 grid place-items-center bg-slate-100/90 p-6 text-center text-sm font-semibold text-[#0F2347]">
            <span><span class="mb-3 inline-block size-7 animate-spin rounded-full border-2 border-blue-200 border-t-blue-600 align-middle" aria-hidden="true"></span><br>Chargement du plan haute résolution…</span>
        </div>
        <div data-pdf-error hidden role="alert" class="absolute inset-0 z-20 grid place-items-center bg-slate-100 p-6 text-center text-sm font-semibold text-red-800"></div>
        <div class="mx-auto w-max rounded-lg bg-white shadow-md">
            <canvas data-pdf-canvas hidden class="block max-w-none select-none" aria-label="Rendu net du plan du salon" draggable="false"></canvas>
        </div>
    </div>
    <p class="border-t border-slate-100 bg-white px-4 py-2.5 text-center text-xs text-slate-500">Après zoom, faites glisser le plan avec la souris ou balayez-le sur écran tactile.</p>
</section>

@vite('resources/js/salon-plan-viewer.js')
