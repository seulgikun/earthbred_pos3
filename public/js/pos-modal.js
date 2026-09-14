/**
 * Earthbred POS - Custom Enterprise Dialog Helper
 * Replaces native browser alert(), confirm(), and prompt() with custom animated popups.
 */
window.PosDialog = (function() {
    let overlay = null;

    function getOrCreateOverlay() {
        if (overlay) return overlay;

        overlay = document.createElement('div');
        overlay.className = 'pos-dialog-overlay';
        overlay.id = 'posCustomDialogOverlay';
        overlay.innerHTML = `
            <div class="pos-dialog-card" id="posCustomDialogCard">
                <div class="pos-dialog-icon-wrapper" id="posDialogIcon">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <h3 class="pos-dialog-title" id="posDialogTitle">Title</h3>
                <p class="pos-dialog-message" id="posDialogMessage">Message</p>
                <div id="posDialogInputWrapper" style="display:none;">
                    <input type="text" class="pos-dialog-input" id="posDialogInput" placeholder="" />
                </div>
                <div class="pos-dialog-actions" id="posDialogActions"></div>
            </div>
        `;
        document.body.appendChild(overlay);
        return overlay;
    }

    function showOverlay() {
        const el = getOrCreateOverlay();
        requestAnimationFrame(() => {
            el.classList.add('active');
        });
    }

    function hideOverlay() {
        if (!overlay) return;
        overlay.classList.remove('active');
    }

    return {
        /**
         * Custom Confirm Dialog (Returns Promise resolving to true/false)
         */
        confirm: function({ title = 'Confirm Action', message = 'Are you sure?', icon = 'fa-circle-question', iconType = 'warning', confirmText = 'Confirm', cancelText = 'Cancel', confirmType = 'confirm-warning' } = {}) {
            return new Promise((resolve) => {
                getOrCreateOverlay();

                const iconEl = document.getElementById('posDialogIcon');
                const titleEl = document.getElementById('posDialogTitle');
                const msgEl = document.getElementById('posDialogMessage');
                const inputWrap = document.getElementById('posDialogInputWrapper');
                const actionsEl = document.getElementById('posDialogActions');

                iconEl.className = `pos-dialog-icon-wrapper ${iconType}`;
                iconEl.innerHTML = `<i class="fa-solid ${icon}"></i>`;
                titleEl.textContent = title;
                msgEl.textContent = message;
                msgEl.style.display = 'block';
                inputWrap.style.display = 'none';

                actionsEl.innerHTML = `
                    <button type="button" class="pos-dialog-btn cancel" id="posDialogCancelBtn">${cancelText}</button>
                    <button type="button" class="pos-dialog-btn ${confirmType}" id="posDialogConfirmBtn">${confirmText}</button>
                `;

                showOverlay();

                const cancelBtn = document.getElementById('posDialogCancelBtn');
                const confirmBtn = document.getElementById('posDialogConfirmBtn');

                cancelBtn.onclick = () => {
                    hideOverlay();
                    resolve(false);
                };

                confirmBtn.onclick = () => {
                    hideOverlay();
                    resolve(true);
                };
            });
        },

        /**
         * Custom Alert Dialog (Returns Promise resolving when closed)
         */
        alert: function({ title = 'Notice', message = '', icon = 'fa-circle-info', iconType = 'info', buttonText = 'OK', buttonType = 'confirm-primary' } = {}) {
            return new Promise((resolve) => {
                getOrCreateOverlay();

                const iconEl = document.getElementById('posDialogIcon');
                const titleEl = document.getElementById('posDialogTitle');
                const msgEl = document.getElementById('posDialogMessage');
                const inputWrap = document.getElementById('posDialogInputWrapper');
                const actionsEl = document.getElementById('posDialogActions');

                iconEl.className = `pos-dialog-icon-wrapper ${iconType}`;
                iconEl.innerHTML = `<i class="fa-solid ${icon}"></i>`;
                titleEl.textContent = title;
                msgEl.textContent = message;
                msgEl.style.display = 'block';
                inputWrap.style.display = 'none';

                actionsEl.innerHTML = `
                    <button type="button" class="pos-dialog-btn ${buttonType}" id="posDialogOkBtn">${buttonText}</button>
                `;

                showOverlay();

                const okBtn = document.getElementById('posDialogOkBtn');
                okBtn.onclick = () => {
                    hideOverlay();
                    resolve(true);
                };
            });
        },

        /**
         * Custom Prompt Dialog (Returns Promise resolving to string value or null)
         */
        prompt: function({ title = 'Input Required', message = '', placeholder = 'Enter text...', defaultValue = '', icon = 'fa-pen-to-square', iconType = 'info', confirmText = 'Submit', cancelText = 'Cancel' } = {}) {
            return new Promise((resolve) => {
                getOrCreateOverlay();

                const iconEl = document.getElementById('posDialogIcon');
                const titleEl = document.getElementById('posDialogTitle');
                const msgEl = document.getElementById('posDialogMessage');
                const inputWrap = document.getElementById('posDialogInputWrapper');
                const inputEl = document.getElementById('posDialogInput');
                const actionsEl = document.getElementById('posDialogActions');

                iconEl.className = `pos-dialog-icon-wrapper ${iconType}`;
                iconEl.innerHTML = `<i class="fa-solid ${icon}"></i>`;
                titleEl.textContent = title;
                if (message) {
                    msgEl.textContent = message;
                    msgEl.style.display = 'block';
                } else {
                    msgEl.style.display = 'none';
                }

                inputWrap.style.display = 'block';
                inputEl.placeholder = placeholder;
                inputEl.value = defaultValue;

                actionsEl.innerHTML = `
                    <button type="button" class="pos-dialog-btn cancel" id="posDialogCancelBtn">${cancelText}</button>
                    <button type="button" class="pos-dialog-btn confirm-primary" id="posDialogConfirmBtn">${confirmText}</button>
                `;

                showOverlay();
                setTimeout(() => inputEl.focus(), 150);

                const cancelBtn = document.getElementById('posDialogCancelBtn');
                const confirmBtn = document.getElementById('posDialogConfirmBtn');

                const handleConfirm = () => {
                    const val = inputEl.value.trim();
                    hideOverlay();
                    resolve(val);
                };

                cancelBtn.onclick = () => {
                    hideOverlay();
                    resolve(null);
                };

                confirmBtn.onclick = handleConfirm;
                inputEl.onkeyup = (e) => {
                    if (e.key === 'Enter') handleConfirm();
                };
            });
        }
    };
})();

