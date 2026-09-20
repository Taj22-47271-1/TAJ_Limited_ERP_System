<?php $__env->startSection('title','Forgot Password'); ?>
<?php $__env->startSection('content'); ?>
<span class="eyebrow dark">ACCOUNT RECOVERY</span><h2>Forgot your password?</h2><p class="muted">Enter your registered email. We will send a 6-digit OTP that expires in 10 minutes.</p>
<form method="POST" action="<?php echo e(route('password.email')); ?>" class="stack-form"><?php echo csrf_field(); ?><label>Email address<input type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus></label><button class="btn primary wide">Send OTP</button></form><p class="auth-switch"><a href="<?php echo e(route('login')); ?>">Back to sign in</a></p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\taj-limited-erp\resources\views/auth/forgot-password.blade.php ENDPATH**/ ?>