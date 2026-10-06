// Add to bag, without leaving the page.
//
// A form marked `data-bag-form` is sent by fetch instead of a page load. The
// server answers with what was added; the bag drawer slides in from the
// inline end with the piece, the bag's total and the way on, and the header's
// count is brought up to date. A refusal (sold out, no price) is said in the
// visitor's language, in a toast.
//
// Without JavaScript the same form posts and redirects back, as it always
// has: this file only adds to it.

import { t } from './bootstrap-data.js';
import { toast } from '../components/toast.js';

function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/** Let the form's submit-once lock go, whatever happened. */
function unlock(form) {
    const state = window.Alpine?.$data?.(form);
    if (state && 'busy' in state) state.busy = false;
}

function paintBag(count) {
    const badge = document.querySelector('[data-bag] [data-bag-count]');
    if (badge) {
        badge.textContent = count;
        badge.hidden = count === 0;
    }

    document.querySelector('[data-bag]')
        ?.setAttribute('aria-label', count > 0 ? t('bagCount', { COUNT: count }) : t('bagEmpty'));
}

function fill(drawer, { item, subtotal }) {
    const image = drawer.querySelector('[data-drawer-image]');
    image.src = item.image;
    image.alt = '';

    const name = drawer.querySelector('[data-drawer-name]');
    name.textContent = item.name;
    name.href = item.url;

    drawer.querySelector('[data-drawer-meta]').textContent =
        [item.colour, `× ${item.quantity}`].filter(Boolean).join(' · ');
    drawer.querySelector('[data-drawer-price]').textContent = `${t('currency')}${item.price}`;
    drawer.querySelector('[data-drawer-total]').textContent = `${t('currency')}${subtotal}`;
}

export function initBagDrawer() {
    const drawer = document.getElementById('bagDrawer');
    if (!(drawer instanceof HTMLDialogElement)) return;

    document.addEventListener('submit', async (event) => {
        const form = event.target.closest?.('form[data-bag-form]');
        if (!form) return;

        event.preventDefault();

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf(),
                },
                body: new FormData(form),
            });

            const data = await response.json().catch(() => null);

            if (!response.ok || !data?.item) {
                // The server's refusals are already in the visitor's language.
                const fallback = response.status === 419 ? t('sessionExpired') : t('addFailed');
                toast(response.status === 422 && data?.message ? data.message : fallback, 'danger');

                return;
            }

            fill(drawer, data);
            paintBag(data.count);

            if (!drawer.open) drawer.showModal();
        } catch {
            toast(t('addFailed'), 'danger');
        } finally {
            unlock(form);
        }
    });
}
