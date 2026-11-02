# 🛒 Souq Gaza — Multi-Vendor Marketplace

> **Laravel Graduation Project** — a production-style, Arabic-first (RTL) multi-vendor e-commerce platform built for the Gaza Strip, with independent delivery companies, zone-based delivery pricing, and simulated Jawwal Pay checkout.

<p align="center">
  <img alt="Laravel" src="https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white">
  <img alt="Filament" src="https://img.shields.io/badge/Filament-4.x-FDAE4B">
  <img alt="Livewire" src="https://img.shields.io/badge/Livewire-3.x-4E56A6">
  <img alt="Tailwind" src="https://img.shields.io/badge/Tailwind-4.x-38BDF8?logo=tailwindcss&logoColor=white">
  <img alt="MySQL" src="https://img.shields.io/badge/MySQL-8%20%2F%20MariaDB-4479A1?logo=mysql&logoColor=white">
  <img alt="Tests" src="https://img.shields.io/badge/tests-108%20passing-brightgreen">
</p>

---

## 📖 Overview

**Souq Gaza** is a multi-vendor marketplace where local stores sell products and independent delivery
companies (or freelance drivers) deliver them across three regions of the Gaza Strip
(**North**, **Central**, **South**).

The platform ships with **four Filament panels** (Admin, Vendor, Delivery, Company), a custom
mobile-first storefront, and a delivery-pricing engine based on the distance between the customer's
region and each vendor's region.

**Core idea:** one cart can contain products from multiple stores → the system splits it into
**one order/invoice per vendor**, each with its own delivery fee and status lifecycle.

---

## ✨ Feature Highlights

| Area | What it does |
|---|---|
| **Storefront** | Full-bleed hero (editable by admin), categories, store carousels, deals, per-store picks |
| **Multi-vendor cart** | One cart grouped by store, live quantity updates, per-store totals |
| **Checkout** | Region dropdown + address details, live delivery-fee calculation, phone validation (`05XXXXXXXX`) |
| **Payments** | Simulated **Jawwal Pay** flow (wallet number → OTP code → paid), sequential invoices `INV-000001` |
| **Orders** | Per-vendor invoices, status badges, customer order history, printable invoice |
| **Delivery** | Delivery companies, drivers, membership, assignment, driver/company dashboards |
| **Favorites** | Product favorites **and** store favorites, fully AJAX (no page reload) |
| **Ratings** | 1–5 star store ratings with animated stars, average + count, one rating per customer |
| **Notifications** | Database notifications (bell) for new orders, in all Filament panels |
| **Auth** | Unified login, role-based redirect, Google OAuth (Socialite), account completion/upgrade |
| **Admin** | Full CRUD, approvals, site settings (hero image/texts), delivery rates, analytics widgets |

---

## 👥 Roles & Capabilities

The system uses **5 roles** (`spatie/laravel-permission`):

| Role | Panel / Entry | Can do |
|---|---|---|
| **Admin** | `/admin` | Manage users, stores, categories, products, orders, delivery companies & drivers, site settings; approve vendors/drivers/companies; view statistics; **can also shop** and receives order notifications |
| **Vendor** | `/vendor` | Manage store profile, sections, products, stock, prices; view own orders; update order status |
| **Customer** | `/` + `/account` | Browse, search, favorite products & stores, rate stores, cart, checkout, pay, track orders |
| **Delivery Driver** | `/delivery` | View assigned orders, start delivery, mark as delivered (only active drivers) |
| **Company Manager** | `/company` | Manage company orders, assign them to the company's drivers, view company drivers |

**Rules enforced in code**

- A **company manager cannot deliver** — only drivers execute deliveries.
- **Independent drivers** are allowed, but must be approved by the admin.
- Pending accounts are redirected to `/pending` until approved.
- A vendor **must** have a region (`zone_id`) — otherwise checkout from that store is blocked with a clear message.
- Cart quantity is capped by product **stock**, with an explicit "maximum reached" message.
- Price/quantity/stock are always **recalculated server-side**; AJAX responses are authoritative.

---

## 🚚 Delivery Pricing (Distance Matrix)

Delivery is calculated **per vendor** from the pair *(customer region, vendor region)*:

