# Design — Diwan

A locked design system for this app. Every page redesign reads this file before
emitting code. Do not regenerate per page — extend or amend this file when the
system needs to grow.

Recorded on 2026-09-03. **Amended 2026-10-06** to describe the system the
2026-10-04 move to Tailwind actually shipped: the walnut-and-linen palette, a
serif display, and the motion that came with them. The earlier text described
a blue, geometric-sans site that no longer existed, and a guide that describes
something else is worse than none. Where the two disagreed, the shipped system
was kept on the owner's decision, and each change is argued for below.

## Brand

**Diwan · ديوان.** A diwan is the seating hall, the couch itself, and a
collected body of poems — all three at once. The word already travels
(English *divan* means a sofa), so the name needs no gloss abroad, and its
second meaning gives the catalogue a frame: a collection, selected.

Wordmark: `DIWAN` set in the display face, uppercase, widely tracked, always
roman, never accompanied by an icon-in-a-circle. In Arabic: `ديوان` in the
Arabic display face, untracked — Arabic letters join, and spacing them apart
breaks the word.

## Genre

modern-minimal, with a warm, residential register — *walnut, linen and
lamplight*.

**Bans, enforced across every page:** no gradient text · no glassmorphism ·
no bouncy or overshoot easing on UI state · no italic headings, ever ·
no pure black or pure white · no `transition: all` · no four-column link
footer · no invented metrics · no dark mode (see below).

## The differentiator — the fit system

Every other furniture store shows a photograph on a white background and a
dimension in centimetres that means nothing to anyone. A common reason a
sofa comes back is that it did not fit — physically, or against the room.

Diwan answers that in four layers, and the design system exists to carry them:

1. **My room** — a control in the header. The visitor enters width × length in
   centimetres. Stored in the session; no account required. Once signed in, the
   room and the way in are also kept with the account, so the next device has
   them; on sign-in the account's fill an empty session, and a session's fill an
   empty account.
2. **Fit badge** — on every product card and product page: *fits · tight ·
   will not fit*, computed from the variant's real dimensions against the saved
   room. Always a word plus an icon. Never colour alone.
3. **Scale rule** — a hairline dimension rule beneath every product image
   carrying the real W × D × H in tabular figures, and, on the product page, a
   175 cm human reference drawn beside the piece.
4. **Room boards** — the wishlist becomes named rooms. A room view sums the
   footprint of its pieces and states what share of the floor they occupy.
5. **The way in** — whether a piece can reach the room at all. A second dialog
   (`routeModal`, opened from the room dialog) asks for the narrowest door on
   the way, how a piece gets up (ground floor, lift, stairs, as three segmented
   buttons), the lift's door and car or the stairs' width and landing, and a
   balcony door or window if a hoist can reach it. Each measurement sits beside
   a small line drawing of where the tape goes, the dimension in brass. No
   field carries an example number: a blank is "not measured", and the check
   says that step is unchecked. A field that comes back wrong is ringed in red
   and the dialog reopens by itself. Kept like the room: the session, and the account once signed in. The verdicts
   come from `App\Support\Passage`, which errs towards "it will not go". The verdict is a badge built like the fit badge (icon and word, on paper): *gets to your room · gets in, with care · needs a hoist · will not get in · not checked*. The piece's page gives it under the fit, with its reason in the visitor's own numbers, any step left unchecked, and "by the measurements you gave". A card shows it only when there is something to warn about. The bag and the checkout name every piece that may not get in before the order; without a way in, a quiet offer to check one. A piece that does not say how it arrives is never called too big: "it may go in taken apart".

The thresholds live once, in `App\Support\Fit`. The home page's live demo reads
them through `Fit::rules()` rather than keeping a copy.

**Honesty rule with the same force as a colour token:** a variant with no
dimensions recorded renders `— dimensions not recorded`. It never renders a
guess, an estimate, or a plausible-looking number.

## Addresses and language

