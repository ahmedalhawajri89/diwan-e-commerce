// The product's photographs: a track you can swipe, thumbnails that move it,
// and a full-screen viewer for a closer look.
//
//   <div x-data="gallery">
//     <div x-ref="track"> …one slide per photograph… </div>
//     <div x-ref="thumbs"> <button data-index="0" @click="show"> … </div>
//     <dialog id="lightbox"> <div x-ref="zoom"> …the same photographs… </div> </dialog>
//   </div>
//
// Scroll-snap does the swiping, natively, on a phone. Every slide is a
// <picture>, so a phone downloads the size it draws rather than the original.
// The thumbnails used to swap one <img>'s src, which is why that one image
// could never be responsive.

const smooth = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';

const rtl = () => document.documentElement.dir === 'rtl';

export default () => ({
    index: 0,
    count: 0,

    init() {
        const track = this.$refs.track;
        this.count = track.children.length;

        this.follow(track);
        if (this.$refs.zoom) this.follow(this.$refs.zoom);

        this.$watch('index', () => this.mark());
        this.mark();
    },

    /** Keep `index` on the slide the track has snapped to, however it got there. */
    follow(track) {
        track.addEventListener(
            'scroll',
            () => {
                // scrollLeft runs negative in a right-to-left track.
                const at = Math.round(Math.abs(track.scrollLeft) / track.clientWidth);
                if (at !== this.index) this.index = Math.min(at, this.count - 1);
            },
            { passive: true },
        );
    },

    get position() {
        return `${this.index + 1} / ${this.count}`;
    },

    get atStart() {
        return this.index <= 0;
    },

    get atEnd() {
        return this.index >= this.count - 1;
    },

    /** The thumbnail for the showing photograph says so. */
    mark() {
        this.$refs.thumbs?.querySelectorAll('[data-index]').forEach((thumb) => {
            thumb.setAttribute('aria-current', Number(thumb.dataset.index) === this.index ? 'true' : 'false');
        });
    },

    go(i, track = this.$refs.track) {
        const slide = track.children[i];
        if (!slide) return;

        this.index = i;
        track.scrollTo({ left: slide.offsetLeft - track.offsetLeft, behavior: smooth() });
    },

    show(event) {
        this.go(Number(event.currentTarget.dataset.index));
    },

    prev() {
        this.go(Math.max(0, this.index - 1), this.activeTrack());
    },

    next() {
        this.go(Math.min(this.count - 1, this.index + 1), this.activeTrack());
    },

    activeTrack() {
        return this.$refs.zoom?.closest('dialog')?.open ? this.$refs.zoom : this.$refs.track;
    },

    /** Opening the viewer: it starts on the photograph that was showing. */
    enlarge() {
        // dialogs.js opens the <dialog> after this handler returns, so the
        // track has no width yet; wait a frame for it to be laid out.
        requestAnimationFrame(() => {
            const zoom = this.$refs.zoom;
            const slide = zoom.children[this.index];
            if (slide) zoom.scrollTo({ left: slide.offsetLeft - zoom.offsetLeft, behavior: 'auto' });
        });
    },

    /** Arrow keys in the viewer, the right way round in either script. */
    key(event) {
        const forward = rtl() ? 'ArrowLeft' : 'ArrowRight';
        const back = rtl() ? 'ArrowRight' : 'ArrowLeft';

        if (event.key === forward) {
            event.preventDefault();
            this.next();
        } else if (event.key === back) {
            event.preventDefault();
            this.prev();
        }
    },

    /** Closing the viewer leaves the page's track on the same photograph. */
    closed() {
        this.go(this.index);
    },
});
