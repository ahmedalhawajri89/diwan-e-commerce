# Design — Diwan

A locked design system for this app. Every page redesign reads this file before
emitting code. Do not regenerate per page — extend or amend this file when the
system needs to grow.

Produced by `hallmark redesign` (multi-page scope) on 2026-09-03.
Supersedes the Furnish / Atelier system of 2026-08-13.

## Brand

**Diwan · ديوان.** A diwan is the seating hall, the couch itself, and a
collected body of poems — all three at once. The word already travels
(English *divan* means a sofa), so the name needs no gloss abroad, and its
second meaning gives the catalogue a frame: a collection, selected.

Wordmark: `DIWAN` set in Bricolage Grotesque 700, tracked `0.06em`, always
roman, never accompanied by an icon-in-a-circle. In Arabic: `ديوان` in
Rubik 600, untracked.

## Genre

modern-minimal

Switched from `editorial` on 2026-09-03 at the user's request: the brief asked
for the craft of a large-scale retailer, and the editorial voice — hairlines
everywhere, asymmetric prose, zero radius — fights the density a shop needs.
modern-minimal keeps the restraint but allows the controls to read as controls.

**Bans, enforced across every page:** no gradient text · no glassmorphism ·
no bouncy or overshoot easing on UI state · no italic headings, ever ·
no pure black or pure white · no `transition: all` · no four-column link
footer · no invented metrics.

## The differentiator — the fit system

Every other furniture store shows a photograph on a white background and a
dimension in centimetres that means nothing to anyone. The commonest reason a
sofa comes back is that it did not fit — physically, or against the room.

Diwan answers that in four layers, and the design system exists to carry them:

1. **My room** — a control in the header. The visitor enters width × length in
   centimetres. Stored in the session; no account required.
2. **Fit badge** — on every product card and product page: *fits · tight ·
   will not fit*, computed from the variant's real dimensions against the saved
   room. Always a word plus an icon. Never colour alone.
3. **Scale rule** — a hairline dimension rule beneath every product image
   carrying the real W × D × H in tabular figures, and, on the product page, a
   175 cm human reference drawn in CSS beside the piece.
4. **Room boards** — the wishlist becomes named rooms. A room view sums the
   footprint of its pieces and states what share of the floor they occupy.

**Honesty rule with the same force as a colour token:** a variant with no
dimensions recorded renders `— dimensions not recorded`. It never renders a
guess, an estimate, or a plausible-looking number.

## Macrostructure family

Pages within a family share the family's shape; they vary only in component
archetypes. Never swap a page to another family's shape to "add variety".

- **Marketing pages** — `15 · Split Studio`. A diptych: every major block
  divides the screen, and the pairing alternates direction down the page. The
  left side is the claim, the right side is the evidence — which for this brand
  means the piece and its measure. Varies on: ratio (7/5 or 6/6) and which side
  leads.
  → `index` · `about` · `lookbook` · `testimonials` · `contact` · `auth/*`
- **Commerce pages** — `11 · Catalogue`. Uniform grid of the same kind of
  thing, filter rail on the inline-start edge, hairline rules between rows,
  card-internal links, no global CTA competing with the grid. Varies on:
  density (3-up / 4-up) and micro-action.
  → `products` · `cart` · `checkout` · `thankyou`
- **Product page** — `19 · Map / Diagram` allowance. The scale diagram is the
  organising element, not an afterthought below the fold. This is the one page
  where the fit system is the layout rather than a badge on it.
  → `product_details`
- **App pages** — `13 · Index-First`. The page IS a list. One short paragraph
  at the top, no display type, no hero. Vertical rows separated by hairlines.
  → all `admin/*` · `profile/*`

## Theme

`custom` — vibe: **"sunlit plaster, measured, one cool signal"**.

Diversification axes: **paper-band = light · display-style = geometric-sans ·
accent-hue = cool.** Differs from the superseded Atelier system (light /
high-contrast-serif / warm) on two of three axes.