Every page worth finding has its language in its address: `/ar/…` and `/en/…` (home, catalogue, category, piece, about, lookbook, reviews, the policy pages, contact). Arabic is the shop's own language (`localization.default`): a visitor who has chosen nothing lands on `/ar`. The language in an address wins over any earlier choice and becomes the visitor's choice, so the bag, checkout and account (which keep plain addresses, as do every POST and every script endpoint) follow the page they came from. The old addresses (`/products`, `/product/x`, `/`) answer with a permanent redirect to the same page in the visitor's language, query string kept. Switching language returns to the same page in the other one. Each page names its twin (`hreflang`), its own address as canonical, `x-default` as the Arabic page, and `og:locale` as `ar_SA` / `en_US`; the sitemap lists every page in both.

## Page families

Pages within a family share the family's shape; they vary only in component
archetypes. Never swap a page to another family's shape to "add variety".

- **The home page, top to bottom:** the hero with the room card; four facts
  true of every order; the rooms; where a returning visitor left off; most
  saved; just in; one room with its own pieces; by budget, with real counts;
  how ordering works; the fit demo; the workshop; what buyers said (approved
  only); questions asked before buying. There are no sales banners,
  customer counts or borrowed social feeds: the shop has none to show.
- **Marketing pages** — `index` · `about` · `lookbook` · `testimonials` ·
  `contact`. Blocks divide the screen into a claim and its evidence, and the
  pairing alternates direction down the page. **Home alone** opens on one
  full-bleed photograph with the header lying clear over it — the single
  cinematic moment in the site, chosen deliberately; no other page gets one.
- **Auth pages** — `auth/*`. A split screen: a photograph and the form, a
  minimal header and no footer. Signing in is a task, not a visit.
- **Commerce pages** — `products` · `category` · `cart` · `checkout` ·
  `thankyou`. Uniform grid of the same kind of thing, filter rail on the
  inline-start edge, hairline rules between rows, card-internal links, no
  global CTA competing with the grid.
- **Product page** — `product_details`. The scale diagram is an organising
  element, not an afterthought below the fold. Its order, top to bottom:
  the trail; the photographs (a swipeable track, a viewer to enlarge); the
  buy panel — room, colour, fit, stock, quantity, Add to bag; the details as
  three `<details>` (description, specification, delivery and payment); the
  reviews; related pieces; what this visitor looked at before. On a phone a
  bar keeps Add to bag in reach once the panel has scrolled away.
- **App pages** — all `admin/*` · `profile/*` · `orders` · `rooms`. The page IS
  a list. One short paragraph at the top, no display type above `--text-2xl`,
  no hero. Vertical rows separated by hairlines.

## Theme

`custom` — vibe: **"walnut, linen and lamplight"**. Ivory paper, espresso ink,
bronze for the text accent, one deep walnut band per page.

Every value is declared once, in the `@theme` block of `resources/css/app.css`,
and Tailwind's own palette is switched off (`--color-*: initial`), so a view
cannot reach for a colour that is not part of the look.

```
--color-paper          oklch(96.5% 0.010 80)   ivory — the page
--color-paper-2        oklch(93.5% 0.014 78)   linen — panels, image wells
--color-paper-3        oklch(90.0% 0.016 75)   the darkest light surface
--color-rule           oklch(86.0% 0.012 72)   hairlines
--color-rule-2         oklch(62.0% 0.014 65)   any boundary a user operates
--color-muted          oklch(47.0% 0.016 60)
--color-ink            oklch(21.0% 0.020 55)   espresso
--color-accent         = ink                   the primary button's fill
--color-accent-ink     oklch(47.0% 0.085 65)   bronze — text accent
--color-focus          oklch(52.0% 0.100 65)
--color-deep           oklch(24.0% 0.022 50)   walnut — the band
--color-on-deep        oklch(96.5% 0.010 80)
--color-on-deep-muted  oklch(76.0% 0.016 70)
--color-brass          oklch(78.0% 0.090 78)   accent on the band only
--color-ok / warn / danger  the fit trio and status
```

### Colour — why these values

The superseded palette carried a drafting-ink blue as its one cool signal.
Against wood, linen and lamplit photography it read as a software product's
colour, not a furniture house's, and the owner replaced it. The signal now
lives in **value, not hue**: the primary button is the espresso ink itself, and
the only chromatic accent on paper is bronze, used as text — links, prices,
active filters. Brass is its counterpart on the deep band, where bronze would
not hold contrast.

