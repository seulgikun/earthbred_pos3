document.addEventListener('DOMContentLoaded', () => {
    
    // Back to Menu
    const backBtn = document.getElementById('backToMenuBtn');
    if (backBtn) {
        backBtn.addEventListener('click', () => {
            window.location.href = '/pos';
        });
    }

    // Payment Methods Selection
    const paymentBtns = document.querySelectorAll('.payment-btn');
    paymentBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            paymentBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    // Dummy Item Data
    let basePrice = 95;
    let quantity = 1;
    let currentDiscountPercent = 10; // Default matches screenshot

    // Elements
    const qtyMinus = document.querySelector('.minus-btn');
    const qtyPlus = document.querySelector('.plus-btn');
    const qtyValue = document.querySelector('.qty-value');
    const itemTotalEl = document.querySelector('.item-total');
    
    const leftTotalDueEl = document.getElementById('leftTotalDue');
    const subtotalAmountEl = document.getElementById('subtotalAmount');
    const discountAmountEl = document.getElementById('discountAmount');
    const rightTotalDueEl = document.getElementById('rightTotalDue');
    
    // Discount Selection
    const discountBtns = document.querySelectorAll('.discount-btn');
    discountBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Optional: allow toggling off if clicked again? Or just require one selected.
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

    // Quantity Controls
    if (qtyMinus && qtyPlus) {
        qtyMinus.addEventListener('click', () => {
            if (quantity > 1) {
                quantity--;
                updateTotals();
            }
        });

        qtyPlus.addEventListener('click', () => {
            quantity++;
            updateTotals();
        });
    }

    // Process Transaction
    const processBtn = document.getElementById('processBtn');
    if (processBtn) {
        processBtn.addEventListener('click', () => {
            alert('Transaction Processed Successfully!');
            window.location.href = '/pos';
        });
    }

    // Update Math Logic
    function updateTotals() {
        // Update item row
        qtyValue.innerText = quantity;
        let subtotal = basePrice * quantity;
        itemTotalEl.innerText = `₱ ${subtotal}`;
        
        // Update Left Total Due
        leftTotalDueEl.innerText = `₱ ${subtotal}`;
        
        // Update Right Summaries
        subtotalAmountEl.innerText = `₱ ${subtotal}`;
        
        let discountVal = 0;
        if (currentDiscountPercent > 0) {
            // Calculate and floor it as shown in screenshot (9.5 -> 9)
            discountVal = Math.floor(subtotal * (currentDiscountPercent / 100)); 
            discountAmountEl.innerText = `- ₱ ${discountVal}`;
            discountAmountEl.style.color = '#28a745'; // text-green
        } else {
            discountAmountEl.innerText = `₱ 0`;
            discountAmountEl.style.color = '#1a1a1a'; // default color
        }

        let finalTotal = subtotal - discountVal;
        rightTotalDueEl.innerText = `₱ ${finalTotal}`;
    }

    // Initialize UI on load
    updateTotals();
});
