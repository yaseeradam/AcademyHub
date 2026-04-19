<div class="space-y-6">
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Imports','subtitle' => 'Upload CSV files to bulk-load school data.','accent' => 'more']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Imports','subtitle' => 'Upload CSV files to bulk-load school data.','accent' => 'more']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="<?php echo e(route('imports.students')); ?>" class="card p-6 transition hover:ring-brand-100">
            <div class="text-sm font-semibold text-gray-900">Import Students</div>
            <div class="mt-2 text-sm text-gray-600">Admission numbers, class/section, guardians.</div>
        </a>

        <a href="<?php echo e(route('imports.teachers')); ?>" class="card p-6 transition hover:ring-brand-100">
            <div class="text-sm font-semibold text-gray-900">Import Teachers</div>
            <div class="mt-2 text-sm text-gray-600">Creates teacher users from a CSV.</div>
        </a>

        <a href="<?php echo e(route('imports.subjects')); ?>" class="card p-6 transition hover:ring-brand-100">
            <div class="text-sm font-semibold text-gray-900">Import Subjects</div>
            <div class="mt-2 text-sm text-gray-600">Subject codes used by results and allocations.</div>
        </a>
    </div>
</div>

