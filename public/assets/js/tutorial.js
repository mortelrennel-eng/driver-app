/* public/assets/js/tutorial.js */
const TutorialManager = (function () {
    // VISUAL DEBUGGER (Removed UI, keeping console logs only)
    function logDebug(msg) {
        console.log("[Tutorial] " + msg);
    }

    // Helper to find sidebar links robustly by text content
    function findSidebarLink(textMatches) {
        const links = Array.from(document.querySelectorAll('.sidebar span, aside span, nav span, a span'));
        for (let link of links) {
            if (link.children.length > 0) continue;
            // Crucial: Check if the element is actually visible on the screen
            if (link.offsetWidth === 0 && link.offsetHeight === 0) continue;
            
            const text = link.textContent.trim();
            for (let match of textMatches) {
                if (text === match) {
                    logDebug(`Found sidebar link span by text: "${match}"`);
                    let btn = link.closest('a, button, .sidebar-item');
                    return btn ? btn : link;
                }
            }
        }
        return null;
    }

    // Helper to find dashboard cards by their headers
    function findDashboardCard(textMatches) {
        const els = Array.from(document.querySelectorAll('h1, h2, h3, h4, h5, h6, span, p, div'));
        for (let el of els) {
            if (el.tagName === 'SCRIPT' || el.tagName === 'STYLE') continue;
            if (el.children.length > 2) continue;

            const text = el.textContent.trim().toUpperCase();
            for (let match of textMatches) {
                if (text === match.toUpperCase() || text.includes(match.toUpperCase())) {
                    logDebug(`Found dashboard text element: "${match}"`);
                    let current = el;
                    while (current && current !== document.body) {
                        const hasShadow = Array.from(current.classList).some(c => c.startsWith('shadow'));
                        if (current.classList.contains('card-hover') || 
                            current.classList.contains('rounded-2xl') ||
                            current.classList.contains('rounded-xl') ||
                            current.classList.contains('card') ||
                            (current.classList.contains('bg-gray-50') && current.classList.contains('border-l')) ||
                            (current.classList.contains('bg-white') && (current.classList.contains('rounded-lg') || hasShadow))) {
                            // Verify it's not the whole screen width unless it's a full-width chart
                            if (current.offsetWidth < window.innerWidth * 0.98) {
                                return current;
                            }
                        }
                        current = current.parentElement;
                    }
                    return el; // fallback
                }
            }
        }
        return null;
    }

    const steps = [
        {
            id: 'sidebar-dashboard',
            getElement: () => findSidebarLink(['Dashboard']),
            popover: { title: 'Dashboard Menu', description: 'Welcome! This is the main Dashboard menu. Clicking here gives you a high-level overview of your entire fleet, revenues, and active drivers.', position: 'right' }
        },
        {
            id: 'dash-total-units',
            getElement: () => findDashboardCard(['TOTAL UNITS']),
            popover: { title: 'Total Units', description: 'Here you can see the total number of cars in your fleet and how many have achieved ROI.', position: 'bottom' }
        },
        {
            id: 'dash-boundary-revenue',
            getElement: () => findDashboardCard(['BOUNDARY REVENUE']),
            popover: { title: 'Boundary Revenue', description: 'This tracks your daily and monthly boundary collections from drivers.', position: 'bottom' }
        },
        {
            id: 'dash-net-income',
            getElement: () => findDashboardCard(['NET INCOME']),
            popover: { title: 'Net Income', description: 'Your actual profit after deducting office expenses and maintenance costs.', position: 'bottom' }
        },
        {
            id: 'dash-under-maintenance',
            getElement: () => findDashboardCard(['UNDER MAINTENANCE']),
            popover: { title: 'Under Maintenance', description: 'The number of units currently in the garage for repairs or regular maintenance.', position: 'bottom' }
        },
        {
            id: 'dash-active-drivers',
            getElement: () => findDashboardCard(['ACTIVE DRIVERS']),
            popover: { title: 'Active Drivers', description: 'The total number of currently active drivers on the road.', position: 'bottom' }
        },
        {
            id: 'dash-expenses',
            getElement: () => findDashboardCard(['EXPENSES TODAY']),
            popover: { title: 'Daily Expenses', description: 'Monitor your daily outflow for office and operational expenses.', position: 'bottom' }
        },
        {
            id: 'dash-coding-units',
            getElement: () => findDashboardCard(['CODING UNITS TODAY']),
            popover: { title: 'Coding Units', description: 'Units that are restricted from operating today due to number coding.', position: 'bottom' }
        },
        {
            id: 'dash-performance',
            getElement: () => findDashboardCard(['UNIT PERFORMANCE', 'Top 10 Performers']),
            popover: { 
                title: 'Unit Performance & Insights', 
                description: 'This chart compares the actual boundary collections against the 30-day target for each unit.<br><br><b>Executive Insights</b> on the right gives AI-generated summaries regarding your fleet health and top performers.', 
                position: 'top' 
            }
        },
        {
            id: 'dash-revenue-trend',
            getElement: () => findDashboardCard(['Revenue Trend']),
            popover: { title: 'Revenue Trend', description: 'This graph shows your revenue trend over time, helping you identify peak days and performance drops.', position: 'top' }
        },
        {
            id: 'dash-expense-breakdown',
            getElement: () => findDashboardCard(['Expense Breakdown & Distribution']),
            popover: { title: 'Expense Breakdown', description: 'A visual breakdown of where your expenses are going, such as maintenance, fuel, and salaries.', position: 'top' }
        },
        {
            id: 'dash-weekly-overview',
            getElement: () => findDashboardCard(['Weekly Financial Overview']),
            popover: { title: 'Weekly Overview', description: 'Compare your income versus expenses on a weekly basis.', position: 'top' }
        },
        {
            id: 'dash-status-distribution',
            getElement: () => findDashboardCard(['Unit Status Distribution']),
            popover: { title: 'Status Distribution', description: 'A quick view of your fleet\'s current status: active, coding, or under maintenance.', position: 'top' }
        },
        {
            id: 'dash-top-drivers',
            getElement: () => findDashboardCard(['Top Performing Drivers']),
            popover: { title: 'Top Performing Drivers', description: 'A ranking of your most consistent and highest-earning drivers based on their daily boundary collections.', position: 'top' }
        },
        {
            id: 'sidebar-units',
            getElement: () => findSidebarLink(['Unit Management']),
            popover: { title: 'Unit Management', description: 'Add new cars, monitor their status, and manage the entire fleet inventory.', position: 'right' }
        },
        {
            id: 'sidebar-drivers',
            getElement: () => findSidebarLink(['Driver Management']),
            popover: { title: 'Driver Management', description: 'Register new drivers, handle bans, contracts, and debt records.', position: 'right' }
        },
        {
            id: 'sidebar-tracking',
            getElement: () => findSidebarLink(['Live Tracking']),
            popover: { title: 'Live Tracking', description: 'Track your units via real-time GPS map integration.', position: 'right' }
        },
        {
            id: 'sidebar-franchise',
            getElement: () => findSidebarLink(['Franchise']),
            popover: { title: 'Franchise Records', description: 'Manage franchise documents and expiration dates.', position: 'right' }
        },
        {
            id: 'sidebar-boundaries',
            getElement: () => findSidebarLink(['Boundaries']),
            popover: { title: 'Boundaries', description: 'Record daily boundary collections and driver remittances.', position: 'right' }
        },
        {
            id: 'sidebar-maintenance',
            getElement: () => findSidebarLink(['Maintenance']),
            popover: { title: 'Maintenance', description: 'Log car repairs, oil changes, and parts inventory.', position: 'right' }
        },
        {
            id: 'sidebar-coding',
            getElement: () => findSidebarLink(['Coding Management']),
            popover: { title: 'Coding Management', description: 'Set and monitor MMDA number coding schemes.', position: 'right' }
        },
        {
            id: 'sidebar-behavior',
            getElement: () => findSidebarLink(['Driver Behavior']),
            popover: { title: 'Driver Behavior', description: 'Track incentives, performance charts, and accident reports.', position: 'right' }
        },
        {
            id: 'sidebar-expenses',
            getElement: () => findSidebarLink(['Office Expenses']),
            popover: { title: 'Office Expenses', description: 'Log daily operational expenses and overhead.', position: 'right' }
        },
        {
            id: 'sidebar-salary',
            getElement: () => findSidebarLink(['Salary Management']),
            popover: { title: 'Salary Management', description: 'Process payroll and employee salaries.', position: 'right' }
        },
        {
            id: 'sidebar-analytics',
            getElement: () => findSidebarLink(['Analytics']),
            popover: { title: 'Analytics', description: 'Generate advanced financial and operational reports.', position: 'right' }
        },
        {
            id: 'sidebar-profitability',
            getElement: () => findSidebarLink(['Unit Profitability']),
            popover: { title: 'Unit Profitability', description: 'Track ROI and net profit for each specific car.', position: 'right' }
        },
        {
            id: 'sidebar-staff',
            getElement: () => findSidebarLink(['General Staff Records', 'Staff']),
            popover: { title: 'Staff Records', description: 'Manage system administrators and mobile app users.', position: 'right' }
        },
        {
            id: 'sidebar-profile',
            getElement: () => findSidebarLink(['Take the Tour Again', 'Logout']),
            popover: { title: 'Profile Menu', description: 'Access your account settings or retake this tour anytime from here.', position: 'right' }
        }
    ];

    let driverObj = null;

    function init(tutorialCompleted) {
        logDebug("Tutorial init() called. Completed status: " + tutorialCompleted);
        if (!window.driver) {
            logDebug("ERROR: window.driver is undefined! Driver.js CDN failed to load.");
            return;
        }

        if (tutorialCompleted && !localStorage.getItem('tutorial_force_restart')) {
            logDebug("Tutorial already completed. Exiting.");
            return;
        }

        const currentStepIndex = parseInt(localStorage.getItem('tutorial_current_step') || '0', 10);
        logDebug("Current step index loaded: " + currentStepIndex);
        
        if (currentStepIndex === 0 && !localStorage.getItem('tutorial_welcome_shown')) {
            logDebug("Showing welcome modal.");
            showWelcomeModal();
        } else {
            startTutorial(currentStepIndex);
        }
    }

    function showWelcomeModal() {
        if(document.getElementById('tutorial-welcome-modal')) return;
        const modalHtml = `
            <div id="tutorial-welcome-modal" class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 999999; background: rgba(17, 24, 39, 0.85); backdrop-filter: blur(12px);">
                <div id="tutorial-welcome-content" class="bg-gray-900 rounded-3xl shadow-2xl p-8 max-w-sm w-full text-center relative border border-gray-700" style="animation: modal-pop-in 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;">
                    <div class="w-20 h-20 bg-blue-500/10 text-blue-400 rounded-full flex items-center justify-center mx-auto mb-6 shadow-[0_0_30px_rgba(59,130,246,0.3)]">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h2 class="text-3xl font-black text-white mb-3 tracking-tight">Welcome!</h2>
                    <p class="text-gray-400 mb-8 leading-relaxed text-sm">This quick tour will show you how to navigate and use the system effectively. We're glad you're here.</p>
                    <div class="flex flex-col gap-3">
                        <button id="tutorial-start-btn" class="w-full py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">Start Tour</button>
                        <button id="tutorial-skip-btn" class="w-full py-3.5 bg-transparent hover:bg-gray-800 text-gray-400 hover:text-white font-semibold rounded-xl transition-all border border-gray-700">Skip for now</button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        document.getElementById('tutorial-start-btn').addEventListener('click', () => {
            logDebug("User clicked Start Tour");
            document.getElementById('tutorial-welcome-modal').remove();
            localStorage.setItem('tutorial_welcome_shown', '1');
            startTutorial(0);
        });

        document.getElementById('tutorial-skip-btn').addEventListener('click', () => {
            logDebug("User skipped tour");
            document.getElementById('tutorial-welcome-modal').remove();
            markTutorialComplete();
        });
    }

    function initProgressBar(totalSteps) {
        let progressBar = document.getElementById('tutorial-global-progress');
        if (!progressBar) {
            const dotsHtml = Array.from({length: totalSteps}).map((_, i) => `<div id="tut-dot-${i}" class="tutorial-progress-dot"></div>`).join('');
            document.body.insertAdjacentHTML('beforeend', `
                <div id="tutorial-global-progress">
                    <div class="tutorial-progress-track">
                        <div id="tutorial-progress-fill" class="tutorial-progress-fill"></div>
                        ${dotsHtml}
                    </div>
                    <button class="tutorial-exit-btn" onclick="if(window.TutorialManager) window.TutorialManager.finishTutorial();">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Exit Tour
                    </button>
                </div>
            `);
        }
    }

    function updateProgress(index, totalSteps) {
        for (let i = 0; i < totalSteps; i++) {
            const dot = document.getElementById(`tut-dot-${i}`);
            if (dot) {
                if (i <= index) dot.classList.add('active');
                else dot.classList.remove('active');
            }
        }
        const fill = document.getElementById('tutorial-progress-fill');
        if (fill) {
            const percentage = (index / (totalSteps - 1)) * 100;
            fill.style.width = `${percentage}%`;
        }
    }

    function startTutorial(stepIndex) {
        logDebug(`startTutorial called with stepIndex: ${stepIndex}`);
        
        if (stepIndex >= steps.length) {
            logDebug("Reached end of steps. Finishing tutorial.");
            finishTutorial();
            return;
        }

        const step = steps[stepIndex];
        logDebug(`Attempting to find element for step: ${step.id}`);
        const targetElement = step.getElement ? step.getElement() : null;
        
        if (!targetElement) {
            logDebug(`WARNING: Target element for step '${step.id}' not found. Auto-skipping to next step.`);
            const nextIndex = stepIndex + 1;
            localStorage.setItem('tutorial_current_step', nextIndex);
            startTutorial(nextIndex);
            return;
        }

        // Apply a unique ID to guarantee Driver.js finds the exact DOM element via CSS selector
        const dynamicId = `tutorial-active-target-${stepIndex}`;
        targetElement.id = dynamicId;
        logDebug(`Target element found. Assigned ID: #${dynamicId}`);

        const isLastStep = stepIndex === steps.length - 1;

        initProgressBar(steps.length);
        updateProgress(stepIndex, steps.length);

        try {
            logDebug(`Initializing Driver.js for #${dynamicId}`);
            driverObj = window.driver.js.driver({
                showProgress: false,
                allowClose: false,
                overlayColor: 'rgba(15, 23, 42, 0.8)',
                popoverOffset: 80, // Move the popover further away to give the arrow space
                animate: true,
                smoothScroll: true,
                stagePadding: 6,
                keyboardControl: true,
                steps: [
                    {
                        element: `#${dynamicId}`,
                        popover: {
                            title: step.popover.title,
                            description: step.popover.description,
                            position: step.popover.position || 'right',
                            showButtons: ['close'], // Force custom click flow by removing next/prev buttons inside Driver
                            closeBtnText: '', 
                        }
                    }
                ],
                onPopoverRender: (popover, { config, state }) => {
                    try {
                        logDebug("onPopoverRender triggered.");
                        const currentStep = stepIndex + 1;
                        const totalSteps = steps.length;
                        
                        // Depending on driver.js version, popover might be the wrapper itself or an object containing it
                        let wrapper = popover.wrapper || popover;
                        
                        if (wrapper && wrapper.classList) {
                            wrapper.classList.add('tutorial-force-click');
                        }

                        // Inject Step number
                        const titleEl = popover.title || wrapper.querySelector('.driver-popover-title');
                        if (titleEl && !titleEl.querySelector('.tutorial-progress-text')) {
                            titleEl.insertAdjacentHTML('afterbegin', `<span class="tutorial-progress-text">Step ${currentStep} of ${totalSteps}</span>`);
                        }

                        // Replace close button with an X icon
                        const closeBtn = popover.closeButton || popover.closeBtn || wrapper.querySelector('.driver-popover-close-btn');
                        if (closeBtn) {
                            closeBtn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
                            closeBtn.onclick = () => finishTutorial();
                        }

                        // Add custom Continue button since we disabled default ones
                        const footerEl = popover.footer || wrapper.querySelector('.driver-popover-footer');
                        if (footerEl && !footerEl.querySelector('.tutorial-continue-btn')) {
                            const btnText = isLastStep ? "Finish Tour" : "Next Step";
                            footerEl.insertAdjacentHTML('beforeend', `
                                <button class="driver-popover-next-btn tutorial-continue-btn" style="width:100%; margin-top:10px;">${btnText}</button>
                                <button class="tutorial-skip-link" onclick="if(window.TutorialManager) window.TutorialManager.finishTutorial();">Skip Tour</button>
                            `);
                            footerEl.querySelector('.tutorial-continue-btn').addEventListener('click', (e) => {
                                e.stopPropagation();
                                logDebug("Continue button clicked in popover.");
                                moveToNextStep(stepIndex);
                            });
                        }

                        // Explicitly hide the Previous button on Step 1
                        const prevBtn = popover.previousButton || popover.prevBtn || wrapper.querySelector('.driver-popover-prev-btn');
                        if (prevBtn) {
                            if (stepIndex === 0) {
                                prevBtn.style.display = 'none';
                            } else {
                                prevBtn.style.display = ''; // Restore default display for other steps
                            }
                        }

                        // Inject Curved Arrow SVG
                        if (wrapper) {
                            // Wait for Driver.js to finish moving the popover
                            setTimeout(() => {
                                if (!document.body.contains(wrapper)) return;

                                let pos = step.popover.position || 'right';
                                
                                // DYNAMIC POSITION CALCULATION: driver.js might fallback to another position if there's no space.
                                if (targetElement && wrapper) {
                                    const tRect = targetElement.getBoundingClientRect();
                                    const pRect = wrapper.getBoundingClientRect();
                                    
                                    const tCenter = { x: tRect.left + tRect.width / 2, y: tRect.top + tRect.height / 2 };
                                    const pCenter = { x: pRect.left + pRect.width / 2, y: pRect.top + pRect.height / 2 };
                                    
                                    const dx = pCenter.x - tCenter.x;
                                    const dy = pCenter.y - tCenter.y;
                                    
                                    if (Math.abs(dx) > Math.abs(dy)) {
                                        pos = dx > 0 ? 'right' : 'left';
                                    } else {
                                        pos = dy > 0 ? 'bottom' : 'top';
                                    }
                                    logDebug(`Dynamic position detected (delayed): ${pos}`);
                                }

                                let pathD = "M 10 90 Q 30 10 90 20";
                                let polyPoints = "90,20 80,15 85,28";
                                
                                if (pos === 'right') {
                                    // Pointing LEFT (towards target on left)
                                    pathD = "M 90 70 Q 50 10 10 30";
                                    polyPoints = "10,30 25,25 20,40";
                                } else if (pos === 'left') {
                                    // Pointing RIGHT
                                    pathD = "M 10 70 Q 50 10 90 30";
                                    polyPoints = "90,30 75,25 80,40";
                                } else if (pos === 'bottom') {
                                    // Pointing UP
                                    pathD = "M 50 90 Q 20 50 50 10";
                                    polyPoints = "50,10 40,25 60,25";
                                } else if (pos === 'top') {
                                    // Pointing DOWN
                                    pathD = "M 50 10 Q 80 50 50 90";
                                    polyPoints = "50,90 40,75 60,75";
                                }

                                const arrowSvg = `
                                    <div class="tutorial-arrow-wrapper arrow-pos-${pos} arrow-fade-in">
                                        <svg class="tutorial-curved-arrow" viewBox="0 0 100 100">
                                            <path d="${pathD}" stroke-dasharray="8,8" />
                                            <polygon points="${polyPoints}" fill="#60a5fa" />
                                        </svg>
                                    </div>
                                `;
                                if (!wrapper.querySelector('.tutorial-arrow-wrapper')) {
                                    wrapper.insertAdjacentHTML('afterbegin', arrowSvg);
                                }
                            }, 350); // 350ms to allow smooth popover transition first
                        }
                        logDebug("onPopoverRender finished successfully.");
                    } catch (err) {
                        logDebug("ERROR in onPopoverRender: " + err.message);
                    }
                },
                onDestroyStarted: () => {
                    logDebug("onDestroyStarted triggered.");
                    if(driverObj) driverObj.destroy();
                    markTutorialComplete();
                }
            });

            // Also allow clicking the actual highlighted element to proceed
            const clickHandler = (e) => {
                logDebug(`Target element #${dynamicId} clicked directly.`);
                
                // Determine if we should allow native behavior (only if it's a link to another page)
                let shouldNavigate = false;
                const linkEl = targetElement.tagName === 'A' ? targetElement : targetElement.closest('a');
                if (linkEl && linkEl.href) {
                    const currentUrl = window.location.href.split('?')[0].split('#')[0];
                    const linkUrl = linkEl.href.split('?')[0].split('#')[0];
                    if (linkUrl !== currentUrl && !linkUrl.includes('javascript:')) {
                        shouldNavigate = true;
                    }
                }

                if (!shouldNavigate) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    logDebug("Native click behavior blocked (Tutorial mode).");
                } else {
                    logDebug("Cross-page navigation allowed.");
                }
                
                // CRITICAL FIX: Immediately save progress BEFORE the browser navigates away on links!
                const nextIndex = stepIndex + 1;
                localStorage.setItem('tutorial_current_step', nextIndex.toString());

                if (targetElement.style.position !== 'relative' && targetElement.style.position !== 'absolute') {
                    targetElement.style.position = 'relative';
                    targetElement.style.overflow = 'hidden';
                }
                
                const ripple = document.createElement('div');
                ripple.className = 'tutorial-ripple';
                const rect = targetElement.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height) * 2;
                ripple.style.position = 'absolute';
                ripple.style.pointerEvents = 'none';
                ripple.style.width = `${size}px`;
                ripple.style.height = `${size}px`;
                ripple.style.left = `${e.clientX - rect.left - size/2}px`;
                ripple.style.top = `${e.clientY - rect.top - size/2}px`;
                targetElement.appendChild(ripple);

                // Destroy driver early so it doesn't block the click navigation
                if (driverObj) {
                    driverObj.destroy();
                }

                setTimeout(() => {
                    if (ripple.parentNode) ripple.remove();
                    // If it was a cross-page link, the page is unloading and this won't matter.
                    // If it was an on-page click, we manually start the next step.
                    if (nextIndex >= steps.length) {
                        finishTutorial();
                    } else {
                        startTutorial(nextIndex);
                    }
                }, 400);
                targetElement.removeEventListener('click', clickHandler, true);
            };
            targetElement.addEventListener('click', clickHandler, true);

            logDebug("Calling driverObj.drive()");
            driverObj.drive();
        } catch (error) {
            logDebug("EXCEPTION CAUGHT: " + error.message);
            console.error(error);
        }
    }

    function moveToNextStep(currentIndex) {
        logDebug(`Moving to next step after ${currentIndex}`);
        const nextIndex = currentIndex + 1;
        localStorage.setItem('tutorial_current_step', nextIndex);
        if (nextIndex >= steps.length) {
            finishTutorial();
        } else {
            setTimeout(() => {
                if (driverObj) {
                    logDebug("Destroying current driver instance.");
                    driverObj.destroy();
                }
                startTutorial(nextIndex);
            }, 300); // Shorter delay
        }
    }

    function finishTutorial() {
        logDebug("finishTutorial called.");
        if (driverObj) driverObj.destroy();
        const progress = document.getElementById('tutorial-global-progress');
        if (progress) progress.remove();
        
        const debuggerEl = document.getElementById('tutorial-debugger');
        if (debuggerEl) {
            debuggerEl.innerHTML += `<div>> <strong>Tutorial Finished!</strong> You can safely refresh the page.</div>`;
        }
        
        markTutorialComplete();
    }

    function markTutorialComplete() {
        localStorage.removeItem('tutorial_current_step');
        localStorage.removeItem('tutorial_welcome_shown');
        localStorage.removeItem('tutorial_force_restart');
        
        fetch('/api/tutorial/complete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        }).catch(err => logDebug("Failed to save complete status: " + err));
    }

    function restart() {
        logDebug("Restart triggered.");
        localStorage.setItem('tutorial_force_restart', '1');
        localStorage.setItem('tutorial_current_step', '0');
        localStorage.removeItem('tutorial_welcome_shown');
        window.location.href = '/'; 
    }

    return {
        init,
        restart,
        finishTutorial
    };
})();
window.TutorialManager = TutorialManager;
