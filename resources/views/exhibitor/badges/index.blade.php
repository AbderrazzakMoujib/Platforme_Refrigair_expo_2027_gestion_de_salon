
<x-exhibitor.layout :user="$user" :exhibitor="$exhibitor">

<div class="space-y-6">

    <!-- BREADCRUMB + HEADER -->
    <header class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">

        <div>
            <nav class="mb-4 text-sm text-slate-500">
                <a href="{{ route('exposant.index') }}"
                   class="hover:text-blue-600">
                    Espace exposant
                </a>

                <span class="mx-2">/</span>

                <span class="font-semibold text-[#2563EB]">
                    Mes badges
                </span>
            </nav>

            <div class="flex items-center gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">

                    <svg class="size-7"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         aria-hidden="true">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <circle cx="9" cy="10" r="2"/>
                        <path d="M5 17c0-2 2-3 4-3s4 1 4 3M15 9h4m-4 4h4"/>
                    </svg>

                </span>

                <div>
                    <h1 class="text-2xl font-extrabold text-[#0F2347] sm:text-3xl">
                        Mes badges
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Gestion des badges de votre équipe et suivi de votre quota.
                    </p>
                </div>
            </div>
        </div>

        <!-- EVENT BANNER -->
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 to-white px-5 py-4 shadow-sm">

            <div class="flex items-center gap-3">

                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-700">

                    <svg class="size-6"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         aria-hidden="true">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M7 3v4M17 3v4M3 10h18"/>
                    </svg>

                </span>

                <div>
                    <h2 class="font-extrabold text-[#0F2347]">
                        {{ $salon?->name ?? 'Refrigair Expo 2027' }}
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        @if ($salon)
                            {{ $salon->start_date?->format('d/m/Y') }}
                            —
                            {{ $salon->end_date?->format('d/m/Y') }}

                            @if ($salon->venue)
                                · {{ $salon->venue }}
                            @endif

                            @if ($salon->city)
                                · {{ $salon->city }}
                            @endif
                        @else
                            Informations du prochain salon à venir
                        @endif
                    </p>
                </div>

            </div>
        </div>
    </header>

    <!-- STATUS MESSAGE -->
    @if (session('status'))
        <div role="status"
             class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div role="alert"
             class="rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            Veuillez corriger les erreurs.
        </div>
    @endif

    <!-- KPI CARDS -->
    <section class="grid gap-4 md:grid-cols-3"
             aria-label="Statistiques des badges">

        <!-- REGISTERED -->
        <article class="flex items-center gap-4 rounded-2xl border border-blue-100 bg-blue-50/70 p-5 shadow-sm">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-violet-100 text-violet-600">
                <svg class="size-7"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     aria-hidden="true">
                    <circle cx="9" cy="8" r="3"/>
                    <path d="M3 20v-2a6 6 0 0 1 12 0v2M17 5a3 3 0 0 1 0 6M17 14a5 5 0 0 1 4 5v1"/>
                </svg>
            </span>

            <div>
                <p class="text-sm font-semibold text-slate-600">
                    Badges enregistrés
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ $count }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    Badges utilisés
                </p>
            </div>

        </article>

        <!-- AUTHORIZED -->
        <article class="flex items-center gap-4 rounded-2xl border border-amber-100 bg-amber-50/70 p-5 shadow-sm">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-amber-100 text-amber-600">

                <svg class="size-7"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     aria-hidden="true">
                    <rect x="3" y="4" width="18" height="16" rx="2"/>
                    <circle cx="9" cy="10" r="2"/>
                    <path d="M5 17c0-2 2-3 4-3s4 1 4 3M15 9h4m-4 4h4"/>
                </svg>

            </span>

            <div>
                <p class="text-sm font-semibold text-slate-600">
                    Badges autorisés
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ $authorized }}
                </p>
            </div>

        </article>

        <!-- REMAINING -->
        <article class="flex items-center gap-4 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-5 shadow-sm">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-emerald-100 text-emerald-600">

                <svg class="size-7"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m7 12 3 3 6-6"/>
                </svg>

            </span>

            <div>
                <p class="text-sm font-semibold text-slate-600">
                    Badges restants
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ $remaining }}
                </p>
            </div>

        </article>

    </section>

    <!-- QUOTA + INFORMATION -->
    <section class="grid gap-5 xl:grid-cols-2">

        <!-- QUOTA -->
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

            <h2 class="text-lg font-extrabold text-[#0F2347]">
                Quota de badges
            </h2>

            <div class="mt-5 flex flex-col items-center gap-6 sm:flex-row">

                <div class="relative grid size-36 shrink-0 place-items-center rounded-full"
                     style="background: conic-gradient(#2563EB {{ $progress }}%, #E2E8F0 {{ $progress }}% 100%);">

                    <div class="grid size-28 place-items-center rounded-full bg-white">
                        <span class="text-3xl font-extrabold text-[#0F2347]">
                            {{ $progress }}%
                        </span>
                    </div>
                </div>

                <div class="w-full flex-1 space-y-3">

                    <div class="rounded-xl border border-slate-100 bg-[#F5F7FB] p-4 text-center">
                        <p class="text-lg font-extrabold text-[#0F2347]">
                            {{ $count }} badges utilisés
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            sur {{ $authorized }} autorisés
                        </p>
                    </div>

                    <div class="rounded-xl bg-emerald-50 p-4 text-center">
                        <p class="font-bold text-emerald-700">
                            {{ $remaining }} badge(s) restant(s)
                        </p>
                    </div>

                </div>

            </div>

        </article>

        <!-- INFO -->
        <article class="rounded-2xl border border-blue-100 bg-gradient-to-br from-white to-blue-50 p-6 shadow-sm">

            <div class="flex items-center gap-3">

                <span class="grid size-10 place-items-center rounded-xl bg-blue-100 text-blue-700">

                    <svg class="size-6"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 11v5M12 7h.01"/>
                    </svg>

                </span>

                <h2 class="text-lg font-extrabold text-[#0F2347]">
                    Informations importantes
                </h2>

            </div>

            <ul class="mt-5 list-disc space-y-3 pl-5 text-sm leading-6 text-slate-700">

                <li>
                    Le quota de badges est défini par l'organisation.
                </li>

                <li>
                    Chaque badge est nominatif et lié à votre entreprise.
                </li>

                <li>
                    Vous pouvez ajouter, modifier ou supprimer
                    les badges de votre équipe.
                </li>

                <li>
                    Les badges inactifs restent comptabilisés
                    dans votre quota.
                </li>

            </ul>

        </article>

    </section>

    <!-- BADGE TABLE -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <!-- TABLE HEADER -->
        <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:p-6">

            <div class="flex flex-wrap items-center justify-between gap-3">

                <div>
                    <h2 class="text-lg font-extrabold text-[#0F2347]">
                        Liste de mes badges ({{ $count }})
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        {{ $activeCount }} actif(s)
                        · {{ $inactiveCount }} inactif(s)
                    </p>
                </div>

                @if ($remaining > 0)
                    <a href="{{ route('exhibitor.badges.create') }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">

                        <span class="text-xl leading-none">+</span>
                        Ajouter un badge

                    </a>
                @else
                    <span class="rounded-xl bg-amber-50 px-4 py-3 text-xs font-bold text-amber-700">
                        Quota atteint
                    </span>
                @endif

            </div>

            <!-- SEARCH + FILTER -->
            <form method="GET"
                  action="{{ route('exhibitor.badges.index') }}"
                  class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_180px_auto]">

                <div class="relative">
                    <label for="search" class="sr-only">
                        Rechercher un badge
                    </label>

                    <svg class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-slate-400"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m16 16 5 5"/>
                    </svg>

                    <input id="search"
                           name="search"
                           type="search"
                           value="{{ $search }}"
                           maxlength="255"
                           placeholder="Rechercher un badge (nom, prénom, fonction)..."
                           class="w-full rounded-xl border border-slate-200 py-3 pl-10 pr-4 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="status" class="sr-only">
                        Statut
                    </label>

                    <select id="status"
                            name="status"
                            class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">

                        <option value="all"
                            @selected($status === 'all')>
                            Tous les badges
                        </option>

                        <option value="active"
                            @selected($status === 'active')>
                            Actifs
                        </option>

                        <option value="inactive"
                            @selected($status === 'inactive')>
                            Inactifs
                        </option>

                    </select>
                </div>

                <div class="flex gap-2">

                    <button type="submit"
                            class="flex-1 rounded-xl bg-[#0F2347] px-4 py-3 text-sm font-bold text-white hover:bg-blue-900">
                        Filtrer
                    </button>

                    @if ($search !== '' || $status !== 'all')
                        <a href="{{ route('exhibitor.badges.index') }}"
                           class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Effacer
                        </a>
                    @endif

                </div>

            </form>

        </div>

        <!-- RESPONSIVE TABLE -->
        <div class="overflow-x-auto">

            <table class="min-w-full text-left text-sm">

                <thead class="bg-[#F0F5FF] text-xs font-bold uppercase tracking-wide text-[#0F2347]">

                    <tr>
                        <th scope="col" class="px-5 py-4">
                            #
                        </th>

                        <th scope="col" class="px-5 py-4">
                            Titulaire
                        </th>

                        <th scope="col" class="px-5 py-4">
                            Fonction
                        </th>

                        <th scope="col" class="px-5 py-4">
                            Date de création
                        </th>

                        <th scope="col" class="px-5 py-4">
                            Statut
                        </th>

                        <th scope="col" class="px-5 py-4 text-right">
                            Actions
                        </th>
                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse ($badges as $badge)

                        @php
                            $badgeInitials = mb_strtoupper(
                                mb_substr($badge->first_name, 0, 1)
                                . mb_substr($badge->last_name, 0, 1)
                            );

                            $isActive = $badge->status === 'active';
                        @endphp

                        <tr class="transition hover:bg-slate-50">

                            <!-- NUMBER -->
                            <td class="px-5 py-4 font-semibold text-slate-500">
                                {{ $badges->firstItem() + $loop->index }}
                            </td>

                            <!-- PERSON -->
                            <td class="min-w-56 px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <span class="grid size-11 shrink-0 place-items-center rounded-full bg-blue-100 text-sm font-extrabold text-blue-700">
                                        {{ $badgeInitials }}
                                    </span>

                                    <div class="min-w-0">
                                        <p class="font-bold text-[#0F2347]">
                                            {{ $badge->full_name }}
                                        </p>

                                        <p class="mt-1 max-w-56 truncate text-xs text-slate-500"
                                           title="{{ $badge->email }}">
                                            {{ $badge->email }}
                                        </p>

                                        <p class="mt-1 text-[11px] text-slate-400">
                                            {{ $badge->reference }}
                                        </p>
                                    </div>

                                </div>

                            </td>

                            <!-- FUNCTION -->
                            <td class="min-w-44 px-5 py-4 text-slate-700">
                                {{ $badge->function }}
                            </td>

                            <!-- DATE -->
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                {{ $badge->created_at?->format('d/m/Y') }}
                            </td>

                            <!-- STATUS -->
                            <td class="px-5 py-4">

                                <span @class([
                                    'inline-flex whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-bold',
                                    'bg-emerald-50 text-emerald-700' => $isActive,
                                    'bg-red-50 text-red-700' => !$isActive,
                                ])>
                                    {{ $isActive ? 'Actif' : 'Inactif' }}
                                </span>

                            </td>

                            <!-- ACTIONS -->
                            <td class="px-5 py-4">

                                <div class="flex items-center justify-end gap-2">

                                    <!-- QR -->
                                    <button type="button"
                                            class="open-badge-qr grid size-9 place-items-center rounded-lg border border-slate-200 text-blue-700 hover:bg-blue-50"
                                            data-url="{{ route('exhibitor.badges.qr', $badge) }}"
                                            data-name="{{ $badge->full_name }}"
                                            data-reference="{{ $badge->reference }}"
                                            title="Voir le QR Code"
                                            aria-label="QR Code de {{ $badge->full_name }}">

                                        <svg class="size-5"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="1.8"
                                             aria-hidden="true">
                                            <rect x="3" y="3" width="7" height="7"/>
                                            <rect x="14" y="3" width="7" height="7"/>
                                            <rect x="3" y="14" width="7" height="7"/>
                                            <path d="M14 14h3v3h-3zM20 14v3M14 20h3M20 20h1"/>
                                        </svg>

                                    </button>

                                    <!-- EDIT -->
                                    <a href="{{ route('exhibitor.badges.edit', $badge) }}"
                                       class="grid size-9 place-items-center rounded-lg border border-blue-200 text-blue-700 hover:bg-blue-50"
                                       title="Modifier"
                                       aria-label="Modifier {{ $badge->full_name }}">

                                        <svg class="size-5"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="1.8"
                                             aria-hidden="true">
                                            <path d="m15 5 4 4M4 20l4-.7L20 7a2.83 2.83 0 0 0-4-4L4 15.3 3 21z"/>
                                        </svg>

                                    </a>

                                    <!-- DELETE -->
                                    <form method="POST"
                                          action="{{ route('exhibitor.badges.destroy', $badge) }}"
                                          onsubmit="return confirm('Supprimer définitivement ce badge de votre liste ?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="grid size-9 place-items-center rounded-lg border border-red-200 text-red-600 hover:bg-red-50"
                                                title="Supprimer"
                                                aria-label="Supprimer {{ $badge->full_name }}">

                                            <svg class="size-5"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="1.8"
                                                 aria-hidden="true">
                                                <path d="M4 7h16M9 7V4h6v3M6 7l1 14h10l1-14M10 11v6M14 11v6"/>
                                            </svg>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">

                                <div class="mx-auto grid size-16 place-items-center rounded-2xl bg-blue-50 text-blue-600">

                                    <svg class="size-8"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.8"
                                         aria-hidden="true">
                                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                                        <circle cx="9" cy="10" r="2"/>
                                        <path d="M5 17c0-2 2-3 4-3s4 1 4 3M15 9h4m-4 4h4"/>
                                    </svg>

                                </div>

                                <h3 class="mt-4 font-bold text-[#0F2347]">
                                    Aucun badge trouvé
                                </h3>

                                <p class="mt-2 text-sm text-slate-500">
                                    @if ($search !== '' || $status !== 'all')
                                        Essayez de modifier vos filtres.
                                    @else
                                        Ajoutez les membres de votre équipe pour préparer leur participation.
                                    @endif
                                </p>

                                @if ($count === 0 && $remaining > 0)
                                    <a href="{{ route('exhibitor.badges.create') }}"
                                       class="mt-5 inline-flex rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                                        Créer mon premier badge
                                    </a>
                                @endif

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <!-- PAGINATION -->
        <div class="border-t border-slate-100 px-5 py-4">

            <p class="mb-3 text-xs text-slate-500">

                @if ($badges->total() > 0)
                    Affichage de {{ $badges->firstItem() }}
                    à {{ $badges->lastItem() }}
                    sur {{ $badges->total() }} badge(s)
                @else
                    Aucun résultat
                @endif

            </p>

            @if ($badges->hasPages())
                {{ $badges->links() }}
            @endif

        </div>

    </section>