// Automatically attach authenticated actor identity (from localStorage) to all API requests
(function() {
    const _origFetch = window.fetch;
    window.fetch = function(resource, config = {}) {
        config = config || {};
        config.headers = config.headers || {};

        const userName = localStorage.getItem('userName');
        const userRole = localStorage.getItem('userRole');
        const userId = localStorage.getItem('userId');
        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = tokenMeta ? tokenMeta.getAttribute('content') : '';

        if (config.headers instanceof Headers) {
            if (userName && !config.headers.has('X-User-Name')) config.headers.append('X-User-Name', userName);
            if (userRole && !config.headers.has('X-User-Role')) config.headers.append('X-User-Role', userRole);
            if (userId && !config.headers.has('X-User-Id')) config.headers.append('X-User-Id', userId);
            if (csrfToken && !config.headers.has('X-CSRF-TOKEN')) config.headers.append('X-CSRF-TOKEN', csrfToken);
        } else if (typeof config.headers === 'object') {
            if (userName && !config.headers['X-User-Name']) config.headers['X-User-Name'] = userName;
            if (userRole && !config.headers['X-User-Role']) config.headers['X-User-Role'] = userRole;
            if (userId && !config.headers['X-User-Id']) config.headers['X-User-Id'] = userId;
            if (csrfToken && !config.headers['X-CSRF-TOKEN']) config.headers['X-CSRF-TOKEN'] = csrfToken;
        }

        return _origFetch.call(this, resource, config);
    };
})();

