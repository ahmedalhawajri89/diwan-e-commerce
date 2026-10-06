# Audit — Diwan, 2026-10-06

Read before writing: design.md (all 502 lines), README.md, `DesignSystemTest`,
`StylesheetCoverageTest`, the last 30 commits, `Fit.php` and `FitTest`.

How each section was checked:
- **Code:** every finding carries a file and line. Each was read in the source; none comes from memory.
- **Browser:** pages were rendered by system Chrome through Playwright, against `php artisan serve` on port 8010 with the local seed data.
  - 31 pages × 2 languages at 390 × 844, as guest, customer and admin.
  - The script and screenshots are in the session scratchpad, not the repo.
- **Contrast:** computed from the oklch tokens in `resources/css/app.css`. For text over a photograph, it was sampled from the real JPEG with its overlay composited.

State at the time of the audit:
- **Tests:** `php artisan test` passes, 181 tests and 624 assertions.
- **Working tree:** 216 entries in `git status`, 168 of them tracked files changed (+4,525 / −7,892), plus about 50 untracked.

Findings are ordered by **impact on the person using the page**. The order is not by ease of fixing.

**Progress** (on branch `quality-pass`):
- **§ 0 decided:** design.md was amended to the shipped system, and the `hm-` rule was retired.
- **§ G done:** the uncommitted work is committed in five slices.
- **Fixed:**
  - The four-column footer is now one row of links (design.md ban).
  - The footer's `transition-all` is gone.
  - Footer touch targets (part of E4) are at least 44px.
  - B1: an unpriced piece can't be bought, and the bag charges the price the page shows.
  - "Free" shipping is now "confirmed when we contact you".
  - D17: the JS rows read the currency from the page.

---

## 0 · Before anything else: the law and the code have split

The brief says design.md is the law and the `hm-` prefix stays. The uncommitted
working tree contradicts design.md on almost every visual axis. The tests now
enforce the working tree, not the document. Two of the brief's own rules cannot
both hold until you decide which side wins.

| design.md says | The working tree does | Where |
| --- | --- | --- |
| Accent is drafting-ink blue, `oklch(44% 0.144 255)`. The cool signal is the reason for the palette (§ Theme, § Colour). | The accent is espresso, `oklch(21% 0.020 55)`, the same value as ink. It adds brass and a dark `deep` band, so the blue no longer appears anywhere. | `resources/css/app.css:27-66` |
| Bricolage Grotesque 700 display, Geist body, Rubik + IBM Plex Arabic. "display-style = geometric-sans". | Cormorant Garamond (serif) display and Markazi Text for Arabic. | `app.css:70-74`, `:155-157`, `package.json` |
| The contrast table in § Colour was measured on the blue palette. | None of those numbers describes the shipped palette any longer. | design.md:133-143 |
| Accent covers at most 3 % of any viewport; it is "never a colour block". | The deep band fills the top banner, the whole footer and the home fit band. Those are colour blocks of the darkest tone. | `layouts/furnish.blade.php:302`, `furnish/index.blade.php` |
| **Footer Ft5:** one statement sentence and one inline row of links. **"Ft3 is banned project-wide."** | A four-column link footer: Shop · House · Help · Account. | `layouts/furnish.blade.php:328-390` |
| Three motion primitives only. "No scroll-triggered fade-up on every section." | `data-reveal` on 7 storefront pages (13 uses on home), plus parallax, `scroll-fade`, `unveil`, `unveil-zoom`, view transitions, and a looping scroll cue. | `index.blade.php:16,60,103,313,318`; `app.css:223-300` |
| Primary CTA in sentence case, an imperative verb phrase. | Every button is uppercase and tracked, e.g. "CHECKOUT". | `components/ui/button.blade.php:21` |
| "The `hm-` class prefix is retained." This is also a hard rule in your brief. | It is gone, and a test forbids it from coming back. | `tests/Unit/DesignSystemTest.php:306` |
| "Bootstrap 5's grid and utility classes" are preserved; `_helpers.scss` is the helper layer. | Bootstrap and SCSS have been deleted, and a test forbids both. | `DesignSystemTest.php:509-537` |
| Six permitted inline styles, including the lookbook hotspots. | Three. The lookbook hotspots were removed, which was the right call. | `DesignSystemTest.php:53` |
| `transition: all` is banned, and width never animates. | Both happen, on the footer link hairline. | `layouts/furnish.blade.php:300` |
| Marketing pages use the Split Studio diptych. | Home has a full-bleed cinematic hero with the header lying over it. | `index.blade.php:16-110` |

All of this arrived in one set of sessions on 2026-10-04 (the Tailwind
migration). Some of it was your explicit choice in those sessions: the palette and
fonts ("walnut & linen", Cormorant/Markazi), the single cinematic hero, and the
four-column footer. The migration never amended design.md beyond swapping file
paths (`git diff design.md` shows only that), so the document now describes a
site that no longer exists.

