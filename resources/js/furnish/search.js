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

import { routes, t } from './bootstrap-data.js';

const MIN_QUERY = 2;
const DEBOUNCE_MS = 300;
const SPINNER_DELAY_MS = 150;

/** How many results the endpoint returns before "browse all" is worth offering. */
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
    row.className = 'group flex items-center gap-4 py-3 border-0 border-b border-rule text-ink no-underline hover:bg-paper-2';

    const image = document.createElement('img');
    image.src = product.image;
    image.alt = '';
    image.width = 56;
    image.height = 72;
    image.className = 'w-14 h-[4.5rem] shrink-0 object-cover bg-paper-2';

    const meta = document.createElement('div');
    meta.className = 'flex-1 min-w-0';

    const name = document.createElement('div');
    name.className = 'font-display text-lg leading-snug truncate';
    name.textContent = product.name;

    const category = document.createElement('div');
    category.className = 'font-label text-xs uppercase tracking-label text-muted';
    category.textContent = product.category;

    meta.append(name, category);

    const price = document.createElement('div');
    price.className = 'font-mono text-sm text-accent-ink';
    price.dir = 'ltr';
    price.textContent = `$${product.price}`;

    row.append(image, meta, price);

    return row;
}

function render(results, products, query) {
    if (!products.length) {
        results.replaceChildren(note(t('searchNone', { QUERY: query })));

        return;
    }

    const list = document.createElement('div');
    products.forEach((product) => list.appendChild(resultRow(product)));

    const children = [list];

    // The endpoint caps its result set, so a full page means there is probably
    // more. Offer the catalogue rather than pretending these are all of them.
    if (products.length === PAGE_SIZE) {
        const more = document.createElement('a');
        more.href = `${routes.products}?q=${encodeURIComponent(query)}`;
        more.className = 'inline-block mt-6 pb-1 border-0 border-b border-current text-sm text-accent-ink no-underline';
        more.textContent = t('browseAll');
        children.push(more);
    }

    results.replaceChildren(...children);
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

    const stopSpinner = () => {
        clearTimeout(spinnerTimer);
        if (spinner) spinner.hidden = true;
    };

    // A native <dialog>: `autofocus` on the input puts the cursor there when
    // it opens, and `close` fires however it was dismissed.
    modal.addEventListener('close', () => {
        input.value = '';
        results.replaceChildren();
        stopSpinner();
    });

    input.addEventListener('input', () => {
        const query = input.value.trim();

        clearTimeout(queryTimer);
        stopSpinner();

        if (query.length < MIN_QUERY) {
            results.replaceChildren();

            return;
        }

        spinnerTimer = setTimeout(() => {
            if (spinner) spinner.hidden = false;
        }, SPINNER_DELAY_MS);

        queryTimer = setTimeout(async () => {
            try {
                const response = await fetch(`${routes.search}?q=${encodeURIComponent(query)}`);
                const products = await response.json();

                stopSpinner();
                render(results, products, query);
            } catch {
                stopSpinner();
                results.replaceChildren(note(t('searchError'), 'danger'));
            }
        }, DEBOUNCE_MS);
    });
}