Green stays out of the accent role for a semantic reason: it is the *fits*
state in the fit trio.

**Measured contrast, every pair the app renders** (oklch → sRGB → WCAG
relative luminance, re-measured 2026-10-06):

| pair | ratio | floor |
| --- | --- | --- |
| ink on paper | 16.05 | 4.5 |
| muted on paper / paper-3 (worst surface) | 6.19 / 5.08 | 4.5 |
| accent-ink on paper / paper-3 | 6.30 / 5.17 | 4.5 |
| paper on accent fill (CTA label) | 16.05 | 4.5 |
| rule-2 as a control border on paper | 3.30 | 3.0 |
| focus ring on paper / paper-3 | 5.10 / 4.19 | 3.0 |
| ok / warn / danger on paper-3 | 4.57 / 4.61 / 5.73 | 4.5 |
| on-deep / on-deep-muted on deep | 14.93 / 7.68 | 4.5 |
| brass on deep | 8.19 | 4.5 |
| focus ring on deep | 2.92 | 3.0 — **never used there** |
| brass focus ring on deep | 8.19 | 3.0 |
| placeholder (muted) on paper | 6.19 | 4.5 |

`focus` fails on the deep band, so nothing there rings in it: `<x-ui.button
variant="light">`, the newsletter and the footer ring in `brass`. The button
component picks one ring colour per variant, so two never compete.
Placeholders are muted ink. The browser's default, the text at half
strength, came to 3.3:1. Text laid over a photograph is measured against
the photograph, not a token, and sits on a deep fall-off rather than the
photograph's luck.

**The deep band.** One per page at most, plus the footer. It is a ground, not
an accent, so the "accent is a highlighter" rule applies to bronze and brass,
not to the band: bronze and brass together occupy **3 % of any viewport at
most**.

### No dark mode — a decision, not an omission

Recorded 2026-09-06, re-affirmed 2026-10-06.

The theme is one vibe: warm paper under lamplight. Its depth comes from paper
steps rather than shadow — `paper` to `paper-2` to `paper-3` — and from one
deep band set against them. Inverted, none of that survives: the band stops
being a band when everything is dark, the paper steps have to be re-solved as
dark surfaces, and bronze at 47 % lightness fails its contrast floor against
them. A dark theme here is not a token swap; it is a second palette to design,
measure and maintain.

It is also worth what it costs only for an app people sit inside. This is a
shop, entered from a search result and left after a purchase.

**If it is ever added:** every colour is defined once, in
`resources/css/app.css`. The work is re-solving those values against a dark
ground and re-running the contrast table above — not hunting for hard-coded
colours, of which there are none. The guard in `tests/Unit/DesignSystemTest`
keeps it that way.

## Typography

- **Display:** Cormorant Garamond, 500–600, style **normal** (roman). A
  high-contrast serif — the register of a furniture house, not a software
  product. Its small x-height is why the display steps run large.
- **Body and labels:** Geist Sans, 400 (500 for emphasis and controls).
- **Money:** Saudi riyals, written once by `App\Support\Money` (`@money(...)` in a template, `money()` in a script): the figure then «ر.س» in Arabic, "SAR" then the figure in English, joined by a non-breaking space, halalas only when there are any. A price is never wrapped in `dir="ltr"`: in Arabic the unit must sit to the figure's left. A guard fails if a template glues a symbol to `number_format()` again. **VAT** (15%, `config/shop.php`) is inside every price, never added on top: the piece's page says "price includes VAT" under the figure, and every total (bag, checkout, order, thank-you, email, panel, slip) is followed by a small muted line "of which VAT (15%)" (`<x-vat-line>`). An order keeps the rate and tax it was placed under; one placed before VAT was counted shows no line.
- **Numerals:** Geist Mono, **tabular figures** — prices, dimensions,
  quantities, order numbers, fit percentages. Nothing else.
- **Arabic:** Markazi Text (display) + IBM Plex Sans Arabic (body and labels).
  Geist Mono keeps the numerals, so figures are identical across locales and
  always Western digits.
