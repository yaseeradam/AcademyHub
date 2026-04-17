<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'subtitle' => null,
    'accent' => 'brand',
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
    'title',
    'subtitle' => null,
    'accent' => 'brand',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $accents = [
        'brand' => ['color' => 'blue', 'icon' => 'from-blue-500 to-indigo-600', 'shadow' => 'shadow-blue-500/30', 'bg' => 'from-blue-50/50 to-indigo-50/30'],
        'dashboard' => ['color' => 'indigo', 'icon' => 'from-indigo-500 to-purple-600', 'shadow' => 'shadow-indigo-500/30', 'bg' => 'from-indigo-50/50 to-purple-50/30'],
        'students' => ['color' => 'blue', 'icon' => 'from-blue-500 to-cyan-600', 'shadow' => 'shadow-blue-500/30', 'bg' => 'from-blue-50/50 to-cyan-50/30'],
        'teachers' => ['color' => 'orange', 'icon' => 'from-orange-500 to-amber-600', 'shadow' => 'shadow-orange-500/30', 'bg' => 'from-orange-50/50 to-amber-50/30'],
        'classes' => ['color' => 'sky', 'icon' => 'from-sky-500 to-blue-600', 'shadow' => 'shadow-sky-500/30', 'bg' => 'from-sky-50/50 to-blue-50/30'],
        'subjects' => ['color' => 'purple', 'icon' => 'from-purple-500 to-indigo-600', 'shadow' => 'shadow-purple-500/30', 'bg' => 'from-purple-50/50 to-indigo-50/30'],
        'attendance' => ['color' => 'cyan', 'icon' => 'from-cyan-500 to-blue-600', 'shadow' => 'shadow-cyan-500/30', 'bg' => 'from-cyan-50/50 to-blue-50/30'],
        'results' => ['color' => 'indigo', 'icon' => 'from-indigo-500 to-blue-600', 'shadow' => 'shadow-indigo-500/30', 'bg' => 'from-indigo-50/50 to-blue-50/30'],
        'finance' => ['color' => 'green', 'icon' => 'from-green-500 to-emerald-600', 'shadow' => 'shadow-green-500/30', 'bg' => 'from-green-50/50 to-emerald-50/30'],
        'billing' => ['color' => 'emerald', 'icon' => 'from-emerald-500 to-teal-600', 'shadow' => 'shadow-emerald-500/30', 'bg' => 'from-emerald-50/50 to-teal-50/30'],
        'accounts' => ['color' => 'teal', 'icon' => 'from-teal-500 to-cyan-600', 'shadow' => 'shadow-teal-500/30', 'bg' => 'from-teal-50/50 to-cyan-50/30'],
        'institute' => ['color' => 'blue', 'icon' => 'from-blue-500 to-indigo-600', 'shadow' => 'shadow-blue-500/30', 'bg' => 'from-blue-50/50 to-indigo-50/30'],
        'settings' => ['color' => 'slate', 'icon' => 'from-slate-600 to-gray-700', 'shadow' => 'shadow-slate-500/30', 'bg' => 'from-slate-50/50 to-gray-50/30'],
        'more' => ['color' => 'gray', 'icon' => 'from-gray-600 to-slate-700', 'shadow' => 'shadow-gray-500/30', 'bg' => 'from-gray-50/50 to-slate-50/30'],
    ];

    $config = $accents[$accent] ?? $accents['brand'];
?>

<section <?php echo e($attributes->class('relative overflow-hidden rounded-2xl border border-gray-100 bg-gradient-to-br ' . $config['bg'] . ' shadow-sm')); ?>>
    <div class="absolute inset-0 bg-white/60 backdrop-blur-sm"></div>
    <div class="absolute right-0 top-0 h-32 w-32 translate-x-8 -translate-y-8 rounded-full bg-gradient-to-br <?php echo e($config['icon']); ?> opacity-10"></div>
    
    <div class="relative px-6 py-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <?php
                $leadingSlot = $leading ?? null;
                $leadingContent = $leadingSlot && ! $leadingSlot->isEmpty() ? $leadingSlot : null;
            ?>

            <div class="flex min-w-0 items-center gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($leadingContent): ?>
                    <div class="shrink-0">
                        <?php echo e($leadingContent); ?>

                    </div>
                <?php else: ?>
                    <div class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br <?php echo e($config['icon']); ?> text-white shadow-lg <?php echo e($config['shadow']); ?> shrink-0">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM13 3h8v6h-8V3zM3 21h8v-6H3v6z" />
                        </svg>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="min-w-0">
                    <h1 class="text-2xl font-black tracking-tight text-gray-900">
                        <?php echo e($title); ?>

                    </h1>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subtitle): ?>
                        <p class="mt-1 text-sm font-semibold text-gray-600"><?php echo e($subtitle); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($actions)): ?>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <?php echo e($actions); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($after)): ?>
            <div class="mt-4 border-t border-gray-200/50 pt-4">
                <?php echo e($after); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</section>

<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/components/page-header.blade.php ENDPATH**/ ?>