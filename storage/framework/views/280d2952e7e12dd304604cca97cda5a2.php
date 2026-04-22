<div class="space-y-6">
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Import Students','subtitle' => 'Supports CSV and Excel (.xlsx, .xls, .ods). AI can auto-complete missing data, detect patterns, and handle custom fields.','accent' => 'students']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Import Students','subtitle' => 'Supports CSV and Excel (.xlsx, .xls, .ods). AI can auto-complete missing data, detect patterns, and handle custom fields.','accent' => 'students']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('imports.index')); ?>" class="btn-outline">All Imports</a>
         <?php $__env->endSlot(); ?>
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

    <div
        x-data="{ uploading: false }"
        x-on:livewire-upload-start="uploading = true"
        x-on:livewire-upload-finish="uploading = false"
        x-on:livewire-upload-error="uploading = false"
        class="card-padded space-y-4"
    >
        
        <div>
            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">File (CSV or Excel)</label>
            <input wire:model="file" type="file" accept=".csv,.txt,.xlsx,.xls,.ods" class="mt-2" />
            <span x-show="uploading" class="mt-1 flex items-center gap-1 text-xs text-slate-500">
                <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Uploading…
            </span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="mt-2 text-sm font-semibold text-orange-700"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model.live="updateExisting" />
                Update existing students
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model.live="createMissingClasses" />
                Create missing classes
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model.live="createMissingSections" />
                Create missing sections
            </label>
        </div>

        
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="analyze" class="btn-outline" :disabled="<?php echo e(empty($file) ? 'true' : 'false'); ?> || uploading" wire:loading.attr="disabled">
                Analyze
            </button>

            <button
                type="button"
                wire:click="analyzeWithAI"
                :disabled="<?php echo e(empty($file) ? 'true' : 'false'); ?> || uploading"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-violet-600 to-purple-600 px-4 py-2 text-sm font-semibold text-white shadow-lg hover:from-violet-700 hover:to-purple-700 disabled:opacity-50"
            >
                <svg wire:loading wire:target="analyzeWithAI" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                <svg wire:loading.remove wire:target="analyzeWithAI" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                <span wire:loading.remove wire:target="analyzeWithAI">🤖 Smart AI Import</span>
                <span wire:loading wire:target="analyzeWithAI">AI Processing...</span>
            </button>

            <button type="button" wire:click="import" class="btn-primary" :disabled="<?php echo e(empty($file) ? 'true' : 'false'); ?> || uploading" wire:loading.attr="disabled">
                Import
            </button>
        </div>

        
        <div class="rounded-xl border border-violet-200 bg-gradient-to-r from-violet-50 to-purple-50 p-4">
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-100">
                    🤖
                </div>
                <div>
                    <h3 class="text-sm font-bold text-violet-900">Smart AI Import Features</h3>
                    <ul class="mt-1 text-xs text-violet-800 space-y-1">
                        <li>• <strong>Auto-complete missing admission numbers</strong> following your school's pattern</li>
                        <li>• <strong>Smart data cleaning:</strong> Fix capitalization, standardize formats, remove duplicates</li>
                        <li>• <strong>Custom field detection:</strong> Automatically extract additional columns into custom fields</li>
                        <li>• <strong>Pattern recognition:</strong> Match class/section names to existing ones</li>
                        <li>• <strong>Intelligent validation:</strong> Remove empty rows and test data automatically</li>
                    </ul>
                </div>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($summary): ?>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-5">
                <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Valid Rows','value' => number_format((int) ($summary['rows_valid']       ?? 0))]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Valid Rows','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format((int) ($summary['rows_valid']       ?? 0)))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Create','value' => number_format((int) ($summary['to_create']        ?? 0))]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Create','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format((int) ($summary['to_create']        ?? 0)))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Update','value' => number_format((int) ($summary['to_update']        ?? 0))]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Update','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format((int) ($summary['to_update']        ?? 0)))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Skip Existing','value' => number_format((int) ($summary['to_skip_existing'] ?? 0))]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Skip Existing','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format((int) ($summary['to_skip_existing'] ?? 0)))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-card','data' => ['label' => 'Removed/Errors','value' => number_format((int) ($summary['errors']           ?? 0))]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Removed/Errors','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format((int) ($summary['errors']           ?? 0)))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $attributes = $__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__attributesOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682)): ?>
