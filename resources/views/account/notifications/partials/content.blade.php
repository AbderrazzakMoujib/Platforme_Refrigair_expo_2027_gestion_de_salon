
<div class="space-y-6">

    <!-- BREADCRUMB -->
    <nav class="flex items-center gap-2 text-sm text-slate-500">

        <a href="{{ route($isExhibitor ? 'exposant.index' : 'commercial.index') }}"
           class="transition hover:text-blue-600">
            Vue globale
        </a>

        <span>/</span>

        <span class="font-semibold text-[#0F2347]">
            Notifications
        </span>

    </nav>

    <!-- HEADER -->
    <header class="flex flex-wrap items-center justify-between gap-4">

        <div class="flex items-center gap-4">

            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-blue-100 text-[#2563EB]">

                <svg class="size-7"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round"
                     aria-hidden="true">

                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                    <path d="M10 21h4"/>

                </svg>

            </span>

            <div>

                <h1 class="text-2xl font-extrabold tracking-tight text-[#0F2347] sm:text-3xl">
                    Notifications
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Retrouvez toutes les activités de votre espace.
                </p>

            </div>

        </div>

        @if ($unreadCount > 0)

            <form method="POST"
                  action="{{ route($notificationsRoute . '.read-all') }}">

                @csrf

                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-white px-5 py-3 text-sm font-bold text-blue-700 transition hover:bg-blue-50">

                    <svg class="size-5"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         stroke-linecap="round"
                         stroke-linejoin="round"
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
    <section class="grid gap-4 sm:grid-cols-3">

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
                    {{ $notifications->total() }}
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
                    {{ $unreadCount }}
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
                    <path d="m8 12 3 3 5-6"/>

                </svg>

            </span>

            <div>

                <p class="text-sm font-medium text-slate-500">
                    Lues
                </p>

                <p class="mt-1 text-3xl font-extrabold text-[#0F2347]">
                    {{ max(0, $notifications->total() - $unreadCount) }}
                </p>

            </div>

        </article>

    </section>

    <!-- NOTIFICATIONS LIST -->
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-5 sm:px-6">

            <div>

                <h2 class="text-lg font-extrabold text-[#0F2347]">

                    Activités récentes

                </h2>

                <p class="mt-1 text-xs text-slate-500">

                    Cliquez sur une notification pour consulter les détails.

                </p>

            </div>

            @if ($unreadCount > 0)

                <span class="rounded-full bg-blue-100 px-3 py-1.5 text-xs font-bold text-blue-700">

                    {{ $unreadCount }} non lue(s)

                </span>

            @endif

        </div>

        <div class="divide-y divide-slate-100">

            @forelse ($notifications as $notification)

                @php
    $data = $notification->data ?? [];

    $unread = is_null($notification->read_at);

    $event = $data['event'] ?? null;

    $ticketId = $data['ticket_id'] ?? null;

    $ticket = null;

    if (
        ($data['type'] ?? null) === 'support'
        && is_scalar($ticketId)
        && ctype_digit((string) $ticketId)
    ) {
        $ticket = $notificationTickets->get((int) $ticketId);
    }

    $senderExhibitor = $ticket?->exhibitor;
    $senderCommercial = $ticket?->commercial;

    $fromExhibitor = in_array(
        $event,
        ['created', 'exhibitor_reply'],
        true
    );

    $fromCommercial = in_array(
        $event,
        ['commercial_reply', 'status_updated'],
        true
    );

    $avatarUrl = null;
    $senderName = null;

    if ($fromExhibitor && $senderExhibitor) {

        $senderName = $senderExhibitor->company_name;

        $avatarUrl = $senderExhibitor->logo
            ? \Illuminate\Support\Facades\Storage::disk('public')
                ->url($senderExhibitor->logo)
            : null;

    } elseif ($fromCommercial && $senderCommercial) {

        $senderName = $senderCommercial->name;

        $avatarUrl = $senderCommercial->profile_photo_url;

    }

    $initials = mb_strtoupper(
        mb_substr(trim($senderName ?? ''), 0, 2)
    ) ?: '?';

    $iconClasses = match ($event) {
        'status_updated' => 'bg-emerald-100 text-emerald-700',
        'created' => 'bg-amber-100 text-amber-700',
        default => 'bg-blue-100 text-blue-700',
    };
@endphp

                <form method="POST"
                      action="{{ route($notificationsRoute . '.open', $notification->id) }}">

                    @csrf

                    <button type="submit"
                            @class([
                                'flex w-full items-start gap-4 px-5 py-5 text-left transition sm:px-6',
                                'bg-blue-50/40 hover:bg-blue-50' => $unread,
                                'bg-white hover:bg-slate-50' => !$unread,
                            ])>

                        <!-- NOTIFICATION SENDER AVATAR -->

<span
    class="relative grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl border border-slate-200 bg-white sm:size-14"
    title="{{ $senderName ?? 'Notification' }}"
>

    @if ($avatarUrl)

        <img
            src="{{ $avatarUrl }}"
            alt="{{ $senderName }}"
            loading="lazy"
            @class([
                'h-full w-full',
                'object-contain p-1.5' => $fromExhibitor,
                'object-cover' => !$fromExhibitor,
            ])
            onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
        >

        <span class="hidden text-lg font-extrabold text-blue-700">
            {{ $initials }}
        </span>

    @elseif ($senderName)

        <span class="grid h-full w-full place-items-center bg-blue-50 text-lg font-extrabold text-blue-700">

            {{ $initials }}

        </span>

    @else

        <span class="grid h-full w-full place-items-center {{ $iconClasses }}">

            <x-admin.icon
                name="bell"
                class="size-6"
            />

        </span>

    @endif

</span>

                        <!-- CONTENT -->
                        <span class="min-w-0 flex-1">

                            <span class="flex flex-wrap items-center gap-2">

                                <strong class="break-words text-sm font-bold text-[#0F2347]">

                                    {{ $data['title'] ?? 'Notification' }}

                                </strong>

                                @if ($unread)

                                    <span class="size-2 shrink-0 rounded-full bg-blue-600"
                                          title="Non lue">
                                    </span>

                                @endif

                            </span>

                            <span class="mt-2 block break-words text-sm leading-6 text-slate-600">

                                {{ $data['message'] ?? 'Une nouvelle activité a été enregistrée.' }}

                            </span>

                            <span class="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-400">

                                <span>

                                    {{ $notification->created_at?->format('d/m/Y à H:i') }}

                                </span>

                                @if ($unread)

                                    <span class="font-semibold text-blue-600">

                                        Non lue

                                    </span>

                                @else

                                    <span class="font-medium text-emerald-600">

                                        Lue

                                    </span>

                                @endif

                            </span>

                        </span>

                        <!-- ARROW -->
                        <span class="self-center text-blue-500">

                            <svg class="size-5"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 stroke-linecap="round"
                                 stroke-linejoin="round"
                                 aria-hidden="true">

                                <path d="m9 18 6-6-6-6"/>

                            </svg>

                        </span>

                    </button>

                </form>

            @empty

                <!-- EMPTY STATE -->
                <div class="px-6 py-16 text-center">

                    <span class="mx-auto grid size-20 place-items-center rounded-2xl bg-blue-50 text-blue-600">

                        <x-admin.icon name="bell" class="size-9"/>

                    </span>

                    <h3 class="mt-5 text-lg font-extrabold text-[#0F2347]">

                        Aucune notification

                    </h3>

                    <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">

                        Vous retrouverez ici les nouvelles activités
                        et les réponses à vos demandes d'assistance.

                    </p>

                </div>

            @endforelse

        </div>

        <!-- PAGINATION -->
        @if ($notifications->hasPages())

            <div class="border-t border-slate-100 px-5 py-4">

                {{ $notifications->links() }}

            </div>

        @endif

    </section>

</div>