| Customer ↓ / Vendor → | North | Central | South |
|---|---|---|---|
| **North** | 5 | 10 | 15 |
| **Central** | 10 | 5 | 10 |
| **South** | 15 | 10 | 5 |

- Same region → **5 ₪**
- Adjacent region → **10 ₪**
- Far region → **15 ₪**
- Multiple stores → fees are summed (one order per store)
- Rates live in the `delivery_rates` table (seeded, ready for an admin UI)

---

## 🧰 Tech Stack

### Backend
- **Laravel 12.70** · PHP 8.2+
- **Filament 4** — 4 panels (Admin, Vendor, Delivery, Company)
- **Livewire 3.8** — reactive UI building blocks
- **Spatie Laravel Permission 6** — roles & permissions
- **Laravel Socialite 5** — Google OAuth
- **MySQL / MariaDB** (production) · **SQLite `:memory:`** (tests)

### Frontend
- **Blade** templates + **Tailwind CSS 4**
- **Alpine.js** (bundled with Livewire) for small interactions
- **Vanilla JavaScript + AJAX (fetch)** — cart, favorites, ratings, delivery fees
- **Vite 7** for asset bundling
- **Cairo** font, full **RTL**, mobile-first responsive design

### Notable packages
`filament/filament`, `livewire/livewire`, `spatie/laravel-permission`,
`laravel/socialite`, `laravel/tinker`, `laravel-vite-plugin`, `@tailwindcss/vite`

---

## 🗄️ Database Schema (main tables)

| Table | Purpose |
|---|---|
| `users` | All accounts (name, email, phone, address, avatar, cover, provider/provider_id) |
| `roles`, `permissions`, `model_has_roles` | Spatie RBAC (`admin`, `vendor`, `customer`, `delivery`, `delivery_manager`) |
| `vendor_profiles` | Store data: name, slug, logo, cover, type, zone/area, address, activation |
| `vendor_sections` | Optional grouped sections inside a store |
| `categories`, `products` | Catalog: price, compare price, stock, thumbnail, featured flag, section |
| `carts`, `cart_items` | Persistent cart, one row per product/quantity |
| `orders`, `order_items` | One order per vendor; subtotal, delivery fee, total, status, payment |
| `delivery_zones` | 3 regions + 11 sub-areas (self-referencing parent) |
| `delivery_rates` | Fee per *(customer zone, vendor zone)* pair |
| `delivery_companies` | Company name, logo, zone, manager, activation |
| `delivery_profiles` | Driver/manager profile: type, company, zone, vehicle, activation |
| `favorites` | Product favorites (user × product) |
| `store_favorites` | Store favorites (user × vendor profile) |
| `store_ratings` | 1–5 rating per user per store |
| `notifications` | Database notifications (order alerts) |
| `site_settings` | Hero image, badge, title, accent, subtitle (single row) |
| `sessions`, `cache`, `jobs` | Framework tables |

---

## 📁 Project Structure (short)

```
app/
├── Filament/
│   ├── Resources/            # Admin resources (users, vendors, products, orders…)
│   ├── Delivery/             # Driver panel resources
│   ├── Company/              # Company manager panel resources
│   └── Pages/Dashboard.php   # Admin dashboard
├── Http/Controllers/
│   ├── Auth/                 # Login, Register, Google OAuth
│   ├── CartController.php    # Cart + AJAX endpoints
│   ├── CheckoutController.php# Checkout + live delivery fees
│   ├── PaymentController.php # Jawwal Pay simulation
│   ├── OrderController.php   # Orders & invoices
│   ├── StoreController.php   # Stores & products
│   ├── FavoriteController.php
│   └── StoreEngagementController.php # Store favorites + ratings
├── Models/                   # 20+ Eloquent models
├── Notifications/            # NewOrderNotification (database)
├── Services/                 # CartService, CheckoutService, DeliveryFeeCalculator
└── Support/                  # Money, RoleHome, Slug, DeliveryProfileSync
resources/
├── css/marketplace.css       # Design system extracted from Figma
├── js/                       # app.js, avatar-lightbox.js, product-modal.js
└── views/
    ├── layouts/app.blade.php # Navbar, mobile nav, toasts, modal, lightbox
    ├── home.blade.php        # Hero, categories, stores, deals, picks
    ├── cart/, checkout/, orders/, payment/, stores/, favorites/, profile/, auth/
    └── partials/product-card.blade.php
```

