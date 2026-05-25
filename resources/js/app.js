import './bootstrap';
import './loading';

// Mobile Sidebar — wrapped in a function so it can be re-called after wire:navigate
function initMobileSidebar() {
    const mobileSidebar = document.getElementById('mobileSidebar');
    const mobileSidebarOverlay = document.getElementById('mobileSidebarOverlay');
    const openMobileSidebar = document.getElementById('openMobileSidebar');
    const closeMobileSidebar = document.getElementById('closeMobileSidebar');

    if (!mobileSidebar) return;

    function openSidebar() {
        mobileSidebar.classList.remove('-translate-x-full');
        if (mobileSidebarOverlay) mobileSidebarOverlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        mobileSidebar.classList.add('-translate-x-full');
        if (mobileSidebarOverlay) mobileSidebarOverlay.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Remove old listeners before re-attaching to avoid duplicates
    const newOpen  = openMobileSidebar?.cloneNode(true);
    const newClose = closeMobileSidebar?.cloneNode(true);
    const newOverlay = mobileSidebarOverlay?.cloneNode(true);

    if (openMobileSidebar && newOpen)   { openMobileSidebar.replaceWith(newOpen);   newOpen.addEventListener('click', openSidebar); }
    if (closeMobileSidebar && newClose) { closeMobileSidebar.replaceWith(newClose); newClose.addEventListener('click', closeSidebar); }
    if (mobileSidebarOverlay && newOverlay) { mobileSidebarOverlay.replaceWith(newOverlay); newOverlay.addEventListener('click', closeSidebar); }
}

document.addEventListener('DOMContentLoaded', initMobileSidebar);
document.addEventListener('livewire:navigated', initMobileSidebar);

// Dark Mode — runs immediately (no DOM dependency)
const html = document.documentElement;
const currentTheme = localStorage.getItem('theme') || 'light';
if (currentTheme === 'dark') {
    html.classList.add('dark');
}

function initDarkMode() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            const theme = html.classList.contains('dark') ? 'dark' : 'light';
            localStorage.setItem('theme', theme);
            darkModeToggle.classList.add('animate-pulse-soft');
            setTimeout(() => darkModeToggle.classList.remove('animate-pulse-soft'), 500);
        });
    }

    // Card entrance animations
    const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    document.querySelectorAll('.card, .stat-card, [class*="card"]').forEach(card => observer.observe(card));
}

document.addEventListener('DOMContentLoaded', initDarkMode);
document.addEventListener('livewire:navigated', initDarkMode);

// Notification system
window.showNotification = function(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-6 z-50 p-4 rounded-lg shadow-lg animate-slide-up ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'warning' ? 'bg-orange-500 text-white' :
        'bg-brand-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center gap-3">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                ${type === 'success' ? '<path d="M20 6 9 17l-5-5" />' :
                  type === 'error' ? '<path d="M6 18L18 6M6 6l12 12" />' :
                  type === 'warning' ? '<path d="M12 9v2m0 4h.01" />' :
                  '<path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />'}
            </svg>
            <span class="text-sm font-medium">${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
};

// Modal alert (center popup)
function ensureAlertModal() {
    let overlay = document.getElementById('alertModalOverlay');
    if (overlay) return overlay;

    overlay = document.createElement('div');
    overlay.id = 'alertModalOverlay';
    overlay.className = 'fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 p-4';
    overlay.innerHTML = `
        <div id="alertModalPanel" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-inset ring-gray-200">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div id="alertModalTitle" class="text-sm font-semibold text-gray-900">Alert</div>
                    <div id="alertModalMessage" class="mt-2 text-sm text-gray-700"></div>
                </div>
                <button id="alertModalClose" type="button" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100" aria-label="Close">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button id="alertModalOk" type="button" class="btn-primary">OK</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    const close = () => {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
    };

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) close();
    });

    overlay.querySelector('#alertModalClose')?.addEventListener('click', close);
    overlay.querySelector('#alertModalOk')?.addEventListener('click', close);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') close();
    });

    return overlay;
}

window.showAlertModal = function(message, type = 'info', options = {}) {
    const overlay = ensureAlertModal();
    const titleEl = overlay.querySelector('#alertModalTitle');
    const msgEl = overlay.querySelector('#alertModalMessage');
    const panel = overlay.querySelector('#alertModalPanel');

    const titles = {
        success: 'Success',
        error: 'Error',
        warning: 'Warning',
        info: 'Alert',
    };

    const rings = {
        success: 'ring-green-200',
        error: 'ring-red-200',
        warning: 'ring-orange-200',
        info: 'ring-brand-200',
    };

    const safeType = titles[type] ? type : 'info';

    if (titleEl) titleEl.textContent = options.title || titles[safeType];
    if (msgEl) msgEl.textContent = message || 'Done.';

    if (panel) {
        panel.classList.remove('ring-green-200', 'ring-red-200', 'ring-orange-200', 'ring-brand-200');
        panel.classList.add(rings[safeType]);
    }

    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
};

// Livewire event bridge — wire up once on livewire:init
document.addEventListener('livewire:init', () => {
    if (typeof window.Livewire === 'undefined') return;

    window.Livewire.on('alert', (eventData = {}) => {
        const payload = Array.isArray(eventData) ? (eventData[0] || {}) : eventData;
        const message = typeof payload === 'string' ? payload : (payload.message || 'Done.');
        const type = typeof payload === 'object' && payload.type ? payload.type : 'info';
        const title = typeof payload === 'object' && payload.title ? payload.title : undefined;
        window.showAlertModal(message, type, { title });
    });

    // Message unread sound
    let audioContext;
    let audioUnlocked = false;

    const unlockAudio = () => {
        audioUnlocked = true;
        document.removeEventListener('pointerdown', unlockAudio);
        document.removeEventListener('keydown', unlockAudio);
    };

    document.addEventListener('pointerdown', unlockAudio, { once: true });
    document.addEventListener('keydown', unlockAudio, { once: true });

    const playBeep = () => {
        if (!audioUnlocked) return;
        try {
            audioContext = audioContext || new (window.AudioContext || window.webkitAudioContext)();
            const ctx = audioContext;
            const oscillator = ctx.createOscillator();
            const gain = ctx.createGain();
            oscillator.type = 'sine';
            oscillator.frequency.value = 880;
            const now = ctx.currentTime;
            gain.gain.setValueAtTime(0.0001, now);
            gain.gain.exponentialRampToValueAtTime(0.12, now + 0.01);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.18);
            oscillator.connect(gain);
            gain.connect(ctx.destination);
            oscillator.start(now);
            oscillator.stop(now + 0.2);
        } catch (e) { /* ignore */ }
    };

    window.Livewire.on('messages-unread', () => playBeep());
});

// Bulk operations
window.selectAll = function(checkbox) {
    const checkboxes = document.querySelectorAll('.checkbox-custom');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
};

window.getSelectedIds = function() {
    const checkboxes = document.querySelectorAll('.checkbox-custom:checked');
    return Array.from(checkboxes).map(cb => cb.value);
};

window.exportData = function(type, format = 'csv') {
    showNotification(`Exporting ${type} as ${format.toUpperCase()}...`, 'info');
    setTimeout(() => {
        showNotification(`${type} exported successfully!`, 'success');
    }, 1500);
};

