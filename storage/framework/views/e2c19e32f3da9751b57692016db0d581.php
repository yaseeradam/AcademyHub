<div class="space-y-6">
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => $student ? 'Edit Student' : 'Add Student','subtitle' => 'Admissions and student bio-data.','accent' => 'students']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($student ? 'Edit Student' : 'Add Student'),'subtitle' => 'Admissions and student bio-data.','accent' => 'students']); ?>
         <?php $__env->slot('actions', null, []); ?> 
            <a href="<?php echo e(route('students.index')); ?>" class="btn-ghost">Back</a>
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

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-2">
                <div class="text-sm font-semibold text-gray-900">Bio</div>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-semibold text-gray-700">Admission Number</label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$student): ?>
                                <label class="flex items-center gap-2 text-xs text-gray-600">
                                    <input type="checkbox" wire:model.live="auto_admission" class="rounded">
                                    Auto-generate
                                </label>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <input
                            wire:model.live="admission_number"
                            type="text"
                            <?php if($auto_admission && !$student): ?> readonly <?php endif; ?>
                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500 <?php echo e($auto_admission && !$student ? 'bg-gray-50' : ''); ?>"
                        />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['admission_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Gender</label>
                        <select
                            wire:model.live="gender"
                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        >
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['gender'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">First Name</label>
                        <input
                            wire:model.live="first_name"
                            type="text"
                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Last Name</label>
                        <input
                            wire:model.live="last_name"
                            type="text"
                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Class</label>
                        <select
                            wire:model.live="class_id"
                            wire:change="$refresh"
                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        >
                            <option value="">Select class</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['class_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Section</label>
                        <select
                            wire:model="section_id"
                            wire:key="sections-<?php echo e($class_id); ?>"
                            <?php echo e(!$class_id ? 'disabled' : ''); ?>

                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500 <?php echo e(!$class_id ? 'bg-gray-50 text-gray-400' : ''); ?>"
                        >
                            <option value=""><?php echo e($class_id ? 'Select section' : 'Select class first'); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($section->id); ?>"><?php echo e($section->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['section_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Date of Birth</label>
                        <input
                            wire:model.live="dob"
                            type="date"
                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['dob'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <label class="text-sm font-semibold text-gray-700">Blood Group</label>
                        <input
                            wire:model.live="blood_group"
                            type="text"
                            placeholder="e.g. O+"
                            class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['blood_group'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- Custom Fields -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->customFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="<?php echo e($field->type === 'textarea' ? 'sm:col-span-2' : ''); ?>">
                            <label class="text-sm font-semibold text-gray-700">
                                <?php echo e($field->label); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($field->required): ?>
                                    <span class="text-red-500">*</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </label>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($field->type === 'text'): ?>
                                <input
                                    wire:model.live="customFieldValues.<?php echo e($field->name); ?>"
                                    type="text"
                                    placeholder="<?php echo e($field->placeholder); ?>"
                                    class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                />
                            <?php elseif($field->type === 'number'): ?>
                                <input
                                    wire:model.live="customFieldValues.<?php echo e($field->name); ?>"
                                    type="number"
                                    placeholder="<?php echo e($field->placeholder); ?>"
                                    class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                />
                            <?php elseif($field->type === 'date'): ?>
                                <input
                                    wire:model.live="customFieldValues.<?php echo e($field->name); ?>"
                                    type="date"
                                    class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                />
                            <?php elseif($field->type === 'select'): ?>
                                <select
                                    wire:model.live="customFieldValues.<?php echo e($field->name); ?>"
                                    class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                >
                                    <option value=""><?php echo e($field->placeholder ?: 'Select option'); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($field->options): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $field->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($option); ?>"><?php echo e($option); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            <?php elseif($field->type === 'textarea'): ?>
                                <textarea
                                    wire:model.live="customFieldValues.<?php echo e($field->name); ?>"
                                    rows="3"
                                    placeholder="<?php echo e($field->placeholder); ?>"
                                    class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                ></textarea>
                            <?php elseif($field->type === 'checkbox'): ?>
                                <div class="mt-2">
                                    <label class="flex items-center">
                                        <input
                                            wire:model.live="customFieldValues.<?php echo e($field->name); ?>"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-600"><?php echo e($field->placeholder ?: 'Yes'); ?></span>
                                    </label>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["customFieldValues.{$field->name}"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="text-sm font-semibold text-gray-900">Passport Photo</div>

                    <div class="mt-5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($passport): ?>
                            <div class="flex items-center gap-3">
                                <img
                                    src="<?php echo e($passport->temporaryUrl()); ?>"
                                    alt="Passport preview"
                                    class="h-20 w-20 rounded-xl object-cover ring-1 ring-inset ring-gray-200"
                                />
                                <div class="text-xs text-gray-500">Preview (not saved yet)</div>
                            </div>
                        <?php elseif($student?->passport_photo_url): ?>
                            <div class="flex items-center gap-3">
                                <img
                                    src="<?php echo e($student->passport_photo_url); ?>"
                                    alt="<?php echo e($student->full_name); ?>"
                                    class="h-20 w-20 rounded-xl object-cover ring-1 ring-inset ring-gray-200"
                                />
                                <div class="text-xs text-gray-500">Current photo</div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <input
                            type="file"
                            wire:model="passport"
                            accept="image/*"
                            class="mt-3 block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-gray-700 hover:file:bg-gray-200"
                        />
                        <div wire:loading wire:target="passport" class="mt-2 flex items-center gap-2 text-xs font-medium text-brand-600">
                            <svg class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Uploading image...
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['passport'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">Create Parent Account</div>
                            <div class="text-xs text-gray-500 mt-1">Create a new parent account and link to this student</div>
                        </div>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.live="create_parent_account" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">Create Account</span>
                        </label>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($create_parent_account): ?>
                        <div class="space-y-4 border-t border-gray-100 pt-4">
                            <div>
                                <label class="text-sm font-semibold text-gray-700">Parent Name <span class="text-red-500">*</span></label>
                                <input
                                    wire:model.live="parent_name"
                                    type="text"
                                    placeholder="Enter parent's full name"
                                    class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                />
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['parent_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-gray-700">Email Address <span class="text-red-500">*</span></label>
                                <input
                                    wire:model.live="parent_email"
                                    type="email"
                                    placeholder="Enter email for login"
                                    class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                />
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['parent_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-gray-700">Phone Number</label>
                                <input
                                    wire:model.live="parent_phone"
                                    type="text"
                                    placeholder="Enter phone number"
                                    class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                />
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['parent_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-gray-700">Password <span class="text-red-500">*</span></label>
                                <input
                                    wire:model.live="parent_password"
                                    type="password"
                                    placeholder="Enter login password"
                                    class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                />
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['parent_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <div class="flex items-start gap-2">
                                    <svg class="h-5 w-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="text-sm text-blue-800">
                                        <strong>Note:</strong> The parent will be able to log in with the email and password above to view their child's academic progress, attendance, and fees.
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="text-sm font-semibold text-gray-900">Linked Parents</div>
                    <div class="mt-2 text-xs text-gray-500">Select existing parent accounts to grant them portal access to this student.</div>

                    <div class="mt-4">
                        <select
                            wire:model="parent_ids"
                            multiple
                            class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            style="min-height: 120px;"
                        >
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->availableParents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($parent->id); ?>"><?php echo e($parent->name); ?> (<?php echo e($parent->email); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <div class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple.</div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['parent_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="text-sm font-semibold text-gray-900">Emergency & Guardian Info</div>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label class="text-sm font-semibold text-gray-700">Name</label>
                            <input
                                wire:model.live="guardian_name"
                                type="text"
                                class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['guardian_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-gray-700">Phone</label>
                            <input
                                wire:model.live="guardian_phone"
                                type="text"
                                class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['guardian_phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-gray-700">Address</label>
                            <textarea
                                wire:model.live="guardian_address"
                                rows="3"
                                class="mt-2 w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            ></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['guardian_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="mt-2 text-sm text-orange-700"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="text-sm font-semibold text-gray-900">Status</div>

                    <div class="mt-5 space-y-4">
                        <select
                            wire:model.live="status"
                            class="w-full rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        >
                            <option value="Active">Active</option>
                            <option value="Graduated">Graduated</option>
                            <option value="Expelled">Expelled</option>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="text-sm text-orange-700"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <button
                            type="submit"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600"
                        >
                            Save Student
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Save Processing Modal -->
    <div wire:loading.flex wire:target="save" class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/40 backdrop-blur-[2px]" style="display: none;">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 transform transition-all">
            <div class="text-center">
                <div class="mx-auto mb-5 h-16 w-16 rounded-full bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-brand-200">
                    <svg class="animate-spin h-8 w-8 text-white" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Saving Changes</h3>
                <p class="text-gray-600 mb-6 text-sm">Please hold on while we update the student record. This usually takes just a moment.</p>
                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-brand-500 to-indigo-600 rounded-full" style="width: 0%; animation: progress 2s ease-in-out infinite"></div>
                </div>
            </div>
        </div>
    </div>
</div>

    <?php
        $__scriptKey = '987492864-0';
        ob_start();
    ?>
<script>
    let progressModal = null;

    $wire.on('validation-error', () => {
        if (progressModal) { progressModal.remove(); progressModal = null; }
        showModal('error', 'Validation Error', 'Please fix the validation errors before saving.');
    });

    $wire.on('upload-error', (event) => {
        if (progressModal) { progressModal.remove(); progressModal = null; }
        showModal('error', 'Upload Failed', event[0].message);
    });

    $wire.on('student-saved', (event) => {
        if (progressModal) { progressModal.remove(); progressModal = null; }
        const data = event?.[0] ?? event;
        showStudentSavedModal(data);
    });

    // Modals are now handled by wire:loading for reliability

    function showProgressModal() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm';
        modal.style.animation = 'fadeIn 0.2s ease-out';
        
        modal.innerHTML = `
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 transform" style="animation: slideUp 0.3s ease-out">
                <div class="text-center">
                    <div class="mx-auto mb-4 h-16 w-16 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 flex items-center justify-center">
                        <svg class="animate-spin h-8 w-8 text-white" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Saving Student...</h3>
                    <p class="text-gray-600 mb-4">Please wait while we save the student information.</p>
                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div class="progress-bar h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full" style="width: 0%; animation: progress 2s ease-in-out infinite"></div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        return modal;
    }

    function showModal(type, title, message, onClose = null) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm';
        modal.style.animation = 'fadeIn 0.2s ease-out';
        
        const colors = {
            success: { bg: 'from-emerald-500 to-teal-500', icon: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
            error: { bg: 'from-rose-500 to-red-500', icon: 'M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }
        };
        
        modal.innerHTML = `
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full transform" style="animation: slideUp 0.3s ease-out">
                <div class="bg-gradient-to-r ${colors[type].bg} p-6 rounded-t-3xl">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <svg class="h-12 w-12 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="${colors[type].icon}" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-white">${title}</h3>
                    </div>
                </div>
                <div class="p-6">
                    <p class="text-gray-700 text-lg leading-relaxed">${message}</p>
                </div>
                <div class="p-6 pt-0 flex gap-3">
                    <button onclick="this.closest('.fixed').remove(); ${onClose ? 'arguments[0]()' : ''}" class="flex-1 bg-gradient-to-r ${colors[type].bg} text-white font-bold py-3 px-6 rounded-xl hover:shadow-lg transition-all">
                        ${type === 'success' ? 'View Students' : 'Close'}
                    </button>
                </div>
            </div>
        `;
        
        if (onClose) {
            modal.querySelector('button').onclick = () => { modal.remove(); onClose(); };
        }
        
        document.body.appendChild(modal);
        modal.onclick = (e) => { if (e.target === modal) { modal.remove(); if (onClose) onClose(); } };
    }

    function showStudentSavedModal(data) {
        const studentsUrl = '<?php echo e(route('students.index')); ?>';
        const downloadUrl = data?.downloadUrl;
        const isNew = !!data?.isNew;
        const parentCreated = !!data?.parentCreated;
        const parentEmail = data?.parentEmail;
        const parentPassword = data?.parentPassword;

        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm';
        overlay.style.animation = 'fadeIn 0.2s ease-out';

        const panel = document.createElement('div');
        panel.className = 'bg-white rounded-3xl shadow-2xl max-w-lg w-full transform';
        panel.style.animation = 'slideUp 0.3s ease-out';

        const header = document.createElement('div');
        header.className = 'bg-gradient-to-r from-emerald-500 to-teal-500 p-6 rounded-t-3xl';

        const headerRow = document.createElement('div');
        headerRow.className = 'flex items-center gap-4';

        const iconWrap = document.createElement('div');
        iconWrap.className = 'flex-shrink-0';
        iconWrap.innerHTML = `
            <svg class="h-12 w-12 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        `;

        const title = document.createElement('h3');
        title.className = 'text-2xl font-bold text-white';
        title.textContent = isNew ? 'Student Created Successfully' : 'Student Updated Successfully';

        headerRow.appendChild(iconWrap);
        headerRow.appendChild(title);
        header.appendChild(headerRow);

        const body = document.createElement('div');
        body.className = 'p-6';

        const message = document.createElement('p');
        message.className = 'text-gray-700 text-lg leading-relaxed mb-4';
        const actionText = isNew ? 'created' : 'updated';
        const nameText = data?.name ? String(data.name) : 'Student';
        const admissionText = data?.admission ? String(data.admission) : '';
        message.textContent = admissionText
            ? `${nameText} (${admissionText}) has been ${actionText} successfully.`
            : `${nameText} has been ${actionText} successfully.`;

        body.appendChild(message);

        // Add parent account info if created
        if (parentCreated && parentEmail && parentPassword) {
            const parentInfo = document.createElement('div');
            parentInfo.className = 'bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4';
            parentInfo.innerHTML = `
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <div>
                        <h4 class="font-semibold text-blue-900 mb-2">Parent Account Created</h4>
                        <div class="text-sm text-blue-800 space-y-1">
                            <div><strong>Email:</strong> ${parentEmail}</div>
                            <div><strong>Password:</strong> ${parentPassword}</div>
                            <div class="text-xs text-blue-600 mt-2">Please share these login details with the parent.</div>
                        </div>
                    </div>
                </div>
            `;
            body.appendChild(parentInfo);
        }

        const actions = document.createElement('div');
        actions.className = 'p-6 pt-0 flex gap-3';

        const goStudents = () => { window.location.href = studentsUrl; };
        const close = () => { overlay.remove(); };

        if (isNew && downloadUrl) {
            const downloadBtn = document.createElement('button');
            downloadBtn.type = 'button';
            downloadBtn.className = 'flex-1 bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold py-3 px-6 rounded-xl hover:shadow-lg transition-all';
            downloadBtn.textContent = 'Download Admission Letter';
            downloadBtn.onclick = () => {
                window.open(downloadUrl, '_blank', 'noopener');
                goStudents();
            };

            const viewBtn = document.createElement('button');
            viewBtn.type = 'button';
            viewBtn.className = 'flex-1 bg-white text-emerald-700 font-bold py-3 px-6 rounded-xl border border-emerald-200 hover:shadow transition-all';
            viewBtn.textContent = 'View Students';
            viewBtn.onclick = goStudents;

            actions.appendChild(downloadBtn);
            actions.appendChild(viewBtn);
        } else {
            const okBtn = document.createElement('button');
            okBtn.type = 'button';
            okBtn.className = 'flex-1 bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-bold py-3 px-6 rounded-xl hover:shadow-lg transition-all';
            okBtn.textContent = 'View Students';
            okBtn.onclick = goStudents;
            actions.appendChild(okBtn);
        }

        panel.appendChild(header);
        panel.appendChild(body);
        panel.appendChild(actions);
        overlay.appendChild(panel);
        document.body.appendChild(overlay);

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                close();
                goStudents();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                close();
                goStudents();
            }
        }, { once: true });
    }
</script>
<style>
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    @keyframes progress {
        0% { width: 0%; }
        50% { width: 70%; }
        100% { width: 90%; }
    }
</style>
    <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/students/form.blade.php ENDPATH**/ ?>