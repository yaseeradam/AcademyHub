<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'value',
    'cardBg' => 'bg-white',
    'ringColor' => 'ring-gray-100',
    'iconBg' => 'bg-gray-100',
    'iconColor' => 'text-gray-700',
]));

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

foreach (array_filter(([
    'label',
    'value',
    'cardBg' => 'bg-white',
    'ringColor' => 'ring-gray-100',
    'iconBg' => 'bg-gray-100',
    'iconColor' => 'text-gray-700',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $gradients = [
        'blue' => ['bg' => 'from-blue-50 to-blue-100/50', 'ring' => 'ring-blue-200/50', 'text' => 'text-blue-600', 'icon' => 'from-blue-400 to-blue-600 shadow-blue-500/30', 'accent' => 'bg-blue-500/5'],
        'purple' => ['bg' => 'from-purple-50 to-purple-100/50', 'ring' => 'ring-purple-200/50', 'text' => 'text-purple-600', 'icon' => 'from-purple-400 to-purple-600 shadow-purple-500/30', 'accent' => 'bg-purple-500/5'],
        'green' => ['bg' => 'from-green-50 to-green-100/50', 'ring' => 'ring-green-200/50', 'text' => 'text-green-600', 'icon' => 'from-green-400 to-green-600 shadow-green-500/30', 'accent' => 'bg-green-500/5'],
        'emerald' => ['bg' => 'from-emerald-50 to-emerald-100/50', 'ring' => 'ring-emerald-200/50', 'text' => 'text-emerald-600', 'icon' => 'from-emerald-400 to-emerald-600 shadow-emerald-500/30', 'accent' => 'bg-emerald-500/5'],
        'violet' => ['bg' => 'from-violet-50 to-violet-100/50', 'ring' => 'ring-violet-200/50', 'text' => 'text-violet-600', 'icon' => 'from-violet-400 to-violet-600 shadow-violet-500/30', 'accent' => 'bg-violet-500/5'],
        'amber' => ['bg' => 'from-amber-50 to-amber-100/50', 'ring' => 'ring-amber-200/50', 'text' => 'text-amber-600', 'icon' => 'from-amber-400 to-amber-600 shadow-amber-500/30', 'accent' => 'bg-amber-500/5'],
        'cyan' => ['bg' => 'from-cyan-50 to-cyan-100/50', 'ring' => 'ring-cyan-200/50', 'text' => 'text-cyan-600', 'icon' => 'from-cyan-400 to-cyan-600 shadow-cyan-500/30', 'accent' => 'bg-cyan-500/5'],
        'pink' => ['bg' => 'from-pink-50 to-pink-100/50', 'ring' => 'ring-pink-200/50', 'text' => 'text-pink-600', 'icon' => 'from-pink-400 to-pink-600 shadow-pink-500/30', 'accent' => 'bg-pink-500/5'],
        'indigo' => ['bg' => 'from-indigo-50 to-indigo-100/50', 'ring' => 'ring-indigo-200/50', 'text' => 'text-indigo-600', 'icon' => 'from-indigo-400 to-indigo-600 shadow-indigo-500/30', 'accent' => 'bg-indigo-500/5'],
    ];
    
    $colorKey = explode('-', $iconColor)[1] ?? 'blue';
    $colors = $gradients[$colorKey] ?? $gradients['blue'];
?>

<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br <?php echo e($colors['bg']); ?> p-5 shadow-sm ring-1 <?php echo e($colors['ring']); ?> transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
    <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full <?php echo e($colors['accent']); ?>"></div>
    <div class="flex items-center gap-4">
        <?php
            $iconSlot = $icon ?? null;
            $iconContent = $iconSlot && ! $iconSlot->isEmpty() ? $iconSlot : $slot;
            $hasIcon = $iconContent && ! $iconContent->isEmpty();
        ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasIcon): ?>
            <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br <?php echo e($colors['icon']); ?> text-white shadow-lg">
                <?php echo e($iconContent); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="min-w-0">
            <div class="text-xs font-medium uppercase tracking-wide <?php echo e($colors['text']); ?>"><?php echo e($label); ?></div>
            <div class="mt-1.5 text-2xl font-bold tracking-tight text-gray-900"><?php echo e($value); ?></div>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/components/stat-card.blade.php ENDPATH**/ ?>