// The bell in the admin top bar: the unread count, the latest notices, and
// word of a new one the moment it arrives, without reloading the page.
//
//   <div x-data="notifications" data-feed="…" data-read="…/__ID__/read"
//        data-read-all="…" data-unread="3" data-label="…" data-label-unread="… :count …">
//     <button x-ref="button" @click="toggle">… <span x-ref="badge"></span></button>
//     <div x-show="open"> <ul x-ref="list"></ul> </div>
//     <div x-ref="toasts"></div>
//   </div>
//
// "Live" is a short poll, not a socket: every 15 seconds the page asks the
// feed for anything newer than the last notice it has seen. It costs one
// small query, works behind any host and through `artisan serve`, and stops
// while the tab is hidden; coming back to the tab asks at once. A notice
// that arrives shows as a toast for six seconds, bumps the badge, and puts
// the count in the tab's title so it is seen from another tab.

import { icon } from '../furnish/interactions.js';

const EVERY = 15000;
const TOAST_FOR = 6000;

const TINT = {
    accent: 'bg-accent-wash text-accent-ink',
    warn: 'bg-warn-wash text-warn',
    ok: 'bg-ok-wash text-ok',
    neutral: 'bg-paper-2 text-neutral',
};

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export default () => ({
    open: false,
    unread: 0,
    latest: 0,
    items: [],
    timer: null,
    baseTitle: '',

    get none() {
        return this.items.length === 0;
    },

    get hasUnread() {
        return this.unread > 0;
    },

    /** Toasts stand aside while the panel is open: it shows the same notices. */
    get closed() {
        return !this.open;
    },

    init() {
        this.unread = Number(this.$root.dataset.unread || 0);
        this.baseTitle = document.title.replace(/^\(\d+\+?\)\s*/, '');
        this.paintBadge();

        // The first ask fills the dropdown and learns where "new" starts;
        // nothing already there is announced as if it had just happened.
        this.fetchFeed(false);

        this.timer = setInterval(() => {
            if (!document.hidden) this.fetchFeed(true);
        }, EVERY);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) this.fetchFeed(true);
        });
    },

    destroy() {
        clearInterval(this.timer);
    },

    toggle() {
        this.open = !this.open;
    },

    close() {
        this.open = false;
    },

    dismiss() {
        if (!this.open) return;
        this.open = false;
        this.$refs.button?.focus();
    },

    async fetchFeed(announce) {
        const url = new URL(this.$root.dataset.feed, window.location.origin);
        if (announce && this.latest) url.searchParams.set('after', this.latest);

        let data;
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!response.ok) return;
            data = await response.json();
        } catch {
            return; // offline for a moment: the next tick tries again
        }

        const fresh = announce ? data.items.filter((item) => item.id > this.latest) : data.items;

        this.items = announce ? [...fresh, ...this.items].slice(0, 8) : data.items;
        this.latest = Math.max(this.latest, data.latest || 0);
        const grew = data.unread > this.unread;
        this.unread = data.unread;

        this.paintList();
        this.paintBadge(grew && announce);
        this.paintRail(data.pendingOrders);

        if (announce) fresh.slice(0, 3).reverse().forEach((item) => this.toast(item));
    },

    async readAll() {
        try {
            await fetch(this.$root.dataset.readAll, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
                credentials: 'same-origin',
            });
        } catch {
            return;
        }
        this.items = this.items.map((item) => ({ ...item, unread: false }));
        this.unread = 0;
        this.paintList();
        this.paintBadge();
    },

    /** Mark it read, then go to it; the read must not be lost to the navigation. */
    follow(item) {
        fetch(this.$root.dataset.read.replace('__ID__', item.id), {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
            credentials: 'same-origin',
            keepalive: true,
        }).finally(() => {
            window.location.href = item.url;
        });
    },

    paintBadge(pulse = false) {
        const badge = this.$refs.badge;
        const n = this.unread;

        badge.textContent = n > 99 ? '99+' : String(n);
        badge.hidden = n === 0;
        if (pulse) {
            badge.classList.remove('motion-safe:animate-ping-once');
            void badge.offsetWidth; // restart the animation
            badge.classList.add('motion-safe:animate-ping-once');
        }

        this.$refs.button.setAttribute('aria-label', n
            ? this.$root.dataset.labelUnread.replace(':count', n)
            : this.$root.dataset.label);
        document.title = n ? `(${n > 99 ? '99+' : n}) ${this.baseTitle}` : this.baseTitle;
    },

    /** The rail's "orders" count is the orders waiting; keep it current too. */
    paintRail(pending) {
        if (typeof pending !== 'number') return;
        document.querySelectorAll('[data-rail-count="orders"]').forEach((el) => {
            el.textContent = String(pending);
        });
    },

    row(item, { compact = false } = {}) {
        const a = document.createElement('a');
        a.href = item.url;
        a.className = 'group flex items-start gap-3 px-4 py-3 text-ink no-underline transition-colors hover:bg-paper-2 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-focus'
            + (item.unread && !compact ? ' bg-accent-wash/40' : '');
        a.addEventListener('click', (event) => {
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.button === 1) return;
            event.preventDefault();
            this.follow(item);
        });

        const disc = document.createElement('span');
        disc.className = `inline-flex items-center justify-center size-9 shrink-0 rounded-pill ${TINT[item.tone] ?? TINT.neutral}`;
        disc.innerHTML = icon(item.icon);

        const body = document.createElement('span');
        body.className = 'flex-1 min-w-0';
        const text = document.createElement('span');
        text.className = 'block text-sm leading-snug' + (item.unread ? ' font-medium' : ' text-neutral');
        text.textContent = item.text;
        const when = document.createElement('span');
        when.className = 'block mt-1 font-mono text-xs text-muted';
        when.textContent = item.when;
        body.append(text, when);

        a.append(disc, body);

        if (item.unread && !compact) {
            const dot = document.createElement('span');
            dot.className = 'mt-2 size-2 shrink-0 rounded-pill bg-accent-ink';
            dot.setAttribute('aria-hidden', 'true');
            a.append(dot);
        }

        return a;
    },

    paintList() {
        const list = this.$refs.list;
        list.replaceChildren(...this.items.map((item) => {
            const li = document.createElement('li');
            li.append(this.row(item));
            return li;
        }));
        this.$refs.empty.hidden = this.items.length > 0;
        this.$refs.markAll.hidden = this.unread === 0;
    },

    toast(item) {
        const box = document.createElement('div');
        box.setAttribute('role', 'status');
        box.className = 'pointer-events-auto relative w-80 max-w-[calc(100vw-2rem)] bg-paper border border-rule overflow-hidden '
            + 'transition-[opacity,translate] duration-300 ease-out opacity-0 translate-y-2';

        const row = this.row(item, { compact: true });
        row.classList.add('pe-10'); // room for the close button
        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'absolute top-2 end-2 inline-flex items-center justify-center size-7 rounded-pill border-0 bg-transparent text-muted cursor-pointer hover:bg-paper-2 hover:text-ink';
        close.setAttribute('aria-label', this.$root.dataset.dismiss);
        close.innerHTML = icon('x');

        // The time it will stay, drawn down along the foot.
        const bar = document.createElement('span');
        bar.className = 'absolute inset-x-0 bottom-0 h-0.5 bg-accent-ink origin-left rtl:origin-right motion-safe:animate-shrink-x';
        bar.setAttribute('aria-hidden', 'true');

        box.append(row, close, bar);
        this.$refs.toasts.prepend(box);
        requestAnimationFrame(() => box.classList.remove('opacity-0', 'translate-y-2'));

        const gone = () => {
            box.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => box.remove(), 300);
        };
        close.addEventListener('click', gone);
        setTimeout(gone, TOAST_FOR);
    },
});
