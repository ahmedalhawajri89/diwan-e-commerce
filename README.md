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
* **Catalogue** — filters by category, price, colour and fit; category pages;
  product pages with a scale drawing beside a 175 cm person.
* **Saved pieces and rooms** — save pieces, file them into named rooms, and see
  what each room adds up to.
* **Bag, checkout (cash on delivery), order history**, reviews that appear only
  once approved, contact form, newsletter.
* **Arabic and English** throughout, with the layout mirrored for Arabic.

### The panel
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