```
--color-paper       oklch(97.5% 0.006 75)   #f9f6f2
--color-paper-2     oklch(95.0% 0.008 75)   #f2eee9
--color-paper-3     oklch(92.0% 0.010 72)   #e9e4dd
--color-rule        oklch(88.0% 0.008 72)   #dbd7d2
--color-rule-2      oklch(65.0% 0.010 70)   #938e89
--color-muted       oklch(46.0% 0.012 68)   #5d5751
--color-neutral     oklch(36.0% 0.014 66)
--color-ink-2       oklch(30.0% 0.016 64)
--color-ink         oklch(19.0% 0.018 62)   #1a120b
--color-accent      oklch(44.0% 0.144 255)  #02519f
--color-accent-ink  oklch(46.0% 0.150 255)  #0256a9
--color-focus       oklch(56.0% 0.150 255)  #2d74ca
--color-ok          oklch(48.0% 0.125 150)
--color-warn        oklch(50.0% 0.115  60)
--color-danger      oklch(46.0% 0.155  25)
```

### Colour — how these values were reached

The starting hypothesis was a cool petrol accent on warm paper, so that the
signal never competes with the warm wood and linen in the product photography.
The hypothesis was tested rather than assumed, by sweeping the hue circle for
values that clear **chroma at or above 0.12** *and* **4.5:1 against the
lightest paper**:

```
165-240 deg  (teal, petrol, cyan)   unreachable in sRGB at any lightness
  0- 30 deg  (crimson, clay)        chroma 0.16 - contrast 7.3-8.6
 45- 60 deg  (copper, amber)        chroma 0.12-0.14 - contrast 4.5-5.4
135-150 deg  (leaf green)           chroma 0.13 - contrast 4.7
     255 deg (true blue)            chroma 0.16 - contrast 4.6+
270-285 deg  (indigo, violet)       reachable - and the AI default. Excluded.
```

The petrol was therefore impossible, not merely unfashionable. The logic
survived by moving to **255 deg — drafting-ink blue**, the colour of
architectural plans and measuring instruments, which ties the signal to the fit
system.

Green was excluded as a brand accent for a semantic reason rather than an
optical one: it collides with the *fits* state in the fit trio.

**Measured contrast, every pair the app actually renders:**

| pair | ratio | floor |
| --- | --- | --- |
| ink on paper | 17.21 | 4.5 |
| muted on paper-3 (worst surface) | 5.63 | 4.5 |
| rule-2 as a control border on paper | 3.01 | 3.0 |
| accent-ink on paper-3 | 5.70 | 4.5 |
| paper on accent fill (CTA label) | 7.32 | 4.5 |
| focus ring on paper-3 | 3.70 | 3.0 |
| ok / warn / danger on paper-3 | 4.86 / 4.90 / 6.09 | 4.5 |

`--color-accent` is a fill (primary CTA, active nav marker). `--color-accent-ink`
is the *text* accent — links, prices, active filters. Accent occupies **3 % of
any viewport at most**. It is a highlighter, never a colour block.

### No dark mode — a decision, not an omission

Recorded 2026-09-06, because a reader could otherwise only tell it was missing,
not whether it was considered.

The theme is one vibe: *sunlit plaster*. The whole palette is a warm ground with
a single cool signal, and the depth comes from paper steps rather than shadow —
`paper` to `paper-2` to `paper-3`, three near-white surfaces separated by two
and a half points of lightness. Inverted, none of that survives: warm plaster
becomes warm grey, the paper steps have to be re-solved from scratch as dark
surfaces, and the drafting-ink accent at 44 % lightness fails its contrast floor
against them and has to move. A dark theme here is not a token swap; it is a
second palette to design, measure and maintain.

It is also worth what it costs only for an app people sit inside. This is a
shop, entered from a search result and left after a purchase.

**If it is ever added:** every colour is defined once, in `tokens.css`, in a
single `:root` block. The work is re-solving those values against a dark ground
and re-running the contrast table in § Colour — not hunting for hard-coded
colours, of which there are none. The guard in `tests/Unit/DesignSystemTest`
keeps it that way.

## Typography

- **Display:** Bricolage Grotesque, weight **700**, style **normal** (roman).
- **Body:** Geist, weight **400** (500 for emphasis and controls).
- **Numerals:** Geist Mono, weight 500, **tabular figures** — prices,
  dimensions, quantities, order numbers, fit percentages. Nothing else.
- **Arabic:** Rubik 600 (display) + IBM Plex Sans Arabic 400 (body). Geist Mono
  keeps the numerals so figures are identical across locales.
- Display tracking `-0.022em`; tight `-0.011em`; label `0.09em` uppercase.
  **All three go to 0 in Arabic** — Arabic script joins and has no uppercase, so
  the tracked small-caps idiom does not transfer.
- Measure: `62ch`. Page max: `84rem` — wider than an editorial page, because
  this is a shop and the grid needs the room.

