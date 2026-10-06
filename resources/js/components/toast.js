// A short message that appears at the bottom of the screen and leaves on its
// own — for something that went wrong in the background, like a save that did
// not reach the server. Successes that the page already shows (a filled icon,
// a counter) do not get one.
//
//   import { toast } from '../components/toast.js';
//   toast(message, 'danger');
//
// The region is a polite live region, so a screen reader announces the words
// without interrupting.

const TONES = {
    danger: 'bg-danger text-paper',
    ok: 'bg-ok text-paper',
    info: 'bg-ink text-paper',
};

const DURATION_MS = 4500;

function region() {
    let node = document.getElementById('toasts');

    if (!node) {
        node = document.createElement('div');
        node.id = 'toasts';
        node.setAttribute('aria-live', 'polite');
        node.className = 'fixed bottom-6 inset-x-0 z-50 flex flex-col items-center gap-2 px-4 pointer-events-none';
        document.body.appendChild(node);
    }

    return node;
}

export function toast(message, tone = 'info') {
    const item = document.createElement('p');
    item.className = `m-0 max-w-md px-5 py-3 text-sm shadow-none pointer-events-auto transition duration-300 opacity-0 translate-y-2 ${TONES[tone] ?? TONES.info}`;
    item.textContent = message;

    region().appendChild(item);

    // Next frame, so the transition has a starting state to move from.
    requestAnimationFrame(() => item.classList.remove('opacity-0', 'translate-y-2'));

    setTimeout(() => {
        item.classList.add('opacity-0');
        setTimeout(() => item.remove(), 300);
    }, DURATION_MS);
}
