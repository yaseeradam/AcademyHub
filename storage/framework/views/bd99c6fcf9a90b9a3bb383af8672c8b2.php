<?php
    $status = (string) ($exam->status ?? 'draft');
    $variant = match ($status) {
        'live' => 'success',
        'ended' => 'info',
        default => 'neutral',
    };
    $canEdit = (bool) $this->canEdit;
    $tab = $this->tab;
    $hasTheory = $exam->questions->contains('type', 'theory');
?>

<div class="space-y-6" x-data="{ tab: '<?php echo e($tab); ?>', monitorSearch: '', monitorFilter: '' }">
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 to-purple-600 p-6 shadow-xl">
        <div class="relative">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white"><?php echo e($exam->title); ?></h1>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['variant' => ''.e($variant).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => ''.e($variant).'']); ?><?php echo e(ucfirst($status)); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'live' && $exam->access_code): ?>
                            <span class="rounded-lg bg-white/20 px-3 py-1 text-xs font-black text-white backdrop-blur-sm">
                                Code: <?php echo e($exam->access_code); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="<?php echo e(route('cbt.index')); ?>" class="rounded-lg bg-white/20 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm hover:bg-white/30">Back</a>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canEdit): ?>
                        <button wire:click="saveDetails" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-violet-600 hover:bg-pink-50">Save Details</button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'draft'): ?>
                        <button wire:click="goLive" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">✓ Go Live</button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'live'): ?>
                        <button wire:click="endAllExams"
                            wire:confirm="End exam and force-submit all active attempts?"
                            class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600">
                            ⏹ End Exam
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($status, ['live', 'ended'], true) && !$exam->results_released_at): ?>
                        <button wire:click="releaseResults"
                            wire:confirm="Release results to students? They will be able to see their scores if 'Show Score' is enabled."
                            class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                            📊 Release Results
                        </button>
                    <?php elseif($exam->results_released_at): ?>
                        <span class="rounded-lg bg-white/20 px-4 py-2 text-sm font-semibold text-emerald-300 backdrop-blur-sm">
                            ✓ Results Released
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'live' && $exam->access_code): ?>
                <div class="mt-4 flex flex-wrap items-center gap-3 rounded-xl bg-white/10 px-4 py-2.5">
                    <span class="text-xs font-semibold text-white/70">Student Link:</span>
                    <a href="<?php echo e(route('cbt.student', ['code' => $exam->access_code])); ?>" target="_blank" class="font-mono text-sm font-bold text-white underline underline-offset-2">
                        /cbt/student?code=<?php echo e($exam->access_code); ?>

                    </a>
                    <span class="text-xs text-white/60">(share this with students)</span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="mt-4 flex flex-wrap gap-2 border-t border-white/20 pt-4">
                <button @click="tab = 'questions'" :class="tab === 'questions' ? 'bg-white text-violet-600' : 'bg-white/10 text-white hover:bg-white/20'" class="rounded-lg px-4 py-2 text-sm font-semibold transition">Questions (<?php echo e($exam->questions->count()); ?>)</button>
                <button @click="tab = 'details'" :class="tab === 'details' ? 'bg-white text-violet-600' : 'bg-white/10 text-white hover:bg-white/20'" class="rounded-lg px-4 py-2 text-sm font-semibold transition">Details</button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($status, ['live', 'ended'], true)): ?>
                    <button @click="tab = 'monitor'" :class="tab === 'monitor' ? 'bg-white text-violet-600' : 'bg-white/10 text-white hover:bg-white/20'" class="rounded-lg px-4 py-2 text-sm font-semibold transition">
                        Monitor
                        <?php $ongoingCount = $exam->attempts->filter(fn($a) => $a->started_at && !$a->submitted_at && !$a->terminated_at)->count(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ongoingCount > 0): ?>
                            <span class="ml-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-green-500 text-[10px] font-black text-white"><?php echo e($ongoingCount); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button @click="tab = 'actions'" :class="tab === 'actions' ? 'bg-white text-violet-600' : 'bg-white/10 text-white hover:bg-white/20'" class="rounded-lg px-4 py-2 text-sm font-semibold transition">Actions</button>
            </div>

        </div>
    </div>



    <div x-show="tab === 'details'" class="rounded-2xl bg-white p-6 shadow-lg border border-gray-200">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canEdit): ?>
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-bold text-gray-800 mb-2">Title</label>
                    <input wire:model="title" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" />
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="mt-2 text-sm text-red-600 font-medium"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-2">Class</label>
                    <select wire:model.live="classId" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm">
                        <option value="" class="text-gray-500">Select Class</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($class->id); ?>" class="text-gray-900"><?php echo e($class->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['classId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="mt-2 text-sm text-red-600 font-medium"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-2">Subject</label>
                    <select wire:model.live="subjectId" <?php if(! $classId): echo 'disabled'; endif; ?> class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm disabled:bg-gray-100 disabled:text-gray-500">
                        <option value="" class="text-gray-500">Select Subject</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($subject->id); ?>" class="text-gray-900"><?php echo e($subject->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['subjectId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="mt-2 text-sm text-red-600 font-medium"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-2">Duration (minutes)</label>
                    <input wire:model="durationMinutes" type="number" min="1" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" />
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['durationMinutes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="mt-2 text-sm text-red-600 font-medium"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-800 mb-2">Term</label>
                    <select wire:model.live="term" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm">
                        <option value="1" class="text-gray-900">Term 1</option>
                        <option value="2" class="text-gray-900">Term 2</option>
                        <option value="3" class="text-gray-900">Term 3</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label class="block text-sm font-bold text-gray-800 mb-2">Session</label>
                    <input wire:model="session" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" placeholder="2025/2026" />
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['session'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="mt-2 text-sm text-red-600 font-medium"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($me?->role === 'admin'): ?>
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Advanced Settings</h3>
                    <div class="grid gap-6 lg:grid-cols-2">
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">Start Time</label>
                            <input wire:model="startsAt" type="datetime-local" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">End Time</label>
                            <input wire:model="endsAt" type="datetime-local" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">PIN</label>
                            <input wire:model="pin" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm font-mono" placeholder="1234" />
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">Grace Minutes</label>
                            <input wire:model="graceMinutes" type="number" min="0" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" />
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-bold text-gray-800 mb-2">Allowed CIDRs</label>
                            <textarea wire:model="allowedCidrs" rows="3" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" placeholder="192.168.99.0/24"></textarea>
                        </div>
                        <div class="lg:col-span-2">
                            <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <input type="checkbox" wire:model="showScore" class="w-5 h-5 text-blue-600 border-2 border-gray-300 rounded focus:ring-blue-500" />
                                <span class="text-sm font-bold text-gray-800">Show Score After Submit</span>
                            </label>
                        </div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="mt-6">
                <label class="block text-sm font-bold text-gray-800 mb-2">Description</label>
                <textarea wire:model="description" rows="4" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm"></textarea>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                <div class="grid gap-4 text-base">
                    <div class="flex"><span class="font-bold text-gray-800 w-24">Class:</span> <span class="text-gray-900"><?php echo e($exam->schoolClass?->name ?? '-'); ?></span></div>
                    <div class="flex"><span class="font-bold text-gray-800 w-24">Subject:</span> <span class="text-gray-900"><?php echo e($exam->subject?->name ?? '-'); ?></span></div>
                    <div class="flex"><span class="font-bold text-gray-800 w-24">Duration:</span> <span class="text-gray-900"><?php echo e((int) $exam->duration_minutes); ?> minutes</span></div>
                    <div class="flex"><span class="font-bold text-gray-800 w-24">Term:</span> <span class="text-gray-900"><?php echo e($exam->term); ?></span></div>
                    <div class="flex"><span class="font-bold text-gray-800 w-24">Session:</span> <span class="text-gray-900"><?php echo e($exam->session); ?></span></div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>



    <div x-show="tab === 'questions'" class="rounded-2xl bg-white p-6 shadow-lg border border-gray-200">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canEdit && $status === 'draft'): ?>
            
            <div class="mb-5 flex flex-wrap items-center gap-2">
                <button wire:click="openAiPanel" class="flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">&#10024; Generate with AI</button>
                <button wire:click="openImportPanel" class="flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">&#128194; Import from File</button>
                <a href="<?php echo e(route('cbt.sample-download')); ?>" class="flex items-center gap-2 rounded-lg border border-sky-300 bg-white px-4 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-50">&#11015; Sample File</a>
                <label class="ml-auto flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                    <input type="checkbox" wire:model="shuffleQuestions" wire:change="saveShuffle" class="h-4 w-4 rounded text-violet-600" />
                    Shuffle Questions
                </label>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAiPanel): ?>
                <div class="mb-5 rounded-xl border-2 border-violet-200 bg-violet-50 p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="font-bold text-violet-900">✨ AI Question Generator</div>
                        <button wire:click="closeAiPanel" class="text-xs text-gray-500 hover:text-gray-700">✕ Close</button>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="sm:col-span-2">
                            <label class="text-xs font-bold uppercase text-gray-600">Topic / Instruction</label>
                            <input wire:model="aiTopic" class="mt-1 w-full rounded-lg border-2 border-gray-200 px-3 py-2 text-sm focus:border-violet-400" placeholder="e.g. Photosynthesis, World War II, Algebra" />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['aiTopic'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="mt-1 text-xs text-red-600"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-gray-600">Type</label>
                            <select wire:model="aiType" class="mt-1 w-full rounded-lg border-2 border-gray-200 px-3 py-2 text-sm focus:border-violet-400">
                                <option value="mcq">MCQ Only</option>
                                <option value="theory">Theory Only</option>
                                <option value="mixed">Mixed (MCQ + Theory)</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-gray-600">Count</label>
                            <input wire:model="aiCount" type="number" min="1" max="20" class="mt-1 w-full rounded-lg border-2 border-gray-200 px-3 py-2 text-sm focus:border-violet-400" />
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-gray-600">Marks each</label>
                            <input wire:model="aiMarks" type="number" min="1" max="100" class="mt-1 w-full rounded-lg border-2 border-gray-200 px-3 py-2 text-sm focus:border-violet-400" />
                        </div>
                    </div>
                    <div class="mt-3">
                        <button wire:click="generateAiQuestions" wire:loading.attr="disabled" wire:target="generateAiQuestions" class="rounded-lg bg-violet-600 px-5 py-2 text-sm font-bold text-white hover:bg-violet-700 disabled:opacity-60">
                            <span wire:loading.remove wire:target="generateAiQuestions">Generate Questions</span>
                            <span wire:loading wire:target="generateAiQuestions">Generating… please wait</span>
                        </button>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($aiPreview)): ?>
                        <div class="mt-4 space-y-2">
                            <div class="text-xs font-bold uppercase text-gray-600">Preview — <?php echo e(count($aiPreview)); ?> questions</div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $aiPreview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="rounded-lg border border-violet-200 bg-white p-3 text-sm">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1">
                                            <span class="mr-2 rounded bg-violet-100 px-1.5 py-0.5 text-xs font-bold text-violet-700"><?php echo e(strtoupper($q['type'])); ?></span>
                                            <span class="font-medium text-gray-900"><?php echo e($q['prompt']); ?></span>
                                            <span class="ml-2 text-xs text-gray-400">(<?php echo e($q['marks']); ?> mark<?php echo e($q['marks'] != 1 ? 's' : ''); ?>)</span>
                                        </div>
                                        <button wire:click="removeAiPreviewItem(<?php echo e($idx); ?>)" class="text-xs text-red-500 hover:text-red-700">✕</button>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($q['type'] ?? '') === 'mcq' && ! empty($q['options'])): ?>
                                        <div class="mt-2 grid grid-cols-2 gap-1">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $q['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oi => $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="rounded px-2 py-1 text-xs <?php echo e($oi === ($q['correct'] ?? 0) ? 'bg-green-100 font-bold text-green-800' : 'bg-gray-50 text-gray-700'); ?>">
                                                    <?php echo e(chr(65 + $oi)); ?>. <?php echo e($opt); ?>

                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($oi === ($q['correct'] ?? 0)): ?> <span class="ml-1">✓</span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button wire:click="insertAiQuestions" class="mt-2 rounded-lg bg-emerald-600 px-5 py-2 text-sm font-bold text-white hover:bg-emerald-700">
                                Add <?php echo e(count($aiPreview)); ?> Questions to Exam
                            </button>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showImportPanel): ?>
                <div class="mb-5 rounded-xl border-2 border-sky-200 bg-sky-50 p-5">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="font-bold text-sky-900">📂 Import Questions from File</div>
                        <button wire:click="closeImportPanel" class="text-xs text-gray-500 hover:text-gray-700">✕ Close</button>
                    </div>
                    <details class="mb-3 rounded-lg border border-sky-200 bg-white p-3 text-xs text-gray-600">
                        <summary class="cursor-pointer font-semibold text-sky-700">File Format Guide (click to expand)</summary>
                        <div class="mt-3 space-y-3">
                            <p class="text-gray-700">Save as <strong>.txt</strong> from Notepad or Word (File → Save As → Plain Text). One blank line between questions.</p>
                            <pre class="rounded bg-gray-50 p-3 font-mono text-xs leading-relaxed">1. What is the powerhouse of the cell?
