// The storefront header: the browse panel, the bar stepping out of the way
// while the visitor reads down the page, and — on a page that opens with a
// full-bleed photograph — the bar lying clear over that photograph until the
// visitor scrolls past it.
//
// One component for all three because they depend on each other: the bar must
// not retract or go clear while the panel hanging from it is open.

/** A diagonal mouse path across the gap should not slam the panel shut. */
const CLOSE_DELAY_MS = 260;

/** Below this the header always shows; it is still in its own space. */
const RETRACT_AFTER_PX = 160;

/** Ignore jitter — a trackpad reports one-pixel scrolls constantly. */
const SCROLL_SLACK_PX = 6;

export default () => ({
    // The browse panel. Opened by click, never by hover: a hover menu cannot
    // be reached on a touch screen and is hostile to a keyboard.
    open: false,
    retracted: false,

    // Clear-over-photograph mode, asked for by the page with `data-overlay`.
    overlay: false,
    y: 0,

    closeTimer: null,
    lastY: 0,

    init() {
        this.overlay = this.$el.hasAttribute('data-overlay');
        this.y = window.scrollY;
        this.lastY = this.y;

        let queued = false;

        window.addEventListener(
            'scroll',
            () => {
                if (queued) return;
                queued = true;

                requestAnimationFrame(() => {
                    queued = false;
                    this.track(window.scrollY);
                });
            },
            { passive: true },
        );
    },

    track(y) {
        this.y = y;

        const delta = y - this.lastY;

        if (Math.abs(delta) < SCROLL_SLACK_PX) return;

        this.retracted = !this.open && y > RETRACT_AFTER_PX && delta > 0;
        this.lastY = y;
    },

    /** Clear while the photograph is still behind the bar. */
    get over() {
        if (!this.overlay || this.open) return false;

        const hero = document.querySelector('[data-hero]');

        return hero ? this.y < hero.offsetTop + hero.offsetHeight - this.$root.offsetHeight : false;
    },

    /** Bound to the header's class list; the classes live here so Tailwind sees them. */
    get barClass() {
        return this.retracted ? '-translate-y-full' : '';
    },

    toggle() {
        this.open = !this.open;
    },

    close() {
        this.open = false;
    },

    /**
     * Focus entering the header brings it back. Without this, Shift+Tab from
     * the page landed on controls the retracted bar was holding off-screen.
     */
    reveal() {
        this.retracted = false;
    },

    /** Focus leaving the header closes the panel hanging from it. */
    focusLeft(event) {
        if (this.open && !this.$root.contains(event.relatedTarget)) {
            this.open = false;
        }
    },

    /** Escape must not strand the keyboard somewhere invisible. */
    dismiss() {
        if (!this.open) return;

        this.open = false;
        this.$refs.trigger?.focus();
    },

    leave() {
        if (!this.open) return;

        this.closeTimer = setTimeout(() => {
            this.open = false;
        }, CLOSE_DELAY_MS);
    },

    enter() {
        clearTimeout(this.closeTimer);
    },
});
