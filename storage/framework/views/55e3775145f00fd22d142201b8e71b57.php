<?php $__env->startSection('title','Admin Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<section class="admin-v2-hero">
    <div><span class="eyebrow">BUSINESS OVERVIEW</span><h1>Welcome back, <?php echo e(auth()->user()->name); ?></h1><p>Manage products, in-house sales, online orders, inventory, accounting and staff access from one dashboard.</p></div>
    <div class="admin-v2-actions"><a href="<?php echo e(route('pos')); ?>">New POS Sale</a><a href="<?php echo e(route('orders.index',['status'=>'pending'])); ?>">Review Orders</a><a href="<?php echo e(route('products.create')); ?>">Add Product</a></div>
</section>

<section class="erp-kpi-grid admin-kpis">
    <article class="erp-kpi"><span class="erp-kpi-icon">৳</span><div><small>Today's Sales</small><strong><?php echo e($companySettings->currency_symbol); ?><?php echo e(number_format($salesToday,2)); ?></strong><em>In-house + online</em></div></article>
    <article class="erp-kpi"><span class="erp-kpi-icon">▣</span><div><small>In-house Sales</small><strong><?php echo e($companySettings->currency_symbol); ?><?php echo e(number_format($inhouseSalesToday,2)); ?></strong><em>POS today</em></div></article>
    <article class="erp-kpi"><span class="erp-kpi-icon">◉</span><div><small>Online Sales</small><strong><?php echo e($companySettings->currency_symbol); ?><?php echo e(number_format($onlineSalesToday,2)); ?></strong><em><?php echo e($pendingOrders); ?> pending orders</em></div></article>
    <article class="erp-kpi profit-card"><span class="erp-kpi-icon">↗</span><div><small>Total Profit</small><strong><?php echo e($companySettings->currency_symbol); ?><?php echo e(number_format($totalProfit,2)); ?></strong><em>Admin-only financial KPI</em></div></article>
    <article class="erp-kpi"><span class="erp-kpi-icon">◫</span><div><small>Products</small><strong><?php echo e(number_format($productCount)); ?></strong><em>Stock value <?php echo e($companySettings->currency_symbol); ?><?php echo e(number_format($stockValue,0)); ?></em></div></article>
    <article class="erp-kpi"><span class="erp-kpi-icon">👥</span><div><small>Customers</small><strong><?php echo e(number_format($customerCount)); ?></strong><em>Registered shoppers</em></div></article>
    <article class="erp-kpi"><span class="erp-kpi-icon">♙</span><div><small>Cashiers</small><strong><?php echo e(number_format($cashierCount)); ?></strong><em><?php echo e($activeUserCount); ?> active users</em></div></article>
    <article class="erp-kpi"><span class="erp-kpi-icon">−</span><div><small>Today's Expenses</small><strong><?php echo e($companySettings->currency_symbol); ?><?php echo e(number_format($expensesToday,2)); ?></strong><em>Today's profit <?php echo e($companySettings->currency_symbol); ?><?php echo e(number_format($profitToday,2)); ?></em></div></article>
</section>

<div class="dashboard-two-col">
<section class="card chart-card">
    <div class="card-head"><div><span class="eyebrow dark">LAST 7 DAYS</span><h2>Sales Performance</h2></div><a href="<?php echo e(route('reports.profit-loss')); ?>">Financial Report →</a></div>
    <div class="mini-bar-chart">
        <?php $__currentLoopData = $salesChart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="mini-bar-col"><span class="mini-bar-value"><?php echo e(number_format($point['total']/1000,1)); ?>k</span><div class="mini-bar" style="height:<?php echo e($point['height']); ?>%"></div><small><?php echo e($point['label']); ?></small></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<section class="card quick-panel">
    <div class="card-head"><div><span class="eyebrow dark">QUICK CONTROL</span><h2>Management Actions</h2></div></div>
    <div class="quick-action-grid">
        <a href="<?php echo e(route('products.create')); ?>"><strong>+ Product</strong><span>Create product with image</span></a>
        <a href="<?php echo e(route('inventory.movements')); ?>"><strong>Stock</strong><span>Adjust & review ledger</span></a>
        <a href="<?php echo e(route('orders.index',['status'=>'pending'])); ?>"><strong>Orders</strong><span>Approve customer orders</span></a>
        <a href="<?php echo e(route('admin.users')); ?>"><strong>Cashiers</strong><span>Create / deactivate users</span></a>
        <a href="<?php echo e(route('admin.roles.index')); ?>"><strong>Roles</strong><span>Permissions & access</span></a>
        <a href="<?php echo e(route('expenses.index')); ?>"><strong>Expenses</strong><span>Record business costs</span></a>
    </div>
</section>
</div>

<div class="dashboard-two-col">
<section class="card">
    <div class="card-head"><div><span class="eyebrow dark">ONLINE</span><h2>Recent Orders</h2></div><a href="<?php echo e(route('orders.index')); ?>">All Orders →</a></div>
    <div class="table-wrap"><table><thead><tr><th>Order</th><th>Customer</th><th>Payment</th><th>Status</th><th class="right">Total</th></tr></thead><tbody>
    <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><a href="<?php echo e(route('orders.show',$o)); ?>"><strong><?php echo e($o->order_no); ?></strong></a></td><td><?php echo e($o->full_name); ?></td><td><?php echo e(strtoupper($o->payment_method)); ?><small class="block"><?php echo e(ucfirst(str_replace('_',' ',$o->payment_status))); ?></small></td><td><span class="status-badge <?php echo e($o->order_status); ?>"><?php echo e(ucwords(str_replace('_',' ',$o->order_status))); ?></span></td><td class="right"><strong><?php echo e($companySettings->currency_symbol); ?><?php echo e(number_format($o->total,2)); ?></strong></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" class="empty-cell">No online orders yet.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<section class="card">
    <div class="card-head"><div><span class="eyebrow dark">INVENTORY</span><h2>Low Stock</h2></div><a href="<?php echo e(route('products.index')); ?>">Products →</a></div>
    <div class="stock-watch-list"><?php $__empty_1 = true; $__currentLoopData = $lowStock; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div><span class="product-thumb tiny"><?php if($p->image_url): ?><img src="<?php echo e($p->image_url); ?>" alt=""><?php else: ?><?php echo e(strtoupper(substr($p->name,0,1))); ?><?php endif; ?></span><div><strong><?php echo e($p->name); ?></strong><small><?php echo e($p->sku); ?></small></div><span class="stock-pill danger"><?php echo e(number_format($p->available_stock,0)); ?> available</span></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><p class="muted">No low-stock products.</p><?php endif; ?></div>
</section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\taj-limited-erp\resources\views/dashboards/admin.blade.php ENDPATH**/ ?>