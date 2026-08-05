/**
 * Euro Taxi System - Full Global App-Like State Engine
 * Designed for seamless, SPA-like navigation and state preservation in mobile WebViews.
 */
(function() {
    const DEBUG = true;
    const pageCache = new Map();
    
    function log(message, ...args) {
        if (DEBUG) {
            console.log(`%c[StateEngine] ⚙️ ${message}`, 'color: #fbbf24; font-weight: bold; font-size: 11px;', ...args);
        }
    }
    
    // Cache Key generators for page pathnames
    function getScrollKey() {
        return `scroll_state_${window.location.pathname}`;
    }
    
    function getModalKey() {
        return `modal_state_${window.location.pathname}`;
    }
    
    function getFormValuesKey() {
        return `form_state_${window.location.pathname}`;
    }
    
    // ----------------------------------------------------
    // 1. DEEP SCROLL POSITION RESTORATION
    // ----------------------------------------------------
    function saveScrollPositions() {
        const scrollData = {};
        
        // A. Main Content Viewport area
        const appContentArea = document.getElementById('appContentArea');
        if (appContentArea) {
            scrollData['#appContentArea'] = appContentArea.scrollTop;
        }
        
        // B. Horizontal Scroll tables or vertical sub-boxes
        document.querySelectorAll('.overflow-x-auto, .overflow-y-auto').forEach((el, index) => {
            const selector = el.id ? `#${el.id}` : `scroll-index-${index}`;
            scrollData[selector] = {
                top: el.scrollTop,
                left: el.scrollLeft
            };
        });
        
        // C. Scrollable modals
        document.querySelectorAll('[id*="Modal"]').forEach((modal) => {
            if (!modal.classList.contains('hidden') && window.getComputedStyle(modal).display !== 'none') {
                scrollData[`#${modal.id}`] = modal.scrollTop;
            }
        });
        
        sessionStorage.setItem(getScrollKey(), JSON.stringify(scrollData));
        log('Cached scroll positions successfully.', scrollData);
    }
    
    function restoreScrollPositions() {
        const dataStr = sessionStorage.getItem(getScrollKey());
        if (!dataStr) return;
        
        try {
            const scrollData = JSON.parse(dataStr);
            log('Restoring scroll coordinates...', scrollData);
            
            // Restore Main Area Scroll
            const appContentArea = document.getElementById('appContentArea');
            if (appContentArea && scrollData['#appContentArea'] !== undefined) {
                appContentArea.scrollTop = scrollData['#appContentArea'];
            }
            
            // Restore Horizontal mobile container scrolls & custom boxes
            document.querySelectorAll('.overflow-x-auto, .overflow-y-auto').forEach((el, index) => {
                const selector = el.id ? `#${el.id}` : `scroll-index-${index}`;
                if (scrollData[selector]) {
                    el.scrollTop = scrollData[selector].top || 0;
                    el.scrollLeft = scrollData[selector].left || 0;
                }
            });
            
            // Restore modal scrolls
            document.querySelectorAll('[id*="Modal"]').forEach((modal) => {
                if (scrollData[`#${modal.id}`] !== undefined) {
                    modal.scrollTop = scrollData[`#${modal.id}`];
                }
            });
            
            log('Scroll positions restored successfully.');
        } catch (e) {
            console.error('[StateEngine] Scroll restoration error:', e);
        }
    }
    
    // ----------------------------------------------------
    // 2. MODAL CONTEXT RECOVERY
    // ----------------------------------------------------
    function saveModalContext() {
        const activeModals = [];
        const formStates = {};
        
        document.querySelectorAll('[id*="Modal"]').forEach((modal) => {
            const isVisible = !modal.classList.contains('hidden') && window.getComputedStyle(modal).display !== 'none';
            if (isVisible) {
                activeModals.push(modal.id);
                
                // Save form fields inside modal to restore if request fails or has validations
                const forms = modal.querySelectorAll('form');
                forms.forEach((form, fIndex) => {
                    const fields = {};
                    form.querySelectorAll('input, select, textarea').forEach((input) => {
                        if (!input.name || input.type === 'password' || input.type === 'hidden' || input.name === '_token') return;
                        
                        if (input.type === 'checkbox' || input.type === 'radio') {
                            fields[input.name] = input.checked;
                        } else {
                            fields[input.name] = input.value;
                        }
                    });
                    formStates[`${modal.id}_form_${fIndex}`] = fields;
                });
            }
        });
        
        if (activeModals.length > 0) {
            sessionStorage.setItem(getModalKey(), JSON.stringify(activeModals));
            sessionStorage.setItem(getFormValuesKey(), JSON.stringify(formStates));
            log('Cached modal states and values:', { activeModals, formStates });
        }
    }
    
    function restoreModalContext() {
        const modalsStr = sessionStorage.getItem(getModalKey());
        const formsStr = sessionStorage.getItem(getFormValuesKey());
        
        if (!modalsStr) return;
        
        try {
            const activeModals = JSON.parse(modalsStr);
            const formStates = JSON.parse(formsStr || '{}');
            
            activeModals.forEach((modalId) => {
                const modal = document.getElementById(modalId);
                if (modal) {
                    log(`Restoring open modal: ${modalId}`);
                    modal.classList.remove('hidden');
                    
                    // Recover fields values
                    const forms = modal.querySelectorAll('form');
                    forms.forEach((form, fIndex) => {
                        const savedFields = formStates[`${modalId}_form_${fIndex}`];
                        if (savedFields) {
                            Object.keys(savedFields).forEach((name) => {
                                const input = form.querySelector(`[name="${name}"]`);
                                if (input) {
                                    if (input.type === 'checkbox' || input.type === 'radio') {
                                        input.checked = savedFields[name];
                                    } else {
                                        input.value = savedFields[name];
                                    }
                                    input.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                            });
                        }
                    });
                    
                    // Trigger Lucide updates inside modal if any
                    if (window.lucide) window.lucide.createIcons();
                    
                    // validation focus trigger
                    const alertMsg = document.querySelector('.alert-slide') || document.querySelector('.text-red-800');
                    if (alertMsg) {
                        const errorField = modal.querySelector('input.border-red-500, select.border-red-500, textarea.border-red-500') || modal.querySelector('input, select');
                        if (errorField) {
                            setTimeout(() => errorField.focus(), 200);
                            log('Focused invalid input field inside restored modal:', errorField);
                        }
                    }
                }
            });
            
            // Clean up session values once restored
            sessionStorage.removeItem(getModalKey());
            sessionStorage.removeItem(getFormValuesKey());
        } catch (e) {
            console.error('[StateEngine] Modal context recovery failed:', e);
        }
    }
    
    // ----------------------------------------------------
    // 3. AJAX-FIRST DOM REPLACEMENT NAVIGATION & ACTION
    // ----------------------------------------------------
    async function performAjaxTransition(url, options = {}, transitionType = 'navigate') {
        log(`Executing AJAX-First ${transitionType} for: ${url}`);
        
        // 1. Caching states before swapping content
        saveScrollPositions();
        saveModalContext();
        
        // 2. Visual loader to suppress WebView white flashes
        document.body.classList.add('page-transitioning');
        
        try {
            // Check cache for GET navigation to make loading literally instant
            if (transitionType === 'navigate' && options.method === 'GET' && pageCache.has(url)) {
                const cachedData = pageCache.get(url);
                log('Rendering preloaded cached state...');
                renderPage(cachedData, url);
                
                // Pre-fetch fresh version in background asynchronously to ensure up-to-date data (Stale-While-Revalidate)
                fetchFreshInBackground(url);
                return;
            }
            
            const fetchOptions = {
                ...options,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    ...options.headers
                }
            };
            
            const response = await fetch(url, fetchOptions);
            
            if (!response.ok) {
                throw new Error(`Server returned status ${response.status}`);
            }
            
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const mainContent = doc.querySelector('#appMainContent');
            const pageTitle = doc.querySelector('title')?.textContent || '';
            
            if (mainContent) {
                const pageData = { mainContent, pageTitle, html };
                
                // Cache standard navigations
                if (transitionType === 'navigate' && options.method === 'GET') {
                    pageCache.set(url, pageData);
                }
                
                renderPage(pageData, response.url);
                
                // Synchronize History Bar states
                if (transitionType === 'navigate' || transitionType === 'submit') {
                    if (window.location.href !== response.url) {
                        history.pushState({}, '', response.url);
                    }
                }
            } else {
                log('No #appMainContent container inside returned HTML. Falling back to native browser reload.');
                window.location.href = url;
            }
        } catch (err) {
            console.error('[StateEngine] Transition failed. Falling back securely to full page load.', err);
            window.location.href = url;
        } finally {
            setTimeout(() => {
                document.body.classList.remove('page-transitioning');
                document.querySelectorAll('.nav-loading').forEach((el) => el.classList.remove('nav-loading'));
            }, 100);
        }
    }
    
    // Dynamic DOM page swapper
    function renderPage(pageData, finalUrl) {
        const appMainContent = document.querySelector('#appMainContent');
        if (!appMainContent) return;
        
        // Replace inner content
        appMainContent.innerHTML = pageData.mainContent.innerHTML;
        
        if (pageData.pageTitle) {
            document.title = pageData.pageTitle;
        }
        
        // Re-compile icons
        if (window.lucide) {
            window.lucide.createIcons();
        }
        
        // Execute dynamic scripts inside new layout context
        const scripts = appMainContent.querySelectorAll('script');
        scripts.forEach((script) => {
            const newScript = document.createElement('script');
            if (script.src) {
                newScript.src = script.src;
            } else {
                newScript.textContent = script.textContent;
            }
            document.head.appendChild(newScript);
        });
        
        // Fire dynamic page reload bindings
        document.dispatchEvent(new CustomEvent('page:loaded', { detail: { url: finalUrl } }));
        
        // Restore cached scroll states, modal forms, etc.
        restoreScrollPositions();
        restoreModalContext();
        
        // Re-attach global listener nodes
        attachEventHandlers();
    }
    
    async function fetchFreshInBackground(url) {
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });
            if (response.ok) {
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const mainContent = doc.querySelector('#appMainContent');
                const pageTitle = doc.querySelector('title')?.textContent || '';
                if (mainContent) {
                    const pageData = { mainContent, pageTitle, html };
                    pageCache.set(url, pageData);
                    log('Cached preloaded background state refreshed successfully.');
                }
            }
        } catch (e) {
            log('Background refresh failed safely:', e);
        }
    }
    
    // ----------------------------------------------------
    // 4. ROUTER EVENT INTERCEPTIONS & DETECTIONS
    // ----------------------------------------------------
    function handleFormSubmit(e) {
        const form = e.target;
        const action = form.getAttribute('action') || window.location.href;
        const method = (form.getAttribute('method') || 'GET').toUpperCase();
        
        // Safe Guards: skip file uploads, logouts, or archive security modals
        if (form.querySelector('input[type="file"]') || form.id === 'logout-form' || action.includes('logout') || form.id === 'globalArchiveSecurityModal') {
            log('Skipping advanced submit. Running default form upload handler.');
            return;
        }
        
        e.preventDefault();
        log(`Intercepted Submit form: ${action} [${method}]`);
        
        const formData = new FormData(form);
        const options = { method };
        let targetUrl = action;
        
        if (method === 'GET') {
            const queryParams = new URLSearchParams(formData).toString();
            targetUrl = action.split('?')[0] + (queryParams ? '?' + queryParams : '');
        } else {
            options.body = formData;
        }
        
        // Synchronize Active URL query state into forms (like page=2, search, tab) so redirect brings them back exactly!
        const currentParams = new URLSearchParams(window.location.search);
        const stateCarriers = ['page', 'search', 'status', 'sort', 'filter', 'view', 'tab', 'type'];
        const formQueryAppender = new URLSearchParams();
        
        stateCarriers.forEach((p) => {
            if (currentParams.has(p)) {
                formQueryAppender.append(p, currentParams.get(p));
            }
        });
        
        const appendStr = formQueryAppender.toString();
        if (appendStr) {
            targetUrl = targetUrl + (targetUrl.includes('?') ? '&' : '?') + appendStr;
            log(`Appended page context parameters into post URL: ${targetUrl}`);
        }
        
        performAjaxTransition(targetUrl, options, 'submit');
    }
    
    function handleLinkClick(e) {
        const link = e.target.closest('a');
        if (!link) return;
        
        const href = link.getAttribute('href');
        
        // Dead links or external anchors
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('tel:') || href.startsWith('mailto:') || e.ctrlKey || e.metaKey || e.shiftKey) {
            return;
        }
        
        // Skip links pointing outside our system domain
        const linkUrl = new URL(href, window.location.origin);
        if (linkUrl.origin !== window.location.origin) return;
        
        e.preventDefault();
        log(`Intercepted dynamic page link: ${href}`);
        
        link.classList.add('nav-loading');
        
        performAjaxTransition(href, { method: 'GET' }, 'navigate');
    }
    
    // ----------------------------------------------------
    // 5. ENGINE STARTUP & BINDINGS
    // ----------------------------------------------------
    function attachEventHandlers() {
        document.removeEventListener('click', handleLinkClick);
        document.removeEventListener('submit', handleFormSubmit);
        
        document.addEventListener('click', handleLinkClick);
        document.addEventListener('submit', handleFormSubmit);
    }
    
    // Navigation back/forward pop listeners
    window.addEventListener('popstate', (e) => {
        log('Browser Back/Forward navigation detected.');
        performAjaxTransition(window.location.href, { method: 'GET' }, 'pop');
    });
    
    // Start Engine
    document.addEventListener('DOMContentLoaded', () => {
        log('Global App-Like State Engine actively loaded.');
        attachEventHandlers();
        
        // Restore states if returning to page
        restoreScrollPositions();
        restoreModalContext();
    });
    
    // Hook for AJAX-driven tab selections and URL synchronization
    window.syncQueryParamState = function(key, value) {
        const url = new URL(window.location.href);
        if (value === null || value === undefined || value === '') {
            url.searchParams.delete(key);
        } else {
            url.searchParams.set(key, value);
        }
        history.replaceState({}, '', url.toString());
        log(`Synchronized address bar state: ?${key}=${value}`);
    };
})();
