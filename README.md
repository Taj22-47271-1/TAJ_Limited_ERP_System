# TAJ Limited ERP — Inventory + POS + Accounting + Online Orders

A Laravel 13 full-stack ERP for **TAJ Limited** using Blade, PostgreSQL, role-based access, POS sales, online customer ordering, stock reservation, accounting and email OTP password reset.


## UI theme — Flatlogic One inspired

The complete UI has been redesigned around the **Flatlogic One** admin-template visual language while keeping the project as Laravel Blade. Staff pages now use a white left sidebar, charcoal top bar, amber/coral business accents, compact analytics cards, clean tables and responsive navigation. Cashier keeps the same design system with an emerald operational accent, while the customer storefront uses the same amber + charcoal palette.

Authentication follows the template-style split layout with the form on the left and a custom geometric security illustration on the right. The original TAJ Limited corporate footer remains below authentication pages. See `FLATLOGIC-ONE-THEME.md` for the design tokens and implementation notes.

## Final role design

| Feature | Admin | Cashier | Customer |
|---|:---:|:---:|:---:|
| Role-specific Dashboard | ✅ | ✅ | ✅ |
| Product Browse | ✅ | ✅ | ✅ shop |
| Product Add / Edit + Image | ✅ | ✅ | ❌ |
| Stock Adjustment / Delete Product | ✅ | ❌ | ❌ |
| In-house POS Sale | ✅ | ✅ | ❌ |
| Online Orders | ✅ manage | ✅ manage | ✅ own |
| Approve Online Order | ✅ | ✅ | ❌ |
| Order Progress | ✅ | ✅ limited | ✅ track |
| Customer Management | ✅ | ✅ limited | Own profile |
| Create / Deactivate Cashier | ✅ | ❌ | ❌ |
| Public Registration | ❌ | ❌ | ✅ |
| Change Own Password | ✅ | ✅ | ✅ |
| Forgot Password Email OTP | ✅ | ✅ | ✅ |
| Accounting | ✅ | ❌ | ❌ |
| Total Profit | ✅ | ❌ | ❌ |
| Reports | ✅ | Own sales only | ❌ |
| Roles & Permissions | ✅ | ❌ | ❌ |
| Company Settings | ✅ | ❌ | ❌ |

### Registration rules

- `/register` **always creates a Customer**.
- A Cashier cannot register publicly.
- Admin creates Cashier/internal staff from **Users & Access** and can activate/deactivate the account.
- Cashier can change their password after login.

## Online customer order workflow

```text
Customer Register/Login
        ↓
Browse Shop
        ↓
Add Product to Cart
        ↓
Checkout
(Name + Email + Phone + Address required)
        ↓
COD OR Online Payment (local demo/sandbox)
        ↓
Pending Approval
        ↓
Admin / Cashier Approves
        ↓
Approved → Processing → Packed → Shipped → Out for Delivery → Delivered
        ↓
Customer sees the full status timeline
```

### Stock logic

When an online order is placed, quantity is **reserved**:

```text
Physical stock = 10
Customer orders = 3
Reserved stock = 3
Available stock = 7
```

When Admin/Cashier approves the order, the reserved quantity is committed:

```text
Physical stock: 10 → 7
Reserved stock: 3 → 0
Available stock: 7
```

If a pending order is cancelled/rejected, the reservation is released. POS sales use **available stock**, so they cannot consume stock already reserved for online customers.

## Product images

Admin and Cashier can upload one main product image:

- JPG / JPEG / PNG / WebP
- Maximum 2 MB
- Stored in `storage/app/public/products`
- Displayed in product management and the customer shop

Run once after setup:

```powershell
php artisan storage:link
```

## Core modules

- Login / Customer Registration / Logout
- Forgot password with 6-digit email OTP
- Change password
- Admin/Cashier/Customer dashboards
- Dynamic roles and permissions for internal staff
- Product image upload, categories, SKU/barcode, pricing
- Physical / reserved / available stock
- Stock ledger and Admin stock adjustment
- Supplier + purchase receiving
- In-house POS and invoices
- Customer online shop, cart and checkout
- COD and local online-payment demo/sandbox
- Online order approval and tracking
- Customers
- Expenses
- Double-entry journals
- Profit & Loss
- Sales CSV export
- Dynamic company settings

