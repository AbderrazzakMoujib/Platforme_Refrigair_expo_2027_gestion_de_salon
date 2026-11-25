
<x-responsable-conferences.layout :user="auth()->user()">

@php
    $search = $filters['search'] ?? '';
    $selectedStatus = $filters['status'] ?? 'all';
    $selectedType = $filters['type'] ?? 'all';
    $selectedPeriod = $filters['period'] ?? 'all';

    $stats = [
        [
            'label' => 'Toutes les notifications',
            'value' => $total,
            'icon' => 'bell',
            'bg' => 'bg-blue-100',
            'color' => 'text-blue-700',
        ],
        [
            'label' => 'Non lues',
            'value' => $unread,
            'icon' => 'inbox',
            'bg' => 'bg-red-100',
            'color' => 'text-red-700',
        ],
        [
            'label' => 'Validations',
            'value' => $validations,
            'icon' => 'clipboard-check',
            'bg' => 'bg-emerald-100',
            'color' => 'text-emerald-700',
        ],
        [
            'label' => 'Refus',
            'value' => $refusals,
            'icon' => 'inbox',
            'bg' => 'bg-red-100',
            'color' => 'text-red-700',
        ],
    ];
@endphp

<div class="space-y-6">

    {{-- HEADER --}}
    <header>
        <p class="text-xs font-bold text-blue-600">
            Conférences & Lounge
            <span class="mx-2 text-slate-300">/</span>
            Notifications
        </p>

        <div class="mt-4 flex items-center gap-4">
            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-blue-600">
                <x-admin.icon name="bell" class="size-7"/>
            </span>

            <div>
                <h1 class="text-3xl font-black text-[#0F2347]">
                    Notifications
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Suivez les nouvelles demandes, les décisions
                    et les mises à jour de réservation.
                </p>
            </div>
        </div>
    </header>

    @if(session('status'))
        <div role="status"
             class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    {{-- STATISTICS --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as $stat)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="grid size-12 place-items-center rounded-xl {{ $stat['bg'] }} {{ $stat['color'] }}">
                        <x-admin.icon :name="$stat['icon']" class="size-6"/>
                    </span>

                    <span class="rounded-full bg-blue-50 px-3 py-1 text-[10px] font-bold text-blue-700">
                        REFRIGAIR 2027
                    </span>
                </div>

                <p class="mt-4 text-sm font-bold text-[#0F2347]">
                    {{ $stat['label'] }}
                </p>

                <p class="mt-2 text-3xl font-black text-[#0F2347]">
                    {{ $stat['value'] }}
                </p>
            </article>
        @endforeach
    </section>

    {{-- NOTIFICATIONS LIST --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-wrap items-center justify-between gap-4 p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid size-12 place-items-center rounded-xl bg-blue-100 text-blue-600">
                    <x-admin.icon name="bell" class="size-6"/>
                </span>

                <div>
                    <h2 class="text-lg font-black text-[#0F2347]">
                        Liste des notifications
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Historique des notifications liées aux
                        Conférences & Lounge.
                    </p>
                </div>
            </div>

            <form method="POST"
                  action="{{ route('responsable-conferences.notifications.read-all') }}">
                @csrf

                <button type="submit"
                        @disabled($unread === 0)
                        class="rounded-xl border border-blue-500 bg-white px-4 py-3 text-sm font-bold text-blue-600 transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-40">
                    ✓ Tout marquer comme lu
                </button>
            </form>
        </div>

        {{-- FILTERS --}}
        <form method="GET"
              action="{{ route('responsable-conferences.notifications.index') }}"
              class="px-4 pb-5 sm:px-6">

            <div class="grid gap-3 rounded-xl bg-[#F1F6FD] p-4 md:grid-cols-2 xl:grid-cols-[1.5fr_1fr_1fr_1fr_auto]">

                <div>
                    <label for="notification-search"
                           class="mb-2 block text-xs font-bold text-[#0F2347]">
                        Rechercher
                    </label>

                    <input id="notification-search"
                           name="search"
                           type="search"
                           maxlength="150"
                           value="{{ $search }}"
                           placeholder="Rechercher dans les notifications..."
                           class="h-11 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm focus:border-blue-500">
                </div>

                <div>
                    <label for="notification-status"
                           class="mb-2 block text-xs font-bold text-[#0F2347]">
                        Statut
                    </label>

                    <select id="notification-status"
                            name="status"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm">
                        <option value="all" @selected($selectedStatus === 'all')>
                            Toutes
                        </option>
                        <option value="unread" @selected($selectedStatus === 'unread')>
                            Non lues
                        </option>
                        <option value="read" @selected($selectedStatus === 'read')>
                            Lues
                        </option>
                    </select>
                </div>

                <div>
                    <label for="notification-type"
                           class="mb-2 block text-xs font-bold text-[#0F2347]">
                        Type
                    </label>

                    <select id="notification-type"
                            name="type"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm">
                        <option value="all" @selected($selectedType === 'all')>
                            Tous les types
                        </option>
                        <option value="conference_request" @selected($selectedType === 'conference_request')>
                            Nouvelles demandes
                        </option>
                        <option value="conference_accepted" @selected($selectedType === 'conference_accepted')>
                            Validations
                        </option>
                        <option value="conference_rejected" @selected($selectedType === 'conference_rejected')>
                            Refus
                        </option>

                        <option value="conference_message"
    @selected($selectedType === 'conference_message')>
    Messages des conférences
</option>

<option value="conference_support"
    @selected($selectedType === 'conference_support')>
    Supports reçus
</option>

<option value="conference_support_review"
    @selected($selectedType === 'conference_support_review')>
    Vérification des supports
</option>
                    </select>
                </div>

                <div>
                    <label for="notification-period"
                           class="mb-2 block text-xs font-bold text-[#0F2347]">
                        Période
                    </label>

                    <select id="notification-period"
                            name="period"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm">
                        <option value="all" @selected($selectedPeriod === 'all')>
                            Toutes les dates
                        </option>
                        <option value="7d" @selected($selectedPeriod === '7d')>
                            7 derniers jours
                        </option>
                        <option value="30d" @selected($selectedPeriod === '30d')>
                            30 derniers jours
                        </option>
                    </select>
                </div>

                <div class="flex items-end gap-2 md:col-span-2 xl:col-span-1">
                    <button type="submit"
                            class="h-11 rounded-xl bg-[#2563EB] px-4 text-xs font-bold text-white hover:bg-blue-700">
                        Filtrer
                    </button>

                    <a href="{{ route('responsable-conferences.notifications.index') }}"
                       class="inline-flex h-11 items-center rounded-xl border border-blue-200 bg-white px-3 text-xs font-bold text-blue-700">
                        Effacer
                    </a>
                </div>
            </div>
        </form>

        {{-- ITEMS --}}
        <div class="space-y-1 px-4 pb-5 sm:px-6">

            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;

                    $type = $data['type'] ?? 'general';
                    $isUnread = $notification->read_at === null;

                    $style = match($type) {
                        'conference_request' => [
                            'bg-blue-100 text-blue-600',
                            'calendar',
                        ],
                        'conference_accepted' => [
                            'bg-emerald-100 text-emerald-700',
                            'clipboard-check',
                        ],
                        'conference_rejected' => [
                            'bg-red-100 text-red-600',
                            'inbox',
                        ],
                        default => [
                            'bg-slate-100 text-slate-600',
                            'bell',
                        ],
                    };
                @endphp

                <article @class([
                    'rounded-xl border px-4 py-4 transition hover:border-blue-200',
                    'border-blue-100 bg-blue-50/70' => $isUnread,
                    'border-slate-100 bg-white' => !$isUnread,
                ])>
                    <div class="flex items-start gap-4">

                        <span class="grid size-12 shrink-0 place-items-center rounded-xl {{ $style[0] }}">
                            <x-admin.icon :name="$style[1]" class="size-6"/>
                        </span>

                        <div class="min-w-0 flex-1">

                            <p class="font-extrabold text-[#0F2347]">
                                {{ $data['title'] ?? 'Notification' }}
                            </p>

                            <p class="mt-1 break-words text-sm leading-6 text-slate-600">
                                {{ $data['message'] ?? '' }}
                            </p>

                            <p class="mt-2 text-xs text-slate-400">
                                {{ $notification->created_at->locale('fr')->diffForHumans() }}
                                · {{ $notification->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        <div class="flex shrink-0 items-center gap-3">

                            @if($isUnread)
                                <span class="size-2.5 rounded-full bg-blue-600"
                                      title="Non lue"></span>
                            @else
                                <span class="text-xs font-semibold text-slate-400">
                                    Lue
                                </span>
                            @endif

                            <form method="POST"
                                  action="{{ route('responsable-conferences.notifications.open', $notification->id) }}">
                                @csrf

                                <button type="submit"
                                        class="rounded-lg border border-blue-200 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-50">
                                    Ouvrir
                                </button>
                            </form>
                        </div>
                    </div>
                </article>

            @empty
                <div class="rounded-xl border border-dashed border-slate-200 p-12 text-center">
                    <div class="mx-auto grid size-14 place-items-center rounded-full bg-blue-50 text-blue-600">
                        <x-admin.icon name="bell" class="size-7"/>
                    </div>

                    <p class="mt-4 font-bold text-[#0F2347]">
                        Aucune notification trouvée
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                        Les nouvelles notifications apparaîtront ici.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 px-5 py-5">

            <p class="text-xs text-slate-600">
                @if($notifications->total())
                    Affichage de
                    <strong>{{ $notifications->firstItem() }}</strong>
                    à
                    <strong>{{ $notifications->lastItem() }}</strong>
                    sur
                    <strong>{{ $notifications->total() }}</strong>
                    notifications
                @else
                    Aucun résultat
                @endif
            </p>

            {{ $notifications->links() }}
        </div>
    </section>
</div>

</x-responsable-conferences.layout>