A. Nucleus
B. Mitochondria
C. Ribosome
D. Golgi body
ANS: B
MARKS: 2

2. Define osmosis.
TYPE: theory
MARKS: 5

3. Which planet is closest to the sun?
A. Earth
B. Venus
C. Mercury
D. Mars
ANS: C</pre>
                            <ul class="list-disc space-y-1 pl-4 text-gray-600">
                                <li>Question starts with a number and dot: <code class="rounded bg-gray-100 px-1">1.</code></li>
                                <li>Options are <code class="rounded bg-gray-100 px-1">A.</code> <code class="rounded bg-gray-100 px-1">B.</code> <code class="rounded bg-gray-100 px-1">C.</code> <code class="rounded bg-gray-100 px-1">D.</code></li>
                                <li>Correct answer: <code class="rounded bg-gray-100 px-1">ANS: B</code></li>
                                <li>Theory question: add <code class="rounded bg-gray-100 px-1">TYPE: theory</code> (no options needed)</li>
                                <li><code class="rounded bg-gray-100 px-1">MARKS: 2</code> is optional — defaults to 1</li>
                                <li>Questions with no options are auto-treated as theory</li>
                            </ul>
                        </div>
                    </details>
                    <div class="flex items-center gap-3">
                        <input type="file" wire:model="importFile" accept=".txt" class="text-sm text-gray-700" />
                        <button wire:click="parseImportFile" wire:loading.attr="disabled" wire:target="parseImportFile,importFile" class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-bold text-white hover:bg-sky-700 disabled:opacity-60">
                            <span wire:loading.remove wire:target="parseImportFile,importFile">Parse File</span>
                            <span wire:loading wire:target="parseImportFile,importFile">Parsing…</span>
                        </button>
                        <a href="<?php echo e(route('cbt.sample-download')); ?>" class="rounded-lg border border-sky-300 bg-white px-4 py-2 text-sm font-bold text-sky-700 hover:bg-sky-50">⬇ Sample File</a>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['importFile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="mt-1 text-xs text-red-600"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($importPreview)): ?>
                        <div class="mt-4 space-y-2">
                            <div class="text-xs font-bold uppercase text-gray-600">Preview — <?php echo e(count($importPreview)); ?> questions found</div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $importPreview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="rounded-lg border border-sky-200 bg-white p-3 text-sm">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1">
                                            <span class="mr-2 rounded bg-sky-100 px-1.5 py-0.5 text-xs font-bold text-sky-700"><?php echo e(strtoupper($q['type'])); ?></span>
                                            <span class="font-medium text-gray-900"><?php echo e($q['prompt']); ?></span>
                                            <span class="ml-2 text-xs text-gray-400">(<?php echo e($q['marks']); ?> mark<?php echo e($q['marks'] != 1 ? 's' : ''); ?>)</span>
                                        </div>
                                        <button wire:click="removeImportPreviewItem(<?php echo e($idx); ?>)" class="text-xs text-red-500 hover:text-red-700">✕</button>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($q['type'] ?? '') === 'mcq' && ! empty($q['options'])): ?>
                                        <div class="mt-2 grid grid-cols-2 gap-1">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $q['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oi => $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="rounded px-2 py-1 text-xs <?php echo e($oi === ($q['correct'] ?? 0) ? 'bg-green-100 font-bold text-green-800' : 'bg-gray-50 text-gray-700'); ?>">
                                                    <?php echo e(chr(65 + $oi)); ?>. <?php echo e($opt); ?>

                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($oi === ($q['correct'] ?? 0)): ?> <span class="ml-1">✓</span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button wire:click="insertImportQuestions" class="mt-2 rounded-lg bg-emerald-600 px-5 py-2 text-sm font-bold text-white hover:bg-emerald-700">
                                Add <?php echo e(count($importPreview)); ?> Questions to Exam
                            </button>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div id="question-form" class="rounded-lg border-2 border-dashed p-6 transition-all duration-300 <?php echo e($editingQuestionId ? 'border-blue-400 bg-blue-50' : 'border-violet-300 bg-violet-50'); ?>"
                x-data="{ qtype: '<?php echo e($questionType); ?>' }"
                x-init="Livewire.on('questionTypeUpdated', t => qtype = t)"
            >
                <div class="text-lg font-bold text-gray-900 mb-4"><?php echo e($editingQuestionId ? 'Edit' : 'Add'); ?> Question</div>
                <textarea wire:model="questionPrompt" rows="3" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm" placeholder="Enter your question here..."></textarea>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['questionPrompt'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="mt-2 text-sm text-red-600 font-medium"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mt-6 grid gap-6 lg:grid-cols-[200px,1fr]">
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">Question Type</label>
                        <select x-model="qtype" wire:model="questionType" class="w-full px-4 py-3 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white shadow-sm">
                            <option value="mcq">Multiple Choice (MCQ)</option>
                            <option value="theory">Theory/Essay</option>
                        </select>
                    </div>

                    <div x-show="qtype === 'mcq'" x-cloak>
                        <label class="block text-sm font-bold text-gray-800 mb-2">Answer Options</label>
                        <div class="grid gap-3 lg:grid-cols-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 4; $i++): ?>
                                <div class="p-3 bg-white border-2 border-gray-200 rounded-lg">
                                    <div class="flex items-center gap-3 mb-2">
                                        <input type="radio" wire:model="correctIndex" value="<?php echo e($i); ?>" id="opt<?php echo e($i); ?>" class="w-4 h-4 text-green-600 border-2 border-gray-300 focus:ring-green-500" />
                                        <label for="opt<?php echo e($i); ?>" class="text-sm font-bold text-gray-800">Option <?php echo e(chr(65 + $i)); ?> <?php echo e($correctIndex == $i ? '(Correct Answer)' : ''); ?></label>
                                    </div>
                                    <input wire:model="optionLabels.<?php echo e($i); ?>" class="w-full px-3 py-2 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white" placeholder="Enter option <?php echo e(chr(65 + $i)); ?>" />
                                </div>
                            <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <div x-show="qtype === 'theory'" x-cloak class="p-4 rounded-lg border-2 border-dashed border-blue-300 bg-blue-50">
                        <div class="text-sm font-bold text-blue-800 mb-2">Theory Question</div>
                        <div class="text-sm text-blue-700">Students write a detailed answer. Requires manual marking.</div>
                    </div>
                </div>
                
                <div class="mt-6 flex items-center gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">Marks</label>
                        <input wire:model="questionMarks" type="number" min="1" class="w-24 px-3 py-2 text-base border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 bg-white" placeholder="10" />
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button wire:click="saveQuestion" class="px-6 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 shadow-md"><?php echo e($editingQuestionId ? 'Update Question' : 'Add Question'); ?></button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editingQuestionId): ?>
                            <button wire:click="startNewQuestion" class="px-6 py-3 bg-gray-500 text-white font-bold rounded-lg hover:bg-gray-600 shadow-md">Cancel</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($status, ['live', 'ended'], true)): ?>
            <div class="mb-5 flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
                <svg class="h-5 w-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                This exam is <strong class="mx-1"><?php echo e(ucfirst($status)); ?></strong> — questions are locked and cannot be added or modified.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mt-6 space-y-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $exam->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="rounded-lg border-2 p-4 shadow-sm transition-all <?php echo e($editingQuestionId === $q->id ? 'border-blue-400 bg-blue-50' : 'border-gray-300 bg-gray-50'); ?>">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="text-base font-bold text-gray-900 mb-3">Q<?php echo e($loop->iteration); ?>. <?php echo e($q->prompt); ?></div>
                            <div class="mt-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($q->type === 'theory'): ?>
                                    <span class="inline-block rounded-lg bg-blue-200 px-3 py-2 text-sm font-bold text-blue-800">Theory Question</span>
                                <?php else: ?>
                                    <div class="grid gap-2 lg:grid-cols-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $q->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="rounded-lg bg-white px-3 py-2 border-2 <?php echo e($opt->is_correct ? 'border-green-500 bg-green-50' : 'border-gray-200'); ?>">
                                                <span class="font-bold text-gray-800"><?php echo e(chr(65 + $loop->index)); ?>.</span> 
                                                <span class="text-gray-900"><?php echo e($opt->label); ?></span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($opt->is_correct): ?>
                                                    <span class="ml-2 text-green-600 font-bold">✓ Correct</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="mt-3 text-sm font-bold text-gray-700">Marks: <?php echo e((int) $q->marks); ?></div>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canEdit && $status === 'draft'): ?>
                            <div class="flex gap-2 ml-4">
                                <button wire:click="editQuestion(<?php echo e($q->id); ?>)" class="px-4 py-2 bg-blue-500 text-white font-bold rounded-lg hover:bg-blue-600 text-sm">Edit</button>
                                <button wire:click="deleteQuestion(<?php echo e($q->id); ?>)" class="px-4 py-2 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 text-sm">Delete</button>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-8 text-center">
                    <div class="text-base font-medium text-gray-600">No questions added yet</div>
                    <div class="text-sm text-gray-500 mt-1">Click "Add Question" above to create your first question</div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <div x-show="tab === 'monitor'" class="space-y-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($status, ['live', 'ended'], true)): ?>
            <?php
                $roster = collect();
                $submittedCount = 0;
                $inProgressCount = 0;
                $notStartedCount = 0;
                $terminatedCount = 0;

                if ($me?->role === 'admin') {
                    $roster = $this->roster;
                    $submittedCount = (int) $roster->where('state', 'submitted')->count();
                    $inProgressCount = (int) $roster->where('state', 'in_progress')->count();
                    $notStartedCount = (int) $roster->where('state', 'not_started')->count();
                    $terminatedCount = (int) $roster->where('state', 'terminated')->count();
                }
            ?>
            
            <div class="grid gap-4 lg:grid-cols-4">
                <div class="cursor-pointer rounded-lg bg-emerald-50 p-4 ring-1 ring-emerald-200 hover:ring-2 hover:ring-emerald-400"
                     @click="monitorFilter = monitorFilter === 'submitted' ? '' : 'submitted'" :class="monitorFilter === 'submitted' ? 'ring-2 ring-emerald-500' : ''">
                    <div class="text-2xl font-bold text-emerald-900"><?php echo e($submittedCount); ?></div>
                    <div class="text-xs font-semibold text-emerald-700">Submitted</div>
                </div>
                <div class="cursor-pointer rounded-lg bg-blue-50 p-4 ring-1 ring-blue-200 hover:ring-2 hover:ring-blue-400"
                     @click="monitorFilter = monitorFilter === 'in_progress' ? '' : 'in_progress'" :class="monitorFilter === 'in_progress' ? 'ring-2 ring-blue-500' : ''">
                    <div class="text-2xl font-bold text-blue-900"><?php echo e($inProgressCount); ?></div>
                    <div class="text-xs font-semibold text-blue-700">In Progress</div>
                </div>
                <div class="cursor-pointer rounded-lg bg-orange-50 p-4 ring-1 ring-orange-200 hover:ring-2 hover:ring-orange-400"
                     @click="monitorFilter = monitorFilter === 'terminated' ? '' : 'terminated'" :class="monitorFilter === 'terminated' ? 'ring-2 ring-orange-500' : ''">
                    <div class="text-2xl font-bold text-orange-900"><?php echo e($terminatedCount); ?></div>
                    <div class="text-xs font-semibold text-orange-700">Terminated</div>
                </div>
                <div class="cursor-pointer rounded-lg bg-gray-50 p-4 ring-1 ring-gray-200 hover:ring-2 hover:ring-gray-400"
                     @click="monitorFilter = monitorFilter === 'not_started' ? '' : 'not_started'" :class="monitorFilter === 'not_started' ? 'ring-2 ring-gray-500' : ''">
                    <div class="text-2xl font-bold text-gray-900"><?php echo e($notStartedCount); ?></div>
                    <div class="text-xs font-semibold text-gray-700">Not Started</div>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-lg">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($me?->role === 'admin'): ?>
                    
                    <div class="mb-4 flex flex-wrap gap-3">
                        <div class="relative flex-1 min-w-[200px]">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                            <input
                                x-model="monitorSearch"
                                type="text"
                                placeholder="Search by name or admission no..."
                                class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-4 text-sm focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                            />
                        </div>
                        <select x-model="monitorFilter" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                            <option value="">All Statuses</option>
                            <option value="submitted">Submitted</option>
                            <option value="in_progress">In Progress</option>
                            <option value="not_started">Not Started</option>
                            <option value="terminated">Terminated</option>
                        </select>
                        <button x-show="monitorSearch || monitorFilter" @click="monitorSearch = ''; monitorFilter = ''" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-500 hover:bg-gray-50">Clear</button>
                    </div>

                    <div class="space-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $roster; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $student = $row['student'];
                                $attempt = $row['attempt'];
                                $state = (string) $row['state'];
                                $answered = (int) ($row['answered'] ?? 0);
                                $totalQuestions = (int) ($exam?->questions?->count() ?? 0);
                            ?>
                            <div class="flex items-center justify-between rounded-lg border bg-gray-50 p-3"
                                 x-show="
                                    (monitorFilter === '' || monitorFilter === '<?php echo e($state); ?>') &&
                                    (monitorSearch === '' || '<?php echo e(strtolower($student->full_name)); ?>'.includes(monitorSearch.toLowerCase()) || '<?php echo e(strtolower($student->admission_number)); ?>'.includes(monitorSearch.toLowerCase()))
                                 ">
                                <div class="flex items-center gap-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->passport_photo_url): ?>
                                        <img src="<?php echo e($student->passport_photo_url); ?>" class="h-10 w-10 rounded-lg object-cover" />
                                    <?php else: ?>
                                        <div class="grid h-10 w-10 place-items-center rounded-lg bg-gray-200 text-sm font-bold"><?php echo e(mb_substr($student->first_name ?? 'S', 0, 1)); ?></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <div>
                                        <div class="text-sm font-semibold"><?php echo e($student->full_name ?? trim($student->first_name.' '.$student->last_name)); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo e($student->admission_number); ?></div>
                                    </div>
                                </div>
                            <div class="flex items-center gap-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($state === 'submitted'): ?>
                                    <span class="rounded bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">Submitted</span>
                                <?php elseif($state === 'in_progress'): ?>
                                    <span class="rounded bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800"><?php echo e($answered); ?>/<?php echo e($totalQuestions); ?></span>
                                <?php elseif($state === 'terminated'): ?>
                                    <span class="rounded bg-orange-100 px-2 py-1 text-xs font-semibold text-orange-800">Terminated</span>
                                <?php else: ?>
                                    <span class="rounded bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800">Not Started</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasTheory && $attempt && ($attempt->submitted_at || $attempt->terminated_at)): ?>
                                        <?php
                                            $theoryStatus = $attempt->theory_status ?? 'pending';
                                        ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($theoryStatus === 'marked'): ?>
                                            <span class="rounded bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">✓ Marked</span>
                                        <?php elseif($theoryStatus === 'forwarded'): ?>
                                            <span class="rounded bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">→ Forwarded</span>
                                        <?php else: ?>
                                            <span class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-800">Theory Pending</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <button wire:click="startReview(<?php echo e($attempt->id); ?>)" class="rounded bg-violet-100 px-2 py-1 text-xs font-semibold text-violet-700 hover:bg-violet-200">
                                            <?php echo e($theoryStatus === 'marked' ? 'View' : 'Mark'); ?>

                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attempt): ?>
                                        <div class="relative">
                                            <button onclick="this.nextElementSibling.classList.toggle('hidden')" class="rounded bg-gray-200 px-3 py-1 text-xs font-semibold hover:bg-gray-300">•••</button>
                                            <div class="absolute right-0 z-10 mt-1 hidden w-40 rounded-lg bg-white shadow-xl ring-1 ring-black/5">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasTheory && ($attempt->submitted_at || $attempt->terminated_at) && ($attempt->theory_status ?? 'pending') !== 'marked'): ?>
                                                    <button wire:click="startForward(<?php echo e($attempt->id); ?>)" class="block w-full px-3 py-2 text-left text-xs font-semibold text-violet-700 hover:bg-violet-50">Forward to Teacher</button>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <button wire:click="startIpOverride(<?php echo e($attempt->id); ?>)" class="block w-full px-3 py-2 text-left text-xs font-semibold hover:bg-gray-50">Change IP</button>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($state === 'in_progress'): ?>
                                                    <button wire:click="forceSubmitAttempt(<?php echo e($attempt->id); ?>)" class="block w-full px-3 py-2 text-left text-xs font-semibold text-blue-700 hover:bg-blue-50">Force Submit</button>
                                                    <button wire:click="terminateAttempt(<?php echo e($attempt->id); ?>)" class="block w-full px-3 py-2 text-left text-xs font-semibold text-orange-700 hover:bg-orange-50">Terminate</button>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <button wire:click="resetAttempt(<?php echo e($attempt->id); ?>)" class="block w-full px-3 py-2 text-left text-xs font-semibold text-rose-700 hover:bg-rose-50">Reset</button>
                                            </div>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="rounded-lg border-2 border-dashed bg-gray-50 p-6 text-center text-sm text-gray-600">No students</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div x-show="monitorSearch !== '' || monitorFilter !== ''" class="pt-1 text-center text-xs text-gray-400" x-cloak>
                            <span x-text="document.querySelectorAll('[x-show]').length"></span>
                            Tip: click a status card or use the dropdown to filter
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editingAttemptIpId): ?>
                        <div class="mt-4 rounded-lg border border-violet-200 bg-violet-50 p-4">
                            <div class="text-sm font-semibold">Allow IP Override</div>
                            <div class="mt-2 flex gap-2">
                                <input wire:model="allowedIp" class="input flex-1 font-mono text-sm" placeholder="192.168.1.50" />
                                <button wire:click="saveIpOverride" class="btn-primary">Save</button>
                                <button wire:click="cancelIpOverride" class="btn-outline">Cancel</button>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $exam->attempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="flex items-center justify-between rounded-lg border bg-gray-50 p-3">
                                <div>
                                    <div class="text-sm font-semibold"><?php echo e($a->student?->full_name ?? 'Student'); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo e($a->student?->admission_number); ?></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold"><?php echo e((int) $a->score); ?>/<?php echo e((int) $a->max_score); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo e(number_format((float) $a->percent, 1)); ?>%</div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasTheory && ($a->submitted_at || $a->terminated_at)): ?>
                                        <?php
                                            $theoryStatus = $a->theory_status ?? 'pending';
                                        ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($theoryStatus === 'marked'): ?>
                                            <span class="mt-1 inline-block rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">✓ Marked</span>
                                        <?php elseif($theoryStatus === 'forwarded'): ?>
                                            <span class="mt-1 inline-block rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">→ Forwarded</span>
                                        <?php else: ?>
                                            <span class="mt-1 inline-block rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-800">Pending</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <button wire:click="startReview(<?php echo e($a->id); ?>)" class="mt-2 rounded bg-violet-100 px-2 py-1 text-xs font-semibold text-violet-700 hover:bg-violet-200">
                                            <?php echo e($theoryStatus === 'marked' ? 'View' : 'Mark'); ?>

                                        </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="rounded-lg border-2 border-dashed bg-gray-50 p-6 text-center text-sm text-gray-600">No attempts</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasTheory && $this->reviewAttempt): ?>
        <?php
            $reviewAttempt = $this->reviewAttempt;
            $theoryQuestions = $exam->questions->where('type', 'theory');
        ?>
        <div class="rounded-2xl bg-white p-6 shadow-lg">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Theory Review</div>
                    <div class="text-xs text-gray-500">
                        <?php echo e($reviewAttempt->student?->full_name ?? 'Student'); ?> • <?php echo e($reviewAttempt->student?->admission_number); ?>

                    </div>
                </div>
                <button wire:click="cancelReview" class="btn-outline">Close</button>
            </div>

            <div class="mt-4 space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $theoryQuestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $answer = $reviewAttempt->answers->firstWhere('question_id', $question->id);
                        $response = trim((string) ($answer?->text_answer ?? ''));
                    ?>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="text-sm font-semibold text-gray-900"><?php echo e($question->prompt); ?></div>
                        <div class="mt-2 rounded-lg bg-white p-3 text-sm text-gray-700">
                            <?php echo e($response !== '' ? $response : 'No answer submitted.'); ?>

                        </div>
                        <div class="mt-3 flex items-center gap-3">
                            <div>
                                <label class="text-xs font-semibold uppercase text-gray-500">Marks (<?php echo e((int) $question->marks); ?>)</label>
                                <input
                                    type="number"
                                    min="0"
                                    max="<?php echo e((int) $question->marks); ?>"
                                    wire:model.defer="theoryMarks.<?php echo e($question->id); ?>"
                                    class="input w-24 text-sm mt-1"
                                />
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["theoryMarks.{$question->id}"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-xs text-rose-600"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs font-semibold uppercase text-gray-500">Comment (Optional)</label>
                                <input
                                    type="text"
                                    wire:model.defer="theoryComments.<?php echo e($question->id); ?>"
                                    placeholder="Add a short comment..."
                                    class="input w-full text-sm mt-1"
                                />
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
                <button wire:click="saveTheoryMarks" class="btn-primary">Save Marks</button>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div x-show="tab === 'actions'" class="rounded-2xl bg-white p-6 shadow-lg">
        <div class="space-y-3">
            <a href="<?php echo e(route('cbt.exams.pdf', $exam)); ?>" target="_blank" class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 text-sm font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50">
                <span class="text-xl">📄</span>
                <span>Download PDF</span>
            </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'live' && $exam->access_code): ?>
                <a href="<?php echo e(route('cbt.student', ['code' => $exam->access_code])); ?>" target="_blank" class="flex items-center gap-3 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm font-semibold text-blue-700 transition hover:border-blue-300 hover:bg-blue-100">
                    <span class="text-xl">🎓</span>
                    <span>Student Portal</span>
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($me?->role === 'admin'): ?>
                <a href="<?php echo e(route('cbt.exams.export', $exam)); ?>" class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-700 transition hover:border-green-300 hover:bg-green-100">
                    <span class="text-xl">📊</span>
                    <span>Export CSV</span>
                </a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($status, ['live', 'ended'], true)): ?>
                    <button wire:click="transferToResults" onclick="return confirm('Transfer CBT scores to academic results?')" class="flex w-full items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-left text-sm font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100">
                        <span class="text-xl">✅</span>
                        <span>Transfer to Results</span>
                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'live'): ?>
                        <button wire:click="endAllExams" onclick="return confirm('End this exam?')" class="flex w-full items-center gap-3 rounded-lg border border-orange-200 bg-orange-50 p-4 text-left text-sm font-semibold text-orange-700 transition hover:border-orange-300 hover:bg-orange-100">
                            <span class="text-xl">⏹️</span>
                            <span>End This Exam</span>
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button wire:click="$set('showDeleteModal', true)" class="flex w-full items-center gap-3 rounded-lg border border-red-200 bg-red-50 p-4 text-left text-sm font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-100">
                    <span class="text-xl">🗑️</span>
                    <span>Delete Exam</span>
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDeleteModal ?? false): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-data x-show="true" x-transition>
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" @click.away="$wire.set('showDeleteModal', false)">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Delete Exam</h3>
                        <p class="text-sm text-gray-600">This action cannot be undone</p>
                    </div>
                </div>
                
                <div class="mt-4 rounded-lg bg-red-50 p-4">
                    <p class="text-sm text-red-900">
                        Are you sure you want to delete <strong><?php echo e($exam->title); ?></strong>? 
                        All questions, student attempts, and scores will be permanently removed.
                    </p>
                </div>

                <div class="mt-6 flex gap-3">
                    <button wire:click="$set('showDeleteModal', false)" class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="deleteExam" class="flex-1 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                        Delete Exam
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showForwardModal ?? false): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-data x-show="true" x-transition>
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" @click.away="$wire.set('showForwardModal', false)">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-violet-100">
                        <svg class="h-6 w-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Forward to Teacher</h3>
                        <p class="text-sm text-gray-600">Assign theory marking</p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="text-xs font-semibold uppercase text-gray-500">Select Teacher</label>
                    <select wire:model="forwardTeacherId" class="mt-1 select w-full">
                        <option value="">Choose teacher...</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->availableTeachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($teacher->id); ?>"><?php echo e($teacher->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['forwardTeacherId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="mt-1 text-xs text-red-600"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="mt-6 flex gap-3">
                    <button wire:click="cancelForward" class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="confirmForward" class="flex-1 rounded-lg bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-700">
                        Forward
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('scrollToForm', () => {
            setTimeout(() => {
                const el = document.getElementById('question-form');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 50);
        });
    });
</script><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/cbt/exam-editor.blade.php ENDPATH**/ ?>