<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title>Earthbred - Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/checkout.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/pos-modal.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/ios26-theme.css') ?>?v=1.0.0">
    <link rel="icon" type="image/png" href="<?= asset('favicon.png') ?>?v=3.0">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>?v=3.0">
    <style>
        /* POS Thermal Receipt Print Styling */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-height: auto !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .app-container, .checkout-header, .checkout-grid, .success-overlay, header, nav, aside {
                display: none !important;
                visibility: hidden !important;
            }
            #printableReceipt {
                display: block !important;
                visibility: visible !important;
                position: static !important;
                width: 100% !important;
                max-width: 80mm !important;
                margin: 0 auto !important;
                padding: 4mm 6mm !important;
                font-family: 'Courier New', Courier, monospace !important;
                font-size: 12px !important;
                line-height: 1.35 !important;
                color: #000000 !important;
                background: #ffffff !important;
                box-shadow: none !important;
            }
            #printableReceipt * {
                visibility: visible !important;
                color: #000000 !important;
            }
        }
        .printable-receipt {
            display: none;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <header class="checkout-header">
            <button class="back-btn" id="backToMenuBtn">
                <i class="fa-solid fa-arrow-left"></i> Back to Menu
            </button>
        </header>

        <!-- Main Layout -->
        <main class="checkout-grid">
            
            <!-- Left Column: Order Summary -->
            <section class="order-summary-panel">
                <div class="summary-header">
                    <div class="col-item">Order Summary</div>
                    <div class="col-name">NAME</div>
                    <div class="col-price">PRICE</div>
                    <div class="col-qty">QUANTITY</div>
                    <div class="col-total">TOTAL</div>
                </div>

                <div class="summary-list" id="orderItemsContainer">
                    <!-- Items will be populated dynamically by checkout.js -->
                </div>

                <div class="summary-footer">
                    <div class="footer-label">TOTAL DUE</div>
                    <div class="footer-amount" id="leftTotalDue">₱ 0</div>
                </div>
            </section>

            <!-- Right Column: Actions -->
            <section class="action-panel">
                
                <div class="payment-section">
                    <h4>PAYMENT METHOD</h4>
                    <div class="payment-methods">
                        <button class="payment-btn active" data-method="cash">
                            <i class="fa-solid fa-money-bill-wave text-green"></i>
                            <span>Cash</span>
                        </button>
                        <button class="payment-btn" data-method="gcash">
                            <i class="fa-solid fa-mobile-screen text-blue"></i>
                            <span>GCash</span>
                        </button>
                    </div>
                </div>

                <div class="discount-section">
                    <h4>DISCOUNT</h4>
                    <div class="discount-options">
                        <?php if(isset($discounts) && count($discounts) > 0): ?>
                            <?php foreach($discounts as $d): ?>
                                <button class="discount-btn" data-discount="<?= $d->percentage ?>"><?= htmlspecialchars($d->name) ?></button>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="font-size:0.8rem; color:#888;">No discounts available.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="totals-section">
                    <div class="totals-row">
                        <span>Subtotal</span>
                        <span id="subtotalAmount">₱ 0</span>
                    </div>
                    <div class="totals-row">
                        <span>Discount</span>
                        <span id="discountAmount">₱ 0</span>
                    </div>
                    
                    <hr class="totals-divider">
                    
                    <div class="totals-row final-total-row">
                        <span>TOTAL DUE</span>
                        <span id="rightTotalDue">₱ 0</span>
                    </div>

                    <button class="process-btn" id="processBtn">PROCESS TRANSACTION</button>
                </div>

            </section>

        </main>
    </div>
    
    <!-- Success Modal -->
    <div class="success-overlay" id="successModal">
        <div class="success-content">
            <div class="success-icon"><i class="fa-solid fa-circle-check"></i></div>
            <h2>Order Processed!</h2>
            <p id="successOrderId">Order #---</p>
            <div style="display: flex; gap: 10px; margin-top: 15px; width: 100%;">
                <button class="success-btn" id="printReceiptBtn" style="background-color: #6a3a30;"><i class="fa-solid fa-print"></i> Re-print Receipt</button>
                <button class="success-btn" id="successOkBtn" style="flex: 1;">Back to POS</button>
            </div>
        </div>
    </div>

    <!-- Thermal Print Container -->
    <div id="printableReceipt" class="printable-receipt"></div>

    <script src="<?= asset('js/pos-modal.js') ?>?v=1.0.0"></script>
    <script src="<?= asset('js/clock-out.js') ?>?v=1.0.0"></script>
    <script src="<?= asset('js/checkout.js') ?>?v=1.2.0"></script>
</body>
</html>