<?php $component = $__componentOriginal527fae77f4db36afc8c8b7e9f5f81682; ?>
<?php unset($__componentOriginal527fae77f4db36afc8c8b7e9f5f81682); ?>
<?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errorsPreview): ?>
            <div class="rounded-xl border border-orange-200 bg-orange-50/60 p-4">
                <div class="text-sm font-semibold text-orange-900">Issues found</div>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-orange-900">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errorsPreview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($e); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showConfirmation): ?>
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 px-4 py-10">
            <div class="w-full max-w-3xl rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between rounded-t-2xl bg-violet-600 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <h2 class="text-lg font-bold text-white">AI Analysis — Review Before Importing</h2>
                    </div>
                    <button wire:click="cancelAiImport" class="text-white/70 hover:text-white">✕</button>
                </div>

                <div class="space-y-5 p-6">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-xl bg-green-50 p-3 text-center">
                            <div class="text-2xl font-black text-green-700"><?php echo e($summary['to_create'] ?? 0); ?></div>
                            <div class="text-xs text-green-600">To Create</div>
                        </div>
                        <div class="rounded-xl bg-blue-50 p-3 text-center">
                            <div class="text-2xl font-black text-blue-700"><?php echo e($summary['to_update'] ?? 0); ?></div>
                            <div class="text-xs text-blue-600">To Update</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3 text-center">
                            <div class="text-2xl font-black text-slate-700"><?php echo e($summary['to_skip_existing'] ?? 0); ?></div>
                            <div class="text-xs text-slate-600">Skip Existing</div>
                        </div>
                        <div class="rounded-xl bg-red-50 p-3 text-center">
                            <div class="text-2xl font-black text-red-700"><?php echo e(count($aiRemoved)); ?></div>
                            <div class="text-xs text-red-600">Removed by AI</div>
                        </div>
                    </div>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aiChanges || $aiRemoved): ?>
                        <div class="rounded-xl border border-violet-200 bg-violet-50">
                            <button 
                                wire:click="$toggle('showChanges')" 
                                class="flex w-full items-center justify-between p-4 text-left hover:bg-violet-100 transition-colors"
                            >
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                    <span class="text-sm font-bold text-violet-900">
                                        View AI Changes (<?php echo e(count($aiChanges) + count($aiRemoved)); ?>)
                                    </span>
                                </div>
                                <svg class="h-4 w-4 text-violet-600 transition-transform <?php echo e($showChanges ? 'rotate-180' : ''); ?>" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showChanges): ?>
                                <div class="border-t border-violet-200 p-4 space-y-4">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aiChanges): ?>
                                        <div>
                                            <div class="mb-2 text-sm font-bold text-violet-900">Data Modifications</div>
                                            <ul class="list-disc space-y-1 pl-5 text-sm text-violet-800">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $aiChanges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $change): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li><?php echo e($change); ?></li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aiRemoved): ?>
                                        <div>
                                            <div class="mb-2 text-sm font-bold text-red-900">Rows Removed</div>
                                            <ul class="list-disc space-y-1 pl-5 text-sm text-red-800">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $aiRemoved; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li>
                                                        <span class="font-semibold">Row <?php echo e($r['row'] ?? '?'); ?>:</span>
                                                        <?php echo e($r['reason'] ?? 'Invalid data'); ?>

                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($r['data'])): ?>
                                                            <span class="ml-1 text-xs text-red-500">(<?php echo e(\Illuminate\Support\Str::limit($r['data'], 60)); ?>)</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aiRows): ?>
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-sm font-bold text-slate-700">Preview & Edit (Double-click cells to edit)</div>
                                <button 
                                    wire:click="toggleChat" 
                                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-500 px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:from-indigo-600 hover:to-purple-600 transition-all"
                                >
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    🤖 Chat with AI
                                </button>
                            </div>
                            
                            <div class="overflow-x-auto rounded-xl border border-slate-200">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-slate-100">
                                        <tr>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Adm #', 'First Name', 'Last Name', 'Gender', 'Class', 'Section', 'Status']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <th class="px-3 py-2 text-left font-semibold text-slate-600"><?php echo e($h); ?></th>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = array_slice($aiRows, 0, 10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-3 py-2 font-mono cursor-pointer hover:bg-blue-50 transition-colors" 
                                                    ondblclick="editCell(this, <?php echo e($index); ?>, 'admission_number', '<?php echo e($row['admission_number'] ?? ''); ?>')"
                                                    title="Double-click to edit">
                                                    <?php echo e($row['admission_number'] ?? ''); ?>

                                                </td>
                                                <td class="px-3 py-2 cursor-pointer hover:bg-blue-50 transition-colors" 
                                                    ondblclick="editCell(this, <?php echo e($index); ?>, 'first_name', '<?php echo e($row['first_name'] ?? ''); ?>')"
                                                    title="Double-click to edit">
                                                    <?php echo e($row['first_name'] ?? ''); ?>

                                                </td>
                                                <td class="px-3 py-2 cursor-pointer hover:bg-blue-50 transition-colors" 
                                                    ondblclick="editCell(this, <?php echo e($index); ?>, 'last_name', '<?php echo e($row['last_name'] ?? ''); ?>')"
                                                    title="Double-click to edit">
                                                    <?php echo e($row['last_name'] ?? ''); ?>

                                                </td>
                                                <td class="px-3 py-2 cursor-pointer hover:bg-blue-50 transition-colors" 
                                                    ondblclick="editCell(this, <?php echo e($index); ?>, 'gender', '<?php echo e($row['gender'] ?? ''); ?>')"
                                                    title="Double-click to edit">
                                                    <?php echo e($row['gender'] ?? ''); ?>

                                                </td>
                                                <td class="px-3 py-2 cursor-pointer hover:bg-blue-50 transition-colors" 
                                                    ondblclick="editCell(this, <?php echo e($index); ?>, 'class_name', '<?php echo e($row['class_name'] ?? ''); ?>')"
                                                    title="Double-click to edit">
                                                    <?php echo e($row['class_name'] ?? ''); ?>

                                                </td>
                                                <td class="px-3 py-2 cursor-pointer hover:bg-blue-50 transition-colors" 
                                                    ondblclick="editCell(this, <?php echo e($index); ?>, 'section_name', '<?php echo e($row['section_name'] ?? ''); ?>')"
                                                    title="Double-click to edit">
                                                    <?php echo e($row['section_name'] ?? ''); ?>

                                                </td>
                                                <td class="px-3 py-2 cursor-pointer hover:bg-blue-50 transition-colors" 
                                                    ondblclick="editCell(this, <?php echo e($index); ?>, 'status', '<?php echo e($row['status'] ?: 'Active'); ?>')"
                                                    title="Double-click to edit">
                                                    <?php echo e($row['status'] ?: 'Active'); ?>

                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </tbody>
                                </table>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($aiRows) > 10): ?>
                                    <div class="px-3 py-2 text-xs text-slate-500 bg-slate-50">… and <?php echo e(count($aiRows) - 10); ?> more rows</div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                        <button wire:click="cancelAiImport" class="btn-outline">Cancel</button>
                        <button wire:click="confirmAiImport" class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-5 py-2 text-sm font-bold text-white hover:bg-violet-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                            Confirm & Import <?php echo e(count($aiRows)); ?> Students
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showChat): ?>
            <div class="fixed inset-y-0 right-0 z-[60] w-96 bg-white shadow-2xl border-l border-slate-200 flex flex-col">
                
                <div class="flex items-center justify-between bg-gradient-to-r from-indigo-500 to-purple-500 px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-full bg-white/20 flex items-center justify-center">
                            🤖
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white">AI Assistant</div>
                            <div class="text-xs text-indigo-100">Ask me to modify your data</div>
                        </div>
                    </div>
                    <button wire:click="toggleChat" class="text-white/70 hover:text-white">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                
                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($chatHistory)): ?>
                        <div class="text-center text-slate-500 py-8">
                            <div class="text-4xl mb-2">💬</div>
                            <div class="text-sm font-medium">Start a conversation!</div>
                            <div class="text-xs mt-1">Try: "Change admission numbers to start with ADM instead of STU"</div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $chatHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex <?php echo e($chat['type'] === 'user' ? 'justify-end' : 'justify-start'); ?>">
                            <div class="max-w-xs <?php echo e($chat['type'] === 'user' ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-800'); ?> rounded-2xl px-4 py-2">
                                <div class="text-sm"><?php echo e($chat['message']); ?></div>
                                <div class="text-xs <?php echo e($chat['type'] === 'user' ? 'text-indigo-200' : 'text-slate-500'); ?> mt-1">
                                    <?php echo e($chat['timestamp']); ?>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($chatProcessing): ?>
                        <div class="flex justify-start">
                            <div class="bg-slate-100 rounded-2xl px-4 py-2">
                                <div class="flex items-center gap-2 text-slate-600">
                                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    <span class="text-sm">AI is thinking...</span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                
                <div class="border-t border-slate-200 p-4">
                    <div class="flex gap-2">
                        <input 
                            wire:model="chatMessage" 
                            wire:keydown.enter="sendChatMessage"
                            type="text" 
                            placeholder="Ask me to modify the data format..."
                            class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                            <?php if($chatProcessing): ?> disabled <?php endif; ?>
                        />
                        <button 
                            wire:click="sendChatMessage"
                            type="button" 
                            class="rounded-xl bg-indigo-500 px-4 py-2 text-white hover:bg-indigo-600 disabled:opacity-50 transition-colors"
                            <?php if($chatProcessing || empty(trim($chatMessage))): ?> disabled <?php endif; ?>
                        >
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($chatProcessing): ?>
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                            <?php else: ?>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </button>
                    </div>
                    
                    <div class="mt-2 text-xs text-slate-500">
                        Examples: "Make all names Title Case", "Change ADM format to YEAR-001", "Fix phone numbers"
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<script>
function editCell(element, rowIndex, field, currentValue) {
    // Create input element
    const input = document.createElement('input');
    input.type = 'text';
    input.value = currentValue;
    input.className = 'w-full px-2 py-1 text-xs border border-blue-300 rounded focus:outline-none focus:border-blue-500';
    
    // Replace cell content with input
    const originalContent = element.innerHTML;
    element.innerHTML = '';
    element.appendChild(input);
    
    // Focus and select all text
    input.focus();
    input.select();
    
    // Handle save on Enter or blur
    const saveEdit = () => {
        const newValue = input.value.trim();
        if (newValue !== currentValue) {
            // Call Livewire method to update the cell
            window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('updateCell', rowIndex, field, newValue);
        }
        element.innerHTML = newValue || originalContent;
    };
    
    // Handle cancel on Escape
    const cancelEdit = () => {
        element.innerHTML = originalContent;
    };
    
    input.addEventListener('blur', saveEdit);
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            saveEdit();
        } else if (e.key === 'Escape') {
            e.preventDefault();
            cancelEdit();
        }
    });
}
</script>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/imports/students.blade.php ENDPATH**/ ?>