<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />

        <title><?php echo e(config('myacademy.school_name', config('app.name', 'MyAcademy'))); ?></title>

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>

    <body class="h-full">
        <?php echo e($slot); ?>

    </body>
</html>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/components/layouts/guest.blade.php ENDPATH**/ ?>