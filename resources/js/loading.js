// Global loading indicator for Livewire 3
// Only shows the loader when the user EXPLICITLY clicks a button or submits a form.
// Background requests (polling, wire:model.live, $refresh, etc.) will NOT trigger it.

// ── User-action tracking ──────────────────────────────────────────────
// We set a flag whenever the user clicks or submits something.
// The flag is cleared after a short window. Livewire requests that land
// inside this window AND contain an explicit method call are considered
// "user-initiated" and will show the loader; everything else is silent.
let _userActionTimestamp = 0;
const USER_ACTION_WINDOW_MS = 150; // ms after click/submit to accept as user-initiated

function markUserAction() {
    _userActionTimestamp = Date.now();
}

// Listen on the capture phase so we catch the event before Livewire does
document.addEventListener('click', markUserAction, true);
document.addEventListener('submit', markUserAction, true);
document.addEventListener('keydown', (e) => {
    // Enter key on buttons / inside forms counts as an action
    if (e.key === 'Enter') markUserAction();
}, true);

document.addEventListener('livewire:init', () => {
    let loadingTimer = null;
    let safetyTimer = null;
    let activeUserRequests = 0;

    /**
     * Methods that are considered "background" – they do NOT warrant a loader
     * even if they happen to coincide with the user-action window.
     */
    const BACKGROUND_METHODS = new Set([
        '$refresh',
        'tick',
        'refreshCount',
    ]);

    /**
     * Decide whether a Livewire request should show the global loader.
     * Returns TRUE only for explicit user-initiated actions.
     */
    function shouldShowLoader({ options, payload }) {
        try {
            // ── 1. Polling header ──────────────────────────────────
            const headers = options?.headers || {};
            if (
                headers['X-Livewire-Is-Polling'] === true ||
                headers['X-Livewire-Is-Polling'] === 'true'
            ) {
                return false;
            }

            // ── 2. Parse payload ───────────────────────────────────
            const raw = payload
                ? typeof payload === 'string'
                    ? JSON.parse(payload)
                    : payload
                : null;
            const components = raw?.components || [];

            // ── 3. Collect every method being called ───────────────
            const allCalls = components.flatMap(c => c?.calls || []);
            const methods = allCalls.map(c => c?.method).filter(Boolean);

            // If there are NO explicit calls (e.g. a pure model sync / dehydrate),
            // this is a background request – skip loader.
            if (methods.length === 0) {
                return false;
            }

            // If ALL methods are background-only, skip loader.
            if (methods.every(m => BACKGROUND_METHODS.has(m))) {
                return false;
            }

            // ── 4. Check if a recent user action triggered this ────
            const elapsed = Date.now() - _userActionTimestamp;
            if (elapsed > USER_ACTION_WINDOW_MS) {
                // No recent click/submit – this was triggered automatically
                // (e.g. wire:model.live, wire:change="$refresh", etc.)
                return false;
            }

            // Has explicit method calls AND was triggered by user action ✓
            return true;
        } catch (e) {
            // If we can't parse, don't show loader (safe default)
            return false;
        }
    }

    // ── Livewire request hook ─────────────────────────────────────────
    Livewire.hook('request', (requestData) => {
        const { succeed, fail } = requestData;

        if (!shouldShowLoader(requestData)) {
            // Still wire up succeed/fail to keep the hook chain intact
            succeed(() => {});
            fail(() => {});
            return;
        }

        activeUserRequests++;

        // Debounce: only show loader if request takes > 300ms
        // This prevents flashing on quick interactions
        if (!loadingTimer && activeUserRequests === 1) {
            loadingTimer = setTimeout(() => {
                if (activeUserRequests > 0) {
                    showGlobalLoading();

                    // Safety net: auto-hide after 15 seconds
                    clearTimeout(safetyTimer);
                    safetyTimer = setTimeout(() => {
                        activeUserRequests = 0;
                        clearTimeout(loadingTimer);
                        loadingTimer = null;
                        hideGlobalLoading();
                    }, 15000);
                }
            }, 300);
        }

        succeed(() => {
            activeUserRequests = Math.max(0, activeUserRequests - 1);
            if (activeUserRequests === 0) {
                clearTimeout(loadingTimer);
                loadingTimer = null;
                clearTimeout(safetyTimer);
                safetyTimer = null;
                hideGlobalLoading();
            }
        });

        fail(({ status }) => {
            activeUserRequests = Math.max(0, activeUserRequests - 1);
            if (activeUserRequests === 0) {
                clearTimeout(loadingTimer);
                loadingTimer = null;
                clearTimeout(safetyTimer);
                safetyTimer = null;
                hideGlobalLoading();
            }

            // Handle different error types
            if (status === 419) {
                window.showError?.('Your session has expired. Please refresh the page.');
            } else if (status === 403) {
                window.showError?.('You do not have permission to perform this action.');
            } else if (status === 500) {
                window.showError?.('A server error occurred. Please try again or contact support.');
            }
        });
    });

    // Safety net: hide loader on unhandled JS errors
    window.addEventListener('error', () => {
        activeUserRequests = 0;
        clearTimeout(loadingTimer);
        loadingTimer = null;
        clearTimeout(safetyTimer);
        safetyTimer = null;
        hideGlobalLoading();
    });
});

// ── Global loading indicator element management ──────────────────────
function showGlobalLoading() {
    let loader = document.getElementById('global-loader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'global-loader';
        loader.className = 'fixed top-24 right-8 z-[100] rounded-2xl bg-white/90 backdrop-blur-md shadow-2xl border border-slate-200/50 p-4 transition-all duration-300';
        loader.innerHTML = `
            <div class="flex items-center gap-4">
                <div class="relative h-7 w-7">
                    <div class="absolute inset-0 rounded-full border-4 border-amber-100"></div>
                    <div class="absolute inset-0 rounded-full border-4 border-amber-500 border-t-transparent animate-spin"></div>
                </div>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-slate-800">Processing</span>
                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Please wait...</span>
                </div>
            </div>
        `;
        document.body.appendChild(loader);
    }
    loader.style.display = 'block';
    // Trigger reflow before transition
    loader.offsetHeight;
    loader.style.opacity = '1';
    loader.style.transform = 'translateX(0)';
}

function hideGlobalLoading() {
    const loader = document.getElementById('global-loader');
    if (loader) {
        loader.style.opacity = '0';
        loader.style.transform = 'translateX(20px)';
        setTimeout(() => {
            if (loader.style.opacity === '0') {
                loader.style.display = 'none';
            }
        }, 300);
    }
}

// Global error/success helpers that use the enhanced notification system
window.showError = function (message) {
    if (window.showNotification) {
        window.showNotification(message, 'error');
    } else {
        alert(message);
    }
};

window.showSuccess = function (message) {
    if (window.showNotification) {
        window.showNotification(message, 'success');
    } else {
        console.log('Success:', message);
    }
};

// Ensure animation styles are present
if (!document.getElementById('loading-animations')) {
    const style = document.createElement('style');
    style.id = 'loading-animations';
    style.textContent = `
        @keyframes slide-in-right {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-slide-in-right {
            animation: slide-in-right 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
    `;
    document.head.appendChild(style);
}

