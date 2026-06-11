// Initialize Lucide icons when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Re-initialize Lucide icons if library is loaded
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

// Auto-hide flash messages after 5 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert-slide');
    alerts.forEach(alert => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    });
}, 5000);

// Common AJAX function
async function makeRequest(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                ...options.headers
            },
            ...options
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error('Request failed:', error);
        throw error;
    }
}

function updateNotificationCount() {
    const list = document.getElementById('notificationList');
    const totalCountSpan = document.querySelector('#notificationDropdown .uppercase.tracking-widest');
    const badge = document.querySelector('#notificationBell span');
    
    const unreadItems = list ? list.querySelectorAll('.notification-item.unread-notif') : [];
    const count = unreadItems.length;
    
    const stockCount = [...unreadItems].filter(i => i.dataset.type === 'low_stock').length;
    const systemCount = count - stockCount;
    
    if (totalCountSpan) totalCountSpan.textContent = count + ' item(s)';
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    // Update System Badge in Tabs
    const systemBadge = document.querySelector('#btn-filter-system span');
    if (systemBadge) {
        if (systemCount > 0) {
            systemBadge.textContent = systemCount;
            systemBadge.classList.remove('hidden');
        } else {
            systemBadge.classList.add('hidden');
        }
    }

    // Update Parts Badge in Tabs
    const partsBadge = document.querySelector('#btn-filter-parts span');
    if (partsBadge) {
        if (stockCount > 0) {
            partsBadge.textContent = stockCount;
            partsBadge.classList.remove('hidden');
        } else {
            partsBadge.classList.add('hidden');
        }
    }

    // Sound logic — play only when count INCREASES (new notifications)
    const storedCount = sessionStorage.getItem('notif_count');
    const storedCountNum = storedCount !== null ? parseInt(storedCount, 10) : -1;
    
    if (count > 0 && (storedCountNum === -1 || count > storedCountNum)) {
        const assetUrlMeta = document.querySelector('meta[name="asset-url"]');
        const assetBase = assetUrlMeta ? assetUrlMeta.getAttribute('content') : '/';
        const audio = new Audio(assetBase + 'assets/sounds/notification.mp3');
        audio.play().catch(e => console.log('Audio autoplay prevented'));
    }
    
    // Always store the current count
    sessionStorage.setItem('notif_count', count.toString());
}

function dismissNotification(button) {
    if (typeof event !== 'undefined') event.stopPropagation();
    const item = button.closest('.notification-item');
    if (!item) return;
    const type = item.getAttribute('data-type');
    const id = item.getAttribute('data-notif-id') || item.getAttribute('data-id');

    // Add to read_notifs local storage & cookie so it doesn't show up in badge count or during polls
    if (id) {
        let readNotifs = JSON.parse(localStorage.getItem('read_notifs') || '{}');
        if (Array.isArray(readNotifs)) readNotifs = {};
        readNotifs[String(id)] = Date.now();
        localStorage.setItem('read_notifs', JSON.stringify(readNotifs));
        document.cookie = "read_notifs=" + encodeURIComponent(JSON.stringify(readNotifs)) + "; path=/; max-age=" + (30 * 24 * 60 * 60);
    }

    // Animate out
    item.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
    item.style.opacity = '0';
    item.style.transform = 'translateX(10px)';
    setTimeout(() => { 
        item.remove(); 
        updateNotificationCount(); 
    }, 200);

    // Call backend for DB-backed alerts if id is numeric
    if (id && !isNaN(id)) {
        fetch('/notifications/dismiss', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: 'id=' + encodeURIComponent(id)
        }).catch(err => console.error('Failed to dismiss alert:', err));
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const bell = document.getElementById('notificationBell');
    const dropdown = document.getElementById('notificationDropdown');
    if (!bell || !dropdown) return;

    bell.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });

    dropdown.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    document.addEventListener('click', () => {
        if (!dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
        }
    });

    updateNotificationCount();
});

// ==========================================================================
// 📡 OFFLINE SYNC & RETRY QUEUE ENGINE
// ==========================================================================

