<div class="space-y-6">
    
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Parent Management</h1>
            <p class="mt-1 text-sm text-gray-500">Create parent accounts and link them to their children</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="syncWhatsappPhones" 
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 hover:text-gray-900">
                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                </svg>
                Sync WhatsApp
            </button>
            <button wire:click="openCreateModal" 
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-brand-500">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 4v16m8-8H4"/>
                </svg>
                Create Parent
            </button>
        </div>
    </div>

    
    <div class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="relative max-w-sm flex-1">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
            <input wire:model.live.debounce.300ms="search" 
                   type="text" 
                   placeholder="Search parents by name or email..." 
                   class="w-full rounded-lg border-0 py-2 pl-10 pr-4 text-sm ring-1 ring-inset ring-gray-200 transition-all focus:ring-2 focus:ring-inset focus:ring-brand-500" />
        </div>
    </div>

    
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50">
                        <th class="px-6 py-4 font-semibold text-gray-900">Parent</th>
                        <th class="px-6 py-4 font-semibold text-gray-900">Contact</th>
                        <th class="px-6 py-4 font-semibold text-gray-900">WhatsApp</th>
                        <th class="px-6 py-4 text-center font-semibold text-gray-900">Children</th>
                        <th class="px-6 py-4 text-right font-semibold text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="transition-colors hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($parent->profile_photo_url): ?>
                                        <img src="<?php echo e($parent->profile_photo_url); ?>" class="h-10 w-10 rounded-full object-cover ring-1 ring-gray-200" />
                                    <?php else: ?>
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-brand-600 ring-1 ring-brand-100">
                                            <span class="text-sm font-semibold"><?php echo e(substr($parent->name, 0, 1)); ?></span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <div>
                                        <div class="font-medium text-gray-900"><?php echo e($parent->name); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo e($parent->custom_fields['phone'] ?? 'No phone'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?php echo e($parent->email); ?></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo e($parent->whatsapp_phone ?? 'Not set'); ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                    <?php echo e($parent->students_count); ?> <?php echo e(Str::plural('child', $parent->students_count)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openEditWhatsappModal(<?php echo e($parent->id); ?>)" class="text-gray-400 transition-colors hover:text-brand-600" title="Edit WhatsApp">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                        </svg>
                                    </button>
                                    <button wire:click="openLinkModal(<?php echo e($parent->id); ?>)" class="text-gray-400 transition-colors hover:text-brand-600" title="Link Children">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                        </svg>
                                    </button>
                                    <button wire:click="deleteParent(<?php echo e($parent->id); ?>)" wire:confirm="Are you sure you want to delete this parent account?" class="text-gray-400 transition-colors hover:text-red-600" title="Delete">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-sm text-gray-500">
                                No parents found. Create the first parent account to get started.
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->parents->hasPages()): ?>
            <div class="border-t border-gray-200 bg-gray-50/50 px-6 py-4">
                <?php echo e($this->parents->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Create Parent Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCreateModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Create Parent Account</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" class="mt-1 w-full rounded-lg border-0 py-2 text-sm ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-brand-500" placeholder="Enter parent's full name" />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                        <input wire:model="email" type="email" class="mt-1 w-full rounded-lg border-0 py-2 text-sm ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-brand-500" placeholder="Enter email address" />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password <span class="text-red-500">*</span></label>
                        <input wire:model="password" type="password" class="mt-1 w-full rounded-lg border-0 py-2 text-sm ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-brand-500" placeholder="Enter secure password" />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">WhatsApp Number</label>
                        <input wire:model="phone" type="text" class="mt-1 w-full rounded-lg border-0 py-2 text-sm ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-brand-500" placeholder="Enter WhatsApp number (optional)" />
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <div class="border-t border-gray-100 bg-gray-50/50 px-6 py-4 flex items-center justify-end gap-3">
                    <button wire:click="closeCreateModal" class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Cancel</button>
                    <button wire:click="createParent" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">Create Account</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Edit WhatsApp Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEditWhatsappModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Update WhatsApp</h3>
                </div>
                <div class="px-6 py-4">
                    <label class="block text-sm font-medium text-gray-700">WhatsApp Number</label>
                    <input wire:model="whatsappPhone" type="text" class="mt-1 w-full rounded-lg border-0 py-2 text-sm ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-brand-500" placeholder="Enter WhatsApp number" />
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['whatsappPhone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="mt-1 text-xs text-red-600"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="border-t border-gray-100 bg-gray-50/50 px-6 py-4 flex items-center justify-end gap-3">
                    <button wire:click="closeEditWhatsappModal" class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Cancel</button>
                    <button wire:click="updateWhatsappPhone" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">Save Changes</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Link Children Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLinkModal && $this->selectedParent): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-xl max-h-[80vh] flex flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Link Children to <?php echo e($this->selectedParent->name); ?></h3>
                </div>
                <div class="overflow-y-auto px-6 py-4 space-y-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->availableStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 p-3 hover:bg-gray-50">
                            <input type="checkbox" wire:model="selectedChildren" value="<?php echo e($student->id); ?>" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->passport_photo_url): ?>
                                <img src="<?php echo e($student->passport_photo_url); ?>" class="h-10 w-10 rounded-full object-cover ring-1 ring-gray-200" />
                            <?php else: ?>
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-600 ring-1 ring-gray-200">
                                    <span class="text-sm font-medium"><?php echo e(substr($student->first_name, 0, 1)); ?></span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($student->full_name); ?></div>
                                <div class="text-xs text-gray-500"><?php echo e($student->admission_number); ?> • <?php echo e($student->schoolClass?->name ?? 'Unassigned'); ?></div>
                            </div>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="border-t border-gray-100 bg-gray-50/50 px-6 py-4 flex items-center justify-end gap-3">
                    <button wire:click="closeLinkModal" class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Cancel</button>
                    <button wire:click="linkChildren" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">Link Selected</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/parents/management.blade.php ENDPATH**/ ?>