- Label tracking `0.14em` uppercase; display tracking `-0.012em`. **Both go to
  0 in Arabic**, and Arabic line-heights open up (`1.25` / `1.4`) because Naskh
  sits taller than a Latin serif at the same size.
- Measure: `60ch`. Page max: `88rem`.

**Two families plus a mono outlier is the ceiling.** The mono carries exactly
one role: numeric data. A brand whose promise is measurement must have its
figures line up vertically.

**Headings are always roman.** No italic headers — not a whole heading, not one
emphasised word inside one. Emphasis is weight, bronze, or a drawn underline.
Italic survives only as body-copy emphasis in running paragraphs.

**Hero headline sizing follows copy length**, not enthusiasm: 50 chars or fewer
gets `--text-display`; 51–90 chars caps at `--text-display-s`; past 90 the
headline gets rewritten shorter.

**Section eyebrows are OFF by default.** When a label is genuinely ordinal it
stacks above the heading in the same column.

## Spacing

Tailwind's 4-point spacing scale, used as utilities. Two values are not
Tailwind namespaces and live in `:root`: `--page-gutter`
(`clamp(1rem, 4vw, 3rem)`) and `--section-gap`
(`clamp(4.5rem, 6vw + 2rem, 8rem)`), the floor between major sections.
Section rhythm is deliberately uneven — sections do not all share the same
padding.

## Shape

Near-square. A shop needs its controls to read as controls, and a furniture
house's surfaces are cut, not moulded:

- `--radius-control: 2px` — buttons, inputs, selects, chips, badges.
- `--radius-surface: 0` — cards, panels, modals, photographs.
- `--radius-pill` — count bubbles and the room chip, nothing else.

**Zero box-shadow on static surfaces.** Depth comes from paper steps and
hairlines. Shadow is allowed only on genuinely floating layers: the mega-menu
panel, dropdowns, and dialogs.

## Motion

Slow and eased — the house moves the way a heavy door closes, not the way an
app bounces.

- Easings — `--ease-out: cubic-bezier(0.16, 1, 0.3, 1)` ·
  `--ease-in: cubic-bezier(0.7, 0, 0.84, 0)` ·
  `--ease-in-out: cubic-bezier(0.65, 0, 0.35, 1)`. Never the browser default
  `ease`. Never bounce or overshoot on UI state.
- **Only `transform`, `translate`, `scale`, `opacity` and `clip-path`
  animate.** Never `width`, `height`, `top`, `left`, `margin`, or `padding`.
  Two exceptions. The fit plan's SVG rectangles (`plan-shape`), whose
  geometry *is* the data being shown. And the admin rail's `width` when the
  operator folds it to icons (amended 2026-10-06): the content beside it has
  to take the space the rail gives up, and no transform can hand a column its
  width. It runs only on that click, for 300 ms, in the admin panel; the
  storefront stays transform-only.

The vocabulary, all declared in `resources/css/app.css`:

| primitive | what it does | where |
| --- | --- | --- |
| `settle` · `rise-line` · `fade-up` | the hero's one entrance on load | home only |
| `parallax-drift` · `scroll-fade` | the hero photograph drifts and its words lift away, tied to scroll | home only |
| `data-reveal` / `data-reveal-group` | a section rises once as it enters, children staggered, capped at 450 ms total delay | storefront sections |
| `data-draw` | a rule draws along its own length — the measure idea in motion | scale rules, the footer tape |
| `unveil` · `unveil-zoom` | a photograph opens from a narrower frame | editorial photographs |
| `@view-transition` | a 350 ms cross-fade between pages | everywhere |
| `rail` | a row of pieces moved by the shopper (swipe, drag, arrows, keys), with a tape whose filled length is the share in view and whose place is how far along | "Just in", "You looked at" |
| `story` | chapters turned by the shopper (tabs, arrows, swipe, keys): the picture cross-fades in 700 ms and settles from `scale(1.06)` to 1 over 1800 ms, the chapter's rule draws in ink along `scale-x`, its words fade up | "How a piece reaches you" |
| `unfold` | an answer opening: uncovered from the top by `clip-path`, fading in and settling 0.5 rem, 600 ms; its plus folds into a minus in an ink disc and a rule draws along the question's foot. One open at a time (`<details name>`); closing is instant | the home page's questions |
| `grow` · `grow-x` | an admin chart's bar or a share's length drawn from its baseline, 900 ms | the admin overview |
| admin rail fold | the rail's width 16 rem ↔ 4.5 rem in 300 ms; labels fade, counts become a dot, a tooltip slides in beside a folded icon | admin panel only |

