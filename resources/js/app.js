import './bootstrap.js';

// Initialize Lucide icons
import * as lucide from 'lucide';
window.lucide = lucide;

// Initialize icons when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
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
