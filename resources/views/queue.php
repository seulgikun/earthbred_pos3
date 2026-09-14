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
    <link rel="stylesheet" href="<?= asset('css/queue.css') ?>?v=1.4.0">
    <link rel="stylesheet" href="<?= asset('css/pos-modal.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/ios26-theme.css') ?>?v=1.0.0">
    <link rel="icon" type="image/png" href="<?= asset('favicon.png') ?>?v=3.0">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>?v=3.0">
    <style>
        body { display: block !important; height: 100vh !important; overflow: hidden !important; background: var(--ios-bg-mesh) !important; }
        .app-container { display: flex !important; width: 100% !important; height: 100vh !important; }
        .orders-grid { display: grid !important; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important; gap: 1.15rem !important; }
        .order-card { display: flex !important; flex-direction: column !important; min-width: 0 !important; padding: 1.1rem 0.9rem !important; box-sizing: border-box !important; }
        .order-footer { display: flex !important; justify-content: space-between !important; align-items: center !important; gap: 6px !important; width: 100% !important; margin-top: auto !important; padding-top: 0.65rem !important; border-top: 1px solid rgba(220,200,180,0.4) !important; box-sizing: border-box !important; }
        .order-total-info { display: flex !important; flex-direction: column !important; font-family: 'Outfit', sans-serif !important; font-size: 0.74rem !important; color: #6b5a4e !important; line-height: 1.15 !important; flex-shrink: 0 !important; }
        .order-total-info strong { font-size: 0.96rem !important; color: #1c1612 !important; font-weight: 800 !important; font-family: 'Plus Jakarta Sans', sans-serif !important; white-space: nowrap !important; }
        .order-actions { display: flex !important; align-items: center !important; gap: 4px !important; flex-shrink: 0 !important; }
        .order-actions .btn-void, .order-actions .btn-complete, .order-actions .btn-print-receipt, .order-actions .btn-print-void {
            flex: initial !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; text-align: center !important; padding: 0.32rem 0.6rem !important; font-size: 0.74rem !important; font-weight: 700 !important; border-radius: 9999px !important; white-space: nowrap !important; box-sizing: border-box !important; gap: 4px !important; cursor: pointer !important; transition: all 0.2s ease !important;
        }
        .order-actions .btn-void i, .order-actions .btn-complete i, .order-actions .btn-print-receipt i, .order-actions .btn-print-void i { font-size: 0.72rem !important; }

        .btn-print-receipt {
            background: #2c1a14 !important;
            color: #fdfaf6 !important;
            border: 1px solid rgba(197, 153, 88, 0.4) !important;
        }
        .btn-print-receipt:hover {
            background: #c59958 !important;
            color: #fff !important;
            transform: translateY(-1px);
        }

        .btn-print-void {
            background: #fef2f2 !important;
            color: #dc2626 !important;
            border: 1px solid #fecaca !important;
        }
        .btn-print-void:hover {
            background: #dc2626 !important;
            color: #fff !important;
            transform: translateY(-1px);
        }

        /* Live queue ticker */
        .queue-live-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            font-weight: 600;
            color: #137333;
            background: rgba(19,115,51,0.1);
            border: 1px solid rgba(19,115,51,0.2);
            border-radius: 9999px;
            padding: 4px 12px;
            font-family: 'Outfit', sans-serif;
        }
        .queue-live-dot {
            width: 8px; height: 8px;
            background: #137333;
            border-radius: 50%;
            animation: livePulse 1.6s ease-in-out infinite;
        }
        @keyframes livePulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.7); }
        }

        /* Stock alert banner */
        .stock-alert-banner {
            margin: 0.75rem 2rem 0;
            border-radius: 12px;
            padding: 0.7rem 1.1rem;
            font-family: 'Outfit', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .stock-alert-banner.alert-crit { background: rgba(197,34,31,0.1); border: 1px solid rgba(197,34,31,0.3); color: #c5221f; }
        .stock-alert-banner.alert-warn { background: rgba(229,160,0,0.1); border: 1px solid rgba(229,160,0,0.3); color: #b06000; }
        .stock-alert-banner-dismiss {
            background: none; border: none; cursor: pointer; color: inherit; opacity: 0.6; font-size: 1rem; padding: 2px 6px;
        }
        .stock-alert-banner-dismiss:hover { opacity: 1; }

        /* Order card enter animation */
        @keyframes cardSlideIn {
            from { opacity: 0; transform: translateY(16px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0)  scale(1); }
        }
        .order-card-new { animation: cardSlideIn 0.35s cubic-bezier(0.34,1.56,0.64,1); }

        .order-card.status-void, .order-card.status-completed {
            opacity: 0.75;
        }
        .order-card.status-void {
            border-left: 4px solid #dc2626 !important;
        }
        .order-card.status-completed {
            border-left: 4px solid #16a34a !important;
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
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos'"><span class="menu-icon">🍽️</span> All Items</li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=coffee'"><span class="menu-icon">☕</span> Coffee</li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=non-coffee'"><span class="menu-icon">🍵</span> Non-Coffee</li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=lemonade'"><span class="menu-icon">🍹</span> Lemonade</li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=foods'"><span class="menu-icon">🍲</span> Foods</li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/shift-notes'"><span class="menu-icon">📝</span> Shift Notes</li>
                    <li class="menu-item active" onclick="window.location.href='<?= url('') ?>/queue'" style="border-top: 1px solid #e5d9c5; margin-top: 0.5rem; padding-top: 1rem;"><span class="menu-icon">📋</span> Order Queuing</li>
                    <li class="menu-item" id="inventory-menu-item" onclick="window.location.href='<?= url('') ?>/inventory'" style="border-top: 1px solid #e5d9c5; margin-top: 0.5rem; padding-top: 1rem;"><span class="menu-icon">📦</span> Inventory</li>
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
                <div id="queueLiveBadge" class="queue-live-badge">
                    <span class="queue-live-dot"></span>
                    <span id="queueLastUpdated">Live</span>
                </div>
            </header>

            <!-- Stock Alert Banners (injected by JS) -->
            <div id="stockAlertContainer"></div>

            <div style="padding: 0.5rem 2rem 0; display: flex; justify-content: flex-end;">
                <div class="daily-total">
                    <span>TOTAL SALES:</span>
                    <strong id="queueTotalSales">₱ <?= number_format($totalSales, 2) ?></strong>
                </div>
            </div>

            <div class="queue-container" id="queueContainer">
                <?php if (count($orders) === 0): ?>
                    <div class="empty-queue" id="emptyQueue">
                        <i class="fa-solid fa-clipboard-check"></i>
                        <p>No orders placed today.</p>
                    </div>
                <?php else: ?>
                    <div class="orders-grid" id="ordersGrid">
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
                                                    <span class="addons">+ <?= is_array($item->addons) ? (isset($item->addons[0]['name']) ? implode(', ', array_column($item->addons, 'name')) : implode(', ', $item->addons)) : $item->addons ?></span>
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
                                        <?php elseif($order->status === 'completed'): ?>
                                            <button class="btn-print-receipt" onclick="printOrderReceipt(<?= $order->id ?>, 'completed')"><i class="fa-solid fa-print"></i> Receipt</button>
                                        <?php elseif($order->status === 'void'): ?>
                                            <button class="btn-print-void" onclick="printOrderReceipt(<?= $order->id ?>, 'void')"><i class="fa-solid fa-file-invoice"></i> Void Receipt</button>
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
        const BASE_URL = '<?= url('') ?>';
        const CSRF = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
        let pendingVoidOrderId = null;
        let knownOrderIds = new Set(<?= json_encode($orders->pluck('id')->values()->all()) ?>);
        let lastUpdatedSeconds = 0;
        let alertsDismissed = false;

        // Cache for thermal printing
        const ordersCache = new Map();
        const initialOrdersList = <?= json_encode($orders->map(function($order) {
            return [
                'id' => (int)$order->id,
                'status' => (string)$order->status,
                'subtotal' => (float)($order->subtotal ?? $order->total),
                'discount_percent' => (int)($order->discount_percent ?? 0),
                'discount_amount' => (float)($order->discount_amount ?? 0),
                'total' => (float)$order->total,
                'payment_method' => strtoupper((string)($order->payment_method ?? 'CASH')),
                'cashier_name' => (string)($order->cashier_name ?: 'Earthbred Staff'),
                'created_at' => $order->created_at ? $order->created_at->format('h:i A') : '',
                'created_at_full' => $order->created_at ? $order->created_at->format('M d, Y h:i A') : '',
                'items' => $order->items ? $order->items->map(function($i) {
                    return [
                        'product_name' => (string)$i->product_name,
                        'customer_name' => (string)($i->customer_name ?? ''),
                        'quantity' => (int)$i->quantity,
                        'item_total' => (float)$i->item_total,
                        'addons' => is_array($i->addons) ? (isset($i->addons[0]['name']) ? array_column($i->addons, 'name') : $i->addons) : [],
                    ];
                })->values()->all() : []
            ];
        })->values()->all()) ?>;

        if (Array.isArray(initialOrdersList)) {
            initialOrdersList.forEach(o => ordersCache.set(Number(o.id), o));
        }

        // =============================================
        // Thermal Receipt Formatter & Printer
        // =============================================
        function formatQueueThermalReceipt(order, statusOverride = null) {
            const status = (statusOverride || order.status || 'completed').toLowerCase();
            const isVoid = status === 'void';
            const dateTimeStr = order.created_at_full || new Date().toLocaleString();
            const cashier = order.cashier_name || 'Staff';
            const paymentMethodUpper = (order.payment_method || 'CASH').toUpperCase();

            let itemsHtml = '';
            (order.items || []).forEach(item => {
                let name = item.product_name;
                if (item.customer_name) {
                    name += ` (${item.customer_name})`;
                }
                itemsHtml += `
                    <div style="display: flex; justify-content: space-between; margin-bottom: 3px; font-weight: 500;">
                        <span>${item.quantity}x ${name}</span>
                        <span>₱${parseFloat(item.item_total || 0).toFixed(2)}</span>
                    </div>
                `;
                if (item.addons && item.addons.length > 0) {
                    item.addons.forEach(add => {
                        const addName = typeof add === 'object' ? (add.name || add.title) : add;
                        itemsHtml += `
                            <div style="font-size: 10px; padding-left: 10px; color: #444;">
                                + ${addName}
                            </div>
                        `;
                    });
                }
            });

            const subtotal = order.subtotal !== undefined ? order.subtotal : order.total;
            const discountPercent = order.discount_percent || 0;
            const discountAmount = order.discount_amount || 0;
            const total = order.total || 0;

            if (isVoid) {
                return `
                    <div style="text-align: center; border: 2px solid #000; padding: 6px; margin-bottom: 8px; font-weight: bold;">
                        <div style="font-size: 16px; letter-spacing: 1px;">*** VOID ORDER ***</div>
                        <div style="font-size: 10px; margin-top: 2px;">TRANSACTION CANCELLED</div>
                    </div>

                    <div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px;">
                        <h2 style="font-size: 15px; margin: 0; font-weight: bold; text-transform: uppercase;">EARTHBRED COFFEE STUDIO</h2>
                        <p style="font-size: 10px; margin: 2px 0;">Void Security Receipt</p>
                        <p style="font-size: 13px; margin: 4px 0; font-weight: bold;">ORDER #${order.id}</p>
                        <p style="font-size: 10px; margin: 0;">${dateTimeStr}</p>
                        <p style="font-size: 10px; margin: 2px 0;">Cashier: <strong>${cashier}</strong></p>
                        <p style="font-size: 10px; margin: 0; color: #000; font-weight: bold;">VOID AUTH: PIN VERIFIED</p>
                    </div>

                    <div style="border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px;">
                        <div style="font-size: 10px; font-weight: bold; margin-bottom: 4px; text-transform: uppercase;">Cancelled Line Items:</div>
                        ${itemsHtml}
                    </div>

                    <div style="border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px; font-size: 11px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                            <span>Subtotal:</span>
                            <span>₱${parseFloat(subtotal).toFixed(2)}</span>
                        </div>
                        ${discountPercent > 0 ? `
                        <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                            <span>Discount (${discountPercent}%):</span>
                            <span>-₱${parseFloat(discountAmount).toFixed(2)}</span>
                        </div>` : ''}
                        <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin-top: 4px;">
                            <span>VOIDED AMOUNT:</span>
                            <span>₱${parseFloat(total).toFixed(2)}</span>
                        </div>
                    </div>

                    <div style="text-align: center; border-top: 1px dashed #000; padding-top: 8px; font-size: 9px; font-weight: bold; letter-spacing: 0.5px;">
                        <p style="margin: 2px 0;">*** VOIDED TRANSACTION RECORD ***</p>
                        <p style="margin: 0;">NOT VALID FOR REFUND OR REDEMPTION</p>
                    </div>
                `;
            }

            return `
                <div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px;">
                    <h2 style="font-size: 16px; margin: 0; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">EARTHBRED COFFEE STUDIO</h2>
                    <p style="font-size: 10px; margin: 2px 0;">Official Sales Receipt</p>
                    <p style="font-size: 14px; margin: 5px 0; font-weight: bold;">ORDER #${order.id}</p>
                    <p style="font-size: 10px; margin: 0;">${dateTimeStr}</p>
                    <p style="font-size: 10px; margin: 2px 0;">Cashier: <strong>${cashier}</strong></p>
                </div>

                <div style="border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px;">
                    ${itemsHtml}
                </div>

                <div style="border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px; font-size: 11px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                        <span>Subtotal:</span>
                        <span>₱${parseFloat(subtotal).toFixed(2)}</span>
                    </div>
                    ${discountPercent > 0 ? `
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                        <span>Discount (${discountPercent}%):</span>
                        <span>-₱${parseFloat(discountAmount).toFixed(2)}</span>
                    </div>` : ''}
                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin-top: 4px;">
                        <span>TOTAL PAID:</span>
                        <span>₱${parseFloat(total).toFixed(2)}</span>
                    </div>
                </div>

                <div style="text-align: left; font-size: 10px; margin-bottom: 8px;">
                    <p style="margin: 2px 0;">Payment Method: <strong>${paymentMethodUpper}</strong></p>
                    <p style="margin: 2px 0;">Status: <strong>PAID & COMPLETED</strong></p>
                </div>

                <div style="text-align: center; border-top: 1px dashed #000; padding-top: 8px; font-size: 10px;">
                    <p style="margin: 2px 0; font-weight: bold;">THANK YOU FOR BREWING WITH US!</p>
                    <p style="margin: 0;">Have a great day & visit us again.</p>
                </div>
            `;
        }

        window.printOrderReceipt = function(orderId, statusOverride = null) {
            const order = ordersCache.get(Number(orderId));
            if (!order) {
                console.warn('Order data not found in cache for #' + orderId);
                return;
            }

            const receiptHtml = formatQueueThermalReceipt(order, statusOverride);

            let printIframe = document.getElementById('queuePrintIframe');
            if (!printIframe) {
                printIframe = document.createElement('iframe');
                printIframe.id = 'queuePrintIframe';
                printIframe.style.position = 'fixed';
                printIframe.style.right = '0';
                printIframe.style.bottom = '0';
                printIframe.style.width = '0';
                printIframe.style.height = '0';
                printIframe.style.border = '0';
                document.body.appendChild(printIframe);
            }

            const iframeDoc = printIframe.contentDocument || printIframe.contentWindow.document;
            iframeDoc.open();
            iframeDoc.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="utf-8">
                    <title>Receipt #${order.id}</title>
                    <style>
                        @page { size: 80mm auto; margin: 0; }
                        body {
                            font-family: 'Courier New', Courier, monospace;
                            font-size: 12px;
                            line-height: 1.35;
                            color: #000;
                            background: #fff;
                            margin: 0;
                            padding: 4mm 6mm;
                            width: 80mm;
                            box-sizing: border-box;
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                        }
                        * { box-sizing: border-box; }
                    </style>
                </head>
                <body>
                    ${receiptHtml}
                </body>
                </html>
            `);
            iframeDoc.close();

            setTimeout(() => {
                try {
                    printIframe.contentWindow.focus();
                    printIframe.contentWindow.print();
                } catch (e) {
                    console.error('Print error:', e);
                }
            }, 300);
        };

        // =============================================
        // Void Modal
        // =============================================
        window.toggleQueueVoidEye = function() {
            const input = document.getElementById('voidPinInput');
            const icon = document.getElementById('queueVoidEyeIcon');
            if (!input || !icon) return;
            if (input.type === 'password') {
                input.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash');
            } else {
                input.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye');
            }
        };

        window.closeVoidModal = function() {
            const modal = document.getElementById('voidAuthModal');
            if (modal) modal.style.display = 'none';
            const input = document.getElementById('voidPinInput');
            if (input) { input.value = ''; input.type = 'password'; }
            const icon = document.getElementById('queueVoidEyeIcon');
            if (icon) icon.classList.replace('fa-eye-slash','fa-eye');
            pendingVoidOrderId = null;
        };

        window.submitVoidAuth = async function() {
            const pinInput = document.getElementById('voidPinInput');
            const pin = pinInput ? pinInput.value.trim() : '';
            if (!pin) {
                if (typeof PosDialog !== 'undefined') {
                    PosDialog.alert({ title: 'PIN Required', message: 'Please enter the 4-digit Void Authorization PIN.', icon: 'fa-key', iconType: 'warning' });
                } else {
                    alert('Please enter a PIN');
                }
                return;
            }
            try {
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : '';
                const r = await fetch(BASE_URL + '/api/void-pin/verify', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ pin })
                });
                const result = await r.json();
                if (result.success) {
                    const orderId = pendingVoidOrderId;
                    closeVoidModal();
                    executeOrderStatusUpdate(orderId, 'void', pin);
                } else {
                    if (typeof PosDialog !== 'undefined') {
                        PosDialog.alert({ title: 'Authentication Failed', message: result.message || 'Invalid Void PIN.', icon: 'fa-triangle-exclamation', iconType: 'danger' });
                    } else {
                        alert(result.message || 'Authentication failed');
                    }
                }
            } catch (e) {
                console.error(e);
                alert('Connection error. Please try again.');
            }
        };

        window.executeOrderStatusUpdate = async function(orderId, status, pin = null) {
            try {
                const payload = { status };
                if (pin) payload.pin = pin;
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : '';
                const r = await fetch(`${BASE_URL}/orders/${orderId}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await r.json();
                if (result.success) {
                    // Update cache status
                    if (ordersCache.has(Number(orderId))) {
                        const o = ordersCache.get(Number(orderId));
                        o.status = status;
                        ordersCache.set(Number(orderId), o);
                    }

                    // If completing order or voiding order, print receipt
                    if (status === 'completed') {
                        printOrderReceipt(orderId, 'completed');
                    } else if (status === 'void') {
                        printOrderReceipt(orderId, 'void');
                    }

                    pollQueue(); // Refresh queue immediately after status change
                } else {
                    if (typeof PosDialog !== 'undefined') {
                        PosDialog.alert({ title: 'Error', message: result.message || 'Error updating status', icon: 'fa-triangle-exclamation', iconType: 'danger' });
                    } else {
                        alert(result.message || 'Error updating status');
                    }
                }
            } catch (e) {
                console.error(e);
                alert('Connection error');
            }
        };

        window.updateOrderStatus = function(orderId, status) {
            if (status === 'void') {
                pendingVoidOrderId = orderId;
                const modal = document.getElementById('voidAuthModal');
                if (modal) modal.style.display = 'flex';
                const input = document.getElementById('voidPinInput');
                if (input) {
                    input.value = '';
                    setTimeout(() => input.focus(), 100);
                }
                return;
            }
            executeOrderStatusUpdate(orderId, status);
        };

        // Bind Enter key and backdrop click on void modal
        document.addEventListener('DOMContentLoaded', function() {
            const voidPinInput = document.getElementById('voidPinInput');
            if (voidPinInput) {
                voidPinInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        submitVoidAuth();
                    }
                });
            }
            const voidModal = document.getElementById('voidAuthModal');
            if (voidModal) {
                voidModal.addEventListener('click', function(e) {
                    if (e.target === this) closeVoidModal();
                });
            }
        });

        // =============================================
        // Build order card HTML
        // =============================================
        function buildOrderCard(order) {
            const itemsHtml = order.items.map(item => {
                const customerHtml = item.customer_name ? `<span class="customer"><i class="fa-solid fa-user"></i> ${item.customer_name}</span>` : '';
                const addonsHtml = item.addons && item.addons.length > 0 ? `<span class="addons">+ ${item.addons.join(', ')}</span>` : '';
                return `
                    <div class="order-item">
                        <span class="qty">${item.quantity}x</span>
                        <div class="item-details">
                            <span class="name">${item.product_name}</span>
                            ${customerHtml}
                            ${addonsHtml}
                        </div>
                        <span class="price">₱ ${parseFloat(item.item_total).toLocaleString('en-PH', {minimumFractionDigits:2,maximumFractionDigits:2})}</span>
                    </div>`;
            }).join('');

            let actionsHtml = '';
            if (order.status === 'pending') {
                actionsHtml = `
                    <button class="btn-void" onclick="updateOrderStatus(${order.id}, 'void')"><i class="fa-solid fa-ban"></i> Void</button>
                    <button class="btn-complete" onclick="updateOrderStatus(${order.id}, 'completed')"><i class="fa-solid fa-check"></i> Complete</button>`;
            } else if (order.status === 'completed') {
                actionsHtml = `<button class="btn-print-receipt" onclick="printOrderReceipt(${order.id}, 'completed')"><i class="fa-solid fa-print"></i> Receipt</button>`;
            } else if (order.status === 'void') {
                actionsHtml = `<button class="btn-print-void" onclick="printOrderReceipt(${order.id}, 'void')"><i class="fa-solid fa-file-invoice"></i> Void Receipt</button>`;
            }

            const totalFormatted = parseFloat(order.total).toLocaleString('en-PH', {minimumFractionDigits:2,maximumFractionDigits:2});

            return `
                <div class="order-card status-${order.status} order-card-new" id="order-card-${order.id}">
                    <div class="order-header">
                        <div>
                            <h3>Order #${order.id}</h3>
                            <span class="order-time">${order.created_at}</span>
                        </div>
                        <span class="badge badge-${order.status}">${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</span>
                    </div>
                    <div class="order-items">${itemsHtml}</div>
                    <div class="order-footer">
                        <div class="order-total-info">
                            <span>Total:</span>
                            <strong>₱ ${totalFormatted}</strong>
                        </div>
                        <div class="order-actions">${actionsHtml}</div>
                    </div>
                </div>`;
        }

        // =============================================
        // Live Queue Poll
        // =============================================
        async function pollQueue() {
            try {
                const r = await fetch(BASE_URL + '/api/queue/live', { credentials: 'same-origin' });
                if (!r.ok) return;
                const data = await r.json();
                if (!data.success) return;

                const orders = data.orders;
                lastUpdatedSeconds = 0;

                // Update total sales
                const totalEl = document.getElementById('queueTotalSales');
                if (totalEl) totalEl.textContent = '₱ ' + parseFloat(data.total_sales).toLocaleString('en-PH', {minimumFractionDigits:2,maximumFractionDigits:2});

                const emptyQueue = document.getElementById('emptyQueue');
                let grid = document.getElementById('ordersGrid');

                if (orders.length === 0) {
                    if (grid) grid.innerHTML = '';
                    if (!emptyQueue) {
                        const container = document.getElementById('queueContainer');
                        container.innerHTML = '<div class="empty-queue" id="emptyQueue"><i class="fa-solid fa-clipboard-check"></i><p>No orders placed today.</p></div>';
                    }
                    return;
                }

                // Create grid if missing
                if (!grid) {
                    const container = document.getElementById('queueContainer');
                    container.innerHTML = '<div class="orders-grid" id="ordersGrid"></div>';
                    grid = document.getElementById('ordersGrid');
                    knownOrderIds = new Set();
                }

                // Remove empty state
                const eq = document.getElementById('emptyQueue');
                if (eq) eq.remove();

                // Add new orders / update status on existing
                orders.forEach(order => {
                    ordersCache.set(Number(order.id), order);
                    const existingCard = document.getElementById('order-card-' + order.id);
                    if (existingCard) {
                        // Update status class and badge if changed
                        const currentStatus = [...existingCard.classList].find(c => c.startsWith('status-'))?.replace('status-','');
                        if (currentStatus !== order.status) {
                            existingCard.className = existingCard.className.replace(/status-\w+/, 'status-' + order.status);
                            const badge = existingCard.querySelector('.badge');
                            if (badge) {
                                badge.className = 'badge badge-' + order.status;
                                badge.textContent = order.status.charAt(0).toUpperCase() + order.status.slice(1);
                            }
                            const actions = existingCard.querySelector('.order-actions');
                            if (actions) {
                                if (order.status === 'completed') {
                                    actions.innerHTML = `<button class="btn-print-receipt" onclick="printOrderReceipt(${order.id}, 'completed')"><i class="fa-solid fa-print"></i> Receipt</button>`;
                                } else if (order.status === 'void') {
                                    actions.innerHTML = `<button class="btn-print-void" onclick="printOrderReceipt(${order.id}, 'void')"><i class="fa-solid fa-file-invoice"></i> Void Receipt</button>`;
                                } else {
                                    actions.innerHTML = `
                                        <button class="btn-void" onclick="updateOrderStatus(${order.id}, 'void')"><i class="fa-solid fa-ban"></i> Void</button>
                                        <button class="btn-complete" onclick="updateOrderStatus(${order.id}, 'completed')"><i class="fa-solid fa-check"></i> Complete</button>`;
                                }
                            }
                        }
                    } else {
                        // New order — prepend it
                        grid.insertAdjacentHTML('afterbegin', buildOrderCard(order));
                        knownOrderIds.add(order.id);
                    }
                });

            } catch(e) { /* silent */ }
        }

        // Poll stock alerts for banners
        async function pollStockAlerts() {
            if (alertsDismissed) return;
            try {
                const r = await fetch(BASE_URL + '/api/pos/stock-status', { credentials: 'same-origin' });
                if (!r.ok) return;
                const data = await r.json();
                renderStockAlerts(data);
            } catch(e) {}
        }

        function renderStockAlerts(data) {
            const container = document.getElementById('stockAlertContainer');
            container.innerHTML = '';

            if (data.out_of_stock_alerts && data.out_of_stock_alerts.length > 0) {
                const names = data.out_of_stock_alerts.map(a => a.item_name).join(', ');
                container.insertAdjacentHTML('beforeend', `
                    <div class="stock-alert-banner alert-crit">
                        <span><i class="fa-solid fa-circle-exclamation"></i> <strong>OUT OF STOCK:</strong> ${names}</span>
                        <button class="stock-alert-banner-dismiss" onclick="this.closest('.stock-alert-banner').remove()"><i class="fa-solid fa-xmark"></i></button>
                    </div>`);
            }
            if (data.low_stock_alerts && data.low_stock_alerts.length > 0) {
                const names = data.low_stock_alerts.map(a => `${a.item_name} (${a.quantity} left)`).join(', ');
                container.insertAdjacentHTML('beforeend', `
                    <div class="stock-alert-banner alert-warn">
                        <span><i class="fa-solid fa-triangle-exclamation"></i> <strong>LOW STOCK:</strong> ${names}</span>
                        <button class="stock-alert-banner-dismiss" onclick="this.closest('.stock-alert-banner').remove()"><i class="fa-solid fa-xmark"></i></button>
                    </div>`);
            }
        }

        // Ticker: "Updated X seconds ago"
        function updateTicker() {
            lastUpdatedSeconds++;
            const el = document.getElementById('queueLastUpdated');
            if (!el) return;
            el.textContent = lastUpdatedSeconds <= 5 ? 'Live' : `Updated ${lastUpdatedSeconds}s ago`;
        }

        // Start polling
        pollQueue();
        pollStockAlerts();
        setInterval(pollQueue, 8000);
        setInterval(pollStockAlerts, 60000);
        setInterval(updateTicker, 1000);
    </script>
    <script src="<?= asset('js/pos-modal.js') ?>?v=1.0.0"></script>
    <script src="<?= asset('js/clock-out.js') ?>?v=1.0.0"></script>
</body>
</html>
