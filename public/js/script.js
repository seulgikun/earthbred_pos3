function getAppBasePath() {
    const pathname = window.location.pathname;
    const idx = pathname.toLowerCase().indexOf('/backend/public');
    if (idx !== -1) {
        return pathname.substring(0, idx + '/backend/public'.length);
    }
    return '';
}

document.addEventListener('DOMContentLoaded', () => {
    // Password / PIN visibility toggles
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetInput = targetId ? document.getElementById(targetId) : this.previousElementSibling;
            if (!targetInput) return;

            if (targetInput.type === 'password') {
                targetInput.type = 'text';
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                targetInput.type = 'password';
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });
    });

    // Tab Switching: Cashier PIN vs Manager
    const tabCashier = document.getElementById('tabCashier');
    const tabManager = document.getElementById('tabManager');
    const cashierLoginForm = document.getElementById('cashierLoginForm');
    const managerLoginForm = document.getElementById('managerLoginForm');
    const cashierPinInput = document.getElementById('cashierPin');

    if (tabCashier && tabManager && cashierLoginForm && managerLoginForm) {
        tabCashier.addEventListener('click', () => {
            tabCashier.classList.add('active');
            tabManager.classList.remove('active');
            cashierLoginForm.style.display = 'flex';
            managerLoginForm.style.display = 'none';
            if (cashierPinInput) cashierPinInput.focus();
        });

        tabManager.addEventListener('click', () => {
            tabManager.classList.add('active');
            tabCashier.classList.remove('active');
            cashierLoginForm.style.display = 'none';
            managerLoginForm.style.display = 'flex';
            const emailInput = document.getElementById('email');
            if (emailInput) {
                emailInput.value = '';
                emailInput.focus();
            }
        });
    }

    // 1. Cashier PIN-only Login Handler
    if (cashierLoginForm) {
        cashierLoginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const pin = (document.getElementById('cashierPin')?.value || '').trim();
            const loginBtn = document.getElementById('cashierLoginBtn');

            if (!/^\d{6}$/.test(pin)) {
                PosDialog.alert({
                    title: 'Invalid PIN',
                    message: 'Please enter a valid 6-digit numeric PIN.',
                    icon: 'fa-triangle-exclamation',
                    iconType: 'warning'
                });
                return;
            }

            try {
                loginBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Verifying PIN...';
                loginBtn.disabled = true;

                const BASE = getAppBasePath();
                const response = await fetch(BASE + '/api/login/pin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ pin })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    localStorage.setItem('userId', data.user.id);
                    localStorage.setItem('userRole', data.user.role);
                    localStorage.setItem('userName', data.user.name);

                    // Cashiers are directed straight to the POS terminal
                    window.location.href = BASE + '/pos';
                } else {
                    PosDialog.alert({
                        title: 'Access Denied',
                        message: data.message || 'Invalid 6-digit PIN. Please try again.',
                        icon: 'fa-triangle-exclamation',
                        iconType: 'danger',
                        buttonText: 'Try Again'
                    });
                    loginBtn.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket"></i> Log In to POS';
                    loginBtn.disabled = false;
                }
            } catch (error) {
                console.error('Error logging in with PIN:', error);
                PosDialog.alert({
                    title: 'Server Error',
                    message: 'Could not connect to authentication server. Please try again.',
                    icon: 'fa-circle-xmark',
                    iconType: 'danger'
                });
                loginBtn.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket"></i> Log In to POS';
                loginBtn.disabled = false;
            }
        });
    }

    // 2. Manager / Owner Email & Password Login Handler
    if (managerLoginForm) {
        managerLoginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = (document.getElementById('email')?.value || '').trim();
            const password = document.getElementById('password')?.value || '';
            const loginBtn = document.getElementById('managerLoginBtn');

            try {
                loginBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Logging in...';
                loginBtn.disabled = true;

                const BASE = getAppBasePath();
                const API_URL = BASE + '/api/login'; 
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();

                if (response.ok && data.success) {
                    localStorage.setItem('userId', data.user.id);
                    localStorage.setItem('userRole', data.user.role);
                    localStorage.setItem('userName', data.user.name);
                    localStorage.setItem('userEmail', data.user.email || '');
                    
                    if (data.user.role === 'owner' || data.user.role === 'manager') {
                        window.location.href = BASE + '/manager';
                    } else {
                        window.location.href = BASE + '/pos';
                    }
                } else {
                    PosDialog.alert({
                        title: 'Authentication Error',
                        message: data.message || 'Login failed. Please check your credentials.',
                        icon: 'fa-triangle-exclamation',
                        iconType: 'danger',
                        buttonText: 'Try Again'
                    });
                    loginBtn.innerHTML = '<i class="fa-solid fa-lock"></i> Log In as Manager';
                    loginBtn.disabled = false;
                }
                
            } catch (error) {
                console.error('Error logging in:', error);
                PosDialog.alert({
                    title: 'Server Error',
                    message: 'Could not connect to authentication server. Please try again.',
                    icon: 'fa-circle-xmark',
                    iconType: 'danger'
                });
                loginBtn.innerHTML = '<i class="fa-solid fa-lock"></i> Log In as Manager';
                loginBtn.disabled = false;
            }
        });
    }

    // Forgot Password Handling
    const forgotPasswordBtn = document.querySelector('.forgot-password');
    if (forgotPasswordBtn) {
        forgotPasswordBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const emailInput = document.getElementById('email');
            let email = emailInput ? emailInput.value.trim() : '';

            if (!email) {
                email = await PosDialog.prompt({
                    title: 'Forgot Password',
                    message: 'Please enter your registered email address to receive a password reset link:',
                    placeholder: 'e.g., cashier@earthbred.com',
                    icon: 'fa-key',
                    iconType: 'info',
                    confirmText: 'Send Reset Link'
                });
            }

            if (!email) return;

            try {
                const BASE = getAppBasePath();
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

                const response = await fetch(BASE + '/api/forgot-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ email })
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    PosDialog.alert({
                        title: 'Reset Link Dispatched',
                        message: data.message,
                        icon: 'fa-paper-plane',
                        iconType: 'success',
                        buttonText: 'Great'
                    });
                } else {
                    PosDialog.alert({
                        title: 'Password Reset Error',
                        message: data.message || 'Failed to dispatch password reset link.',
                        icon: 'fa-triangle-exclamation',
                        iconType: 'danger',
                        buttonText: 'Close'
                    });
                }
            } catch (err) {
                console.error('Forgot Password error:', err);
                PosDialog.alert({
                    title: 'Request Failed',
                    message: 'An unexpected error occurred. Please try again later.',
                    icon: 'fa-circle-xmark',
                    iconType: 'danger'
                });
            }
        });
    }
});
