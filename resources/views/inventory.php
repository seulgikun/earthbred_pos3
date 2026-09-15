<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Earthbred - Inventory Management</title>
    <meta name="description"
        content="Earthbred Coffee Studio Inventory Management System. Monitor stock levels, track consumption, and generate formal inventory reports.">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/inventory.css') ?>?v=1.1.0">
    <link rel="stylesheet" href="<?= asset('css/pos-modal.css') ?>?v=1.1.0">
    <link rel="stylesheet" href="<?= asset('css/ios26-theme.css') ?>?v=1.1.0">
    <link rel="icon" type="image/png" href="<?= asset('favicon.png') ?>?v=3.0">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>?v=3.0">
    <?php if (isset($isManager) && $isManager): ?>
        <link rel="stylesheet" href="<?= asset('css/manager.css') ?>?v=1.1.0">
    <?php else: ?>
        <link rel="stylesheet" href="<?= asset('css/pos.css') ?>?v=1.1.0">
        <style>
            /* Override pos.css body centering — let content scroll freely */
            body {
                display: flex !important;
                flex-direction: row !important;
                height: 100vh !important;
                overflow: hidden !important;
                background-color: #ede9e0 !important;
                justify-content: initial !important;
                align-items: initial !important;
            }
            /* inv-app fills full viewport height as flex row */
            .inv-app {
                flex: 1 !important;
                display: flex !important;
                flex-direction: row !important;
                width: 100% !important;
                height: 100vh !important;
                overflow: hidden !important;
            }
            /* Desktop: sidebar fixed height, scrollable */
            @media (min-width: 1281px) {
                .sidebar {
                    width: 250px !important;
                    height: 100vh !important;
                    flex-shrink: 0 !important;
                    position: sticky !important;
                    top: 0 !important;
                    overflow-y: auto !important;
                    overflow-x: hidden !important;
                    background-color: #f5f0e6 !important;
                    border-right: 2px solid #e5d9c5 !important;
                }
            }
            /* Main inventory content scrolls vertically */
            .inv-main {
                flex: 1 !important;
                overflow-y: auto !important;
                overflow-x: hidden !important;
                height: 100vh !important;
            }
            /* Tablet/Mobile: inv-app becomes column layout */
            @media (max-width: 1280px) {
                body {
                    flex-direction: column !important;
                }
                .inv-app {
                    flex-direction: column !important;
                }
                .inv-main {
                    width: 100% !important;
                    height: 100vh !important;
                }
            }
        </style>
    <?php endif; ?>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- html2pdf -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        (function() {
            function checkAuth() {
                if (!localStorage.getItem('userId') || !localStorage.getItem('userRole')) {
                    window.location.replace('<?= url('') ?>/login');
                }
            }
            checkAuth();
            window.addEventListener('pageshow', function (event) {
                checkAuth();
                if (event.persisted) {
                    window.location.reload();
                }
            });
        })();
    </script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="inv-app">

        <!-- =========================================
         SIDEBAR
    ========================================= -->
        <?php if (isset($isManager) && $isManager): ?>
            <!-- Manager Sidebar Console -->
            <aside class="mgr-sidebar">
                <div class="mgr-logo-section" style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <img src="<?= asset('images/earthbred-logo-dark.png') ?>" alt="Earthbred" class="brand-logo-img">
                        <div>
                            <h1 class="mgr-logo-main">earthbred</h1>
                            <p class="mgr-logo-sub">Coffee Studio</p>
                        </div>
                    </div>
                    <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" title="Close Menu">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="mgr-user-profile">
                    <i class="fa-solid fa-circle-user mgr-profile-icon"></i>
                    <div class="mgr-user-info">
                        <p class="mgr-user-name">Juan Reyes</p>
                        <p class="mgr-user-id" id="sidebarUserRole">Manager</p>
                    </div>
                </div>

                <nav class="mgr-nav">
                    <h3 class="mgr-nav-heading">NAVIGATION</h3>
                    <ul class="mgr-nav-list">
                        <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager'">
                            <i class="fa-solid fa-chart-line mgr-nav-icon"></i> Dashboard
                        </li>
                    </ul>

                    <h3 class="mgr-nav-heading">OPERATIONS</h3>
                    <ul class="mgr-nav-list">
                        <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/products'">
                            <i class="fa-solid fa-tags mgr-nav-icon"></i> Product Management
                        </li>
                        <li class="mgr-nav-item"
                            onclick="window.location.href='<?= url('') ?>/manager/shift-notes'">
                            <i class="fa-solid fa-note-sticky mgr-nav-icon"></i> Shift Notes
                        </li>
                        <li class="mgr-nav-item"
                            onclick="window.location.href='<?= url('') ?>/manager/sales-report'">
                            <i class="fa-solid fa-file-invoice-dollar mgr-nav-icon"></i> Sales Reports
                        </li>
                        <li class="mgr-nav-item active"
                            onclick="window.location.href='<?= url('') ?>/manager/inventory'">
                            <i class="fa-solid fa-boxes-stacked mgr-nav-icon"></i> Inventory
                        </li>
                    </ul>

                    <h3 class="mgr-nav-heading">TOOLS</h3>
                    <ul class="mgr-nav-list">
                        <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/ai'">
                            <i class="fa-solid fa-robot mgr-nav-icon"></i> AI Gemini Assistant
                        </li>
                        <li class="mgr-nav-item owner-only-link" style="display: none !important;" onclick="window.location.href='<?= url('') ?>/manager/accounts'">
                            <i class="fa-solid fa-users mgr-nav-icon"></i> Account Management
                        </li>
                    </ul>
                </nav>

                <div class="mgr-sidebar-footer">
                    <div class="mgr-clock-out">
                        <i class="fa-solid fa-power-off"></i> Clock Out
                    </div>
                </div>
            </aside>
            <script>
                (function() {
                    const role = (localStorage.getItem('userRole') || '').toLowerCase();
                    const roleLabel = role.charAt(0).toUpperCase() + role.slice(1) || 'Manager';
                    if (role === 'owner') {
                        document.body.classList.add('is-owner');
                        document.querySelectorAll('.owner-only-link').forEach(el => el.style.setProperty('display', 'flex', 'important'));
                    } else {
                        document.body.classList.remove('is-owner');
                        document.querySelectorAll('.owner-only-link').forEach(el => el.style.setProperty('display', 'none', 'important'));
                    }
                    if (localStorage.getItem('userName')) {
                        const userNameEl = document.querySelector('.mgr-user-name');
                        if (userNameEl) userNameEl.textContent = localStorage.getItem('userName');
                    }
                    const roleEl = document.getElementById('sidebarUserRole');
                    if (roleEl) roleEl.textContent = roleLabel;
                })();
            </script>
        <?php else: ?>
            <!-- Cashier Sidebar -->
            <aside class="sidebar">
                <div class="logo-section" style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <img src="<?= asset('images/earthbred-logo-dark.png') ?>" alt="Earthbred" class="brand-logo-img">
                        <div>
                            <h1 class="logo-main">earthbred</h1>
                            <p class="logo-sub">Coffee Studio</p>
                        </div>
                    </div>
                    <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" title="Close Menu">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                
                <div class="user-profile">
                    <i class="fa-solid fa-circle-user profile-icon"></i>
                    <div class="user-info">
                        <p class="user-name">Aries Marolina</p>
                        <p class="user-id">Staff 001</p>
                    </div>
                </div>

                <nav class="menu-section">
                    <h3 class="menu-heading">MENU</h3>
                    <ul class="menu-list">
                        <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos'">
                            <span class="menu-icon">🍽️</span> All Items
                        </li>
                        <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=coffee'">
                            <span class="menu-icon">☕</span> Coffee
                        </li>
                        <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=non-coffee'">
                            <span class="menu-icon">🍵</span> Non-Coffee
                        </li>
                        <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=lemonade'">
                            <span class="menu-icon">🍹</span> Lemonade
                        </li>
                        <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=foods'">
                            <span class="menu-icon">🍲</span> Foods
                        </li>
                        <li class="menu-item" onclick="window.location.href='<?= url('') ?>/shift-notes'">
                            <span class="menu-icon">📝</span> Shift Notes
                        </li>
                        <li class="menu-item" onclick="window.location.href='<?= url('') ?>/queue'" style="border-top: 1px solid #e5d9c5; margin-top: 0.5rem; padding-top: 1rem;">
                            <span class="menu-icon">📋</span> Order Queuing
                        </li>
                        <li class="menu-item active" id="inventory-menu-item" onclick="window.location.href='<?= url('') ?>/inventory'" style="border-top: 1px solid #e5d9c5; margin-top: 0.5rem; padding-top: 1rem;">
                            <span class="menu-icon">📦</span> Inventory
                        </li>
                    </ul>
                </nav>

                <div class="clock-out">
                    <i class="fa-solid fa-power-off"></i> Clock Out
                </div>
            </aside>
        <?php endif; ?>

        <!-- =========================================
         MAIN CONTENT
    ========================================= -->
        <main class="inv-main">

            <!-- Top Header -->
            <header class="inv-header">
                <div class="inv-header-left" style="display: flex; align-items: center; gap: 0.75rem;">
                    <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Toggle Navigation Menu" type="button" onclick="if(typeof window.toggleGlobalSidebar==='function')window.toggleGlobalSidebar();" style="touch-action:manipulation;cursor:pointer;">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div>
                        <h2 class="inv-page-title">Inventory Management</h2>
                        <p class="inv-page-subtitle" id="invDateLabel">&nbsp;</p>
                    </div>
                </div>
                <div class="inv-header-actions">
                    <button class="inv-btn inv-btn-secondary" id="viewArchiveBtn" style="background:#533524; color:#fff;">
                        <i class="fa-solid fa-box-archive"></i> Archived Items
                    </button>
                    <button class="inv-btn inv-btn-secondary" id="addItemBtn">
                        <i class="fa-solid fa-plus"></i> Add Item
                    </button>
                    <button class="inv-btn inv-btn-primary" id="exportPdfBtn">
                        <i class="fa-solid fa-file-pdf"></i> Export PDF
                    </button>
                </div>
            </header>

            <!-- ---- KPI Cards ---- -->
            <section class="inv-kpi-row">
                <div class="inv-kpi-card">
                    <div class="inv-kpi-icon" style="background:linear-gradient(135deg,#6a3a30,#9c5a4a);">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <div class="inv-kpi-info">
                        <p class="inv-kpi-label">Total Items</p>
                        <p class="inv-kpi-value" id="kpiTotal">—</p>
                    </div>
                </div>
                <div class="inv-kpi-card">
                    <div class="inv-kpi-icon" style="background:linear-gradient(135deg,#1a7a4a,#2da56a);">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="inv-kpi-info">
                        <p class="inv-kpi-label">In Stock</p>
                        <p class="inv-kpi-value" id="kpiInStock">—</p>
                    </div>
                </div>
                <div class="inv-kpi-card">
                    <div class="inv-kpi-icon" style="background:linear-gradient(135deg,#b06000,#e5a000);">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="inv-kpi-info">
                        <p class="inv-kpi-label">Low Stock</p>
                        <p class="inv-kpi-value" id="kpiLow">—</p>
                    </div>
                </div>
                <div class="inv-kpi-card">
                    <div class="inv-kpi-icon" style="background:linear-gradient(135deg,#c5221f,#e84040);">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </div>
                    <div class="inv-kpi-info">
                        <p class="inv-kpi-label">Out of Stock</p>
                        <p class="inv-kpi-value" id="kpiOut">—</p>
                    </div>
                </div>
            </section>

            <!-- ---- Main Grid: Table + Alerts ---- -->
            <section class="inv-content-grid">

                <!-- LEFT: Table -->
                <div class="inv-table-panel">
                    <div class="inv-table-header">
                        <h3 class="inv-section-title"><i class="fa-solid fa-table-list"></i> Stock Register</h3>
                        <div class="inv-search-wrap">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="inventorySearch" placeholder="Search items..."
                                class="inv-search-input">
                        </div>
                    </div>
                    <div class="inv-table-container">
                        <table class="inv-table" id="inventoryTable">
                            <thead>
                                <tr>
                                    <th>ITEM</th>
                                    <th>CATEGORY</th>
                                    <th>QTY</th>
                                    <th>STATUS</th>
                                    <th>Consumption Log / Issue Type</th>
                                    <th style="text-align:center;">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody id="inventoryTableBody">
                                <tr>
                                    <td colspan="6" class="inv-loading-row"><i class="fa-solid fa-spinner fa-spin"></i>
                                        Loading inventory...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- RIGHT: Alerts + Charts -->
                <div class="inv-side-panel">

                    <!-- Stock Alert Assistant -->
                    <div class="inv-alert-card">
                        <div class="inv-alert-card-header">
                            <div class="inv-alert-title-group">
                                <i class="fa-solid fa-bell-concierge inv-alert-icon"></i>
                                <span class="inv-alert-title">Stock Alert Assistant</span>
                            </div>

                        </div>
                        <p class="inv-alert-intro">Real-time stock observations &amp; recommendations:</p>
                        <ul class="inv-alert-list" id="aiAlertsList">
                            <li class="inv-alert-item"><i class="fa-solid fa-spinner fa-spin"></i> Analyzing stock
                                levels...</li>
                        </ul>
                    </div>

                    <!-- Low Stock Banners -->
                    <div id="lowStockBanners" class="inv-banners-wrap"></div>

                    <!-- Doughnut Chart -->
                    <div class="inv-chart-card">
                        <h4 class="inv-chart-title"><i class="fa-solid fa-chart-pie"></i> Stock Status Overview</h4>
                        <div class="inv-chart-wrap">
                            <canvas id="stockDoughnutChart"></canvas>
                        </div>
                    </div>

                </div>
            </section>



        </main>
    </div>

    <!-- =========================================
     MODAL: ADD STOCK
