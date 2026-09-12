<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthbred - Account Management</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Montserrat:wght@400;600;700;800;900&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/manager.css') ?>?v=1.0.0">
    <link rel="stylesheet" href="<?= asset('css/pos-modal.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/ios26-theme.css') ?>?v=1.0.1">
    <link rel="icon" type="image/png" href="<?= asset('favicon.png') ?>?v=3.0">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>?v=3.0">
    <!-- Inline styles for account management -->
    <style>
        .mgr-accounts-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-top: 20px;
        }

        .mgr-accounts-table th,
        .mgr-accounts-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .mgr-accounts-table th {
            background-color: #fcfbf9;
            color: #8d786c;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .mgr-accounts-table td {
            font-family: 'Poppins', sans-serif;
            color: #5c4a40;
            font-size: 0.95rem;
        }

        .mgr-accounts-table tr:last-child td {
            border-bottom: none;
        }

        .role-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .role-manager {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .role-cashier {
            background-color: #f1f8e9;
            color: #558b2f;
        }

        .action-btn {
            background: #fff;
            border: 1px solid #c8b8a6;
            padding: 6px 13px;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            color: #3d271d;
            font-weight: 600;
            transition: all 0.2s;
        }

        .action-btn:hover {
            background: #3d271d;
            color: #ffffff;
            border-color: #26160e;
        }

        /* Modals */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: #fff;
            width: 100%;
            max-width: 450px;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s ease-out forwards;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.2rem;
            color: #3d2e24;
        }

        .close-modal-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #888;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            color: #5c4a40;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #dcdcdc;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3d271d;
        }

        .save-btn {
            background: #3d271d;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            width: 100%;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .save-btn:hover {
            background: #26160e;
        }

        .add-product-btn {
            background: linear-gradient(135deg, #533524 0%, #3d271d 60%, #26160e 100%) !important;
            color: #ffffff !important;
            border: 1px solid #26160e !important;
            padding: 9px 18px;
            border-radius: 8px !important;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(45, 26, 17, 0.25);
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .add-product-btn:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #5f3e2b 0%, #462c21 60%, #2e1a11 100%) !important;
            box-shadow: 0 6px 18px rgba(45, 26, 17, 0.38);
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Reset My Password button style - Rich Earthbred Brown */
        .owner-reset-pw-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 8px !important;
            border: 1px solid #26160e !important;
            background: linear-gradient(135deg, #533524 0%, #3d271d 60%, #26160e 100%) !important;
            color: #ffffff !important;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(45, 26, 17, 0.25);
            transition: all 0.2s;
        }

        .owner-reset-pw-btn:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #5f3e2b 0%, #462c21 60%, #2e1a11 100%) !important;
            box-shadow: 0 6px 18px rgba(45, 26, 17, 0.38);
        }

        /* Audit Log button - Matched to Rich Earthbred Brown */
        .audit-log-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 8px !important;
            border: 1px solid #26160e !important;
            background: linear-gradient(135deg, #533524 0%, #3d271d 60%, #26160e 100%) !important;
            color: #ffffff !important;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(45, 26, 17, 0.25);
            transition: all 0.2s;
        }

        .audit-log-btn:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #5f3e2b 0%, #462c21 60%, #2e1a11 100%) !important;
            box-shadow: 0 6px 18px rgba(45, 26, 17, 0.38);
        }

        /* Audit Log Modal */
        .audit-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.55);
            z-index: 2000;
            display: none;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .audit-modal-overlay.active {
            display: flex;
        }

        .audit-modal-box {
            background: #fff;
            width: 100%;
            max-width: 960px;
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
            max-height: 88vh;
            animation: slideUp 0.3s ease-out;
            overflow: hidden;
        }

        .audit-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 24px;
            border-bottom: 1px solid #f0e8dc;
            background: #fff;
        }

        .audit-modal-header h3 {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.25rem;
            color: #3d271d;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        /* Audit Tabs Navigation */
        .audit-tabs {
            display: flex;
            background: #faf6f2;
            border-bottom: 1px solid #f0e8dc;
            padding: 0 20px;
            gap: 6px;
            overflow-x: auto;
        }

        .audit-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border: none;
            background: transparent;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.82rem;
            font-weight: 600;
            color: #8d786c;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.2s ease;
            white-space: nowrap;
            outline: none;
        }

        .audit-tab-btn:hover {
            color: #533524;
            background: rgba(83, 53, 36, 0.05);
        }

        .audit-tab-btn.active {
            color: #533524;
            border-bottom-color: #533524;
            font-weight: 700;
            background: #fff;
        }

        .audit-modal-filters {
            display: flex;
            gap: 10px;
            padding: 12px 24px;
            border-bottom: 1px solid #f0e8dc;
            flex-wrap: wrap;
            align-items: center;
            background: #fff;
        }

        .audit-filter-select,
        .audit-search-input {
            padding: 7px 12px;
            border: 1px solid #d5c5b5;
            border-radius: 7px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            color: #3d271d;
            background: #fdfaf7;
        }

        .audit-search-input {
            flex: 1;
            min-width: 180px;
        }

        .audit-filter-btn {
            padding: 7px 16px;
            border-radius: 7px;
            background: linear-gradient(135deg, #533524 0%, #3d271d 100%);
            color: #fff;
            border: none;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .audit-filter-btn:hover {
            opacity: 0.92;
        }

        .audit-modal-body {
            overflow-y: auto;
            padding: 0 24px 10px;
            flex: 1;
            min-height: 280px;
        }

        .audit-log-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .audit-log-table th {
            text-align: left;
            padding: 10px 12px;
            background: #fcfaf7;
            color: #8d786c;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #f0e8dc;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .audit-log-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f5ede4;
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            color: #5c4a40;
            vertical-align: top;
        }

        .audit-log-table tr:hover td {
            background-color: #faf6f2;
        }

        .audit-action-badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 12px;
            font-size: 0.74rem;
            font-weight: 700;
            font-family: 'Montserrat', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .badge-void {
            background: #fff3e0;
            color: #e65100;
            border: 1px solid #ffe0b2;
        }

        .badge-price {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .badge-product {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
        }

        .badge-discount {
            background: #f3e5f5;
            color: #7b1fa2;
            border: 1px solid #e1bee7;
        }

        .badge-inventory {
            background: #fce4ec;
            color: #c62828;
            border: 1px solid #f8bbd0;
        }

        .badge-security {
            background: #fff8e1;
            color: #f57f17;
            border: 1px solid #ffecb3;
        }

        .badge-default {
            background: #f5f5f5;
            color: #616161;
            border: 1px solid #e0e0e0;
        }

        .audit-empty {
            text-align: center;
            padding: 40px;
            color: #a0887c;
            font-family: 'Poppins', sans-serif;
        }

        /* Audit Server-Side Pagination Bar */
        .audit-pagination-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 24px;
            border-top: 1px solid #f0e8dc;
            background: #fdfaf7;
            flex-wrap: wrap;
            gap: 12px;
        }

        .audit-page-info {
            font-family: 'Poppins', sans-serif;
            font-size: 0.82rem;
            color: #7d6a5d;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .audit-page-info strong {
            color: #3d271d;
        }

        .audit-page-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .audit-page-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 13px;
            border-radius: 6px;
            border: 1px solid #d5c5b5;
            background: #fff;
            color: #533524;
            font-family: 'Poppins', sans-serif;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .audit-page-btn:hover:not(:disabled) {
            background: #533524;
            color: #fff;
            border-color: #533524;
        }

        .audit-page-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .audit-page-indicator {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.8rem;
            font-weight: 700;
            color: #533524;
            padding: 0 4px;
        }

        .audit-per-page-select {
            padding: 4px 8px;
            border: 1px solid #d5c5b5;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.8rem;
            color: #533524;
            background: #fff;
            cursor: pointer;
            outline: none;
        }
    </style>
    <script>
        // Check Owner Role on Page Load
        if (localStorage.getItem('userRole') !== 'owner') {
            window.location.href = '<?= url('') ?>/manager';
        }
        document.body && document.body.classList.add('is-owner');
        document.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('is-owner');
            if (localStorage.getItem('userName')) {
                document.getElementById('sidebarUserName').textContent = localStorage.getItem('userName');
            }
            // Set role label
            const role = (localStorage.getItem('userRole') || '').toLowerCase();
            const roleEl = document.getElementById('sidebarUserId');
            if (roleEl) roleEl.textContent = role.charAt(0).toUpperCase() + role.slice(1) || 'Owner';
        });
    </script>
