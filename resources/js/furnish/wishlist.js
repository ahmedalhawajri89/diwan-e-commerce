// Saved pieces: the nav dropdown, the card bookmarks, and the guest-to-account
// handover.
//
// Moved out of a 277-line <script> block in layouts/furnish.blade.php. The
// behaviour is unchanged; what changed is that the markup it builds carries
// classes instead of styles set one property at a time, and the routes and
// strings come from the bootstrap data block rather than from Blade
// interpolation inside the script.
//
// The guest case is the interesting one. Somebody who has not signed in still
// gets to save pieces — the ids go in localStorage, and the server renders the
// same dropdown from a POSTed list. On sign-in the list is handed over once and
// the local copy is dropped, so nobody loses what they saved by registering.

import { icon, setIcon } from './interactions.js';
import { isAuthenticated, postHeaders, routes, t } from './bootstrap-data.js';
import { toast } from '../components/toast.js';

const GUEST_KEY = 'diwan_wishlist_ids';

// The key carried the shop's old name. A guest's saves under it are moved
// across once, then the old key is dropped: renaming it outright would have
// emptied every guest's saved pieces.
const LEGACY_KEY = 'furnish_wishlist_ids';

/* ── The guest's list ───────────────────────────────────────────────────── */

function guestIds() {
    try {
        const legacy = localStorage.getItem(LEGACY_KEY);

        if (legacy !== null) {
            if (localStorage.getItem(GUEST_KEY) === null) localStorage.setItem(GUEST_KEY, legacy);
            localStorage.removeItem(LEGACY_KEY);
        }

        return JSON.parse(localStorage.getItem(GUEST_KEY)) || [];
    } catch {
        // A private window, cleared site data, or a browser set to block
        // storage. An empty list is the honest answer; throwing here would
        // take the whole nav down over a bookmark.
        return [];
    }
}

function setGuestIds(ids) {
    try {
        localStorage.setItem(GUEST_KEY, JSON.stringify(ids));
    } catch {
        // Nothing to do. The visitor's saves will not survive the page, which
        // is the browser's decision rather than ours to work around.
    }
}

/* ── Failure ────────────────────────────────────────────────────────────── */

/**
 * Silent success, loud failure.
 *
 * A save the visitor can see in the counter and the icon does not get a toast.
 * A save that did not happen does, because otherwise the icon says it did.
 */
function notifyFailure(message) {
    toast(message, 'danger');
}

/**
 * What to tell the visitor when a request did not go through. Always the
 * page's own words: the server's `message` was English on both sites
 * ("Unauthenticated", "CSRF token mismatch.").
 */
function failureFor(response, fallback) {
    // A session that has expired: reloading the page is the fix, so say so.
    if (response && (response.status === 401 || response.status === 419)) {
        return t('sessionExpired');
    }

    return t(fallback);
}

/** Parse JSON only when there is JSON; an error page is not a reply. */
async function readJson(response) {
    try {
        return await response.json();
    } catch {
        return null;
    }
}

/* ── The dropdown ───────────────────────────────────────────────────────── */

function emptyState() {
    const wrap = document.createElement('div');
    wrap.className = 'p-4';

    const line = document.createElement('p');
    line.className = 'm-0 mb-3 text-sm text-muted';
    line.textContent = t('wishlistEmpty');

    const link = document.createElement('a');
    link.href = routes.products;
    link.className = 'text-sm text-accent-ink underline underline-offset-4';
    link.textContent = t('wishlistBrowse');

    wrap.append(line, link);

    return wrap;
}

function actionButton(item, { className, glyph, label }) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = `inline-flex items-center justify-center size-11 p-0 border-0 bg-transparent text-muted cursor-pointer hover:text-ink disabled:opacity-40 disabled:cursor-wait focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-focus ${className}`;
    button.dataset.productId = item.product_id;
    button.title = label;
    button.setAttribute('aria-label', label);
    button.innerHTML = icon(glyph);

    return button;
}

