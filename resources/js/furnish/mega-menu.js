// N11 · the mega-menu panel.
//
// Click to open, click or Escape to close, focus returns to the trigger.
// Deliberately not hover-opened: a hover menu is unreachable on a touch screen
// and hostile with a keyboard.
//
// Moved out of layouts/furnish.blade.php unchanged.

/** A diagonal mouse path across the gap should not slam the panel shut. */
const CLOSE_DELAY_MS = 260;

export function initMegaMenu() {
    const trigger = document.getElementById('megaTrigger');
    const panel = document.getElementById('megaPanel');
    const scrim = document.getElementById('megaScrim');

    if (!trigger || !panel || !scrim) return;

    const isOpen = () => trigger.getAttribute('aria-expanded') === 'true';

    const setOpen = (open) => {
        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        panel.hidden = !open;
        scrim.hidden = !open;
    };

    trigger.addEventListener('click', () => setOpen(!isOpen()));
    scrim.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen()) {
            setOpen(false);

            // Escape must not strand the keyboard somewhere invisible.
            trigger.focus();
        }
    });

    let closeTimer = null;

    [panel, trigger.closest('.hm-nav')].filter(Boolean).forEach((element) => {
        element.addEventListener('mouseleave', () => {
            closeTimer = setTimeout(() => setOpen(false), CLOSE_DELAY_MS);
        });

        element.addEventListener('mouseenter', () => clearTimeout(closeTimer));
    });
}
