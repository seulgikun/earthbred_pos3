<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title>Earthbred - Order Queuing</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/pos.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/queue.css') ?>?v=1.1.0">
    <link rel="stylesheet" href="<?= asset('css/pos-modal.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/ios26-theme.css') ?>?v=1.0.0">
    <link rel="icon" type="image/png" href="<?= asset('favicon.png') ?>?v=3.0">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>?v=3.0">
    <style>
        body {
            display: block !important;
            height: 100vh !important;
            overflow: hidden !important;
            background: var(--ios-bg-mesh) !important;
        }
        .app-container {
            display: flex !important;
            width: 100% !important;
            height: 100vh !important;
        }
        .orders-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
            gap: 1.25rem !important;
        }
        .order-card {
            display: flex !important;
            flex-direction: column !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
        }
        .order-footer {
            display: flex !important;
            flex-direction: column !important;
            gap: 0.65rem !important;
            width: 100% !important;
            margin-top: auto !important;
            padding-top: 0.85rem !important;
            border-top: 1px solid rgba(220,200,180,0.4) !important;
            box-sizing: border-box !important;
        }
        .order-total-info {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            width: 100% !important;
            font-family: 'Outfit', sans-serif !important;
            font-size: 0.95rem !important;
            color: #594a40 !important;
        }
        .order-total-info strong {
            font-size: 1.2rem !important;
            color: #1c1612 !important;
            font-weight: 800 !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            white-space: nowrap !important;
        }
        .order-actions {
            display: flex !important;
            gap: 0.5rem !important;
            width: 100% !important;
        }
        .order-actions .btn-void,
        .order-actions .btn-complete {
            flex: 1 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            padding: 0.6rem 0.5rem !important;
            font-size: 0.82rem !important;
            font-weight: 700 !important;
            border-radius: 9999px !important;
            white-space: nowrap !important;
            box-sizing: border-box !important;
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="app-container">
        <!-- Sidebar -->
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
                    <li class="menu-item active" onclick="window.location.href='<?= url('') ?>/queue'" style="border-top: 1px solid #e5d9c5; margin-top: 0.5rem; padding-top: 1rem;">
                        <span class="menu-icon">📋</span> Order Queuing
                    </li>
                    <li class="menu-item" id="inventory-menu-item" onclick="window.location.href='<?= url('') ?>/inventory'" style="border-top: 1px solid #e5d9c5; margin-top: 0.5rem; padding-top: 1rem;">
                        <span class="menu-icon">📦</span> Inventory
                    </li>
                    <li class="menu-item" id="sales-report-menu-item" onclick="window.location.href='<?= url('') ?>/manager/sales-report'">
                        <span class="menu-icon">📊</span> Sales Report
                    </li>
                </ul>
            </nav>

            <div class="clock-out">
                <i class="fa-solid fa-power-off"></i> Clock Out
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="top-header queue-header">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Toggle Navigation Menu" type="button" onclick="if(typeof window.toggleGlobalSidebar==='function')window.toggleGlobalSidebar();" style="touch-action:manipulation;cursor:pointer;">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h2>Order Queue (Today)</h2>
                </div>
            </header>

            <div style="padding: 1.5rem 2rem 0; display: flex; justify-content: flex-end;">
                <div class="daily-total">
                    <span>TOTAL SALES:</span>
                    <strong>₱ <?= number_format($totalSales, 2) ?></strong>
                </div>
            </div>

            <div class="queue-container">
                <?php if (count($orders) === 0): ?>
                    <div class="empty-queue">
                        <i class="fa-solid fa-clipboard-check"></i>
                        <p>No orders placed today.</p>
                    </div>
                <?php else: ?>
                    <div class="orders-grid">
                        <?php foreach($orders as $order): ?>
                            <div class="order-card status-<?= $order->status ?>" id="order-card-<?= $order->id ?>">
                                <div class="order-header">
                                    <div>
                                        <h3>Order #<?= $order->id ?></h3>
                                        <span class="order-time"><?= $order->created_at->format('h:i A') ?></span>
                                    </div>
                                    <span class="badge badge-<?= $order->status ?>"><?= ucfirst($order->status) ?></span>
                                </div>
                                
                                <div class="order-items">
                                    <?php foreach($order->items as $item): ?>
                                        <div class="order-item">
                                            <span class="qty"><?= $item->quantity ?>x</span>
                                            <div class="item-details">
                                                <span class="name"><?= $item->product_name ?></span>
                                                <?php if($item->customer_name): ?>
                                                    <span class="customer"><i class="fa-solid fa-user"></i> <?= $item->customer_name ?></span>
                                                <?php endif; ?>
                                                <?php if(!empty($item->addons)): ?>
                                                    <span class="addons">+ <?= implode(', ', $item->addons) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="price">₱ <?= number_format($item->item_total, 2) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="order-footer">
                                    <div class="order-total-info">
                                        <span>Total:</span>
                                        <strong>₱ <?= number_format($order->total, 2) ?></strong>
                                    </div>
                                    <div class="order-actions">
                                        <?php if($order->status === 'pending'): ?>
                                            <button class="btn-void" onclick="updateOrderStatus(<?= $order->id ?>, 'void')"><i class="fa-solid fa-ban"></i> Void</button>
                                            <button class="btn-complete" onclick="updateOrderStatus(<?= $order->id ?>, 'completed')"><i class="fa-solid fa-check"></i> Complete</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Void Auth Modal -->
    <div class="modal-overlay" id="voidAuthModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div class="modal-content" style="background: #fff; padding: 2rem; border-radius: 12px; max-width: 400px; width: 90%; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <h3 style="margin-top: 0; font-family: 'Montserrat', sans-serif; color: #2c1a14;"><i class="fa-solid fa-ban" style="color: #d32f2f;"></i> Void Authentication</h3>
            <p style="font-size: 0.9rem; margin-bottom: 1.2rem; color: #666;">Please enter the Manager / Owner Void PIN to cancel this order.</p>
            
            <div style="position: relative; display: flex; align-items: center; margin-bottom: 1.5rem;">
                <input type="password" id="voidPinInput" maxlength="4" pattern="\d{4}" inputmode="numeric" placeholder="Enter 4-digit PIN" style="width: 100%; padding: 12px 42px 12px 12px; border-radius: 8px; border: 1px solid #ccc; font-family: 'Poppins', sans-serif; letter-spacing: 4px; font-size: 1.1rem; box-sizing: border-box;">
                <button type="button" onclick="toggleQueueVoidEye()" style="position: absolute; right: 12px; background: none; border: none; color: #888; cursor: pointer; padding: 4px; font-size: 1.1rem;">
                    <i class="fa-solid fa-eye" id="queueVoidEyeIcon"></i>
                </button>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button onclick="closeVoidModal()" style="padding: 10px 18px; border: none; border-radius: 6px; cursor: pointer; background: #eee; font-family: 'Poppins', sans-serif; font-weight: 500;">Cancel</button>
                <button onclick="submitVoidAuth()" style="padding: 10px 18px; border: none; border-radius: 6px; cursor: pointer; background: #3d271d; color: #fff; font-family: 'Poppins', sans-serif; font-weight: 600;">Authenticate</button>
            </div>
        </div>
    </div>

    <script>
        let pendingVoidOrderId = null;

        function toggleQueueVoidEye() {
            const input = document.getElementById('voidPinInput');
            const icon = document.getElementById('queueVoidEyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function closeVoidModal() {
            document.getElementById('voidAuthModal').style.display = 'none';
            const input = document.getElementById('voidPinInput');
            input.value = '';
            input.type = 'password';
            const icon = document.getElementById('queueVoidEyeIcon');
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
            pendingVoidOrderId = null;
        }

        async function submitVoidAuth() {
            const pin = document.getElementById('voidPinInput').value;
            if (!pin) return alert('Please enter a PIN');

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('<?= url('') ?>/api/void-pin/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ pin: pin })
                });

                const result = await response.json();
                if (result.success) {
                    const orderId = pendingVoidOrderId;
                    closeVoidModal();
                    // Proceed with actual voiding
                    executeOrderStatusUpdate(orderId, 'void');
                } else {
                    alert(result.message || 'Authentication failed');
                }
            } catch (e) {
                console.error(e);
                alert('Connection error');
            }
        }

        async function executeOrderStatusUpdate(orderId, status) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(`<?= url('') ?>/orders/${orderId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: status })
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert('Error updating status');
                }
            } catch (e) {
                console.error(e);
                alert('Connection error');
            }
        }

        function updateOrderStatus(orderId, status) {
            if (status === 'void') {
                pendingVoidOrderId = orderId;
                document.getElementById('voidAuthModal').style.display = 'flex';
                return;
            }
            executeOrderStatusUpdate(orderId, status);
        }
    </script>
    <script src="<?= asset('js/pos-modal.js') ?>?v=1.0.0"></script>
    <script src="<?= asset('js/clock-out.js') ?>?v=1.0.0"></script>
</body>
</html>
