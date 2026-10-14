# Diwan — furniture, measured

Diwan is a bilingual (Arabic / English, right-to-left and left-to-right) furniture
shop built on Laravel. Its one idea is the fit system: a visitor gives the two
measurements of their room once, and every piece in the shop answers whether it
fits — on its card, on its page, in the bag, and before the courier reaches the
door. Nothing on the site is invented: no estimated dimensions, no placeholder
reviews, no figures the database does not hold.

## What it does

### The shop
* **The fit system** — a room set once in the session; a verdict (fits / tight /
  will not fit) with its reason on every piece; the share of the floor a bag
  takes up together; a doorway warning at checkout; a live demo on the home page.
  Underneath it, `App\Support\Passage` works out whether a piece gets into the
  home at all: through the front door, into a lift (car and door) or round the
  turn of the stairs. It errs towards "it will not go": a 2 cm margin a side,
  room rounded down, a piece carried flat round a turn, and a step whose
  measurements are not known left unchecked rather than assumed. When the
  lift or stairs refuse a piece but a balcony clears it, it says so (a hoist).
  A piece is walked in at the size it is carried: the product form records
  whether it arrives assembled, with parts off or in boxes, and its largest
  part. The visitor gives their way in (door, lift or stairs, balcony) in a
  dialog beside the room, kept in the session. Each piece's page, card, the
  bag, checkout and «Ask Diwan» then say whether it gets to the room, and why.
  The way in is kept with the order for whoever delivers it (order page and
  packing slip); after delivery the customer is asked once whether it got in,
  and the overview counts the answers.
* **Catalogue** — filters by category, price, colour and fit; category pages;
  product pages with a scale drawing beside a 175 cm person.
* **Saved pieces and rooms** — save pieces, file them into named rooms, and see
  what each room adds up to.
* **Bag, checkout (cash on delivery), order history**, reviews that appear only
  once approved, contact form, newsletter.
* **Arabic and English** throughout, with the layout mirrored for Arabic.

### The panel
* **Shop settings** — the seller's legal name and VAT number, the return period,
  and the delivery days and notice. All empty until the shop decides; until then
  the storefront says each is agreed on contact.
* Products (with photographs and real dimensions), categories, orders, review
  moderation, users and roles, messages, newsletter subscribers.
* Destructive actions are confirmed in the panel's own dialog.

## Technology

* **Backend:** Laravel 12, PHP 8.2+, MySQL (SQLite in tests), Pest.
* **Frontend:** Blade components, **Tailwind CSS v4** (via `@tailwindcss/vite`),
  **Alpine.js** — the CSP build, `@alpinejs/csp`, because the Content-Security-
  Policy forbids `unsafe-eval`.
* **Assets:** Vite; self-hosted fonts (Cormorant Garamond, Geist, Markazi Text,
  IBM Plex Sans Arabic); icons as one SVG sprite built from `bootstrap-icons`
  by `php artisan icons:build`; photographs served as AVIF/WebP by
  `php artisan images:derive`.
* **Money:** Saudi riyals throughout, written by one formatter
  (`App\Support\Money`, `@money(...)` in Blade, `money()` in scripts).
  Prices include Saudi VAT (15%, `SHOP_VAT_RATE`); totals show the tax inside
  them, and each order keeps the rate and tax it was placed under.
* **Invoices:** a simplified tax invoice with ZATCA's phase-one QR code (TLV,
  Base64, drawn by `chillerlan/php-qrcode`) once the shop has a VAT number and
  the order is delivered or paid; a plain receipt saying why until then. Phase
  two (reporting to Fatoora) needs a registered business and is not done.
* **Security:** a strict CSP — scripts and styles from this origin only, inline
  scripts signed with a per-request nonce, nothing from a third-party origin.

The design guide — colours, type, components, motion, and the honesty rules —
is [design.md](design.md).

