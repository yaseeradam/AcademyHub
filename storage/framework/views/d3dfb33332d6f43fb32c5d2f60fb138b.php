<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />

        <title><?php echo e(config('myacademy.school_name', config('app.name', 'MyAcademy'))); ?> &middot; CBT Portal</title>

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    </head>

    <body class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-indigo-50 text-slate-900">
        <main>
            <?php echo e($slot); ?>

        </main>

        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>
</html>

<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/layouts/portal.blade.php ENDPATH**/ ?>