**Two families plus a mono outlier is the ceiling.** The mono carries exactly
one role: numeric data. A brand whose promise is measurement must have its
figures line up vertically — so every figure is tabular, everywhere.

**Headings are always roman.** No italic headers — not a whole heading, not one
emphasised word inside one. Emphasis is weight, `--color-accent-ink`, or a drawn
underline. Italic survives only as body-copy emphasis in running paragraphs.

**Hero headline sizing follows copy length**, not enthusiasm: 50 chars or fewer
gets `--text-display`; 51-90 chars caps at `--text-display-s`; past 90 the
headline gets rewritten shorter. Aim for 7 words or fewer.

**Section eyebrows are OFF by default.** No `01 · THE TOUR`. The tag-left /
heading-right two-column section head is banned outright. When a label is
genuinely ordinal it stacks above the heading in the same column.

## Spacing

4-point named scale. Values live in `tokens.css`. Pages must use named tokens
(`var(--space-md)`), never raw values — and they reach those tokens through a
class, never through a `style` attribute. See § The helper layer.

`--space-3xs` 2px · `2xs` 4px · `xs` 8px · `sm` 12px · `md` 16px · `lg` 24px ·
`xl` 40px · `2xl` 64px · `3xl` 104px · `4xl` 160px

Section rhythm is deliberately uneven — `--section-gap` (5rem) is the floor
between major sections, but sections do not all share the same padding.

## Shape

Unlike the superseded editorial system, this one is **not** zero-radius. A shop
needs its controls to read as controls. The radius is small and applied
consistently:

- `--radius-control: 4px` — buttons, inputs, selects, chips, badges.
- `--radius-surface: 6px` — cards, panels, modals, dropdowns.
- `0` — rules, dividers, image edges, table cells.
- `--radius-pill` — reserved for count bubbles and nothing else.

**Zero box-shadow on static surfaces.** Depth comes from paper steps
(`paper` to `paper-2` to `paper-3`) and from hairlines. Shadow is allowed only
on genuinely floating layers: the mega-menu panel, dropdowns, and modals.

## Motion

- Easings — `--ease-out: cubic-bezier(0.16, 1, 0.3, 1)` ·
  `--ease-in: cubic-bezier(0.7, 0, 0.84, 0)` ·
  `--ease-in-out: cubic-bezier(0.65, 0, 0.35, 1)`. Never the browser default
  `ease`. Never bounce or overshoot on UI state.
- Durations — `--dur-micro` 120ms · `--dur-short` 220ms · `--dur-long` 420ms.
  Exits run at about 75 % of the enter.
- Only `transform` and `opacity` animate. Never `width`, `height`, `top`,
  `left`, `margin`, or `padding`.
- **Three motion primitives for the whole app**, no more:
  `rise` (one orchestrated entrance on first load, staggered by DOM index via
  `--i`, capped at about 500ms total) · `measure-draw` (the scale rule draws
  along its own length via `scaleX` when a card enters — the only motion tied to
  the brand idea) · `press` (1px translate on `:active`).
- No scroll-triggered fade-up on every section. AOS is not in this project.
- Reduced-motion fallback: all spatial motion collapses to a 150ms-or-shorter
  opacity crossfade under `prefers-reduced-motion: reduce`.

**Focus rings never animate.** They appear instantly on `:focus-visible` at
3:1 or better. Transitioning a focus ring leaves keyboard users without an
indicator at the start of the transition.

## Microinteractions stance

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

- **Primary CTA** — `--radius-control`, `--color-accent` fill, `--color-paper`
  text, `--space-sm` by `--space-lg` padding, body font at 500, sentence case.
  Copy is an imperative verb phrase: *Add to bag* · *Place order* ·
  *Save changes*. Never *Get started now!*
- **Secondary CTA** — outlined chip: `--color-rule-2` border, transparent fill,
  same radius and padding. Used for *Save to a room*, *Compare*.
- **Tertiary** — typographic link: the word, an arrow, a 1px underline that
  thickens on hover. No box.
- **Newsletter / email capture** — inline form-as-CTA: one field, one button,
  never its own bordered card.
- **Never two primary CTAs in one view.**

## Nav and footer

- **Storefront nav — N11 Mega-menu panel.** A two-row bar: utility row
  (wordmark · search · my-room · locale · saved · bag · account) above a
  category row whose triggers open a full-width grouped panel over a scrim.
  The **my-room control lives in the nav** because the fit system has to be
  present on every page, not buried in a settings screen. Collapses below
  60rem into a disclosure; the my-room control moves into the panel.
