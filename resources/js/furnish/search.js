// Catalogue search, as it behaves in the nav's modal.
//
// Moved out of a <script> block in layouts/furnish.blade.php. Nothing about
// the behaviour changed except that the markup it builds now carries classes
// instead of inline styles — the script was setting `row.style.borderBottom`
// and `p.style.fontSize` on every element it created, which is the same
// problem as a `style` attribute in a template, written somewhere a
// stylesheet audit could not see it.
//
// The rest is as it was, including the two decisions worth keeping:
//   * a 300ms debounce, so a query is one request rather than one per keystroke;
//   * a spinner that delay-shows by 150ms, so a fast query never flashes it.

import { money, routes, t } from './bootstrap-data.js';

const MIN_QUERY = 2;
const DEBOUNCE_MS = 300;
const SPINNER_DELAY_MS = 150;

/**
 * How many results are shown. The endpoint returns one more than this, so a
 * sixth result is how the page knows there is more to browse. It used to
 * return six and test for exactly five, so "browse all" appeared when there
 * was nothing more and never when there was.
 */
const PAGE_SIZE = 5;

function note(text, variant = 'muted') {
    const p = document.createElement('p');
    p.className = variant === 'danger'
        ? 'm-0 px-4 py-3 bg-danger-wash text-danger text-sm'
        : 'm-0 text-sm text-muted';
    p.textContent = text;

    return p;
}

function resultRow(product) {
    const row = document.createElement('a');
    row.href = `/product/${product.slug}`;
    row.className = 'group flex items-center gap-4 p-2 -m-2 text-ink no-underline hover:bg-paper-2 focus-visible:outline-2 focus-visible:outline-focus';

    const image = document.createElement('img');
    image.src = product.image;
    image.alt = '';
    image.width = 64;
    image.height = 80;
    image.loading = 'lazy';
    image.className = 'w-16 h-20 shrink-0 object-cover bg-paper-2 transition-transform duration-300 group-hover:scale-[1.03]';

    const meta = document.createElement('div');
    meta.className = 'flex-1 min-w-0';

    const name = document.createElement('div');
    name.className = 'font-display text-lg leading-snug line-clamp-2';
    name.textContent = product.name;

    const category = document.createElement('div');
    category.className = 'font-label text-xs uppercase tracking-label text-muted';
    category.textContent = product.category;

    meta.append(name, category);

    const price = document.createElement('div');
    // A piece with no variant has no price; say so in words rather than
    // print "$null" — or worse, a number.
    if (product.price === null || product.price === undefined) {
        price.className = 'text-xs text-muted';
        price.textContent = t('noPrice');
    } else {
        // The line follows the page, so in Arabic the price sits under the
        // name and reads figure-then-unit, as the server writes it.
        price.className = 'font-mono text-sm text-accent-ink';
        price.textContent = money(product.price);
    }

    meta.append(price);
    row.append(image, meta);

    return row;
}

function render(results, products, query) {
    announce(products.length === 0 ? t('searchNone', { QUERY: query }) : t('searchCount', { QUERY: query, COUNT: Math.min(products.length, PAGE_SIZE) + (products.length > PAGE_SIZE ? '+' : '') }));

    if (!products.length) {
        results.replaceChildren(note(t('searchNone', { QUERY: query })));

        return;
    }

    const list = document.createElement('div');
    list.className = 'grid gap-5 sm:grid-cols-2';
    products.slice(0, PAGE_SIZE).forEach((product) => list.appendChild(resultRow(product)));

    const children = [list];

    // More than a page came back: offer the catalogue rather than pretending
    // these are all of them.
    if (products.length > PAGE_SIZE) {
        const more = document.createElement('a');
        more.href = `${routes.products}?q=${encodeURIComponent(query)}`;
        more.className = 'inline-block mt-6 pb-1 border-0 border-b border-current text-sm text-accent-ink no-underline';
        more.textContent = t('browseAll');
        children.push(more);
    }

    results.replaceChildren(...children);
}

/* ── Recent searches ──────────────────────────────────────────────────────
   The visitor's last five, kept on their own device only. Saved when a
   search leads somewhere: a result opened, or Enter to the catalogue. */

const RECENT_KEY = 'diwan_recent_searches';
const RECENT_MAX = 5;

