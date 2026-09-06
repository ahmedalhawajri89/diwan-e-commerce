// Diwan — the behaviour that used to live in `on*` attributes in the markup.
//
// Why it moved
// ───────────────────────────────────────────────────────────────────────────
// An `onclick` attribute cannot be covered by a CSP nonce or hash. Eleven of
// them forced `script-src 'unsafe-inline'` on the whole application, which
// defeats the point of having a policy at all: the one directive that stops
// an injected <script> from running was being switched off so a bookmark
// icon could toggle.
//
// Everything here is delegated from `document`, so it also works for markup
// that arrives later — the search results panel and the saved-pieces dropdown
// both render cards after load, and previously had to re-bind by hand.
//
// See design.md § The helper layer for the same argument about `style`.

/* ── Icons ────────────────────────────────────────────────────────────────
   Icons are <svg><use href="#i-name"> drawn from a sprite, not glyphs in a
   webfont. Swapping one is therefore repointing the <use>, not toggling a
   class — so the two helpers below exist for the layout's wishlist script,
   which has to switch a bookmark between hollow and filled and to build
   buttons that did not exist when the page was parsed.

   Exposed on `window` because the layout's script is inline: it needs the
   routes and translated strings only the server has, and cannot import. That
   is the same contract `window.toggleWishlist` already follows. */

export const icon = (name) =>
    `<svg class="hm-icon" aria-hidden="true" focusable="false"><use href="#i-${name}"></use></svg>`;

/** Repoint an existing icon at a different glyph in the sprite. */
export function setIcon(element, name) {
    // Accepts the <svg> itself or anything containing one, so a caller can
    // pass a button without knowing how its icon is nested.
    const svg = element instanceof SVGElement ? element : element?.querySelector('svg');
    svg?.querySelector('use')?.setAttribute('href', `#i-${name}`);
}

// Still on `window` as well: the admin layout's remaining inline script has no
// import of its own. Remove these two lines when that script moves out too.
window.diwanIcon = icon;
window.diwanSetIcon = setIcon;

/* ── Forms that submit themselves ─────────────────────────────────────────
   A filter checkbox, a sort select, a quantity stepper, the room a saved
   piece belongs to. Seven controls carried `onchange="this.form.submit()"`.

   `.submit()` rather than `.requestSubmit()`, deliberately: requestSubmit
   fires the submit event and runs constraint validation, so the cart's
   `min="1"` would start blocking a quantity of 0 on the client. That is
   arguably an improvement, but it is a behaviour change, and this pass is
   moving code rather than deciding questions the markup had already
   answered. Worth revisiting on its own. */

document.addEventListener('change', (event) => {
    const control = event.target.closest('[data-autosubmit]');
    if (!control) return;

    const form = control.form || control.closest('form');
    if (form) form.submit();
});

/* ── Saved pieces ─────────────────────────────────────────────────────────
   `window.toggleWishlist` is defined by the layout, because it needs routes
   and translated strings that only the server knows. It is already treated as
   a global there — the saved-pieces dropdown calls it the same way — so this
   listener follows the existing contract rather than inventing a second one.

   The element is passed through so the function can flip `aria-pressed` and
   the icon optimistically, which is what the inline handler did. */

document.addEventListener('click', (event) => {
    const button = event.target.closest('.toggle-wishlist');
    if (!button) return;

    // A card's whole surface is a stretched link to the product. The save
    // button sits above it on its own layer, and must not also navigate.
    event.preventDefault();

    const id = button.dataset.productId;
    if (id && typeof window.toggleWishlist === 'function') {
        window.toggleWishlist(id, button);
    }
});

/* ── Product gallery ──────────────────────────────────────────────────── */

document.addEventListener('click', (event) => {
    const thumb = event.target.closest('[data-hero-src]');
    if (!thumb) return;

    const hero = document.getElementById('heroImage');
    if (!hero) return;

    hero.src = thumb.dataset.heroSrc;

    // The alternate views are a set of radio-like controls, so say which one
    // is showing. The inline version said nothing, and a screen-reader user
    // pressing these got no feedback at all.
    document.querySelectorAll('[data-hero-src]').forEach((el) => {
        el.setAttribute('aria-pressed', el === thumb ? 'true' : 'false');
    });
});

/* ── Card images ──────────────────────────────────────────────────────────
   `.hm-card__media img` starts at `opacity: 0` and is revealed by `is-loaded`,
   so this listener is not decoration: without it the catalogue is blank.

   That makes the cached-image race a real hazard rather than a nicety. An
   `onload` attribute is parsed before the image can finish loading, so it
   cannot be missed; a listener attached from a deferred module can be — the
   image may already be complete by the time this file runs. So each image is
   checked for `complete` as well as listened to.

   `error` reveals too. A broken image should show the browser's own broken
   icon and its alt text, not a hole where a product used to be. */

const FADED = '.hm-card__media img';

function reveal(image) {
    image.classList.add('is-loaded');
}

function revealSettled(root = document) {
    root.querySelectorAll(FADED).forEach((image) => {
        if (image.complete) reveal(image);
    });
}

// `load` and `error` do not bubble, so these listen in the capture phase.
// That covers images added after load — search results, the saved dropdown —
// without anything having to re-bind.
document.addEventListener('load', (event) => {
    const image = event.target;
    if (image instanceof HTMLImageElement && image.matches(FADED)) reveal(image);
}, true);

document.addEventListener('error', (event) => {
    const image = event.target;
    if (image instanceof HTMLImageElement && image.matches(FADED)) reveal(image);
}, true);

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => revealSettled());
} else {
    revealSettled();
}

// Coming back through the bfcache re-runs no scripts but can re-fire nothing
// either, so settle whatever is on screen.
window.addEventListener('pageshow', () => revealSettled());
