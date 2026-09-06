// The seam between the server and the front end.
//
// Everything under this directory used to live in <script> blocks inside
// layouts/furnish.blade.php — 431 lines of search, wishlist and menu logic —
// for one reason: it needed routes, a CSRF token and translated strings, and a
// Blade template was the only thing that had them.
//
// The server now writes those into a `type="application/json"` block and the
// modules read it here. JSON in a non-executable script tag is data: a
// translated string containing a quote cannot become code, which is not true
// of `window.X = {{ $value }}`.
//
// Read once at module load. The block is rendered in <head>'s wake, before any
// module runs, and nothing rewrites it.

const EMPTY = {
    authenticated: false,
    csrf: '',
    routes: {},
    i18n: {},
};

function read() {
    const node = document.getElementById('diwan-data');

    if (!node) {
        // Admin pages use a different layout and do not render the block.
        // Returning a shape rather than null means every caller reads
        // `data.routes.x` and gets undefined, instead of throwing on `.routes`.
        return EMPTY;
    }

    try {
        return { ...EMPTY, ...JSON.parse(node.textContent) };
    } catch (error) {
        // A malformed block means the page is broken in a way this module
        // cannot fix. Say so once, then behave as if there were no data —
        // the storefront still renders, it just does not enhance.
        console.error('diwan: could not parse the bootstrap data block', error);

        return EMPTY;
    }
}

export const data = read();

export const routes = data.routes;
export const isAuthenticated = data.authenticated;

/**
 * A translated string, with `__PLACEHOLDER__` substitutions.
 *
 * Falls back to the key itself rather than to English: a missing string is a
 * bug, and a key on screen says which one. Silent English would look like a
 * translation nobody got round to.
 */
export function t(key, replacements = {}) {
    let line = data.i18n[key] ?? key;

    for (const [name, value] of Object.entries(replacements)) {
        line = line.replaceAll(`__${name}__`, value);
    }

    return line;
}

/** Headers every same-origin write in this app sends. */
export function postHeaders() {
    return {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': data.csrf,
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
    };
}
