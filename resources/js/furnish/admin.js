// The admin panel's behaviour: confirming a destructive action, and removing
// a product image without reloading the form. The side rail's disclosure is
// the Alpine `disclosure` component, in the layout.
//
// Loaded by main.js, which the admin layout shares with the shop. Each
// initialiser bails out when its markup is absent, so the storefront pays
// nothing for it beyond the bytes.

/* ── Destructive actions ────────────────────────────────────────────────── */

/**
 * Deleting a product or a user cannot be undone, so it is confirmed in the
 * layout's own <dialog id="confirmDialog"> — the browser's modal, with focus
 * trapped and Escape closing it, and nothing loaded from a third party.
 *
 * A form can name its own question with data-confirm-title and
 * data-confirm-message; otherwise the dialog's default words stand.
 *
 * Delegated from `document`, so a form rendered after load is covered
 * without anything re-binding.
 */
export function initDeleteConfirm() {
    const dialog = document.getElementById('confirmDialog');
    const accept = document.getElementById('confirmAccept');

    // No dialog on the page means no confirmation is possible. The form then
    // submits as written: losing the operator's click is worse than losing the
    // safeguard, and the server still authorises the action.
    if (!(dialog instanceof HTMLDialogElement) || !accept) return;

    const title = dialog.querySelector('#confirmTitle');
    const message = dialog.querySelector('#confirmMessage');
    const defaults = { title: title.textContent, message: message.textContent };

    let pending = null;

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('.delete-form');

        if (!form || form.dataset.confirmed === 'true') return;

        event.preventDefault();

        pending = form;
        title.textContent = form.dataset.confirmTitle || defaults.title;
        message.textContent = form.dataset.confirmMessage || defaults.message;

        // Focus returns to the button that asked, if the operator cancels.
        const opener = event.submitter ?? form.querySelector('[type=submit]');
        dialog.addEventListener('close', () => opener?.focus(), { once: true });

        dialog.showModal();
    });

    accept.addEventListener('click', () => {
        if (!pending) return;

        // Marked before resubmitting so this listener stands aside on the
        // second pass rather than asking again forever.
        pending.dataset.confirmed = 'true';
        dialog.close();
        pending.requestSubmit();
        pending = null;
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
    initDeleteConfirm();
    initImageDelete();
}
