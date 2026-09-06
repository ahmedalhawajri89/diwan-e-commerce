# Furnish - Modern E-Commerce Platform

Furnish is a fully-featured, modern E-Commerce web application built with Laravel and styled with custom SCSS. It is designed to provide a premium, dynamic, and user-friendly shopping experience for customers, alongside a powerful admin dashboard for store management.

## 🚀 Key Features

### Storefront (User-facing)
* **Modern UI/UX:** A stunning, premium interface with card-based layouts, smooth micro-animations, and responsive design.
* **Dynamic Product Catalog:** Browse products with a modern hero slider, detailed product pages, and categorized displays.
* **Interactive Cart & Checkout:** Seamless shopping cart experience enhanced with beautiful, interactive notifications using SweetAlert2 and PHP Flasher.
* **Customer Dashboard:** A clean profile management interface where users can view orders, manage addresses, and update account details.
* **Interactive Product Reviews:** Customers can leave star-rated reviews for products, featuring a modern UI with sorting capabilities.
* **Wishlist System:** An interactive wishlist allowing users to save their favorite furniture pieces for later.
* **Contact & Newsletter:** Modern contact forms and newsletter subscription functionality to keep in touch with customers.

### Admin Dashboard (Backend)
* **Comprehensive Management:** Full CRUD (Create, Read, Update, Delete) capabilities for:
  * Products (with multiple images and variants)
  * Categories
  * Customer Orders
  * Product Reviews (approve/reject functionality)
  * User Accounts
  * Contact Messages & Newsletter Subscribers
* **Role-Based Access Control:** Secure admin routes ensuring only authorized personnel can access the dashboard.
* **Modern Admin Interface:** An intuitive, organized backend utilizing data tables, modern forms, and quick-action buttons.

## 🛠️ Technology Stack
* **Backend:** Laravel 11, PHP 8.x, MySQL
* **Frontend:** Blade Templating, Custom SCSS (Sass), Vanilla JavaScript
* **Assets Management:** Vite
* **UI/UX Enhancements:** SweetAlert2, PHP Flasher, Swiper.js (for sliders), Bootstrap Icons

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
8. Run migrations and seeders (to populate admin user and dummy data):
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
