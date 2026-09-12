/**
 * Earthbred POS - Universal Clock Out Handler
 * Ensures Clock Out is ALWAYS 100% functional on every page across Cashier & Manager sides.
 */
document.addEventListener('DOMContentLoaded', () => {
    const getBaseUrl = function() {
        const pathname = window.location.pathname;
        const idx = pathname.toLowerCase().indexOf('/backend/public');
        return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
    };

    // ── Guard to prevent double-firing ──────────────────────────────────────
    let _clockOutPending = false;

    function handleClockOut(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent click from bubbling to sidebar/overlay handlers
        }
        if (_clockOutPending) return; // Already showing – don't open twice
        _clockOutPending = true;

        window.PosDialog.confirm({
                title: 'Clock Out',
                message: 'Are you sure you want to end your active shift and clock out?',
                icon: 'fa-power-off',
                iconType: 'clockout',
                confirmText: 'Clock Out',
                cancelText: 'Stay Logged In',
                confirmType: 'confirm-danger'
            }).then(confirmed => {
                _clockOutPending = false;
                if (confirmed) performLogout();
            });
    }

    function performLogout() {
        try {
            localStorage.removeItem('earthbred_cart');
            localStorage.removeItem('userRole');
            localStorage.removeItem('userName');
            localStorage.removeItem('userId');
            localStorage.removeItem('userEmail');
        } catch(e) {}
        window.location.href = getBaseUrl() + '/login';
    }

    // Synchronize active account user name across all sidebars and headers
    function syncUserProfileName() {
        let savedName = localStorage.getItem('userName');
        if (savedName === 'Earthbred Owner' || (localStorage.getItem('userRole') === 'owner' && (!savedName || savedName === 'Owner'))) {
            savedName = 'Christopher Lim';
            localStorage.setItem('userName', savedName);
        } else if (savedName === 'Earthbred Manager') {
            savedName = 'junric limpangog';
            localStorage.setItem('userName', savedName);
        }
        if (savedName) {
            document.querySelectorAll('.user-name, .mgr-user-name, #sidebarUserName, #cashierSidebarUserName').forEach(el => {
                el.textContent = savedName;
            });
        }
        const savedRole = localStorage.getItem('userRole');
        if (savedRole) {
            document.querySelectorAll('.user-id, .mgr-user-role').forEach(el => {
                const roleCapitalized = savedRole.charAt(0).toUpperCase() + savedRole.slice(1);
                if (el.textContent.includes('Staff') || el.textContent.includes('Owner') || el.textContent.includes('Manager')) {
                    el.textContent = roleCapitalized;
                }
            });
        }
    }
    syncUserProfileName();

    // Attach listener to any clock out buttons/elements on the current page
    const selectors = ['.clock-out', '.mgr-clock-out', '#clockOutBtn', '#sidebarClockOut', '[data-action="clock-out"]'];
    selectors.forEach(selector => {
        document.querySelectorAll(selector).forEach(btn => {
            btn.style.cursor = 'pointer';
            btn.addEventListener('click', handleClockOut);
        });
    });

    // =============================================
    // Universal Responsive Tablet/Mobile Drawer (iPad & Phones)
    // =============================================
    window.isAnySidebarActive = function() {
        const sidebars = document.querySelectorAll('.sidebar, .mgr-sidebar, .inv-sidebar');
        return Array.from(sidebars).some(sb => sb.classList.contains('active'));
    };

    window.toggleGlobalSidebar = function() {
        const sidebars = document.querySelectorAll('.sidebar, .mgr-sidebar, .inv-sidebar');
        const overlays = document.querySelectorAll('.sidebar-overlay, #sidebarOverlay');
        const willBeActive = !window.isAnySidebarActive();
        sidebars.forEach(sb => sb.classList.toggle('active', willBeActive));
        overlays.forEach(ov => ov.classList.toggle('active', willBeActive));
        if (window.innerWidth <= 1280) {
            document.body.style.overflow = willBeActive ? 'hidden' : '';
        }
    };

    window.closeGlobalSidebar = function() {
        const sidebars = document.querySelectorAll('.sidebar, .mgr-sidebar, .inv-sidebar');
        const overlays = document.querySelectorAll('.sidebar-overlay, #sidebarOverlay');
        sidebars.forEach(sb => sb.classList.remove('active'));
        overlays.forEach(ov => ov.classList.remove('active'));
        document.body.style.overflow = '';
    };

    // Global event delegation for close/overlay buttons (toggle handled by inline onclick)
    document.addEventListener('click', (e) => {
        // Skip toggle buttons — they use inline onclick to avoid double-fire
        if (e.target.closest('.sidebar-toggle-btn, .mgr-sidebar-toggle-btn, #sidebarToggleBtn')) {
            return;
        }

        const closeBtn = e.target.closest('.sidebar-close-btn, #sidebarCloseBtn');
        if (closeBtn) {
            e.preventDefault();
            e.stopPropagation();
            window.closeGlobalSidebar();
            return;
        }

        const overlay = e.target.closest('.sidebar-overlay, #sidebarOverlay');
        if (overlay) {
            e.preventDefault();
            e.stopPropagation();
            window.closeGlobalSidebar();
        }
    });

    // Note: sidebar toggle handled by inline onclick on the button element.
    // This prevents double-fire that occurred with simultaneous touchend + click.

    // Close on Escape key press
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isAnySidebarActive()) {
            closeGlobalSidebar();
        }
    });

    // Close drawer when clicking nav items on tablet/mobile screens (up to 1280px)
    // Explicitly skip clock-out buttons to avoid accidental clock-out modal trigger
    document.querySelectorAll('.menu-item:not(.clock-out), .mgr-nav-item:not(.mgr-clock-out), .inv-nav-item').forEach(item => {
        item.addEventListener('click', () => {
            if (window.innerWidth <= 1280) {
                closeGlobalSidebar();
            }
        });
    });

    // Reset body overflow on window resize to desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth > 1280) {
            document.body.style.overflow = '';
            document.querySelectorAll('.sidebar, .mgr-sidebar, .inv-sidebar').forEach(sb => sb.classList.remove('active'));
            document.querySelectorAll('.sidebar-overlay, #sidebarOverlay').forEach(ov => ov.classList.remove('active'));
        }
    });
});

