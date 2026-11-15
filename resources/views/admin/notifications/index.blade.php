
<x-admin.layout :admin="$admin" :admin-initials="$adminInitials">

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <p class="text-sm font-semibold text-blue-600">
                Administration / Notifications
            </p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">
                Centre de notifications
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Suivez les nouvelles inscriptions et demandes d'équipements.
            </p>
        </div>

        @if ($unreadCount > 0)
            <form method="POST"
                  action="{{ route('admin.notifications.read-all') }}">
                @csrf
                <button type="submit"
                        class="rounded-xl bg-[#2563EB] px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
                    Tout marquer comme lu
                </button>
            </form>
        @endif
    </div>

    @if (session('status'))
        <div role="status"
             class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <section class="mt-7 grid gap-4 sm:grid-cols-3">
        <x-admin.stat-card
            label="Total notifications"
            :value="$totalCount"
            icon="bell"
            description="Historique des notifications."
        />

        <x-admin.stat-card
            label="Non lues"
            :value="$unreadCount"
            icon="inbox"
            description="Notifications à consulter."
        />

        <x-admin.stat-card
            label="Lues"
            :value="$readCount"
            icon="bell"
            description="Notifications consultées."
        />
    </section>

    <section class="mt-7 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-bold text-slate-900">
                Notifications récentes
            </h2>

            <div class="flex flex-wrap gap-2">
                @foreach ([
                    'all' => 'Toutes',
                    'unread' => 'Non lues',
                    'read' => 'Lues',
                ] as $key => $label)
                    <a href="{{ route('admin.notifications.index', ['filter' => $key]) }}"
                       @class([
                           'rounded-lg px-3 py-2 text-xs font-semibold transition',
                           'bg-[#2563EB] text-white' => $filter === $key,
                           'bg-slate-100 text-slate-600 hover:bg-slate-200' => $filter !== $key,
                       ])>
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                    $isRequest = ($data['type'] ?? '') === 'equipment_request';
                @endphp

                <article @class([
                    'flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-start',
                    'bg-blue-50/50' => $isUnread,
                    'bg-white' => !$isUnread,
                ])>
                    <div class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-700">
                        <x-admin.icon
                            :name="$isRequest ? 'inbox' : 'users'"
                            class="size-5"
                        />
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <h3 class="font-bold text-slate-800">
                                {{ $data['title'] ?? 'Notification' }}
                            </h3>

                            @if ($isUnread)
                                <span class="rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-semibold text-blue-700">
                                    Nouvelle
                                </span>
                            @endif
                        </div>

                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $data['message'] ?? 'Une activité a été enregistrée.' }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            {{ $notification->created_at?->format('d/m/Y à H:i') }}
                        </p>
                    </div>

                    <div class="flex shrink-0 flex-wrap gap-2">
                        <form method="POST"
                              action="{{ route('admin.notifications.open', $notification->id) }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-lg bg-[#2563EB] px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700">
                                Consulter
                            </button>
                        </form>

                        @if ($isUnread)
                            <form method="POST"
                                  action="{{ route('admin.notifications.read', $notification->id) }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                        class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                    Marquer comme lue
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="px-6 py-16 text-center">
                    <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-blue-50 text-blue-600">
                        <x-admin.icon name="bell" class="size-7" />
                    </div>

                    <h3 class="mt-5 font-bold text-slate-800">
                        Aucune notification
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        Les nouvelles activités apparaîtront ici.
                    </p>
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="border-t border-slate-100 px-6 py-5">
                {{ $notifications->links() }}
            </div>
        @endif
    </section>

</x-admin.layout>