function savedRow(item) {
    const row = document.createElement('div');
    row.className = 'flex items-center gap-3 px-4 py-3 border-0 border-b border-rule';

    const thumbLink = document.createElement('a');
    thumbLink.href = `/product/${item.slug}`;
    thumbLink.tabIndex = -1;
    thumbLink.setAttribute('aria-hidden', 'true');

    const image = document.createElement('img');
    image.src = item.image;
    image.alt = '';
    image.width = 44;
    image.height = 56;
    image.className = 'block w-11 h-14 object-cover bg-paper-2';
    thumbLink.appendChild(image);

    const meta = document.createElement('div');
    meta.className = 'flex-1 min-w-0';

    const name = document.createElement('a');
    name.href = `/product/${item.slug}`;
    name.className = 'block truncate text-sm text-ink no-underline hover:underline';
    name.textContent = item.name;

    const price = document.createElement('div');
    // A piece with no variant has no price; say so in words rather than
    // print "$null" — or worse, a number.
    if (item.price === null || item.price === undefined) {
        price.className = 'text-xs text-muted';
        price.textContent = t('noPrice');
    } else {
        price.className = 'font-mono text-xs text-muted';
        price.dir = 'ltr';
        price.textContent = `${t('currency')}${item.price}`;
    }

    meta.append(name, price);

    const actions = document.createElement('div');
    actions.className = 'flex flex-col';
    actions.append(
        actionButton(item, {
            className: 'move-to-cart-btn',
            glyph: 'bag-plus',
            label: t('moveNamed', { NAME: item.name }),
        }),
        actionButton(item, {
            className: 'remove-wishlist-btn',
            glyph: 'x',
            label: t('removeNamed', { NAME: item.name }),
        }),
    );

    row.append(thumbLink, meta, actions);

    return row;
}

function paintCounters(count) {
    document.querySelectorAll('.wishlist-counter-badge').forEach((badge) => {
        badge.textContent = count;
        badge.hidden = count === 0;
    });

    const headerIcon = document.querySelector('.wishlist-header-icon');

    if (headerIcon) {
        setIcon(headerIcon, count > 0 ? 'bookmark-fill' : 'bookmark');
    }

    // The button's name carries the count: the badge inside it is hidden
    // from screen readers by the button's own label.
    document.getElementById('wishlistDropdownBtn')
        ?.setAttribute('aria-label', count > 0 ? t('savedCount', { COUNT: count }) : t('savedNone'));
}

/** Every card on the page agrees with the list, including ones rendered later. */
function paintCards(items) {
    const saved = items.map((item) => String(item.product_id));

    document.querySelectorAll('.toggle-wishlist').forEach((button) => {
        const isSaved = saved.includes(button.getAttribute('data-product-id'));

        button.classList.toggle('is-saved', isSaved);
        button.setAttribute('aria-pressed', isSaved ? 'true' : 'false');
        setIcon(button, isSaved ? 'bookmark-fill' : 'bookmark');
    });
}

function paint(items, count) {
    paintCounters(count);

    const panel = document.getElementById('wishlistDropdownContent');

    if (panel) {
        // Removing or moving a piece repaints the list and destroys the button
        // that had focus, which dropped a keyboard user back to <body>. Keep
        // them in the list: on the same row's place, or the list itself.
        const hadFocus = panel.contains(document.activeElement);
        const at = hadFocus ? [...panel.querySelectorAll('button')].indexOf(document.activeElement) : -1;

        panel.replaceChildren(
            ...(items.length === 0 ? [emptyState()] : items.map(savedRow)),
        );

        if (hadFocus) {
            const buttons = panel.querySelectorAll('button');
            (buttons[Math.min(at, buttons.length - 1)] ?? panel).focus();
        }
    }

    paintCards(items);
}

/** The list could not be loaded: say so, and offer to try again. */
function paintError() {
    const panel = document.getElementById('wishlistDropdownContent');

    if (!panel) return;

    const wrap = document.createElement('div');
    wrap.className = 'p-4';

    const line = document.createElement('p');
    line.className = 'm-0 mb-3 text-sm text-danger';
    line.textContent = t('loadFailed');

    const retry = document.createElement('button');
    retry.type = 'button';
    retry.className = 'inline-flex items-center min-h-11 p-0 border-0 bg-transparent text-sm text-accent-ink underline underline-offset-4 cursor-pointer';
    retry.textContent = t('retry');
    retry.addEventListener('click', () => refresh());

    wrap.append(line, retry);
    panel.replaceChildren(wrap);
}

/* ── Talking to the server ──────────────────────────────────────────────── */

async function refresh() {
    try {
        if (!isAuthenticated) {
            const ids = guestIds();

            if (ids.length === 0) {
                paint([], 0);

                return;
            }

            const response = await fetch(routes.wishlistGuestDropdown, {
                method: 'POST',
                headers: postHeaders(),
                body: JSON.stringify({ ids }),
            });

            const data = response.ok ? await readJson(response) : null;
            data?.success ? paint(data.items, data.count) : paintError();

            return;
        }

        const response = await fetch(routes.wishlistDropdown, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        });

        const data = response.ok ? await readJson(response) : null;
        data?.success ? paint(data.items, data.count) : paintError();
    } catch {
        // Offline, or the server did not answer: the panel says so instead of
        // "Loading…" for as long as the page is open.
        paintError();
    }
}