</head>

<body class="is-owner">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="mgr-app">
        <!-- Sidebar -->
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
                    <p class="mgr-user-name" id="sidebarUserName">Owner</p>
                    <p class="mgr-user-id" id="sidebarUserId">Owner</p>
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
                    <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/sales-report'">
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
                    <li class="mgr-nav-item active" style="display: flex !important;">
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

        <!-- Main Content Area -->
        <main class="mgr-main">
            <header class="mgr-header"
                style="justify-content: space-between; display: flex; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Toggle Navigation Menu" type="button" onclick="if(typeof window.toggleGlobalSidebar==='function')window.toggleGlobalSidebar();" style="touch-action:manipulation;cursor:pointer;">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h2 class="mgr-page-title">Account Management</h2>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <button class="audit-log-btn" onclick="openAuditLogModal()">
                        <i class="fa-solid fa-clipboard-list"></i> Audit Trail
                    </button>
                    <button class="owner-reset-pw-btn" id="resetMyPasswordBtn" onclick="openOwnerResetModal()">
                        <i class="fa-solid fa-key"></i> Reset My Password
                    </button>
                    <button class="add-product-btn" onclick="openAddAccountModal()" style="background-color:#3d271d;"><i
                            class="fa-solid fa-plus"></i> Add Account</button>
                </div>
            </header>

            <div class="mgr-content">
                <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%;">
                    <table class="mgr-accounts-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="accountsTableBody">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Account Modal -->
    <div class="modal-overlay" id="addAccountModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Create New Account</h3>
                <button class="close-modal-btn" onclick="closeAddAccountModal()"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="addAccountForm">
                    <div class="form-group">
                        <label>Account Role</label>
                        <select id="accountRole" required>
                            <option value="cashier" selected>Cashier (Uses 6-Digit PIN)</option>
                            <option value="manager">Manager (Uses Password)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="accountName" placeholder="e.g. Maria Santos" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" id="accountEmail" placeholder="e.g. maria@gmail.com" pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$" title="Please enter a valid @gmail.com address" required>
                        <small style="color: #8d786c; font-size: 0.75rem; display: block; margin-top: 3px;">Only @gmail.com addresses are supported. A verification email will be dispatched to this address.</small>
                    </div>

                    <!-- Cashier 6-Digit PIN Section (Default) -->
                    <div class="form-group" id="cashierPinSection">
                        <label style="display: flex; justify-content: space-between; align-items: center;">
                            <span>Cashier 6-Digit PIN</span>
                            <span style="font-size: 0.72rem; color: #3d271d; font-weight: 700;"><i
                                    class="fa-solid fa-shield-halved"></i> Exclusive PIN Access</span>
                        </label>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <input type="text" id="accountPin" maxlength="6" pattern="\d{6}" placeholder="123456"
                                inputmode="numeric"
                                style="letter-spacing: 4px; font-weight: 800; font-size: 1.2rem; text-align: center; max-width: 170px; border: 1.5px solid #d1c0a5; border-radius: 8px; padding: 10px;"
                                required>
                            <button type="button" id="generatePinBtn"
                                style="padding: 10px 14px; font-size: 0.85rem; border-radius: 8px; background: #fdfaf6; border: 1px solid #d1c0a5; cursor: pointer; color: #3d271d; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-dice"></i> Generate PIN
                            </button>
                        </div>
                        <small style="color: #666; font-size: 0.75rem; display: block; margin-top: 4px;">No complex
                            password needed. Cashier will log in using ONLY this 6-digit PIN.</small>
                    </div>

                    <!-- Manager Password Section (Hidden by default) -->
                    <div id="managerPasswordSection" style="display: none;">
                        <div class="form-group">
                            <label>Password</label>
                            <div class="password-input-wrapper"
                                style="position:relative; display:flex; align-items:center;">
                                <input type="password" id="accountPassword" minlength="8"
                                    placeholder="e.g. Earthbred@2026" style="padding-right: 42px;">
                                <i class="fa-regular fa-eye password-toggle" data-target="accountPassword"
                                    style="position:absolute; right:12px; cursor:pointer; color:#8d786c; font-size:1rem; padding:4px;"
                                    title="Toggle password visibility"></i>
                            </div>
                            <small style="color: #666; font-size: 0.75rem; display: block; margin-top: 4px;">Min 8 chars
                                with uppercase, lowercase, number & symbol.</small>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <div class="password-input-wrapper"
                                style="position:relative; display:flex; align-items:center;">
                                <input type="password" id="accountPasswordConfirm" minlength="8"
                                    placeholder="Re-enter password" style="padding-right: 42px;">
                                <i class="fa-regular fa-eye password-toggle" data-target="accountPasswordConfirm"
                                    style="position:absolute; right:12px; cursor:pointer; color:#8d786c; font-size:1rem; padding:4px;"
                                    title="Toggle password visibility"></i>
                            </div>
                            <small id="addAccMatchHint"
                                style="font-size:0.75rem; margin-top:4px; display:block; font-weight:600;"></small>
                        </div>
                    </div>

                    <button type="submit" class="save-btn" id="saveAccountBtn" style="margin-top: 15px;">Create
                        Account</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password / PIN Modal -->
    <div class="modal-overlay" id="changePasswordModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="cpModalTitle">Update Credentials</h3>
                <button class="close-modal-btn" onclick="closeChangePasswordModal()"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <input type="hidden" id="cpUserId">
                    <input type="hidden" id="cpUserRole">
                    <p
                        style="font-family: 'Poppins', sans-serif; font-size: 0.9rem; margin-bottom: 15px; color: #5c4a40;">
                        Updating for: <strong id="cpUserName"></strong> (<span id="cpRoleBadge"></span>)</p>

                    <!-- Cashier PIN Update -->
                    <div class="form-group" id="cpPinSection">
                        <label>New 6-Digit PIN</label>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <input type="text" id="cpNewPin" maxlength="6" pattern="\d{6}" placeholder="e.g. 567890"
                                inputmode="numeric"
                                style="letter-spacing: 4px; font-weight: 800; font-size: 1.2rem; text-align: center; max-width: 170px; border: 1.5px solid #d1c0a5; border-radius: 8px; padding: 10px;">
                            <button type="button" id="cpGeneratePinBtn"
                                style="padding: 10px 14px; font-size: 0.85rem; border-radius: 8px; background: #fdfaf6; border: 1px solid #d1c0a5; cursor: pointer; color: #3d271d; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-dice"></i> Generate PIN
                            </button>
                        </div>
                        <small style="color: #666; font-size: 0.75rem; display: block; margin-top: 4px;">Enter exactly 6
                            numeric digits for cashier login.</small>
                    </div>

                    <!-- Manager Password Update -->
                    <div id="cpPasswordSection" style="display: none;">
                        <div class="form-group">
                            <label>New Password</label>
                            <div class="password-input-wrapper"
                                style="position:relative; display:flex; align-items:center;">
                                <input type="password" id="cpNewPassword" minlength="8" placeholder="Enter new password"
                                    style="padding-right: 42px;">
                                <i class="fa-regular fa-eye password-toggle" data-target="cpNewPassword"
                                    style="position:absolute; right:12px; cursor:pointer; color:#8d786c; font-size:1rem; padding:4px;"
                                    title="Toggle password visibility"></i>
                            </div>
                            <small style="color: #666; font-size: 0.75rem; display: block; margin-top: 4px;">Min 8 chars
                                with uppercase, lowercase, number & symbol.</small>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <div class="password-input-wrapper"
                                style="position:relative; display:flex; align-items:center;">
                                <input type="password" id="cpNewPasswordConfirm" minlength="8"
                                    placeholder="Re-enter new password" style="padding-right: 42px;">
                                <i class="fa-regular fa-eye password-toggle" data-target="cpNewPasswordConfirm"
                                    style="position:absolute; right:12px; cursor:pointer; color:#8d786c; font-size:1rem; padding:4px;"
                                    title="Toggle password visibility"></i>
                            </div>
                            <small id="cpMatchHint"
                                style="font-size:0.75rem; margin-top:4px; display:block; font-weight:600;"></small>
                        </div>
                    </div>

                    <button type="submit" class="save-btn" id="savePasswordBtn">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Owner Reset My Password Modal -->
    <div class="modal-overlay" id="ownerResetPasswordModal">
        <div class="modal-content" style="max-width: 420px;">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fa-solid fa-key" style="color:#3d271d; margin-right:8px;"></i> Reset
                    My Password</h3>
                <button class="close-modal-btn" onclick="closeOwnerResetModal()"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div id="ownerResetInfo"
                    style="font-family:'Poppins',sans-serif; font-size:0.9rem; color:#5c4a40; margin-bottom:18px; line-height:1.6;">
                    <p>A password reset link will be sent to your registered owner email address.</p>
                    <p id="ownerResetEmailDisplay"
                        style="margin-top:8px; font-weight:700; color:#3d271d; background:#fdf8f2; padding:8px 12px; border-radius:8px; border:1px solid #e2d5c3; word-break:break-all;">
                    </p>
                    <p style="margin-top:10px; font-size:0.8rem; color:#8d786c;"><i class="fa-solid fa-clock"></i> The
                        reset link will expire in <strong>60 minutes</strong>.</p>
                </div>
                <div id="ownerResetResult" style="display:none; text-align:center; padding:16px 0;"></div>
                <div style="display:flex; gap:10px;">
                    <button type="button" class="save-btn" id="sendResetLinkBtn" onclick="sendOwnerResetLink()"
                        style="background: linear-gradient(135deg, #533524 0%, #3d271d 60%, #26160e 100%);">
                        <i class="fa-solid fa-paper-plane"></i> Send Verification Link
                    </button>
                    <button type="button" onclick="closeOwnerResetModal()"
                        style="background:#f5f0ea; color:#5c4a40; border:1px solid #d1c0a5; padding:12px 20px; border-radius:6px; font-family:'Montserrat',sans-serif; font-weight:600; cursor:pointer; flex:0 0 auto;">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const getBaseUrl = () => {
            const pathname = window.location.pathname;
            const idx = pathname.toLowerCase().indexOf('/backend/public');
            return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
        };

        async function openOwnerResetModal() {
            let email = localStorage.getItem('userEmail') || '';
            const emailDisplay = document.getElementById('ownerResetEmailDisplay');

            if (!email) {
                if (emailDisplay) emailDisplay.textContent = 'Fetching owner account email...';
                try {
                    const res = await fetch(getBaseUrl() + '/api/users');
                    const data = await res.json();
                    if (data.owner && data.owner.email) {
                        email = data.owner.email;
                        localStorage.setItem('userEmail', email);
                    }
                } catch (e) { }
            }

            if (emailDisplay) {
                emailDisplay.textContent = email || 'christopherlim1995@gmail.com';
            }
            document.getElementById('ownerResetInfo').style.display = 'block';
            document.getElementById('ownerResetResult').style.display = 'none';
            const btn = document.getElementById('sendResetLinkBtn');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Verification Link';
            document.getElementById('ownerResetPasswordModal').classList.add('active');
        }

        function closeOwnerResetModal() {
            document.getElementById('ownerResetPasswordModal').classList.remove('active');
        }

        async function sendOwnerResetLink() {
            const BASE = getBaseUrl();
            let email = localStorage.getItem('userEmail') || '';
            if (!email) {
                const displayEl = document.getElementById('ownerResetEmailDisplay');
                if (displayEl && displayEl.textContent && displayEl.textContent.includes('@')) {
                    email = displayEl.textContent.trim();
                }
            }

            if (!email) {
                document.getElementById('ownerResetResult').innerHTML = '<p style="color:#c5221f; font-family:Poppins,sans-serif;"><i class="fa-solid fa-circle-xmark"></i> Owner email not found. Please reload the page.</p>';
                document.getElementById('ownerResetInfo').style.display = 'none';
                document.getElementById('ownerResetResult').style.display = 'block';
                return;
            }

            const btn = document.getElementById('sendResetLinkBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending verification link...';

            try {
                const res = await fetch(BASE + '/api/forgot-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email: email })
                });
                const data = await res.json();
                document.getElementById('ownerResetInfo').style.display = 'none';

                if (data.success) {
                    document.getElementById('ownerResetResult').innerHTML =
                        '<div style="text-align:center; padding: 10px 0;">'
                        + '<i class="fa-solid fa-circle-check" style="font-size:3rem; color:#16a34a; margin-bottom:12px; display:block;"></i>'
                        + '<p style="font-family:Montserrat,sans-serif; font-weight:700; font-size:1.1rem; color:#3d271d; margin-bottom:8px;">Verification Link Dispatched!</p>'
                        + '<p style="font-family:Poppins,sans-serif; font-size:0.88rem; color:#5c4a40; line-height:1.5;">A password reset verification link has been sent to <strong>' + email + '</strong>.</p>'
                        + '<p style="font-size:0.8rem; color:#8d786c; margin-top:12px;"><i class="fa-solid fa-clock"></i> The link will expire in <strong>10 minutes</strong>. Please check your email inbox and spam folder.</p>'
                        + '</div>';
                    btn.style.display = 'none';
                } else {
                    document.getElementById('ownerResetResult').innerHTML =
                        '<p style="color:#c5221f; font-family:Poppins,sans-serif;"><i class="fa-solid fa-circle-xmark"></i> ' + (data.message || 'Failed to send verification link.') + '</p>';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Try Again';
                }
                document.getElementById('ownerResetResult').style.display = 'block';
            } catch (err) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send Verification Link';
                alert('Network error while connecting to mail service. Please check your connection.');
            }
        }
    </script>

    <script src="<?= asset('js/pos-modal.js') ?>?v=1.0.0"></script>
    <script src="<?= asset('js/clock-out.js') ?>?v=1.0.0"></script>
    <script src="<?= asset('js/manager-accounts.js') ?>?v=1.0.0"></script>

    <!-- ═══════════ AUDIT LOG MODAL ═══════════ -->
    <div class="audit-modal-overlay" id="auditLogModal">
        <div class="audit-modal-box">
            <div class="audit-modal-header">
                <h3><i class="fa-solid fa-shield-halved" style="color:#7a5c44;"></i> Audit Log — Store Activity</h3>
                <button class="close-modal-btn" onclick="closeAuditLogModal()"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>

            <!-- Tab Navigation -->
            <div class="audit-tabs">
                <button class="audit-tab-btn active" data-category="all" onclick="switchAuditTab('all', this)">
                    <i class="fa-solid fa-list-ul"></i> All Activity
                </button>
                <button class="audit-tab-btn" data-category="inventory" onclick="switchAuditTab('inventory', this)">
                    <i class="fa-solid fa-boxes-stacked"></i> Inventory
                </button>
                <button class="audit-tab-btn" data-category="security" onclick="switchAuditTab('security', this)">
                    <i class="fa-solid fa-user-shield"></i> Security &amp; Auth
                </button>
                <button class="audit-tab-btn" data-category="transactions"
                    onclick="switchAuditTab('transactions', this)">
                    <i class="fa-solid fa-receipt"></i> Transactions &amp; Voids
                </button>
            </div>

            <!-- Filters Bar -->
            <div class="audit-modal-filters">
                <select class="audit-filter-select" id="auditActionFilter" onchange="onAuditActionChange()">
                    <option value="ALL">All Action Types</option>
                    <option value="VOID_OVERRIDE">Void Override</option>
                    <option value="VOID_PIN_UPDATE">Void PIN Update</option>
                    <option value="VOID_PIN_VERIFY">Void PIN Verify</option>
                    <option value="PRICE_UPDATE">Price Update</option>
                    <option value="PRODUCT_CREATE">Product Create</option>
                    <option value="PRODUCT_UPDATE">Product Update</option>
                    <option value="PRODUCT_DELETE">Product Delete</option>
                    <option value="DISCOUNT_APPLY">Discount Apply</option>
                    <option value="INVENTORY_UPDATE">Inventory Update</option>
                </select>
                <input type="text" class="audit-search-input" id="auditSearchInput"
                    placeholder="Search by name, action, details...">
                <button class="audit-filter-btn" onclick="applyAuditSearch()"><i
                        class="fa-solid fa-magnifying-glass"></i> Filter</button>
            </div>

            <!-- Table Body -->
            <div class="audit-modal-body">
                <table class="audit-log-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Action</th>
                            <th>Performed By</th>
                            <th>Target</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody id="auditLogTableBody">
                        <tr>
                            <td colspan="5" class="audit-empty"><i class="fa-solid fa-spinner fa-spin"></i> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Server-Side Pagination Bar -->
            <div class="audit-pagination-bar">
                <div class="audit-page-info">
                    <span>Showing <strong id="auditFrom">0</strong>–<strong id="auditTo">0</strong> of <strong
                            id="auditTotal">0</strong> entries</span>
                    <span style="opacity:0.35;">|</span>
                    <label style="display:inline-flex; align-items:center; gap:4px; font-size:0.8rem;">
                        Per page:
                        <select class="audit-per-page-select" id="auditPerPageSelect"
                            onchange="changeAuditPerPage(this.value)">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </label>
                </div>
                <div class="audit-page-controls">
                    <button class="audit-page-btn" id="auditPrevBtn" onclick="changeAuditPage(-1)" disabled>
                        <i class="fa-solid fa-chevron-left"></i> Prev
                    </button>
                    <span class="audit-page-indicator" id="auditPageIndicator">Page 1 of 1</span>
                    <button class="audit-page-btn" id="auditNextBtn" onclick="changeAuditPage(1)" disabled>
                        Next <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const BASE_URL = '<?= url('') ?>';
        let currentAuditCategory = 'all';
        let currentAuditPage = 1;
        let currentAuditPerPage = 10;
        let lastAuditPage = 1;

        function openAuditLogModal() {
            document.getElementById('auditLogModal').classList.add('active');
            currentAuditPage = 1;
            loadAuditLogs();
        }

        function closeAuditLogModal() {
            document.getElementById('auditLogModal').classList.remove('active');
        }

        // Close on backdrop click
        document.getElementById('auditLogModal').addEventListener('click', function (e) {
            if (e.target === this) closeAuditLogModal();
        });

        // Enter key triggers filter
        document.getElementById('auditSearchInput').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                currentAuditPage = 1;
                loadAuditLogs();
            }
        });

        function switchAuditTab(category, btnElement) {
            currentAuditCategory = category;
            currentAuditPage = 1;
            document.querySelectorAll('.audit-tab-btn').forEach(btn => btn.classList.remove('active'));
            if (btnElement) btnElement.classList.add('active');
            loadAuditLogs();
        }

        function onAuditActionChange() {
            currentAuditPage = 1;
            loadAuditLogs();
        }

        function applyAuditSearch() {
            currentAuditPage = 1;
            loadAuditLogs();
        }

        function changeAuditPage(delta) {
            const targetPage = currentAuditPage + delta;
            if (targetPage >= 1 && targetPage <= lastAuditPage) {
                currentAuditPage = targetPage;
                loadAuditLogs();
            }
        }

        function changeAuditPerPage(newVal) {
            currentAuditPerPage = parseInt(newVal, 10) || 10;
            currentAuditPage = 1;
            loadAuditLogs();
        }

        function getActionBadgeClass(action) {
            if (!action) return 'badge-default';
            const a = action.toUpperCase();
            if (a.includes('VOID')) return 'badge-void';
            if (a.includes('PRICE') || a.includes('PRODUCT')) return 'badge-product';
            if (a.includes('DISCOUNT')) return 'badge-discount';
            if (a.includes('INVENTORY') || a.includes('STOCK')) return 'badge-inventory';
            if (a.includes('PIN') || a.includes('SECURITY') || a.includes('PASSWORD') || a.includes('AUTH') || a.includes('LOGIN')) return 'badge-security';
            return 'badge-default';
        }

        async function loadAuditLogs() {
            const tbody = document.getElementById('auditLogTableBody');
            tbody.innerHTML = '<tr><td colspan="5" class="audit-empty"><i class="fa-solid fa-spinner fa-spin"></i> Loading logs...</td></tr>';

            const action = document.getElementById('auditActionFilter').value;
            const search = document.getElementById('auditSearchInput').value.trim();

            let url = `${BASE_URL}/api/audit-logs?page=${currentAuditPage}&per_page=${currentAuditPerPage}&category=${encodeURIComponent(currentAuditCategory)}`;
            if (action && action !== 'ALL') url += '&action=' + encodeURIComponent(action);
            if (search) url += '&search=' + encodeURIComponent(search);

            try {
                const res = await fetch(url);
                const data = await res.json();

                const logs = data.logs || data.data || [];
                const total = data.total || 0;
                const from = data.from || (logs.length > 0 ? 1 : 0);
                const to = data.to || logs.length;
                lastAuditPage = data.last_page || 1;
                currentAuditPage = data.current_page || 1;

                // Update pagination controls
                document.getElementById('auditFrom').textContent = from;
                document.getElementById('auditTo').textContent = to;
                document.getElementById('auditTotal').textContent = total;
                document.getElementById('auditPageIndicator').textContent = `Page ${currentAuditPage} of ${lastAuditPage}`;

                document.getElementById('auditPrevBtn').disabled = (currentAuditPage <= 1);
                document.getElementById('auditNextBtn').disabled = (currentAuditPage >= lastAuditPage);

                if (!data.success || logs.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="audit-empty"><i class="fa-solid fa-inbox" style="font-size:1.8rem; margin-bottom:8px; opacity:0.6; display:block;"></i>No audit log entries found for this category or filter.</td></tr>';
                    return;
                }

                tbody.innerHTML = logs.map(log => {
                    const ts = log.created_at ? new Date(log.created_at).toLocaleString('en-PH', {
                        year: 'numeric', month: 'short', day: '2-digit',
                        hour: '2-digit', minute: '2-digit', second: '2-digit'
                    }) : '—';
                    const badgeClass = getActionBadgeClass(log.action);
                    const actionLabel = escapeHtml((log.action || 'ACTIVITY').replace(/_/g, ' '));
                    let rawName = log.manager_name;
                    if (!rawName || rawName === 'Store Manager' || rawName === 'Earthbred Owner' || rawName === 'Earthbred Manager') {
                        rawName = (log.manager_role === 'owner') ? 'Christopher Lim' : 'junric limpangog';
                    }
                    const roleBadge = log.manager_role ? ` <span style="opacity:0.65;font-size:0.75rem;text-transform:capitalize;">[${escapeHtml(log.manager_role)}]</span>` : '';
                    const performer = `<span style="font-weight:600; color:#3d271d;">${escapeHtml(rawName)}</span>${roleBadge}`;
                    const target = log.target ? `<span style="font-weight:600;">${escapeHtml(log.target)}</span>` : '<span style="opacity:0.45;">—</span>';
                    const details = log.details ? escapeHtml(log.details) : '—';

                    return `<tr>
                        <td style="white-space:nowrap; font-size:0.8rem; color:#8d786c;">${ts}</td>
                        <td><span class="audit-action-badge ${badgeClass}">${actionLabel}</span></td>
                        <td>${performer}</td>
                        <td>${target}</td>
                        <td style="max-width:320px; word-break:break-word;">${details}</td>
                    </tr>`;
                }).join('');
            } catch (err) {
                console.error('Audit log fetch/render error:', err);
                tbody.innerHTML = '<tr><td colspan="5" class="audit-empty" style="color:#c5221f;"><i class="fa-solid fa-circle-xmark"></i> Failed to load audit logs. Please try again.</td></tr>';
            }
        }

        function escapeHtml(str) {
            const d = document.createElement('div');
            d.appendChild(document.createTextNode(str));
            return d.innerHTML;
        }
    </script>
</body>

</html>