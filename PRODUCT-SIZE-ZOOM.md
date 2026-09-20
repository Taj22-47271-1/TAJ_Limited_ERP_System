# Product Size & Image Zoom Upgrade

## Added

1. Optional `has_sizes` setting per product.
2. Flexible comma-separated size options such as `XS, S, M, L, XL, XXL`.
3. Customer size pills on the product detail page.
4. Buy It Now and Add to Cart actions.
5. Cursor-position image magnifier on desktop.
6. Click-to-open large product image dialog.
7. Selected size stored on online order items.
8. Selected size stored on POS sale items.
9. Size shown in cart, checkout, customer order tracking, staff order review and sales invoice.
10. POS size-selection dialog for clothing/sized products.
11. Existing non-sized products remain unchanged.

## Database migration

`2026_09_13_120000_add_product_size_support.php`

Adds:

- `products.has_sizes`
- `products.size_options`
- `online_order_items.selected_size`
- `sale_items.selected_size`

Run `php artisan migrate` after replacing the project.