</div>

<!-- QR MODAL -->
<dialog id="badge-qr-modal"
        aria-labelledby="badge-qr-title"
        class="m-auto w-[calc(100%-2rem)] max-w-sm rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-900/60">

    <div class="border-b border-slate-100 px-6 py-5">

        <div class="flex items-center justify-between gap-3">

            <h2 id="badge-qr-title"
                class="text-lg font-extrabold text-[#0F2347]">
                QR Code du badge
            </h2>

            <button type="button"
                    id="close-badge-qr"
                    class="grid size-9 place-items-center rounded-lg text-slate-500 hover:bg-slate-100"
                    aria-label="Fermer">
                ✕
            </button>

        </div>

    </div>

    <div class="px-6 py-6 text-center">

        <p id="badge-qr-name"
           class="font-bold text-[#0F2347]">
        </p>

        <p id="badge-qr-reference"
           class="mt-1 text-xs text-slate-500">
        </p>

        <div class="mx-auto mt-5 grid size-64 max-w-full place-items-center rounded-xl border border-slate-200 bg-white p-3">

            <img id="badge-qr-image"
                 alt="QR Code de vérification du badge"
                 class="h-full w-full object-contain">

        </div>

        <p class="mt-4 text-xs leading-5 text-slate-500">
            QR Code individuel du badge.
            La vérification est réservée aux personnes autorisées.
        </p>

        <a id="badge-qr-open"
           href="#"
           target="_blank"
           rel="noopener noreferrer"
           class="mt-5 inline-flex rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
            Ouvrir le QR Code
        </a>

    </div>

</dialog>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('badge-qr-modal');
    const image = document.getElementById('badge-qr-image');
    const name = document.getElementById('badge-qr-name');
    const reference = document.getElementById('badge-qr-reference');
    const link = document.getElementById('badge-qr-open');
    const close = document.getElementById('close-badge-qr');

    if (!modal || !image || !link) return;

    document.querySelectorAll('.open-badge-qr').forEach(button => {
        button.addEventListener('click', () => {
            const url = button.dataset.url;

            image.src = url;
            link.href = url;
            name.textContent = button.dataset.name || '';
            reference.textContent = button.dataset.reference || '';

            modal.showModal();
        });
    });

    close?.addEventListener('click', () => {
        modal.close();
    });

    modal.addEventListener('click', event => {
        if (event.target === modal) {
            modal.close();
        }
    });

    modal.addEventListener('close', () => {
        image.removeAttribute('src');
    });
});
</script>

</x-exhibitor.layout>