- **App nav — N3 Side-rail.** Admin only. Explicit per-page allowance.
- **Footer — Ft5 Statement.** One large display sentence closes the page —
  a closing line, not a sitemap. Wordmark, a single inline row of real utility
  links, and the licence line sit beneath in muted small type.

**Ft3 is banned project-wide.** Four columns of links (Shop · Support ·
Company · Legal) plus a social-icon row plus a tiny copyright tail is the most
recognised AI footer fingerprint.

**N1a and N6 are banned project-wide.** N1a (wordmark-left, inline links,
button-right) is the most recognised AI nav; N6 is the superseded system's
masthead and carries the wrong genre.

## Per-page allowances

- **Marketing pages** MAY use the diptych's right side for photography or for a
  drawn scale figure. No other enrichment.
- **Commerce pages** MUST NOT use enrichment beyond the fit system's own
  drawings. The products are the content.
- **Product page** MAY use the scale diagram at full size — it is the page.
- **App pages** MUST NOT use enrichment, MUST NOT use display type above
  `--text-2xl`, and MUST NOT use the mega-menu.

## What pages MUST share

- The wordmark, and its Arabic twin.
- The accent colour and its placement — 3 % of any viewport at most.
- The display + body + mono trio and the roles each one carries; tabular figures
  on every number without exception.
- The CTA voice — 4px radius, accent fill, imperative label.
- The rule language — 1px hairlines in `--color-rule`; `--color-rule-2` for any
  boundary a user can interact with.
- The fit system's vocabulary — the same three words, the same three icons, the
  same "not recorded" fallback, on every surface a product appears.

## What pages MAY differ on

- Macrostructure within the page-type family.
- Diptych ratio and which side leads.
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
- **No placeholder names** — never "Jane Doe" / "John Smith".
- **Typographic punctuation** throughout: em dashes, ellipses, curly quotes.
  Never a double hyphen, never three periods.
- **The photograph is of the piece.** Amended 2026-09-06. A shop whose promise
  is measurement cannot show you a picture of something else, and it was doing
  exactly that: `ProductImageFactory` picked one of eight files at random, so
  "Cozy Stool" carried a photograph of an office chair and one sofa stood in
  for four unrelated products. Images are now keyed to the kind of piece, in
  `ProductImageFactory::BY_TYPE`.

  Two consequences, both deliberate:
  - A product with no image of its own gets `product-placeholder.jpg` — a flat
    paper-3 field with a hairline cross. **Never another product's photograph.**
    Borrowing one is the picture equivalent of an invented dimension.
  - **No 3D renders.** A large share of stock "minimalist furniture" is CGI, and
    a rendered sofa on a page that promises real measurements is the same lie in
    a different medium. Every photograph was checked; provenance and licence for
    each are in `public/assets/images/CREDITS.md`.

## Implementation boundaries

This redesign replaces the **visual and interaction layer**, plus the additive
code the fit system needs. Preserved without modification:

- Every existing route, controller, model, and migration.
- Every form action, method, field name, and `@csrf` token.
- Cart, wishlist, search, and checkout JavaScript behaviour and endpoints.
- Bootstrap 5's grid and utility classes — the token layer sits *above* them.

Added (additive only, nothing removed):

- `product_variants.width_cm / depth_cm / height_cm` — nullable.
- `App\Support\Fit` — the fit computation, pure and unit-testable.
- `RoomController` plus two routes — save and clear the session room.
- Blade components for the product card, the fit badge, and the scale rule.

The `hm-` class prefix is retained. It names the design-system layer, not the
brand; renaming it would force a rewrite of working JavaScript for no gain.

## The helper layer

Amended 2026-09-05.

The redesign shipped correct tokens through the wrong mechanism: **408 inline
`style` attributes**. Every one referenced a token, so no page broke the colour
or spacing contract — but the attribute is the wrong carrier for three reasons,
and the third is the one that matters.

1. The same three declarations were retyped ninety times, so they drifted.
2. A systemic change — the rhythm of a list row, the size of small print —
   meant editing forty files instead of one declaration.
3. **A `style` attribute cannot be covered by a CSP nonce or hash.** Only
   `style-src-attr 'unsafe-inline'` allows it, and a shop that takes money
   should not be shipping that to permit decoration.

