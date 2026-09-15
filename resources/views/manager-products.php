<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthbred - Product Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Montserrat:wght@400;600;700;800;900&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/manager.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/manager-products.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/pos-modal.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= asset('css/ios26-theme.css') ?>?v=<?= time() ?>">
    <link rel="icon" type="image/png" href="<?= asset('favicon.png') ?>?v=3.0">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>?v=3.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <style>
        .add-product-btn {
            border-radius: 8px !important;
            padding: 9px 18px !important;
            font-family: 'Poppins', sans-serif !important;
            font-weight: 600 !important;
            font-size: 0.88rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            border: 1px solid rgba(0,0,0,0.18) !important;
            box-shadow: 0 4px 12px rgba(45, 26, 17, 0.22) !important;
            transition: all 0.2s ease !important;
        }
        .add-product-btn:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 18px rgba(45, 26, 17, 0.35) !important;
        }
        .mgr-search-box {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            background: #ffffff !important;
            border: 1.5px solid #eadeca !important;
            border-radius: 8px !important;
            padding: 0 14px !important;
            height: 40px !important;
            flex: 1 !important;
            max-width: 320px !important;
            min-width: 200px !important;
            box-sizing: border-box !important;
            transition: all 0.2s ease !important;
        }
        .mgr-search-box:focus-within {
            border-color: #6a3a30 !important;
            box-shadow: 0 0 0 3px rgba(106, 58, 48, 0.12) !important;
        }
        .mgr-search-box i {
            color: #8d786c !important;
            font-size: 0.88rem !important;
            flex-shrink: 0 !important;
            position: static !important;
            transform: none !important;
        }
        .mgr-search-box input.mgr-search-input {
            border: none !important;
            background: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
            font-family: 'Poppins', sans-serif !important;
            font-size: 0.88rem !important;
            color: #2c1a14 !important;
            outline: none !important;
            width: 100% !important;
            height: 100% !important;
            box-shadow: none !important;
        }
        .mgr-search-box input.mgr-search-input::placeholder {
            color: #9c8a7e !important;
            opacity: 1 !important;
        }
    </style>