function recent() {
    try {
        return JSON.parse(localStorage.getItem(RECENT_KEY)) || [];
    } catch {
        return [];
    }
}

function remember(query) {
    const q = query.trim();
    if (q.length < MIN_QUERY) return;
    try {
        localStorage.setItem(RECENT_KEY, JSON.stringify([q, ...recent().filter((x) => x !== q)].slice(0, RECENT_MAX)));
    } catch {
        // Storage blocked: nothing is remembered, which is the visitor's choice.
    }
}

function paintRecent(onPick) {
    const box = document.getElementById('searchRecent');
    const list = document.getElementById('searchRecentList');
    if (!box || !list) return;

    const items = recent();
    box.hidden = items.length === 0;
    list.replaceChildren(...items.map((q) => {
        const li = document.createElement('li');
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'inline-flex items-center min-h-11 px-4 rounded-pill bg-paper-2 border-0 text-sm text-ink cursor-pointer hover:bg-paper-3 focus-visible:outline-2 focus-visible:outline-focus';
        b.dir = 'auto';
        b.textContent = q;
        b.addEventListener('click', () => onPick(q));
        li.appendChild(b);
        return li;
    }));
}

/** Results are announced: the list itself is silent to a screen reader. */
function announce(text) {
    const status = document.getElementById('searchStatus');
    if (status) status.textContent = text;
}

export function initSearch() {
    // One modal, one listener. An earlier build shipped two modals sharing an
    // id and bound to the one that never opened.
    const modal = document.getElementById('searchModal');
    const input = document.getElementById('searchInput');
    const spinner = document.getElementById('searchSpinner');
    const results = document.getElementById('searchResults');

    if (!modal || !input || !results) return;

    let queryTimer;
    let spinnerTimer;
    let inFlight = null;   // the request for the latest query; older ones are aborted
    const idle = document.getElementById('searchIdle');
    const showIdle = (on) => {
        if (idle) idle.hidden = !on;
    };
    const pick = (q) => {
        input.value = q;
        input.dispatchEvent(new Event('input'));
        input.focus();
    };

    paintRecent(pick);
    document.getElementById('searchRecentClear')?.addEventListener('click', () => {
        try {
            localStorage.removeItem(RECENT_KEY);
        } catch {
            // nothing stored
        }
        paintRecent(pick);
        input.focus();
    });

    // A result opened, or Enter to the whole catalogue: remember the words.
    results.addEventListener('click', (event) => {
        if (event.target.closest('a')) remember(input.value);
    });
    input.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' || input.value.trim().length < MIN_QUERY) return;
        event.preventDefault();
        remember(input.value);
        window.location.href = `${routes.products}?q=${encodeURIComponent(input.value.trim())}`;
    });

    const stopSpinner = () => {
        clearTimeout(spinnerTimer);
        if (spinner) spinner.hidden = true;
    };

    // A native <dialog>: `autofocus` on the input puts the cursor there when
    // it opens, and `close` fires however it was dismissed.
    modal.addEventListener('close', () => {
        // A response that lands after the dialog closed must not refill it.
        inFlight?.abort();
        input.value = '';
        results.replaceChildren();
        announce('');
        stopSpinner();
        showIdle(true);
        paintRecent(pick);
    });

    input.addEventListener('input', () => {
        const query = input.value.trim();

        clearTimeout(queryTimer);
        stopSpinner();

        if (query.length < MIN_QUERY) {
            results.replaceChildren();
            showIdle(true);

            return;
        }

        showIdle(false);

        spinnerTimer = setTimeout(() => {
            if (spinner) spinner.hidden = false;
        }, SPINNER_DELAY_MS);

        queryTimer = setTimeout(async () => {
            // A slow answer for "so" must not overwrite the answer for "sofa".
            inFlight?.abort();
            const controller = new AbortController();
            inFlight = controller;

            try {
                const response = await fetch(`${routes.search}?q=${encodeURIComponent(query)}`, { signal: controller.signal });
                const products = await response.json();

                stopSpinner();
                render(results, products, query);
            } catch (error) {
                if (error.name === 'AbortError') return;

                stopSpinner();
                announce(t('searchError'));
                results.replaceChildren(note(t('searchError'), 'danger'));
            }
        }, DEBOUNCE_MS);
    });
}
