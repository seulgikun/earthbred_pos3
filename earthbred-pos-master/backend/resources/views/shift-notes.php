<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthbred - Shift Notes</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Montserrat:wght@400;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/pos.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/shift-notes.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/pos-modal.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/ios26-theme.css') ?>?v=1.0.0">
    <link rel="icon" type="image/png" href="<?= asset('favicon.png') ?>?v=3.0">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>?v=3.0">
    <style>
        .pos-category-tag.tag-general { background-color: #e3f2fd !important; color: #1565c0 !important; border: 1px solid #bbdefb; }
        .pos-category-tag.tag-equipment { background-color: #fff3e0 !important; color: #ef6c00 !important; border: 1px solid #ffe0b2; }
        .pos-category-tag.tag-complaint { background-color: #ffebee !important; color: #c62828 !important; border: 1px solid #ffcdd2; }
        .pos-category-tag.tag-task { background-color: #e8f5e9 !important; color: #2e7d32 !important; border: 1px solid #c8e6c9; }

        /* Override shift-notes.css & pos.css body/app-container to use sidebar layout */
        body {
            display: block !important;
            height: 100vh !important;
            overflow: hidden !important;
            background-color: #222 !important;
        }
        .app-container {
            display: flex !important;
            flex-direction: row !important;
            padding: 0 !important;
            width: 100% !important;
            height: 100vh !important;
            background-color: #f7f3eb !important;
        }
        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background-color: #FAF4E8;
            height: 100vh;
            overflow: hidden;
        }
        .top-nav {
            padding: 1.25rem 2rem !important;
            margin-bottom: 0 !important;
            border-bottom: 2px solid #e5d9c5;
        }
        .page-title {
            position: static !important;
            transform: none !important;
            display: block !important;
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 1.3rem;
            color: #000;
        }
        .content-wrapper {
            padding: 1.5rem 2rem 2rem !important;
            overflow-y: auto !important;
            flex-grow: 1 !important;
            display: flex;
            gap: 1.5rem;
        }

        @media (max-width: 899px) {
            .app-container {
                flex-direction: column !important;
            }
            .content-wrapper {
                flex-direction: column !important;
                padding: 1rem !important;
                gap: 1.25rem !important;
            }
            .write-note-section, .view-notes-section {
                width: 100% !important;
                max-width: 100% !important;
            }
            .top-nav {
                padding: 0.85rem 1.25rem !important;
            }
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
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos'">
                        <span class="menu-icon">🍽️</span> All Items
                    </li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=coffee'">
                        <span class="menu-icon">☕</span> Coffee
                    </li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=non-coffee'">
                        <span class="menu-icon">🍵</span> Non-Coffee
                    </li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=lemonade'">
                        <span class="menu-icon">🍹</span> Lemonade
                    </li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/pos?filter=foods'">
                        <span class="menu-icon">🍲</span> Foods
                    </li>
                    <li class="menu-item active" onclick="window.location.href='<?= url('') ?>/shift-notes'">
                        <span class="menu-icon">📝</span> Shift Notes
                    </li>
                    <li class="menu-item" onclick="window.location.href='<?= url('') ?>/queue'" style="border-top: 1px solid #e5d9c5; margin-top: 0.5rem; padding-top: 1rem;">
                        <span class="menu-icon">📋</span> Order Queuing
                    </li>
                    <li class="menu-item" id="inventory-menu-item" onclick="window.location.href='<?= url('') ?>/inventory'" style="border-top: 1px solid #e5d9c5; margin-top: 0.5rem; padding-top: 1rem;">
                        <span class="menu-icon">📦</span> Inventory
                    </li>
                    <li class="menu-item" id="sales-report-menu-item" onclick="window.location.href='<?= url('') ?>/manager/sales-report'">
                        <span class="menu-icon">📊</span> Sales Report
                    </li>
                </ul>
            </nav>

            <div class="clock-out">
                <i class="fa-solid fa-power-off"></i> Clock Out
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Top Navigation -->
            <div class="top-nav" style="display: flex; align-items: center; gap: 0.75rem;">
                <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Toggle Navigation Menu" type="button" onclick="if(typeof window.toggleGlobalSidebar==='function')window.toggleGlobalSidebar();" style="touch-action:manipulation;cursor:pointer;">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="page-title" style="position: static; transform: none; display: block;">Shift Notes</div>
            </div>

            <div class="content-wrapper">
            <!-- Left Side: Write a Note -->
            <div class="write-note-section">
                <div class="section-card">
                    <h3 class="section-title">WRITE A NOTE</h3>
                    <form action="<?= url('') ?>/shift-notes" method="POST">
                        <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="cashier_name" id="shiftNoteCashierName" value="Cashier">
                        <script>
                            document.addEventListener('DOMContentLoaded', () => {
                                if (localStorage.getItem('userName')) {
                                    document.getElementById('shiftNoteCashierName').value = localStorage.getItem('userName');
                                }
                            });
                        </script>
                        
                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label for="category" style="display: block; font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 0.85rem; color: #482f25; margin-bottom: 0.5rem; text-transform: uppercase;">Category</label>
                            <select name="category" id="category" style="width: 100%; padding: 12px; border: 1px solid #d1c0a5; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 0.95rem; background-color: #faf8f5; outline: none; color: #333;" required>
                                <option value="General">General</option>
                                <option value="Equipment">Equipment</option>
                                <option value="Complaint">Complaint</option>
                                <option value="Task">Task</option>
                            </select>
                        </div>

                        <textarea name="note" class="note-textarea" placeholder="Write your shift note here... (e.g. ice machine not working, customer complaint about wait time, restock cups before next shift)" required style="height: 140px;"></textarea>
                        <button type="submit" class="add-note-btn">
                            <i class="fa-solid fa-plus"></i> Add Note
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Side: Notes Log -->
            <div class="notes-log-section">
                <div class="section-card log-card">
                    <h3 class="section-title">Notes Log</h3>
                    <div class="log-container">
                        <?php if (isset($notes) && $notes->count() > 0): ?>
                            <div class="notes-list">
                                <?php foreach ($notes as $note): ?>
                                    <div class="note-item <?= $note->is_done ? 'done' : '' ?>">
                                        <div class="note-header" style="display: flex; justify-content: space-between; align-items: center;">
                                            <span class="note-author" style="display: flex; align-items: center; gap: 8px;">
                                                <i class="fa-solid fa-user"></i> 
                                                <?= htmlspecialchars($note->cashier_name) ?>
                                                <span class="pos-category-tag tag-<?= strtolower($note->category ?? 'general') ?>" style="font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; text-transform: uppercase;">
                                                    <?= htmlspecialchars($note->category ?? 'General') ?>
                                                </span>
                                            </span>
                                            <span class="note-time"><?= $note->created_at->format('M d, h:i A') ?></span>
                                        </div>
                                        <div class="note-body">
                                            <?= nl2br(htmlspecialchars($note->note)) ?>
                                        </div>
                                        <?php if (!$note->is_done): ?>
                                            <form action="<?= url('') ?>/shift-notes/<?= $note->id ?>/done" method="POST" class="mark-done-form">
                                                <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                                                <input type="hidden" name="_method" value="PATCH">
                                                <button type="submit" class="mark-done-btn">Mark as Done</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">📝</div>
                                <p class="empty-title">No notes yet.</p>
                                <p class="empty-desc">Add your first shift note on the left.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <script src="<?= asset('js/pos-modal.js') ?>?v=1.0.0"></script>
        <script src="<?= asset('js/clock-out.js') ?>?v=1.0.0"></script>
    </div>
</body>
</html>
