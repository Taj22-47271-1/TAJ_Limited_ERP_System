# Flatlogic One Inspired Theme

This build keeps the TAJ Limited Laravel ERP business logic and applies a cohesive interface inspired by the open-source **Flatlogic One React** admin template.

## Applied across the project

- Admin dashboard and all admin module pages
- Cashier dashboard and cashier-accessible module pages
- Login, registration, forgot-password OTP and reset-password pages
- Customer storefront, product catalog, product details, cart, checkout and order tracking
- Forms, cards, tables, status badges, buttons and responsive navigation

## Design system

- **Charcoal:** `#2F3034` — top navigation and dark surfaces
- **Warm amber:** `#FFB547` — primary brand/action color
- **Coral:** `#FF6B61` — alerts and secondary accents
- **Emerald:** `#35BF93` — cashier/success accent
- **Blue:** `#5B8DEF` — information accent
- **Background:** `#F5F6F8`
- **Surface:** `#FFFFFF`

The Admin area uses amber as the main accent. Cashier uses emerald for role recognition while preserving the same component system. Customer commerce uses amber + charcoal so the store remains connected visually to the ERP.

## Main implementation files

- `public/assets/flatlogic-one.css`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/auth.blade.php`
- `resources/views/layouts/customer.blade.php`
- `resources/views/dashboards/admin.blade.php`
- `resources/views/dashboards/cashier.blade.php`

No Node/React build is required. The Flatlogic visual language has been adapted to the existing Laravel Blade stack so all current routes, controllers, PostgreSQL logic, roles and permissions remain intact.
