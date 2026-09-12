document.addEventListener('DOMContentLoaded', () => {
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
            if (window.innerWidth < 900) {
                closeSidebar();
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

    // Clock out handled by clock-out.js

    // Modal Logic
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
    let currentProductCategory = '';

    function isCurrentProductFood() {
        const cat = (currentProductCategory || '').toLowerCase().trim();
        return cat === 'foods' || cat === 'food' || cat.includes('food');
    }
    
    function updateTotalPrice() {
        let addonsTotal = 0;
        const isFood = isCurrentProductFood();

        addonCheckboxes.forEach(cb => {
            if (!isFood && cb.checked) {
                addonsTotal += parseInt(cb.getAttribute('data-price'));
            } else if (isFood) {
                cb.checked = false;
            }
        });
        
        let qty = parseInt(qtyInput.value);
        let finalPrice = (currentBasePrice + (isFood ? 0 : addonsTotal)) * qty;
        modalTotalPrice.innerText = `₱ ${finalPrice}`;
    }

    // Open Modal when clicking a product card
    productCards.forEach(card => {
        card.addEventListener('click', () => {
            const name = card.querySelector('.product-name').innerText;
            const priceText = card.querySelector('.product-price').innerText;
            currentProductCategory = (card.getAttribute('data-category') || '').toLowerCase().trim();
            // Extract the number from "₱ 95"
            currentBasePrice = parseInt(priceText.replace(/[^0-9]/g, ''));
            
            modalProductName.innerText = name;
            qtyInput.value = 1;
            customerNameInput.value = ''; // Reset customer name
            
            // Reset addons
            addonCheckboxes.forEach(cb => cb.checked = false);

            // Remove/hide add-ons for food items
            const isFood = isCurrentProductFood();
            if (addonsSection) {
                addonsSection.style.display = isFood ? 'none' : '';
            }
            
            updateTotalPrice();
            modal.style.display = 'flex';
        });
    });

    // Prevent .add-btn from double triggering or doing something else if it's inside the card
    const addBtns = document.querySelectorAll('.add-btn');
    addBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            // Let it bubble to the card to open modal, or we can just stop it
            // Actually, we don't need to do anything, clicking it will trigger the card click.
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

    // Add to order action
    if (addToOrderBtn) {
        addToOrderBtn.addEventListener('click', () => {
            // Redirect to checkout page
            window.location.href = '/checkout';
        });
    }

    // Top Right Order Button Redirect
    const mainOrderBtn = document.querySelector('.order-btn');
    if (mainOrderBtn) {
        mainOrderBtn.addEventListener('click', () => {
            window.location.href = '/checkout';
        });
    }
});
