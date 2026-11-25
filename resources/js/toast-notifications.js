const TOAST_TYPES = new Set(['success', 'error', 'warning', 'info']);
const TOAST_DURATIONS = {
    success: 4000,
    info: 5000,
    warning: 6000,
    error: 7000,
};
const TOAST_PRESENTATION = {
    success: { title: 'Succès', icon: '✓' },
    error: { title: 'Une erreur est survenue', icon: '!' },
    warning: { title: 'À vérifier', icon: '!' },
    info: { title: 'Information', icon: 'i' },
};

const root = document.getElementById('global-toast-root');
const activeToasts = [];

function normalized(value) {
    return String(value).replace(/\s+/g, ' ').trim();
}

function hideDuplicateStatusBanner(message) {
    const target = normalized(message);
    if (!target) return;

    document.querySelectorAll('body *').forEach(element => {
        if (element.closest('.ui-toast')) return;
        if (normalized(element.textContent) === target) {
            element.hidden = true;
            element.setAttribute('data-toast-duplicate', 'true');
        }
    });
}

function removeToast(entry) {
    if (entry.closed) return;
    entry.closed = true;
    window.clearTimeout(entry.timeout);
    entry.item.removeEventListener('mouseenter', entry.pause);
    entry.item.removeEventListener('mouseleave', entry.resume);
    entry.item.removeEventListener('focusin', entry.pause);
    entry.item.removeEventListener('focusout', entry.focusout);
    entry.closeButton.removeEventListener('click', entry.close);
    entry.item.remove();
    const index = activeToasts.indexOf(entry);
    if (index !== -1) activeToasts.splice(index, 1);
}

function showToast(options = {}) {
    if (!root || !options || typeof options !== 'object') return false;

    const type = TOAST_TYPES.has(options.type) ? options.type : 'info';
    const message = typeof options.message === 'string' ? normalized(options.message) : '';
    if (!message) return false;

    const title = typeof options.title === 'string' && options.title.trim()
        ? options.title.trim().slice(0, 80)
        : TOAST_PRESENTATION[type].title;
    const duplicate = activeToasts.find(entry => entry.type === type && entry.message === message);
    if (duplicate) return duplicate.item;

    while (activeToasts.length >= 3) removeToast(activeToasts[0]);

    const item = document.createElement('section');
    item.className = `ui-toast ui-toast--${type}`;
    item.setAttribute('role', type === 'error' ? 'alert' : 'status');
    item.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');
    item.setAttribute('aria-atomic', 'true');

    const icon = document.createElement('span');
    icon.className = 'ui-toast__icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = TOAST_PRESENTATION[type].icon;

    const content = document.createElement('div');
    content.className = 'ui-toast__content';

    const heading = document.createElement('p');
    heading.className = 'ui-toast__title';
    heading.textContent = title;

    const description = document.createElement('p');
    description.className = 'ui-toast__message';
    description.textContent = message;
    content.append(heading, description);

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'ui-toast__close';
    closeButton.setAttribute('aria-label', 'Fermer le message');
    closeButton.textContent = '×';

    const progress = document.createElement('span');
    progress.className = 'ui-toast__progress';
    const duration = Number.isFinite(options.duration)
        ? Math.min(10000, Math.max(1000, Math.round(options.duration)))
        : TOAST_DURATIONS[type];
    progress.style.animationDuration = `${duration}ms`;

    item.append(icon, content, closeButton, progress);
    root.append(item);

    const entry = {
        item,
        closeButton,
        type,
        message,
        timeout: null,
        remaining: duration,
        startedAt: 0,
        closed: false,
    };

    entry.close = () => removeToast(entry);
    entry.pause = () => {
        if (entry.timeout === null) return;
        window.clearTimeout(entry.timeout);
        entry.timeout = null;
        entry.remaining = Math.max(0, entry.remaining - (performance.now() - entry.startedAt));
        progress.style.animationPlayState = 'paused';
    };
    entry.resume = () => {
        if (entry.timeout !== null || entry.closed) return;
        progress.style.animationPlayState = 'running';
        entry.startedAt = performance.now();
        entry.timeout = window.setTimeout(entry.close, entry.remaining);
    };
    entry.focusout = event => {
        if (!item.contains(event.relatedTarget)) entry.resume();
    };

    closeButton.addEventListener('click', entry.close);
    item.addEventListener('mouseenter', entry.pause);
    item.addEventListener('mouseleave', entry.resume);
    item.addEventListener('focusin', entry.pause);
    item.addEventListener('focusout', entry.focusout);
    activeToasts.push(entry);
    entry.resume();

    hideDuplicateStatusBanner(message);
    return item;
}

window.showToast = showToast;

if (root) {
    try {
        const payload = root.dataset.toastPayload || '';
        const binary = window.atob(payload);
        const bytes = Uint8Array.from(binary, character => character.charCodeAt(0));
        const messages = JSON.parse(new TextDecoder().decode(bytes));
        if (Array.isArray(messages)) messages.forEach(showToast);
    } catch {
        // A malformed flash payload must never break the rest of the page.
    }
}
