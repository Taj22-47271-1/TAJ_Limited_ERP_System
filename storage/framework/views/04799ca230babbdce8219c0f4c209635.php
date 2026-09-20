<?php $__env->startSection('title','Sign In'); ?>

<?php $__env->startSection('content'); ?>
<span class="eyebrow dark">WELCOME BACK</span>
<h2>Sign in to TAJ ERP</h2>
<p class="muted">Admin, Cashier or Customer can sign in with their account.</p>

<form method="POST" action="<?php echo e(route('login.store')); ?>" class="stack-form">
<?php echo csrf_field(); ?>
<label>Email address
<input type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus placeholder="you@company.com">
</label>

<label>Password
<div class="password-wrap">
<input id="loginPassword" type="password" name="password" required placeholder="••••••••">
<button type="button" class="show-pass" data-target="loginPassword">Show</button>
</div>
</label>

<div class="form-row between">
<label class="check"><input type="checkbox" name="remember" value="1"> Remember me</label>
<a href="<?php echo e(route('password.request')); ?>">Forgot password?</a>
</div>

<button class="btn primary wide" type="submit">Sign In</button>
</form>

<p class="auth-switch">New account? <a href="<?php echo e(route('register')); ?>">Create account</a></p>

<script>
document.querySelector('.show-pass').onclick=function(){
const i=document.getElementById(this.dataset.target);
i.type=i.type==='password'?'text':'password';
this.textContent=i.type==='password'?'Show':'Hide'
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.auth', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\taj-limited-erp\resources\views/auth/login.blade.php ENDPATH**/ ?>