---

## 🚀 Getting Started

### Requirements
- PHP **8.2+** with `pdo_mysql`, `gd`, `intl`, `mbstring`, `openssl`
- Composer 2
- Node.js 20+ & npm
- MySQL 8 / MariaDB 10.4+ (or SQLite for a quick try)

### Installation

```bash
# 1. Clone
git clone https://github.com/ramezgamer17-cloud/gaza-marketplace-graduation-project-2.git
cd gaza-marketplace-graduation-project-2

# 2. Install dependencies
composer install
npm install

# 3. Environment
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env, then:
php artisan migrate --seed

# 5. Storage + assets
php artisan storage:link
npm run build

# 6. Run
php artisan serve
```

Open **http://127.0.0.1:8000**

### Demo accounts (password: `password`)

| Role | Email | Entry |
|---|---|---|
| Admin | `admin@multistore.test` | `/admin` |
| Customer | `customer@demo.test` | `/` |
| Vendor | `sharma@demo.test` | `/vendor` |
| Driver | `delivery@demo.test` | `/delivery` |
| Company manager | `manager@demo.test` | `/company` |

### Google OAuth (optional)

Add to `.env`:

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
```

Register the same redirect URI in **Google Cloud Console → Credentials**.
> 🔐 Never commit real credentials. `.env` is git-ignored and `client_secret_*.json` is blocked.

---

## ✅ Testing

```bash
php artisan test
```

**Current status:** `108 passed (388 assertions)`

Coverage includes: authentication & registration, Google callback handling, profile completion,
storefront browsing, cart (including AJAX JSON responses), checkout & delivery-fee matrix,
invoices, Jawwal Pay flow, favorites (products & stores), store ratings, delivery/company panels,
admin screens, notifications, and mobile layout guards.

> Feature tests run on **SQLite `:memory:`** for speed. Production uses MySQL — always smoke-test
> new queries against MySQL too (column types, FKs, ordering).

---

## 📌 Roadmap / Remaining Work

### 🔴 High priority (complete the order lifecycle)
- [ ] Vendor **"Mark as prepared"** action that notifies the delivery company
- [ ] Driver assignment screen (manual by manager) + **notification to the driver**
- [ ] Customer **order tracking timeline** (paid → preparing → on the way → delivered)
- [ ] Status-change notifications for customer / vendor / driver (database + optional SMS)

### 🟠 Medium priority
- [ ] **Live search** (products & stores) with Livewire + debounce
- [ ] **Chat** between customer and vendor (per order)
- [ ] Vendor **stories** (24h highlights)
- [ ] Admin UI to edit **delivery rates** (currently seeded data)
- [ ] Delivery **discount rules** (same category / same region) — business rule pending

### 🟢 Low priority / polish
- [ ] Redesign the invoice page to match the new Figma-based design
- [ ] Seed realistic product images (currently placeholders for many demo items)
- [ ] MySQL-specific smoke test suite (beyond SQLite tests)
- [ ] Queue worker for notifications, backups, monitoring
- [ ] Deployment guide (shared host / VPS + HTTPS + cron)

---

## 🔐 Security Notes

- `.env` is **not** tracked; secrets never belong in the repository.
- Credential JSON files (`client_secret_*.json`, `service-account*.json`) are **git-ignored**.
- Git history was purged from a previously committed OAuth credential; the leaked secret was
  **rotated** and the OAuth client recreated.
- If you fork this project: use your own Google OAuth client and your own `.env`.

---

## 🧪 Useful Commands

```bash
php artisan migrate:fresh --seed     # rebuild demo data
php artisan optimize:clear           # clear config/route/view caches
php artisan test --filter=PaymentTest
npm run build                        # production assets
npm run dev                          # Vite dev server (HMR)
```

---

## 👨‍🎓 Academic Context

This project was built as a **graduation project** to demonstrate a realistic, full-stack
multi-vendor marketplace: role-based dashboards, transactional checkout, delivery logistics,
AJAX-driven UX, and a mobile-first Arabic interface.

**Team / Author:** Ramez — Gaza, Palestine
**Stack:** Laravel · Filament · Livewire · Tailwind · MySQL

---

## 📄 License

Released under the **MIT License** — free to use for learning and academic purposes.
