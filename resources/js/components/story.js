// A short story in chapters: one picture at a time, its words beside it.
//
//   <section x-data="story" @keydown="key">
//     <div data-stage> <figure data-slide="0">…</figure> … </div>
//     <button data-go="0" @click="choose">…</button> …
//     <div data-text="0">…</div> …
//   </section>
//
// The shopper moves it: the chapter tabs, the arrows, a swipe, or the arrow
// keys. Nothing advances on a timer: a story that turns its own pages takes
// the picture away from someone halfway through reading it. Every chapter is
// rendered by the server; this only chooses which shows, and the hidden ones
// are inert.

const rtl = () => document.documentElement.dir === 'rtl';

export default () => ({
    index: 0,
    count: 0,

    init() {
        this.count = this.$root.querySelectorAll('[data-slide]').length;
        this.$watch('index', () => this.paint());
        this.paint();
        this.swipe();
    },

    get atStart() {
        return this.index === 0;
    },

    get atEnd() {
        return this.index === this.count - 1;
    },

    get position() {
        const two = (n) => String(n).padStart(2, '0');
        return `${two(this.index + 1)} / ${two(this.count)}`;
    },

    go(i) {
        this.index = Math.max(0, Math.min(this.count - 1, i));
    },

    next() {
        this.go(this.index + 1);
    },

    prev() {
        this.go(this.index - 1);
    },

    choose(event) {
        this.go(Number(event.currentTarget.dataset.go));
    },

    /** Arrow keys, the right way round in either script. */
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

    paint() {
        this.$root.querySelectorAll('[data-slide], [data-text]').forEach((el) => {
            const on = Number(el.dataset.slide ?? el.dataset.text) === this.index;
            el.toggleAttribute('data-active', on);
            el.inert = !on;
            el.setAttribute('aria-hidden', on ? 'false' : 'true');
        });

        this.$root.querySelectorAll('[data-go]').forEach((tab) => {
            const on = Number(tab.dataset.go) === this.index;
            tab.toggleAttribute('data-active', on);
            tab.setAttribute('aria-selected', on ? 'true' : 'false');
            tab.tabIndex = on ? 0 : -1;
        });
    },

    /** A swipe across the picture turns the page, in the reading direction. */
    swipe() {
        const stage = this.$root.querySelector('[data-stage]');
        if (!stage) return;

        let startX = null;

        stage.addEventListener('pointerdown', (event) => {
            startX = event.clientX;
        });

        stage.addEventListener('pointerup', (event) => {
            if (startX === null) return;
            const dx = event.clientX - startX;
            startX = null;

            if (Math.abs(dx) < 50) return;
            const towardsEnd = rtl() ? dx > 0 : dx < 0;
            towardsEnd ? this.next() : this.prev();
        });
    },
});
