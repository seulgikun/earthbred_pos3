<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earthbred - AI Assistant</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Montserrat:wght@400;600;700;800;900&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/manager.css') ?>?v=1.0.0">
    <link rel="stylesheet" href="<?= asset('css/manager-ai.css') ?>?v=1.0.0">
    <link rel="stylesheet" href="<?= asset('css/pos-modal.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/ios26-theme.css') ?>?v=1.0.0">
    <link rel="icon" type="image/png" href="<?= asset('favicon.png') ?>?v=3.0">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>?v=3.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <!-- Marked.js for markdown parsing -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
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
                    <li class="mgr-nav-item"
                        onclick="window.location.href='<?= url('') ?>/manager/products'">
                        <i class="fa-solid fa-tags mgr-nav-icon"></i> Product Management
                    </li>
                    <li class="mgr-nav-item"
                        onclick="window.location.href='<?= url('') ?>/manager/shift-notes'">
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
                    <li class="mgr-nav-item active">
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

        <!-- Main Content Area -->
        <main class="ai-main">
            <header class="ai-header" style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <button class="sidebar-toggle-btn" id="sidebarToggleBtn" title="Toggle Navigation Menu" type="button" onclick="if(typeof window.toggleGlobalSidebar==='function')window.toggleGlobalSidebar();" style="touch-action:manipulation;cursor:pointer;">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h2>MANAGER DASHBOARD</h2>
                </div>
            </header>

            <div class="ai-content-wrap">
                <!-- AI Title Banner -->
                <div class="ai-title-banner">
                    <div class="ai-icon">🤖</div>
                    <div class="ai-title-text">
                        <h3>EarthBred AI Assistant</h3>
                        <p>Ask about sales trends, menu ideas, inventory, shift notes, or anything about running the
                            café.</p>
                    </div>
                    <button id="clearChatBtn" type="button" title="Clear chat history" style="
                        margin-left: auto;
                        background: rgba(255,255,255,0.08);
                        border: 1px solid rgba(255,255,255,0.18);
                        color: #fff;
                        border-radius: 10px;
                        padding: 7px 14px;
                        font-size: 0.82rem;
                        font-family: inherit;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        white-space: nowrap;
                        transition: background 0.2s;
                    " onmouseover="this.style.background='rgba(255,255,255,0.18)'" onmouseout="this.style.background='rgba(255,255,255,0.08)'">
                        <i class="fa-solid fa-trash-can" style="font-size:0.78rem;"></i> Clear Chat
                    </button>
                </div>

                <!-- Chat Display Area -->
                <div class="ai-chat-window" id="chatWindow">
                    <div class="chat-message bot-message">
                        <div class="message-avatar">🤖</div>
                        <div class="message-bubble bot-bubble">
                            <p>Hi! I'm your EarthBred AI Assistant powered by Gemini. I can help you with:</p>
                            <ul>
                                <li>Sales performance & trends</li>
                                <li>Inventory & stock questions</li>
                                <li>Staff shift note summaries</li>
                                <li>Menu & pricing suggestions</li>
                                <li>General café management advice</li>
                            </ul>
                            <p>What would you like to know?</p>
                        </div>
                    </div>
                </div>

                <!-- Input Area -->
                <div class="ai-input-area">
                    <form id="chatForm" class="chat-form">
                        <input type="text" id="chatInput" placeholder="Ask anything about your café..." required
                            autocomplete="off">
                        <button type="submit" class="send-btn"><i class="fa-solid fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="<?= asset('js/pos-modal.js') ?>?v=1.0.0"></script>
    <script src="<?= asset('js/clock-out.js') ?>?v=1.0.0"></script>
    <script src="<?= asset('js/manager-ai.js') ?>?v=1.2.0"></script>
</body>

</html>
