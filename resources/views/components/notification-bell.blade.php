
@props(['user'])

@php
    $notificationPrefix = match ($user->role->value) {
        'admin' => 'admin.notifications',
        'commercial' => 'commercial.notifications',
        'exposant' => 'exhibitor.notifications',
    };

    $initialUnread = $user
        ->unreadNotifications()
        ->count();
@endphp

<div id="live-notification-component"
     class="relative"
     data-endpoint="{{ route('notifications.live') }}"
     data-csrf="{{ csrf_token() }}">

    <!-- BELL BUTTON -->
    <button type="button"
            id="notification-toggle"
            aria-label="Notifications"
            aria-expanded="false"
            aria-controls="notification-dropdown"
            class="relative grid size-11 place-items-center rounded-xl text-slate-600 transition hover:bg-blue-50 hover:text-blue-700">

        <svg class="size-6"
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

        <!-- UNREAD BADGE -->
        <span id="notification-count"
              aria-live="polite"
              @class([
                  'absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white ring-2 ring-white',
                  'hidden' => $initialUnread === 0,
              ])>

            {{ $initialUnread > 99 ? '99+' : $initialUnread }}

        </span>

    </button>

    <!-- DROPDOWN -->
    <div id="notification-dropdown"
         class="fixed inset-x-3 top-16 z-50 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl sm:absolute sm:inset-x-auto sm:right-0 sm:top-14 sm:w-96">

        <!-- HEADER -->
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5">

            <div>
                <h2 class="text-lg font-extrabold text-[#0F2347]">
                    Notifications
                </h2>

                <p id="notification-subtitle"
                   class="mt-1 text-xs text-slate-500">
                    {{ $initialUnread }} non lue(s)
                </p>
            </div>

            <span class="grid size-10 place-items-center rounded-xl bg-blue-50 text-blue-600">

                <svg class="size-5"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="1.8"
                     aria-hidden="true">
                    <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                    <path d="M10 21h4"/>
                </svg>

            </span>

        </div>

        <!-- NOTIFICATIONS LIST -->
        <div id="notification-list"
             class="max-h-96 divide-y divide-slate-100 overflow-y-auto">

            <div class="p-8 text-center text-sm text-slate-500">
                Chargement des notifications...
            </div>

        </div>

        <!-- FOOTER -->
        <div class="space-y-3 border-t border-slate-100 p-4">

            <form method="POST"
                  action="{{ route($notificationPrefix . '.read-all') }}">
                @csrf

                <button type="submit"
                        class="w-full rounded-xl border border-blue-200 px-4 py-2.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-50">

                    Tout marquer comme lu

                </button>
            </form>

            <a href="{{ route($notificationPrefix . '.index') }}"
               class="block rounded-xl bg-[#2563EB] px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-blue-700">

                Voir toutes les notifications

            </a>

        </div>

    </div>

</div>

