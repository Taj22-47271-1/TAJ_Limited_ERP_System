<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> | <?php echo e($companySettings->company_name ?? 'TAJ Limited'); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('assets/app.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/flatlogic-one.css')); ?>">
    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<?php
    $user = auth()->user();
    $roleName = $user->roleDefinition?->name ?? ucwords(str_replace('_',' ',$user->role));
    $isCashier = $user->role === 'cashier';
?>
<body class="fl-body <?php echo e($isCashier ? 'fl-cashier-theme' : 'fl-admin-theme'); ?>">
<div class="fl-app-shell">
    <aside class="fl-sidebar drawer" id="drawer">
        <div class="fl-sidebar-brand">
            <a href="<?php echo e(route('dashboard')); ?>" class="fl-brand-link">
                <span class="fl-logo-mark">T</span>
                <span class="fl-brand-text"><strong><?php echo e(strtoupper($companySettings->company_name ?? 'TAJ LIMITED')); ?></strong><small>ERP SYSTEM</small></span>
            </a>
            <button type="button" id="drawerClose" class="fl-sidebar-close" aria-label="Close navigation">×</button>
        </div>

        <div class="fl-sidebar-scroll">
            <div class="fl-nav-section">
                <span class="fl-nav-title">APP</span>
                <a class="fl-nav-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('dashboard')); ?>"><span class="fl-nav-icon">▦</span><span>Dashboard</span></a>
                <?php if($user->hasPermission('pos.use')): ?><a class="fl-nav-item <?php echo e(request()->routeIs('pos') ? 'active' : ''); ?>" href="<?php echo e(route('pos')); ?>"><span class="fl-nav-icon">▣</span><span>In-house POS</span></a><?php endif; ?>
                <?php if($user->hasPermission('orders.view')): ?><a class="fl-nav-item <?php echo e(request()->routeIs('orders.*') ? 'active' : ''); ?>" href="<?php echo e(route('orders.index')); ?>"><span class="fl-nav-icon">◎</span><span>Online Orders</span><span class="fl-nav-dot"></span></a><?php endif; ?>
                <?php if($user->hasPermission('sales.view')): ?><a class="fl-nav-item <?php echo e(request()->routeIs('sales.*') ? 'active' : ''); ?>" href="<?php echo e(route('sales.index')); ?>"><span class="fl-nav-icon">↗</span><span><?php echo e($isCashier ? 'My Sales' : 'Sales'); ?></span></a><?php endif; ?>
            </div>

            <div class="fl-nav-section">
                <span class="fl-nav-title">BUSINESS</span>
                <?php if($user->hasPermission('products.view')): ?><a class="fl-nav-item <?php echo e(request()->routeIs('products.*') ? 'active' : ''); ?>" href="<?php echo e(route('products.index')); ?>"><span class="fl-nav-icon">◇</span><span>Products</span></a><?php endif; ?>
                <?php if($user->hasPermission('products.manage')): ?><a class="fl-nav-item <?php echo e(request()->routeIs('categories.*') ? 'active' : ''); ?>" href="<?php echo e(route('categories.index')); ?>"><span class="fl-nav-icon">⊞</span><span>Categories</span></a><?php endif; ?>
                <?php if($user->hasPermission('inventory.view')): ?><a class="fl-nav-item <?php echo e(request()->routeIs('inventory.*') ? 'active' : ''); ?>" href="<?php echo e(route('inventory.movements')); ?>"><span class="fl-nav-icon">⇄</span><span><?php echo e($user->hasPermission('inventory.manage') ? 'Stock Management' : 'Stock Ledger'); ?></span></a><?php endif; ?>
                <?php if($user->hasPermission('purchases.manage')): ?><a class="fl-nav-item <?php echo e(request()->routeIs('purchases.*') ? 'active' : ''); ?>" href="<?php echo e(route('purchases.index')); ?>"><span class="fl-nav-icon">↓</span><span>Purchases</span></a><?php endif; ?>
                <?php if($user->hasPermission('customers.manage')): ?><a class="fl-nav-item <?php echo e(request()->routeIs('customers.*') ? 'active' : ''); ?>" href="<?php echo e(route('customers.index')); ?>"><span class="fl-nav-icon">♙</span><span>Customers</span></a><?php endif; ?>
                <?php if($user->hasPermission('suppliers.manage')): ?><a class="fl-nav-item <?php echo e(request()->routeIs('suppliers.*') ? 'active' : ''); ?>" href="<?php echo e(route('suppliers.index')); ?>"><span class="fl-nav-icon">▤</span><span>Suppliers</span></a><?php endif; ?>
            </div>

            <?php if($user->hasPermission('accounting.view') || $user->hasPermission('accounting.manage') || $user->hasPermission('reports.view')): ?>
            <div class="fl-nav-section">
                <span class="fl-nav-title">FINANCE</span>
                <?php if($user->hasPermission('accounting.manage')): ?><a class="fl-nav-item <?php echo e(request()->routeIs('expenses.*') ? 'active' : ''); ?>" href="<?php echo e(route('expenses.index')); ?>"><span class="fl-nav-icon">−</span><span>Expenses</span></a><?php endif; ?>
                <?php if($user->hasPermission('accounting.view')): ?><a class="fl-nav-item <?php echo e(request()->routeIs('journals.*') ? 'active' : ''); ?>" href="<?php echo e(route('journals.index')); ?>"><span class="fl-nav-icon">≡</span><span>Accounting</span></a><?php endif; ?>
                <?php if($user->hasPermission('reports.view')): ?><a class="fl-nav-item <?php echo e(request()->routeIs('reports.*') ? 'active' : ''); ?>" href="<?php echo e(route('reports.profit-loss')); ?>"><span class="fl-nav-icon">◔</span><span>Reports / Profit</span></a><?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if($user->hasPermission('users.manage') || $user->hasPermission('roles.manage') || $user->hasPermission('settings.manage')): ?>
            <div class="fl-nav-section">
                <span class="fl-nav-title">ADMINISTRATION</span>
                <?php if($user->hasPermission('users.manage')): ?><a class="fl-nav-item <?php echo e(request()->routeIs('admin.users') ? 'active' : ''); ?>" href="<?php echo e(route('admin.users')); ?>"><span class="fl-nav-icon">♟</span><span>Users</span></a><?php endif; ?>
                <?php if($user->hasPermission('roles.manage')): ?><a class="fl-nav-item <?php echo e(request()->routeIs('admin.roles.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.roles.index')); ?>"><span class="fl-nav-icon">◆</span><span>Roles & Permissions</span></a><?php endif; ?>
                <?php if($user->hasPermission('settings.manage')): ?><a class="fl-nav-item <?php echo e(request()->routeIs('admin.settings') ? 'active' : ''); ?>" href="<?php echo e(route('admin.settings')); ?>"><span class="fl-nav-icon">⚙</span><span>Settings</span></a><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="fl-sidebar-account">
            <span class="fl-user-avatar"><?php echo e(strtoupper(substr($user->name,0,1))); ?></span>
            <div><strong><?php echo e($user->name); ?></strong><small><?php echo e($roleName); ?></small></div>
            <a href="<?php echo e(route('password.change')); ?>" class="fl-account-cog" title="Change password">⚙</a>
        </div>
    </aside>

    <div class="drawer-overlay fl-drawer-overlay" id="drawerOverlay"></div>

    <section class="fl-workspace">
        <header class="fl-topbar">
            <div class="fl-topbar-left">
                <button class="fl-mobile-menu" type="button" id="drawerToggle" aria-label="Open navigation">☰</button>
                <div class="fl-breadcrumb"><span>Main</span><b>›</b><strong><?php echo $__env->yieldContent('title', 'Dashboard'); ?></strong></div>
            </div>
            <div class="fl-topbar-right">
                <?php if($user->hasPermission('products.view')): ?>
                <form class="fl-global-search" action="<?php echo e(route('products.index')); ?>" method="GET">
                    <span>⌕</span><input name="q" value="<?php echo e(request()->routeIs('products.*') ? request('q') : ''); ?>" placeholder="Search products...">
                </form>
                <?php endif; ?>
                <?php if($user->hasPermission('orders.view')): ?><a href="<?php echo e(route('orders.index',['status'=>'pending'])); ?>" class="fl-icon-button" title="Pending orders">◉</a><?php endif; ?>
                <a href="<?php echo e(route('password.change')); ?>" class="fl-top-user">
                    <span class="fl-top-avatar"><?php echo e(strtoupper(substr($user->name,0,1))); ?></span>
                    <span><strong><?php echo e($user->name); ?></strong><small><?php echo e($roleName); ?></small></span>
                </a>
                <form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button class="fl-logout-button" type="submit" title="Sign out">↪</button></form>
            </div>
        </header>

        <?php if(session('status')): ?><div class="toast success fl-toast"><?php echo e(session('status')); ?></div><?php endif; ?>
        <?php if($errors->any()): ?><div class="toast error fl-toast"><?php echo e($errors->first()); ?></div><?php endif; ?>

        <main class="page-shell fl-content">
            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <footer class="fl-app-footer">
            <span>© <?php echo e(date('Y')); ?> <?php echo e($companySettings->company_name ?? 'TAJ Limited'); ?></span>
            <span>Inventory · POS · E-commerce · Accounting</span>
        </footer>
    </section>
</div>
<script src="<?php echo e(asset('assets/app.js')); ?>"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH F:\taj-limited-erp\resources\views/layouts/app.blade.php ENDPATH**/ ?>