This is more motion than the 2026-09 system allowed, which was three
primitives and no scroll-triggered entrances. It is kept because it was asked
for and it serves the register; it is bounded by the rules that follow.

**Reduced motion is a hard requirement.** Every primitive above sits under
`prefers-reduced-motion: no-preference`, and `reveal.js` sets `[data-motion]`
only when motion is welcome — so with JavaScript off, or reduced motion asked
for, **nothing is ever hidden**. Scroll-linked motion also requires
`animation-timeline` support; without it the page is simply still.

**Focus rings never animate.** They appear instantly on `:focus-visible` at
3:1 or better.

## Microinteractions stance

- **Add to bag opens the bag drawer**, not a new page: the piece, its colour
  and quantity, the bag's total before delivery, and the way on (checkout,
  the bag, or back to the page). It slides in from the inline end. A
  refusal, sold out or unpriced, is a toast in the visitor's language. The
  form still posts on its own without JavaScript.
- **Silent success.** A cart update the user can see does not get a toast.
  Toasts are for failures, for async effects the user cannot see, and for
  explicit confirmations they will need later.
- **Optimistic update + Undo** over confirmation dialogs. Reserve the modal for
  genuinely irreversible destructive actions.
- Tooltip delay: **800ms on hover, 0ms on focus.**
- Every hover affordance has a focus equivalent and works on coarse pointers.
- Spinners delay-show by 150ms and, once shown, stay 300ms or longer.
- `transition` always names its properties. `transition: all` is banned.

## CTA voice

All buttons come from one component, `<x-ui.button>`, which names five
variants: `primary` · `ghost` · `light` · `quiet` · `danger`.

- **Primary** — espresso fill, ivory label, `--radius-control`, at least 48px
  tall. The label is set in the label style: uppercase and tracked in Latin
  script, untracked in Arabic. Copy is an imperative verb phrase:
  *Add to bag* · *Place order* · *Save changes*. Never *Get started now!*
- **Ghost** — `--color-rule-2` outline, transparent fill. *Save to a room*.
- **Light** — paper fill, for a button laid over a photograph or the deep band.
- **Tertiary** — `<x-ui.link>`: the word, an arrow, an underline. No box.
- **Newsletter / email capture** — inline form-as-CTA: one field, one button,
  never its own bordered card.
- **Never two primary CTAs in one view.**

## Nav and footer

- **Storefront nav — a mega-menu panel.** A deep utility band (my-room ·
  locale) above a bar (menu · search · wordmark · saved · bag · account) and a
  category row whose triggers open a full-width grouped panel over a scrim.
  The panel lists the rooms; pointing at one, or tabbing to it, shows that
  room beside the list: a photograph of one of its own pieces, its line, and
  its two newest pieces with price and fit. Hidden previews are inert. The
  panel opens with a short fall and fade, and the previews cross-fade.
  The **my-room control lives in the nav** because the fit system has to be
  present on every page, not buried in a settings screen. Below `64rem` the
  categories move into a drawer. The bar never leaves: on the way down the
  category row slides up behind it and the wordmark settles smaller, by
  transform alone, so nothing on the page below moves. On the way up, or
  when focus enters, it opens out again.
- **App nav — a side rail and a top bar.** Admin only. The rail holds the
  sections; it folds to icons (labels fade, counts become a dot, a tooltip
  names each icon) and remembers that in a cookie the server reads, so the
  page never draws open and then snaps shut. Below `lg` it is a drawer. The
  top bar holds the fold button, the page's name, the shop, the language and
  the account menu; nothing about the account lives in the rail. Beside the account sits the bell: the unread count on a small badge that swells once when it grows, a panel of the latest notices, and a toast at the inline end below the bar when one arrives (six seconds, its time drawn down along its foot). It polls the feed every 15 seconds and pauses while the tab is hidden; the tab's title carries the count.