## ⚙️ Installation & Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/ahmedalhawajri89/furnish.git
   ```
2. Navigate to the project directory:
   ```bash
   cd furnish
   ```
3. Install PHP dependencies:
   ```bash
   composer install
   ```
4. Install NPM dependencies and build assets:
   ```bash
   npm install && npm run build
   ```
5. Build the responsive image derivatives (AVIF and WebP at the widths the
   layouts use). Requires GD with WebP support; AVIF is used when available:
   ```bash
   php artisan images:derive
   ```
   Pages render without this — `<x-picture>` falls back to the original file —
   but every photograph is then served at full size to every device. Run
   `php artisan images:derive --check` in CI to catch a deploy that skipped it.
6. Copy the environment file and generate application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
7. Configure your database credentials in the `.env` file.
8. Run migrations and seeders. On a local machine this builds a demonstration
   shop: two demo accounts, thirty pieces in six rooms with Arabic and English
   copy, and a set of demo reviews. Elsewhere the demo accounts and reviews
   are skipped:
   ```bash
   php artisan migrate --seed
   ```
9. Link storage for images:
   ```bash
   php artisan storage:link
   ```
10. Start the development server:
   ```bash
   php artisan serve
   ```
11. **Start a queue worker.** Transactional email is queued, and
    `QUEUE_CONNECTION=database` means a message sits in the `jobs` table until
    a worker takes it:
   ```bash
   php artisan queue:work
   ```
   Without this the shop still takes orders and reports success — it simply
   never sends the receipt, the shipping notice or the cancellation, and
   nothing anywhere raises an error. In production run it under a process
   supervisor (systemd, supervisord, or `php artisan queue:listen` in
   development) so it restarts after a deploy.

   The same worker encodes an admin's uploaded photographs (`DeriveImage`).
   Without it a new photograph is still shown, as its original file, until
   the next `php artisan images:derive`. `composer dev` starts the server,
   a worker, the log tail and Vite together. The log tail (Pail) needs the
   `pcntl` extension, which Windows does not have: there it prints a note and
   steps aside while the rest keeps running, and the log is
   `storage/logs/laravel.log`.

## ⚡ Speed

Measured on this codebase, so the order is the order of the gain:

1. **Turn on OPcache.** Without it PHP re-reads and re-parses every compiled
   Blade view each time it renders, and a page renders 140 to 200 of them.
   With it the catalogue went from about 500 ms to 150 ms, and a single icon
   from 1.15 ms to 0.28 ms. In `php.ini`: `zend_extension=opcache`,
   `opcache.enable=1`, and in production `opcache.validate_timestamps=0`
   (then reload PHP after each deploy).
2. **Cache what Laravel can:** `php artisan optimize` (config, routes, events,
   views) on every deploy.
3. **Compress.** Pages are gzipped by `CompressResponse` when nothing in front
   does it (the home page goes from 326 KB to 33 KB). Behind nginx, prefer
   `gzip on;` or brotli there: it also covers `public/build`, which should be
   served with `Cache-Control: public, max-age=31536000, immutable` because
   every file name carries its hash.
4. **Run the queue worker** (above), so a photograph upload answers at once.

The app does the rest itself: the home page's shelves are cached until the
catalogue changes, a link is fetched while the cursor rests on it
(`partials/speculation`), and the product form shrinks a phone's photograph
in the browser before sending it.

## ✨ Description drafts (Claude)

The product form can write a first draft of both descriptions, English and
Arabic, from what the shop has recorded about the piece (its name, category,
colours, dimensions when all three are on file) and, once saved, its
photograph. Price and stock are never sent. The instructions forbid stating
anything the facts do not give: no invented material, size or warranty.

A draft is never saved by itself. Empty fields are filled and marked until
edited; fields that already hold text get the draft beside them, replaced
only on request.

To turn it on, set a key in `.env` and clear the config cache:

```bash
ANTHROPIC_API_KEY=sk-ant-...
ANTHROPIC_MODEL=claude-opus-5-5   # the default
php artisan config:clear          # or `php artisan optimize` again in production
```

Without a key the button is simply absent. Each draft is one request to
Claude Opus 5.5 (about $0.02 at current prices; the fixed instructions are
cached), throttled to 10 a minute per operator, with token usage written to
the log. Set a monthly spend limit on the key in the Anthropic console.

## 💬 «Ask Diwan» (Claude)

A button at the foot of every storefront page (on the home page, a line in
the room card while the hero is in view) opens a short conversation.
The visitor says what they want (the room and its size, a colour, a
budget) and Claude answers using two read-only tools over the live
catalogue: `search_catalogue` (words, room, colour, price, and the fit
verdict on the visitor's room) and `get_piece`. Its last answer is held to
a schema: a short reply and the ids of up to four pieces. Only ids the tools
returned in that turn are kept, and the pieces are drawn by the server from
the database, so every price, picture and fit a visitor sees is a row's,
never the model's words. The instructions forbid stating anything the
tools did not return.

It shares the key with the description drafts and is absent without one.
Each question is a few requests to Claude Opus 5.5 at low effort (roughly
$0.02 to $0.05), bounded by a throttle of 8 a minute, 30 questions per
visitor a day and 300 for the whole shop a day:

```bash
ASSISTANT_PER_VISITOR=30
ASSISTANT_PER_DAY=300
```

The conversation lives in the visitor's session, the last few turns only.

## 🗂 Activity log

Everything an administrator does in the panel is written to the activity
log (Admin → Activity): each piece, colour, photograph, category, order,
review, account, message or subscriber added, changed or deleted, with
every changed field before and after, and every sign-in (and failed attempt
on an administrator's address) with its IP. Passwords and tokens are never
written. A customer's own actions are not in it; the notifications and each
order's history hold those.

Lines older than a year are deleted by `model:prune`, scheduled daily. The
scheduler needs one cron entry on the server:

```
* * * * * cd /path/to/diwan && php artisan schedule:run >> /dev/null 2>&1
```

## 📬 Email

Four messages leave the application:

| Message | Trigger | To |
| --- | --- | --- |
| Order receipt | Checkout completes | Customer, in the language they shopped in |
| Shipped / Delivered / Cancelled | An operator advances the order | Customer |
| Contact form | Somebody uses the contact page | The address in `ContactController` |

Only `shipped`, `delivered` and `cancelled` send. `processing` is an internal
step that tells a customer nothing they can act on, and a shop that emails on
every internal move trains people to ignore its email.

Nothing promises a delivery date, because nothing in the application knows one.

## 📦 Stock

`product_variants.stock` is decremented inside the checkout transaction, under
a row lock, so the last piece cannot be sold twice. Cancelling an order returns
its quantities to the shelf.

The count is real, and both the product page and the `schema.org` structured
data read it — which is why it has to be. Before this it was set by hand and
never decremented, so a variant advertised itself as in stock indefinitely, to
shoppers and to search engines alike.

## 📝 License

This project is open-source and available under the [MIT license](https://opensource.org/licenses/MIT).
