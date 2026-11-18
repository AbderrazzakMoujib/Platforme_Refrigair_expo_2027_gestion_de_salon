
@props([
    'user',
    'indexRoute',
    'openRoute',
])

@php
    $notificationId = 'notifications-' . uniqid();

    $openUrlTemplate = route(
        $openRoute,
        ['notification' => '__NOTIFICATION_ID__']
    );
@endphp

<div
    id="{{ $notificationId }}"
    data-live-notifications
    data-endpoint="{{ route('notifications.live') }}"
    data-open-template="{{ $openUrlTemplate }}"
    class="relative"
>
    <!-- BELL -->
    <button
        type="button"
        data-bell
        aria-label="Ouvrir les notifications"
        aria-haspopup="true"
        aria-expanded="false"
        class="relative grid size-10 place-items-center rounded-xl text-slate-600 transition hover:bg-blue-50 hover:text-blue-700"
    >
        <svg
            class="size-6"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            stroke-linejoin="round"
            aria-hidden="true"
        >
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
            <path d="M10 21h4"/>
        </svg>

        <span
            data-count
            class="absolute -right-1 -top-1 hidden min-w-5 rounded-full bg-red-600 px-1 py-0.5 text-center text-[10px] font-bold text-white"
        ></span>
    </button>

    <!-- DROPDOWN -->
    <div
        data-dropdown
        class="absolute right-0 top-12 z-50 hidden w-[min(340px,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
    >
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4">
            <h2 class="font-extrabold text-[#0F2347]">
                Notifications
            </h2>

            <span
                data-dropdown-count
                class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700"
            >
                0 non lue
            </span>
        </div>

        <div data-list class="max-h-96 overflow-y-auto">
            <p class="px-4 py-8 text-center text-sm text-slate-500">
                Chargement...
            </p>
        </div>

        <a
            href="{{ route($indexRoute) }}"
            class="block border-t border-slate-100 px-4 py-3 text-center text-sm font-bold text-blue-700 hover:bg-blue-50"
        >
            Voir toutes les notifications
        </a>
    </div>

    <!-- TOAST -->
    <div
        data-toast
        role="status"
        aria-live="polite"
        class="fixed bottom-5 right-5 z-[100] hidden w-[min(360px,calc(100vw-2rem))] rounded-2xl border border-blue-100 bg-white p-4 shadow-2xl"
    >
        <div class="flex items-start gap-3">

            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-600">
                <svg class="size-5" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor"
                     stroke-width="1.8" aria-hidden="true">
                    <path d="M4 5h16v13H8l-4 3V5Z"/>
                    <path d="M8 10h8M8 14h5"/>
                </svg>
            </span>

            <div class="min-w-0 flex-1">
                <p data-toast-title class="font-bold text-[#0F2347]"></p>
                <p data-toast-message class="mt-1 break-words text-sm text-slate-600"></p>
            </div>

            <button
                type="button"
                data-toast-close
                class="text-lg text-slate-400 hover:text-slate-700"
                aria-label="Fermer la notification"
            >
                ×
            </button>
        </div>
    </div>
</div>

