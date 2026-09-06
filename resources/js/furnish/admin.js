// The admin panel's behaviour: the side-rail disclosure, delete confirmation,
// and removing a product image without reloading the form.
//
// Moved out of <script> blocks in layouts/app.blade.php and
// admin/products/edit.blade.php. The storefront's modules went first; this is
// the last of it, and it means no Blade template in the project now contains
// executable JavaScript.
//
// Loaded by main.js, which the admin layout shares with the shop. Each
// initialiser bails out when its markup is absent, so the storefront pays
// nothing for it beyond the bytes.

import { data } from './bootstrap-data.js';

/* ── N3 · the side-rail disclosure ──────────────────────────────────────── */

export function initRail() {
    const toggle = document.getElementById('railToggle');
    const panel = document.getElementById('railPanel');

    if (!toggle || !panel) return;

    // The rail collapses below 60rem. Above it the panel is always open and
    // the button is not rendered at all, so the media query is the source of
    // truth rather than the button's state.
    const narrow = window.matchMedia('(max-width: 60rem)');

    const sync = () => {
        panel.hidden = narrow.matches;
        toggle.setAttribute('aria-expanded', narrow.matches ? 'false' : 'true');
    };

    sync();
    narrow.addEventListener('change', sync);

    toggle.addEventListener('click', () => {
        const open = toggle.getAttribute('aria-expanded') === 'true';

        toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
        panel.hidden = open;
    });
}

/* ── Destructive actions ────────────────────────────────────────────────── */

/**
 * design.md reserves the modal for genuinely irreversible actions, and
 * deleting a product or a user is one — so this keeps its confirmation where
 * an ordinary update gets optimistic-update-and-undo instead.
 *
 * Delegated from `document`, so a form rendered after load is covered without
 * anything re-binding.
 */
export function initDeleteConfirm() {
    const strings = data.admin ?? {};

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('.delete-form');

        if (!form || form.dataset.confirmed === 'true') return;

        event.preventDefault();

        // No SweetAlert2 on the page means no confirmation is possible. Submit
        // rather than silently swallowing the operator's click — losing the
        // action is worse than losing the safeguard, and the server still
        // authorises it.
        if (typeof window.Swal === 'undefined') {
            form.submit();

            return;
        }

        const token = (name) => getComputedStyle(document.documentElement)
            .getPropertyValue(name)
            .trim();

        window.Swal.fire({
            title: form.dataset.confirmTitle || strings.confirm,
            text: form.dataset.confirmMessage || strings.confirmDelete,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: strings.yesDelete,
            cancelButtonText: strings.cancel,
            confirmButtonColor: token('--color-danger'),
            cancelButtonColor: token('--color-neutral'),
        }).then((result) => {
            if (result.isConfirmed) {
                // Marked before resubmitting so this listener stands aside on
                // the second pass rather than asking again forever.
                form.dataset.confirmed = 'true';
                form.submit();
            }
        });
    });
}

/* ── Product images ─────────────────────────────────────────────────────── */

/**
 * The delete endpoint answers JSON, so the row goes in place rather than the
 * whole form reloading and losing whatever the operator had typed.
 */
export function initImageDelete() {
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('.js-delete-image');

        if (!button) return;

        const row = button.closest('[data-image-row]') ?? button.closest('div');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        try {
            const response = await fetch(button.dataset.url, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
            });

            const payload = await response.json();

            if (payload.success) row?.remove();
        } catch (error) {
            console.error('diwan: could not remove the image', error);
        }
    });
}

export function initAdmin() {
    initRail();
    initDeleteConfirm();
    initImageDelete();
}
