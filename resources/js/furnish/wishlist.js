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

const GUEST_KEY = 'furnish_wishlist_ids';

/* ── The guest's list ───────────────────────────────────────────────────── */

function guestIds() {
    try {
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
 * Silent success, loud failure — design.md's microinteractions stance.
 *
 * A save the visitor can see in the counter and the icon does not get a toast.
 * A save that did not happen does, because otherwise the icon says it did.
 */
function notifyFailure(message) {
    if (typeof window.flasher !== 'undefined') {
        window.flasher.error(message);
    } else if (typeof window.Swal !== 'undefined') {
        window.Swal.fire({
            toast: true,
            position: 'bottom-end',
            icon: 'error',
            title: message,
            showConfirmButton: false,
            timer: 4000,
        });
    }
}

/* ── The dropdown ───────────────────────────────────────────────────────── */

function emptyState() {
    const wrap = document.createElement('div');
    wrap.className = 'p-3';

    const line = document.createElement('p');
    line.className = 'hm-muted hm-t-sm';
    line.textContent = t('wishlistEmpty');

    const link = document.createElement('a');
    link.href = routes.products;
    link.className = 'hm-link hm-link--sm';
    link.textContent = t('wishlistBrowse');

    wrap.append(line, link);

    return wrap;
}

function actionButton(item, { className, glyph, label }) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = `hm-util hm-savedrow__action ${className}`;
    button.dataset.productId = item.product_id;
    button.title = label;
    button.setAttribute('aria-label', label);
    button.innerHTML = icon(glyph);

    return button;
}

function savedRow(item) {
    const row = document.createElement('div');
    row.className = 'hm-savedrow';

    const thumbLink = document.createElement('a');
    thumbLink.href = `/product/${item.slug}`;

    const image = document.createElement('img');
    image.src = item.image;
    image.alt = '';
    image.width = 44;
    image.height = 56;
    image.className = 'hm-savedrow__thumb';
    thumbLink.appendChild(image);

    const meta = document.createElement('div');
    meta.className = 'hm-fill';

    const name = document.createElement('a');
    name.href = `/product/${item.slug}`;
    name.className = 'hm-savedrow__name text-truncate';
    name.textContent = item.name;

    const price = document.createElement('div');
    price.className = 'hm-num hm-muted hm-t-xs';
    price.dir = 'ltr';
    price.textContent = `$${item.price}`;

    meta.append(name, price);

    const actions = document.createElement('div');
    actions.className = 'd-flex flex-column';
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
        badge.classList.toggle('d-none', count === 0);
    });

    const headerIcon = document.querySelector('.wishlist-header-icon');

    if (headerIcon) {
        setIcon(headerIcon, count > 0 ? 'bookmark-fill' : 'bookmark');
    }
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
        panel.replaceChildren(
            ...(items.length === 0 ? [emptyState()] : items.map(savedRow)),
        );
    }

    paintCards(items);
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

            const data = await response.json();
            if (data.success) paint(data.items, data.count);

            return;
        }

        const response = await fetch(routes.wishlistDropdown, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        });

        const data = await response.json();
        if (data.success) paint(data.items, data.count);
    } catch (error) {
        console.error('diwan: could not load saved pieces', error);
    }
}

/**
 * Toggle a piece. Global, because the layout's own markup and interactions.js
 * both reach for it, and because that was the contract before this file
 * existed.
 */
window.toggleWishlist = async function toggleWishlist(productId, button = null) {
    // Optimistic: the icon flips before the request lands, and refresh()
    // corrects it if the server disagrees.
    if (button) {
        const pressed = button.getAttribute('aria-pressed') === 'true';
        button.setAttribute('aria-pressed', pressed ? 'false' : 'true');
        button.classList.toggle('is-saved', !pressed);
    }

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

        const data = await response.json();

        if (data.success) {
            refresh();
        } else {
            notifyFailure(data.message || t('updateFailed'));
        }
    } catch {
        notifyFailure(t('updateFailed'));
    }
};

async function moveToCart(productId) {
    if (!isAuthenticated) {
        window.location.href = routes.login;

        return;
    }

    try {
        const response = await fetch(routes.wishlistMoveToCart, {
            method: 'POST',
            headers: postHeaders(),
            body: JSON.stringify({ product_id: productId }),
        });

        const data = await response.json();

        if (!data.success) {
            notifyFailure(data.message || t('moveFailed'));

            return;
        }

        refresh();
        paintBagCount(data.cart_count);
    } catch {
        notifyFailure(t('moveFailed'));
    }
}

/** The bag's badge does not exist until the bag has something in it. */
function paintBagCount(count) {
    const bag = document.querySelector('[data-bag]');

    if (!bag) return;

    const badge = bag.querySelector('.hm-util__count');

    if (badge) {
        badge.textContent = count;

        return;
    }

    if (count > 0) {
        const created = document.createElement('span');
        created.className = 'hm-util__count';
        created.textContent = count;
        bag.appendChild(created);
    }
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
            moveToCart(id);
        }
    });

    // Signing in hands the guest list over once, then drops the local copy so
    // it cannot be replayed on a later session as somebody else's saves.
    if (isAuthenticated && guestIds().length > 0) {
        fetch(routes.wishlistSync, {
            method: 'POST',
            headers: postHeaders(),
            body: JSON.stringify({ ids: guestIds() }),
        })
            .then(() => localStorage.removeItem(GUEST_KEY))
            .catch(() => {})
            .finally(refresh);

        return;
    }

    refresh();
}