<script>
(() => {
    const root = document.getElementById(@json($notificationId));

    if (!root) return;

    const bell = root.querySelector('[data-bell]');
    const badge = root.querySelector('[data-count]');
    const dropdown = root.querySelector('[data-dropdown]');
    const dropdownCount = root.querySelector(
        '[data-dropdown-count]'
    );
    const list = root.querySelector('[data-list]');

    const toast = root.querySelector('[data-toast]');
    const toastTitle = root.querySelector('[data-toast-title]');
    const toastMessage = root.querySelector('[data-toast-message]');
    const toastClose = root.querySelector('[data-toast-close]');

    const endpoint = root.dataset.endpoint;
    const openTemplate = root.dataset.openTemplate;

    const csrf = document.querySelector(
        'meta[name="csrf-token"]'
    )?.content ?? @json(csrf_token());

    let initialized = false;
    let lastSeenId = null;
    let loading = false;
    let toastTimer = null;

    function showToast(item) {
        if (toastTimer) {
            clearTimeout(toastTimer);
        }

        toastTitle.textContent = item.title;
        toastMessage.textContent = item.message;

        toast.classList.remove('hidden');

        toastTimer = setTimeout(() => {
            toast.classList.add('hidden');
        }, 6000);
    }

    function createNotification(item) {
        const form = document.createElement('form');
        form.method = 'POST';

        // URL créée par Laravel, pas par les données
        // reçues dans la notification.
        form.action = openTemplate.replace(
            '__NOTIFICATION_ID__',
            encodeURIComponent(item.id)
        );

        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = csrf;

        const button = document.createElement('button');
        button.type = 'submit';

        button.className = [
            'flex w-full items-start gap-3 border-b',
            'border-slate-100 px-4 py-4 text-left',
            'transition hover:bg-blue-50',
            item.unread ? 'bg-blue-50/50' : 'bg-white',
        ].join(' ');

        const dot = document.createElement('span');
        dot.className = item.unread
            ? 'mt-1.5 size-2 shrink-0 rounded-full bg-blue-600'
            : 'mt-1.5 size-2 shrink-0 rounded-full bg-slate-200';

        const body = document.createElement('span');
        body.className = 'min-w-0 flex-1';

        const title = document.createElement('strong');
        title.className = 'block text-sm text-[#0F2347]';
        title.textContent = item.title;

        const message = document.createElement('span');
        message.className =
            'mt-1 block break-words text-xs leading-5 text-slate-600';
        message.textContent = item.message;

        const date = document.createElement('span');
        date.className =
            'mt-2 block text-[11px] text-slate-400';
        date.textContent = item.created_at || '';

        body.append(title, message, date);
        button.append(dot, body);
        form.append(token, button);

        return form;
    }

    function render(data) {
        const count = Number(data.unread_count) || 0;
        const notifications = Array.isArray(data.notifications)
            ? data.notifications
            : [];

        badge.textContent = count > 99 ? '99+' : String(count);
        badge.classList.toggle('hidden', count === 0);

        dropdownCount.textContent =
            `${count} non lue${count > 1 ? 's' : ''}`;

        const fragment = document.createDocumentFragment();

        if (!notifications.length) {
            const empty = document.createElement('p');
            empty.className =
                'px-4 py-8 text-center text-sm text-slate-500';
            empty.textContent = 'Aucune notification.';
            fragment.appendChild(empty);
        } else {
            notifications.forEach(item => {
                fragment.appendChild(createNotification(item));
            });
        }

        list.replaceChildren(fragment);

        // Pas de toast au premier chargement :
        // uniquement pour une notification qui arrive
        // pendant que l'utilisateur est sur la page.
        const newest = notifications[0] || null;

        if (initialized && newest && newest.id !== lastSeenId) {
            const previous = notifications.findIndex(
                item => item.id === lastSeenId
            );

            // Toast seulement pour les nouvelles notifications
            // réellement arrivées après la précédente.
            if (previous > 0) {
                const arrivals = notifications
                    .slice(0, previous)
                    .filter(item => item.unread);

                if (arrivals.length) {
                    showToast(arrivals[0]);
                }
            }
        }

        lastSeenId = newest?.id ?? null;
        initialized = true;
    }

    async function refresh() {
        if (loading || document.hidden) return;

        loading = true;

        try {
            const response = await fetch(endpoint, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) return;

            const data = await response.json();
            render(data);
        } catch (error) {
            // On conserve l'état affiché en cas de coupure réseau.
        } finally {
            loading = false;
        }
    }

    bell.addEventListener('click', () => {
        const isClosed = dropdown.classList.contains('hidden');

        dropdown.classList.toggle('hidden', !isClosed);
        bell.setAttribute('aria-expanded', String(isClosed));

        if (isClosed) {
            refresh();
        }
    });

    document.addEventListener('click', event => {
        if (!root.contains(event.target)) {
            dropdown.classList.add('hidden');
            bell.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            dropdown.classList.add('hidden');
            bell.setAttribute('aria-expanded', 'false');
        }
    });

    toastClose.addEventListener('click', () => {
        toast.classList.add('hidden');
    });

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refresh();
    });

    refresh();

    // Polling toutes les 5 secondes.
    setInterval(refresh, 5000);
})();
</script>