</head>
<body>
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
                    <p class="mgr-user-name">Juan Reyes</p>
                    <p class="mgr-user-id" id="sidebarUserRole">Manager</p>
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
                    <li class="mgr-nav-item active">
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
                    <li class="mgr-nav-item owner-only-link" style="display: none !important;" onclick="window.location.href='<?= url('') ?>/manager/accounts'">
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
        <script>
            (function() {
                function checkAuth() {
                    const role = (localStorage.getItem('userRole') || '').toLowerCase();
                    const uid = localStorage.getItem('userId');
                    if (!role || !uid) {
                        window.location.replace('<?= url('') ?>/login');
                        return false;
                    }
                    if (role === 'cashier') {
                        window.location.replace('<?= url('') ?>/pos');
                        return false;
                    }
                    return true;
                }
                if (!checkAuth()) return;
                window.addEventListener('pageshow', function (event) {
                    if (!checkAuth() || event.persisted) {
                        window.location.reload();
                    }
                });
                const role = (localStorage.getItem('userRole') || '').toLowerCase();
                const roleLabel = role.charAt(0).toUpperCase() + role.slice(1) || 'Manager';
                if (role === 'owner') {
                    document.body.classList.add('is-owner');
                    document.querySelectorAll('.owner-only-link').forEach(el => el.style.setProperty('display', 'flex', 'important'));
                    document.querySelectorAll('.owner-only-btn').forEach(el => el.style.setProperty('display', 'inline-flex', 'important'));
                } else {
                    document.body.classList.remove('is-owner');
                    document.querySelectorAll('.owner-only-link').forEach(el => el.style.setProperty('display', 'none', 'important'));
                    document.querySelectorAll('.owner-only-btn').forEach(el => el.style.setProperty('display', 'none', 'important'));
                }
                if (localStorage.getItem('userName')) {
                    const userNameEl = document.querySelector('.mgr-user-name');
                    if (userNameEl) userNameEl.textContent = localStorage.getItem('userName');
                }
                const roleEl = document.getElementById('sidebarUserRole');
                if (roleEl) roleEl.textContent = roleLabel;
            })();
        </script>

        <!-- Main Content Area -->
        <main class="mgr-main">
            <header class="mgr-header" style="justify-content: space-between; display: flex; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Toggle Navigation Menu" type="button" onclick="if(typeof window.toggleGlobalSidebar==='function')window.toggleGlobalSidebar();" style="touch-action:manipulation;cursor:pointer;">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h2 class="mgr-page-title">Product Management</h2>
                </div>
            </header>

            <div style="padding: 1.25rem 1.25rem 0; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap;">
                <!-- Search and Category Filter -->
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; flex: 1; min-width: 280px;">
                    <div class="mgr-search-box">
                        <input type="text" id="managerProductSearch" class="mgr-search-input" placeholder="Search menu items...">
                    </div>
                    <div style="display: flex; gap: 4px; flex-wrap: wrap;" id="managerCategoryFilterWrap">
                        <button type="button" class="mgr-filter-pill active" data-cat="all">All</button>
                        <button type="button" class="mgr-filter-pill" data-cat="coffee">Coffee</button>
                        <button type="button" class="mgr-filter-pill" data-cat="non-coffee">Non-Coffee</button>
                        <button type="button" class="mgr-filter-pill" data-cat="lemonade">Lemonade</button>
                        <button type="button" class="mgr-filter-pill" data-cat="foods">Foods</button>
                    </div>
                </div>

                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button class="add-product-btn" onclick="openAddonsModal()" style="background-color:#4a3728;"><i class="fa-solid fa-cookie-bite"></i> Manage Add-ons</button>
                    <button class="add-product-btn" onclick="openDiscountsModal()" style="background-color:#482f25;"><i class="fa-solid fa-percent"></i> Manage Discounts</button>
                    <button class="add-product-btn" onclick="openAddModal()" style="background-color:#3d271d;"><i class="fa-solid fa-plus"></i> Add Item</button>
                </div>
            </div>

            <div class="mgr-content">
                <div class="products-grid" id="managerProductsGrid">
                    <?php foreach($products as $product): ?>
                    <div class="product-card" data-category="<?= htmlspecialchars(strtolower(trim($product->category))) ?>" data-name="<?= htmlspecialchars(strtolower(trim($product->name))) ?>">
                        <div class="product-image-wrap">
                            <img src="<?= asset('images/' . $product->picture) ?>" alt="<?= htmlspecialchars($product->name) ?>">
                        </div>
                        <div class="product-info">
                            <h4 class="product-name"><?= htmlspecialchars($product->name) ?></h4>
                            <p class="product-category"><?= htmlspecialchars($product->category) ?></p>
                            <p class="product-price">
                                ₱ <?= $product->price ?>
                                <?php if($product->discounted_price): ?>
                                    <span class="discounted"> (Discount: ₱ <?= $product->discounted_price ?>)</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="product-actions">
                            <button class="edit-btn" onclick="openEditModal(<?= htmlspecialchars(json_encode($product)) ?>)">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                            <button class="delete-btn" onclick="deleteProduct(<?= (int)$product->id ?>, '<?= htmlspecialchars(addslashes($product->name), ENT_QUOTES) ?>')">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Manager Product Pagination -->
                <div class="mgr-pagination" id="managerProductsPagination" style="margin-top: 1.5rem;"></div>
            </div>
        </main>
    </div>

    <!-- Product Modal (Add/Edit) -->
    <div class="modal-overlay" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add Product</h3>
                <button class="close-modal-btn" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="productForm" enctype="multipart/form-data">
                    <input type="hidden" id="productId" name="id">
                    
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" id="productName" name="name" required>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select id="productCategory" name="category" required>
                            <option value="coffee">Coffee</option>
                            <option value="non-coffee">Non-Coffee</option>
                            <option value="lemonade">Lemonade</option>
                            <option value="foods">Foods</option>
                        </select>
                    </div>

                    <div class="form-group row">
                        <div class="col">
                            <label>Price (₱)</label>
                            <input type="number" step="0.01" id="productPrice" name="price" required>
                        </div>
                        <div class="col">
                            <label>Discounted Price (₱) [Optional]</label>
                            <input type="number" step="0.01" id="productDiscountedPrice" name="discounted_price">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Picture</label>
                        <input type="file" id="productPicture" name="picture" accept="image/*">
                        <small>Leave blank if you don't want to change the picture during edit.</small>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="save-btn" id="saveProductBtn">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add-ons Modal -->
    <div class="modal-overlay" id="addonsModal">
        <div class="modal-content" style="max-width: 550px;">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fa-solid fa-cookie-bite"></i> Manage Add-ons</h3>
                <button class="close-modal-btn" onclick="closeAddonsModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="addonForm" style="display:flex; gap:10px; margin-bottom: 20px; align-items: center; flex-wrap: wrap;">
                    <input type="hidden" id="addonId" value="">
                    <input type="text" id="addonName" placeholder="Add-on Name (e.g. Extra Rice)" required style="flex:2; min-width: 140px; padding:10px; border:1px solid #ccc; border-radius:6px; font-family:'Poppins',sans-serif; font-size:0.9rem;">
                    <select id="addonCategory" required style="flex:1.2; min-width: 120px; padding:10px; border:1px solid #ccc; border-radius:6px; font-family:'Poppins',sans-serif; font-size:0.9rem; background:#fff;">
                        <option value="food">🍲 Food</option>
                        <option value="drinks" selected>☕ Drinks / Coffee</option>
                        <option value="all">🌐 All Categories</option>
                    </select>
                    <div style="position: relative; flex:1; min-width: 90px;">
                        <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #888; font-weight: 600; font-size:0.9rem;">₱</span>
                        <input type="number" step="0.01" min="0" id="addonPrice" placeholder="0.00" required style="width: 100%; padding:10px 10px 10px 24px; border:1px solid #ccc; border-radius:6px; font-family:'Poppins',sans-serif; font-size:0.9rem; box-sizing: border-box;">
                    </div>
                    <button type="submit" id="saveAddonBtn" class="save-btn" style="padding: 10px 16px; margin:0; width:auto; white-space: nowrap; font-size:0.85rem;"><i class="fa-solid fa-plus"></i> <span id="addonSubmitText">Add</span></button>
                    <button type="button" id="cancelAddonEditBtn" onclick="resetAddonForm()" style="display:none; padding: 10px 14px; margin:0; width:auto; background:#eee; color:#333; border:none; border-radius:6px; cursor:pointer; font-family:'Poppins',sans-serif; font-size:0.85rem;">Cancel</button>
                </form>
                <div style="font-size: 0.85rem; font-weight: 600; color: #8d786c; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Active Add-ons</div>
                <div id="addonsList" style="max-height: 300px; overflow-y: auto; border: 1px solid #f0f0f0; border-radius: 8px; padding: 5px;">
                    <!-- populated by js -->
                </div>
            </div>
        </div>
    </div>

    <!-- Discounts Modal -->
    <div class="modal-overlay" id="discountsModal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fa-solid fa-percent"></i> Manage Global Discounts</h3>
                <button class="close-modal-btn" onclick="closeDiscountsModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form id="addDiscountForm" style="display:flex; gap:10px; margin-bottom: 20px;">
                    <input type="text" id="discountName" placeholder="Name (e.g. 10% Off)" required style="flex:1; padding:10px; border:1px solid #ccc; border-radius:6px; font-family:'Poppins',sans-serif;">
                    <input type="number" id="discountPercent" placeholder="%" min="1" max="100" required style="width:80px; padding:10px; border:1px solid #ccc; border-radius:6px; font-family:'Poppins',sans-serif;">
                    <button type="submit" class="save-btn" style="padding: 10px 16px; margin:0; width:auto;">Add</button>
                </form>
                <div id="discountsList" style="max-height: 300px; overflow-y: auto; border: 1px solid #f0f0f0; border-radius: 8px; padding: 5px;">
                    <!-- populated by js -->
                </div>
            </div>
    <script src="<?= asset('js/pos-modal.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= asset('js/clock-out.js') ?>?v=<?= time() ?>"></script>
    <script src="<?= asset('js/manager-products.js') ?>?v=<?= time() ?>"></script>
</body>
</html>
