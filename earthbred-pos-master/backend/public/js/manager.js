/* ============================================================
   manager.js — Earthbred Manager Dashboard Frontend Logic
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    const BASE = (function() {
        const pathname = window.location.pathname;
        const idx = pathname.toLowerCase().indexOf('/backend/public');
        return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
    })();
    let salesChartInstance = null;

    /**
     * Format number as Philippine Peso currency
     */
    function formatPHP(amount) {
        return '₱' + Number(amount).toLocaleString('en-PH', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    /**
     * Render the trend HTML string with caret icons
     */
    function renderTrendHTML(trendData, type = 'percent') {
        const isUp = trendData.direction === 'up';
        const iconClass = isUp ? 'fa-caret-up' : 'fa-caret-down';
        const trendClass = isUp ? 'up' : 'down';
        
        let text = '';
        if (type === 'percent') {
            text = `${trendData.percent}% vs yesterday`;
        } else {
            text = trendData.text;
        }

        return `<span class="mgr-kpi-trend ${trendClass}"><i class="fa-solid ${iconClass}"></i> ${text}</span>`;
    }

    /**
     * Fetch all statistics from the API and update the UI
     */
    function loadDashboardData() {
        // Fast-render from session cache if available
        const cached = sessionStorage.getItem('mgr_dashboard_cache');
        if (cached) {
            try {
                const cachedData = JSON.parse(cached);
                applyDashboardData(cachedData);
            } catch (e) {}
        }

        fetch(`${BASE}/api/manager/stats`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    console.error('Failed to load manager dashboard data:', data.message);
                    return;
                }
                sessionStorage.setItem('mgr_dashboard_cache', JSON.stringify(data));
                applyDashboardData(data);
            })
            .catch(err => {
                console.error('Error fetching dashboard stats:', err);
            });
    }

    function applyDashboardData(data) {
        // 1. Update KPI values
        document.getElementById('kpiTodaySales').textContent = formatPHP(data.today_sales);
        document.getElementById('kpiTodaySalesTrend').innerHTML = renderTrendHTML(data.today_sales_trend, 'percent');

        document.getElementById('kpiTodayOrders').textContent = data.today_orders;
        document.getElementById('kpiTodayOrdersTrend').innerHTML = renderTrendHTML(data.today_orders_trend, 'text');

        document.getElementById('kpiAOV').textContent = formatPHP(data.avg_order_value);
        document.getElementById('kpiAOVTrend').innerHTML = renderTrendHTML(data.avg_order_value_trend, 'text');

        // 2. Render weekly sales chart
        renderSalesChart(data.chart_data);

        // 3. Render Top Selling Items
        renderTopItems(data.top_items, data.is_top_items_fallback);

        // 4. Render alerts list
        renderAlerts(data.inventory_alerts, data.unresolved_shift_notes_count);

        // 5. Render Sales Report Analytics Quick View
        if (data.sales_analytics) {
            renderDashboardSalesAnalytics(data.sales_analytics);
        }
    }

    /**
     * Render the Sales Report Analytics Quick View Widget on Dashboard
     */
    function renderDashboardSalesAnalytics(sa) {
        if (!sa) return;
        const fs = sa.financial_summary || {};
        const tp = sa.target_progress || {};
        const pm = sa.payment_methods || {};
        const ins = sa.insights || {};

        const grossEl = document.getElementById('dashGrossSales');
        const discEl = document.getElementById('dashDiscounts');
        const peakEl = document.getElementById('dashPeakRush');
        const splitEl = document.getElementById('dashPaymentSplit');
        const pctEl = document.getElementById('dashTargetPercent');
        const barEl = document.getElementById('dashTargetBar');
        const colEl = document.getElementById('dashCollectedVal');
        const remEl = document.getElementById('dashRemainingVal');

        if (grossEl) grossEl.textContent = formatPHP(fs.gross_sales || 0);
        if (discEl) discEl.textContent = '-' + formatPHP(fs.discounts_total || 0);
        if (peakEl) peakEl.textContent = ins.top_rush_period || 'Morning (8am-12pm)';
        if (splitEl) splitEl.textContent = `Cash ${pm.cash_percent || 0}% • GCash ${pm.gcash_percent || 0}%`;

        if (pctEl) pctEl.textContent = `${tp.percent || 0}%`;
        if (barEl) barEl.style.width = `${Math.min(100, tp.percent || 0)}%`;
        if (colEl) colEl.textContent = formatPHP(tp.current || 0);
        if (remEl) remEl.textContent = formatPHP(tp.remaining || 0);
    }

    /**
     * Render or update the Chart.js instance for weekly sales
     */
    function renderSalesChart(chartData) {
        const ctx = document.getElementById('mgrSalesChart');
        if (!ctx) return;

        // Destroy previous instance if it exists
        if (salesChartInstance) {
            salesChartInstance.destroy();
        }

        salesChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Sales (₱)',
                    data: chartData.values,
                    backgroundColor: '#a87850', // Premium brown bar color
                    hoverBackgroundColor: '#875830',
                    borderRadius: 6,
                    borderSkipped: false,
                    barThickness: 34
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` Sales: ₱${ctx.raw}k`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Poppins', size: 11, weight: '600' },
                            color: '#5c4a40'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(44, 26, 20, 0.05)' },
                        ticks: {
                            font: { family: 'Montserrat', size: 10, weight: '700' },
                            color: '#5c4a40',
                            callback: (val) => `₱${val}k`
                        }
                    }
                }
            }
        });
    }

    /**
     * Render the top selling items table
     */
    function renderTopItems(items, isFallback) {
        const tbody = document.getElementById('mgrTopItemsBody');
        const titleEl = document.getElementById('mgrTopItemsTitle');
        if (!tbody) return;

        // Update section title depending on fallback state
        if (isFallback) {
            titleEl.innerHTML = `Top Selling Items <span style="font-size: 0.8rem; font-weight: 500; color: var(--brand-light); text-transform: none;">(All-time history)</span>`;
        } else {
            titleEl.textContent = 'Top Selling Items Today';
        }

        tbody.innerHTML = '';

        if (items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 2rem; color: #5c4a40;">No order data available yet.</td></tr>`;
            return;
        }

        items.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><span class="mgr-item-name">${item.product_name}</span></td>
                <td><span class="mgr-category-tag">${item.category}</span></td>
                <td><span class="mgr-qty-sold">${item.qty_sold}</span></td>
                <td><span class="mgr-revenue">₱${item.revenue.toLocaleString()}</span></td>
                <td>
                    <div class="mgr-share-bar-wrap">
                        <div class="mgr-share-bar-bg">
                            <div class="mgr-share-bar-fill" style="width: ${item.share_percent}%;"></div>
                        </div>
                        <span class="mgr-share-percent">${item.share_percent}%</span>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    /**
     * Render operation notifications/alerts list
     */
    function renderAlerts(inventoryAlerts, unresolvedShiftNotes) {
        const container = document.getElementById('mgrAlertsList');
        if (!container) return;

        container.innerHTML = '';

        const outOfStock = inventoryAlerts.out_of_stock;
        const lowStock = inventoryAlerts.low_stock;
        
        let totalAlertsCount = outOfStock.length + lowStock.length + (unresolvedShiftNotes > 0 ? 1 : 0);

        // 1. Critical out of stock alerts
        outOfStock.forEach(itemName => {
            const banner = document.createElement('div');
            banner.className = 'mgr-alert-banner mgr-alert-banner-crit';
            banner.innerHTML = `
                <i class="fa-solid fa-circle-xmark"></i>
                <div class="mgr-alert-content">
                    <span class="mgr-alert-text">Out of Stock: ${itemName}</span>
                    <span class="mgr-alert-subtext">Restock needed immediately</span>
                </div>
            `;
            container.appendChild(banner);
        });

        // 2. Low stock warning alerts
        lowStock.forEach(item => {
            const banner = document.createElement('div');
            banner.className = 'mgr-alert-banner mgr-alert-banner-warn';
            banner.innerHTML = `
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div class="mgr-alert-content">
                    <span class="mgr-alert-text">Low Stock: ${item.item_name}</span>
                    <span class="mgr-alert-subtext">Only ${item.quantity} remaining</span>
                </div>
            `;
            container.appendChild(banner);
        });

        // 3. Shift Notes alerts
        if (unresolvedShiftNotes > 0) {
            const banner = document.createElement('div');
            banner.className = 'mgr-alert-banner mgr-alert-banner-info';
            banner.innerHTML = `
                <i class="fa-solid fa-note-sticky"></i>
                <div class="mgr-alert-content">
                    <span class="mgr-alert-text">${unresolvedShiftNotes} Unresolved shift note${unresolvedShiftNotes > 1 ? 's' : ''}</span>
                    <span class="mgr-alert-subtext">Action required from manager</span>
                </div>
            `;
            container.appendChild(banner);
        }

        // 4. Healthy Operations state (if total alerts count is 0)
        if (totalAlertsCount === 0) {
            const banner = document.createElement('div');
            banner.className = 'mgr-alert-banner';
            banner.style.cssText = 'background-color: var(--green-bg); border: 1.5px solid #a5d6a7; color: var(--green);';
            banner.innerHTML = `
                <i class="fa-solid fa-circle-check"></i>
                <div class="mgr-alert-content">
                    <span class="mgr-alert-text">Operations Healthy</span>
                    <span class="mgr-alert-subtext" style="color: var(--text-mid);">All stock levels are optimal and shift notes are completed.</span>
                </div>
            `;
            container.appendChild(banner);
        }
    }

    // Manager Clock Out handler
    const mgrClockOutBtn = document.querySelector('.mgr-clock-out');
    if (mgrClockOutBtn) {
        mgrClockOutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const confirmed = await PosDialog.confirm({
                title: 'End Manager Session',
                message: 'Are you sure you want to log out of the Manager Dashboard?',
                icon: 'fa-power-off',
                iconType: 'clockout',
                confirmText: 'Clock Out',
                cancelText: 'Stay Logged In',
                confirmType: 'confirm-danger'
            });

            if (confirmed) {
                localStorage.removeItem('earthbred_cart');
                window.location.href = BASE + '/login';
            }
        });
    }

    // Initialize Dashboard data fetch
    loadDashboardData();

});