## Requirements

- Windows 10/11 with Laragon is supported
- PHP 8.3+
- Composer 2
- PostgreSQL (your pgAdmin/PostgreSQL 18 setup is suitable)
- PHP extensions including `pdo_pgsql` and `pgsql`

No Node/NPM build is required.

---

# Windows + Laragon + PostgreSQL setup

## 1. Enable the PostgreSQL PHP driver

Your active Laragon PHP configuration should contain:

```ini
extension=pdo_pgsql
extension=pgsql
```

For the PHP version used in this project, check the active file with:

```powershell
php --ini
```

Then verify:

```powershell
php -m | findstr pgsql
```

Expected output:

```text
pdo_pgsql
pgsql
```

## 2. Create the PostgreSQL database

In pgAdmin create:

```text
taj_erp
```

Owner can be `postgres`.

## 3. Install Laravel dependencies

Inside the extracted project folder:

```powershell
composer install
copy .env.example .env
php artisan key:generate
```

## 4. Configure `.env`

Use your real PostgreSQL password:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=taj_erp
DB_USERNAME=postgres
DB_PASSWORD=YOUR_POSTGRES_PASSWORD
```

For Gmail OTP:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USER=projectw073@gmail.com
MAIL_PASS=YOUR_GMAIL_APP_PASSWORD
MAIL_SCHEME=smtp
MAIL_FROM_ADDRESS=projectw073@gmail.com
MAIL_FROM_NAME="TAJ Limited"
```

The real Gmail App Password is intentionally **not included** in the project ZIP.

## 5. Create tables and demo data

For a brand-new development database:

```powershell
php artisan config:clear
php artisan migrate:fresh --seed
php artisan storage:link
```

If you already have data you need to keep, use migrations instead of `migrate:fresh`.

## 6. Run

```powershell
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

---

# Demo accounts

### Admin

```text
Email: admin@tajlimited.com
Password: Password123!
```

### Cashier

```text
Email: cashier@tajlimited.com
Password: Password123!
```

### Customer

```text
Email: customer@tajlimited.com
Password: Password123!
```

Change demo passwords before production use.

## Dashboard behavior

### Admin

Admin sees business-wide KPIs including:

- Today's total sales
- In-house sales
- Online sales
- **Total profit (Admin only)**
- Products / stock value
- Customer count
- Cashier count
- Pending online orders
- Low-stock products
- Sales chart

### Cashier

Cashier sees:

- Own sales today
- Own invoice count
- Pending online orders
- Active online orders
- Customer count
- Quick product add/edit
- POS / order approval shortcuts

Cashier does **not** see total profit, accounting, stock adjustment, users, roles or settings.

### Customer

Customer sees:

- Shop
- Cart
- Checkout
- Own orders
- Payment status
- Order progress timeline
- Change password

## Online payment note

The included online payment screen is a **local development demo/sandbox**. It records a successful Card/bKash/Nagad-style payment locally so the entire ERP order workflow can be tested without merchant credentials.

Before production deployment, replace `OnlinePaymentController` with a real payment gateway integration such as SSLCommerz/bKash/Nagad using your merchant credentials and webhook/callback verification.

## Important security notes

- Never commit `.env`.
- Never commit a Gmail App Password or payment credentials.
- Use HTTPS in production.
- Set `APP_DEBUG=false` in production.
- Change seeded passwords.
- Back up PostgreSQL regularly.
- Validate real gateway callbacks server-side before marking payments paid.

## Clothing sizes and product image magnifier

This build adds optional customer-selectable sizes and a zoomable product detail page.

- Admin/Cashier can enable **size selection** from Add/Edit Product and enter values such as `S, M, L, XL`.
- Products without size selection continue to behave exactly as before.
- Sized products require the customer to select a size before Add to Cart / Buy it now.
- The selected size is saved on online order items and in-house POS sale items and is visible on order details and invoices.
- POS asks the cashier to select a size for sized products.
- Product detail images support cursor-position magnification and a full-screen image preview.
- Inventory quantity remains a single shared product stock total; selected sizes are recorded for fulfillment and sales history.

For an existing database run:

```bash
php artisan migrate
php artisan config:clear
php artisan view:clear
```

For a fresh development database you may run:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
```
