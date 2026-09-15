<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthbred - Shift Notes Console</title>
    <meta name="description"
        content="Earthbred Coffee Studio Manager Shift Notes Console. Monitor, filter, and resolve shift logs submitted by staff.">
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Montserrat:wght@400;600;700;800;900&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/manager.css') ?>?v=1.0.0">
    <link rel="stylesheet" href="<?= asset('css/pos-modal.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/ios26-theme.css') ?>?v=1.0.1">
    <link rel="icon" type="image/png" href="<?= asset('favicon.png') ?>?v=3.0">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>?v=3.0">
    <style>
        /* ===== Liquid Glass overrides for Manager Shift Notes ===== */
        .mgr-main { background: transparent !important; }
        .mgr-content { padding: 1.5rem !important; }
        .filter-buttons-container {
            display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.25rem;
        }
        .filter-btn {
            padding: 0.55rem 1.15rem;
            border-radius: 9999px;
            border: 1.5px solid rgba(210,195,180,0.5);
            background: rgba(255,255,255,0.72);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 600;
            font-size: 0.82rem;
            color: #594a40;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(0.34,1.56,0.64,1);
        }
        .filter-btn:hover { background: rgba(255,255,255,0.9) !important; transform: translateY(-2px); }
        .filter-btn.active {
            background: linear-gradient(135deg, #533524 0%, #3d271d 60%, #26160e 100%) !important;
            color: #fff !important;
            border-color: transparent !important;
            box-shadow: 0 6px 16px rgba(45,26,17,0.35);
        }
        .mgr-notes-list { display: flex; flex-direction: column; gap: 0; }
        /* Override all note card inline styles */
        .mgr-note-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.84) 0%, rgba(255,255,255,0.68) 100%) !important;
            backdrop-filter: blur(20px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
            border: 1px solid rgba(255,255,255,0.75) !important;
            border-radius: 20px !important;
            padding: 1.25rem !important;
            box-shadow: 0 8px 22px rgba(44,30,20,0.07), inset 0 1px 0 rgba(255,255,255,0.9) !important;
            margin-bottom: 0.9rem !important;
            transition: transform 0.22s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.22s ease !important;
        }
        .mgr-note-card:hover { transform: translateY(-3px) !important; box-shadow: 0 14px 30px rgba(44,30,20,0.11) !important; }
        .mgr-note-author { font-family: 'Outfit', sans-serif !important; color: #d97706 !important; font-weight: 700 !important; font-size: 0.9rem !important; }
        .mgr-note-body { color: #382d26 !important; font-family: 'Plus Jakarta Sans', sans-serif !important; }
        .mgr-note-time { color: #8c786c !important; }
        .mgr-note-footer { border-top: 1px solid rgba(220,200,180,0.3) !important; }
        .resolve-btn {
            background: rgba(46,125,50,0.12) !important;
            color: #2e7d32 !important;
            border: 1px solid rgba(46,125,50,0.25) !important;
            padding: 0.4rem 0.95rem !important;
            border-radius: 9999px !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-weight: 700 !important;
            font-size: 0.82rem !important;
            cursor: pointer !important;
            transition: all 0.22s ease !important;
        }
        .resolve-btn:hover { background: #2e7d32 !important; color: #fff !important; transform: scale(1.04) !important; }
        .status-resolved { color: #2e7d32 !important; font-family: 'Plus Jakarta Sans', sans-serif !important; font-weight: 700 !important; }
        .category-tag {
            border-radius: 9999px !important;
            font-family: 'Outfit', sans-serif !important;
            font-weight: 700 !important;
            font-size: 0.72rem !important;
        }
        .filter-empty-state { color: #6b5a4e; text-align: center; padding: 2rem; }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="mgr-app">

        <!-- =========================================
         SIDEBAR
    ========================================= -->
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
                    <li class="mgr-nav-item" onclick="window.location.href='<?= url('') ?>/manager/products'">
                        <i class="fa-solid fa-tags mgr-nav-icon"></i> Product Management
                    </li>
                    <li class="mgr-nav-item active">
                        <i class="fa-solid fa-note-sticky mgr-nav-icon"></i> Shift Notes
                    </li>
                    <li class="mgr-nav-item"
                        onclick="window.location.href='<?= url('') ?>/manager/sales-report'">
                        <i class="fa-solid fa-file-invoice-dollar mgr-nav-icon"></i> Sales Reports
                    </li>
                    <li class="mgr-nav-item"
                        onclick="window.location.href='<?= url('') ?>/manager/inventory'">
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
                } else {
                    document.body.classList.remove('is-owner');
                    document.querySelectorAll('.owner-only-link').forEach(el => el.style.setProperty('display', 'none', 'important'));
                }
                if (localStorage.getItem('userName')) {
                    const userNameEl = document.querySelector('.mgr-user-name');
                    if (userNameEl) userNameEl.textContent = localStorage.getItem('userName');
                }
                const roleEl = document.getElementById('sidebarUserRole');
                if (roleEl) roleEl.textContent = roleLabel;
            })();
        </script>

        <!-- =========================================
         MAIN CONTENT AREA
    ========================================= -->
        <main class="mgr-main">
            <!-- Top header -->
            <header class="mgr-header" style="justify-content: space-between; display: flex; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Toggle Navigation Menu" type="button" onclick="if(typeof window.toggleGlobalSidebar==='function')window.toggleGlobalSidebar();" style="touch-action:manipulation;cursor:pointer;">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h2 class="mgr-page-title">Shift Notes</h2>
                </div>
            </header>

            <!-- Inner Content Scroll Area -->
            <div class="mgr-content">
                <!-- Greeting -->
                <div class="mgr-greeting-wrap">
                    <h3 class="mgr-greeting-title">Shift Notes</h3>
                    <p class="mgr-greeting-sub">All notes submitted by staff across shifts.</p>
                </div>

                <!-- Filter Buttons -->
                <div class="filter-buttons-container">
                    <button class="filter-btn active" data-filter="all">All</button>
                    <button class="filter-btn" data-filter="general">General</button>
                    <button class="filter-btn" data-filter="equipment">Equipment</button>
                    <button class="filter-btn" data-filter="complaint">Complaint</button>
                    <button class="filter-btn" data-filter="task">Task</button>
                    <button class="filter-btn" data-filter="resolved">Resolved</button>
                </div>

                <!-- Notes List -->
                <div class="mgr-notes-list" id="notesListContainer">
                    <?php if (isset($notes) && $notes->count() > 0): ?>
                        <?php foreach ($notes as $note): ?>
                            <div class="mgr-note-card"
                                data-category="<?= strtolower(htmlspecialchars($note->category ?? 'general')) ?>"
                                data-status="<?= $note->is_done ? 'resolved' : 'unresolved' ?>"
                                style="background: #ffffff; border: 1.5px solid #eadeca; border-radius: 14px; padding: 1.5rem; display: <?= $note->is_done ? 'none' : 'flex' ?>; flex-direction: column; gap: 10px; box-shadow: 0 4px 12px rgba(44, 26, 20, 0.03); margin-bottom: 1rem;">

                                <div class="mgr-note-header"
                                    style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <!-- Category Tag -->
                                        <span
                                            class="category-tag tag-<?= strtolower(htmlspecialchars($note->category ?? 'general')) ?>"
                                            style="font-size: 0.72rem; font-weight: 700; padding: 4px 10px; border-radius: 8px; text-transform: uppercase;">
                                            <?= htmlspecialchars($note->category ?? 'General') ?>
                                        </span>
                                        <span class="mgr-note-author"
                                            style="font-weight: 600; color: #2c1a14; font-size: 0.9rem;">
                                            <?= htmlspecialchars($note->cashier_name ?? 'Staff') ?>
                                        </span>
                                    </div>
                                    <span class="mgr-note-time" style="font-size: 0.8rem; color: #8d786c; font-weight: 500;">
                                        <?= $note->created_at->format('M d, h:i A') ?>
                                    </span>
                                </div>

                                <div class="mgr-note-body"
                                    style="font-size: 0.92rem; line-height: 1.6; color: #5c4a40; margin-top: 5px; white-space: pre-wrap;">
                                    <?= htmlspecialchars($note->note) ?>
                                </div>

                                <div class="mgr-note-footer"
                                    style="display: flex; justify-content: flex-end; align-items: center; margin-top: 10px; border-top: 1px solid #f5edd6; padding-top: 10px; min-height: 35px;">
                                    <?php if ($note->is_done): ?>
                                        <span class="status-resolved"
                                            style="color: #2e7d32; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                                            <i class="fa-solid fa-circle-check"></i> Resolved
                                        </span>
                                    <?php else: ?>
                                        <form action="<?= url('') ?>/shift-notes/<?= $note->id ?>/done" method="POST"
                                            onsubmit="handleResolveNote(event, this)"
                                            style="margin: 0;">
                                            <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="_method" value="PATCH">
                                            <button type="submit" class="resolve-btn">
                                                <i class="fa-solid fa-check"></i> Resolve
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state-card"
                            style="display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 4rem 2rem; color: #8d786c; background: #ffffff; border: 1.5px solid #eadeca; border-radius: 14px;">
                            <i class="fa-solid fa-note-sticky"
                                style="font-size: 3rem; margin-bottom: 1rem; color: #eadeca;"></i>
                            <p style="font-weight: 700; font-size: 1.1rem; color: #2c1a14; margin-bottom: 5px;">No shift
                                notes yet</p>
                            <p style="font-size: 0.88rem;">Notes logged by staff from the POS will show up here.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Dynamic JS Empty State (hidden by default) -->
                <div id="filterEmptyState" class="empty-state-card"
                    style="display: none; flex-direction: column; justify-content: center; align-items: center; padding: 4rem 2rem; color: #8d786c; background: #ffffff; border: 1.5px solid #eadeca; border-radius: 14px; margin-top: 1rem;">
                    <i class="fa-solid fa-filter" style="font-size: 3rem; margin-bottom: 1rem; color: #eadeca;"></i>
                    <p style="font-weight: 700; font-size: 1.1rem; color: #2c1a14; margin-bottom: 5px;">No matching
                        notes found</p>
                    <p style="font-size: 0.88rem;">There are no shift notes for the selected filter category.</p>
                </div>

            </div>
        </main>

    </div>

    <script>
        function applyShiftNotesFilter(filterValue) {
            const noteCards = document.querySelectorAll('.mgr-note-card');
            const filterEmptyState = document.getElementById('filterEmptyState');
            let visibleCount = 0;

            noteCards.forEach(card => {
                const category = card.getAttribute('data-category');
                const status = card.getAttribute('data-status');

                let shouldShow = false;

                if (filterValue === 'all') {
                    // Active Categories view: Hide all resolved notes to keep workspace clean
                    shouldShow = (status !== 'resolved');
                } else if (filterValue === 'resolved') {
                    // Resolved Tab: Only show resolved notes
                    shouldShow = (status === 'resolved');
                } else {
                    // Specific Category: Must match category AND be unresolved
                    shouldShow = (category === filterValue && status !== 'resolved');
                }

                if (shouldShow) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (visibleCount === 0 && noteCards.length > 0) {
                filterEmptyState.style.display = 'flex';
            } else {
                filterEmptyState.style.display = 'none';
            }
        }

        async function handleResolveNote(e, form) {
            e.preventDefault();
            const btn = form.querySelector('.resolve-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Resolving...';
            }

            const card = form.closest('.mgr-note-card');
            const url = form.action;
            const formData = new FormData(form);

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                // Update card status to resolved
                card.setAttribute('data-status', 'resolved');

                // Update card footer to show the green Resolved badge
                const footer = card.querySelector('.mgr-note-footer');
                if (footer) {
                    footer.innerHTML = `
                        <span class="status-resolved"
                            style="color: #2e7d32; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-circle-check"></i> Resolved
                        </span>
                    `;
                }

                const activeTabBtn = document.querySelector('.filter-btn.active');
                const currentFilter = activeTabBtn ? activeTabBtn.getAttribute('data-filter') : 'all';

                // If currently on an active category view (all, general, equipment, complaint, task),
                // smoothly animate the card disappearing and move it directly into the Resolved tab!
                if (currentFilter !== 'resolved') {
                    card.style.transition = 'opacity 0.35s ease, transform 0.35s cubic-bezier(0.4, 0, 0.2, 1), max-height 0.4s ease, margin 0.35s ease, padding 0.35s ease';
                    card.style.transform = 'translateY(-12px) scale(0.96)';
                    card.style.opacity = '0';
                    card.style.maxHeight = card.offsetHeight + 'px';
                    card.style.overflow = 'hidden';

                    // Force browser reflow to trigger smooth collapse
                    card.offsetHeight;

                    card.style.maxHeight = '0px';
                    card.style.paddingTop = '0px';
                    card.style.paddingBottom = '0px';
                    card.style.marginTop = '0px';
                    card.style.marginBottom = '0px';
                    card.style.borderWidth = '0px';

                    setTimeout(() => {
                        card.style.display = 'none';
                        // Clean up animation properties so when switching to "Resolved" tab it renders cleanly
                        card.style.removeProperty('max-height');
                        card.style.removeProperty('overflow');
                        card.style.removeProperty('padding-top');
                        card.style.removeProperty('padding-bottom');
                        card.style.removeProperty('margin-top');
                        card.style.removeProperty('margin-bottom');
                        card.style.removeProperty('border-width');
                        card.style.removeProperty('transform');
                        card.style.removeProperty('opacity');
                        card.style.removeProperty('transition');

                        // Re-evaluate visible count in current view
                        const remaining = Array.from(document.querySelectorAll('.mgr-note-card'))
                            .filter(c => c.style.display !== 'none');
                        const filterEmptyState = document.getElementById('filterEmptyState');
                        if (remaining.length === 0 && filterEmptyState) {
                            filterEmptyState.style.display = 'flex';
                        }
                    }, 400);
                }
            } catch (err) {
                console.error('Error resolving shift note:', err);
                form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const filterButtons = document.querySelectorAll('.filter-btn');

            filterButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    filterButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const filterValue = this.getAttribute('data-filter');
                    applyShiftNotesFilter(filterValue);
                });
            });

            // Initial filter run to guarantee resolved notes are hidden from the active view
            applyShiftNotesFilter('all');
        });
    </script>
    <script src="<?= asset('js/pos-modal.js') ?>?v=1.0.0"></script>
    <script src="<?= asset('js/clock-out.js') ?>?v=1.0.0"></script>
</body>

</html>
