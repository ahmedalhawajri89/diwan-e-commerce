// Sections settle into place as they reach the viewport.
//
//   <h2 data-reveal>…</h2>                       rises and fades in once
//   <div data-reveal-group> <a data-reveal>…      children follow one another
//   <svg data-draw>…                              rules itself in from the start
//
// The hidden starting state lives in app.css under `[data-motion]`, which is
// set here — so with JavaScript off, or with reduced motion asked for, nothing
// is ever hidden. Only opacity, translate and a clip move.

const STAGGER_MS = 90;
const MAX_STAGGER_MS = 450;

export function initReveal() {
    const quiet = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (quiet || !('IntersectionObserver' in window)) return;

    // Within a group, each child waits a beat longer than the last, capped so
    // a long grid does not make the visitor wait for its last row.
    document.querySelectorAll('[data-reveal-group]').forEach((group) => {
        group.querySelectorAll('[data-reveal]').forEach((item, index) => {
            item.style.setProperty('--reveal-delay', `${Math.min(index * STAGGER_MS, MAX_STAGGER_MS)}ms`);
        });
    });

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                entry.target.setAttribute('data-shown', '');
                observer.unobserve(entry.target);
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.12 },
    );

    document.documentElement.setAttribute('data-motion', '');
    document.querySelectorAll('[data-reveal], [data-draw]').forEach((item) => observer.observe(item));
}
