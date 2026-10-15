// Every modal on the site is a native <dialog>, opened with showModal().
//
// The browser already does the hard parts: it traps focus, makes the rest of
// the page inert, closes on Escape and draws the backdrop. What is left is
// wiring, done once here and delegated from `document`:
//
//   <button data-open-dialog="roomModal">   opens  <dialog id="roomModal">
//   <button data-close-dialog>              closes the dialog it sits in
//   a click on the backdrop                 closes the dialog
//
// Focus goes back to whatever opened the dialog when it closes, so Escape never
// strands a keyboard user at the top of the page.

const openers = new WeakMap();

function open(dialog, opener) {
    if (dialog.open) return;

    // One modal at a time: the room control inside the mobile menu opens the
    // room dialog, and the menu should give way to it, not sit underneath.
    const current = opener.closest('dialog[open]');
    const returnTo = current ? openers.get(current) ?? opener : opener;

    current?.close();

    openers.set(dialog, returnTo);
    dialog.showModal();
}

/** A click whose point is outside the dialog's box landed on the backdrop. */
function onBackdrop(dialog, event) {
    const box = dialog.getBoundingClientRect();

    return (
        event.clientX < box.left ||
        event.clientX > box.right ||
        event.clientY < box.top ||
        event.clientY > box.bottom
    );
}

export function initDialogs() {
    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-open-dialog]');

        if (opener) {
            const dialog = document.getElementById(opener.dataset.openDialog);

            if (dialog instanceof HTMLDialogElement) {
                event.preventDefault();
                open(dialog, opener);
            }

            return;
        }

        const closer = event.target.closest('[data-close-dialog]');

        if (closer) {
            closer.closest('dialog')?.close();

            return;
        }

        // A keyboard "click" on a button inside reports 0,0 coordinates; only
        // a real pointer can land on the backdrop.
        if (event.target instanceof HTMLDialogElement && event.detail > 0 && onBackdrop(event.target, event)) {
            event.target.close();
        }
    });

    // A dialog whose form came back with an error opens again by itself
    // (`data-open-on-load`), so the visitor sees what to fix where they typed it.
    document.querySelectorAll('dialog[data-open-on-load]').forEach((dialog) => {
        if (!dialog.open) dialog.showModal();
    });

    // `close` fires however the dialog was closed: Escape, a close button,
    // the backdrop, or a <form method="dialog">.
    document.addEventListener(
        'close',
        (event) => {
            const dialog = event.target;

            if (!(dialog instanceof HTMLDialogElement)) return;

            const opener = openers.get(dialog);
            openers.delete(dialog);

            if (opener?.isConnected) opener.focus();
        },
        true,
    );
}