<!-- TOAST NOTIFICATION -->
<div id="notification-toast"
     role="status"
     aria-live="polite"
     class="fixed bottom-5 right-5 z-[9999] hidden w-[calc(100%-2.5rem)] max-w-sm rounded-2xl border border-blue-200 bg-white p-5 shadow-2xl">

    <div class="flex items-start gap-3">

        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-blue-100 text-blue-600">

            <svg class="size-6"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.8"
                 aria-hidden="true">
                <path d="M4 5h16v13H8l-4 3V5Z"/>
                <path d="M8 10h8M8 14h5"/>
            </svg>

        </span>

        <div class="min-w-0 flex-1">

            <p class="text-[11px] font-bold uppercase tracking-wide text-blue-600">
                Nouvelle notification
            </p>

            <h3 id="toast-title"
                class="mt-1 text-sm font-extrabold text-[#0F2347]">
            </h3>

            <p id="toast-message"
               class="mt-2 text-xs leading-5 text-slate-600">
            </p>

        </div>

        <button type="button"
                id="toast-close"
                aria-label="Fermer la notification"
                class="text-xl text-slate-400 hover:text-slate-700">
            ×
        </button>

    </div>

    <form id="toast-form"
          method="POST"
          class="mt-4">
        @csrf

        <button type="submit"
                class="w-full rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">

            Consulter

        </button>
    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const component = document.getElementById(
        'live-notification-component'
    );

    if (!component) return;

    const endpoint = component.dataset.endpoint;
    const csrf = component.dataset.csrf;

    const toggle = document.getElementById('notification-toggle');
    const dropdown = document.getElementById('notification-dropdown');
    const badge = document.getElementById('notification-count');
    const subtitle = document.getElementById('notification-subtitle');
    const list = document.getElementById('notification-list');

    const toast = document.getElementById('notification-toast');
    const toastTitle = document.getElementById('toast-title');
    const toastMessage = document.getElementById('toast-message');
    const toastForm = document.getElementById('toast-form');
    const toastClose = document.getElementById('toast-close');

    let knownIds = new Set();
    let initialized = false;
    let isFetching = false;
    let toastTimeout;

    function updateCount(value) {
        const count = Number(value) || 0;

        badge.textContent = count > 99 ? '99+' : count;

        badge.classList.toggle('hidden', count === 0);

        subtitle.textContent = `${count} non lue(s)`;

        toggle.setAttribute(
            'aria-label',
            `Notifications : ${count} non lue(s)`
        );
    }

    function createNotification(item) {
        const form = document.createElement('form');

        form.method = 'POST';
        form.action = item.open_url;

        const token = document.createElement('input');

        token.type = 'hidden';
        token.name = '_token';
        token.value = csrf;

        form.appendChild(token);

        const button = document.createElement('button');

        button.type = 'submit';

        button.className =
            'flex w-full items-start gap-3 p-4 text-left ' +
            'transition hover:bg-blue-50';

        if (item.unread) {
            button.classList.add('bg-blue-50/50');
        }

        const icon = document.createElement('span');

        icon.className =
            'grid size-10 shrink-0 place-items-center ' +
            'rounded-xl bg-blue-100 text-lg text-blue-600';

        icon.textContent = item.type === 'support'
            ? '✉'
            : '▤';

        const content = document.createElement('div');

        content.className = 'min-w-0 flex-1';

        const title = document.createElement('p');

        title.className =
            'text-sm font-bold text-[#0F2347]';

        title.textContent = item.title;

        const message = document.createElement('p');

        message.className =
            'mt-1 break-words text-xs leading-5 text-slate-600';

        message.textContent = item.message;

        const time = document.createElement('p');

        time.className =
            'mt-2 text-[11px] text-slate-400';

        time.textContent = item.time;

        content.append(title, message, time);

        button.append(icon, content);

        if (item.unread) {
            const dot = document.createElement('span');

            dot.className =
                'mt-2 size-2 shrink-0 rounded-full bg-red-500';

            dot.setAttribute('aria-label', 'Non lue');

            button.appendChild(dot);
        }

        form.appendChild(button);

        return form;
    }

    function renderNotifications(items) {
        const fragment = document.createDocumentFragment();

        if (!items.length) {
            const empty = document.createElement('div');

            empty.className =
                'p-10 text-center text-sm text-slate-500';

            empty.textContent = 'Aucune notification';

            fragment.appendChild(empty);
        } else {
            items.forEach(item => {
                fragment.appendChild(
                    createNotification(item)
                );
            });
        }

        list.replaceChildren(fragment);
    }

    function hideToast() {
        clearTimeout(toastTimeout);
        toast.classList.add('hidden');
    }

    function showToast(item) {
        clearTimeout(toastTimeout);

        toastTitle.textContent = item.title;
        toastMessage.textContent = item.message;

        toastForm.action = item.open_url;

        toast.classList.remove('hidden');

        toastTimeout = setTimeout(() => {
            hideToast();
        }, 8000);
    }

    async function fetchNotifications() {
        if (isFetching) return;

        isFetching = true;

        try {
            const response = await fetch(endpoint, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',

                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) return;

            const data = await response.json();

            const items = Array.isArray(data.notifications)
                ? data.notifications
                : [];

            updateCount(data.unread_count);

            renderNotifications(items);

            if (initialized) {
                const newNotifications = items.filter(item =>
                    item.unread && !knownIds.has(item.id)
                );

                if (newNotifications.length > 0) {
                    showToast(newNotifications[0]);
                }
            }

            items.forEach(item => {
                knownIds.add(item.id);
            });

            initialized = true;

        } catch (error) {
            console.error(
                'Erreur notifications :',
                error
            );
        } finally {
            isFetching = false;
        }
    }

    function closeDropdown() {
        dropdown.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
    }

    toggle.addEventListener('click', () => {
        const isHidden = dropdown.classList.contains('hidden');

        dropdown.classList.toggle('hidden');

        toggle.setAttribute(
            'aria-expanded',
            String(isHidden)
        );

        if (isHidden) {
            fetchNotifications();
        }
    });

    document.addEventListener('click', event => {
        if (!component.contains(event.target)) {
            closeDropdown();
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeDropdown();
            hideToast();
        }
    });

    toastClose.addEventListener('click', hideToast);

    // Initialisation.
    fetchNotifications();

    // Actualisation automatique toutes les 5 secondes.
    setInterval(() => {
        if (!document.hidden) {
            fetchNotifications();
        }
    }, 5000);

    // Actualisation au retour sur l'onglet.
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            fetchNotifications();
        }
    });
});
</script>