========================================= -->
    <div class="inv-modal-overlay" id="addStockModal">
        <div class="inv-modal">
            <div class="inv-modal-header">
                <h3><i class="fa-solid fa-plus-circle"></i> Add Stock</h3>
                <button class="inv-modal-close" id="closeAddStockModal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="addStockForm" class="inv-modal-body">
                <input type="hidden" id="addStockId">
                <div class="inv-form-group">
                    <label>Item Name</label>
                    <input type="text" id="addStockItemName" class="inv-input" readonly>
                </div>
                <div class="inv-form-group">
                    <label>Quantity to Add</label>
                    <input type="number" id="addStockQty" class="inv-input" min="1" required placeholder="e.g. 10">
                </div>
                <button type="submit" class="inv-submit-btn">
                    <i class="fa-solid fa-check"></i> Save Stock
                </button>
            </form>
        </div>
    </div>

    <!-- =========================================
     MODAL: EDIT STOCK
========================================= -->
    <div class="inv-modal-overlay" id="editStockModal">
        <div class="inv-modal">
            <div class="inv-modal-header">
                <h3><i class="fa-solid fa-pen-to-square"></i> Edit Stock</h3>
                <button class="inv-modal-close" id="closeEditStockModal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="editStockForm" class="inv-modal-body">
                <input type="hidden" id="editStockId">
                <div class="inv-form-group">
                    <label>Item Name</label>
                    <input type="text" id="editStockItemName" class="inv-input" readonly>
                </div>
                <div class="inv-form-group">
                    <label>Corrected Quantity</label>
                    <input type="number" id="editStockQty" class="inv-input" min="0" required
                        placeholder="Enter correct count">
                </div>
                <div class="inv-form-group">
                    <label>Consumption Log / Issue Type</label>
                    <select id="editStockIssueType" class="inv-input inv-select" required>
                        <option value="Morning Check">☀️ Morning Check</option>
                        <option value="Evening Check">🌙 Evening Check</option>
                        <option value="Restocked">📦 Restocked</option>
                        <option value="Spillage">💧 Spillage</option>
                        <option value="Expired">⚠️ Expired</option>
                        <option value="Incorrect Entry">✏️ Incorrect Entry</option>
                    </select>
                </div>
                <div class="inv-form-group">
                    <label>Notes / Explanation <span style="color:#aaa;font-weight:400;">(Optional)</span></label>
                    <input type="text" id="editStockNotes" class="inv-input"
                        placeholder="e.g. Adjusted from morning audit">
                </div>
                <button type="submit" class="inv-submit-btn inv-submit-btn-edit">
                    <i class="fa-solid fa-check"></i> Update Stock
                </button>
            </form>
        </div>
    </div>

    <!-- =========================================
     MODAL: ADD NEW ITEM
