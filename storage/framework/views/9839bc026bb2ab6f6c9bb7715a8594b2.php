<?php ($user = auth()->user()); ?>

<?php ($activeConversation = $conversationId ? $this->conversations->firstWhere('id', $conversationId) : null); ?>
<?php ($activeOtherId = data_get($activeConversation, 'other_user_id')); ?>
<?php ($activeTitle = data_get($activeConversation, 'title', 'Chat')); ?>
<?php ($activePhotoUrl = data_get($activeConversation, 'other_user_photo_url')); ?>

<div class="-mx-6 -my-6 overflow-hidden">
<div class="bg-gradient-to-br from-slate-100 via-gray-50 to-blue-50 flex h-[calc(100vh-4rem)]">
    <!-- Sidebar - User List -->
    <div class="w-full lg:w-96 bg-white border-r border-gray-200 flex flex-col shadow-xl">
        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-4 shadow-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-white/20 backdrop-blur-sm grid place-items-center">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-white">Messages</h1>
                        <p class="text-xs text-white/80"><?php echo e($user->name); ?></p>
                    </div>
                </div>
                <a href="<?php echo e(route('more-features')); ?>" class="rounded-lg bg-white/20 backdrop-blur-sm px-3 py-1.5 text-xs font-semibold text-white hover:bg-white/30 transition">
                    Back
                </a>
            </div>
        </div>

        <!-- Search -->
        <div class="p-3 border-b border-gray-200 bg-gray-50">
            <div class="relative">
                <input wire:model.live.debounce.250ms="userSearch" class="w-full rounded-xl border-0 bg-white pl-10 pr-4 py-2.5 text-sm shadow-sm ring-1 ring-gray-200 focus:ring-2 focus:ring-emerald-500" placeholder="Search users..." />
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <!-- User List -->
        <div class="flex-1 overflow-y-auto">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->recipientOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php ($unread = (int) ($this->unreadByUser[$u->id] ?? 0)); ?>
                <button
                    type="button"
                    wire:click="startConversation(<?php echo e($u->id); ?>)"
                    class="w-full flex items-center gap-3 p-4 border-b transition group <?php echo e((int) $activeOtherId === (int) $u->id ? 'bg-emerald-50 border-emerald-100' : 'hover:bg-gray-50 border-gray-100'); ?>"
                >
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($u->profile_photo_url): ?>
                        <img
                            src="<?php echo e($u->profile_photo_url); ?>"
                            alt="<?php echo e($u->name); ?>"
                            class="h-12 w-12 rounded-full object-cover ring-1 ring-inset ring-gray-200 shadow-sm"
                        />
                    <?php else: ?>
                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 grid place-items-center text-white font-bold shadow-lg transition">
                            <?php echo e(strtoupper(substr($u->name, 0, 1))); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="flex-1 text-left min-w-0">
                        <div class="font-semibold text-gray-900 truncate"><?php echo e($u->name); ?></div>
                        <div class="text-xs text-gray-500 capitalize"><?php echo e($u->role); ?></div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unread > 0): ?>
                        <div class="inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-red-600 px-2 py-0.5 text-[10px] font-black leading-none text-white shadow-sm">
                            <?php echo e($unread > 99 ? '99+' : $unread); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((int) $activeOtherId === (int) $u->id): ?>
                        <div class="h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-sm"></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <svg class="h-5 w-5 text-gray-400 group-hover:text-emerald-600 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->recipientOptions->isEmpty()): ?>
                <div class="p-8 text-center text-sm text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    No users found
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>


    </div>

    <!-- Chat Window -->
    <div class="flex-1 flex flex-col bg-[#e5ddd5]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;100&quot; height=&quot;100&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cpath d=&quot;M0 0h100v100H0z&quot; fill=&quot;%23e5ddd5&quot;/%3E%3Cpath d=&quot;M20 20h60v60H20z&quot; fill=&quot;%23d9d0c7&quot; opacity=&quot;.05&quot;/%3E%3C/svg%3E');">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$conversationId): ?>
            <div class="flex-1 flex items-center justify-center">
                <div class="text-center">
                    <div class="mx-auto h-32 w-32 rounded-full bg-white/80 backdrop-blur-sm grid place-items-center shadow-2xl mb-6">
                        <svg class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-700 mb-2"><?php echo e(config('myacademy.school_name', config('app.name', 'MyAcademy'))); ?> Messages</h2>
                    <p class="text-gray-600">Select a user from the sidebar to start chatting</p>
                </div>
            </div>
        <?php else: ?>
             <!-- Chat Header -->
             <div class="bg-white border-b border-gray-200 p-4 shadow-sm">
                 <div class="flex items-center gap-3">
                     <div class="h-10 w-10 rounded-full overflow-hidden ring-1 ring-inset ring-gray-200 bg-white">
                         <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activePhotoUrl): ?>
                             <img
                                 src="<?php echo e($activePhotoUrl); ?>"
                                 alt="<?php echo e($activeTitle); ?>"
                                 class="h-10 w-10 object-cover"
                             />
                         <?php else: ?>
                             <div class="h-10 w-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 grid place-items-center text-white font-bold">
                                 <?php echo e(strtoupper(substr($activeTitle !== '' ? $activeTitle : 'C', 0, 1))); ?>

                             </div>
                         <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                     </div>
                     <div>
                         <div class="font-semibold text-gray-900"><?php echo e($activeTitle); ?></div>
                         <div class="text-xs text-gray-500">Online</div>
                     </div>
                 </div>
             </div>

            <!-- Messages -->
             <div class="flex-1 overflow-y-auto p-4 space-y-3" wire:poll.5s>
                 <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $this->chatMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                     <?php ($isMe = (int) $m->sender_id === (int) $me->id); ?>
                     <div class="flex items-end gap-2 <?php echo e($isMe ? 'justify-end' : 'justify-start'); ?>">
                         <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $isMe): ?>
                             <div class="shrink-0">
                                 <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($m->sender?->profile_photo_url): ?>
                                     <img
                                         src="<?php echo e($m->sender?->profile_photo_url); ?>"
                                         alt="<?php echo e($m->sender?->name); ?>"
                                         class="h-8 w-8 rounded-full object-cover ring-1 ring-inset ring-gray-200 shadow-sm"
                                     />
                                 <?php else: ?>
                                     <?php if (isset($component)) { $__componentOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.avatar','data' => ['name' => $m->sender?->name ?? 'User','size' => '32','class' => 'ring-1 ring-inset ring-gray-200 shadow-sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m->sender?->name ?? 'User'),'size' => '32','class' => 'ring-1 ring-inset ring-gray-200 shadow-sm']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b)): ?>