**Decision needed (yours, not mine).** Either:
1. **Amend design.md to the shipped system.** Re-measure the contrast table, record the new palette, type, footer and motion with the reason for each, and rewrite § Implementation boundaries and § The helper layer. Drop the `hm-` rule: it named a layer that no longer exists, and keeping it now would mean reintroducing dead class names. **This is my recommendation.**
2. **Revert the visual layer to design.md.** That throws away the 2026-10-04 work.

The hard bans in your brief (no four-column footer, no `transition: all`) are a
separate question from 1 or 2. If design.md is amended, they need either a fix
or an explicit, argued exception written into the document.

Smaller notes on the same theme:
- README:35 already describes the new fonts, so README and design.md disagree with each other too.
- "Playwright is in this project" is not true. It is not in `package.json`. The 2026-10-04 checks used a copy of `playwright-core` installed outside the repo, and so did this audit.

---

## Cross-cutting: the ten that hurt people most

The proposed order of work. The details are in the sections below.

1. **A customer's message goes to a stranger.** The contact form mails `admin@furnish.com`, a real third-party domain. *(A1)*
2. **A piece with no price is sold at $50.** The page says "Price on request", but the bag stores 50.00, and Move to bag does the same. This is an invented number in the till. *(B1)*
3. **Arabic shoppers get English errors.** There is no `lang/ar/validation.php`, `auth.php` or `passwords.php`, so the result reads "The الهاتف field is required." The reset and verification emails are English too. *(D1, D2)*
4. **The fit filter hides pieces that fit.** It runs after `paginate(12)`, so page 1 can be empty while pages 2+ hold matches, and the pager is hidden. *(B2)*
5. **Changing a filter, the sort or a quantity reloads the page.** A keyboard user cannot step a quantity from 1 to 3; each arrow press reloads. *(C1)*
6. **The fit system's own control is a 16 px touch target.** "Set your room" in the top band measures 100 × 16 on a phone. *(E1)*
7. **The success banner prints a raw slug.** After saving the profile it says `profile-updated`, in both languages. *(D3)*
8. **Double-submits double the order.** Add to bag and Move to bag have no submit guard, and the server adds the quantity again. *(B5, B11)*
9. **Hero and category-tile text fails contrast over the photographs.** The lede has a median of 4.2:1 on a phone; tile counts are 1.9–2.5:1. *(C3)*
10. **The product page's LCP image is the full 1600 × 2000 JPEG** (~750 KB on a phone), although its AVIF/WebP derivatives already exist. *(F1)*

---

## A · Identity drift (Furnish → Diwan)

No URL contains "furnish", so there is no SEO or redirect exposure. Page
titles and `og:site_name` already read `diwan.brand.name`.

### User-visible, highest impact first

| # | Trace | Where | Impact |
| --- | --- | --- | --- |
| A1 | `Mail::to('admin@furnish.com')` | `app/Http/Controllers/ContactController.php:23` | **Customer messages go to an unrelated domain.** The same method flashes English text that mentions "Mailtrap" to the public (`:26`, `:29`). |
| A2 | `APP_NAME=Laravel` | `.env.example:1` | Mail goes out "From: Laravel". It also names the session cookie and cache prefix (`config/session.php:130`, `config/cache.php:115`). |
| A3 | "Furnish Store" in the staff contact email | `resources/views/emails/contact.blade.php:44` | Staff see the old name. |
| A4 | Clone URL `…/furnish.git`, `cd furnish` | `README.md:49,53` | Public docs. |
| A5 | Demo accounts `@fu.local` | `database/seeders/AdminUserSeeder.php:29,44` | Local only. `.local` is reserved for mDNS; `.test` is the right TLD. |

### Internal (no user ever sees these)

| Trace | Count | Where |
| --- | --- | --- |
| Route names `furnish.*` | 9 routes; ~160 `route()` references, ~70 of them in tests | `routes/web.php:27-43`, `SitemapController.php:64-69`, `layouts/furnish.blade.php:35-39` |
| `FurnishController` | 1 class, 10 bindings | `app/Http/Controllers/FurnishController.php:14` |
| View directory `furnish/` | 15 templates, 15 `view('furnish.…')` calls | FurnishController (11), Cart, Checkout, OrderHistory, RoomBoard |
| `layouts/furnish` | 20 `@extends`, plus one composer bound **by name** | `AppServiceProvider.php:49` |
| `resources/js/furnish/` | 7 modules, 1 import | `resources/js/app.js:11` |
| localStorage key `furnish_wishlist_ids` | 1 | `resources/js/furnish/wishlist.js:19`. **Stateful:** renaming it would erase every guest's saved pieces. |
| Dead files `lang/{en,ar}/furnish.php` | 67 keys each, no caller | Still brand "FURNISH" / "فرنِش" |
| Comments naming Furnish or Atelier | ~10 | `StorefrontNavComposer.php:15`, `errors/minimal.blade.php:5`, the `js/furnish/*` headers, `design.md:8,87,394` |
| `composer.json` name | `laravel/laravel` with the skeleton description | — |

Not drift: the cache keys `diwan.nav.categories` and `diwan.sitemap`, and the session key `diwan.room`, already use the new name.

