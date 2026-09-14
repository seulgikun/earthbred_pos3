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
    // Unified Product Filtering & Pagination (Sidebar & Tablet Bar)
    // =============================================
    const menuItems = document.querySelectorAll('.menu-item');
    const categoryChips = document.querySelectorAll('.category-chip');
    const productCards = document.querySelectorAll('.product-card');

    const POS_PAGE_SIZE = 10;
    let currentPosPage = 1;
    let activeCategoryFilter = 'all';

    function renderPosPagination(totalItems) {
        const paginationEl = document.getElementById('posPagination');
        if (!paginationEl) return;

        const totalPages = Math.ceil(totalItems / POS_PAGE_SIZE) || 1;
        if (currentPosPage > totalPages) currentPosPage = totalPages;
        if (currentPosPage < 1) currentPosPage = 1;

        if (totalPages <= 1) {
            paginationEl.innerHTML = '';
            paginationEl.style.display = 'none';
            return;
        }

        paginationEl.style.display = 'flex';
        let html = '';

        // Prev Button
        html += `<button class="pagination-btn" ${currentPosPage === 1 ? 'disabled' : ''} onclick="goToPosPage(${currentPosPage - 1})"><i class="fa-solid fa-chevron-left"></i> Prev</button>`;

        // Page numbers
        for (let p = 1; p <= totalPages; p++) {
            html += `<button class="pagination-btn ${p === currentPosPage ? 'active' : ''}" onclick="goToPosPage(${p})">${p}</button>`;
        }

        // Next Button
        html += `<button class="pagination-btn" ${currentPosPage === totalPages ? 'disabled' : ''} onclick="goToPosPage(${currentPosPage + 1})">Next <i class="fa-solid fa-chevron-right"></i></button>`;

        const startIdx = (currentPosPage - 1) * POS_PAGE_SIZE + 1;
        const endIdx = Math.min(currentPosPage * POS_PAGE_SIZE, totalItems);
        html += `<span class="pagination-info">${startIdx}–${endIdx} of ${totalItems}</span>`;

        paginationEl.innerHTML = html;
    }

    window.goToPosPage = function(page) {
        currentPosPage = page;
        applyCategoryFilter(activeCategoryFilter, false);
        const main = document.querySelector('.main-content');
        if (main) main.scrollTo({ top: 0, behavior: 'smooth' });
    };

    function applyCategoryFilter(filterValue, resetPage = true) {
        if (!filterValue) filterValue = 'all';
        activeCategoryFilter = filterValue;
        if (resetPage) currentPosPage = 1;

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

        // Filter cards matching category
        const matchingCards = [];
        productCards.forEach(card => {
            const cat = (card.getAttribute('data-category') || '').toLowerCase().trim();
            if (filterValue === 'all' || cat === filterValue) {
                matchingCards.push(card);
            } else {
                card.style.display = 'none';
            }
        });

        // Paginate matching cards
        const start = (currentPosPage - 1) * POS_PAGE_SIZE;
        const end = start + POS_PAGE_SIZE;

        matchingCards.forEach((card, index) => {
            if (index >= start && index < end) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });

        renderPosPagination(matchingCards.length);
    }

    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            // Skip navigation links
            if (item.id === 'inventory-menu-item') return;

            const filterValue = item.getAttribute('data-filter');
            if (filterValue) {
                applyCategoryFilter(filterValue, true);
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
                applyCategoryFilter(filterValue, true);
            }
        });
    });

    // Check URL query param e.g. /pos?filter=coffee on load
    (function initFilter() {
        const urlParams = new URLSearchParams(window.location.search);
        const filterParam = urlParams.get('filter') || 'all';
        applyCategoryFilter(filterParam, true);
    })();

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
            const item = cb.closest('.addon-item');
            const itemCat = (cb.getAttribute('data-category') || 'drinks').toLowerCase();

            const isMatch = isFood 
                ? (itemCat === 'food' || itemCat === 'all')
                : (itemCat === 'drinks' || itemCat === 'drink' || itemCat === 'all');

            if (isMatch && cb.checked) {
                addonsTotal += parseFloat(cb.getAttribute('data-price') || 0);
                if (label) label.classList.add('checked');
            } else {
                if (!isMatch) cb.checked = false;
                if (label) label.classList.remove('checked');
            }
        });
        
        let qty = parseInt(qtyInput.value) || 1;
        let finalPrice = (currentBasePrice + addonsTotal) * qty;
        modalTotalPrice.innerText = `₱ ${finalPrice.toFixed(2).replace(/\.00$/, '')}`;
    }

    // Open Modal when clicking a product card
    productCards.forEach(card => {
        card.addEventListener('click', () => {
            // Guard against clicking out-of-stock products
            if (card.classList.contains('is-out-of-stock') || card.getAttribute('data-out-of-stock') === 'true') {
                return;
            }

            const name = card.querySelector('.product-name').innerText;
            const priceText = card.querySelector('.product-price').innerText;
            const imgEl = card.querySelector('.product-image');
            currentBasePrice = parseFloat(card.getAttribute('data-price'));
            currentProductImage = imgEl ? imgEl.getAttribute('src') : '';
            currentProductCategory = (card.getAttribute('data-category') || '').toLowerCase().trim();
            
            modalProductName.innerText = name;
            qtyInput.value = 1;
            customerNameInput.value = '';
            
            const isFood = isCurrentProductFood();
            let visibleAddonsCount = 0;

            // Filter addons based on product category (Food vs Drinks)
            const addonItems = document.querySelectorAll('.addon-item');
            addonItems.forEach(item => {
                const cb = item.querySelector('.addon-checkbox');
                const itemCat = (item.getAttribute('data-category') || (cb ? cb.getAttribute('data-category') : '') || 'drinks').toLowerCase();
                const isMatch = isFood 
                    ? (itemCat === 'food' || itemCat === 'all')
                    : (itemCat === 'drinks' || itemCat === 'drink' || itemCat === 'all');

                if (isMatch) {
                    item.style.display = '';
                    visibleAddonsCount++;
                } else {
                    item.style.display = 'none';
                }

                if (cb) {
                    cb.checked = false;
                    const label = cb.closest('.addon-label');
                    if (label) label.classList.remove('checked');
                }
            });

            if (addonsSection) {
                addonsSection.style.display = visibleAddonsCount > 0 ? '' : 'none';
            }
            
            updateTotalPrice();
            modal.style.display = 'flex';
            setTimeout(() => {
                if (qtyInput) {
                    qtyInput.focus();
                    qtyInput.select();
                }
            }, 60);
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

    // Quantity input direct typing events
    if (qtyInput) {
        qtyInput.addEventListener('input', () => {
            updateTotalPrice();
        });

        qtyInput.addEventListener('change', () => {
            let val = parseInt(qtyInput.value);
            if (isNaN(val) || val < 1) {
                qtyInput.value = 1;
            }
            updateTotalPrice();
        });

        qtyInput.addEventListener('focus', () => {
            qtyInput.select();
        });
    }

    // =============================================
    // Add to Cart (instead of redirecting)
    // =============================================
    if (addToOrderBtn) {
        addToOrderBtn.addEventListener('click', () => {
            let addonsTotal = 0;
            let selectedAddons = [];
            const isFood = isCurrentProductFood();

            addonCheckboxes.forEach(cb => {
                const itemCat = (cb.getAttribute('data-category') || 'drinks').toLowerCase();
                const isMatch = isFood 
                    ? (itemCat === 'food' || itemCat === 'all')
                    : (itemCat === 'drinks' || itemCat === 'drink' || itemCat === 'all');

                if (isMatch && cb.checked) {
                    addonsTotal += parseFloat(cb.getAttribute('data-price') || 0);
                    selectedAddons.push(cb.value);
                }
            });

            let qty = parseInt(qtyInput.value);
            if (isNaN(qty) || qty < 1) qty = 1;
            let itemTotal = (currentBasePrice + addonsTotal) * qty;

            const cartItem = {
                product_name: modalProductName.innerText,
                customer_name: customerNameInput.value.trim() || '',
                price: currentBasePrice,
                quantity: qty,
                addons: selectedAddons,
                addons_total: addonsTotal,
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

    // =============================================
    // Real-Time Stock Status Polling
    // =============================================
    function applyStockStatus(outOfStockIds) {
        const ids = new Set(outOfStockIds.map(String));
        productCards.forEach(card => {
            const cardId = String(card.getAttribute('data-id') || '');
            const isOut = ids.has(cardId);
            const wasOut = card.getAttribute('data-out-of-stock') === 'true';

            if (isOut === wasOut) return; // No change needed

            card.setAttribute('data-out-of-stock', isOut ? 'true' : 'false');

            if (isOut) {
                card.classList.add('is-out-of-stock');
                // Insert out-of-stock badge if missing
                if (!card.querySelector('.out-of-stock-badge')) {
                    const badge = document.createElement('div');
                    badge.className = 'out-of-stock-badge';
                    badge.innerHTML = '<i class="fa-solid fa-ban"></i> OUT OF STOCK';
                    card.insertBefore(badge, card.firstChild);
                }
                // Remove the + add button
                const addBtn = card.querySelector('.add-btn');
                if (addBtn) addBtn.remove();
            } else {
                card.classList.remove('is-out-of-stock');
                // Remove out-of-stock badge
                const badge = card.querySelector('.out-of-stock-badge');
                if (badge) badge.remove();
                // Restore the + add button if missing
                if (!card.querySelector('.add-btn')) {
                    const addBtn = document.createElement('button');
                    addBtn.className = 'add-btn';
                    addBtn.innerHTML = '<i class="fa-solid fa-plus"></i>';
                    card.insertBefore(addBtn, card.firstChild);
                }
            }
        });
    }

    // Inject a stock alert banner below the header
    function showStockAlertBanner(data) {
        const existing = document.getElementById('posStockAlertBar');
        if (existing) existing.remove();

        const alerts = [];
        if (data.out_of_stock_alerts && data.out_of_stock_alerts.length > 0) {
            const names = data.out_of_stock_alerts.map(a => a.item_name).join(', ');
            alerts.push(`<span style="color:#c5221f;"><i class="fa-solid fa-circle-exclamation"></i> <strong>OUT OF STOCK:</strong> ${names}</span>`);
        }
        if (data.low_stock_alerts && data.low_stock_alerts.length > 0) {
            const names = data.low_stock_alerts.map(a => `${a.item_name} (${a.quantity})`).join(', ');
            alerts.push(`<span style="color:#b06000;"><i class="fa-solid fa-triangle-exclamation"></i> <strong>LOW STOCK:</strong> ${names}</span>`);
        }

        if (alerts.length === 0) return;

        const bar = document.createElement('div');
        bar.id = 'posStockAlertBar';
        bar.style.cssText = `
            position: sticky; top: 0; z-index: 100;
            background: rgba(255,248,230,0.96);
            border-bottom: 1px solid rgba(229,160,0,0.35);
            padding: 8px 20px;
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            font-family: 'Outfit', sans-serif; font-size: 0.82rem; font-weight: 600;
            backdrop-filter: blur(8px);
        `;
        bar.innerHTML = `
            <div style="display:flex;flex-direction:column;gap:2px;">${alerts.join('')}</div>
            <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#888;font-size:1rem;padding:2px 6px;">
                <i class="fa-solid fa-xmark"></i>
            </button>`;

        const mainContent = document.querySelector('.main-content');
        if (mainContent) mainContent.insertBefore(bar, mainContent.firstChild);
    }

    function pollStockStatus() {
        fetch(BASE + '/api/pos/stock-status', {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!data) return;
            if (Array.isArray(data.out_of_stock)) {
                applyStockStatus(data.out_of_stock);
            }
            showStockAlertBanner(data);
        })
        .catch(() => {}); // Silently fail — page will catch up on next reload
    }

    // Poll immediately on page load, then every 60 seconds
    pollStockStatus();
    setInterval(pollStockStatus, 60000);

});