<?php $attributes = $__attributesOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b; ?>
<?php unset($__attributesOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b)): ?>
<?php $component = $__componentOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b; ?>
<?php unset($__componentOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b); ?>
<?php endif; ?>
                                 <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                             </div>
                         <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                         <div class="max-w-[75%]">
                             <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isMe): ?>
                                 <div class="text-xs font-semibold text-gray-600 mb-1 ml-2"><?php echo e($m->sender?->name); ?></div>
                             <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                             <div class="rounded-lg <?php echo e($isMe ? 'bg-[#dcf8c6] rounded-tr-none' : 'bg-white rounded-tl-none'); ?> px-4 py-2 shadow-sm">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim((string) $m->body) !== ''): ?>
                                    <p class="text-sm text-gray-900 whitespace-pre-wrap"><?php echo e($m->body); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($m->attachment_path): ?>
                                    <div class="mt-2">
                                        <a
                                            class="inline-flex items-center gap-2 rounded-lg bg-black/5 px-3 py-2 text-xs font-bold text-gray-800 hover:bg-black/10"
                                            href="<?php echo e(route('messages.attachments.download', $m)); ?>"
                                            target="_blank"
                                        >
                                            <span>Download</span>
                                            <span class="font-mono text-[10px] text-gray-600"><?php echo e($m->attachment_name ?: 'attachment'); ?></span>
                                        </a>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($m->attachment_size): ?>
                                            <div class="mt-1 text-[10px] text-gray-500"><?php echo e(number_format($m->attachment_size / 1024, 1)); ?> KB</div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div class="text-[10px] text-gray-500 mt-1 text-right"><?php echo e($m->created_at?->format('g:i A')); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- Input -->
            <div class="bg-white border-t border-gray-200 p-4">
                <div class="flex items-end gap-2">
                    <label class="h-12 w-12 rounded-full bg-gray-100 grid place-items-center text-gray-600 shadow-sm hover:bg-gray-200 transition cursor-pointer" title="Attach file">
                        <input type="file" wire:model="attachment" class="hidden" />
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48" />
                        </svg>
                    </label>
                    <textarea
                        wire:model="body"
                        rows="1"
                        class="flex-1 rounded-2xl border-0 bg-gray-100 px-4 py-3 text-sm resize-none focus:ring-2 focus:ring-emerald-500"
                        placeholder="Type a message..."
                        wire:keydown.enter.prevent="send"
                    ></textarea>
                    <button type="button" wire:click="send" class="h-12 w-12 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 grid place-items-center text-white shadow-lg hover:shadow-xl transition-shadow duration-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attachment): ?>
                    <div class="mt-2 flex items-center justify-between gap-2 rounded-xl bg-gray-50 px-3 py-2 text-xs text-gray-700 ring-1 ring-gray-200">
                        <div class="truncate"><span class="font-bold">Attached:</span> <?php echo e($attachment->getClientOriginalName()); ?></div>
                        <button type="button" class="text-red-600 font-bold" wire:click="$set('attachment', null)">Remove</button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="mt-2 text-xs text-red-600"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['attachment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="mt-2 text-xs text-red-600"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
</div>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/messages/index.blade.php ENDPATH**/ ?>