### Rename plan: nothing deleted, nothing broken

**Step 1 — user-visible.**
- **Contact recipient.** Read it from `config('mail.contact_to')` (env `MAIL_CONTACT_TO`).
  - **Your input:** the real address.
  - The two flashes move to `diwan.flash.*`.
- **APP_NAME and mail sender.** Set `APP_NAME=Diwan` and `MAIL_FROM_NAME=Diwan` in `.env.example`.
  - **Risk:** this renames the session cookie, which signs everyone out and drops every saved room.
  - Pin `SESSION_COOKIE=laravel-session` (or accept a one-time sign-out).
- **Staff email footer.** Use `__('diwan.brand.name')`.
- **README.** Update the clone URL after the GitHub repo is renamed; GitHub redirects the old one.

**Step 2 — persisted state, behind shims.**
- **localStorage.** Read `furnish_wishlist_ids` once, copy it to `diwan_wishlist_ids`, then delete the old key. Keep the shim for a release.
- **Demo emails.** Update them in place (`where email = admin@fu.local → update`) before any `firstOrCreate`. Otherwise a second, unprotected admin is created beside the protected one.

**Step 3 — internal; old names keep resolving.**
- **Controller.**
  - Add `StorefrontController` with the body.
  - `FurnishController` becomes an empty `@deprecated` subclass.
  - Routes point at the new class.
  - A subclass is safer than `class_alias`, which route caching cannot see.
- **Route names.**
  - **Keep `furnish.*`.** Laravel allows one name per route. Registering duplicates behaves differently cached and uncached, and it breaks `routeIs()` in the nav.
  - The names never reach a user, so the honest cost of keeping them is zero.
