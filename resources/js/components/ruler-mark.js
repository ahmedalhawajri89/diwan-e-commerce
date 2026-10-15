// The marker on the footer's tape measure, placed at a real length.
//
//   <span x-data="rulerMark" data-cm="350" data-span="600">…</span>
//
// The position is a computed value — the visitor's own room width — so it is
// set here through the style object rather than written into a `style`
// attribute, which the design system keeps for values a stylesheet cannot know
// and which the CSP would refuse.
//
// With motion welcome, the mark waits at zero until the tape has been drawn,
// then travels out to its length: the measurement being taken.

/** Matches the tape's draw in app.css, so the mark sets off as it finishes. */
const AFTER_DRAW_MS = 1100;

export default () => ({
    init() {
        const cm = Number(this.$el.dataset.cm);
        const span = Number(this.$el.dataset.span);
        const at = `${Math.min(100, Math.max(0, (cm / span) * 100))}%`;

        const quiet = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (quiet || !('IntersectionObserver' in window)) {
            this.$el.style.left = at;

            return;
        }

        this.$el.style.left = '0%';

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (!entry.isIntersecting) return;

                observer.disconnect();
                setTimeout(() => {
                    this.$el.style.left = at;
                }, AFTER_DRAW_MS);
            },
            { threshold: 0.5 },
        );

        observer.observe(this.$el);
    },
});
