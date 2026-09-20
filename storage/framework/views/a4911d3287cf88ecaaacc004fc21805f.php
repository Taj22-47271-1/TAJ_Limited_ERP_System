<?php $__env->startSection('title','Verify OTP'); ?>
<?php $__env->startSection('content'); ?>
<span class="eyebrow dark">VERIFY IDENTITY</span><h2>Enter your OTP</h2><p class="muted">A code was sent to <strong><?php echo e($email); ?></strong>.</p>
<form method="POST" action="<?php echo e(route('password.otp.verify')); ?>" class="stack-form"><?php echo csrf_field(); ?><label>6-digit OTP<input class="otp-input" inputmode="numeric" maxlength="6" name="otp" required autofocus placeholder="000000"></label><button class="btn primary wide">Verify OTP</button></form><p class="auth-switch"><a href="<?php echo e(route('password.request')); ?>">Request a new OTP</a></p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\taj-limited-erp\resources\views/auth/verify-otp.blade.php ENDPATH**/ ?>