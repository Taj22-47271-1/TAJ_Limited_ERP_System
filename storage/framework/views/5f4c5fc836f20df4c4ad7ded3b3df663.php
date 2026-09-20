<?php $__env->startSection('title','Reset Password'); ?>
<?php $__env->startSection('content'); ?>
<span class="eyebrow dark">NEW PASSWORD</span><h2>Reset your password</h2><p class="muted">Use at least 8 characters. A stronger password should include uppercase, lowercase, numbers and symbols.</p>
<form method="POST" action="<?php echo e(route('password.update')); ?>" class="stack-form"><?php echo csrf_field(); ?><label>New password<input type="password" name="password" required></label><label>Confirm new password<input type="password" name="password_confirmation" required></label><button class="btn primary wide">Reset Password</button></form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\taj-limited-erp\resources\views/auth/reset-password.blade.php ENDPATH**/ ?>