- **Views.**
  - Move `furnish/*` to `shop/*`.
  - Leave a one-line `@extends` pass-through for each old name, or skip the move entirely. *(Your call; I'd skip it.)*
- **Layout.**
  - `layouts/storefront` holds the content, and `layouts/furnish` becomes `@extends('layouts.storefront')`.
  - Bind the composer to **both** names, because it is bound by name.
- **JS directory.** `git mv resources/js/furnish resources/js/storefront` and update the one import. This is safe because only `app.js` is a Vite entry.
- **Dead files.** `lang/*/furnish.php` and `resources/js/furnish/custom.js` have no caller.
  - Your brief bans deleting routes, controllers and the like, not dead lang files.
  - I'd still ask before removing them.

---

## B · Missing UI states

### High

| # | Finding | Where | What the user sees / what's missing |
| --- | --- | --- | --- |
| B1 | **A piece with no price is sold at $50** | `CartController.php:31`; `WishlistController.php:104,190,223,239,309`; `product_details.blade.php:81-85,121-134` | The page says "Price on request", but Add to bag is enabled and the bag stores `50.00`. The saved-pieces dropdown shows "$50.00". The no-price state has no state of its own. |
| B2 | **The fit filter runs after pagination** | `FurnishController.php:159-173`; `products.blade.php:24-27,126-128,156-174` | Page 1 can say "Nothing matches" while later pages hold fitting pieces, and the pager sits in the non-empty branch, so there is no way forward. When some do fit, it still says "Showing 1–12 of 40" over 3 cards. The comment calls this deliberate; the result is a lie about the catalogue. |
| B3 | **A failed save leaves the bookmark wrong** | `resources/js/furnish/wishlist.js:221-224,246-253` | `aria-pressed` flips before the request. On failure a toast shows but nothing reverts, so the icon says "saved" when it isn't. The comment at :219 claims `refresh()` corrects this; it only runs on success. |
| B4 | **A failed review loses what was typed** | `product_details.blade.php:239-251`; `ReviewController.php:12-15` | There is no `old()` on the comment or the rating. The error appears only in the banner at the top, and the form far below comes back empty. There is also no submit guard, so a double click creates two pending reviews. |
| B5 | **Add to bag can submit twice and adds no stock check** | `product_details.blade.php:121`; `CartController.php:42-44` | On a slow connection a second click doubles the quantity. Stock is only checked at checkout, so the shopper learns a piece is sold out at the last step. |
| B6 | **The contact form shows developer text, in English** | `ContactController.php:26,29` | "…Check your Mailtrap configuration." in the red banner. The message *was* saved, so for the shopper this is a success. |
| B7 | **Admin delete dialogs hide the cascade** | `components/admin/row-actions.blade.php:37-38`; `lang/en/admin.php:205`; `admin/reviews/index.blade.php:93` | Deleting a category cascades to its products, their variants, images, reviews and bag lines; deleting a user takes their orders. The dialog only says "This cannot be undone." For reviews it asks to delete "Reviews". |
| B8 | **A guest's saved pieces are lost when sync fails** | `wishlist.js:322-329` | localStorage is cleared on *any* HTTP response, including 419 and 500. |

### Medium

| # | Finding | Where |
| --- | --- | --- |
| B9 | The saved-pieces dropdown says "Loading" forever on any failure or an expired session. | `layouts/furnish.blade.php:133`; `wishlist.js:197,207-210` |
| B10 | Wishlist toasts prefer the server's raw English ("Unauthenticated", "CSRF token mismatch."), even in Arabic. | `wishlist.js:249,273` |
| B11 | Move to bag has no pending state; a double click adds 2, and sold-out pieces move without complaint. | `wishlist.js:256-283`; `WishlistController.php:212-260` |
| B12 | The bag doesn't show stock. Cart quantity has no `max` on the server or the client; a huge number can overflow the column and return a 500. | `CartController.php:63`; `cart.blade.php:76`; `CheckoutController.php:171-177` |
| B13 | The 419 page says "go back and send it again". Going back restores the same expired token, so it fails again. | `lang/en/diwan.php:408`; `bootstrap/app.php:39-41` |
| B14 | All room forms share field names in the default error bag, so one room's error shows under every room's form and the new-room form. | `furnish/rooms.blade.php:45,72-77,154-159`; `RoomBoardController.php:116` |
| B15 | A newsletter error appears under the Contact or Profile email field instead of its own; the footer field has no `old()`. | `layouts/furnish.blade.php:312-324` |
| B16 | Admin image delete: no confirm (unlike every other delete), no busy state, failure only in the console. The "Primary" chip doesn't move until reload. | `resources/js/furnish/admin.js:73-95`; `admin/products/edit.blade.php:41-45` |
| B17 | The thank-you page prints the raw status `pending` in every language. | `furnish/thankyou.blade.php:193` |
| B18 | A search result without a price shows "$null". The "browse all" link appears at exactly 5 results, but the endpoint returns up to 6. | `search.js:21,61,82`; `FurnishController.php:297,305` |
| B19 | Search has no abort. A slow response for "so" can overwrite the results for "sofa", or refill the dialog after it closed. | `search.js:135-145` |
| B20 | **Long text has no wrap protection anywhere** except the admin stat tile. One long word or URL overflows review comments, the home quote, admin contact messages, the account dropdown name, room names and the admin rail name. | `product_details.blade.php:221`; `testimonials.blade.php:21`; `index.blade.php` (quote); `admin/contacts/index.blade.php:137-148`; `layouts/furnish.blade.php:168`; `layouts/app.blade.php:103`; `rooms.blade.php:48` |

### Low

| # | Finding | Where |
| --- | --- | --- |
| B21 | A colour swatch is the free-text colour ("Walnut"), which is not a CSS colour, so it renders as an empty circle. Its name only appears in `title`, which touch screens never show. | `products.blade.php:99` |
| B22 | Category pills have a fixed `h-11`; a long name wraps and spills out. | `category.blade.php:245` |
| B23 | The catalogue's empty state doesn't name its cause (query, fit, colour, past the last page). Admin lists test `count()` rather than `total()`, so an out-of-range page says "No X yet". | `products.blade.php:169-173`; `admin/*/index` |
| B24 | The room dialog closes on a validation error; the `old()` values are hidden inside it. | `components/room-dialog.blade.php:234`; `RoomController.php:159` |
| B25 | Admin save buttons are disabled but look unchanged (no `disabled:` styles). A file-validation error clears the chosen files and only reaches the banner. | `admin/products/_form.blade.php:59-64`; `create.blade.php:68`; `orders/edit.blade.php:88` |
| B26 | Admin order with no address: the aside just disappears. | `admin/orders/show.blade.php:42-56` |
| B27 | The new-product form with zero categories cannot be submitted, and nothing says why. | `admin/products/_form.blade.php:24-28` |
| B28 | A review by a deleted user renders a stray " · date". | `product_details.blade.php:223`; `index.blade.php` (figcaption) |

**Already sound:**
- **Empty states.** The bag, orders, rooms, lookbook, testimonials, category, the dropdown and search all have one.
- **Missing dimensions.** These render "not recorded" everywhere.
- **Checkout.** A double-submit guard, field errors with `old()`, stock locked under a row lock, and mail failure isolated from the order.
- **Admin tables.** Wrapped in `overflow-x-auto`.

---

## C · Accessibility

| # | Finding | Where | WCAG |
| --- | --- | --- | --- |
| C1 | **Controls submit the page on `change`.** Arrow keys on a quantity or closed select, or Space on a filter checkbox, reload the page, and focus goes back to the top. | `resources/js/furnish/interactions.js:55-61`; `products.blade.php:55,73,86,101,139`; `cart.blade.php:76`; `components/room-picker.blade.php:17` | 3.2.2 (A) |
| C2 | **The retracting header ignores focus.** Shift+Tab back into the hidden header lands on off-screen controls, and the returning bar covers the focused element. There is no `scroll-padding-top`. | `resources/js/components/site-header.js:53-61`; `layouts/furnish.blade.php:87-90` | 2.4.11, 2.4.7 |
| C3 | **Text over photographs fails contrast** (sampled from the real JPEGs): the hero lede (median 4.22 on a phone, worst 2.06); the hero h1 around the lamp (worst 2.8); the "Search" label over the hero (worst 2.5); category-tile counts (median 1.88–2.48). | `index.blade.php:75,85,133,138`; `layouts/furnish.blade.php:107` | 1.4.3 |
| C4 | Search results are not announced: no live region and no count. | `layouts/furnish.blade.php:533,536`; `search.js:171-194` | 4.1.3 |
| C5 | The bag's `aria-label` goes stale ("Bag, empty" after Move to bag). The saved button's fixed label hides its count. The toast live region is created in the same tick as the first message, so that message is missed. | `layouts/furnish.blade.php:121,152`; `wishlist.js:286-293`; `components/toast.js:20-31` | 4.1.2, 4.1.3 |
| C6 | The saved-items dropdown repaints and destroys the focused button, so focus drops to `<body>`. Its thumbnail links have no name. | `wishlist.js:91-100,169` | 2.4.3, 2.4.4 |
| C7 | No visible focus on the search input, the 404 search or the newsletter field: each has `outline-none`, and the underline doesn't change (newsletter: 1.07:1 between states). | `layouts/furnish.blade.php:318,532`; `components/error-page.blade.php:24` | 2.4.7 |
| C8 | The focus ring is 2.92:1 on the deep band, under 3:1. Light buttons on deep and over the hero use it. | `components/ui/button.blade.php:23`; `index.blade.php:91,196` | 1.4.11 |
| C9 | Placeholder text is 3.31:1: preflight sets it to 50 % of `currentColor`. | search, room dialog, rooms, 404, every `x-ui.field` with a placeholder | 1.4.3 |
| C10 | The W/D/H labels on every card are 3.30:1 (3.01 on the fit box). All three `<abbr>` share one `title`, so the letters are never expanded. | `components/scale-rule.blade.php:18-21` | 1.4.3, 1.3.1 |
| C11 | The mega-menu panel sits *after* all five nav links in the DOM, so Tab from "Browse" skips it. It stays open when focus leaves. | `layouts/furnish.blade.php:195,212` | 2.4.3 |
| C12 | The selected gallery thumbnail looks the same focused and unfocused. | `product_details.blade.php:56` | 2.4.7 |
| C13 | **Heading order:** product page h2 before h1 (`product_details:66,78`); category h1 → card h3 (`category:23`, `product-card:49`); catalogue cards sit under the "Colour" h2 with no results heading (`products:93,160`); admin rail h2s before h1. | as listed | 1.3.1 |
| C14 | Repeated controls share a name: every cart row says "Quantity" and "Remove"; admin reviews say "Delete: Reviews"; rooms say "Edit". | `cart.blade.php:75,86`; `admin/reviews/index.blade.php:41,47,57`; `rooms.blade.php:60-63` | 2.4.6 |
| C15 | Room dialog, newsletter and review form errors are not tied to their fields (no `aria-invalid`, no `describedby`). | `room-dialog.blade.php:45-58`; `layouts/furnish.blade.php:316`; `product_details.blade.php:239,248` | 3.3.1 |
| C16 | The language link's `aria-label="Language: Arabic"` replaces the visible "العربية", so speech-input users can't say what they see. | `layouts/furnish.blade.php:76-80,448-450` | 2.5.3 |
| C17 | **Reduced motion:** the header slide, hover transitions and the toast translate are not gated. `animate-cue` loops forever. | `layouts/furnish.blade.php:90,300`; `index.blade.php:103,134,137`; `toast.js:36` | 2.3.3, 2.2.2 |
| C18 | Filter groups use an h2 instead of `fieldset`/`legend`. | `products.blade.php:70-107` | 1.3.1 |
| C19 | "Back to top" links to `#top`, which doesn't exist. | `layouts/furnish.blade.php:453` | 2.4.3 |
| C20 | Minor: pager `aria-label` sits on role-less spans; lookbook alt repeats the h2; the mobile drawer has no `aria-current`; the fit-demo live region leaves out the reason line. | `vendor/pagination/diwan.blade.php:18,46`; `lookbook.blade.php:21`; `layouts/furnish.blade.php:482`; `index.blade.php:232,241` | — |

**Already sound:**
- **Page language.** `lang` and `dir` are on every layout; there are skip links on all three layouts.
- **Dialogs.** Every one is a native `<dialog>` opened with `showModal()`, so the focus trap, inert background and Escape come free. Focus returns to the opener.
- **Labels.** Every icon-only button has a name. Every field built with `x-ui.field` has a label, `aria-invalid` and `describedby`.
- **Status.** Never colour alone.
- **Contrast.** Every token pair on a flat surface passes; muted on paper-3 is 5.08.
- **Reduced motion.** Honoured for reveal, parallax and view transitions.

---

## D · Arabic and English

Key parity is clean: admin.php has 175 keys in each language and diwan.php 470,
with matching placeholders. Direction is clean too: no physical left/right
utility without an `rtl:` partner. Arrows and chevrons mirror server-side
(`components/icon.blade.php:33-41`), and non-directional icons correctly don't.

### Highest impact first

| # | Finding | Where |
| --- | --- | --- |
| D1 | **Validation, login, throttle and password messages are English in Arabic.** `lang/ar/` holds only admin, diwan and furnish. The checkout shows "The الهاتف field is required." | `lang/ar/`; `config/app.php:83`; `LoginRequest.php:49,71`; `CheckoutController.php:45-50` |
| D2 | **The password-reset and email-verification mails are Laravel's stock English** and LTR. | `app/Models/User.php` (no notification override) |
| D3 | **The success banner prints a raw slug:** `profile-updated`, `password-updated`. | `ProfileController.php:58`; `PasswordController.php:27`; `layouts/furnish.blade.php:271-275`; `layouts/app.blade.php:125` |
| D4 | **Order-status emails always go out in English.** Nothing records the language the order was placed in. | `Admin/OrderController.php:75-80,106` |
| D5 | **The catalogue content is English-only.** Product and category names, descriptions and colours are single columns. The Arabic product page reads "Cozy Stool", "LIVING ROOM" and "White", plus a **Lorem-ipsum description** (seen in the 390 px screenshot). This is a content decision (see Questions). | `ProductFactory.php:16-24` |
| D6 | The 500/503 pages are English-only and hard-coded `dir="ltr"`. | `errors/minimal.blade.php:20,25,96`; `errors/500.blade.php`, `errors/503.blade.php` |
| D7 | **Mixed digits.** The site's rule is Western figures, but 8 Arabic strings hard-code Arabic-Indic digits. One drawing shows "١٧٥ سم" beside "120 × 75", and the testimonials line reads "4 من ٥". | `lang/ar/diwan.php:77,91,92,110,111,306,357,567` |
| D8 | Arabic text set in `font-mono` falls back to an unstyled system font. Geist Mono has no Arabic. Affects piece counts, "سم" and share figures. | `app.css:74,153-160` |
| D9 | The area unit is hard-coded as `m&sup2;`, although the Arabic strings say "م²". | `room-dialog.blade.php:67`; `index.blade.php:298`; `product_details.blade.php:176`; `rooms.blade.php:53`; `profile/edit.blade.php:71` |
| D10 | The footer ruler pill renders as "175 إنسان · سم": its Arabic label starts with a number inside a `dir="ltr"` block. | `layouts/furnish.blade.php:411-416` |
| D11 | "سم" sits inside `dir="ltr"` dimension spans, so an RTL reader meets the unit before the numbers. | `product_details.blade.php:164-167`; `index.blade.php:224-225,286,294` |
| D12 | "These :count pieces" is not pluralised, giving "These 1 pieces" and "هذه 1 قطع". | `lang/*/diwan.php:478-479`; `rooms.blade.php:95,98` |
| D13 | **Order emails:** a hard-coded `$`, a Latin ", " between city and country, uppercase and tracking on Arabic, and a letter-spaced Arabic wordmark that breaks the joins. Gmail strips `<html dir>`. | `emails/order-placed.blade.php:56,67,79,84`; `components/emails/layout.blade.php:27,50` |
| D14 | Order numbers differ: `#7` in emails, `#000007` on the site and in admin. | `order-placed:29`; `order-status:29`; `furnish/order.blade.php:28` |
| D15 | **Arabic pages cannot be indexed or shared.** The locale lives only in the session, `hreflang` points at a redirect, both languages share one canonical, and `og:locale` is `ar` rather than `ar_AR`. | `partials/seo.blade.php:30,42-45,55` |
| D16 | Review quotes use English curly quotes with no `dir="auto"`. | `index.blade.php:340`; `testimonials.blade.php:21` |
| D17 | JS-built rows hard-code `$`. | `search.js:61`; `wishlist.js:113` |
| D18 | The staff contact email is English and `text-align:left`; Arabic messages render left-aligned. | `emails/contact.blade.php:4-44`; `Mail/ContactMail.php:34` |
| D19 | The admin-gate refusal is English on the storefront. | `app/Http/Middleware/AdminAuth.php:23` |
| D20 | **Minor items:**<br>• Arabic-Indic digits typed into `type=number` fields are silently rejected, so the input needs normalising.<br>• The upload hint is a literal string (`admin/products/_form.blade.php:63`).<br>• The slug has no `dir="ltr"` (`product_details.blade.php:196`).<br>• "مرحباً ،" appears when there is no name. | — |

---

## E · Mobile (390 × 844)

**What passed.** There is no horizontal scroll on any of the 62 renders (31 pages × 2 languages): `scrollWidth` equals the viewport on every one, with nothing escaping it. Every page has exactly one `<h1>`.

**What failed: touch targets under 44 px.**
- **Measured with:** `getBoundingClientRect` on every visible link, button, input, select and summary.
- **What counts:** inline links inside running prose are exempt by WCAG convention. They are left out below, except where they *are* the control.
- **Against the WCAG floor:** everything clears 2.5.8 (24 px), but your bar is 44.

| # | Target | Measured | Where it appears |
| --- | --- | --- | --- |
| E1 | **"Set your room"**, the fit system's own entry point, in the top band | **100 × 16** | every storefront page — `layouts/furnish.blade.php` (banner) |
| E2 | Header icon buttons: Menu, Search, Saved, Bag, Sign in | 40 × 40 | every storefront page |
| E3 | Language link in the band | 33 × 16 | every storefront page |
| E4 | Footer links (17 of them) | ~20 tall | every storefront page |
| E5 | Card "Save" bookmark / card title link | 40 × 40 / ~31 tall | home, catalogue, category, product, rooms |
| E6 | "Set your room" chip on cart lines and the product page | ~24 tall | cart, product |
| E7 | Home fit-demo range sliders | 310 × 24 | home |
| E8 | Pager numbers and Next | 40 × 40 | catalogue, admin products |
| E9 | "Keep me signed in" checkbox / password "Show" | 16 × 16 / 31 × 16 | login, register |
| E10 | Admin order-number links / admin "Menu" | 59 × 18 / 86 × 36 | admin dashboard, orders |
| E11 | Breadcrumb and back links ("Back to the shop", "Catalogue") | 20–25 tall | auth, policy and 404 pages |

**Also seen in the screenshots:**
- **Guest bag and saved pieces.** `/cart` and `/wishlist` send a guest to the login page: both routes sit behind `auth` (`routes/web.php:52-86`). This is a product question; see Questions.
- **Arabic product page.** The specification table and the review prompt read correctly in RTL. The product's own content is English (D5).

---

## F · Performance

| # | Finding | Where |
| --- | --- | --- |
| F1 | **The product page's hero image is the original JPEG** (1600 × 2000, ~750 KB for the sofa), served to phones. The derivatives exist. A plain `<img>` was kept so the gallery can swap `src`; it can swap `srcset` too. | `product_details.blade.php:45`; allowance at `DesignSystemTest.php` `PLAIN_IMG_ALLOWED` |
| F2 | **Search and saved-pieces thumbnails load full originals** for 56 × 72 and 44 × 56 boxes, so a single search can pull ~3–4 MB. `WishlistController` also re-implements image URLs by hand and picks `images->first()` rather than the primary image. | `FurnishController.php:306`; `search.js:39`; `WishlistController.php:93-102,212-221`; `wishlist.js:95` |
| F3 | Every product card is `loading="lazy"`, including the first row, which is the LCP on the catalogue. | `components/product-card.blade.php:27` |
| F4 | **Admin upload derives in-request:** up to 6 widths × AVIF + WebP per photo. A big phone photo can hit the timeout. | `Admin/ProductController.php:76,85,179,186`; `app/Support/ImageDeriver.php:65-66,122-131` |
| F5 | **Manifest and cleanup gaps.** The read-modify-write of the manifest is unlocked, and it lives under `public/`, so a deploy overwrites upload entries. Deleting a product or image orphans its derivatives. | `ImageDeriver.php:122-131`; `ProductController.php:198-200,213-215` |
| F6 | **Home runs four uncached query groups.** The fit-demo query sorts every active product by a correlated `max(width*depth)` subquery on every view. | `FurnishController.php:26-67` (52-59 for the demo) |
| F7 | **Catalogue and category pages repeat the cached nav-category query uncached**, and `availableColors` runs a `DISTINCT` on every request. | `FurnishController.php:177,178,200-203` |
| F8 | **`CACHE_STORE=database` and `SESSION_DRIVER=database`.** The nav "cache hit" is still a SQL query, and every request reads and writes a session row. | `.env.example:30,40` |
| F9 | **One bundle for two audiences.** Admin JS ships to shoppers and storefront JS to admins: one 87 KB bundle. | `vite.config.js` |
| F10 | **Fonts.** 80 files (1.6 MB) built, 46 of them cyrillic/vietnamese/latin-ext subsets and 37 legacy `.woff` copies. Browsers fetch only what `unicode-range` needs, so the cost is build bloat plus 41 `@font-face` rules in the CSS. `geist-sans/600` and `geist-mono/500` look unused. | `resources/css/fonts.css` |
| F11 | **N+1 in sync:** `WishlistController::sync` runs 2 queries per id, with no bound on the array. | `WishlistController.php:259-266` |
| F12 | **Dead weight:**<br>• `resources/js/furnish/custom.js` is imported nowhere.<br>• `lang/*/furnish.php` has no caller.<br>• `autoprefixer` and `postcss.config.js` duplicate `@tailwindcss/vite`.<br>• `laravel/sanctum` is unused but registers a route.<br>• `laravel/sail` has no compose file.<br>• `laravel/breeze` is a leftover scaffold.<br>• `bootstrap-icons` is build-time only and belongs in devDependencies. | — |
| F13 | `View::composer('*')` runs for every component render, every card, icon and picture. Cheap, but it scales with the page. | `AppServiceProvider.php:42` |

**Verified clean.** The main paths are eager-loaded:
- **Pages:** cart, checkout, rooms, orders.
- **Admin:** every index, and the admin order page.
- **Sitemap:** cached for an hour.
- **Card helpers:** `primaryVariant` and `primaryImage` read loaded relations.

---

## G · Uncommitted work

This is not about twenty files. It is the whole Tailwind/Alpine migration from
2026-10-04, phases 0 through 4: 168 tracked files and about 50 untracked ones.

- **Removed:** SCSS, Bootstrap, the `tw:` prefix, PHPFlasher and SweetAlert. Every storefront, auth, error, profile and admin view was rewritten.
- **Guarded:** the test suite passes and pins the result (no SCSS, no Bootstrap, no `hm-`).
- **Verified:** it was checked in a browser in both languages at the time.

**Recommendation: keep it, and commit it in the slices below.** Reverting would
throw away work you directed and approved, and several real bug fixes are in it.

### Proposed commits, independent ones first

Each of the first six stands alone and should pass the suite on its own. I will
verify that by stashing and testing each one before committing.

1. **Make the seeders safe to run twice, and keep demo accounts off production.**
   - `AdminUserSeeder`, `RoleSeeder`, `DatabaseSeeder`.
2. **Answer unknown URLs inside the web group, so a 404 keeps the visitor's language and session.**
   - `routes/web.php` (the `Route::fallback`), `ErrorPageTest`.
3. **Give every kind of piece its own photographs, three to a type.**
   - 30 new photos and 10 deleted.
   - `ProductImageFactory`, `StockImageSeeder`, `StockImageTest`, `CREDITS.md`.
   - The replaced hero, workshop and auth-panel photos.
4. **Stop the lookbook pinning random products onto photographs.**
   - `FurnishController::lookbook`, `lookbook.blade.php`, `LookbookTest`.
5. **Name the fit thresholds, so the home demo reads the same rules as the server.**
   - `Fit.php` (`rules()`). The demo itself lands with the views in commit 7.
6. **Replace PHPFlasher and SweetAlert with the shop's own dialog and toast; translate every flash.**
   - **Backend:** `composer.json`/`.lock`, the 12 controllers' `flash()` → `session()->flash(__())`, the CSP middleware and its test, `bootstrap/app.php`.
   - **Assets:** `public/vendor/flasher`.
   - **Lang:** the `flash` keys.
   - This cannot fully stand alone: the old layouts render Flasher. It may have to merge into commit 7. I'll find out when I test it.
7. **Move the shop onto Tailwind and Alpine, and retire Bootstrap and SCSS.** This is the bulk:
   - `resources/css`, `resources/js/app.js` and `components/`.
   - Every view and the new `ui/` and `admin/` components.
   - The paginator, `vite.config.js` and `package.json`.
   - The guard tests: `DesignSystemTest`, `StylesheetCoverageTest`, `HomePageTest`, `AuthPagesTest`, `NavigationQueriesTest`.
   - The deleted SCSS, `tokens.css`, the Bootstrap pager views and `layouts/auth-panel`.

   The intermediate states of this migration were never built on their own: the
   tree went straight from SCSS to none. Splitting it further into storefront
   and admin would produce commits that don't build. I'd keep it as one commit
   with a long message, as d9589c9 did.
8. **design.md and README**, in the same commit as 7. This depends on your answer to § 0.

`package.json`, `package-lock.json` and others are CRLF in the working copy,
while the repo is `eol=lf`. Git will normalise them on commit; worth knowing
before reading the diff.

---

## Found after the audit

- **H1 · The footer claims every piece is measured, and some aren't.**
  - **The copy:** the tagline "Every piece measured before it is photographed" (`lang/en/diwan.php:15`) and the ruler note "Every piece on this site is measured to the centimetre…" both say so.
  - **The data:** 4 of the 29 seeded variants have no dimensions, and the product page says "not recorded" for them.
  - **Why it matters:** the claim is an invented fact under the honesty rules.
  - **Decision needed:** the copy is yours to rewrite.

## Questions only you can answer

These are product decisions, not code ones. I won't guess any of them.

1. **§ 0:** amend design.md to the shipped system, or revert? And the `hm-` rule: drop it?
2. **The contact address** that customer messages should reach (A1).
3. **A piece with no price** (B1): should it be impossible to buy (my default), or should it say "Ask us" and link to Contact?
4. **"Shipping — Free"** on the bag and checkout (`cart.blade.php:114`, `checkout.blade.php:111`). This is a promise. The policy page says the delivery terms aren't settled. Is delivery actually free? If not, what should that line say?
5. **Guest bag.** Today a guest must sign in before they can add to the bag. Intended, or should guests have a session bag?
6. **Arabic product content** (D5). Do you have Arabic names and descriptions, or should products carry an Arabic name only when one is supplied? Also, the seeded descriptions are Lorem ipsum. Should the demo catalogue get real or empty descriptions? Placeholder text counts as an invented fact under the honesty rules.
7. **Arabic URLs** (D15). Putting the locale in the path (`/ar/…`) is the fix, but it adds URLs. That is a routing and SEO change, so it's yours to approve.
8. **Renames** (A, step 3). Keep the `furnish.*` route names and the `furnish/` view directory (my recommendation), or move them behind shims?

---

*One side effect of this audit.* The 390 px sweep signed in as
`customer@fu.local` on the local database and added a Cozy Stool to that bag
twice. Nothing else was written.