/**
 * Toggle a piece. Global, because the layout's own markup and interactions.js
 * both reach for it, and because that was the contract before this file
 * existed.
 */
window.toggleWishlist = async function toggleWishlist(productId, button = null) {
    // Optimistic: the icon flips before the request lands. If the request
    // fails it flips back: before, a failed save left the bookmark saying
    // "saved" for a piece that was not.
    const wasPressed = button?.getAttribute('aria-pressed') === 'true';
    const flip = (pressed) => {
        if (!button) return;
        button.setAttribute('aria-pressed', pressed ? 'true' : 'false');
        button.classList.toggle('is-saved', pressed);
        setIcon(button, pressed ? 'bookmark-fill' : 'bookmark');
    };

    flip(!wasPressed);

    if (!isAuthenticated) {
        const id = String(productId);
        const ids = guestIds();

        setGuestIds(ids.includes(id) ? ids.filter((x) => x !== id) : ids.concat(id));
        refresh();

        return;
    }

    try {
        const response = await fetch(routes.wishlistToggle, {
            method: 'POST',
            headers: postHeaders(),
            body: JSON.stringify({ product_id: productId }),
        });

        const data = response.ok ? await readJson(response) : null;

        if (data?.success) {
            refresh();
        } else {
            flip(wasPressed);
            notifyFailure(failureFor(response, 'updateFailed'));
        }
    } catch {
        flip(wasPressed);
        notifyFailure(t('updateFailed'));
    }
};

async function moveToCart(productId, button = null) {
    if (!isAuthenticated) {
        window.location.href = routes.login;

        return;
    }

    // One request per press: a second click while the first is in flight
    // used to put two in the bag.
    if (button?.disabled) return;
    if (button) button.disabled = true;

    try {
        const response = await fetch(routes.wishlistMoveToCart, {
            method: 'POST',
            headers: postHeaders(),
            body: JSON.stringify({ product_id: productId }),
        });

        const data = response.ok ? await readJson(response) : null;

        if (!data?.success) {
            // The server's refusal for a piece with no price is already in
            // the visitor's language (diwan.flash.no_price); nothing else is.
            notifyFailure(response.status === 422 && data?.message ? data.message : failureFor(response, 'moveFailed'));
            if (button) button.disabled = false;

            return;
        }

        refresh();
        paintBagCount(data.cart_count);
    } catch {
        notifyFailure(t('moveFailed'));
        if (button) button.disabled = false;
    }
}

/** The bag's badge is always in the markup, hidden while the bag is empty. */
function paintBagCount(count) {
    const badge = document.querySelector('[data-bag] [data-bag-count]');

    if (!badge) return;

    badge.textContent = count;
    badge.hidden = count === 0;

    // The link's name was rendered by the server and went stale: "Bag, empty"
    // after a piece had just been moved into it.
    document.querySelector('[data-bag]')
        ?.setAttribute('aria-label', count > 0 ? t('bagCount', { COUNT: count }) : t('bagEmpty'));
}

/* ── Wiring ─────────────────────────────────────────────────────────────── */

export function initWishlist() {
    // Delegated, so the dropdown's own buttons work without re-binding every
    // time it is repainted. The previous version re-attached listeners after
    // each render, which is the same job done once per repaint instead of once.
    document.addEventListener('click', (event) => {
        const remove = event.target.closest('.remove-wishlist-btn');
        const move = event.target.closest('.move-to-cart-btn');

        if (!remove && !move) return;

        event.preventDefault();
        event.stopPropagation();

        const id = (remove ?? move).getAttribute('data-product-id');

        if (remove) {
            window.toggleWishlist(id);
        } else {
            moveToCart(id, move);
        }
    });

    // Signing in hands the guest list over once, then drops the local copy so
    // it cannot be replayed on a later session as somebody else's saves.
    if (isAuthenticated && guestIds().length > 0) {
        // Only dropped once the server has kept it. fetch() resolves on a 419
        // or a 500 too, and the list used to be thrown away on any answer.
        fetch(routes.wishlistSync, {
            method: 'POST',
            headers: postHeaders(),
            body: JSON.stringify({ ids: guestIds() }),
        })
            .then(async (response) => {
                const data = response.ok ? await readJson(response) : null;

                if (data?.success) {
                    try {
                        localStorage.removeItem(GUEST_KEY);
                    } catch {
                        // Storage blocked: nothing to remove.
                    }
                }
            })
            .catch(() => {})
            .finally(refresh);

        return;
    }

    refresh();
}