========================================= -->
    <div class="inv-modal-overlay" id="addItemModal">
        <div class="inv-modal">
            <div class="inv-modal-header">
                <h3><i class="fa-solid fa-cubes"></i> Add New Inventory Item</h3>
                <button class="inv-modal-close" id="closeAddItemModal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="addItemForm" class="inv-modal-body">
                <div class="inv-form-group">
                    <label>Item Name</label>
                    <input type="text" id="newItemName" class="inv-input" required placeholder="e.g. Oat Milk">
                </div>
                <div class="inv-form-group">
                    <label>Category</label>
                    <select id="newItemCategory" class="inv-input inv-select" required>
                        <option value="">— Select Category —</option>
                        <option value="Milk">Milk</option>
                        <option value="Syrup">Syrup</option>
                        <option value="Coffee Beans">Coffee Beans</option>
                        <option value="Cups & Packaging">Cups &amp; Packaging</option>
                        <option value="Ingredients">Ingredients</option>
                        <option value="Supplies">Supplies</option>
                        <option value="Food">Food</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="inv-form-group">
                    <label>Initial Quantity</label>
                    <input type="number" id="newItemQty" class="inv-input" min="0" required placeholder="e.g. 50">
                </div>
                <div class="inv-form-group">
                    <label>Low Stock Threshold <span style="color:#aaa;font-weight:400;">(Alert when
                            below)</span></label>
                    <input type="number" id="newItemThreshold" class="inv-input" min="1" required placeholder="e.g. 10">
                </div>
                <button type="submit" class="inv-submit-btn">
                    <i class="fa-solid fa-plus"></i> Add to Inventory
                </button>
            </form>
        </div>
    </div>

    <!-- =========================================
     PDF REPORT TEMPLATE (hidden)
