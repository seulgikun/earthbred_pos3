<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthbred - POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/pos.css') ?>?v=1.1.0">
    <link rel="stylesheet" href="<?= asset('css/pos-modal.css') ?>?v=1.1.0">
    <link rel="stylesheet" href="<?= asset('css/ios26-theme.css') ?>?v=1.1.0">
    <link rel="icon" type="image/png" href="<?= asset('favicon.png') ?>?v=3.0">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>?v=3.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">

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
                    <p class="user-name" id="sidebarUserName">Cashier</p>
                    <p class="user-id">Staff 001</p>
                </div>
            </div>
            <script>
                if (localStorage.getItem('userName')) {
                    document.getElementById('sidebarUserName').textContent = localStorage.getItem('userName');
                }
            </script>

            <nav class="menu-section">
                <h3 class="menu-heading">MENU</h3>
                <ul class="menu-list">
                    <li class="menu-item active" data-filter="all">
                        <span class="menu-icon">🍽️</span> All Items
                    </li>
                    <li class="menu-item" data-filter="coffee">
                        <span class="menu-icon">☕</span> Coffee
                    </li>
                    <li class="menu-item" data-filter="non-coffee">
                        <span class="menu-icon">🍵</span> Non-Coffee
                    </li>
                    <li class="menu-item" data-filter="lemonade">
                        <span class="menu-icon">🍹</span> Lemonade
                    </li>
                    <li class="menu-item" data-filter="foods">
                        <span class="menu-icon">🍲</span> Foods
                    </li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/shift-notes'">
                        <span class="menu-icon">📝</span> Shift Notes
                    </li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/queue'" style="border-top: 1px solid #e5d9c5; margin-top: 0.5rem; padding-top: 1rem;">
                        <span class="menu-icon">📋</span> Order Queuing
                    </li>
                    <li class="menu-item" id="inventory-menu-item" onclick="window.location.href='<?= url('') ?>/inventory'" style="border-top: 1px solid #e5d9c5; margin-top: 0.5rem; padding-top: 1rem;">
                        <span class="menu-icon">📦</span> Inventory
                    </li>

                </ul>
            </nav>

            <div class="clock-out">
                <i class="fa-solid fa-power-off"></i> Clock Out
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Header -->
            <header class="top-header">
                <button class="sidebar-toggle-btn" id="sidebarToggleBtn"
                    title="Toggle Navigation Menu"
                    onclick="if(typeof window.toggleGlobalSidebar==='function')window.toggleGlobalSidebar();"
                    style="touch-action:manipulation;cursor:pointer;"
                    type="button">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="header-spacer"></div>
                <button class="order-btn">
                    <i class="fa-solid fa-cart-shopping"></i> Order
                </button>
            </header>

            <!-- Tablet / Mobile Horizontal Category Selector Bar -->
            <nav class="tablet-category-bar" id="tabletCategoryBar">
                <button type="button" class="category-chip active" data-filter="all"><span>🍽️</span> All Items</button>
                <button type="button" class="category-chip" data-filter="coffee"><span>☕</span> Coffee</button>
                <button type="button" class="category-chip" data-filter="non-coffee"><span>🍵</span> Non-Coffee</button>
                <button type="button" class="category-chip" data-filter="lemonade"><span>🍹</span> Lemonade</button>
                <button type="button" class="category-chip" data-filter="foods"><span>🍲</span> Foods</button>
            </nav>

            <!-- Product Grid -->
            <div class="product-grid">
                
                <?php foreach($products as $product): 
                    $outOfStock = $product->isOutOfStock($inventories ?? null);
                ?>
                <div class="product-card <?= $outOfStock ? 'is-out-of-stock' : '' ?>" data-category="<?= htmlspecialchars((string)($product->category ?? '')) ?>" data-id="<?= $product->id ?>" data-price="<?= $product->discounted_price ? $product->discounted_price : $product->price ?>" data-out-of-stock="<?= $outOfStock ? 'true' : 'false' ?>">
                    <?php if($outOfStock): ?>
                        <div class="out-of-stock-badge"><i class="fa-solid fa-ban"></i> OUT OF STOCK</div>
                    <?php else: ?>
                        <button class="add-btn"><i class="fa-solid fa-plus"></i></button>
                    <?php endif; ?>
                    <img src="<?= asset('images/' . ($product->picture ?? 'placeholder.png')) ?>" alt="<?= htmlspecialchars((string)($product->name ?? '')) ?>" class="product-image">
                    <h4 class="product-name"><?= htmlspecialchars((string)($product->name ?? '')) ?></h4>
                    <?php if($product->discounted_price): ?>
                        <p class="product-price">
                            <span style="text-decoration: line-through; font-size: 0.8em; color: #888;">₱ <?= number_format((float)$product->price, 0) ?></span>
                            ₱ <?= number_format((float)$product->discounted_price, 0) ?>
                        </p>
                    <?php else: ?>
                        <p class="product-price">₱ <?= number_format((float)$product->price, 0) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

            </div>

            <!-- POS Product Pagination -->
            <div class="pos-pagination" id="posPagination"></div>

        </main>
    </div>

    <!-- Product Modal -->
    <div class="modal-overlay" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalProductName">Product Name</h3>
                <button class="close-modal-btn" id="closeModalBtn"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="customer-section">
                    <h4>Customer Name (Optional)</h4>
                    <input type="text" id="customerNameInput" class="customer-input" placeholder="e.g., John Doe">
                </div>

                <div class="quantity-section">
                    <h4>Quantity</h4>
                    <div class="quantity-type-wrap">
                        <input type="number" class="customer-input qty-type-input" id="qtyInput" value="1" min="1" step="1" inputmode="numeric" placeholder="Enter quantity (e.g. 1, 2, 5)">
                    </div>
                </div>

                <div class="addons-section" id="addonsSection">
                    <h4 id="addonsSectionTitle">Add-ons</h4>
                    <?php if(!empty($addons) && count($addons) > 0): ?>
                        <?php foreach($addons as $addon): ?>
                            <div class="addon-item" data-category="<?= htmlspecialchars((string)($addon->category ?? 'drinks')) ?>">
                                <label class="addon-label">
                                    <input type="checkbox" class="addon-checkbox" data-category="<?= htmlspecialchars((string)($addon->category ?? 'drinks')) ?>" data-price="<?= (float)$addon->price ?>" value="<?= htmlspecialchars((string)$addon->name) ?>">
                                    <span class="custom-checkbox"></span>
                                    <?= htmlspecialchars((string)$addon->name) ?> <?= $addon->price > 0 ? '(+₱ ' . number_format((float)$addon->price, 0) . ')' : '(Free)' ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:#888; font-size: 0.85rem;" id="noAddonsMsg">No add-ons available</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button class="add-to-order-btn" id="addToOrderBtn">
                    Add to Order <span id="modalTotalPrice">₱ 0</span>
                </button>
            </div>
        </div>
    </div>


    
    <script src="<?= asset('js/pos-modal.js') ?>?v=1.1.0"></script>
    <script src="<?= asset('js/clock-out.js') ?>?v=1.1.0"></script>
    <script src="<?= asset('js/pos.js') ?>?v=1.3.0"></script>
</body>
</html>
