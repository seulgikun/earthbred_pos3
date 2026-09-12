document.addEventListener('DOMContentLoaded', () => {
    const BASE = (function() {
        const pathname = window.location.pathname;
        const idx = pathname.toLowerCase().indexOf('/backend/public');
        return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
    })();
    // =============================================
    // Cart Management (localStorage)
    // =============================================
    function getCart() {
        return JSON.parse(localStorage.getItem('earthbred_cart') || '[]');
    }

    function saveCart(cart) {
        localStorage.setItem('earthbred_cart', JSON.stringify(cart));
        updateCartBadge();
    }

    function updateCartBadge() {
        const cart = getCart();
        const badge = document.getElementById('cartBadge');
        if (badge) {
            if (cart.length > 0) {
                badge.textContent = cart.length;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    // Note: Responsive Sidebar Drawer Toggle is handled globally by clock-out.js

    // =============================================
    // Unified Product Filtering (Sidebar & Tablet Bar)
    // =============================================
    const menuItems = document.querySelectorAll('.menu-item');
    const categoryChips = document.querySelectorAll('.category-chip');
    const productCards = document.querySelectorAll('.product-card');

    function applyCategoryFilter(filterValue) {
        if (!filterValue) return;

        // Sync menu item pills
        menuItems.forEach(i => {
            if (i.getAttribute('data-filter') === filterValue) {
                i.classList.add('active');
            } else if (i.getAttribute('data-filter')) {
                i.classList.remove('active');
            }
        });

        // Sync tablet chips
        categoryChips.forEach(chip => {
            if (chip.getAttribute('data-filter') === filterValue) {
                chip.classList.add('active');
            } else {
                chip.classList.remove('active');
            }
        });

        // Filter cards
        productCards.forEach(card => {
            if (filterValue === 'all') {
                card.style.display = 'flex';
            } else {
                if (card.getAttribute('data-category') === filterValue) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            }
        });
    }

    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            // Skip navigation links
            if (item.id === 'inventory-menu-item') return;

            const filterValue = item.getAttribute('data-filter');
            if (filterValue) {
                applyCategoryFilter(filterValue);
            }

            // Close drawer if on tablet/mobile
            if (window.innerWidth <= 1280) {
                if (typeof window.closeGlobalSidebar === 'function') window.closeGlobalSidebar();
            }
        });
    });

    categoryChips.forEach(chip => {
        chip.addEventListener('click', () => {
            const filterValue = chip.getAttribute('data-filter');
            if (filterValue) {
                applyCategoryFilter(filterValue);
            }
        });
    });

    // =============================================
    // Clock out logic handled globally by clock-out.js

    // =============================================
    // Add cart badge to the Order button
    // =============================================
    const mainOrderBtn = document.querySelector('.order-btn');
    if (mainOrderBtn) {
        // Inject badge element
        mainOrderBtn.style.position = 'relative';
        const badge = document.createElement('span');
        badge.id = 'cartBadge';
        badge.style.cssText = `
            display: none;
            position: absolute;
            top: -6px; right: -6px;
            background: #c0392b;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            width: 20px; height: 20px;
            border-radius: 50%;
            justify-content: center;
            align-items: center;
            font-family: 'Montserrat', sans-serif;
        `;
        mainOrderBtn.appendChild(badge);
        updateCartBadge();

        mainOrderBtn.addEventListener('click', () => {
            const cart = getCart();
            if (cart.length === 0) {
                showToast('Your cart is empty. Add items first!', '#e74c3c');
                return;
            }
            window.location.href = BASE + '/checkout';
        });
    }

    // =============================================
    // Modal Logic
    // =============================================
    const modal = document.getElementById('productModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const modalProductName = document.getElementById('modalProductName');
    const modalTotalPrice = document.getElementById('modalTotalPrice');
    const qtyInput = document.getElementById('qtyInput');
    const qtyMinus = document.getElementById('qtyMinus');
    const qtyPlus = document.getElementById('qtyPlus');
    const addonCheckboxes = document.querySelectorAll('.addon-checkbox');
    const addonsSection = document.getElementById('addonsSection') || document.querySelector('.addons-section');
    const addToOrderBtn = document.getElementById('addToOrderBtn');
    const customerNameInput = document.getElementById('customerNameInput');
    
    let currentBasePrice = 0;
    let currentProductImage = '';
    let currentProductCategory = '';
    
    function isCurrentProductFood() {
        const cat = (currentProductCategory || '').toLowerCase().trim();
        return cat === 'foods' || cat === 'food' || cat.includes('food');
    }

    function updateTotalPrice() {
        let addonsTotal = 0;
        const isFood = isCurrentProductFood();

        addonCheckboxes.forEach(cb => {
            const label = cb.closest('.addon-label');
            if (!isFood && cb.checked) {
                addonsTotal += parseFloat(cb.getAttribute('data-price') || 0);
                if (label) label.classList.add('checked');
            } else {
                if (isFood) cb.checked = false;
                if (label) label.classList.remove('checked');
            }
        });
        
        let qty = parseInt(qtyInput.value) || 1;
        let finalPrice = (currentBasePrice + (isFood ? 0 : addonsTotal)) * qty;
        modalTotalPrice.innerText = `₱ ${finalPrice.toFixed(2).replace(/\.00$/, '')}`;
    }

    // Open Modal when clicking a product card
    productCards.forEach(card => {
        card.addEventListener('click', () => {
            const name = card.querySelector('.product-name').innerText;
            const priceText = card.querySelector('.product-price').innerText;
            const imgEl = card.querySelector('.product-image');
            currentBasePrice = parseFloat(card.getAttribute('data-price'));
            currentProductImage = imgEl ? imgEl.getAttribute('src') : '';
            currentProductCategory = (card.getAttribute('data-category') || '').toLowerCase().trim();
            
            modalProductName.innerText = name;
            qtyInput.value = 1;
            customerNameInput.value = '';
            
            // Reset addons
            addonCheckboxes.forEach(cb => {
                cb.checked = false;
                const label = cb.closest('.addon-label');
                if (label) label.classList.remove('checked');
            });

            // Remove/hide add-ons for food items
            const isFood = isCurrentProductFood();
            if (addonsSection) {
                addonsSection.style.display = isFood ? 'none' : '';
            }
            
            updateTotalPrice();
            modal.style.display = 'flex';
        });
    });

    addonCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateTotalPrice);
    });

    // Prevent .add-btn from double triggering
    const addBtns = document.querySelectorAll('.add-btn');
    addBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Let it bubble to the card to open modal
        });
    });

    // Close Modal
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }
    
    // Close when clicking outside modal content
    if (modal) {
        modal.addEventListener('click', (e) => {
            if(e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    // Quantity controls
    if (qtyMinus && qtyPlus) {
        qtyMinus.addEventListener('click', () => {
            let qty = parseInt(qtyInput.value);
            if (qty > 1) {
                qtyInput.value = qty - 1;
                updateTotalPrice();
            }
        });

        qtyPlus.addEventListener('click', () => {
            let qty = parseInt(qtyInput.value);
            qtyInput.value = qty + 1;
            updateTotalPrice();
        });
    }

    // Addon changes update price dynamically
    addonCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateTotalPrice);
    });

    // =============================================
    // Add to Cart (instead of redirecting)
    // =============================================
    if (addToOrderBtn) {
        addToOrderBtn.addEventListener('click', () => {
            let addonsTotal = 0;
            let selectedAddons = [];
            const isFood = isCurrentProductFood();

            if (!isFood) {
                addonCheckboxes.forEach(cb => {
                    if (cb.checked) {
                        addonsTotal += parseFloat(cb.getAttribute('data-price') || 0);
                        selectedAddons.push(cb.value);
                    }
                });
            }

            let qty = parseInt(qtyInput.value);
            let itemTotal = (currentBasePrice + addonsTotal) * qty;

            const cartItem = {
                product_name: modalProductName.innerText,
                customer_name: customerNameInput.value.trim() || '',
                price: currentBasePrice,
                quantity: qty,
                addons: isFood ? [] : selectedAddons,
                addons_total: isFood ? 0 : addonsTotal,
                item_total: itemTotal,
                image: currentProductImage
            };

            const cart = getCart();
            cart.push(cartItem);
            saveCart(cart);

            // Close modal and show confirmation
            modal.style.display = 'none';
            showToast(`${cartItem.product_name} added to cart!`, '#28a745');
        });
    }

    // =============================================
    // Toast Notification
    // =============================================
    function showToast(message, bgColor) {
        // Remove existing toast
        const existing = document.getElementById('posToast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'posToast';
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background-color: ${bgColor || '#482f25'};
            color: #fff;
            padding: 14px 28px;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.95rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        document.body.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
        });

        // Auto remove
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }

    // =============================================
    // URL Filter Parameter Handler
    // =============================================
    const urlParams = new URLSearchParams(window.location.search);
    const initialFilter = urlParams.get('filter');
    if (initialFilter) {
        const targetItem = Array.from(menuItems).find(item => item.getAttribute('data-filter') === initialFilter);
        if (targetItem) {
            targetItem.click();
        }
    }

});