- **Footer — a statement, not a sitemap.** The newsletter, a single inline row
  of real utility links, the tape-measure band, and the licence line beneath
  in muted small type.

**Four columns of links are banned project-wide.** Shop · Support · Company ·
Legal plus a social-icon row plus a copyright tail is the most recognised
generated-footer fingerprint, and a shop with a dozen pages does not need a
sitemap at the bottom of each one.

## Per-page allowances

- **Marketing pages** MAY use photography and the drawn scale figure. Home MAY
  use its one full-bleed hero.
- **Commerce pages** MUST NOT use enrichment beyond the fit system's own
  drawings. The products are the content.
- **Product page** MAY use the scale diagram at full size.
- **App pages** MUST NOT use enrichment, MUST NOT use display type above
  `--text-2xl`, and MUST NOT use the mega-menu.

## What pages MUST share

- The wordmark, and its Arabic twin.
- The palette and its roles — bronze and brass together at 3 % of any viewport
  at most.
- The display + body + mono trio and the roles each one carries; tabular figures
  on every number without exception.
- The CTA voice — `<x-ui.button>`, one primary per view, imperative label.
- The rule language — 1px hairlines in `--color-rule`; `--color-rule-2` for any
  boundary a user can interact with.
- The fit system's vocabulary — the same three words, the same three icons, the
  same "not recorded" fallback, on every surface a product appears.

## What pages MAY differ on

- Macrostructure within the page family.
- Which side of a split leads.
- Section rhythm and padding — deliberately uneven is correct.
- Grid density on commerce pages.

## Honesty rules

These are content rules with the same force as the colour tokens.

- **No invented metrics.** No "4.9/5 from 2,000+ customers", no "-25 %", no
  "10-year warranty" unless the number is real and supplied. Where a
  number-shaped hole exists and no real number does, ship the hole.
- **No invented dimensions.** The fit system reads real columns. Missing data
  renders "not recorded" — never an estimate.
- **No invented testimonials.** The `reviews` table has an `approved` status.
  Proof sections read from it. No approved reviews means no section.
  `DemoReviewSeeder` writes reviews so the review surfaces can be designed
  against, and for that reason it refuses to run anywhere but `local` and
  `testing`. A real shop starts with none.
- **The catalogue speaks both languages.** A product and a category carry an
  English and an Arabic name and description; the Arabic site shows the Arabic
  and falls back to the English, never to an empty line. Demo copy describes
  the shape and use of a piece and nothing else: no material, warranty or lead
  time the owner has not supplied.
- **No placeholder names** — never "Jane Doe" / "John Smith".
- **Typographic punctuation** throughout: em dashes, ellipses, curly quotes.
  Never a double hyphen, never three periods.
- **The photograph is of the piece.** Amended 2026-09-06. A shop whose promise
  is measurement cannot show you a picture of something else. Images are keyed
  to the kind of piece — three photographs per kind, dealt out in turn by
  `ProductImageFactory::imageFor()` — and a photograph an admin uploaded is
  never replaced by a stock one.

  Two consequences, both deliberate:
  - A product with no image of its own gets `product-placeholder.jpg` — a flat
    paper field with a hairline cross. **Never another product's photograph.**
    Borrowing one is the picture equivalent of an invented dimension.
  - **No 3D renders.** A large share of stock "minimalist furniture" is CGI, and
    a rendered sofa on a page that promises real measurements is the same lie in
    a different medium. Every photograph was checked; provenance and licence for
    each are in `public/assets/images/CREDITS.md`.

## Implementation boundaries

Preserved without modification, by every redesign:

- Every existing route, controller, model, and migration.
- Every form action, method, field name, and `@csrf` token.
- Cart, wishlist, search, and checkout JavaScript endpoints.

The stack, since 2026-10-04:

- **Tailwind CSS v4** through `@tailwindcss/vite`. No SCSS and no Bootstrap;
  `DesignSystemTest` fails if either comes back.