`resources/scss/furnish/_helpers.scss` now carries those patterns as classes.
It is imported *after* `_hallmark.scss`, because a helper has to beat the
component class it is layered onto — the specificity the inline style it
replaces used to have.

**The rule: a `style` attribute may only carry a value the stylesheet cannot
know.** Six survive, and each is a coordinate or a measurement computed per
instance:

| where | what it carries |
| --- | --- |
| `components/scale-figure` (×2) | the drawn height and width of a piece, as a percentage of the human reference |
| `furnish/lookbook` | a hotspot's position on a photograph |
| `furnish/products` | a colour swatch's actual colour |
| `emails/contact` (×2) | HTML email has no other mechanism |

Everything else is a class. The stagger index is the instructive case: `--i`
looked like data, but every grid in the app is a `take(3…9)` or a
`paginate(12)`, so its range is small and known — hence `.hm-i-0` … `.hm-i-15`,
and `.hm-rise`'s `var(--i, 0)` fallback means an index past the ladder
degrades to no delay rather than to a broken animation.

**Where a pattern is a thing rather than an adjustment, it gets a component
name, not a stack of helpers.** The Index-First row — the unit every admin and
profile page is built from — is `.hm-listrow`, not `.hm-py-md .hm-ruled-b`.

Naming, so the next page does not invent a second vocabulary:

- `.hm-t-*` type scale · `.hm-mt-* .hm-mb-* .hm-py-* .hm-p-* .hm-gap-*` space ·
  `.hm-mis-*` inline-start space, so it follows the writing direction.
- `.hm-ruled-t/-b/-y` hairlines · `.hm-hold-*` maximum widths, named for the
  job rather than the measurement.
- Where Bootstrap already ships the idea (`d-flex`, `w-100`, `overflow-hidden`),
  use Bootstrap's. A second copy under an `hm-` name is two sources of truth.

## Exports

The canonical source is `tokens.css` at the project root.

### tokens.css

See `tokens.css` — it is the authoritative token file and is loaded as its own
Vite entry ahead of the SCSS bundle, so the token layer always wins.

### Tailwind v4 `@theme`

```css
@theme {
  --color-paper:      oklch(97.5% 0.006 75);
  --color-paper-2:    oklch(95.0% 0.008 75);
  --color-ink:        oklch(19.0% 0.018 62);
  --color-ink-2:      oklch(30.0% 0.016 64);
  --color-muted:      oklch(46.0% 0.012 68);
  --color-rule:       oklch(88.0% 0.008 72);
  --color-accent:     oklch(44.0% 0.144 255);
  --color-accent-ink: oklch(46.0% 0.150 255);

  --font-display: "Bricolage Grotesque", sans-serif;
  --font-body:    "Geist", sans-serif;
  --font-mono:    "Geist Mono", monospace;

  --spacing-md: 1rem;
  --spacing-lg: 1.5rem;
  --spacing-xl: 2.5rem;
  --text-md:    1.0625rem;
  --ease-out:   cubic-bezier(0.16, 1, 0.3, 1);
  --radius:     4px;
}
```

### DTCG `tokens.json`

```json
{
  "color": {
    "paper":      { "$value": "oklch(97.5% 0.006 75)", "$type": "color" },
    "ink":        { "$value": "oklch(19.0% 0.018 62)", "$type": "color" },
    "accent":     { "$value": "oklch(44.0% 0.144 255)", "$type": "color" },
    "accent-ink": { "$value": "oklch(46.0% 0.150 255)", "$type": "color" }
  },
  "font": {
    "display": { "$value": "Bricolage Grotesque", "$type": "fontFamily" },
    "body":    { "$value": "Geist",               "$type": "fontFamily" },
    "mono":    { "$value": "Geist Mono",          "$type": "fontFamily" }
  },
  "space": {
    "md": { "$value": "1rem",   "$type": "dimension" },
    "lg": { "$value": "1.5rem", "$type": "dimension" },
    "xl": { "$value": "2.5rem", "$type": "dimension" }
  }
}
```

### shadcn/ui CSS variables

```css
:root {
  --background:         97.5% 0.006 75;
  --foreground:         19.0% 0.018 62;
  --primary:            44.0% 0.144 255;
  --primary-foreground: 97.5% 0.006 75;
  --muted:              95.0% 0.008 75;
  --muted-foreground:   46.0% 0.012 68;
  --border:             88.0% 0.008 72;
  --input:              65.0% 0.010 70;
  --ring:               56.0% 0.150 255;
  --radius:             4px;
}
```
