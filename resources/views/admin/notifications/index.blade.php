
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

@php
    $selectedData = $selectedNotification?->data ?? [];

    $selectedType = $selectedData['type'] ?? '';

    $typeLabels = [
        'equipment_request' => 'Demande d’équipements',
        'exhibitor' => 'Nouvel exposant',
    ];

    $selectedIsUnread = $selectedNotification
        && is_null($selectedNotification->read_at);
@endphp

<div class="space-y-7">

    <!-- BREADCRUMB -->
    <nav class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.index') }}"
           class="hover:text-blue-600">
            Administration
        </a>

        <span>/</span>

        <span class="font-semibold text-[#0F2347]">
            Notifications
        </span>
    </nav>

    <!-- HEADER -->
    <header class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div class="flex items-center gap-4">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <x-admin.icon name="bell" class="size-7"/>
            </span>

            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                    Notifications
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Suivez les activités importantes de la plateforme.
                </p>
            </div>

        </div>

        @if ($unreadCount > 0)

            <form method="POST"
                  action="{{ route('admin.notifications.read-all') }}">
                @csrf

                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-white px-5 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">

                    <svg class="size-5"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         stroke-linecap="round"
                         aria-hidden="true">
                        <path d="m5 12 4 4L19 6"/>
                    </svg>

                    Tout marquer comme lu
                </button>

            </form>

        @endif

    </header>

    <!-- SUCCESS MESSAGE -->
    @if (session('status'))
        <div role="status"
             class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <!-- STATISTICS -->
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <!-- TOTAL -->
        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <x-admin.icon name="bell" class="size-7"/>
            </span>

            <div>
                <p class="text-sm font-medium text-slate-500">
                    Total
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ number_format($totalCount, 0, ',', ' ') }}
                </p>
            </div>

        </article>

        <!-- UNREAD -->
        <article class="flex items-center gap-4 rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-amber-100 text-amber-600">
                <x-admin.icon name="inbox" class="size-7"/>
            </span>

            <div>
                <p class="text-sm font-medium text-slate-500">
                    Non lues
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ number_format($unreadCount, 0, ',', ' ') }}
                </p>
            </div>

        </article>

        <!-- READ -->
        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-emerald-100 text-emerald-600">

                <svg class="size-7"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     stroke-linecap="round"
                     stroke-linejoin="round"
                     aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="m7 12 3 3 5-6"/>
                </svg>

            </span>

            <div>
                <p class="text-sm font-medium text-slate-500">
                    Lues
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ number_format($readCount, 0, ',', ' ') }}
                </p>
            </div>

        </article>

        <!-- TODAY -->
        <article class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-violet-100 text-violet-600">
                <x-admin.icon name="calendar" class="size-7"/>
            </span>

            <div>
                <p class="text-sm font-medium text-slate-500">
                    Aujourd'hui
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ number_format($todayCount, 0, ',', ' ') }}
                </p>
            </div>

        </article>

    </section>

    <!-- MAIN CARD -->
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">

        <!-- STATUS TABS -->
        <div class="flex flex-wrap gap-2">

            @foreach ([
                'all' => ['Toutes', $totalCount],
                'unread' => ['Non lues', $unreadCount],
                'read' => ['Lues', $readCount],
            ] as $key => $tab)

                <a href="{{ route(
                        'admin.notifications.index',
                        array_merge(
                            request()->except(['filter', 'selected', 'page']),
                            ['filter' => $key]
                        )
                    ) }}"
                   @class([
                       'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                       'bg-[#2563EB] text-white' => $filter === $key,
                       'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50' => $filter !== $key,
                   ])>

                    @if ($key === 'unread')
                        <span class="size-2 rounded-full bg-red-500"></span>
                    @elseif ($key === 'read')
                        <span class="size-2 rounded-full bg-emerald-500"></span>
                    @endif

                    {{ $tab[0] }} ({{ $tab[1] }})

                </a>

            @endforeach

        </div>

        <!-- FILTERS -->
        <form method="GET"
              action="{{ route('admin.notifications.index') }}"
              class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_minmax(0,1fr)_auto_auto]">

            <input type="hidden"
                   name="filter"
                   value="{{ $filter }}">

            <!-- SEARCH -->
            <div class="relative">

                <label for="search" class="sr-only">
                    Rechercher une notification
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
                       maxlength="255"
                       value="{{ request('search') }}"
                       placeholder="Rechercher une notification..."
                       class="w-full rounded-xl border border-slate-200 py-3 pl-10 pr-4 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- TYPE -->
            <div>
                <label for="type" class="sr-only">
                    Type
                </label>

                <select id="type"
                        name="type"
                        class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">

                    <option value="">
                        Tous les types
                    </option>

                    <option value="equipment_request"
                        @selected(request('type') === 'equipment_request')>
                        Demandes d'équipements
                    </option>

                    <option value="exhibitor"
                        @selected(request('type') === 'exhibitor')>
                        Nouveaux exposants
                    </option>

                </select>
            </div>

            <!-- DATE -->
            <div>
                <label for="period" class="sr-only">
                    Période
                </label>

                <select id="period"
                        name="period"
                        class="w-full rounded-xl border border-slate-200 px-3 py-3 text-sm">

                    <option value="all"
                        @selected($period === 'all')>
                        Toutes les dates
                    </option>

                    <option value="today"
                        @selected($period === 'today')>
                        Aujourd'hui
                    </option>

                    <option value="7d"
                        @selected($period === '7d')>
                        7 derniers jours
                    </option>

                    <option value="30d"
                        @selected($period === '30d')>
                        30 derniers jours
                    </option>

                </select>
            </div>

            <button type="submit"
                    class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                Filtrer
            </button>

            <a href="{{ route('admin.notifications.index') }}"
               class="rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">
                Réinitialiser
            </a>

        </form>

        <!-- LIST + DETAILS -->
        <div class="mt-6 grid items-start gap-5 xl:grid-cols-[minmax(0,1.6fr)_minmax(300px,1fr)]">

            <!-- LIST -->
            <div class="min-w-0 overflow-hidden rounded-xl border border-slate-200">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">

                    <h2 class="font-bold text-[#0F2347]">
                        Notifications récentes
                    </h2>

                    <span class="text-xs font-semibold text-slate-500">
                        {{ $notifications->total() }} résultat(s)
                    </span>

                </div>

                <div class="divide-y divide-slate-100">

                    @forelse ($notifications as $notification)

                        @php
                            $data = $notification->data ?? [];

                            $isUnread = is_null($notification->read_at);

                            $isRequest = ($data['type'] ?? '') === 'equipment_request';

                            $isSelected = $selectedNotification
                                && $selectedNotification->id === $notification->id;
                        @endphp

                        <article @class([
                            'relative flex gap-3 px-4 py-4 transition sm:px-5',
                            'bg-blue-50/70' => $isSelected,
                            'bg-blue-50/30' => $isUnread && !$isSelected,
                            'bg-white hover:bg-slate-50' => !$isUnread && !$isSelected,
                        ])>

                            <!-- SELECT NOTIFICATION -->
                            <a href="{{ route(
                                    'admin.notifications.index',
                                    array_merge(
                                        request()->except(['selected']),
                                        ['selected' => $notification->id]
                                    )
                                ) }}"
                               class="absolute inset-0 z-0"
                               aria-label="Afficher {{ $data['title'] ?? 'la notification' }}">
                            </a>

                            <!-- ICON -->
                            <div @class([
                                'pointer-events-none relative z-10 grid size-12 shrink-0 place-items-center rounded-xl',
                                'bg-blue-100 text-blue-600' => $isRequest,
                                'bg-violet-100 text-violet-600' => !$isRequest,
                            ])>
                                <x-admin.icon
                                    :name="$isRequest ? 'box' : 'users'"
                                    class="size-6"
                                />
                            </div>

                            <!-- CONTENT -->
                            <div class="pointer-events-none relative z-10 min-w-0 flex-1">

                                <div class="flex flex-wrap items-center gap-2">

                                    <h3 class="text-sm font-bold text-[#0F2347]">
                                        {{ $data['title'] ?? 'Notification' }}
                                    </h3>

                                    @if ($isUnread)
                                        <span class="size-2 shrink-0 rounded-full bg-red-500"
                                              title="Non lue">
                                        </span>
                                    @endif

                                </div>

                                <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-600">
                                    {{ $data['message'] ?? 'Une nouvelle activité a été enregistrée.' }}
                                </p>

                                <p class="mt-2 text-xs text-slate-400">
                                    {{ $notification->created_at?->format('d/m/Y à H:i') }}
                                </p>

                            </div>

                            <span class="pointer-events-none relative z-10 self-center text-blue-500">
                                <svg class="size-5"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8"
                                     aria-hidden="true">
                                    <path d="m9 18 6-6-6-6"/>
                                </svg>
                            </span>

                        </article>

                    @empty

                        <div class="px-6 py-14 text-center">

                            <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-blue-50 text-blue-600">
                                <x-admin.icon name="bell" class="size-8"/>
                            </span>

                            <h3 class="mt-4 font-bold text-[#0F2347]">
                                Aucune notification
                            </h3>

                            <p class="mt-2 text-sm text-slate-500">
                                Les nouvelles activités apparaîtront ici.
                            </p>

                        </div>

                    @endforelse

                </div>

                <!-- PAGINATION -->
                <div class="border-t border-slate-100 px-5 py-4">

                    <p class="mb-3 text-xs text-slate-500">

                        @if ($notifications->total() > 0)
                            Affichage de {{ $notifications->firstItem() }}
                            à {{ $notifications->lastItem() }}
                            sur {{ $notifications->total() }} notifications
                        @else
                            Aucun résultat
                        @endif

                    </p>

                    @if ($notifications->hasPages())
                        {{ $notifications->links() }}
                    @endif

                </div>

            </div>

            <!-- DETAILS -->
            <aside class="overflow-hidden rounded-xl border border-slate-200 bg-white"
                   aria-label="Détails de la notification">

                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">

                    <h2 class="font-bold text-[#0F2347]">
                        Détails de la notification
                    </h2>

                    <a href="{{ route(
                            'admin.notifications.index',
                            request()->except('selected')
                        ) }}"
                       class="text-slate-400 hover:text-slate-700"
                       title="Fermer le détail"
                       aria-label="Fermer le détail">

                        <svg class="size-5"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             aria-hidden="true">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>

                    </a>

                </div>

                @if ($selectedNotification)

                    <div class="space-y-5 p-5">

                        <!-- TITLE -->
                        <div class="flex items-start gap-3">

                            <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-600">
                                <x-admin.icon
                                    :name="$selectedType === 'equipment_request' ? 'box' : 'users'"
                                    class="size-6"
                                />
                            </span>

                            <div>

                                <h3 class="font-bold text-[#0F2347]">
                                    {{ $selectedData['title'] ?? 'Notification' }}
                                </h3>

                                <p class="mt-2 text-xs text-slate-500">
                                    {{ $selectedNotification->created_at?->format('d/m/Y à H:i') }}
                                </p>

                                <span class="mt-2 inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                    {{ $typeLabels[$selectedType] ?? 'Notification' }}
                                </span>

                            </div>

                        </div>

                        <!-- MESSAGE -->
                        <div class="rounded-xl bg-[#F5F7FB] p-4">

                            <p class="whitespace-pre-line text-sm leading-7 text-slate-700">
                                {{ $selectedData['message'] ?? 'Une nouvelle activité a été enregistrée.' }}
                            </p>

                        </div>

                        <!-- INFO -->
                        <div class="space-y-3 rounded-xl border border-slate-100 p-4">

                            <div class="flex justify-between gap-3 text-sm">
                                <span class="text-slate-500">
                                    Catégorie
                                </span>

                                <span class="text-right font-semibold text-[#0F2347]">
                                    {{ $typeLabels[$selectedType] ?? 'Générale' }}
                                </span>
                            </div>

                            <div class="flex justify-between gap-3 text-sm">
                                <span class="text-slate-500">
                                    Reçue le
                                </span>

                                <span class="text-right font-semibold text-[#0F2347]">
                                    {{ $selectedNotification->created_at?->format('d/m/Y') }}
                                </span>
                            </div>

                            <div class="flex justify-between gap-3 text-sm">
                                <span class="text-slate-500">
                                    Statut
                                </span>

                                @if ($selectedIsUnread)
                                    <span class="font-semibold text-red-600">
                                        Non lue
                                    </span>
                                @else
                                    <span class="font-semibold text-emerald-600">
                                        Lue
                                    </span>
                                @endif

                            </div>

                        </div>

                        <!-- ACTIONS -->
                        <div class="flex flex-col gap-3 sm:flex-row xl:flex-col 2xl:flex-row">

                            <form method="POST"
                                  action="{{ route('admin.notifications.open', $selectedNotification->id) }}"
                                  class="flex-1">
                                @csrf

                                <button type="submit"
                                        class="w-full rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                                    Consulter
                                </button>

                            </form>

                            @if ($selectedIsUnread)

                                <form method="POST"
                                      action="{{ route('admin.notifications.read', $selectedNotification->id) }}"
                                      class="flex-1">

                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                            class="w-full rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        Marquer comme lu
                                    </button>

                                </form>

                            @endif

                        </div>

                    </div>

                @else

                    <div class="px-6 py-16 text-center">

                        <span class="mx-auto grid size-14 place-items-center rounded-xl bg-slate-100 text-slate-400">
                            <x-admin.icon name="bell" class="size-7"/>
                        </span>

                        <p class="mt-4 text-sm font-semibold text-slate-600">
                            Sélectionnez une notification pour afficher ses détails.
                        </p>

                    </div>

                @endif

            </aside>

        </div>

    </section>

</div>

</x-admin.layout>