- **Alpine.js, the CSP build** (`@alpinejs/csp`), because the
  Content-Security-Policy forbids `unsafe-eval`. Components are registered by
  name in `resources/js/app.js` and named in the markup — never written inline
  in `x-data`.
- **Native `<dialog>`** for every modal, opened by `data-open-dialog="id"`.

**The `hm-` prefix is retired.** It named the SCSS design-system layer, and
that layer is gone; a prefix with nothing behind it would be a second
vocabulary for the next page to guess at. A view that uses an `hm-` class
fails `DesignSystemTest`.

## Utilities and components

**A `style` attribute may only carry a value the stylesheet cannot know.** A
`style` attribute cannot be covered by a CSP nonce or hash, so each one would
force `style-src-attr 'unsafe-inline'` on a shop that takes money. Three
survive, each a measurement computed per instance:

| where | what it carries |
| --- | --- |
| `components/scale-figure` (×2) | the drawn height and width of a piece, as a percentage of the human reference |
| `furnish/products` | a colour swatch's actual colour |

Mail templates are exempt: an HTML email client has no other mechanism.

Everything else is a Tailwind utility. **Where a pattern is a thing rather than
an adjustment, it gets a component, not a repeated string of utilities:**

- `components/admin/` — `head`, `stats`, `stat`, `table`, `row-actions`, and for the overview `bars` (a day-by-day SVG chart with its figures in a table for screen readers), `spark` (a sparkline under a figure), `money` (an amount with its cents set small). Every list opens with `toolbar`: a search (a GET form with a button, never submitted on change), status chips that carry their real counts, a sort menu and an export, all links, so each view of a list is an address. A list that acts on several rows adds a box to each and a `bulk-bar`: a dark pill fixed to the foot of the screen that rises (transform) while anything is chosen and says how many; deleting goes through the same confirmation dialog as a single row. A piece's stock is edited where it is listed (`stock-cell`): its total, marked sold out or low, opens a small panel with one field per colour and −/+ steppers, saved without a reload. Every movement (a sale, a cancellation, a hand change, the product form) is a line in the stock log through `App\Support\Stock`, shown on the piece's page with what was left after it. `completeness` draws six short bars (photo, price, dimensions, how it arrives, Arabic, description) and names what is missing. Under the dimensions, the product form asks how the piece arrives: four choices as bordered cards (not said yet, assembled, parts come off, flat in boxes), each with a line on what it means, and only for the last two the size of the largest thing carried. "Not said yet" is the default, never a guess. An order's page is work, not a record: its stages as a track (done in ink, the current one ringed, each with when it was reached, from `order_events`), one button for the next stage beside "mark as paid" and a cancel that asks first, the customer's number as a call button, the address with a copy, the pieces, and its history with staff notes. Beside the address, "the way in": the narrowest door, the lift or stairs and a balcony as the customer gave them when ordering (kept with the order), and each piece's way-in badge against them, so whoever delivers it knows before setting out whether it needs the stairs, a hoist or taking apart; then, once delivered, what the customer said. Cancelling asks why, in a quiet select beside the button (optional), and the reason sits on the history line. The overview's pipeline card ends with the customers' answers counted (got in easily, with difficulty, did not, and cancellations because a piece would not get in), or a line saying none have come yet. Its packing slip is a page of its own, A4, with a box to tick beside each piece and, when it is paid on delivery, the amount to collect set large. The product form's "write a draft" sits above the two description fields as a tinted band with one button, present only when a Claude key is configured; while it works it shows the indeterminate bar, and its draft fills empty fields (tinted until edited) or waits beside filled ones with "replace" and "keep mine". It never saves. The activity log is a timeline grouped by day, like the notifications: each line an initials disc with a dot for what kind of act it was, a sentence in the reader's language, the time and the address, and, when several fields changed, a fold that opens to a before / after table (the old value struck through). A single change reads in the sentence itself, its arrow pointing the way the language reads. «Ask Diwan» is a round espresso button with a brass ring at the storefront's inline-start foot (raised above the add-to-bag bar on a piece's page); its name slides out beside it on hover, and once a visit, a few seconds after it is first seen, a small paper card with a tail rises above it to say what it is for. Nothing floats over the home page's photograph: while the hero is in view the button is tucked away (faded, inert) and the room card carries the way in instead, a quiet line at its foot ("Not sure where to start? Ask Diwan"); the button rises once the visitor scrolls past. Both open a drawer from the same side: suggestions to start, the visitor's words on ink, the answer as plain text with compact piece cards beneath (photograph, name, real price, fit badge), three pulsing dots while it works, and a line saying where the answers come from. Present only when a Claude key is set. Shop settings is one page in three groups (invoices, returns, deliveries), each headed by a chip that says what the storefront does with it right now ("plain receipts", "returns by contact", "agreed by phone"), and each field says what leaving it empty means. Nothing has a default: an empty policy is an undecided one, and the storefront says it will be agreed on contact. Delivery days are pill toggles, the Saudi week from Saturday. Delivery cities are one page: the list (fee, or "on contact"; offered or hidden; how many orders) and a line to add one. At the checkout, with cities set up, the city is a choice that shows its fee, and choosing one rewrites the delivery line, the total, its label ("total" once delivery is in it, "total, before delivery" while it is not) and the VAT inside it at once, from figures the server wrote; an unknown fee is "agreed on contact", never free. A second step, "when it comes", offers a day (only the shop's delivery days, from its notice, two weeks ahead) and morning or evening as segmented buttons; without those settings it says the shop will call to agree a day. Every order then shows its delivery fee and day the same way (`<x-delivery-summary>`): on the customer's order, the thank-you page, the email, the panel and the slip. An order's paper is one A4 page, printable, in the reader's language (`invoices/show`): a simplified tax invoice with ZATCA's QR code in the top corner (black on white, the one place it is allowed, because a scanner needs it) only once the shop has a legal name and VAT number and the sale has happened (delivered or paid); otherwise an "order receipt" that says in one line why it is not a tax invoice yet. The seller's name, number and moment are frozen on the order when first issued. Lines show unit prices with VAT included, then total before VAT, the VAT, and the total. The customer's order page and the panel's both link to it. Charts are drawn by the server as SVG: no chart library, no third-party origin.
- `components/ui/` — `page`, `page-head`, `breadcrumbs`, `button`, `link`,
  `field`, `select`, `check`, `chip`, `note`, `empty`, `stepper`, `rail`.
