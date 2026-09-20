# TAJ Limited ERP Architecture

## Roles

### Admin
Full business access: product/catalog management, stock adjustment, POS, online order approval/progress, purchases, customers, suppliers, accounting, profit reports, users, roles and settings.

### Cashier
Operational access: own POS/in-house sales, product add/edit and images, customers, online-order approval and delivery-stage progress. No stock adjustment, total profit, accounting, user administration or settings.

### Customer
Public-registration role: customer storefront, cart, checkout, COD/online demo payment, own order tracking, own profile and password management.

## Inventory invariant

```text
available_stock = physical stock - reserved_stock
```

- POS can only remove `available_stock`.
- Placing an online order increments `reserved_stock`.
- Approving an online order decrements both physical stock and reserved stock by the ordered quantity.
- Cancelling/rejecting a pending online order decrements reserved stock only.

This prevents an in-house POS transaction from consuming stock already promised to an online customer.

## Online order state machine

```text
pending
  ├─ rejected
  ├─ cancelled (customer)
  └─ approved
       ↓
    processing
       ↓
      packed
       ↓
     shipped
       ↓
 out_for_delivery
       ↓
    delivered
```

Online-payment orders must be paid before approval. The included payment controller is a local demo/sandbox implementation; production gateway callbacks must be verified server-side.

## Accounting integration

In-house sale and approved online order:

- Debit Cash and/or Accounts Receivable
- Credit Sales Revenue
- Debit Cost of Goods Sold
- Credit Inventory

COD online order is initially posted to Accounts Receivable when approved. When delivered, the receivable is collected into Cash.

Purchase:

- Debit Inventory
- Credit Cash and/or Accounts Payable

Expense:

- Debit Operating Expense
- Credit Cash

## Important tables

- users / roles / password_reset_otps
- settings
- categories / products
- customers / suppliers
- purchases / purchase_items
- sales / sale_items
- online_orders / online_order_items / order_status_histories
- stock_movements
- expenses
- accounts / journal_entries / journal_lines

## UI architecture

- `layouts.auth`: split login/registration experience with corporate footer below.
- `layouts.admin`: navy management dashboard.
- `layouts.cashier`: green operational dashboard.
- `layouts.customer`: ecommerce storefront/customer portal.
- `layouts.app`: permission-aware common internal module layout.
