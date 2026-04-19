<div class="relative" x-data="{ open: false }" x-on:click.outside="open = false" wire:poll.15s>

    
    <button
        x-on:click="open = !open"
        class="relative rounded-xl border border-gray-200/70 bg-white p-2 text-slate-500 shadow-sm hover:bg-slate-50 hover:shadow-md transition-all duration-200 group"
        aria-label="Notifications"
    >
        <svg class="h-6 w-6 transition-transform duration-200 group-hover:scale-110 <?php echo e($this->unreadCount > 0 ? 'animate-[wiggle_1s_ease-in-out_infinite]' : ''); ?>"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
        </svg>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->unreadCount > 0): ?>
            <span class="absolute -right-1 -top-1 min-w-[18px] rounded-full bg-gradient-to-br from-green-500 to-emerald-600 px-1.5 py-0.5 text-[10px] font-bold text-white ring-2 ring-white shadow-lg animate-pulse">
                <?php echo e($this->unreadCount > 99 ? '99+' : $this->unreadCount); ?>

            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </button>

    
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        x-cloak
        class="absolute right-0 top-full z-50 mt-2 w-80 sm:w-96 rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 overflow-hidden"
        style="display:none;"
    >
        
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 bg-gradient-to-r from-green-50 to-emerald-50">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-slate-900">Notifications</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->unreadCount > 0): ?>
                    <span class="rounded-full bg-green-600 px-2 py-0.5 text-[10px] font-black text-white"><?php echo e($this->unreadCount); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="flex items-center gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->unreadCount > 0): ?>
                    <button wire:click="markAllRead" class="text-xs font-semibold text-green-600 hover:text-green-800">
                        Mark all read
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button x-on:click="open = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        
        <div class="max-h-[420px] overflow-y-auto divide-y divide-slate-50">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $isUnread = is_null($n->read_at);
                    $iconPath = match($n->type ?? '') {
                        'homework' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M16 13H8 M16 17H8',
                        'grade','result' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                        default    => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
                    };
                ?>
                <div class="flex items-start gap-3 px-4 py-3 <?php echo e($isUnread ? 'bg-green-50/40' : 'hover:bg-slate-50'); ?> transition-colors">
                    <div class="mt-0.5 flex-shrink-0 grid h-8 w-8 place-items-center rounded-xl <?php echo e($isUnread ? 'bg-green-600' : 'bg-slate-200'); ?>">
                        <svg class="h-4 w-4 <?php echo e($isUnread ? 'text-white' : 'text-slate-500'); ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($iconPath); ?>"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-xs font-bold text-slate-900 leading-snug"><?php echo e($n->title); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isUnread): ?>
                                <button wire:click="markRead(<?php echo e($n->id); ?>)" class="flex-shrink-0 text-[10px] text-green-600 hover:text-green-800 font-semibold whitespace-nowrap">
                                    Mark read
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($n->body): ?>
                            <p class="mt-0.5 text-xs text-slate-500 leading-snug line-clamp-2"><?php echo e($n->body); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span class="mt-1 block text-[10px] text-slate-400"><?php echo e($n->created_at?->diffForHumans()); ?></span>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="px-4 py-10 text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="mt-2 text-xs font-semibold text-slate-500">No notifications</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <style>
    @keyframes wiggle {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-10deg); }
        75% { transform: rotate(10deg); }
    }
    </style>
</div>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/livewire/student/notification-bell.blade.php ENDPATH**/ ?>