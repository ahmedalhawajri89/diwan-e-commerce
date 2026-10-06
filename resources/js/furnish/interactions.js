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
    `<svg data-icon aria-hidden="true" focusable="false"><use href="#i-${name}"></use></svg>`;

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

/* ── Arabic-Indic digits ─────────────────────────────────────────────────
   A measurement field takes what an Arabic keyboard types (٣٥٠) and shows
   it as the site's figures (350) as it is typed. The server converts them
   too (App\Http\Middleware\WesternDigits), for a form sent without this. */

const ARABIC_DIGITS = /[٠-٩۰-۹]/g;

document.addEventListener('input', (event) => {
    const field = event.target.closest?.('[data-digits]');
    if (!field || !ARABIC_DIGITS.test(field.value)) return;

    ARABIC_DIGITS.lastIndex = 0;
    field.value = field.value.replace(ARABIC_DIGITS, (d) => String(d.charCodeAt(0) & 0xf));
});

/* ── No form submits itself ─────────────────────────────────────────────
   Filters, the sort, quantities and the room picker all used to submit on
   `change`. Each now waits for a button, because a keyboard user moving
   through a select or stepping a number must not reload the page on the way.
   design.md § Utilities and components; DesignSystemTest keeps it so. */

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

