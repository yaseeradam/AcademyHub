<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['variant' => 'neutral']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['variant' => 'neutral']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $classes = match ($variant) {
        'success' => 'bg-green-100 text-green-600',
        'warning' => 'bg-orange-100 text-orange-600',
        'info' => 'bg-brand-100 text-brand-700',
        'purple' => 'bg-purple-100 text-purple-600',
        default => 'bg-gray-100 text-gray-600',
    };
?>

<span <?php echo e($attributes->class("inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {$classes}")); ?>>
    <?php echo e($slot); ?>

</span>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/components/status-badge.blade.php ENDPATH**/ ?>