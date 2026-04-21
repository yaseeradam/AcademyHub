<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<script>document.documentElement.classList.remove('dark'); try { localStorage.removeItem('darkMode') } catch(e){}</script>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />
    <title><?php echo e(config('myacademy.school_name', config('app.name', 'MyAcademy'))); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <style>
        body { font-family: 'Inter', 'Space Grotesk', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; }
        .nav-icon-box { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .sidebar-scroll::-webkit-scrollbar { width:4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background:transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background:#e2e8f0; border-radius:99px; }
    </style>
</head>
<body class="h-full bg-[#f5f6fa] text-slate-900">

<?php
$hasCbt            = true;
$appMode           = (string) config('myacademy.mode', 'full');
$cbtLocked         = false;
$user              = auth()->user();
$schoolLogo        = config('myacademy.school_logo');
$schoolName        = config('myacademy.school_name', config('app.name', 'MyAcademy'));
$userInitial       = mb_strtoupper(mb_substr($user?->name ?? 'U', 0, 1));

// Role-aware accent colour (used for active state)
$accent = match($user?->role) {
    'admin'   => 'violet',
    'bursar'  => 'emerald',
    'teacher' => 'sky',
    'parent'  => 'pink',
    default   => 'violet',
};
$activeBg  = "bg-{$accent}-500";
$activeShadow = "shadow-{$accent}-200";
?>

<div id="app" class="flex min-h-screen">

    
    <div id="mobileSidebarOverlay" class="fixed inset-0 z-40 hidden bg-black/40 backdrop-blur-sm lg:hidden"></div>

    
    <aside id="mobileSidebar"
           class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-[#f5f6fa] transition-transform duration-300 lg:hidden">

        <div class="flex items-center justify-end px-4 pt-4">
            <button id="closeMobileSidebar"
                    class="rounded-xl bg-white p-2 text-slate-400 shadow-sm hover:text-slate-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        
        <div class="mx-3 mb-2 rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl bg-violet-50 ring-2 ring-violet-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($schoolLogo): ?>
                        <img src="<?php echo e(asset('uploads/'.str_replace('\\','/',$schoolLogo))); ?>" alt="Logo" class="h-full w-full object-contain p-1"/>
                    <?php else: ?>
                        <svg class="h-7 w-7 text-violet-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3L1 9l11 6 9-4.91V17a2 2 0 01-1.1 1.79l-7.4 3.7a2 2 0 01-1.8 0l-7.4-3.7A2 2 0 012 17V9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V9"/>
                        </svg>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="min-w-0">
                    <div class="truncate text-sm font-extrabold leading-tight text-slate-900"><?php echo e($schoolName); ?></div>
                    <div class="mt-0.5 text-[11px] font-semibold text-violet-500">Smart Learning System</div>
                </div>
            </div>
        </div>

        <div class="px-5 pb-1 pt-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Main Menu</span>
        </div>

        <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 pb-3 space-y-0.5">
            <?php echo $__env->make('layouts.partials.app-nav', ['mobile' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </nav>

        <div class="p-3">
            <form method="POST" action="<?php echo e(route('logout')); ?>" id="mobileLogoutForm">
                <?php echo csrf_field(); ?>
                <button type="button" onclick="doLogout('mobileLogoutForm')"
                        class="w-full flex items-center gap-3 rounded-2xl bg-slate-800 px-4 py-3.5 hover:bg-slate-900 transition-all">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-red-500 shadow-md">
                        <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-extrabold text-white">Logout</div>
                        <div class="text-[11px] font-medium text-slate-400">See you later 👋</div>
                    </div>
                </button>
            </form>
        </div>
    </aside>

    
    <aside class="hidden w-72 flex-shrink-0 flex-col bg-[#f5f6fa] lg:flex">

        
        <div class="mx-3 mt-4 mb-2 rounded-2xl bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl bg-violet-50 ring-2 ring-violet-100">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($schoolLogo): ?>
                        <img src="<?php echo e(asset('uploads/'.str_replace('\\','/',$schoolLogo))); ?>" alt="Logo" class="h-full w-full object-contain p-1"/>
                    <?php else: ?>
                        <svg class="h-7 w-7 text-violet-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3L1 9l11 6 9-4.91V17a2 2 0 01-1.1 1.79l-7.4 3.7a2 2 0 01-1.8 0l-7.4-3.7A2 2 0 012 17V9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V9"/>
                        </svg>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="min-w-0">
                    <div class="truncate text-sm font-extrabold leading-tight text-slate-900"><?php echo e($schoolName); ?></div>
                    <div class="mt-0.5 text-[11px] font-semibold text-violet-500">Smart Learning System</div>
                </div>
            </div>
        </div>

        <div class="px-5 pb-1 pt-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Main Menu</span>
        </div>

        <nav class="flex-1 overflow-y-auto sidebar-scroll px-3 pb-3 space-y-0.5">
            <?php echo $__env->make('layouts.partials.app-nav', ['mobile' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </nav>

        <div class="p-3">
            <form method="POST" action="<?php echo e(route('logout')); ?>" id="logoutForm">
                <?php echo csrf_field(); ?>
                <button type="button" onclick="doLogout('logoutForm')"
                        class="w-full flex items-center gap-3 rounded-2xl bg-slate-800 px-4 py-3.5 hover:bg-slate-900 transition-all">
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-red-500 shadow-md">
                        <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-extrabold text-white">Logout</div>
                        <div class="text-[11px] font-medium text-slate-400">See you later 👋</div>
                    </div>
                </button>
            </form>
        </div>
    </aside>

    
    <div class="flex flex-1 min-w-0 flex-col">

        
        <header class="sticky top-0 z-10 flex h-[72px] items-center justify-between gap-4 border-b border-slate-200/60 bg-white px-6 shadow-sm">

            <div class="flex items-center gap-4">
                <button id="openMobileSidebar"
                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 lg:hidden">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div>
                    <h1 class="text-lg font-extrabold tracking-tight text-slate-900"><?php echo e($schoolName); ?></h1>
                    <p class="text-xs font-medium text-slate-400"><?php echo e(now()->format('l, F j, Y')); ?></p>
                </div>
            </div>

            <div class="flex items-center gap-3">

                
                <div class="relative flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 transition-colors">
                    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('notifications.bell', []);

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2859434850-0', null);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                </div>

                
                <a href="<?php echo e(route('profile')); ?>"
                   class="flex items-center gap-2.5 rounded-full border border-slate-200 bg-white py-1 pl-1 pr-4 shadow-sm hover:shadow-md transition-all">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user?->profile_photo_url): ?>
                        <img src="<?php echo e($user->profile_photo_url); ?>" alt="<?php echo e($user->name); ?>"
                             class="h-8 w-8 flex-shrink-0 rounded-full object-cover"/>
                    <?php else: ?>
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-violet-500 text-white shadow-sm">
                            <span class="text-sm font-extrabold leading-none"><?php echo e($userInitial); ?></span>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="hidden leading-tight sm:block">
                        <div class="text-sm font-bold text-slate-800"><?php echo e($user?->name ?? 'User'); ?></div>
                        <div class="text-[11px] font-semibold text-slate-400"><?php echo e(ucfirst($user?->role ?? 'user')); ?></div>
                    </div>
                </a>

                
                <button type="button" onclick="doLogout('logoutForm')"
                        class="flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-slate-800 transition-all">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="hidden sm:inline">Logout</span>
                </button>

            </div>
        </header>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($subscriptionDueDate) && $user?->role === 'admin'): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($subscriptionIsPastDue && $subscriptionDaysPastDue <= 14): ?>
                <div class="fixed inset-x-0 bottom-0 z-50 p-3">
                    <div class="mx-auto max-w-4xl rounded-2xl bg-red-600 px-5 py-3 shadow-xl flex items-center justify-between gap-4">
                        <p class="text-sm font-bold text-white">Subscription expired — edit features are locked. <a href="<?php echo e(route('billing.index')); ?>" class="underline">Renew now</a></p>
                    </div>
                </div>
                <style>#mainContent main input,#mainContent main select,#mainContent main textarea,#mainContent main button:not(.allow-billing){pointer-events:none!important;opacity:.6!important}</style>
            <?php elseif(!$subscriptionIsPastDue && $subscriptionDaysUntilDue <= 7): ?>
                <div class="fixed inset-x-0 bottom-0 z-50 p-3">
                    <div class="mx-auto max-w-4xl rounded-2xl bg-amber-500 px-5 py-3 shadow-xl flex items-center justify-between gap-4">
                        <p class="text-sm font-bold text-white">Subscription expires in <?php echo e($subscriptionDaysUntilDue); ?> days. <a href="<?php echo e(route('billing.index')); ?>" class="underline">Renew now</a></p>
                        <button onclick="this.closest('.fixed').remove()" class="text-white/80 hover:text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <main class="flex-1 overflow-y-auto px-6 py-6">
            <?php echo $__env->yieldContent('content'); ?>
            <?php echo e($slot ?? ''); ?>

        </main>

        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('global-modal', []);

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2859434850-1', null);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    </div>

</div>

<?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

<?php echo $__env->yieldPushContent('scripts'); ?>
<?php if (isset($component)) { $__componentOriginale5bc9b34dd139a393f71cdc403b71855 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5bc9b34dd139a393f71cdc403b71855 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.notifications','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('notifications'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5bc9b34dd139a393f71cdc403b71855)): ?>
<?php $attributes = $__attributesOriginale5bc9b34dd139a393f71cdc403b71855; ?>
<?php unset($__attributesOriginale5bc9b34dd139a393f71cdc403b71855); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5bc9b34dd139a393f71cdc403b71855)): ?>
<?php $component = $__componentOriginale5bc9b34dd139a393f71cdc403b71855; ?>
<?php unset($__componentOriginale5bc9b34dd139a393f71cdc403b71855); ?>
<?php endif; ?>

<script>
    // CSRF-safe logout
    function doLogout(formId = 'logoutForm') {
        fetch('/csrf-token', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(d => {
                if (d.token) {
                    document.querySelector('meta[name="csrf-token"]').setAttribute('content', d.token);
                    document.querySelectorAll('input[name="_token"]').forEach(el => el.value = d.token);
                }
            })
            .catch(() => {})
            .finally(() => document.getElementById(formId).submit());
    }

    // Mobile sidebar
    const mobileSidebar = document.getElementById('mobileSidebar');
    const mobileOverlay = document.getElementById('mobileSidebarOverlay');
    document.getElementById('openMobileSidebar')?.addEventListener('click', () => {
        mobileSidebar.classList.remove('-translate-x-full');
        mobileOverlay.classList.remove('hidden');
    });
    const closeMobile = () => {
        mobileSidebar.classList.add('-translate-x-full');
        mobileOverlay.classList.add('hidden');
    };
    document.getElementById('closeMobileSidebar')?.addEventListener('click', closeMobile);
    mobileOverlay?.addEventListener('click', closeMobile);

    // Push notifications
    if ('serviceWorker' in navigator && 'PushManager' in window) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }
    function sendBrowserNotification(title, body, url = '/') {
        if ('Notification' in window && Notification.permission === 'granted') {
            const n = new Notification(title, { body, icon: '/favicon.ico' });
            n.onclick = () => { window.focus(); if (url !== '/') window.location.href = url; n.close(); };
        }
    }
    window.addEventListener('load', () => {
        if ('Notification' in window && Notification.permission === 'default') {
            setTimeout(() => Notification.requestPermission(), 3000);
        }
    });
    document.addEventListener('livewire:init', () => {
        Livewire.on('browser-notification', (event) => {
            const d = event[0] || event;
            sendBrowserNotification(d.title || 'MyAcademy', d.message || 'New notification', d.url || '/');
        });
    });
</script>


<div x-data="{ loading: false }"
     x-on:livewire:navigating.window="loading = true"
     x-on:livewire:navigated.window="loading = false"
     x-show="loading" style="display:none"
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/50 backdrop-blur-sm">
    <div class="h-12 w-12 animate-spin rounded-full border-4 border-slate-200 border-t-violet-500 shadow-lg"></div>
</div>

</body>
</html>
<?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/layouts/app.blade.php ENDPATH**/ ?>