import test from 'node:test';
import assert from 'node:assert/strict';

class FakeElement {
    constructor(tagName = 'div') {
        this.tagName = tagName;
        this.children = [];
        this.parentNode = null;
        this.dataset = {};
        this.style = {};
        this.attributes = {};
        this.listeners = new Map();
        this.hidden = false;
        this.className = '';
        this._text = '';
    }

    set textContent(value) {
        this.children.forEach(child => { child.parentNode = null; });
        this.children = [];
        this._text = String(value ?? '');
    }

    get textContent() {
        return this._text + this.children.map(child => child.textContent).join('');
    }

    append(...children) {
        this._text = '';
        children.forEach(child => {
            child.parentNode = this;
            this.children.push(child);
        });
    }

    setAttribute(name, value) { this.attributes[name] = String(value); }
    getAttribute(name) { return this.attributes[name] ?? null; }
    addEventListener(name, callback) { this.listeners.set(name, callback); }
    removeEventListener(name) { this.listeners.delete(name); }
    dispatch(name, event = {}) { this.listeners.get(name)?.(event); }
    contains(element) { return element === this || this.children.some(child => child.contains(element)); }

    closest(selector) {
        let current = this;
        while (current) {
            if (selector === '.ui-toast' && current.className.split(' ').includes('ui-toast')) return current;
            current = current.parentNode;
        }
        return null;
    }

    remove() {
        if (!this.parentNode) return;
        this.parentNode.children = this.parentNode.children.filter(child => child !== this);
        this.parentNode = null;
    }
}

const root = new FakeElement('div');
root.id = 'global-toast-root';
root.dataset.toastPayload = Buffer.from('[]', 'utf8').toString('base64');
const statusBanner = new FakeElement('div');
statusBanner.setAttribute('role', 'status');
statusBanner.textContent = 'La modification est enregistrée.';
const body = new FakeElement('body');
body.append(root, statusBanner);

const timers = new Map();
let nextTimer = 1;
globalThis.document = {
    body,
    getElementById: id => id === root.id ? root : null,
    createElement: tagName => new FakeElement(tagName),
    querySelectorAll: selector => selector === 'body *'
        ? allDescendants(body)
        : [],
};
globalThis.window = {
    atob: value => Buffer.from(value, 'base64').toString('binary'),
    setTimeout: callback => { const id = nextTimer++; timers.set(id, callback); return id; },
    clearTimeout: id => timers.delete(id),
};

function allDescendants(element) {
    return element.children.flatMap(child => [child, ...allDescendants(child)]);
}

await import('../../resources/js/toast-notifications.js');

function clearToasts() {
    [...root.children].forEach(item => item.children.find(child => child.tagName === 'button')?.dispatch('click'));
}

test('validates types, safely inserts dynamic text, and reuses an identical active toast', () => {
    const message = '<img src=x onerror=alert(1)>';
    const first = window.showToast({ type: 'unknown', message });
    const duplicate = window.showToast({ type: 'info', message });

    assert.equal(first, duplicate);
    assert.equal(root.children.length, 1);
    assert.equal(first.getAttribute('role'), 'status');
    assert.equal(first.children[1].children[1].textContent, message);
    assert.equal(first.children[1].children[1].children.length, 0);
    clearToasts();
});

test('limits visible toasts to three and hides only an identical legacy banner', () => {
    window.showToast({ type: 'success', message: 'La modification est enregistrée.' });
    assert.equal(statusBanner.hidden, true);

    window.showToast({ type: 'info', message: 'Deuxième' });
    window.showToast({ type: 'warning', message: 'Troisième' });
    window.showToast({ type: 'error', message: 'Quatrième' });

    assert.equal(root.children.length, 3);
    assert.equal(root.children.some(item => item.textContent.includes('La modification est enregistrée.')), false);
    assert.equal(root.children.some(item => item.textContent.includes('Deuxième')), true);
    assert.ok(root.children.every(item => item.getAttribute('aria-live') === 'polite' || item.getAttribute('aria-live') === 'assertive'));
    clearToasts();
    clearToasts();
});

test('close button removes its toast and invalid empty messages are ignored', () => {
    assert.equal(window.showToast({ type: 'error', message: '   ' }), false);
    const item = window.showToast({ type: 'error', message: 'Action refusée.' });
    const closeButton = item.children.find(child => child.tagName === 'button');

    assert.equal(closeButton.getAttribute('aria-label'), 'Fermer le message');
    closeButton.dispatch('click');
    assert.equal(root.children.length, 0);
});

test('supports a five-second welcome duration without changing the default success duration', () => {
    const welcome = window.showToast({ type: 'success', title: 'Bienvenue', message: 'Bienvenue Élodie.', duration: 5000 });
    const welcomeProgress = welcome.children.find(child => child.className === 'ui-toast__progress');
    assert.equal(welcomeProgress.style.animationDuration, '5000ms');
    clearToasts();

    const standard = window.showToast({ type: 'success', message: 'Modification enregistrée.' });
    const standardProgress = standard.children.find(child => child.className === 'ui-toast__progress');
    assert.equal(standardProgress.style.animationDuration, '4000ms');
    clearToasts();
});