========================================= -->
    <div id="pdfReportTemplate" style="display:none;">
        <div id="pdfContent" class="pdf-report">
            <!-- Header -->
            <div class="pdf-header">
                <div class="pdf-brand">
                    <h1 class="pdf-brand-name">earthbred</h1>
                    <p class="pdf-brand-sub">Coffee Studio</p>
                </div>
                <div class="pdf-report-meta">
                    <h2 class="pdf-report-title">INVENTORY REPORT</h2>
                    <p class="pdf-report-date" id="pdfReportDate"></p>
                </div>
            </div>
            <div class="pdf-divider"></div>

            <!-- Summary KPIs -->
            <div class="pdf-summary-row">
                <div class="pdf-summary-box">
                    <p class="pdf-summary-label">Total Items</p>
                    <p class="pdf-summary-val" id="pdfKpiTotal">0</p>
                </div>
                <div class="pdf-summary-box pdf-summary-green">
                    <p class="pdf-summary-label">In Stock</p>
                    <p class="pdf-summary-val" id="pdfKpiIn">0</p>
                </div>
                <div class="pdf-summary-box pdf-summary-yellow">
                    <p class="pdf-summary-label">Low Stock</p>
                    <p class="pdf-summary-val" id="pdfKpiLow">0</p>
                </div>
                <div class="pdf-summary-box pdf-summary-red">
                    <p class="pdf-summary-label">Out of Stock</p>
                    <p class="pdf-summary-val" id="pdfKpiOut">0</p>
                </div>
            </div>

            <!-- Charts row -->
            <div class="pdf-charts-row">
                <div class="pdf-chart-box" style="width: 100%; max-width: 350px; margin: 0 auto;">
                    <h3 class="pdf-chart-label">Stock Status Overview</h3>
                    <img id="pdfDoughnutImg" class="pdf-chart-img" src="" alt="Doughnut Chart">
                </div>
            </div>

            <!-- Inventory Table -->
            <h3 class="pdf-table-heading">Stock Register</h3>
            <table class="pdf-table" id="pdfInventoryTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ITEM NAME</th>
                        <th>CATEGORY</th>
                        <th>QTY</th>
                        <th>STATUS</th>
                        <th>LAST ISSUE TYPE</th>
                    </tr>
                </thead>
                <tbody id="pdfTableBody"></tbody>
            </table>

            <!-- Low Stock Section -->
            <div id="pdfAlertsSection">
                <h3 class="pdf-table-heading">⚠️ Low Stock Alerts</h3>
                <ul id="pdfAlertsList" class="pdf-alerts-list"></ul>
            </div>

            <!-- Footer -->
            <div class="pdf-footer">
                <p>Generated by Earthbred POS System &bull; <span id="pdfFooterDate"></span></p>
                <p class="pdf-footer-note">This report is for internal use only. Please review and act on low-stock
                    alerts promptly.</p>
            </div>
        </div>
    </div>

    <!-- Archive Modal -->
    <div class="modal-overlay" id="archiveModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div class="modal-content" style="background: #fff; border-radius: 12px; padding: 24px; max-width: 650px; width: 90%; max-height: 80vh; display: flex; flex-direction: column;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 12px; margin-bottom: 16px;">
                <h3 class="modal-title" style="margin: 0; font-family: 'Montserrat', sans-serif; color: #2c1a14;"><i class="fa-solid fa-box-archive" style="color: #6a3a30;"></i> Archived Inventory Items</h3>
                <button class="close-modal-btn" id="closeArchiveModalBtn" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #888;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <p style="font-size: 0.85rem; color: #666; margin-top: 0;">Items that were soft-deleted or archived. Click <strong>Restore</strong> to return an item to active inventory.</p>
            <div style="flex: 1; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #eee; text-align: left;">
                            <th style="padding: 8px;">ITEM NAME</th>
                            <th style="padding: 8px;">CATEGORY</th>
                            <th style="padding: 8px;">ARCHIVED AT</th>
                            <th style="padding: 8px; text-align: center;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody id="archivedTableBody">
                        <tr><td colspan="4" style="text-align: center; padding: 20px; color: #888;">Loading archived items...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="<?= asset('js/pos-modal.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= asset('js/clock-out.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= asset('js/inventory.js') ?>?v=<?= time() ?>"></script>
    <?php if (!isset($isManager) || !$isManager): ?>
    <script>
        (function() {
            function removeSalesReport() {
                document.querySelectorAll('.menu-item, .menu-list li, .mgr-nav-item, nav li, aside li').forEach(function(li) {
                    if ((li.textContent || '').toLowerCase().includes('sales report')) {
                        li.remove();
                    }
                });
            }
            removeSalesReport();
            document.addEventListener('DOMContentLoaded', removeSalesReport);
            window.addEventListener('load', removeSalesReport);
            [50, 150, 300, 600, 1200, 2500].forEach(function(t) { setTimeout(removeSalesReport, t); });
            try {
                var observer = new MutationObserver(removeSalesReport);
                observer.observe(document.body || document.documentElement, { childList: true, subtree: true });
            } catch(e) {}
        })();
    </script>
    <?php endif; ?>
</body>

</html>
