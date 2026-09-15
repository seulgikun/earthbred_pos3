<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthbred - Sales Report Console</title>
    <meta name="description" content="Earthbred Coffee Studio Manager Sales Report Console. Track store overview and individual cashier sales reports.">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Montserrat:wght@400;600;700;800;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/manager.css') ?>?v=1.0.0">
    <link rel="stylesheet" href="<?= asset('css/pos-modal.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/ios26-theme.css') ?>?v=1.0.1">
    <link rel="icon" type="image/png" href="<?= asset('favicon.png') ?>?v=3.0">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>?v=3.0">
    <style>
        /* ===== Liquid Glass & Layout overrides ===== */
        .mgr-main { background: transparent !important; }
        .mgr-header { background: linear-gradient(135deg, rgba(255,255,255,0.85) 0%, rgba(255,255,255,0.7) 100%) !important; backdrop-filter: blur(28px) saturate(190%) !important; border-bottom: 1px solid rgba(255,255,255,0.8) !important; }
        
        /* Navigation View Switcher Tabs */
        .report-type-nav {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            background: rgba(255,255,255,0.65);
            padding: 0.4rem;
            border-radius: 9999px;
            width: fit-content;
            border: 1px solid rgba(210,195,180,0.4);
            box-shadow: 0 4px 18px rgba(44,30,20,0.04);
        }
        .report-nav-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.65rem 1.4rem;
            border-radius: 9999px;
            border: none;
            background: transparent;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 0.92rem;
            color: #6b5a4e;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.34,1.56,0.64,1);
        }
        .report-nav-tab:hover {
            color: #2c1a14;
            background: rgba(255,255,255,0.8);
        }
        .report-nav-tab.active {
            background: linear-gradient(135deg, #533524 0%, #3d271d 60%, #26160e 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 6px 18px rgba(45,26,17,0.35);
            transform: scale(1.02);
        }
        .report-pill-badge {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 9999px;
            background: rgba(255,255,255,0.25);
            color: inherit;
            font-weight: 800;
            letter-spacing: 0.3px;
        }

        /* Filter buttons */
        .filter-buttons-container { display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; }
        .filter-btn {
            padding: 0.55rem 1.15rem;
            border-radius: 9999px;
            border: 1.5px solid rgba(210,195,180,0.5);
            background: rgba(255,255,255,0.72);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 600;
            font-size: 0.85rem;
            color: #594a40;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(0.34,1.56,0.64,1);
        }
        .filter-btn:hover { background: rgba(255,255,255,0.9) !important; transform: translateY(-2px); }
        .filter-btn.active {
            background: linear-gradient(135deg, #533524 0%, #3d271d 60%, #26160e 100%) !important;
            color: #fff !important;
            border-color: transparent !important;
            box-shadow: 0 8px 20px rgba(45,26,17,0.35);
        }

        .cashier-select-dropdown {
            padding: 0.55rem 1.2rem;
            border-radius: 9999px;
            border: 1.5px solid rgba(210,195,180,0.6);
            background: rgba(255,255,255,0.85);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 600;
            font-size: 0.85rem;
            color: #3b2d24;
            outline: none;
            cursor: pointer;
        }

        #exportSalesReportPdfBtn {
            background: linear-gradient(135deg, #533524 0%, #3d271d 60%, #26160e 100%) !important;
            color: #ffffff !important;
            border-radius: 8px !important;
            font-family: 'Outfit', sans-serif !important;
            font-weight: 700 !important;
            padding: 0.65rem 1.35rem !important;
            border: 1px solid #26160e !important;
            box-shadow: 0 4px 12px rgba(45,26,17,0.25) !important;
            transition: transform 0.22s ease !important;
            cursor: pointer !important;
        }
        #exportSalesReportPdfBtn:hover { transform: translateY(-2px) scale(1.02) !important; }

        .mgr-kpi-card, .mgr-panel, .mgr-bottom-panel {
            background: linear-gradient(135deg, rgba(255,255,255,0.84) 0%, rgba(255,255,255,0.68) 100%) !important;
            backdrop-filter: blur(28px) saturate(190%) !important;
            -webkit-backdrop-filter: blur(28px) saturate(190%) !important;
            border: 1px solid rgba(255,255,255,0.75) !important;
            border-radius: 24px !important;
            padding: 1.5rem !important;
            box-shadow: 0 14px 36px -10px rgba(44,30,20,0.1), inset 0 1px 0 rgba(255,255,255,0.9) !important;
        }
        .mgr-panel-title { font-family: 'Outfit', sans-serif !important; font-weight: 800 !important; font-size: 1.15rem !important; color: #1c1612 !important; margin-bottom: 1rem !important; }
        .mgr-table { background: transparent !important; border-radius: 18px !important; }
        .mgr-table th { background: rgba(245,237,228,0.7) !important; font-family: 'Outfit', sans-serif !important; font-weight: 700 !important; color: #6b5a4e !important; }

        /* Cashier Table Specifics */
        .cashier-user-cell { display: flex; align-items: center; gap: 12px; }
        .cashier-avatar-badge {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #533524 0%, #3d271d 60%, #26160e 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.1rem;
            box-shadow: 0 4px 10px rgba(45,26,17,0.25);
            flex-shrink: 0;
        }
        .cashier-name-text { font-family: 'Outfit', sans-serif; font-weight: 700; color: #2c1a14; font-size: 0.98rem; }
        .cashier-email-text { font-size: 0.78rem; color: #8d786c; }
        .badge-cashier-role {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            background: #eef2ff;
            color: #4f46e5;
            border: 1px solid #c7d2fe;
        }
        .badge-manager-role {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .view-cashier-btn {
            background: linear-gradient(135deg, rgba(245,158,11,0.12) 0%, rgba(217,119,6,0.18) 100%);
            border: 1px solid rgba(217,119,6,0.3);
            color: #b45309;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 6px 14px;
            border-radius: 9999px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .view-cashier-btn:hover {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(217,119,6,0.3);
        }

        /* Modal styling */
        .cashier-modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(28,22,18,0.55);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            padding: 1.5rem;
        }
        .cashier-modal-box {
            background: #ffffff;
            border-radius: 28px;
            width: 100%;
            max-width: 820px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 60px -15px rgba(0,0,0,0.3);
            padding: 2rem;
            position: relative;
            animation: modalPopIn 0.28s cubic-bezier(0.34,1.56,0.64,1);
        }
        @keyframes modalPopIn {
            from { opacity: 0; transform: scale(0.92); }
            to { opacity: 1; transform: scale(1); }
        }

        /* ===== SALES REPORT PDF STYLES (html2pdf) ===== */
        #pdfSalesReportTemplate {
            display: none;
        }
        #pdfSalesContent {
            width: 750px !important;
            max-width: 750px !important;
            min-width: 750px !important;
            box-sizing: border-box !important;
            font-family: 'Poppins', sans-serif !important;
            padding: 20px 24px !important;
            background: #ffffff !important;
            color: #2c1a14 !important;
            margin: 0 !important;
        }
        #pdfSalesContent .pdf-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 2px solid #eadeca;
        }
        #pdfSalesContent .pdf-brand-name {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 1.7rem;
            letter-spacing: -1.5px;
            color: #2c1a14;
            line-height: 1;
            margin: 0;
        }
        #pdfSalesContent .pdf-brand-sub {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 0.5rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #8d786c;
            margin: 2px 0 0 0;
        }
        #pdfSalesContent .pdf-report-meta {
            text-align: right;
        }
        #pdfSalesContent .pdf-report-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 1rem;
            color: #482f25;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin: 0;
        }
        #pdfSalesContent .pdf-report-period {
            font-size: 0.72rem;
            color: #8d786c;
            font-weight: 700;
            margin: 2px 0 0 0;
            text-transform: uppercase;
        }
        #pdfSalesContent .pdf-report-date {
            font-size: 0.66rem;
            color: #8d786c;
            margin: 2px 0 0 0;
        }
        #pdfSalesContent .pdf-kpi-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 10px;
        }
        #pdfSalesContent .pdf-kpi-card {
            background-color: #faf5eb;
            border: 1.5px solid #eadeca;
            border-radius: 6px;
            padding: 6px 10px;
            text-align: center;
        }
        #pdfSalesContent .pdf-kpi-label {
            font-size: 0.6rem;
            font-weight: 700;
            color: #8d786c;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 2px 0;
        }
        #pdfSalesContent .pdf-kpi-val {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 1.25rem;
            color: #2c1a14;
            margin: 0;
            line-height: 1.1;
        }
        #pdfSalesContent .pdf-chart-card {
            border: 1.5px solid #eadeca;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 10px;
            background: #faf8f4;
            text-align: center;
        }
        #pdfSalesContent .pdf-chart-heading {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 0.76rem;
            color: #482f25;
            text-transform: uppercase;
            margin: 0 0 6px 0;
            text-align: left;
            letter-spacing: 0.8px;
        }
        #pdfSalesContent .pdf-chart-img {
            width: 100%;
            max-height: 130px;
            object-fit: contain;
            margin: 0 auto;
            display: block;
        }
        #pdfSalesContent .pdf-table-card {
            border: 1.5px solid #eadeca;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 10px;
            background: #ffffff;
        }
        #pdfSalesContent .pdf-table-heading {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 0.78rem;
            color: #482f25;
            text-transform: uppercase;
            margin: 0 0 6px 0;
            letter-spacing: 0.8px;
            padding-bottom: 3px;
            border-bottom: 1.5px solid #eadeca;
        }
        #pdfSalesContent .pdf-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.71rem;
        }
        #pdfSalesContent .pdf-table th {
            background: #f5edd6;
            color: #6b5a4e;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 0.61rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 8px;
            border-bottom: 1.5px solid #eadeca;
        }
        #pdfSalesContent .pdf-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #f2e9db;
            color: #2c1a14;
            vertical-align: middle;
        }
        #pdfSalesContent .pdf-table tr:nth-child(even) td {
            background: #fdfbf7;
        }
        #pdfSalesContent .pdf-footer {
            border-top: 1.5px solid #eadeca;
            padding-top: 8px;
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.64rem;
            color: #8d786c;
        }

        /* Page-break avoidance rules */
        #pdfSalesContent tr,
        #pdfSalesContent .pdf-kpi-row,
        #pdfSalesContent .pdf-kpi-card,
        #pdfSalesContent #pdfAnalyticsBox,
        #pdfSalesContent .pdf-chart-card,
        #pdfSalesContent .pdf-table-card,
        #pdfSalesContent .pdf-footer {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    </style>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <!-- html2pdf -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="mgr-app">

    <!-- =========================================
         SIDEBAR
    ========================================= -->
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
                <p class="mgr-user-name">Manager</p>
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
                <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/shift-notes'">
                    <i class="fa-solid fa-note-sticky mgr-nav-icon"></i> Shift Notes
                </li>
                <li class="mgr-nav-item active">
                    <i class="fa-solid fa-file-invoice-dollar mgr-nav-icon"></i> Sales Reports
                </li>
                <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/inventory'">
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
        // STRICT ACCESS GUARD: ONLY MANAGER OR OWNER
        (function() {
            function checkAuth() {
                const role = (localStorage.getItem('userRole') || '').toLowerCase();
                const uid = localStorage.getItem('userId');
                if (!role || !uid) {
                    window.location.replace('<?= url('') ?>/login');
                    return false;
                }
                if (role !== 'owner' && role !== 'manager') {
                    window.location.replace('<?= url('') ?>/pos');
                    return false;
                }
                return true;
            }
            if (!checkAuth()) return;
            window.addEventListener('pageshow', function (event) {
                if (!checkAuth() || event.persisted) {
                    window.location.reload();
                }
            });
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

    <!-- =========================================
         MAIN CONTENT AREA
    ========================================= -->
    <main class="mgr-main">
        <!-- Top header -->
        <header class="mgr-header" style="justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; display: flex; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Toggle Navigation Menu" type="button" onclick="if(typeof window.toggleGlobalSidebar==='function')window.toggleGlobalSidebar();" style="touch-action:manipulation;cursor:pointer;">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h2 class="mgr-page-title">Sales Report Console</h2>
                <span class="report-pill-badge" style="background: rgba(217,119,6,0.15); color: #b45309; border: 1px solid rgba(217,119,6,0.3); padding: 4px 12px; font-size: 0.78rem;">
                    <i class="fa-solid fa-shield-halved"></i> Manager Protected
                </span>
            </div>
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <button class="resolve-btn" id="shiftSummaryBtn" style="display: flex; align-items: center; gap: 8px; background: #2c1a14; color: #fdfaf6; border: 1px solid rgba(197,153,88,0.4);">
                    <i class="fa-solid fa-file-waveform"></i> End-of-Shift Summary
                </button>
                <button class="resolve-btn" id="exportSalesReportPdfBtn" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-file-pdf"></i> Export PDF Report
                </button>
            </div>
        </header>

        <!-- Inner Content Scroll Area -->
        <div class="mgr-content">

            <!-- Report Navigation View Switcher -->
            <div class="report-type-nav">
                <button class="report-nav-tab active" id="tabNavOverall" onclick="switchReportTab('overall')">
                    <i class="fa-solid fa-store"></i> Store Overview
                </button>
                <button class="report-nav-tab" id="tabNavCashier" onclick="switchReportTab('cashier')">
                    <i class="fa-solid fa-user-tag"></i> Cashier Sales Report
                    <span class="report-pill-badge" style="background: rgba(0,0,0,0.12);">Exclusive</span>
                </button>
            </div>

            <!-- ====================================================
                 VIEW 1: STORE OVERALL SALES
            ==================================================== -->
            <div id="viewOverallSales">
                <!-- Greeting / Title Section -->
                <div class="mgr-greeting-wrap">
                    <h3 class="mgr-greeting-title">Overall Store Sales</h3>
                    <p class="mgr-greeting-sub" id="salesReportPeriodLabel">Here's your overview for today.</p>
                </div>

                <!-- Filter Buttons -->
                <div class="filter-buttons-container" style="margin-bottom: 1.25rem;">
                    <button class="filter-btn active" data-range="daily">Daily</button>
                    <button class="filter-btn" data-range="weekly">Weekly</button>
                    <button class="filter-btn" data-range="monthly">Monthly</button>
                </div>


                <!-- Primary KPI Row (3 Cards) -->
                <section class="mgr-kpi-row">
                    <!-- Sales -->
                    <div class="mgr-kpi-card">
                        <p class="mgr-kpi-label" id="kpiSalesLabel">Today's Sales</p>
                        <p class="mgr-kpi-value mgr-kpi-val" id="kpiSalesVal">₱0</p>
                        <p class="mgr-kpi-trend" id="kpiSalesSubtext" style="color: var(--text-muted);">Total Revenue</p>
                    </div>
                    <!-- Orders -->
                    <div class="mgr-kpi-card">
                        <p class="mgr-kpi-label" id="kpiOrdersLabel">Orders Today</p>
                        <p class="mgr-kpi-value mgr-kpi-val" id="kpiOrdersVal">0</p>
                        <p class="mgr-kpi-trend" id="kpiOrdersSubtext" style="color: var(--text-muted);">Total Orders</p>
                    </div>
                    <!-- Average Order Value -->
                    <div class="mgr-kpi-card">
                        <p class="mgr-kpi-label" id="kpiAOVLabel">Avg Order Value</p>
                        <p class="mgr-kpi-value mgr-kpi-val" id="kpiAOVVal">₱0</p>
                        <p class="mgr-kpi-trend" id="kpiAOVSubtext" style="color: var(--text-muted);">Average basket size</p>
                    </div>
                </section>

                <!-- Financial Health & Basket Depth Row (4 Cards) -->
                <section class="mgr-kpi-row" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); margin-top: 1rem;">
                    <!-- Gross Sales -->
                    <div class="mgr-kpi-card" style="border-left: 4px solid #f59e0b;">
                        <p class="mgr-kpi-label">Gross Sales</p>
                        <p class="mgr-kpi-value mgr-kpi-val" id="kpiGrossSales" style="font-size: 1.35rem;">₱0</p>
                        <p class="mgr-kpi-trend" style="color: var(--text-muted);">Pre-discount volume</p>
                    </div>
                    <!-- Total Discounts -->
                    <div class="mgr-kpi-card" style="border-left: 4px solid #ef4444;">
                        <p class="mgr-kpi-label">Discounts Deducted</p>
                        <p class="mgr-kpi-value mgr-kpi-val" id="kpiDiscountsTotal" style="font-size: 1.35rem; color: #dc2626;">-₱0</p>
                        <p class="mgr-kpi-trend" id="kpiDiscountRate" style="color: #ef4444;">0% of gross</p>
                    </div>
                    <!-- Net Sales -->
                    <div class="mgr-kpi-card" style="border-left: 4px solid #16a34a;">
                        <p class="mgr-kpi-label">Net Sales</p>
                        <p class="mgr-kpi-value mgr-kpi-val" id="kpiNetSales" style="font-size: 1.35rem; color: #16a34a;">₱0</p>
                        <p class="mgr-kpi-trend" style="color: #16a34a;">Collected revenue</p>
                    </div>
                    <!-- Basket Depth -->
                    <div class="mgr-kpi-card" style="border-left: 4px solid #6366f1;">
                        <p class="mgr-kpi-label">Avg Basket Depth</p>
                        <p class="mgr-kpi-value mgr-kpi-val" id="kpiBasketDepth" style="font-size: 1.35rem; color: #4f46e5;">0 items</p>
                        <p class="mgr-kpi-trend" id="kpiTotalItemsSold" style="color: var(--text-muted);">0 total items</p>
                    </div>
                </section>

                <!-- Revenue Chart Card -->
                <div class="mgr-panel" style="margin-top: 1rem;">
                    <h4 class="mgr-panel-title">Revenue Chart</h4>
                    <div class="mgr-chart-container" style="height: 280px; position: relative;">
                        <canvas id="salesReportChart"></canvas>
                    </div>
                </div>

                <!-- Day-Part / Shift Rush Performance -->
                <div class="mgr-panel" style="margin-top: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            <h4 class="mgr-panel-title" style="margin-bottom: 2px;">Day-Part & Shift Rush Distribution</h4>
                            <p style="font-size: 0.78rem; color: #8d786c; margin: 0;">Sales distribution across morning, lunch, afternoon, and evening trading windows.</p>
                        </div>
                        <span class="report-pill-badge" id="topRushBadge" style="background: rgba(217,119,6,0.15); color: #b45309; border: 1px solid rgba(217,119,6,0.3); font-size: 0.8rem; padding: 4px 12px;">
                            <i class="fa-solid fa-fire"></i> Peak: Morning Rush
                        </span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 0.85rem;" id="dayPartsGrid">
                        <!-- Morning -->
                        <div class="day-part-card" style="background: rgba(255,255,255,0.7); border: 1px solid rgba(220,200,180,0.45); border-radius: 16px; padding: 0.9rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                                <span style="font-size: 0.82rem; font-weight: 700; color: #594a40;"><i class="fa-solid fa-mug-saucer" style="color: #d97706; margin-right: 6px;"></i> Morning (8am-12pm)</span>
                                <span id="dpMorningPercent" style="font-size: 0.8rem; font-weight: 800; color: #b45309;">0%</span>
                            </div>
                            <p id="dpMorningRevenue" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 800; color: #2c1a14; margin: 0 0 2px 0;">₱0</p>
                            <p id="dpMorningOrders" style="font-size: 0.74rem; color: #8d786c; margin: 0;">0 orders</p>
                        </div>
                        <!-- Lunch -->
                        <div class="day-part-card" style="background: rgba(255,255,255,0.7); border: 1px solid rgba(220,200,180,0.45); border-radius: 16px; padding: 0.9rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                                <span style="font-size: 0.82rem; font-weight: 700; color: #594a40;"><i class="fa-solid fa-utensils" style="color: #d97706; margin-right: 6px;"></i> Lunch (12pm-3pm)</span>
                                <span id="dpLunchPercent" style="font-size: 0.8rem; font-weight: 800; color: #b45309;">0%</span>
                            </div>
                            <p id="dpLunchRevenue" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 800; color: #2c1a14; margin: 0 0 2px 0;">₱0</p>
                            <p id="dpLunchOrders" style="font-size: 0.74rem; color: #8d786c; margin: 0;">0 orders</p>
                        </div>
                        <!-- Afternoon -->
                        <div class="day-part-card" style="background: rgba(255,255,255,0.7); border: 1px solid rgba(220,200,180,0.45); border-radius: 16px; padding: 0.9rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                                <span style="font-size: 0.82rem; font-weight: 700; color: #594a40;"><i class="fa-solid fa-cloud-sun" style="color: #d97706; margin-right: 6px;"></i> Afternoon (3pm-6pm)</span>
                                <span id="dpAfternoonPercent" style="font-size: 0.8rem; font-weight: 800; color: #b45309;">0%</span>
                            </div>
                            <p id="dpAfternoonRevenue" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 800; color: #2c1a14; margin: 0 0 2px 0;">₱0</p>
                            <p id="dpAfternoonOrders" style="font-size: 0.74rem; color: #8d786c; margin: 0;">0 orders</p>
                        </div>
                        <!-- Evening -->
                        <div class="day-part-card" style="background: rgba(255,255,255,0.7); border: 1px solid rgba(220,200,180,0.45); border-radius: 16px; padding: 0.9rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                                <span style="font-size: 0.82rem; font-weight: 700; color: #594a40;"><i class="fa-solid fa-moon" style="color: #d97706; margin-right: 6px;"></i> Evening (6pm-10pm)</span>
                                <span id="dpEveningPercent" style="font-size: 0.8rem; font-weight: 800; color: #b45309;">0%</span>
                            </div>
                            <p id="dpEveningRevenue" style="font-family: 'Outfit', sans-serif; font-size: 1.15rem; font-weight: 800; color: #2c1a14; margin: 0 0 2px 0;">₱0</p>
                            <p id="dpEveningOrders" style="font-size: 0.74rem; color: #8d786c; margin: 0;">0 orders</p>
                        </div>
                    </div>
                </div>

                <!-- Hourly Peak Trading Heatmap -->
                <div class="mgr-panel" style="margin-top: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            <h4 class="mgr-panel-title" style="margin-bottom: 2px;"><i class="fa-solid fa-chart-simple" style="color: #c59958; margin-right: 6px;"></i> Hourly Trading Heatmap</h4>
                            <p style="font-size: 0.78rem; color: #8d786c; margin: 0;">Visual peak order velocity and revenue density from 8:00 AM to 10:00 PM.</p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.72rem; color: #8d786c;">
                            <span>Low</span>
                            <span style="display: inline-block; width: 12px; height: 12px; background: rgba(197, 153, 88, 0.15); border-radius: 3px;"></span>
                            <span style="display: inline-block; width: 12px; height: 12px; background: rgba(197, 153, 88, 0.45); border-radius: 3px;"></span>
                            <span style="display: inline-block; width: 12px; height: 12px; background: rgba(197, 153, 88, 0.75); border-radius: 3px;"></span>
                            <span style="display: inline-block; width: 12px; height: 12px; background: #966b33; border-radius: 3px;"></span>
                            <span>Peak</span>
                        </div>
                    </div>
                    <div id="hourlyHeatmapGrid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(68px, 1fr)); gap: 8px; margin-top: 0.5rem;">
                        <!-- Generated via JS -->
                        <div style="text-align: center; color: #8d786c; grid-column: 1 / -1; padding: 1.5rem;">Loading hourly trading heatmap...</div>
                    </div>
                </div>

                <!-- =============================================
                     SALES ANALYTICS & DEEP-DIVE INSIGHTS
                ============================================= -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1rem; margin-top: 1rem;">
                    
                    <!-- 1. Payment Methods Breakdown (Cash vs GCash) -->
                    <div class="mgr-panel">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                            <h4 class="mgr-panel-title" style="margin-bottom: 0;">Payment Method Split</h4>
                            <span class="report-pill-badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; border: 1px solid rgba(16, 185, 129, 0.3);">
                                <i class="fa-solid fa-wallet"></i> Cash vs GCash
                            </span>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                            <div style="width: 140px; height: 140px; position: relative; margin: 0 auto;">
                                <canvas id="paymentMethodChart"></canvas>
                            </div>
                            <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; gap: 0.6rem;">
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.45rem 0.7rem; background: rgba(22, 163, 74, 0.08); border-radius: 12px; border-left: 4px solid #16a34a;">
                                    <div>
                                        <p style="font-size: 0.72rem; font-weight: 700; color: #15803d; margin: 0; text-transform: uppercase;">Cash Sales</p>
                                        <p id="analyticsCashTotal" style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.05rem; color: #14532d; margin: 0;">₱0</p>
                                    </div>
                                    <span id="analyticsCashPercent" style="font-size: 0.88rem; font-weight: 800; color: #16a34a;">0%</span>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.45rem 0.7rem; background: rgba(2, 132, 199, 0.08); border-radius: 12px; border-left: 4px solid #0284c7;">
                                    <div>
                                        <p style="font-size: 0.72rem; font-weight: 700; color: #0369a1; margin: 0; text-transform: uppercase;">GCash (Digital)</p>
                                        <p id="analyticsGCashTotal" style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.05rem; color: #075985; margin: 0;">₱0</p>
                                    </div>
                                    <span id="analyticsGCashPercent" style="font-size: 0.88rem; font-weight: 800; color: #0284c7;">0%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Category Share Chart -->
                    <div class="mgr-panel">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                            <h4 class="mgr-panel-title" style="margin-bottom: 0;">Category Sales Share</h4>
                            <span class="report-pill-badge" style="background: rgba(217, 119, 6, 0.15); color: #d97706; border: 1px solid rgba(217, 119, 6, 0.3);">
                                <i class="fa-solid fa-chart-pie"></i> Revenue Share
                            </span>
                        </div>
                        <div style="height: 140px; position: relative;">
                            <canvas id="categoryShareChart"></canvas>
                        </div>
                    </div>

                    <!-- 3. Key Sales Insights & Peak Hours -->
                    <div class="mgr-panel" style="display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                <h4 class="mgr-panel-title" style="margin-bottom: 0;">Performance Insights</h4>
                                <span class="report-pill-badge" style="background: rgba(99, 102, 241, 0.15); color: #4f46e5; border: 1px solid rgba(99, 102, 241, 0.3);">
                                    <i class="fa-solid fa-bolt"></i> Key Highlights
                                </span>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem;">
                                <div style="background: rgba(250, 245, 235, 0.8); border: 1px solid rgba(230, 215, 200, 0.6); border-radius: 12px; padding: 0.6rem;">
                                    <p style="font-size: 0.7rem; color: #8d786c; font-weight: 700; text-transform: uppercase; margin: 0 0 2px 0;"><i class="fa-regular fa-clock"></i> Peak Activity</p>
                                    <p id="insightPeakWindow" style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 0.98rem; color: #2c1a14; margin: 0;">—</p>
                                    <span id="insightPeakRevenue" style="font-size: 0.72rem; color: #b45309; font-weight: 600;">₱0</span>
                                </div>
                                <div style="background: rgba(250, 245, 235, 0.8); border: 1px solid rgba(230, 215, 200, 0.6); border-radius: 12px; padding: 0.6rem;">
                                    <p style="font-size: 0.7rem; color: #8d786c; font-weight: 700; text-transform: uppercase; margin: 0 0 2px 0;"><i class="fa-solid fa-trophy"></i> Top Category</p>
                                    <p id="insightTopCategory" style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 0.98rem; color: #2c1a14; margin: 0; text-transform: capitalize;">—</p>
                                    <span id="insightTopCategoryShare" style="font-size: 0.72rem; color: #15803d; font-weight: 600;">0% share</span>
                                </div>
                                <div style="background: rgba(250, 245, 235, 0.8); border: 1px solid rgba(230, 215, 200, 0.6); border-radius: 12px; padding: 0.6rem;">
                                    <p style="font-size: 0.7rem; color: #8d786c; font-weight: 700; text-transform: uppercase; margin: 0 0 2px 0;"><i class="fa-solid fa-boxes-packing"></i> Units Sold</p>
                                    <p id="insightTotalUnits" style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 0.98rem; color: #2c1a14; margin: 0;">0</p>
                                    <span style="font-size: 0.72rem; color: #8d786c;">Total items</span>
                                </div>
                                <div style="background: rgba(250, 245, 235, 0.8); border: 1px solid rgba(230, 215, 200, 0.6); border-radius: 12px; padding: 0.6rem;">
                                    <p style="font-size: 0.7rem; color: #8d786c; font-weight: 700; text-transform: uppercase; margin: 0 0 2px 0;"><i class="fa-solid fa-credit-card"></i> Top Method</p>
                                    <p id="insightTopPayment" style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 0.98rem; color: #2c1a14; margin: 0;">—</p>
                                    <span id="insightTopPaymentShare" style="font-size: 0.72rem; color: #0284c7; font-weight: 600;">0%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Breakdown Table -->
                <section class="mgr-panel" style="margin-top: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <h4 class="mgr-panel-title" style="margin-bottom: 0;">Category Sales Summary</h4>
                        <span class="report-pill-badge" style="background: rgba(44, 26, 20, 0.08); color: #2c1a14;">
                            <i class="fa-solid fa-layer-group"></i> Department Performance
                        </span>
                    </div>
                    <div class="mgr-table-container">
                        <table class="mgr-table">
                            <thead>
                                <tr>
                                    <th>CATEGORY</th>
                                    <th style="text-align: right;">QTY SOLD</th>
                                    <th style="text-align: right;">REVENUE</th>
                                    <th style="width: 220px; text-align: right;">REVENUE SHARE</th>
                                </tr>
                            </thead>
                            <tbody id="categoryTableBody">
                                <tr><td colspan="4" style="text-align: center; padding: 1.5rem; color: #8d786c;"><i class="fa-solid fa-spinner fa-spin"></i> Loading category analytics...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Top Selling Items Card -->
                <section class="mgr-bottom-panel" style="margin-top: 1rem; margin-bottom: 2rem;">
                    <h4 class="mgr-panel-title" id="topItemsSectionTitle">Top Selling Items Today</h4>
                    <div class="mgr-table-container">
                        <table class="mgr-table">
                            <thead>
                                <tr>
                                    <th>ITEM</th>
                                    <th>CATEGORY</th>
                                    <th>QTY SOLD</th>
                                    <th>REVENUE</th>
                                    <th style="width: 220px;">SHARE</th>
                                </tr>
                            </thead>
                            <tbody id="topItemsTableBody">
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 2rem; color: #5c4a40;">
                                        <i class="fa-solid fa-spinner fa-spin"></i> Loading top selling items...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- ====================================================
                 VIEW 2: CASHIER SPECIFIC SALES REPORT
            ==================================================== -->
            <div id="viewCashierSales" style="display: none;">
                <!-- Cashier Greeting / Title -->
                <div class="mgr-greeting-wrap">
                    <h3 class="mgr-greeting-title">Cashier Sales & Performance Report</h3>
                    <p class="mgr-greeting-sub" id="cashierReportPeriodLabel">Specific sales metrics, transactions, and contributions per cashier.</p>
                </div>

                <!-- Cashier Filters Bar -->
                <div class="filter-buttons-container" style="margin-bottom: 1.25rem; justify-content: space-between;">
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <button class="cashier-filter-btn filter-btn active" data-cashier-range="daily">Today's Shift</button>
                        <button class="cashier-filter-btn filter-btn" data-cashier-range="weekly">Last 7 Days</button>
                        <button class="cashier-filter-btn filter-btn" data-cashier-range="monthly">Last 30 Days</button>
                        <button class="cashier-filter-btn filter-btn" data-cashier-range="all">All-Time</button>
                    </div>
                    <div style="display: flex; gap: 0.75rem; align-items: center;">
                        <label for="cashierSelectFilter" style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 0.85rem; color: #6b5a4e;">Cashier:</label>
                        <select id="cashierSelectFilter" class="cashier-select-dropdown">
                            <option value="">All Cashiers (Comparison)</option>
                        </select>
                        <button id="refreshCashierSalesBtn" class="filter-btn" title="Refresh cashier sales" style="padding: 0.55rem 0.85rem;">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </button>
                    </div>
                </div>

                <!-- Cashier KPI Row (4 Cards) -->
                <section class="mgr-kpi-row" style="grid-template-columns: repeat(4, 1fr);">
                    <!-- Total Cashier Sales -->
                    <div class="mgr-kpi-card">
                        <p class="mgr-kpi-label">Total Cashier Revenue</p>
                        <p class="mgr-kpi-value mgr-kpi-val" id="kpiCashierTotalSales">₱0</p>
                        <p class="mgr-kpi-trend" id="kpiCashierSalesSub" style="color: var(--text-muted);">Across all selected staff</p>
                    </div>
                    <!-- Active Cashiers -->
                    <div class="mgr-kpi-card">
                        <p class="mgr-kpi-label">Active Cashiers</p>
                        <p class="mgr-kpi-value mgr-kpi-val" id="kpiCashierActiveCount">0</p>
                        <p class="mgr-kpi-trend" id="kpiCashierActiveSub" style="color: var(--text-muted);">On shift with transactions</p>
                    </div>
                    <!-- Top Performer -->
                    <div class="mgr-kpi-card">
                        <p class="mgr-kpi-label">Top Selling Cashier</p>
                        <p class="mgr-kpi-value mgr-kpi-val" id="kpiCashierTopName" style="font-size: 1.4rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">—</p>
                        <p class="mgr-kpi-trend" id="kpiCashierTopSales" style="color: #d97706; font-weight: 700;">₱0</p>
                    </div>
                    <!-- Avg Sales per Cashier -->
                    <div class="mgr-kpi-card">
                        <p class="mgr-kpi-label">Avg Sales / Cashier</p>
                        <p class="mgr-kpi-value mgr-kpi-val" id="kpiCashierAvgSales">₱0</p>
                        <p class="mgr-kpi-trend" style="color: var(--text-muted);">Per active cashier</p>
                    </div>
                </section>

                <!-- Cashier Comparison Chart -->
                <div class="mgr-panel" style="margin-top: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h4 class="mgr-panel-title" style="margin-bottom: 0;">Cashier Revenue Comparison</h4>
                        <span style="font-size: 0.82rem; color: #8d786c; font-weight: 600;">Sales Volume by Staff Member</span>
                    </div>
                    <div class="mgr-chart-container" style="height: 270px; position: relative;">
                        <canvas id="cashierRevenueChart"></canvas>
                    </div>
                </div>

                <!-- Cashier Sales Breakdown Table -->
                <section class="mgr-bottom-panel" style="margin-top: 1.25rem; margin-bottom: 2rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h4 class="mgr-panel-title" style="margin-bottom: 0;">Individual Cashier Performance</h4>
                        <span style="font-size: 0.8rem; color: #8d786c;">Real-time breakdown of cash, GCash, and sales share</span>
                    </div>
                    <div class="mgr-table-container">
                        <table class="mgr-table">
                            <thead>
                                <tr>
                                    <th>CASHIER</th>
                                    <th>ROLE</th>
                                    <th>ORDERS</th>
                                    <th>CASH SALES</th>
                                    <th>GCASH SALES</th>
                                    <th>TOTAL SALES</th>
                                    <th style="width: 180px;">STORE SHARE</th>
                                    <th style="text-align: center; width: 140px;">ACTION</th>
                                </tr>
                            </thead>
                            <tbody id="cashierReportTableBody">
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 2rem; color: #5c4a40;">
                                        <i class="fa-solid fa-spinner fa-spin"></i> Loading cashier sales...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

        </div>
    </main>

</div>

<!-- =========================================
     CASHIER DETAIL TRANSACTION MODAL
========================================= -->
<div id="cashierDetailModal" class="cashier-modal-overlay">
    <div class="cashier-modal-box">
        <!-- Modal Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #f0ebe5; padding-bottom: 1rem; margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div id="modalCashierAvatar" class="cashier-avatar-badge" style="width: 52px; height: 52px; font-size: 1.3rem;">C</div>
                <div>
                    <h3 id="modalCashierName" style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.35rem; color: #2c1a14; margin: 0;">Cashier Name</h3>
                    <p id="modalCashierEmail" style="font-size: 0.85rem; color: #8d786c; margin: 2px 0 0 0;">cashier@earthbred.com</p>
                </div>
            </div>
            <button onclick="closeCashierModal()" style="background: rgba(0,0,0,0.06); border: none; border-radius: 50%; width: 36px; height: 36px; cursor: pointer; color: #5c4a40; font-size: 1rem; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal KPIs -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 1.5rem;">
            <div style="background: #faf5eb; border: 1px solid #ebd9c5; border-radius: 16px; padding: 14px; text-align: center;">
                <span style="font-size: 0.75rem; color: #8d786c; font-weight: 700; text-transform: uppercase;">Total Sales</span>
                <p id="modalCashierSales" style="font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 1.25rem; color: #2c1a14; margin: 4px 0 0 0;">₱0</p>
            </div>
            <div style="background: #faf5eb; border: 1px solid #ebd9c5; border-radius: 16px; padding: 14px; text-align: center;">
                <span style="font-size: 0.75rem; color: #8d786c; font-weight: 700; text-transform: uppercase;">Orders</span>
                <p id="modalCashierOrders" style="font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 1.25rem; color: #2c1a14; margin: 4px 0 0 0;">0</p>
            </div>
            <div style="background: #faf5eb; border: 1px solid #ebd9c5; border-radius: 16px; padding: 14px; text-align: center;">
                <span style="font-size: 0.75rem; color: #8d786c; font-weight: 700; text-transform: uppercase;">Cash Sales</span>
                <p id="modalCashierCash" style="font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 1.25rem; color: #28a745; margin: 4px 0 0 0;">₱0</p>
            </div>
            <div style="background: #faf5eb; border: 1px solid #ebd9c5; border-radius: 16px; padding: 14px; text-align: center;">
                <span style="font-size: 0.75rem; color: #8d786c; font-weight: 700; text-transform: uppercase;">GCash Sales</span>
                <p id="modalCashierGCash" style="font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 1.25rem; color: #0070ba; margin: 4px 0 0 0;">₱0</p>
            </div>
        </div>

        <!-- Top Selling Products by this cashier -->
        <div style="margin-bottom: 1.5rem;">
            <h4 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1rem; color: #2c1a14; margin-bottom: 0.75rem;">Top Products Sold by this Cashier</h4>
            <div id="modalTopProductsList" style="display: flex; gap: 8px; flex-wrap: wrap;">
                <!-- dynamic pills -->
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <div>
            <h4 style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1rem; color: #2c1a14; margin-bottom: 0.75rem;">Recent Orders Processed</h4>
            <div style="max-height: 250px; overflow-y: auto; border: 1px solid #f0ebe5; border-radius: 14px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; font-family: 'Plus Jakarta Sans', sans-serif;">
                    <thead style="background: #faf5eb; position: sticky; top: 0;">
                        <tr>
                            <th style="padding: 10px; text-align: left; color: #8d786c; font-weight: 700;">ORDER #</th>
                            <th style="padding: 10px; text-align: left; color: #8d786c; font-weight: 700;">DATE & TIME</th>
                            <th style="padding: 10px; text-align: left; color: #8d786c; font-weight: 700;">ITEMS</th>
                            <th style="padding: 10px; text-align: left; color: #8d786c; font-weight: 700;">PAYMENT</th>
                            <th style="padding: 10px; text-align: right; color: #8d786c; font-weight: 700;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody id="modalRecentOrdersBody">
                        <tr><td colspan="5" style="text-align: center; padding: 1.5rem; color: #8d786c;">Loading orders...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="text-align: right; margin-top: 1.5rem;">
            <button onclick="closeCashierModal()" style="background: #2c1a14; color: #fff; border: none; padding: 0.6rem 1.4rem; border-radius: 9999px; font-family: 'Outfit', sans-serif; font-weight: 700; cursor: pointer;">
                Close Breakdown
            </button>
        </div>
    </div>
</div>

<!-- =========================================
     SHIFT SUMMARY REPORT MODAL
========================================= -->
<div id="shiftSummaryModal" style="display: none; position: fixed; inset: 0; background: rgba(30, 20, 15, 0.65); backdrop-filter: blur(5px); z-index: 99999; overflow-y: auto; padding: 1.5rem 1rem; align-items: center; justify-content: center;">
    <div style="background: #fdfbf7; border: 1.5px solid #e2d1c3; border-radius: 20px; max-width: 820px; width: 100%; margin: auto; padding: 1.75rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); position: relative;" id="shiftSummaryPrintableArea">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1.5px solid #ece0d1; padding-bottom: 1rem; margin-bottom: 1.25rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="background: #2c1a14; color: #c59958; font-weight: 800; font-size: 0.75rem; padding: 4px 10px; border-radius: 6px; letter-spacing: 0.5px;">DAILY SHIFT REPORT</span>
                    <span id="shiftModalDate" style="font-size: 0.82rem; font-weight: 600; color: #8d786c;">Today</span>
                </div>
                <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.45rem; font-weight: 800; color: #2c1a14; margin: 6px 0 0 0;">End-of-Shift Store Reconciliation</h3>
                <p style="font-size: 0.8rem; color: #8d786c; margin: 2px 0 0 0;" id="shiftModalTimestamp">Generated on —</p>
            </div>
            <div style="display: flex; gap: 8px;">
                <button onclick="window.printShiftSummary()" style="background: #c59958; color: #fff; border: none; padding: 0.55rem 1rem; border-radius: 10px; font-weight: 700; font-size: 0.82rem; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-print"></i> Print Report
                </button>
                <button onclick="closeShiftSummaryModal()" style="background: #ebdcd0; color: #5c4033; border: none; width: 34px; height: 34px; border-radius: 50%; font-weight: 800; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Summary KPI Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
            <div style="background: #fff; border: 1px solid #ebdcd0; border-radius: 12px; padding: 0.85rem;">
                <p style="font-size: 0.72rem; font-weight: 700; color: #8d786c; margin: 0; text-transform: uppercase;">Net Collected</p>
                <p id="shiftModalNetRev" style="font-family: 'Outfit', sans-serif; font-size: 1.35rem; font-weight: 800; color: #16a34a; margin: 4px 0 0 0;">₱0</p>
                <p style="font-size: 0.7rem; color: #8d786c; margin: 2px 0 0 0;">Gross: <span id="shiftModalGross">₱0</span></p>
            </div>
            <div style="background: #fff; border: 1px solid #ebdcd0; border-radius: 12px; padding: 0.85rem;">
                <p style="font-size: 0.72rem; font-weight: 700; color: #8d786c; margin: 0; text-transform: uppercase;">Orders Handled</p>
                <p id="shiftModalOrders" style="font-family: 'Outfit', sans-serif; font-size: 1.35rem; font-weight: 800; color: #2c1a14; margin: 4px 0 0 0;">0</p>
                <p style="font-size: 0.7rem; color: #8d786c; margin: 2px 0 0 0;"><span id="shiftModalCompleted">0</span> Done &bull; <span id="shiftModalPending">0</span> In-Queue</p>
            </div>
            <div style="background: #fff; border: 1px solid #ebdcd0; border-radius: 12px; padding: 0.85rem;">
                <p style="font-size: 0.72rem; font-weight: 700; color: #8d786c; margin: 0; text-transform: uppercase;">Cash in Drawer</p>
                <p id="shiftModalCash" style="font-family: 'Outfit', sans-serif; font-size: 1.35rem; font-weight: 800; color: #b45309; margin: 4px 0 0 0;">₱0</p>
                <p style="font-size: 0.7rem; color: #8d786c; margin: 2px 0 0 0;">GCash: <span id="shiftModalGcash">₱0</span></p>
            </div>
            <div style="background: #fff; border: 1px solid #ebdcd0; border-radius: 12px; padding: 0.85rem;">
                <p style="font-size: 0.72rem; font-weight: 700; color: #8d786c; margin: 0; text-transform: uppercase;">Discounts & Voids</p>
                <p id="shiftModalDiscounts" style="font-family: 'Outfit', sans-serif; font-size: 1.35rem; font-weight: 800; color: #dc2626; margin: 4px 0 0 0;">₱0</p>
                <p style="font-size: 0.7rem; color: #8d786c; margin: 2px 0 0 0;"><span id="shiftModalVoids">0</span> Voided orders</p>
            </div>
        </div>

        <!-- Two Columns: Cashier Breakdown + Inventory Alerts -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
            <!-- Cashier Breakdown -->
            <div style="background: #fff; border: 1px solid #ebdcd0; border-radius: 12px; padding: 1rem;">
                <h5 style="font-size: 0.85rem; font-weight: 800; color: #2c1a14; margin: 0 0 0.75rem 0; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-users" style="color: #c59958;"></i> Cashier Performance
                </h5>
                <div id="shiftModalCashierList" style="display: flex; flex-direction: column; gap: 6px; font-size: 0.8rem;">
                    <p style="color: #8d786c; margin: 0;">No cashier shift data.</p>
                </div>
            </div>

            <!-- Inventory Watchlist -->
            <div style="background: #fff; border: 1px solid #ebdcd0; border-radius: 12px; padding: 1rem;">
                <h5 style="font-size: 0.85rem; font-weight: 800; color: #2c1a14; margin: 0 0 0.75rem 0; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-triangle-exclamation" style="color: #d97706;"></i> Inventory Stock Alerts
                </h5>
                <div id="shiftModalInventoryAlerts" style="display: flex; flex-direction: column; gap: 6px; font-size: 0.8rem;">
                    <p style="color: #8d786c; margin: 0;">All stock levels optimal.</p>
                </div>
            </div>
        </div>

        <!-- Top Selling Items Today Table -->
        <div style="background: #fff; border: 1px solid #ebdcd0; border-radius: 12px; padding: 1rem;">
            <h5 style="font-size: 0.85rem; font-weight: 800; color: #2c1a14; margin: 0 0 0.75rem 0; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-trophy" style="color: #c59958;"></i> Top 5 Products This Shift
            </h5>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.82rem;">
                    <thead>
                        <tr style="border-bottom: 1.5px solid #ece0d1; background: #faf5eb;">
                            <th style="padding: 8px; text-align: left; color: #8d786c; font-weight: 700;">#</th>
                            <th style="padding: 8px; text-align: left; color: #8d786c; font-weight: 700;">ITEM</th>
                            <th style="padding: 8px; text-align: right; color: #8d786c; font-weight: 700;">QTY</th>
                            <th style="padding: 8px; text-align: right; color: #8d786c; font-weight: 700;">REVENUE</th>
                        </tr>
                    </thead>
                    <tbody id="shiftModalTopItems">
                        <tr><td colspan="4" style="text-align: center; padding: 1rem; color: #8d786c;">Loading top items...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="text-align: right; margin-top: 1.25rem;">
            <button onclick="closeShiftSummaryModal()" style="background: #2c1a14; color: #fff; border: none; padding: 0.6rem 1.4rem; border-radius: 9999px; font-family: 'Outfit', sans-serif; font-weight: 700; cursor: pointer;">
                Close Report
            </button>
        </div>
    </div>
</div>

<!-- =========================================
     PDF REPORT TEMPLATE (Hidden from UI)
========================================= -->
<div id="pdfSalesReportTemplate" style="display: none;">
    <div id="pdfSalesContent" class="pdf-report">
        <!-- Brand Header -->
        <div class="pdf-header">
            <div>
                <h1 class="pdf-brand-name">earthbred</h1>
                <p class="pdf-brand-sub">Coffee Studio</p>
            </div>
            <div class="pdf-report-meta">
                <h2 id="pdfReportMainTitle" class="pdf-report-title">SALES REPORT</h2>
                <p id="pdfReportPeriod" class="pdf-report-period"></p>
                <p id="pdfGeneratedTime" class="pdf-report-date"></p>
            </div>
        </div>

        <!-- KPI Row -->
        <div class="pdf-kpi-row">
            <div class="pdf-kpi-card">
                <p class="pdf-kpi-label" id="pdfKpiSalesLabel">Revenue</p>
                <p class="pdf-kpi-val" id="pdfKpiSalesVal">₱0</p>
            </div>
            <div class="pdf-kpi-card">
                <p class="pdf-kpi-label" id="pdfKpiOrdersLabel">Orders</p>
                <p class="pdf-kpi-val" id="pdfKpiOrdersVal">0</p>
            </div>
            <div class="pdf-kpi-card">
                <p class="pdf-kpi-label" id="pdfKpiThirdLabel">Avg Order Value</p>
                <p class="pdf-kpi-val" id="pdfKpiAOVVal">₱0</p>
            </div>
        </div>

        <!-- Analytics Highlights Box -->
        <div id="pdfAnalyticsBox" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 10px;">
            <div style="background: #faf5eb; border: 1.5px solid #eadeca; border-radius: 6px; padding: 6px 10px; font-size: 0.68rem;">
                <p style="margin: 0; font-weight: 700; color: #8d786c; font-size: 0.58rem; text-transform: uppercase;">Payment Mix</p>
                <p id="pdfPayMethodsSummary" style="margin: 2px 0 0 0; font-weight: 800; color: #2c1a14;">Cash: ₱0 (0%) | GCash: ₱0 (0%)</p>
            </div>
            <div style="background: #faf5eb; border: 1.5px solid #eadeca; border-radius: 6px; padding: 6px 10px; font-size: 0.68rem;">
                <p style="margin: 0; font-weight: 700; color: #8d786c; font-size: 0.58rem; text-transform: uppercase;">Top Category</p>
                <p id="pdfTopCategorySummary" style="margin: 2px 0 0 0; font-weight: 800; color: #2c1a14; text-transform: capitalize;">—</p>
            </div>
            <div style="background: #faf5eb; border: 1.5px solid #eadeca; border-radius: 6px; padding: 6px 10px; font-size: 0.68rem;">
                <p style="margin: 0; font-weight: 700; color: #8d786c; font-size: 0.58rem; text-transform: uppercase;">Peak Activity</p>
                <p id="pdfPeakSummary" style="margin: 2px 0 0 0; font-weight: 800; color: #2c1a14;">—</p>
            </div>
        </div>

        <!-- Dynamic Chart Section -->
        <div class="pdf-chart-card">
            <h3 id="pdfChartHeading" class="pdf-chart-heading">Revenue Graph</h3>
            <img id="pdfSalesChartImg" class="pdf-chart-img" alt="Revenue Chart">
        </div>

        <!-- Dynamic Table Section -->
        <div class="pdf-table-card">
            <h3 id="pdfTableHeading" class="pdf-table-heading">Breakdown Table</h3>
            <table class="pdf-table">
                <thead id="pdfTableHeader">
                    <tr>
                        <th style="text-align: left;">Rank</th>
                        <th style="text-align: left;">Item Name</th>
                        <th style="text-align: left;">Category</th>
                        <th style="text-align: right;">Qty Sold</th>
                        <th style="text-align: right;">Revenue</th>
                        <th style="text-align: right;">Share</th>
                    </tr>
                </thead>
                <tbody id="pdfTopItemsBody">
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="pdf-footer">
            <p style="margin: 0;">Generated by Earthbred POS System &bull; <span id="pdfFooterTimestamp"></span></p>
            <p style="margin: 0; font-weight: 600; text-transform: uppercase;">Confidential &bull; Store Administration</p>
        </div>
    </div>
</div>

<script src="<?= asset('js/pos-modal.js') ?>?v=1.0.0"></script>
<script src="<?= asset('js/clock-out.js') ?>?v=1.0.0"></script>
<script src="<?= asset('js/sales-report.js') ?>?v=1.0.0"></script>
</body>
</html>
