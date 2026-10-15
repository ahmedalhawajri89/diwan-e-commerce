// «Ask Diwan»: a short conversation in a drawer. The visitor's words go to
// the server; the answer comes back as a sentence and, below it, the pieces
// it points to, already drawn by the server from the catalogue (so their
// prices are the rows', never the model's words).
//
//   <div x-data="assistant" data-url="…" data-forget="…" data-failed="…" data-thinking="…">
//     <button x-ref="opener" @click="show">…</button>
//     <dialog x-ref="dialog" @close="closed">
//       <div x-ref="log">…</div>
//       <form @submit.prevent="send"><textarea x-ref="input" @keydown.enter="enter"></textarea></form>
//     </dialog>
//   </div>

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// Once a visit, a short card says what the button is for: a few seconds
// after the page settles, for ten seconds, then never again that visit.
const TEASE_AFTER = 4000;
const TEASE_FOR = 10000;
const TEASED_KEY = 'diwan.assistant.teased';

// Below this width (Tailwind's lg) the button steps aside while scrolling down.
const WIDE = 1024;
// A scroll smaller than this is a hand resting on the screen, not reading on.
const SCROLL_SLACK = 12;

const teasedAlready = () => {
    try {
        return sessionStorage.getItem(TEASED_KEY) === '1';
    } catch {
        return true; // storage blocked: do not risk showing it on every page
    }
};

const markTeased = () => {
    try {
        sessionStorage.setItem(TEASED_KEY, '1');
    } catch {
        // nothing to remember with
    }
};

export default () => ({
    open: false,
    busy: false,
    teasing: false,
    // Nothing floats over the home page's photograph: while it is on screen
    // the button stays tucked away, and the room card there carries its own
    // way in (a [data-open-assistant] button).
    onHero: false,
    // On a phone or tablet the button would sit over what is being read (a
    // price, a rating, the "will it reach your room?" button). It steps
    // aside while the page is scrolled down, and comes back on the way up.
    reading: false,
    armed: false,
    returnTo: null,

    /** The button and its card are out of sight: the drawer is open, the hero is in view, or a phone is reading on. */
    get tucked() {
        return this.open || this.onHero || this.reading;
    },

    init() {
        const hero = document.querySelector('[data-hero]');
        if (hero && 'IntersectionObserver' in window) {
            this.onHero = true;
            new IntersectionObserver(([entry]) => {
                this.onHero = entry.isIntersecting;
                if (this.onHero) this.teasing = false;
                else this.armTeaser();
            }, { threshold: 0.15 }).observe(hero);
        } else {
            this.armTeaser();
        }

        let lastY = window.scrollY;
        window.addEventListener('scroll', () => {
            const y = window.scrollY;
            if (Math.abs(y - lastY) < SCROLL_SLACK) return;
            // Only where the screen is narrow enough for it to cover the page.
            this.reading = window.innerWidth < WIDE && y > lastY;
            lastY = y;
        }, { passive: true });

        // Any [data-open-assistant] on the page opens the drawer, outside
        // this component's own markup (the hero's room card has one).
        document.addEventListener('click', (event) => {
            const trigger = event.target.closest?.('[data-open-assistant]');
            if (!trigger) return;
            event.preventDefault();
            this.show(trigger);
        });
    },

    /** The card's timer starts only once the button can actually be seen. */
    armTeaser() {
        if (this.armed || teasedAlready()) return;
        this.armed = true;

        setTimeout(() => {
            if (this.tucked || teasedAlready()) return;
            this.teasing = true;
            markTeased();
            setTimeout(() => {
                this.teasing = false;
            }, TEASE_FOR);
        }, TEASE_AFTER);
    },

    dismissTeaser() {
        this.teasing = false;
        markTeased();
        this.$refs.opener?.focus();
    },

    show(trigger) {
        this.returnTo = trigger instanceof HTMLElement ? trigger : null;
        this.teasing = false;
        markTeased();
        this.$refs.dialog.showModal();
        this.open = true;
        this.$nextTick(() => this.$refs.input.focus());
    },

    hide() {
        this.$refs.dialog.close();
    },

    closed() {
        this.open = false;
        (this.returnTo ?? this.$refs.opener)?.focus();
        this.returnTo = null;
    },

    /** Enter sends; Shift+Enter is a new line. */
    enter(event) {
        if (event.shiftKey || event.isComposing) return;
        event.preventDefault();
        this.send();
    },

    suggest(event) {
        this.$refs.input.value = event.currentTarget.dataset.text;
        this.send();
    },

    async send() {
        const text = this.$refs.input.value.trim();
        if (text.length < 2 || this.busy) return;

        this.busy = true;
        this.$refs.input.value = '';
        this.$refs.suggestions?.closest('div')?.classList.add('hidden');
        this.bubble(text, 'user');
        const waiting = this.thinking();

        try {
            const response = await fetch(this.$root.dataset.url, {
                method: 'POST',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                credentials: 'same-origin',
                body: JSON.stringify({ message: text }),
            });
            const data = await response.json().catch(() => ({}));
            waiting.remove();

            if (!response.ok) {
                this.bubble(data.message || this.$root.dataset.failed, 'error');
                return;
            }

            const answer = this.bubble(data.reply, 'assistant');
            if (data.html) answer.insertAdjacentHTML('beforeend', data.html);
        } catch {
            waiting.remove();
            this.bubble(this.$root.dataset.failed, 'error');
        } finally {
            this.busy = false;
            this.$refs.input.focus();
        }
    },

    async restart() {
        try {
            await fetch(this.$root.dataset.forget, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
                credentials: 'same-origin',
            });
        } catch {
            // Nothing to undo: the next question simply starts with what is left.
        }
        [...this.$refs.log.children].slice(1).forEach((node) => node.remove());
        this.$refs.suggestions?.closest('div')?.classList.remove('hidden');
        this.$refs.input.focus();
    },

    bubble(text, who) {
        const box = document.createElement('div');
        const tone = {
            user: 'justify-self-end max-w-[85%] px-3.5 py-2.5 bg-ink text-paper',
            assistant: 'justify-self-start w-full max-w-full',
            error: 'justify-self-start max-w-[85%] px-3.5 py-2.5 bg-danger-wash text-danger',
        }[who];
        box.className = `grid motion-safe:animate-fade-up ${tone}`;

        const p = document.createElement('p');
        p.className = 'm-0 text-sm leading-body whitespace-pre-line [overflow-wrap:anywhere]';
        p.dir = 'auto';
        p.textContent = text;
        box.append(p);

        this.$refs.log.append(box);
        this.$refs.log.scrollTop = this.$refs.log.scrollHeight;

        return box;
    },

    /** Three dots while the answer is on its way, with words for a screen reader. */
    thinking() {
        const box = document.createElement('div');
        box.className = 'justify-self-start inline-flex items-center gap-1.5 px-3.5 py-3 bg-paper-2';
        box.setAttribute('role', 'status');
        for (let i = 0; i < 3; i++) {
            const dot = document.createElement('span');
            dot.className = 'size-1.5 rounded-pill bg-muted motion-safe:animate-pulse';
            dot.setAttribute('aria-hidden', 'true');
            box.append(dot);
        }
        const words = document.createElement('span');
        words.className = 'sr-only';
        words.textContent = this.$root.dataset.thinking;
        box.append(words);
        this.$refs.log.append(box);
        this.$refs.log.scrollTop = this.$refs.log.scrollHeight;

        return box;
    },
});
