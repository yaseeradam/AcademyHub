<!-- Notification Container -->
<div id="notificationContainer" class="fixed top-20 right-6 z-50 space-y-3 max-w-sm"></div>

<style>
@keyframes slideIn {
    from {
        transform: translateX(120%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
@keyframes slideOut {
    from {
        transform: translateX(0) scale(1);
        opacity: 1;
    }
    to {
        transform: translateX(120%) scale(0.95);
        opacity: 0;
    }
}
.notification-enter {
    animation: slideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.notification-exit {
    animation: slideOut 0.3s cubic-bezier(0.4, 0, 1, 1);
}
</style>

<script>
// Enhanced notification system
window.showNotification = function(message, type = 'info', options = {}) {
    const container = document.getElementById('notificationContainer');
    if (!container) return;
    
    const notification = document.createElement('div');
    const id = 'notification-' + Date.now();
    notification.id = id;
    
    const icons = {
        success: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        error: '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        warning: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />',
        info: '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />'
    };
    
    const gradients = {
        success: 'bg-gradient-to-r from-emerald-500 to-teal-500',
        error: 'bg-gradient-to-r from-rose-500 to-red-500',
        warning: 'bg-gradient-to-r from-amber-500 to-orange-500',
        info: 'bg-gradient-to-r from-blue-500 to-indigo-500'
    };
    
    notification.className = `notification-enter p-4 rounded-2xl shadow-2xl text-white ${gradients[type]} backdrop-blur-sm border border-white/20`;
    notification.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    ${icons[type]}
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold leading-snug">${message}</p>
                ${options.action ? `<button class="mt-2 text-xs font-medium underline hover:no-underline opacity-90 hover:opacity-100 transition-opacity" onclick="${options.action}">${options.actionText || 'View'}</button>` : ''}
            </div>
            <button onclick="closeNotification('${id}')" class="flex-shrink-0 ml-2 opacity-70 hover:opacity-100 transition-opacity rounded-lg hover:bg-white/10 p-1">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    `;
    
    container.appendChild(notification);
    
    // Auto-remove after duration
    const duration = options.duration || (type === 'error' ? 5000 : 3500);
    setTimeout(() => {
        closeNotification(id);
    }, duration);
    
    return id;
};

window.closeNotification = function(id) {
    const notification = document.getElementById(id);
    if (notification) {
        notification.classList.remove('notification-enter');
        notification.classList.add('notification-exit');
        setTimeout(() => notification.remove(), 300);
    }
};

// Show progress notification
window.showProgressNotification = function(message, progress = 0) {
    const id = showNotification(message, 'info', { duration: 0 });
    const notification = document.getElementById(id);
    
    if (notification) {
        const progressBar = document.createElement('div');
        progressBar.className = 'mt-2.5 bg-white/20 rounded-full h-1.5 overflow-hidden';
        progressBar.innerHTML = `<div class="bg-white rounded-full h-full transition-all duration-500 ease-out" style="width: ${progress}%"></div>`;
        notification.querySelector('.flex-1').appendChild(progressBar);
    }
    
    return {
        id,
        updateProgress: (newProgress) => {
            const bar = notification?.querySelector('.bg-white');
            if (bar) bar.style.width = `${newProgress}%`;
            if (newProgress >= 100) {
                setTimeout(() => closeNotification(id), 1200);
            }
        }
    };
};

// Show success notification with action
window.showSuccessNotification = function(message, action = null, actionText = null) {
    return showNotification(message, 'success', {
        action,
        actionText,
        duration: 4000
    });
};

// Show error notification
window.showErrorNotification = function(message) {
    return showNotification(message, 'error', {
        duration: 5000
    });
};

// Show warning notification
window.showWarningNotification = function(message) {
    return showNotification(message, 'warning', {
        duration: 4000
    });
};
</script><?php /**PATH C:\laragon\www\myacademy-laravel\resources\views/components/notifications.blade.php ENDPATH**/ ?>