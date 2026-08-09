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
5. Copy the environment file and generate application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
6. Configure your database credentials in the `.env` file.
7. Run migrations and seeders (to populate admin user and dummy data):
   ```bash
   php artisan migrate --seed
   ```
8. Link storage for images:
   ```bash
   php artisan storage:link
   ```
9. Start the development server:
   ```bash
   php artisan serve
   ```

## 📝 License

This project is open-source and available under the [MIT license](https://opensource.org/licenses/MIT).
