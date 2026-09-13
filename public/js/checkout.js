document.addEventListener('DOMContentLoaded', () => {
    
    // =============================================
    // Read Cart from localStorage
    // =============================================
    const BASE = (function() {
        const pathname = window.location.pathname;
        const idx = pathname.toLowerCase().indexOf('/backend/public');
        return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
    })();

    let cart = JSON.parse(localStorage.getItem('earthbred_cart') || '[]');
    let currentDiscountPercent = 0;
    let lastProcessedOrderData = null;
    let lastProcessedOrderId = null;

    const orderItemsContainer = document.getElementById('orderItemsContainer');
    const leftTotalDueEl = document.getElementById('leftTotalDue');
    const subtotalAmountEl = document.getElementById('subtotalAmount');
    const discountAmountEl = document.getElementById('discountAmount');
    const rightTotalDueEl = document.getElementById('rightTotalDue');

    // =============================================
    // Render Cart Items
    // =============================================
    function renderCart() {
        orderItemsContainer.innerHTML = '';

        if (cart.length === 0) {
            orderItemsContainer.innerHTML = `
                <div class="empty-cart-msg">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <p>Your cart is empty.<br>Go back to add items.</p>
                </div>
            `;
            updateTotals();
            return;
        }

        cart.forEach((item, index) => {
            const row = document.createElement('div');
            row.className = 'summary-item';
            row.innerHTML = `
                <div class="col-item item-details">
                    <img src="${item.image || ''}" alt="${item.product_name}" class="item-img">
                    <span class="item-name">${item.product_name}</span>
                    <button class="remove-item-btn" data-index="${index}" title="Remove item">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
                <div class="col-name item-customer-name">${item.customer_name || '—'}</div>
                <div class="col-price item-price">₱ ${item.price}</div>
                <div class="col-qty item-qty">
                    <button class="qty-control-btn minus-btn" data-index="${index}"><i class="fa-solid fa-minus"></i></button>
                    <span class="qty-value">${item.quantity}</span>
                    <button class="qty-control-btn plus-btn" data-index="${index}"><i class="fa-solid fa-plus"></i></button>
                </div>
                <div class="col-total item-total">₱ ${item.item_total}</div>
            `;
            orderItemsContainer.appendChild(row);
        });

        // Attach quantity & remove event listeners
        document.querySelectorAll('.minus-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-index'));
                if (cart[idx].quantity > 1) {
                    cart[idx].quantity--;
                    recalcItemTotal(idx);
                    saveAndRender();
                }
            });
        });

        document.querySelectorAll('.plus-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-index'));
                cart[idx].quantity++;
                recalcItemTotal(idx);
                saveAndRender();
            });
        });

        document.querySelectorAll('.remove-item-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.getAttribute('data-index'));
                cart.splice(idx, 1);
                saveAndRender();
            });
        });

        updateTotals();
    }

    function recalcItemTotal(idx) {
        const item = cart[idx];
        item.item_total = (item.price + (item.addons_total || 0)) * item.quantity;
    }

    function saveAndRender() {
        localStorage.setItem('earthbred_cart', JSON.stringify(cart));
        renderCart();
    }

    // =============================================
    // Update Totals
    // =============================================
    function updateTotals() {
        let subtotal = 0;
        cart.forEach(item => {
            subtotal += item.item_total;
        });

        leftTotalDueEl.innerText = `₱ ${subtotal}`;
        subtotalAmountEl.innerText = `₱ ${subtotal}`;

        let discountVal = 0;
        if (currentDiscountPercent > 0) {
            discountVal = Math.floor(subtotal * (currentDiscountPercent / 100));
            discountAmountEl.innerText = `- ₱ ${discountVal}`;
            discountAmountEl.style.color = '#28a745';
        } else {
            discountAmountEl.innerText = `₱ 0`;
            discountAmountEl.style.color = '#1a1a1a';
        }

        let finalTotal = subtotal - discountVal;
        rightTotalDueEl.innerText = `₱ ${finalTotal}`;
    }

    // =============================================
    // Back to Menu
    // =============================================
    const backBtn = document.getElementById('backToMenuBtn');
    if (backBtn) {
        backBtn.addEventListener('click', () => {
            window.location.href = BASE + '/pos';
        });
    }

    // =============================================
    // Payment Methods Selection
    // =============================================
    const paymentBtns = document.querySelectorAll('.payment-btn');
    paymentBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            paymentBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // =============================================
    // Discount Selection
    // =============================================
    const discountBtns = document.querySelectorAll('.discount-btn');
    discountBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.classList.contains('active')) {
                btn.classList.remove('active');
                currentDiscountPercent = 0;
            } else {
                discountBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentDiscountPercent = parseInt(btn.getAttribute('data-discount'));
            }
            updateTotals();
        });
    });

    // =============================================
    // Format POS Thermal Receipt (58mm/80mm Standard)
    // =============================================
    function formatThermalReceipt(orderData, orderId) {
        const now = new Date();
        const dateTimeStr = now.toLocaleDateString() + ' ' + now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        let itemsHtml = '';
        orderData.items.forEach(item => {
            let name = item.product_name;
            if (item.customer_name) {
                name += ` (${item.customer_name})`;
            }
            itemsHtml += `
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px; font-weight: 500;">
                    <span>${item.quantity}x ${name}</span>
                    <span>₱${parseFloat(item.item_total).toFixed(2)}</span>
                </div>
            `;
            if (item.addons && item.addons.length > 0) {
                item.addons.forEach(add => {
                    const addName = typeof add === 'object' ? (add.name || add.title) : add;
                    itemsHtml += `
                        <div style="font-size: 10px; padding-left: 10px; color: #333;">
                            + ${addName}
                        </div>
                    `;
                });
            }
        });

        const paymentMethodUpper = (orderData.payment_method || 'CASH').toUpperCase();

        return `
            <div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px;">
                <h2 style="font-size: 16px; margin: 0; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">EARTHBRED COFFEE STUDIO</h2>
                <p style="font-size: 10px; margin: 2px 0;">Official Sales Receipt</p>
                <p style="font-size: 13px; margin: 5px 0; font-weight: bold;">ORDER #${orderId}</p>
                <p style="font-size: 10px; margin: 0;">${dateTimeStr}</p>
            </div>

            <div style="border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px;">
                ${itemsHtml}
            </div>

            <div style="border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px; font-size: 11px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                    <span>Subtotal:</span>
                    <span>₱${parseFloat(orderData.subtotal).toFixed(2)}</span>
                </div>
                ${orderData.discount_percent > 0 ? `
                <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                    <span>Discount (${orderData.discount_percent}%):</span>
                    <span>-₱${parseFloat(orderData.discount_amount).toFixed(2)}</span>
                </div>` : ''}
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin-top: 4px;">
                    <span>TOTAL PAID:</span>
                    <span>₱${parseFloat(orderData.total).toFixed(2)}</span>
                </div>
            </div>

            <div style="text-align: left; font-size: 10px; margin-bottom: 10px;">
                <p style="margin: 2px 0;">Payment Method: <strong>${paymentMethodUpper}</strong></p>
                <p style="margin: 2px 0;">Status: <strong>PAID & COMPLETED</strong></p>
            </div>

            <div style="text-align: center; border-top: 1px dashed #000; padding-top: 8px; font-size: 10px;">
                <p style="margin: 2px 0; font-weight: bold;">THANK YOU FOR BREWING WITH US!</p>
                <p style="margin: 0;">Have a great day & visit us again.</p>
            </div>
        `;
    }

    function triggerThermalPrint(orderData, orderId) {
        const receiptHtml = formatThermalReceipt(orderData, orderId);
        
        // Populate standard on-page container
        const receiptContainer = document.getElementById('printableReceipt');
        if (receiptContainer) {
            receiptContainer.innerHTML = receiptHtml;
        }

        // Use dedicated hidden print iframe for seamless tablet & mobile printing
        let printIframe = document.getElementById('thermalPrintIframe');
        if (!printIframe) {
            printIframe = document.createElement('iframe');
            printIframe.id = 'thermalPrintIframe';
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
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Receipt #${orderId}</title>
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
            } catch (err) {
                // Fallback to window.print() if iframe print is restricted
                if (receiptContainer) {
                    receiptContainer.style.display = 'block';
                }
                window.print();
            }
        }, 350);
    }

    // =============================================
    // Process Transaction — POST to backend & Auto Print
    // =============================================
    const processBtn = document.getElementById('processBtn');
    const successModal = document.getElementById('successModal');
    const successOkBtn = document.getElementById('successOkBtn');
    const printReceiptBtn = document.getElementById('printReceiptBtn');
    const successOrderId = document.getElementById('successOrderId');

    if (processBtn) {
        processBtn.addEventListener('click', async () => {
            if (cart.length === 0) {
                PosDialog.alert({
                    title: 'Empty Cart',
                    message: 'Your cart is empty! Please add items before processing checkout.',
                    icon: 'fa-cart-shopping',
                    iconType: 'warning'
                });
                return;
            }

            // Gather data
            let subtotal = 0;
            cart.forEach(item => { subtotal += item.item_total; });
            let discountVal = currentDiscountPercent > 0
                ? Math.floor(subtotal * (currentDiscountPercent / 100))
                : 0;
            let finalTotal = subtotal - discountVal;

            const activePayment = document.querySelector('.payment-btn.active');
            const paymentMethod = activePayment
                ? activePayment.getAttribute('data-method')
                : 'cash';

            const rawCashierId = localStorage.getItem('userId');
            const cashierId = rawCashierId ? parseInt(rawCashierId, 10) : null;
            const cashierName = localStorage.getItem('userName') || 'Earthbred Cashier';

            const orderData = {
                items: cart.map(item => ({
                    customer_name: item.customer_name || null,
                    product_name: item.product_name,
                    price: item.price,
                    quantity: item.quantity,
                    addons: item.addons || [],
                    addons_total: item.addons_total || 0,
                    item_total: item.item_total
                })),
                subtotal: subtotal,
                discount_percent: currentDiscountPercent,
                discount_amount: discountVal,
                total: finalTotal,
                payment_method: paymentMethod,
                cashier_id: isNaN(cashierId) ? null : cashierId,
                cashier_name: cashierName
            };

            // Disable button while processing
            processBtn.disabled = true;
            processBtn.textContent = 'PROCESSING...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const response = await fetch(BASE + '/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(orderData)
                });

                const result = await response.json();

                if (result.success) {
                    lastProcessedOrderData = orderData;
                    lastProcessedOrderId = result.order_id;

                    // Clear cart
                    localStorage.removeItem('earthbred_cart');
                    cart = [];

                    // Show success modal
                    if (successOrderId) {
                        successOrderId.textContent = `Order #${result.order_id}`;
                    }
                    successModal.style.display = 'flex';

                    // Automatically Trigger Thermal Printing
                    triggerThermalPrint(orderData, result.order_id);

                } else {
                    PosDialog.alert({
                        title: 'Transaction Failed',
                        message: result.message || 'Error processing order. Please try again.',
                        icon: 'fa-circle-exclamation',
                        iconType: 'danger'
                    });
                    processBtn.disabled = false;
                    processBtn.textContent = 'PROCESS TRANSACTION';
                }
            } catch (err) {
                console.error('Error:', err);
                PosDialog.alert({
                    title: 'Network Error',
                    message: 'Failed to process order. Please check your connection.',
                    icon: 'fa-wifi',
                    iconType: 'danger'
                });
                processBtn.disabled = false;
                processBtn.textContent = 'PROCESS TRANSACTION';
            }
        });
    }

    // Manual Re-print receipt button on Success Modal
    if (printReceiptBtn) {
        printReceiptBtn.addEventListener('click', () => {
            if (lastProcessedOrderData && lastProcessedOrderId) {
                triggerThermalPrint(lastProcessedOrderData, lastProcessedOrderId);
            }
        });
    }

    // Success modal OK button
    if (successOkBtn) {
        successOkBtn.addEventListener('click', () => {
            window.location.href = BASE + '/pos';
        });
    }

    // Initial Render
    renderCart();
});