const OfflineSyncEngine = {
    QUEUE_KEY: 'eurotaxi_offline_api_queue',

    init() {
        this.registerServiceWorker();
        this.bindEvents();
        this.checkInitialConnection();
    },

    registerServiceWorker() {
        const isCapacitor = (typeof window !== 'undefined' && window.Capacitor) || 
                            navigator.userAgent.includes('Capacitor') || 
                            navigator.userAgent.includes('Android');

        if ('serviceWorker' in navigator) {
            if (isCapacitor) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js?v=2.8')
                        .then(reg => console.log('Service Worker registered successfully:', reg.scope))
                        .catch(err => console.error('Service Worker registration failed:', err));
                });
            } else {
                // Programmatically unregister any legacy service workers on desktop to restore pure network fetching!
                navigator.serviceWorker.getRegistrations().then(registrations => {
                    let unregisteredAny = false;
                    for (let registration of registrations) {
                        registration.unregister().then(success => {
                            if (success) {
                                console.log('Stale desktop Service Worker successfully purged.');
                                unregisteredAny = true;
                            }
                        });
                    }
                    if (unregisteredAny) {
                        setTimeout(() => window.location.reload(), 300);
                    }
                });
            }
        }
    },

    bindEvents() {
        window.addEventListener('online', () => this.handleOnlineStatusChange(true));
        window.addEventListener('offline', () => this.handleOnlineStatusChange(false));
    },

    checkInitialConnection() {
        this.verifyRealOffline().then(isReallyOffline => {
            if (isReallyOffline) {
                this.showOfflineBanner();
            }
        });
    },

    handleOnlineStatusChange(isOnline) {
        if (isOnline) {
            this.hideOfflineBanner();
            this.processOfflineQueue();
        } else {
            this.verifyRealOffline().then(isReallyOffline => {
                if (isReallyOffline) {
                    this.showOfflineBanner();
                }
            });
        }
    },

    async verifyRealOffline() {
        if (!navigator.onLine) return true;
        try {
            // Confirm actual internet by sending a micro-HEAD ping to the server
            const res = await fetch('/favicon_euro_transparent.png?ping=' + Date.now(), { method: 'HEAD', cache: 'no-store' });
            return !res.ok;
        } catch (e) {
            return true;
        }
    },

    showOfflineBanner() {
        let banner = document.getElementById('offline-status-banner');
        if (!banner) {
            banner = document.createElement('div');
            banner.id = 'offline-status-banner';
            banner.innerHTML = `
                <div class="fixed top-0 left-0 right-0 z-50 bg-red-600 text-white text-xs font-semibold py-2 px-4 text-center flex items-center justify-center gap-2 shadow-md animate-bounce">
                    <i data-lucide="wifi-off" class="w-4 h-4"></i>
                    <span>Naka-offline ka ngayon. Isasave muna namin ang iyong ginagawa at awtomatikong isasabay kapag may internet na.</span>
                </div>
            `;
            document.body.appendChild(banner);
            if (window.lucide) window.lucide.createIcons();
        }
    },

    hideOfflineBanner() {
        const banner = document.getElementById('offline-status-banner');
        if (banner) {
            banner.remove();
        }
    },

    enqueueRequest(url, method, payload) {
        const queue = this.getQueue();
        queue.push({
            id: Date.now() + Math.random().toString(36).substr(2, 5),
            url: url,
            method: method,
            payload: payload,
            timestamp: new Date().toISOString()
        });
        localStorage.setItem(this.QUEUE_KEY, JSON.stringify(queue));
        this.showToast('Naka-save offline ang iyong update!');
    },

    getQueue() {
        const queueRaw = localStorage.getItem(this.QUEUE_KEY);
        return queueRaw ? JSON.parse(queueRaw) : [];
    },

    processOfflineQueue() {
        const queue = this.getQueue();
        if (queue.length === 0) return;

        this.showToast('I-ni-isabay ang iyong mga offline updates...');

        const promises = queue.map(req => {
            return fetch(req.url, {
                method: req.method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(req.payload)
            }).then(res => {
                if (res.ok) {
                    return req.id;
                }
                return null;
            }).catch(err => {
                console.error('Failed to sync queued request:', err);
                return null;
            });
        });

        Promise.all(promises).then(completedIds => {
            const successfulIds = completedIds.filter(id => id !== null);
            let remainingQueue = this.getQueue().filter(req => !successfulIds.includes(req.id));
            
            localStorage.setItem(this.QUEUE_KEY, JSON.stringify(remainingQueue));

            if (successfulIds.length > 0) {
                this.showToast('Matagumpay na na-sync ang lahat ng offline updates mo! 🚀');
                setTimeout(() => window.location.reload(), 1500);
            }
        });
    },

    showToast(message) {
        const toast = document.createElement('div');
        toast.className = "fixed bottom-20 left-1/2 transform -translate-x-1/2 z-50 bg-slate-900 text-white text-xs font-semibold py-3 px-6 rounded-full shadow-2xl flex items-center gap-2";
        toast.innerHTML = `<span>${message}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    OfflineSyncEngine.init();
});
