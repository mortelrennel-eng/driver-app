<?php
$file = 'public/js/realtime-dashboard.js';
$jsCode = <<<'EOD'
class RealTimeDashboard {
    constructor() {
        this.updateInterval = 5000;
        this.isUpdating = false;
        this.lastUpdateTime = Date.now();
        this.stabilityDelay = 3000;
        
        this.previousStats = {};
        if (window.__INITIAL_STATS__) {
            Object.keys(window.__INITIAL_STATS__).forEach(key => {
                this.previousStats[key] = parseFloat(window.__INITIAL_STATS__[key]) || 0;
            });
        }
        
        this.init();
    }

    init() {
        setTimeout(() => {
            this.startRealTimeUpdates();
        }, this.stabilityDelay);
        
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopRealTimeUpdates();
            } else {
                this.startRealTimeUpdates();
                setTimeout(() => this.updateDashboardData(), 1500);
            }
        });
    }

    startRealTimeUpdates() {
        if (!this.pollInterval) {
            this.pollInterval = setInterval(() => {
                this.updateDashboardData();
            }, this.updateInterval);
        }
    }

    stopRealTimeUpdates() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
    }

    async updateDashboardData() {
        if (this.isUpdating) return;
        if (Date.now() - this.lastUpdateTime < 2000) return;

        this.isUpdating = true;
        this.lastUpdateTime = Date.now();

        try {
            const response = await fetch(`/api/dashboard/realtime?_=${Date.now()}`);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            
            if (data.success && data.stats) {
                const newActiveUnits = parseFloat(data.stats.active_units) || 0;
                if (newActiveUnits === 0 && this.previousStats.active_units > 0) {
                    return;
                }

                this.updateStats(data.stats);
                this.updateCharts(data.charts);
                
                if (data.alerts) {
                    this.updateAlerts(data.alerts);
                }
            }
        } catch (error) {
            // Silently fail in production
        } finally {
            this.isUpdating = false;
        }
    }

    updateStats(stats) {
        const statConfig = [
            { key: 'active_units', selector: '[data-stat="active_units"]', format: 'number' },
            { key: 'roi_achieved', selector: '[data-stat="roi_achieved"]', format: 'number' },
            { key: 'coding_units', selector: '[data-stat="coding_units"]', format: 'number' },
            { key: 'maintenance_units', selector: '[data-stat="maintenance_units"]', format: 'number' },
            { key: 'active_drivers', selector: '[data-stat="active_drivers"]', format: 'number' },
            { key: 'today_boundary', selector: '[data-stat="today_boundary"]', format: 'currency' },
            { key: 'today_expenses', selector: '[data-stat="today_expenses"]', format: 'currency' },
            { key: 'net_income', selector: '[data-stat="net_income"]', format: 'currency' },
            { key: 'daily_target', selector: '[data-stat="daily_target"]', format: 'currency' }
        ];

        statConfig.forEach(config => {
            const newValue = stats[config.key];
            const prevValue = this.previousStats[config.key];
            const nVal = parseFloat(newValue) || 0;
            const pVal = parseFloat(prevValue) || 0;

            if (newValue !== undefined && newValue !== null && Math.abs(nVal - pVal) > 0.01) {
                const element = document.querySelector(config.selector);
                if (element) {
                    this.animateValue(element, nVal, config.format);
                    this.previousStats[config.key] = nVal;
                }
            }
        });
    }

    animateValue(element, endValue, format) {
        const currentText = element.textContent || '0';
        const startValue = parseFloat(currentText.replace(/[^\d.-]/g, '')) || 0;
        
        if (Math.abs(startValue - endValue) < 0.01) return;

        const duration = 1000;
        const startTime = performance.now();

        const formatFn = (val) => {
            if (format === 'currency') {
                return '₱' + Math.floor(val).toLocaleString();
            }
            return Math.floor(val).toLocaleString();
        };

        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            
            const currentVal = startValue + (endValue - startValue) * easeProgress;
            element.textContent = formatFn(currentVal);

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                element.textContent = formatFn(endValue);
            }
        };

        requestAnimationFrame(animate);
    }

    updateCharts(charts) {
        if (!charts) return;

        if (window.weeklyChart && charts.weekly_data) {
            window.weeklyChart.data.labels = charts.weekly_data.map(d => d.day);
            window.weeklyChart.data.datasets[0].data = charts.weekly_data.map(d => d.boundary);
            window.weeklyChart.data.datasets[1].data = charts.weekly_data.map(d => d.expenses);
            window.weeklyChart.data.datasets[2].data = charts.weekly_data.map(d => d.net);
            window.weeklyChart.update('none');
        }

        if (window.unitStatusChart && charts.unit_status_data) {
            window.unitStatusChart.data.labels = charts.unit_status_data.map(d => d.status);
            window.unitStatusChart.data.datasets[0].data = charts.unit_status_data.map(d => d.count);
            window.unitStatusChart.update('none');
        }

        if (window.revenueTrendChart && charts.revenue_trend) {
            window.revenueTrendChart.data.labels = charts.revenue_trend.map(d => d.date);
            window.revenueTrendChart.data.datasets[0].data = charts.revenue_trend.map(d => d.revenue);
            window.revenueTrendChart.update('none');
        }

        if (window.unitPerformanceChart && charts.unit_performance) {
            window.unitPerformanceChart.data.labels = charts.unit_performance.map(d => d.unit);
            window.unitPerformanceChart.data.datasets[0].data = charts.unit_performance.map(d => d.performance);
            window.unitPerformanceChart.data.datasets[1].data = charts.unit_performance.map(d => d.target);
            window.unitPerformanceChart.update('none');
        }
    }

    updateAlerts(alerts) {
        const container = document.getElementById('alerts-container');
        if (!container) return;

        const currentAlertsHash = JSON.stringify(alerts);
        if (this.lastAlertsHash === currentAlertsHash) return;
        this.lastAlertsHash = currentAlertsHash;

        if (alerts.length === 0) {
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                    <div class="p-4 bg-gray-50 rounded-full mb-4">
                        <i data-lucide="check-circle-2" class="w-12 h-12 text-gray-200"></i>
                    </div>
                    <p class="font-black uppercase tracking-widest text-xs">All Clear</p>
                    <p class="text-[10px] mt-1">No pending system alerts detected</p>
                </div>
            `;
        } else {
            container.innerHTML = alerts.map(alert => `
                <div class="group relative bg-white border border-gray-100 rounded-2xl p-4 transition-all duration-300 hover:shadow-lg hover:border-orange-100 mb-3 overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-orange-500"></div>
                    <div class="flex items-start gap-4">
                        <div class="p-2.5 bg-orange-50 rounded-xl border border-orange-100">
                            <i data-lucide="alert-circle" class="w-5 h-5 text-orange-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h5 class="text-sm font-black text-gray-900 mb-1 truncate">${alert.message}</h5>
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-0.5 bg-orange-100 text-orange-600 text-[9px] font-black uppercase tracking-widest rounded-full border border-orange-200">${alert.severity}</span>
                                <span class="text-[10px] text-gray-400 font-bold">${alert.alert_type}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.dashboardManager = new RealTimeDashboard();
});
EOD;

file_put_contents($file, $jsCode);
echo "SILENT JS SUCCESS";
