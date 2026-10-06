// A row of pieces that moves sideways: "Just in", "You looked at".
//
//   <section x-data="rail">
//     <ul x-ref="track"> <li>…card…</li> … </ul>
//     <div x-ref="tape"></div>   ← the measure: where in the row you are
//     <button @click="prev"> <button @click="next">
//   </section>
//
// Not a slideshow. Nothing moves on its own, nothing loops, nothing hides the
// row behind a timer: the shopper moves it, by swiping on a phone, by the
// arrows, by dragging with a mouse, or with the keyboard. What the component
// adds is a sense of where you are, drawn as a length on a tape rather than
// as dots, because this shop's idea is measurement.
//
// The browser's scrollbar is hidden; scrolling itself is native, so a
// trackpad, a wheel and a screen reader all keep working.

const quiet = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

export default () => ({
    atStart: true,
    atEnd: false,
    scrollable: false,

    init() {
        const track = this.$refs.track;
        this.rtl = getComputedStyle(track).direction === 'rtl';

        let queued = false;
        const measure = () => {
            if (queued) return;
            queued = true;
            requestAnimationFrame(() => {
                queued = false;
                this.measure();
            });
        };

        track.addEventListener('scroll', measure, { passive: true });
        new ResizeObserver(measure).observe(track);
        this.measure();
        this.dragWithMouse(track);
    },

    /** Where the row is, as two numbers the tape draws with: how much shows, and how far along. */
    measure() {
        const track = this.$refs.track;
        const max = track.scrollWidth - track.clientWidth;
        const x = Math.abs(track.scrollLeft);   // negative in a right-to-left row

        this.scrollable = max > 2;
        this.atStart = x <= 2;
        this.atEnd = x >= max - 2;

        const tape = this.$refs.tape;
        if (tape) {
            tape.style.setProperty('--size', this.scrollable ? (track.clientWidth / track.scrollWidth).toFixed(4) : 1);
            tape.style.setProperty('--pos', this.scrollable ? (x / max).toFixed(4) : 0);
        }
    },

    /** One step is as many whole cards as fit, so a press never strands half a card. */
    step(sign) {
        const track = this.$refs.track;
        const card = track.querySelector(':scope > li');
        const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
        const width = card ? card.getBoundingClientRect().width + gap : track.clientWidth;
        const perPage = Math.max(1, Math.floor((track.clientWidth + gap) / width));

        track.scrollBy({
            left: sign * (this.rtl ? -1 : 1) * perPage * width,
            behavior: quiet() ? 'auto' : 'smooth',
        });
    },

    next() {
        this.step(1);
    },

    prev() {
        this.step(-1);
    },

    /**
     * A mouse can drag the row, as a thumb does on a phone. Snapping is
     * suspended while it moves and resumes on release, so the row settles on
     * a card's edge. A drag is not a click: letting go over a card after
     * dragging does not open it.
     */
    dragWithMouse(track) {
        let down = false;
        let moved = false;
        let startX = 0;
        let startLeft = 0;

        track.addEventListener('pointerdown', (event) => {
            if (event.pointerType !== 'mouse' || event.button !== 0) return;
            down = true;
            moved = false;
            startX = event.clientX;
            startLeft = track.scrollLeft;
        });

        window.addEventListener('pointermove', (event) => {
            if (!down) return;
            const dx = event.clientX - startX;

            if (!moved && Math.abs(dx) > 6) {
                moved = true;
                track.dataset.dragging = '';
            }

            if (moved) track.scrollLeft = startLeft - dx;
        });

        window.addEventListener('pointerup', () => {
            if (!down) return;
            down = false;
            delete track.dataset.dragging;
        });

        // Swallow the click that ends a drag, before the card's link sees it.
        track.addEventListener(
            'click',
            (event) => {
                if (!moved) return;
                event.preventDefault();
                event.stopPropagation();
                moved = false;
            },
            true,
        );

        // Images would otherwise start the browser's own drag-and-drop.
        track.addEventListener('dragstart', (event) => event.preventDefault());
    },
});
