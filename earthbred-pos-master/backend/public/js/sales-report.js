/* ============================================================
   sales-report.js — Earthbred Sales Report Console Logic
   Supports: Store Overview & Individual Cashier Sales Reports
   Protected: Strictly Manager & Owner
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    const BASE = (function() {
        const pathname = window.location.pathname;
        const idx = pathname.toLowerCase().indexOf('/backend/public');
        return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
    })();

    // Role verification
    const userRole = (localStorage.getItem('userRole') || '').toLowerCase();
    const isAuthorized = userRole === 'owner' || userRole === 'manager';
    if (!isAuthorized) {
        alert('Access Denied: You do not have permission to view sales reports.');
        window.location.href = BASE + '/pos';
        return;
    }

    // Active States
    let currentTab = 'overall'; // 'overall' | 'cashier'
    let salesData = null;
    let salesChartInstance = null;
    let paymentChartInstance = null;
    let categoryChartInstance = null;
    let activeRange = 'daily';

    // Cashier Report States
    let cashierData = null;
    let cashierChartInstance = null;
    let activeCashierRange = 'daily';
    let selectedCashierId = '';

    // Format helper
    const formatCurrency = (val) => '₱' + parseFloat(val || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });

    // =========================================================
    // TAB SWITCHER
    // =========================================================
    window.switchReportTab = function(tabName) {
        currentTab = tabName;
        const tabNavOverall = document.getElementById('tabNavOverall');
        const tabNavCashier = document.getElementById('tabNavCashier');
        const viewOverall = document.getElementById('viewOverallSales');
        const viewCashier = document.getElementById('viewCashierSales');

        if (tabName === 'overall') {
            tabNavOverall.classList.add('active');
            tabNavCashier.classList.remove('active');
            viewOverall.style.display = 'block';
            viewCashier.style.display = 'none';
        } else {
            tabNavCashier.classList.add('active');
            tabNavOverall.classList.remove('active');
            viewOverall.style.display = 'none';
            viewCashier.style.display = 'block';

            // Load cashier data if not yet loaded or on first visit
            if (!cashierData) {
                fetchCashierSalesData();
            }
        }
    };

    // =========================================================
    // STORE OVERVIEW: INITIALIZE CHART
    // =========================================================
    function renderChart(labels, values) {
        const ctx = document.getElementById('salesReportChart');
        if (!ctx) return;

        if (salesChartInstance) {
            salesChartInstance.destroy();
        }

        const barColor = '#c59958';
        const hoverColor = '#b68a52';

        salesChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue',
                    data: values,
                    backgroundColor: barColor,
                    hoverBackgroundColor: hoverColor,
                    borderRadius: 6,
                    borderWidth: 0,
                    barPercentage: 0.55
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => ` ${context.dataset.label}: ${formatCurrency(context.parsed.y)}`
                        },
                        titleFont: { family: 'Poppins', size: 12 },
                        bodyFont: { family: 'Poppins', size: 12 }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#8d786c',
                            font: { family: 'Montserrat', size: 11, weight: '700' }
                        }
                    },
                    y: {
                        grid: { color: '#f5edd6', drawBorder: false },
                        ticks: {
                            color: '#8d786c',
                            font: { family: 'Montserrat', size: 10, weight: '600' },
                            callback: function(value) {
                                if (value >= 1000) return '₱' + (value / 1000) + 'k';
                                return '₱' + value;
                            }
                        }
                    }
                }
            }
        });
    }

    // =========================================================
    // STORE OVERVIEW: RENDER TOP ITEMS
    // =========================================================
    function renderTopItemsTable(items) {
        const tbody = document.getElementById('topItemsTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (!items || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem; color: #8d786c;">No sales recorded for this period.</td></tr>';
            return;
        }

        items.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><span class="mgr-item-name">${item.product_name}</span></td>
                <td><span class="mgr-category-tag">${item.category}</span></td>
                <td><span class="mgr-qty-sold">${item.qty_sold}</span></td>
                <td><span class="mgr-revenue">${formatCurrency(item.revenue)}</span></td>
                <td>
                    <div class="mgr-share-bar-wrap">
                        <div class="mgr-share-bar-bg">
                            <div class="mgr-share-bar-fill" style="width: ${item.share_percent}%; background-color: #c59958;"></div>
                        </div>
                        <span class="mgr-share-percent">${item.share_percent}%</span>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // =========================================================
    // STORE OVERVIEW: SALES ANALYTICS RENDERING
    // =========================================================
    function renderPaymentChart(pm) {
        const ctx = document.getElementById('paymentMethodChart');
        if (!ctx) return;
        if (paymentChartInstance) {
            paymentChartInstance.destroy();
        }

        const cashRev = (pm && pm.cash_revenue) ? parseFloat(pm.cash_revenue) : 0;
        const gcashRev = (pm && pm.gcash_revenue) ? parseFloat(pm.gcash_revenue) : 0;
        const total = cashRev + gcashRev;

        paymentChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Cash', 'GCash'],
                datasets: [{
                    data: total > 0 ? [cashRev, gcashRev] : [1, 0],
                    backgroundColor: total > 0 ? ['#16a34a', '#0284c7'] : ['#e5e7eb', '#e5e7eb'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                if (total === 0) return ' No sales recorded';
                                return ` ${context.label}: ${formatCurrency(context.raw)}`;
                            }
                        }
                    }
                }
            }
        });

        const cashTotalEl = document.getElementById('analyticsCashTotal');
        const cashPctEl = document.getElementById('analyticsCashPercent');
        const gcashTotalEl = document.getElementById('analyticsGCashTotal');
        const gcashPctEl = document.getElementById('analyticsGCashPercent');

        if (cashTotalEl) cashTotalEl.textContent = formatCurrency(cashRev);
        if (cashPctEl) cashPctEl.textContent = (pm && pm.cash_percent !== undefined ? pm.cash_percent : 0) + '%';
        if (gcashTotalEl) gcashTotalEl.textContent = formatCurrency(gcashRev);
        if (gcashPctEl) gcashPctEl.textContent = (pm && pm.gcash_percent !== undefined ? pm.gcash_percent : 0) + '%';
    }

    function renderCategoryChartAndTable(categoriesList) {
        const ctx = document.getElementById('categoryShareChart');
        const tbody = document.getElementById('categoryTableBody');

        if (categoryChartInstance) {
            categoryChartInstance.destroy();
        }

        if (!categoriesList || categoriesList.length === 0) {
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 1.5rem; color: #8d786c;">No category transactions recorded for this period.</td></tr>';
            }
            return;
        }

        const labels = categoriesList.map(c => c.category.charAt(0).toUpperCase() + c.category.slice(1));
        const values = categoriesList.map(c => c.revenue);
        const palette = ['#f59e0b', '#d97706', '#b45309', '#059669', '#0284c7', '#8b5cf6', '#ec4899', '#6366f1'];

        if (ctx) {
            categoryChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: palette.slice(0, labels.length),
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 10,
                                font: { family: 'Outfit', size: 10, weight: '600' },
                                color: '#594a40',
                                padding: 6
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => ` ${context.label}: ${formatCurrency(context.raw)} (${categoriesList[context.dataIndex].share_percent}%)`
                            }
                        }
                    }
                }
            });
        }

        // Category breakdown table
        if (tbody) {
            tbody.innerHTML = '';
            categoriesList.forEach(c => {
                const tr = document.createElement('tr');
                const capName = c.category.charAt(0).toUpperCase() + c.category.slice(1);
                tr.innerHTML = `
                    <td><span style="font-weight: 700; color: #2c1a14;">${capName}</span></td>
                    <td style="text-align: right; font-weight: 600; color: #594a40;">${c.qty_sold}</td>
                    <td style="text-align: right; font-weight: 700; color: #b45309;">${formatCurrency(c.revenue)}</td>
                    <td style="text-align: right;">
                        <div class="mgr-share-bar-wrap" style="justify-content: flex-end;">
                            <div class="mgr-share-bar-bg" style="width: 110px;">
                                <div class="mgr-share-bar-fill" style="width: ${c.share_percent}%; background: linear-gradient(135deg, #f59e0b, #d97706);"></div>
                            </div>
                            <span class="mgr-share-percent" style="min-width: 38px; text-align: right;">${c.share_percent}%</span>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }
    }

    function renderPerformanceInsights(insights) {
        if (!insights) return;
        const peakWindow = document.getElementById('insightPeakWindow');
        const peakRev = document.getElementById('insightPeakRevenue');
        const topCat = document.getElementById('insightTopCategory');
        const topCatShare = document.getElementById('insightTopCategoryShare');
        const totalUnits = document.getElementById('insightTotalUnits');
        const topPayment = document.getElementById('insightTopPayment');
        const topPaymentShare = document.getElementById('insightTopPaymentShare');

        if (peakWindow) peakWindow.textContent = insights.peak_label || '—';
        if (peakRev) peakRev.textContent = insights.peak_revenue > 0 ? formatCurrency(insights.peak_revenue) : '₱0';
        if (topCat) topCat.textContent = insights.top_category || '—';
        if (topCatShare) topCatShare.textContent = `${insights.top_category_share || 0}% share`;
        if (totalUnits) totalUnits.textContent = (insights.total_items_sold || 0).toLocaleString();
        if (topPayment) topPayment.textContent = insights.top_payment_method || '—';
        if (topPaymentShare) topPaymentShare.textContent = `${insights.top_payment_percent || 0}% volume`;
    }

    function renderFinancialSummary(fs) {
        if (!fs) return;
        const grossEl = document.getElementById('kpiGrossSales');
        const discEl = document.getElementById('kpiDiscountsTotal');
        const discRateEl = document.getElementById('kpiDiscountRate');
        const netEl = document.getElementById('kpiNetSales');
        const basketEl = document.getElementById('kpiBasketDepth');
        const itemsSoldEl = document.getElementById('kpiTotalItemsSold');

        if (grossEl) grossEl.textContent = formatCurrency(fs.gross_sales || 0);
        if (discEl) discEl.textContent = '-' + formatCurrency(fs.discounts_total || 0);
        if (discRateEl) discRateEl.textContent = `${fs.discount_rate || 0}% of gross`;
        if (netEl) netEl.textContent = formatCurrency(fs.net_sales || 0);
        if (basketEl) basketEl.textContent = `${fs.avg_basket_items || 0} items`;
        if (itemsSoldEl) itemsSoldEl.textContent = `${(fs.total_items_sold || 0).toLocaleString()} items sold`;
    }

    function renderTargetProgress(tp) {
        if (!tp) return;
        const subEl = document.getElementById('targetGoalSubtitle');
        const badgeEl = document.getElementById('targetStatusBadge');
        const pctEl = document.getElementById('targetPercentLabel');
        const barEl = document.getElementById('targetProgressBar');
        const curEl = document.getElementById('targetCurrentVal');
        const remEl = document.getElementById('targetRemainingVal');

        if (subEl) subEl.textContent = `Period Target: ${formatCurrency(tp.goal || 0)}`;
        if (pctEl) pctEl.textContent = `${tp.percent || 0}%`;
        if (curEl) curEl.textContent = formatCurrency(tp.current || 0);
        if (remEl) remEl.textContent = formatCurrency(tp.remaining || 0);
        if (barEl) barEl.style.width = `${Math.min(100, tp.percent || 0)}%`;

        if (badgeEl) {
            if ((tp.percent || 0) >= 100) {
                badgeEl.innerHTML = '<i class="fa-solid fa-trophy"></i> Target Achieved!';
                badgeEl.style.background = 'rgba(16,185,129,0.2)';
                badgeEl.style.color = '#059669';
                badgeEl.style.borderColor = 'rgba(16,185,129,0.4)';
            } else if ((tp.percent || 0) >= 60) {
                badgeEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> On Track';
                badgeEl.style.background = 'rgba(16,185,129,0.15)';
                badgeEl.style.color = '#059669';
                badgeEl.style.borderColor = 'rgba(16,185,129,0.3)';
            } else {
                badgeEl.innerHTML = '<i class="fa-solid fa-hourglass-half"></i> In Progress';
                badgeEl.style.background = 'rgba(217,119,6,0.15)';
                badgeEl.style.color = '#b45309';
                badgeEl.style.borderColor = 'rgba(217,119,6,0.3)';
            }
        }
    }

    function renderDayParts(dayParts, topRushLabel) {
        if (!dayParts) return;
        const rushBadge = document.getElementById('topRushBadge');
        if (rushBadge && topRushLabel) {
            rushBadge.innerHTML = `<i class="fa-solid fa-fire"></i> Peak: ${topRushLabel}`;
        }

        const map = {
            'morning': { rev: 'dpMorningRevenue', ord: 'dpMorningOrders', pct: 'dpMorningPercent' },
            'lunch': { rev: 'dpLunchRevenue', ord: 'dpLunchOrders', pct: 'dpLunchPercent' },
            'afternoon': { rev: 'dpAfternoonRevenue', ord: 'dpAfternoonOrders', pct: 'dpAfternoonPercent' },
            'evening': { rev: 'dpEveningRevenue', ord: 'dpEveningOrders', pct: 'dpEveningPercent' }
        };

        Object.keys(map).forEach(key => {
            const data = dayParts[key];
            if (!data) return;
            const revEl = document.getElementById(map[key].rev);
            const ordEl = document.getElementById(map[key].ord);
            const pctEl = document.getElementById(map[key].pct);

            if (revEl) revEl.textContent = formatCurrency(data.revenue || 0);
            if (ordEl) ordEl.textContent = `${data.count || 0} orders`;
            if (pctEl) pctEl.textContent = `${data.percent || 0}%`;
        });
    }

    // =========================================================
    // STORE OVERVIEW: APPLY RANGE
    // =========================================================
    function applyRange(range) {
        activeRange = range;
        if (!salesData || !salesData[range]) return;

        const data = salesData[range];
        const periodLabel = document.getElementById('salesReportPeriodLabel');

        if (range === 'daily') {
            periodLabel.textContent = "Here's your overview for today.";
            document.getElementById('kpiSalesLabel').textContent = "Today's Sales";
            document.getElementById('kpiOrdersLabel').textContent = "Orders Today";
            document.getElementById('topItemsSectionTitle').textContent = "Top Selling Items Today";
        } else if (range === 'weekly') {
            periodLabel.textContent = "Here's your weekly overview (Last 7 Days).";
            document.getElementById('kpiSalesLabel').textContent = "Weekly Sales";
            document.getElementById('kpiOrdersLabel').textContent = "Orders This Week";
            document.getElementById('topItemsSectionTitle').textContent = "Top Selling Items This Week";
        } else if (range === 'monthly') {
            periodLabel.textContent = "Here's your monthly overview (Last 30 Days).";
            document.getElementById('kpiSalesLabel').textContent = "Monthly Sales";
            document.getElementById('kpiOrdersLabel').textContent = "Orders This Month";
            document.getElementById('topItemsSectionTitle').textContent = "Top Selling Items This Month";
        }

        document.getElementById('kpiSalesVal').textContent = formatCurrency(data.revenue);
        document.getElementById('kpiOrdersVal').textContent = data.orders_count;
        document.getElementById('kpiAOVVal').textContent = formatCurrency(data.aov);

        renderChart(data.chart.labels, data.chart.values);
        renderTopItemsTable(data.top_items);

        if (data.analytics) {
            renderPaymentChart(data.analytics.payment_methods);
            renderCategoryChartAndTable(data.analytics.categories);
            renderPerformanceInsights(data.analytics.insights);
            renderFinancialSummary(data.analytics.financial_summary);
            renderTargetProgress(data.analytics.target_progress);
            renderDayParts(data.analytics.day_parts, data.analytics.top_day_part);
        }
    }

    function fetchSalesData() {
        fetch(`${BASE}/api/manager/sales-data`)
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    salesData = res;
                    applyRange('daily');
                }
            })
            .catch(err => console.error('Error fetching sales data:', err));
    }

    fetchSalesData();

    // Store Overview range filter buttons
    document.querySelectorAll('#viewOverallSales .filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#viewOverallSales .filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            applyRange(this.getAttribute('data-range'));
        });
    });


    // =========================================================
    // CASHIER SALES REPORT: LOGIC & RENDERING
    // =========================================================
    function fetchCashierSalesData() {
        const tbody = document.getElementById('cashierReportTableBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 2rem; color: #5c4a40;"><i class="fa-solid fa-spinner fa-spin"></i> Loading cashier sales...</td></tr>`;
        }

        let url = `${BASE}/api/manager/cashier-sales?range=${activeCashierRange}`;
        if (selectedCashierId) {
            url += `&cashier_id=${selectedCashierId}`;
        }

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-User-Role': localStorage.getItem('userRole') || 'manager'
            }
        })
        .then(res => {
            if (res.status === 403) {
                throw new Error('Unauthorized access to cashier sales.');
            }
            return res.json();
        })
        .then(data => {
            if (!data.success) {
                console.error('Failed to load cashier sales:', data.message);
                return;
            }
            cashierData = data;
            renderCashierDashboard(data);
        })
        .catch(err => {
            console.error('Cashier sales error:', err);
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 2rem; color: #dc2626;"><i class="fa-solid fa-circle-exclamation"></i> ${err.message || 'Error loading cashier data.'}</td></tr>`;
            }
        });
    }

    function renderCashierDashboard(data) {
        // 1. Update Subtitle
        const periodSub = document.getElementById('cashierReportPeriodLabel');
        if (periodSub) {
            periodSub.textContent = `Displaying cashier transactions and sales for: ${data.period_title}`;
        }

        // 2. Populate Dropdown if needed
        const select = document.getElementById('cashierSelectFilter');
        if (select && select.options.length <= 1 && data.roster) {
            data.roster.forEach(u => {
                if (u.role && (u.role.toLowerCase() === 'manager' || u.role.toLowerCase() === 'owner')) return;
                const opt = document.createElement('option');
                opt.value = u.id;
                opt.textContent = `${u.name} (Cashier)`;
                select.appendChild(opt);
            });
        }

        // 3. Update KPI Cards
        const sum = data.summary;
        document.getElementById('kpiCashierTotalSales').textContent = formatCurrency(sum.total_cashier_sales);
        document.getElementById('kpiCashierSalesSub').textContent = `Total of ${sum.total_store_orders} store orders`;
        document.getElementById('kpiCashierActiveCount').textContent = sum.active_cashiers_count;
        document.getElementById('kpiCashierActiveSub').textContent = `Out of ${data.cashiers.length} staff accounts`;
        document.getElementById('kpiCashierTopName').textContent = sum.top_cashier_name;
        document.getElementById('kpiCashierTopSales').textContent = sum.top_cashier_sales > 0 ? formatCurrency(sum.top_cashier_sales) : '—';
        document.getElementById('kpiCashierAvgSales').textContent = formatCurrency(sum.avg_sales_per_cashier);

        // 4. Render Cashier Revenue Comparison Chart
        renderCashierChart(data.chart.labels, data.chart.values, data.chart.orders);

        // 5. Render Cashier Breakdown Table
        renderCashierTable(data.cashiers);
    }

    function renderCashierChart(labels, values, orders) {
        const ctx = document.getElementById('cashierRevenueChart');
        if (!ctx) return;

        if (cashierChartInstance) {
            cashierChartInstance.destroy();
        }

        // Vibrant gradient or palette for cashiers
        cashierChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Revenue (₱)',
                    data: values,
                    backgroundColor: '#d97706',
                    hoverBackgroundColor: '#b45309',
                    borderRadius: 8,
                    barThickness: 38,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const idx = ctx.dataIndex;
                                const ordCount = orders[idx] !== undefined ? orders[idx] : 0;
                                return [
                                    ` Revenue: ${formatCurrency(ctx.parsed.y)}`,
                                    ` Orders: ${ordCount}`
                                ];
                            }
                        },
                        titleFont: { family: 'Outfit', size: 13, weight: '700' },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12 }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#6b5a4e',
                            font: { family: 'Outfit', size: 12, weight: '700' }
                        }
                    },
                    y: {
                        grid: { color: 'rgba(210,195,180,0.3)', drawBorder: false },
                        ticks: {
                            color: '#8d786c',
                            font: { family: 'Montserrat', size: 11, weight: '600' },
                            callback: function(val) {
                                if (val >= 1000) return '₱' + (val / 1000) + 'k';
                                return '₱' + val;
                            }
                        }
                    }
                }
            }
        });
    }

    function renderCashierTable(cashiers) {
        const tbody = document.getElementById('cashierReportTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (!cashiers || cashiers.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; padding: 2rem; color: #8d786c;">No cashiers found matching criteria.</td></tr>`;
            return;
        }

        cashiers.forEach(c => {
            if (c.role && (c.role.toLowerCase() === 'manager' || c.role.toLowerCase() === 'owner')) return;
            const tr = document.createElement('tr');
            const initial = (c.cashier_name || 'C').charAt(0).toUpperCase();
            const roleBadgeClass = 'badge-cashier-role';

            tr.innerHTML = `
                <td>
                    <div class="cashier-user-cell">
                        <div class="cashier-avatar-badge">${initial}</div>
                        <div>
                            <div class="cashier-name-text">${c.cashier_name}</div>
                            <div class="cashier-email-text">${c.email}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="${roleBadgeClass}">${c.role}</span>
                </td>
                <td>
                    <span style="font-family: 'Montserrat', sans-serif; font-weight: 700; color: #2c1a14;">${c.orders_count}</span>
                    <span style="font-size: 0.75rem; color: #8d786c; display: block;">${c.orders_count > 0 ? (c.first_sale_at + ' - ' + c.last_sale_at) : 'No shift sales'}</span>
                </td>
                <td>
                    <span style="font-family: 'Montserrat', sans-serif; font-weight: 600; color: #15803d;">${formatCurrency(c.cash_sales)}</span>
                </td>
                <td>
                    <span style="font-family: 'Montserrat', sans-serif; font-weight: 600; color: #0369a1;">${formatCurrency(c.gcash_sales)}</span>
                </td>
                <td>
                    <span style="font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 1.05rem; color: #b45309;">${formatCurrency(c.total_sales)}</span>
                </td>
                <td>
                    <div class="mgr-share-bar-wrap">
                        <div class="mgr-share-bar-bg">
                            <div class="mgr-share-bar-fill" style="width: ${c.share_percent}%; background: linear-gradient(135deg, #f59e0b, #d97706);"></div>
                        </div>
                        <span class="mgr-share-percent">${c.share_percent}%</span>
                    </div>
                </td>
                <td style="text-align: center;">
                    <button class="view-cashier-btn" onclick="openCashierDetail(${c.cashier_id || 0}, '${c.cashier_name.replace(/'/g, "\\'")}')">
                        <i class="fa-solid fa-chart-pie"></i> Details
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // =========================================================
    // CASHIER FILTERS EVENT HANDLERS
    // =========================================================
    document.querySelectorAll('.cashier-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.cashier-filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            activeCashierRange = this.getAttribute('data-cashier-range');
            fetchCashierSalesData();
        });
    });

    const cashierSelect = document.getElementById('cashierSelectFilter');
    if (cashierSelect) {
        cashierSelect.addEventListener('change', function() {
            selectedCashierId = this.value;
            fetchCashierSalesData();
        });
    }

    const refreshBtn = document.getElementById('refreshCashierSalesBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            fetchCashierSalesData();
        });
    }


    // =========================================================
    // CASHIER DETAIL MODAL LOGIC
    // =========================================================
    window.openCashierDetail = function(cashierId, cashierName) {
        const modal = document.getElementById('cashierDetailModal');
        if (!modal) return;

        document.getElementById('modalCashierName').textContent = cashierName || 'Cashier Overview';
        document.getElementById('modalCashierEmail').textContent = 'Fetching transactions & metrics...';
        document.getElementById('modalCashierAvatar').textContent = (cashierName || 'C').charAt(0).toUpperCase();

        document.getElementById('modalCashierSales').textContent = '...';
        document.getElementById('modalCashierOrders').textContent = '...';
        document.getElementById('modalCashierCash').textContent = '...';
        document.getElementById('modalCashierGCash').textContent = '...';

        document.getElementById('modalTopProductsList').innerHTML = `<span style="color: #8d786c; font-size: 0.85rem;"><i class="fa-solid fa-spinner fa-spin"></i> Loading top products...</span>`;
        document.getElementById('modalRecentOrdersBody').innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 1.5rem; color: #8d786c;"><i class="fa-solid fa-spinner fa-spin"></i> Loading recent orders...</td></tr>`;

        modal.style.display = 'flex';

        fetch(`${BASE}/api/manager/cashier-sales/${cashierId}/details?range=${activeCashierRange}`, {
            headers: {
                'Accept': 'application/json',
                'X-User-Role': localStorage.getItem('userRole') || 'manager'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            document.getElementById('modalCashierName').textContent = data.cashier.name;
            document.getElementById('modalCashierEmail').textContent = `${data.cashier.email} • ${data.cashier.role}`;
            document.getElementById('modalCashierSales').textContent = formatCurrency(data.summary.total_sales);
            document.getElementById('modalCashierOrders').textContent = data.summary.orders_count;
            document.getElementById('modalCashierCash').textContent = formatCurrency(data.summary.cash_sales);
            document.getElementById('modalCashierGCash').textContent = formatCurrency(data.summary.gcash_sales);

            // Render top products pills
            const topProdContainer = document.getElementById('modalTopProductsList');
            topProdContainer.innerHTML = '';
            if (data.top_items && data.top_items.length > 0) {
                data.top_items.forEach(it => {
                    const pill = document.createElement('div');
                    pill.style.cssText = 'background: #faf5eb; border: 1px solid #e2d3be; border-radius: 9999px; padding: 6px 14px; display: inline-flex; align-items: center; gap: 8px; font-size: 0.82rem;';
                    pill.innerHTML = `
                        <span style="font-weight: 700; color: #2c1a14;">${it.product_name}</span>
                        <span style="background: #d97706; color: #fff; font-weight: 800; font-size: 0.72rem; padding: 2px 7px; border-radius: 9999px;">${it.qty_sold} sold</span>
                    `;
                    topProdContainer.appendChild(pill);
                });
            } else {
                topProdContainer.innerHTML = `<span style="color: #8d786c; font-size: 0.85rem;">No item sales recorded for this period.</span>`;
            }

            // Render recent orders
            const ordersTbody = document.getElementById('modalRecentOrdersBody');
            ordersTbody.innerHTML = '';
            if (data.recent_orders && data.recent_orders.length > 0) {
                data.recent_orders.forEach(ord => {
                    const tr = document.createElement('tr');
                    tr.style.borderBottom = '1px solid #f0ebe5';

                    const itemsList = ord.items.map(i => `${i.qty}x ${i.name}`).join(', ');

                    tr.innerHTML = `
                        <td style="padding: 10px; font-weight: 700; color: #2c1a14;">#${ord.id}</td>
                        <td style="padding: 10px; color: #6b5a4e; font-size: 0.8rem;">${ord.created_at}</td>
                        <td style="padding: 10px; max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #444;" title="${itemsList}">${itemsList || '—'}</td>
                        <td style="padding: 10px;"><span style="font-size: 0.75rem; font-weight: 700; padding: 2px 8px; border-radius: 9999px; background: ${ord.payment_method === 'CASH' ? '#dcfce7; color: #166534;' : '#e0f2fe; color: #075985;'}">${ord.payment_method}</span></td>
                        <td style="padding: 10px; text-align: right; font-weight: 700; color: #b45309;">${formatCurrency(ord.total)}</td>
                    `;
                    ordersTbody.appendChild(tr);
                });
            } else {
                ordersTbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 1.5rem; color: #8d786c;">No order history found for this cashier in this period.</td></tr>`;
            }
        })
        .catch(err => {
            console.error('Error fetching cashier details:', err);
        });
    };

    window.closeCashierModal = function() {
        const modal = document.getElementById('cashierDetailModal');
        if (modal) modal.style.display = 'none';
    };

    // Close modal on click outside box
    const modalOverlay = document.getElementById('cashierDetailModal');
    if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) closeCashierModal();
        });
    }


    // =========================================================
    // EXPORT PDF: DYNAMIC (OVERALL OR CASHIER)
    // =========================================================
    const exportBtn = document.getElementById('exportSalesReportPdfBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            const now = new Date();
            const dateStr = now.toLocaleDateString('en-PH', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
            const timeStr = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });

            document.getElementById('pdfGeneratedTime').textContent = `Generated: ${dateStr} at ${timeStr}`;
            document.getElementById('pdfFooterTimestamp').textContent = `${dateStr} • ${timeStr}`;

            let chartCanvas = null;
            const pdfChartImg = document.getElementById('pdfSalesChartImg');

            if (currentTab === 'cashier') {
                // EXPORT CASHIER SPECIFIC SALES REPORT
                if (!cashierData || !cashierData.cashiers) {
                    alert('No cashier report data available to export.');
                    return;
                }

                document.getElementById('pdfReportMainTitle').textContent = 'CASHIER SALES REPORT';
                document.getElementById('pdfReportPeriod').textContent = `${cashierData.period_title}`;

                document.getElementById('pdfKpiSalesLabel').textContent = 'Total Cashier Sales';
                document.getElementById('pdfKpiSalesVal').textContent = formatCurrency(cashierData.summary.total_cashier_sales);

                document.getElementById('pdfKpiOrdersLabel').textContent = 'Active Staff';
                document.getElementById('pdfKpiOrdersVal').textContent = cashierData.summary.active_cashiers_count;

                document.getElementById('pdfKpiThirdLabel').textContent = 'Top Cashier';
                document.getElementById('pdfKpiAOVVal').textContent = cashierData.summary.top_cashier_name;

                document.getElementById('pdfChartHeading').textContent = 'Cashier Sales Comparison';
                chartCanvas = document.getElementById('cashierRevenueChart');

                document.getElementById('pdfTableHeading').textContent = 'Cashier Breakdown';
                const thead = document.getElementById('pdfTableHeader');
                thead.innerHTML = `
                    <tr>
                        <th style="text-align: left;">Cashier Name</th>
                        <th style="text-align: left;">Role</th>
                        <th style="text-align: right;">Orders</th>
                        <th style="text-align: right;">Cash Sales</th>
                        <th style="text-align: right;">GCash Sales</th>
                        <th style="text-align: right;">Total Sales</th>
                        <th style="text-align: right;">Share</th>
                    </tr>
                `;

                const tbody = document.getElementById('pdfTopItemsBody');
                tbody.innerHTML = '';
                const displayCashiers = cashierData.cashiers.filter(c => !c.role || (c.role.toLowerCase() !== 'manager' && c.role.toLowerCase() !== 'owner'));

                if (displayCashiers.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 16px; color: #8d786c; font-weight: 500;">No cashier records found for this period.</td></tr>`;
                } else {
                    displayCashiers.forEach(c => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td style="font-weight: 700;">${c.cashier_name}</td>
                            <td>${c.role}</td>
                            <td style="text-align: right; font-weight: 600;">${c.orders_count}</td>
                            <td style="text-align: right;">${formatCurrency(c.cash_sales)}</td>
                            <td style="text-align: right;">${formatCurrency(c.gcash_sales)}</td>
                            <td style="text-align: right; font-weight: 800; color: #b45309;">${formatCurrency(c.total_sales)}</td>
                            <td style="text-align: right; font-weight: 700;">${c.share_percent}%</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }

            } else {
                // EXPORT OVERALL STORE SALES REPORT
                if (!salesData || !salesData[activeRange]) {
                    alert('No store sales report data available to export.');
                    return;
                }
                const data = salesData[activeRange];

                document.getElementById('pdfReportMainTitle').textContent = 'STORE SALES REPORT';
                document.getElementById('pdfReportPeriod').textContent = `${activeRange.toUpperCase()} REPORT`;

                document.getElementById('pdfKpiSalesLabel').textContent = activeRange === 'daily' ? "Today's Sales" : (activeRange === 'weekly' ? "Weekly Sales" : "Monthly Sales");
                document.getElementById('pdfKpiSalesVal').textContent = formatCurrency(data.revenue);

                document.getElementById('pdfKpiOrdersLabel').textContent = activeRange === 'daily' ? "Orders Today" : (activeRange === 'weekly' ? "Orders This Week" : "Orders This Month");
                document.getElementById('pdfKpiOrdersVal').textContent = data.orders_count;

                document.getElementById('pdfKpiThirdLabel').textContent = "Avg Order Value";
                document.getElementById('pdfKpiAOVVal').textContent = formatCurrency(data.aov);

                document.getElementById('pdfChartHeading').textContent = 'Revenue Graph';
                chartCanvas = document.getElementById('salesReportChart');

                document.getElementById('pdfTableHeading').textContent = 'Top Selling Items';
                const thead = document.getElementById('pdfTableHeader');
                thead.innerHTML = `
                    <tr>
                        <th style="text-align: left;">Rank</th>
                        <th style="text-align: left;">Item Name</th>
                        <th style="text-align: left;">Category</th>
                        <th style="text-align: right;">Qty Sold</th>
                        <th style="text-align: right;">Revenue</th>
                        <th style="text-align: right;">Share</th>
                    </tr>
                `;

                const tbody = document.getElementById('pdfTopItemsBody');
                tbody.innerHTML = '';
                if (data.top_items && data.top_items.length > 0) {
                    data.top_items.forEach((item, index) => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td style="font-weight: 700;">#${index + 1}</td>
                            <td style="font-weight: 600;">${item.product_name}</td>
                            <td>${item.category}</td>
                            <td style="text-align: right;">${item.qty_sold}</td>
                            <td style="text-align: right; font-weight: 600;">${formatCurrency(item.revenue)}</td>
                            <td style="text-align: right; font-weight: 700; color: #8d786c;">${item.share_percent}%</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 16px; color: #8d786c; font-weight: 500;">No item sales recorded for this period.</td></tr>`;
                }
            }

            // Setup PDF template on-screen at top-left with fixed 750px width
            const pdfTemplate = document.getElementById('pdfSalesReportTemplate');
            pdfTemplate.style.display = 'block';
            pdfTemplate.style.position = 'fixed';
            pdfTemplate.style.top = '0';
            pdfTemplate.style.left = '0';
            pdfTemplate.style.width = '750px';
            pdfTemplate.style.zIndex = '999999';
            pdfTemplate.style.background = '#ffffff';

            const hidePdfTemplate = () => {
                pdfTemplate.style.display = 'none';
                pdfTemplate.style.position = '';
                pdfTemplate.style.top = '';
                pdfTemplate.style.left = '';
                pdfTemplate.style.width = '';
                pdfTemplate.style.zIndex = '';
                pdfTemplate.style.background = '';
            };

            const filename = `earthbred_${currentTab === 'cashier' ? 'cashier_sales' : 'store_sales'}_report_${now.toISOString().slice(0, 10)}.pdf`;

            const opt = {
                margin:       [0.3, 0.3, 0.3, 0.3],
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, logging: false, scrollY: 0, scrollX: 0 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' },
                pagebreak:    { mode: ['css', 'legacy'] }
            };

            let generated = false;
            const triggerGenerate = () => {
                if (generated) return;
                generated = true;
                html2pdf()
                    .set(opt)
                    .from(document.getElementById('pdfSalesContent'))
                    .save()
                    .then(() => {
                        hidePdfTemplate();
                    })
                    .catch(err => {
                        console.error('PDF export error:', err);
                        hidePdfTemplate();
                        alert('Error exporting sales report PDF.');
                    });
            };

            if (chartCanvas && pdfChartImg) {
                pdfChartImg.onload = () => triggerGenerate();
                pdfChartImg.onerror = () => triggerGenerate();
                pdfChartImg.src = chartCanvas.toDataURL('image/png');
                if (pdfChartImg.complete) {
                    triggerGenerate();
                }
            } else {
                triggerGenerate();
            }
        });
    }

});
