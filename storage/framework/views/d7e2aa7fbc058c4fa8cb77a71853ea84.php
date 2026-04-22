<?php $__env->startSection('content'); ?>
    <div class="space-y-6">
        
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Add New Teacher</h1>
                <p class="mt-1 text-sm text-gray-500">Create a teacher account with access to academic modules</p>
            </div>
            <a href="<?php echo e(route('teachers')); ?>" 
               class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50">
                <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to Teachers
            </a>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="font-medium text-emerald-800"><?php echo e(session('status')); ?></p>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    <div>
                        <h3 class="font-medium text-red-800">Please fix the following errors:</h3>
                        <ul class="mt-2 list-inside list-disc text-sm text-red-700">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <form method="POST" action="<?php echo e(route('teachers.store')); ?>" enctype="multipart/form-data" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <?php echo csrf_field(); ?>
            
            
            <div class="lg:col-span-2 space-y-6">
                
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Personal Information</h2>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Full Name *</label>
                            <input
                                name="name"
                                type="text"
                                class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                                value="<?php echo e(old('name')); ?>"
                                placeholder="e.g., Mrs. Anita Okoye"
                                required
                                autocomplete="name"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Email Address *</label>
                            <input
                                name="email"
                                type="email"
                                class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                                value="<?php echo e(old('email')); ?>"
                                placeholder="e.g., anita@school.edu"
                                required
                                autocomplete="email"
                            />
                        </div>
                    </div>
                </div>

                
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Security & Roles</h2>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Password *</label>
                            <input
                                name="password"
                                type="password"
                                class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                                required
                                autocomplete="new-password"
                                placeholder="Enter secure password"
                            />
                            <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">Confirm Password *</label>
                            <input
                                name="password_confirmation"
                                type="password"
                                class="w-full rounded-lg border-2 border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-900 shadow-sm transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 hover:border-gray-400"
                                required
                                autocomplete="new-password"
                                placeholder="Confirm password"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-4 hover:bg-gray-100 transition-colors">
                            <div>
                                <span class="block text-sm font-medium text-gray-900">Active Account</span>
                                <span class="block text-xs text-gray-500">Enable login access</span>
                            </div>
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" name="is_active" value="1" <?php if(old('is_active', true)): echo 'checked'; endif; ?> class="sr-only peer" />
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-500 peer-focus:ring-offset-2 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </div>
                        </label>
                        
                        <label class="flex cursor-pointer items-center justify-between rounded-lg border border-gray-200 bg-gray-50 p-4 hover:bg-gray-100 transition-colors">
                            <div>
                                <span class="block text-sm font-medium text-gray-900">Class Teacher</span>
                                <span class="block text-xs text-gray-500">Allows managing attendance</span>
                            </div>
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" name="is_class_teacher" value="1" <?php if(old('is_class_teacher', false)): echo 'checked'; endif; ?> class="sr-only peer" />
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-500 peer-focus:ring-offset-2 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            
            
            <div class="space-y-6">
                
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900 mb-4 border-b border-gray-100 pb-2">Profile Photo</h2>
                    <label for="photo" class="flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors group">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="mb-3 w-8 h-8 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <p class="mb-1 items-center text-sm font-semibold text-gray-700">Click to upload photo</p>
                            <p class="text-xs text-gray-500">PNG, JPG up to 2MB</p>
                        </div>
                        <input id="photo" name="photo" type="file" accept="image/*" class="hidden" />
                    </label>
                </div>

                
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-3">
                    <button type="submit" 
                            class="flex w-full justify-center items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-brand-500">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        Create Teacher Account
                    </button>
                    <a href="<?php echo e(route('teachers')); ?>" 
                       class="flex w-full justify-center items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-all hover:bg-gray-50">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    
    <div id="loadingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 backdrop-blur-sm">
        <div class="rounded-2xl bg-white p-8 shadow-xl max-w-sm w-full mx-4">
            <div class="flex flex-col items-center text-center">
                <svg class="animate-spin -ml-1 mr-3 h-10 w-10 text-brand-600 mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Creating Teacher</h3>
                <p class="text-sm text-gray-500">Setting up the profile...</p>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    document.getElementById('loadingModal').classList.remove('hidden');
    document.getElementById('loadingModal').classList.add('flex');
});

// File upload preview
document.getElementById('photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const label = document.querySelector('label[for="photo"]');
            label.innerHTML = `
                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                    <img src="${e.target.result}" class="w-16 h-16 rounded-full object-cover mb-3 ring-2 ring-gray-100" />
                    <p class="text-sm text-gray-700 font-semibold truncate px-4 max-w-full">${file.name}</p>
                    <p class="text-xs text-gray-500 mt-1">Click to change photo</p>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/pages/teachers/create.blade.php ENDPATH**/ ?>