/* ============================================================
   inventory.js — Earthbred Inventory Management Frontend
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {

    const BASE = (function() {
        const pathname = window.location.pathname;
        const idx = pathname.toLowerCase().indexOf('/backend/public');
        return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
    })();
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function getAuthHeaders(extra = {}) {
        return {
            'X-CSRF-TOKEN': CSRF,
            'X-User-Id': localStorage.getItem('userId') || '',
            'X-User-Name': localStorage.getItem('userName') || '',
            'X-User-Role': localStorage.getItem('userRole') || '',
            ...extra
        };
    }

    // =========================================================
    // CLOCK & SHIFT INDICATOR
    // =========================================================
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const timeEl = document.getElementById('currentTimeDisplay');
        const shiftEl = document.getElementById('shiftLabel');
        const dotEl   = document.querySelector('.inv-shift-dot');

        if (timeEl) timeEl.textContent = `${h}:${m}`;

        const hour = now.getHours();
        if (shiftEl) {
            if (hour >= 6 && hour < 12) {
                shiftEl.textContent = 'Morning Shift — Check stocks';
                dotEl.style.backgroundColor = '#e5a000';
            } else if (hour >= 12 && hour < 18) {
                shiftEl.textContent = 'Afternoon Shift';
                dotEl.style.backgroundColor = '#137333';
            } else if (hour >= 18 && hour < 22) {
                shiftEl.textContent = 'Evening Shift — Check stocks';
                dotEl.style.backgroundColor = '#c5221f';
            } else {
                shiftEl.textContent = 'Off Hours';
                dotEl.style.backgroundColor = '#888';
            }
        }
    }
    updateClock();
    setInterval(updateClock, 60000);

    // Set date label
    const dateLabel = document.getElementById('invDateLabel');
    if (dateLabel) {
        const now = new Date();
        dateLabel.textContent = now.toLocaleDateString('en-PH', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    }

    // =========================================================
    // TOAST
    // =========================================================
    function showToast(msg, color = '#482f25') {
        let toast = document.getElementById('invToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'invToast';
            document.body.appendChild(toast);
        }
        toast.textContent = msg;
        toast.style.backgroundColor = color;
        toast.style.opacity = '1';
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.style.opacity = '0';
        }, 2500);
    }

    // =========================================================
    // CHART INSTANCES
    // =========================================================
    let doughnutChart = null;

    function buildDoughnutChart(inStock, lowStock, outStock) {
        const ctx = document.getElementById('stockDoughnutChart');
        if (!ctx) return;
        if (doughnutChart) doughnutChart.destroy();

        doughnutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['In Stock', 'Low Stock', 'Out of Stock'],
                datasets: [{
                    data: [inStock, lowStock, outStock],
                    backgroundColor: ['#137333', '#e5a000', '#c5221f'],
                    hoverBackgroundColor: ['#1a7a4a', '#f0b400', '#e53935'],
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { family: 'Poppins', size: 11, weight: '600' },
                            padding: 14,
                            color: '#444'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.label}: ${ctx.parsed} item(s)`
                        }
                    }
                }
            }
        });
    }

    // =========================================================
    // RULE-BASED STOCK ALERT ENGINE
    // =========================================================
    function generateAlerts(items) {
        const alertsList = document.getElementById('aiAlertsList');
        alertsList.innerHTML = '';

        const warnings = items.filter(i => i.quantity === 0 || i.quantity <= i.min_threshold);

        if (warnings.length === 0) {
            const li = document.createElement('li');
            li.className = 'inv-alert-item';
            li.innerHTML = '<strong>System Observation:</strong> All stock levels are healthy! No alerts detected.';
            alertsList.appendChild(li);
            return;
        }

        warnings.forEach(item => {
            const li = document.createElement('li');
            li.className = 'inv-alert-item';
            let msg = '';

            if (item.quantity === 0) {
                // Out of stock rules
                if (/milk/i.test(item.item_name)) {
                    msg = `<strong>Critical:</strong> ${item.item_name} is completely out. All milk-based drinks are affected. Restock immediately.`;
                } else if (/espresso|bean/i.test(item.item_name)) {
                    msg = `<strong>Critical:</strong> ${item.item_name} is out of stock. Coffee production is halted. Urgent restock required.`;
                } else if (/cup|lid|straw|packaging/i.test(item.item_name)) {
                    msg = `<strong>Operations Alert:</strong> ${item.item_name} is out. Orders cannot be served. Check packaging supplies immediately.`;
                } else if (/syrup/i.test(item.item_name)) {
                    msg = `<strong>Alert:</strong> ${item.item_name} is out of stock. Suggest offering alternative flavors to customers.`;
                } else {
                    msg = `<strong>Out of Stock:</strong> ${item.item_name} has 0 units left. Restock before next shift.`;
                }
            } else {
                // Low stock rules
                const percent = Math.round((item.quantity / item.min_threshold) * 100);
                const hour = new Date().getHours();
                const period = hour < 12 ? 'morning' : hour < 18 ? 'afternoon' : 'evening';

                if (/milk/i.test(item.item_name)) {
                    msg = `<strong>Low Stock:</strong> ${item.item_name} has only ${item.quantity} left. ${period === 'morning' ? 'Morning rush may deplete this quickly.' : 'Consider restocking before tomorrow.'}`;
                } else if (/espresso|bean/i.test(item.item_name)) {
                    msg = `<strong>Low Stock Warning:</strong> ${item.item_name} is at ${item.quantity} units. Core ingredient — schedule reorder now.`;
                } else if (/syrup/i.test(item.item_name)) {
                    msg = `<strong>Flavor Alert:</strong> ${item.item_name} has ${item.quantity} remaining (${percent}% of threshold). Inform staff of alternatives.`;
                } else if (/cup|lid|straw|packaging/i.test(item.item_name)) {
                    msg = `<strong>Supply Alert:</strong> ${item.item_name} down to ${item.quantity}. Estimated to last ${Math.round(item.quantity / 10)} shifts.`;
                } else {
                    msg = `<strong>Low Stock:</strong> ${item.item_name} is low (${item.quantity} units). Log an evening check and plan restock.`;
                }
            }

            li.innerHTML = msg;
            alertsList.appendChild(li);
        });
    }

    // =========================================================
    // LOW STOCK BANNERS
    // =========================================================
    function renderBanners(warnings) {
        const container = document.getElementById('lowStockBanners');
        container.innerHTML = '';

        warnings.forEach(item => {
            const div = document.createElement('div');
            if (item.quantity === 0) {
                div.className = 'inv-banner inv-banner-crit';
                div.innerHTML = `
                    <div class="inv-banner-left">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>${item.item_name}</span>
                    </div>
                    <span class="inv-banner-right">Out of stock</span>
                `;
            } else {
                div.className = 'inv-banner inv-banner-warn';
                div.innerHTML = `
                    <div class="inv-banner-left">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>${item.item_name}</span>
                    </div>
                    <span class="inv-banner-right">Only ${item.quantity} left</span>
                `;
            }
            container.appendChild(div);
        });
    }

    // =========================================================
    // RENDER TABLE
    // =========================================================
    function renderTable(items) {
        const tbody = document.getElementById('inventoryTableBody');
        tbody.innerHTML = '';

        if (items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="inv-loading-row">No items found.</td></tr>';
            return;
        }

        items.forEach(item => {
            let statusClass, statusLabel, statusIcon;
            if (item.quantity === 0) {
                statusClass = 'inv-status-out';
                statusLabel = 'Out of Stock';
                statusIcon  = 'fa-circle-xmark';
            } else if (item.quantity <= item.min_threshold) {
                statusClass = 'inv-status-low';
                statusLabel = 'Low Stock';
                statusIcon  = 'fa-triangle-exclamation';
            } else {
                statusClass = 'inv-status-in';
                statusLabel = 'In Stock';
                statusIcon  = 'fa-circle-check';
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><span class="inv-item-name">${item.item_name}</span></td>
                <td><span class="inv-category-tag">${item.category || (item.category_record ? item.category_record.name : 'General')}</span></td>
                <td><span class="inv-qty">${item.quantity}</span></td>
                <td>
                    <span class="inv-status-badge ${statusClass}">
                        <i class="fa-solid ${statusIcon}"></i> ${statusLabel}
                    </span>
                </td>
                <td><span class="inv-issue-tag">${item.latest_issue_type || '—'}</span></td>
                <td>
                    <div class="inv-actions-cell">
                        <button class="inv-action-btn inv-edit-stock-btn"
                            data-id="${item.id}" data-name="${item.item_name}"
                            data-qty="${item.quantity}" data-issue="${item.latest_issue_type || 'Morning Check'}">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </button>
                        <button class="inv-action-btn inv-archive-stock-btn" style="background-color: #6a3a30; color: white;"
                            data-id="${item.id}" data-name="${item.item_name}">
                            <i class="fa-solid fa-box-archive"></i> Archive
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        bindTableButtons();
    }

    // =========================================================
    // LOAD INVENTORY DATA
    // =========================================================
    let allItems = [];

    function loadInventoryData() {
        fetch(`${BASE}/api/inventory`, {
            headers: getAuthHeaders()
        })
            .then(r => {
                if (r.status === 401) {
                    window.location.href = BASE + '/login';
                    return null;
                }
                return r.json();
            })
            .then(items => {
                if (!items) return;
                if (Array.isArray(items)) {
                    allItems = items;
                    renderFullDashboard(items);
                } else if (items.message) {
                    showToast(items.message, '#c5221f');
                }
            })
            .catch(err => {
                console.error('Inventory fetch error:', err);
                showToast('Failed to load inventory data.', '#c5221f');
            });
    }

    function renderFullDashboard(items) {
        // KPI
        const total    = items.length;
        const outItems = items.filter(i => i.quantity === 0);
        const lowItems = items.filter(i => i.quantity > 0 && i.quantity <= i.min_threshold);
        const inItems  = items.filter(i => i.quantity > i.min_threshold);

        document.getElementById('kpiTotal').textContent   = total;
        document.getElementById('kpiInStock').textContent = inItems.length;
        document.getElementById('kpiLow').textContent     = lowItems.length;
        document.getElementById('kpiOut').textContent     = outItems.length;

        // Table
        renderTable(items);

        // Alerts
        generateAlerts(items);
        renderBanners([...outItems, ...lowItems]);

        // Charts
        buildDoughnutChart(inItems.length, lowItems.length, outItems.length);
    }

    loadInventoryData();

    // =========================================================
    // SEARCH FILTER
    // =========================================================
    const searchInput = document.getElementById('inventorySearch');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.toLowerCase().trim();
            const safeStr = v => (v == null ? '' : String(v)).toLowerCase();
            const filtered = q
                ? allItems.filter(i => {
                    return safeStr(i.item_name).includes(q) ||
                           safeStr(i.category).includes(q) ||
                           safeStr(i.category_record && i.category_record.name).includes(q);
                  })
                : allItems;
            renderTable(filtered);
        });
    }

    // =========================================================
    // BIND TABLE ACTION BUTTONS
    // =========================================================
    function bindTableButtons() {
        document.querySelectorAll('.inv-edit-stock-btn').forEach(btn => {
            btn.addEventListener('click', e => {
                e.stopPropagation();
                document.getElementById('editStockId').value        = btn.dataset.id;
                document.getElementById('editStockItemName').value  = btn.dataset.name;
                document.getElementById('editStockQty').value       = btn.dataset.qty;
                document.getElementById('editStockIssueType').value = btn.dataset.issue;
                document.getElementById('editStockNotes').value     = '';
                openModal('editStockModal');
            });
        });

        document.querySelectorAll('.inv-archive-stock-btn').forEach(btn => {
            btn.addEventListener('click', async e => {
                e.stopPropagation();
                const id = btn.dataset.id;
                const name = btn.dataset.name;
                
                const confirmed = await PosDialog.confirm({
                    title: 'Archive Inventory Item',
                    message: `Are you sure you want to archive "${name}"? It will be moved to the archive where you can restore it anytime.`,
                    icon: 'fa-box-archive',
                    iconType: 'warning',
                    confirmText: 'Archive Item',
                    cancelText: 'Cancel',
                    confirmType: 'confirm-warning'
                });

                if (confirmed) {
                    fetch(`${BASE}/api/inventory/${id}`, {
                        method: 'DELETE',
                        headers: getAuthHeaders()
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showToast(`"${name}" moved to archive! ✓`, '#137333');
                            loadInventoryData();
                        } else {
                            showToast(data.message || 'Error archiving item.', '#c5221f');
                        }
                    })
                    .catch(() => showToast('Server error. Please try again.', '#c5221f'));
                }
            });
        });
    }

    // =========================================================
    // ARCHIVE MODAL LOGIC
    // =========================================================
    const viewArchiveBtn = document.getElementById('viewArchiveBtn');
    const closeArchiveModalBtn = document.getElementById('closeArchiveModalBtn');

    if (viewArchiveBtn) {
        viewArchiveBtn.addEventListener('click', () => {
            loadArchivedItems();
            openModal('archiveModal');
        });
    }

    if (closeArchiveModalBtn) {
        closeArchiveModalBtn.addEventListener('click', () => {
            closeModal('archiveModal');
        });
    }

    function loadArchivedItems() {
        const tbody = document.getElementById('archivedTableBody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: #888;"><i class="fa-solid fa-spinner fa-spin"></i> Loading archived items...</td></tr>';

        fetch(`${BASE}/api/inventory/archived`, {
            headers: getAuthHeaders()
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.items) {
                renderArchivedTable(data.items);
            } else {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: #c5221f;">Failed to load archived items.</td></tr>';
            }
        })
        .catch(() => {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px; color: #c5221f;">Server error loading archived items.</td></tr>';
        });
    }

    function renderArchivedTable(items) {
        const tbody = document.getElementById('archivedTableBody');
        if (!tbody) return;

        if (items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 25px; color: #888;">No archived items found.</td></tr>';
            return;
        }

        tbody.innerHTML = '';
        items.forEach(item => {
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #eee';

            const delDate = new Date(item.deleted_at);
            const dateStr = delDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });

            tr.innerHTML = `
                <td style="padding: 10px 8px; font-weight: 600; color: #2c1a14;">${item.item_name}</td>
                <td style="padding: 10px 8px;"><span class="inv-category-tag">${item.category}</span></td>
                <td style="padding: 10px 8px; font-size: 0.85rem; color: #666;">${dateStr}</td>
                <td style="padding: 10px 8px; text-align: center;">
                    <button class="inv-action-btn" onclick="restoreArchivedItem(${item.id}, '${item.item_name.replace(/'/g, "\\'")}')" style="background: #137333; color: #fff; padding: 5px 12px; font-size: 0.8rem; border-radius: 6px; border: none; cursor: pointer;">
                        <i class="fa-solid fa-rotate-left"></i> Restore
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    window.restoreArchivedItem = async function(id, name) {
        const confirmed = await PosDialog.confirm({
            title: 'Restore Inventory Item',
            message: `Do you want to restore "${name}" back to the active inventory?`,
            icon: 'fa-rotate-left',
            iconType: 'info',
            confirmText: 'Restore Item',
            cancelText: 'Cancel',
            confirmType: 'confirm-primary'
        });

        if (!confirmed) return;

        fetch(`${BASE}/api/inventory/${id}/restore`, {
            method: 'POST',
            headers: getAuthHeaders()
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(`"${name}" restored to inventory! ✓`, '#137333');
                loadArchivedItems();
                loadInventoryData();
            } else {
                showToast(data.message || 'Error restoring item.', '#c5221f');
            }
        })
        .catch(() => showToast('Server error. Please try again.', '#c5221f'));
    };

    // =========================================================
    // MODAL HELPERS
    // =========================================================
    function openModal(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = 'flex';
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    }

    // Close buttons
    const closeAddStock = document.getElementById('closeAddStockModal');
    if (closeAddStock) closeAddStock.addEventListener('click', () => closeModal('addStockModal'));

    const closeEditStock = document.getElementById('closeEditStockModal');
    if (closeEditStock) closeEditStock.addEventListener('click', () => closeModal('editStockModal'));

    const closeAddItem = document.getElementById('closeAddItemModal');
    if (closeAddItem) closeAddItem.addEventListener('click', () => closeModal('addItemModal'));

    // Click outside to close
    ['addStockModal','editStockModal','addItemModal','archiveModal'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('click', e => {
                if (e.target.id === id) closeModal(id);
            });
        }
    });

    // Add Item Button
    document.getElementById('addItemBtn').addEventListener('click', () => {
        document.getElementById('addItemForm').reset();
        openModal('addItemModal');
    });

    // =========================================================
    // ADD STOCK FORM
    // =========================================================
    document.getElementById('addStockForm').addEventListener('submit', e => {
        e.preventDefault();
        const id  = document.getElementById('addStockId').value;
        const qty = parseInt(document.getElementById('addStockQty').value);

        fetch(`${BASE}/api/inventory/${id}/add`, {
            method: 'POST',
            headers: getAuthHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ quantity_added: qty })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeModal('addStockModal');
                showToast('Stock added successfully! ✓', '#137333');
                loadInventoryData();
            } else {
                showToast(data.message || 'Error adding stock.', '#c5221f');
            }
        })
        .catch(() => showToast('Server error. Please try again.', '#c5221f'));
    });

    // =========================================================
    // EDIT STOCK FORM
    // =========================================================
    document.getElementById('editStockForm').addEventListener('submit', e => {
        e.preventDefault();
        const id        = document.getElementById('editStockId').value;
        const qty       = parseInt(document.getElementById('editStockQty').value);
        const issueType = document.getElementById('editStockIssueType').value;
        const notes     = document.getElementById('editStockNotes').value;

        fetch(`${BASE}/api/inventory/${id}/edit`, {
            method: 'POST',
            headers: getAuthHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ quantity: qty, issue_type: issueType, notes: notes })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeModal('editStockModal');
                showToast('Stock updated successfully! ✓', '#137333');
                loadInventoryData();
            } else {
                showToast(data.message || 'Error updating stock.', '#c5221f');
            }
        })
        .catch(() => showToast('Server error. Please try again.', '#c5221f'));
    });

    // =========================================================
    // ADD NEW ITEM FORM
    // =========================================================
    document.getElementById('addItemForm').addEventListener('submit', e => {
        e.preventDefault();
        const payload = {
            item_name:     document.getElementById('newItemName').value.trim(),
            category:      document.getElementById('newItemCategory').value,
            quantity:      parseInt(document.getElementById('newItemQty').value),
            min_threshold: parseInt(document.getElementById('newItemThreshold').value)
        };

        fetch(`${BASE}/api/inventory`, {
            method: 'POST',
            headers: getAuthHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeModal('addItemModal');
                showToast(`"${payload.item_name}" added to inventory! ✓`, '#137333');
                loadInventoryData();
            } else {
                const msgs = data.errors
                    ? Object.values(data.errors).flat().join(' ')
                    : (data.message || 'Error adding item.');
                showToast(msgs, '#c5221f');
            }
        })
        .catch(() => showToast('Server error. Please try again.', '#c5221f'));
    });

    // =========================================================
    // EXPORT PDF
    // =========================================================
    document.getElementById('exportPdfBtn').addEventListener('click', () => {
        if (allItems.length === 0) {
            showToast('No inventory data to export.', '#c5221f');
            return;
        }

        // Fill PDF summary KPIs
        const outItems = allItems.filter(i => i.quantity === 0);
        const lowItems = allItems.filter(i => i.quantity > 0 && i.quantity <= i.min_threshold);
        const inItems  = allItems.filter(i => i.quantity > i.min_threshold);

        document.getElementById('pdfKpiTotal').textContent = allItems.length;
        document.getElementById('pdfKpiIn').textContent    = inItems.length;
        document.getElementById('pdfKpiLow').textContent   = lowItems.length;
        document.getElementById('pdfKpiOut').textContent   = outItems.length;

        const now = new Date();
        const dateStr = now.toLocaleDateString('en-PH', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
        const timeStr = now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('pdfReportDate').textContent   = `${dateStr} — ${timeStr}`;
        document.getElementById('pdfFooterDate').textContent   = `${dateStr} at ${timeStr}`;

        // Fill PDF table
        const pdfTbody = document.getElementById('pdfTableBody');
        pdfTbody.innerHTML = '';
        allItems.forEach((item, idx) => {
            let statusText;
            if (item.quantity === 0)              statusText = 'Out of Stock';
            else if (item.quantity <= item.min_threshold) statusText = 'Low Stock';
            else                                   statusText = 'In Stock';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${idx + 1}</td>
                <td><strong>${item.item_name}</strong></td>
                <td>${item.category}</td>
                <td><strong>${item.quantity}</strong></td>
                <td>${statusText}</td>
                <td>${item.latest_issue_type || '—'}</td>
            `;
            pdfTbody.appendChild(tr);
        });

        // Fill PDF alerts
        const pdfAlertsList = document.getElementById('pdfAlertsList');
        pdfAlertsList.innerHTML = '';
        const warnings = [...outItems, ...lowItems];

        if (warnings.length === 0) {
            const li = document.createElement('li');
            li.className = 'pdf-alert-row';
            li.style.background = '#e6f4ea';
            li.style.border = '1px solid #c3e6cb';
            li.style.color = '#137333';
            li.style.borderRadius = '6px';
            li.style.padding = '8px 12px';
            li.textContent = '✓ All stock levels are healthy. No alerts.';
            pdfAlertsList.appendChild(li);
        } else {
            warnings.forEach(item => {
                const li = document.createElement('li');
                li.className = item.quantity === 0
                    ? 'pdf-alert-row pdf-alert-crit'
                    : 'pdf-alert-row pdf-alert-warn';
                li.innerHTML = `<strong>${item.item_name}</strong> — ${item.quantity === 0
                    ? 'OUT OF STOCK. Immediate restock required.'
                    : `Low: Only ${item.quantity} unit(s) remaining.`}`;
                pdfAlertsList.appendChild(li);
            });
        }

        // Capture chart images
        const doughnutCanvas = document.getElementById('stockDoughnutChart');
        const pdfDoughnutImg = document.getElementById('pdfDoughnutImg');

        // Setup PDF template on-screen at top-left with fixed 750px width
        const pdfTemplate = document.getElementById('pdfReportTemplate');
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

        const filename = `earthbred_inventory_${now.toISOString().slice(0, 10)}.pdf`;

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
                .from(document.getElementById('pdfContent'))
                .save()
                .then(() => {
                    hidePdfTemplate();
                    showToast('PDF report exported successfully! ✓', '#137333');
                })
                .catch(err => {
                    console.error('Inventory PDF export error:', err);
                    hidePdfTemplate();
                    showToast('Error exporting PDF.', '#c5221f');
                });
        };

        if (doughnutCanvas && pdfDoughnutImg) {
            pdfDoughnutImg.onload = () => triggerGenerate();
            pdfDoughnutImg.onerror = () => triggerGenerate();
            pdfDoughnutImg.src = doughnutCanvas.toDataURL('image/png');
            if (pdfDoughnutImg.complete) {
                triggerGenerate();
            }
        } else {
            triggerGenerate();
        }
    });

}); // end DOMContentLoaded