- **A row that moves sideways is a `rail`, never a bare scrollbar.** Nothing
  in it moves on its own and nothing loops: a carousel on a timer takes the
  row away from the shopper mid-read. Position is drawn as a length on a
  tape, not as dots, because this shop's idea is measurement. With every
  piece in view the controls are not drawn.
- Alpine components in `resources/js/components/` — `gallery` (the photograph
  track and its viewer), `stickyBuy`, `stepper`, `share`, `submitOnce`,
  `story` (chapters, one picture at a time, never on a timer; the drawing
  chapter is the real largest measured piece and is left out when none is),
  alongside the header, dialog, disclosure and fit-demo ones. A control that
  changes a value never submits a form on its own: the shopper presses a
  button.
- `components/admin/` — `head`, `stats`, `stat`, `table`, `row-actions`.
- The fit system — `product-card`, `fit-badge`, `scale-rule`, `scale-figure`,
  `room-dialog`, `room-picker`. The card says only what the piece's rows say:
  its fit, "Last 3" or "Out of stock" when that is true, how many colours it
  comes in, its price and its measurements. It carries **no "New" badge**:
  in a young catalogue every card would wear one, and a mark on everything
  marks nothing. A second photograph appears on hover only when it is of the
  same piece.
- `<x-picture>` for every photograph: AVIF and WebP derivatives at the widths
  the layout uses, with a `sizes` the layout has worked out.

A class assembled from pieces in PHP or JavaScript is invisible to Tailwind's
scan; `StylesheetCoverageTest` renders real pages in both languages and fails
on any class the built stylesheet has no rule for.

## Exports

The canonical source is the `@theme` block in `resources/css/app.css`. It is
the only place a colour, typeface, size, radius or easing is declared; this
document quotes it and does not replace it.
