/* ============================================================
   manager-accounts.js — Earthbred Account Management Logic
   ============================================================ */

document.addEventListener('DOMContentLoaded', () => {
    const BASE = (function() {
        const pathname = window.location.pathname;
        const idx = pathname.toLowerCase().indexOf('/backend/public');
        return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
    })();
    
    // Check role again just in case
    if (localStorage.getItem('userRole') !== 'owner') {
        window.location.href = BASE + '/manager';
        return;
    }

    loadAccounts();

    // Random 6-digit PIN generator helper
    function generateRandomPin() {
        return String(Math.floor(100000 + Math.random() * 900000));
    }

    // Role switcher for Add Account Modal
    const accountRoleSelect = document.getElementById('accountRole');
    const cashierPinSection = document.getElementById('cashierPinSection');
    const managerPasswordSection = document.getElementById('managerPasswordSection');
    const accountPinInput = document.getElementById('accountPin');
    const accountPasswordInput = document.getElementById('accountPassword');
    const accountPasswordConfirmInput = document.getElementById('accountPasswordConfirm');

    function syncAddAccountRoleUI() {
        const role = accountRoleSelect ? accountRoleSelect.value : 'cashier';
        if (role === 'cashier') {
            if (cashierPinSection) cashierPinSection.style.display = 'block';
            if (managerPasswordSection) managerPasswordSection.style.display = 'none';
            if (accountPinInput) {
                accountPinInput.required = true;
                if (!accountPinInput.value) accountPinInput.value = generateRandomPin();
            }
            if (accountPasswordInput) accountPasswordInput.required = false;
            if (accountPasswordConfirmInput) accountPasswordConfirm.required = false;
        } else {
            if (cashierPinSection) cashierPinSection.style.display = 'none';
            if (managerPasswordSection) managerPasswordSection.style.display = 'block';
            if (accountPinInput) accountPinInput.required = false;
            if (accountPasswordInput) accountPasswordInput.required = true;
            if (accountPasswordConfirmInput) accountPasswordConfirm.required = true;
        }
    }

    if (accountRoleSelect) {
        accountRoleSelect.addEventListener('change', syncAddAccountRoleUI);
    }

    // Generate PIN button handlers
    const generatePinBtn = document.getElementById('generatePinBtn');
    if (generatePinBtn && accountPinInput) {
        generatePinBtn.addEventListener('click', () => {
            accountPinInput.value = generateRandomPin();
        });
    }

    const cpGeneratePinBtn = document.getElementById('cpGeneratePinBtn');
    const cpNewPinInput = document.getElementById('cpNewPin');
    if (cpGeneratePinBtn && cpNewPinInput) {
        cpGeneratePinBtn.addEventListener('click', () => {
            cpNewPinInput.value = generateRandomPin();
        });
    }

    // Setup Modals
    window.openAddAccountModal = function() {
        document.getElementById('addAccountForm').reset();
        const hint = document.getElementById('addAccMatchHint');
        if (hint) hint.textContent = '';
        if (accountRoleSelect) accountRoleSelect.value = 'cashier';
        syncAddAccountRoleUI();

        const pwd = document.getElementById('accountPassword');
        const pwdC = document.getElementById('accountPasswordConfirm');
        if (pwd) pwd.type = 'password';
        if (pwdC) pwdC.type = 'password';
        document.querySelectorAll('#addAccountModal .password-toggle').forEach(t => {
            t.classList.remove('fa-eye-slash');
            t.classList.add('fa-eye');
        });
        document.getElementById('addAccountModal').classList.add('active');
    };

    window.closeAddAccountModal = function() {
        document.getElementById('addAccountModal').classList.remove('active');
    };

    window.openChangePasswordModal = function(id, name, role = 'cashier', currentPin = '') {
        document.getElementById('changePasswordForm').reset();
        document.getElementById('cpUserId').value = id;
        document.getElementById('cpUserRole').value = role;
        document.getElementById('cpUserName').textContent = name;
        
        const roleBadge = document.getElementById('cpRoleBadge');
        if (roleBadge) {
            roleBadge.textContent = role.toUpperCase();
            roleBadge.className = `role-badge role-${role}`;
        }

        const cpModalTitle = document.getElementById('cpModalTitle');
        const cpPinSection = document.getElementById('cpPinSection');
        const cpPasswordSection = document.getElementById('cpPasswordSection');
        const cpNewPin = document.getElementById('cpNewPin');
        const cpNewPwd = document.getElementById('cpNewPassword');
        const cpNewPwdC = document.getElementById('cpNewPasswordConfirm');
        const hint = document.getElementById('cpMatchHint');
        if (hint) hint.textContent = '';

        if (role === 'cashier') {
            if (cpModalTitle) cpModalTitle.textContent = 'Update Cashier PIN';
            if (cpPinSection) cpPinSection.style.display = 'block';
            if (cpPasswordSection) cpPasswordSection.style.display = 'none';
            if (cpNewPin) {
                cpNewPin.value = currentPin || generateRandomPin();
                cpNewPin.required = true;
            }
            if (cpNewPwd) cpNewPwd.required = false;
            if (cpNewPwdC) cpNewPwdC.required = false;
        } else {
            if (cpModalTitle) cpModalTitle.textContent = 'Change Manager Password';
            if (cpPinSection) cpPinSection.style.display = 'none';
            if (cpPasswordSection) cpPasswordSection.style.display = 'block';
            if (cpNewPin) cpNewPin.required = false;
            if (cpNewPwd) {
                cpNewPwd.type = 'password';
                cpNewPwd.required = true;
            }
            if (cpNewPwdC) {
                cpNewPwdC.type = 'password';
                cpNewPwdC.required = true;
            }
        }

        document.querySelectorAll('#changePasswordModal .password-toggle').forEach(t => {
            t.classList.remove('fa-eye-slash');
            t.classList.add('fa-eye');
        });
        document.getElementById('changePasswordModal').classList.add('active');
    };

    window.closeChangePasswordModal = function() {
        document.getElementById('changePasswordModal').classList.remove('active');
    };

    // Load Accounts
    function loadAccounts() {
        fetch(`${BASE}/api/users`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.owner && data.owner.email) {
                        localStorage.setItem('userEmail', data.owner.email);
                    }
                    renderAccountsTable(data.users);
                } else {
                    console.error('Failed to load accounts');
                }
            })
            .catch(err => console.error(err));
    }

    function renderAccountsTable(users) {
        const tbody = document.getElementById('accountsTableBody');
        tbody.innerHTML = '';

        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No accounts found.</td></tr>';
            return;
        }

        users.forEach(user => {
            const tr = document.createElement('tr');
            
            // Format date
            const date = new Date(user.created_at);
            const dateStr = date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

            const isVerified = user.email_verified_at 
                ? '<span style="color: #2e7d32; font-weight:600;"><i class="fa-solid fa-circle-check"></i> Verified</span>' 
                : '<span style="color: #ed6c02; font-weight:600;"><i class="fa-solid fa-clock"></i> Verification Sent</span>';

            const pinDisplay = user.role === 'cashier' 
                ? `<span style="font-family: monospace; background: #fef3c7; color: #b45309; font-weight: 800; padding: 2px 7px; border-radius: 6px; font-size: 0.85rem; margin-left: 6px; border: 1px solid #fde68a;">PIN: ${user.pin || 'None'}</span>`
                : '';

            const resetBtnText = user.role === 'cashier' ? 'Reset PIN' : 'Reset Password';

            tr.innerHTML = `
                <td><strong>${user.name}</strong></td>
                <td>${user.email}</td>
                <td><span class="role-badge role-${user.role}">${user.role}</span> ${pinDisplay}</td>
                <td>${isVerified}</td>
                <td>
                    <button class="action-btn" onclick="openChangePasswordModal(${user.id}, '${user.name.replace(/'/g, "\\'")}', '${user.role}', '${user.pin || ''}')">
                        <i class="fa-solid fa-key"></i> ${resetBtnText}
                    </button>
                    <button class="action-btn" onclick="deleteUser(${user.id}, '${user.name.replace(/'/g, "\\'")}')" style="color: #d32f2f; border-color: #ffcdd2; margin-left: 5px;">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Delete User
    window.deleteUser = async function(id, name) {
        const confirmed = await PosDialog.confirm({
            title: 'Delete Staff Account',
            message: `Are you sure you want to delete the account for ${name}? This action cannot be undone.`,
            icon: 'fa-user-xmark',
            iconType: 'danger',
            confirmText: 'Delete Account',
            cancelText: 'Cancel',
            confirmType: 'confirm-danger'
        });

        if (!confirmed) return;

        fetch(`${BASE}/api/users/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                PosDialog.alert({
                    title: 'Account Deleted',
                    message: 'Account deleted successfully.',
                    icon: 'fa-circle-check',
                    iconType: 'success'
                });
                loadAccounts();
            } else {
                PosDialog.alert({
                    title: 'Error',
                    message: 'Error deleting account: ' + (data.message || 'Unknown error'),
                    icon: 'fa-circle-exclamation',
                    iconType: 'danger'
                });
            }
        })
        .catch(err => {
            console.error(err);
            PosDialog.alert({
                title: 'Error',
                message: 'An error occurred while deleting the account.',
                icon: 'fa-circle-xmark',
                iconType: 'danger'
            });
        });
    };

    // Eye Icon Toggles
    document.querySelectorAll('.password-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const targetInput = targetId ? document.getElementById(targetId) : this.previousElementSibling;
            if (targetInput) {
                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    this.classList.remove('fa-eye');
                    this.classList.add('fa-eye-slash');
                    this.setAttribute('title', 'Hide password');
                } else {
                    targetInput.type = 'password';
                    this.classList.remove('fa-eye-slash');
                    this.classList.add('fa-eye');
                    this.setAttribute('title', 'Show password');
                }
            }
        });
    });

    // Password Match Indicators for Add Account Modal
    const accPwd = document.getElementById('accountPassword');
    const accPwdConfirm = document.getElementById('accountPasswordConfirm');
    const addAccHint = document.getElementById('addAccMatchHint');

    function checkAddAccountMatch() {
        if (!accPwd || !accPwdConfirm || !addAccHint) return;
        if (!accPwdConfirm.value) {
            addAccHint.textContent = '';
            return;
        }
        if (accPwd.value === accPwdConfirm.value) {
            addAccHint.innerHTML = '<span style="color:#2e7d32;"><i class="fa-solid fa-circle-check"></i> Passwords match</span>';
        } else {
            addAccHint.innerHTML = '<span style="color:#c62828;"><i class="fa-solid fa-circle-xmark"></i> Passwords do not match</span>';
        }
    }
    if (accPwd) accPwd.addEventListener('input', checkAddAccountMatch);
    if (accPwdConfirm) accPwdConfirm.addEventListener('input', checkAddAccountMatch);

    // Password Match Indicators for Change Password Modal
    const cpPwd = document.getElementById('cpNewPassword');
    const cpPwdConfirm = document.getElementById('cpNewPasswordConfirm');
    const cpMatchHint = document.getElementById('cpMatchHint');

    function checkCpMatch() {
        if (!cpPwd || !cpPwdConfirm || !cpMatchHint) return;
        if (!cpPwdConfirm.value) {
            cpMatchHint.textContent = '';
            return;
        }
        if (cpPwd.value === cpPwdConfirm.value) {
            cpMatchHint.innerHTML = '<span style="color:#2e7d32;"><i class="fa-solid fa-circle-check"></i> Passwords match</span>';
        } else {
            cpMatchHint.innerHTML = '<span style="color:#c62828;"><i class="fa-solid fa-circle-xmark"></i> Passwords do not match</span>';
        }
    }
    if (cpPwd) cpPwd.addEventListener('input', checkCpMatch);
    if (cpPwdConfirm) cpPwdConfirm.addEventListener('input', checkCpMatch);

    // Add Account Submit
    document.getElementById('addAccountForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const role = document.getElementById('accountRole').value;
        const name = document.getElementById('accountName').value.trim();
        const email = document.getElementById('accountEmail').value.trim();

        if (!email.toLowerCase().endsWith('@gmail.com')) {
            PosDialog.alert({
                title: 'Gmail Address Required',
                message: 'Please enter a valid Gmail address ending with @gmail.com.',
                icon: 'fa-envelope',
                iconType: 'warning'
            });
            return;
        }

        let payload = { name, email, role };

        if (role === 'cashier') {
            const pin = document.getElementById('accountPin').value.trim();
            if (!/^\d{6}$/.test(pin)) {
                PosDialog.alert({
                    title: 'Invalid PIN',
                    message: 'Cashier PIN must be exactly 6 numeric digits (e.g. 123456).',
                    icon: 'fa-triangle-exclamation',
                    iconType: 'danger'
                });
                return;
            }
            payload.pin = pin;
        } else {
            const pwd = document.getElementById('accountPassword').value;
            const pwdConfirm = document.getElementById('accountPasswordConfirm')?.value;

            if (pwdConfirm && pwd !== pwdConfirm) {
                PosDialog.alert({
                    title: 'Password Mismatch',
                    message: 'Password and Confirm Password do not match.',
                    icon: 'fa-triangle-exclamation',
                    iconType: 'danger'
                });
                return;
            }
            payload.password = pwd;
            payload.password_confirmation = pwdConfirm || pwd;
        }

        const btn = document.getElementById('saveAccountBtn');
        btn.textContent = 'Saving & Sending Link...';
        btn.disabled = true;

        fetch(`${BASE}/api/users`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeAddAccountModal();
                loadAccounts();
                PosDialog.alert({
                    title: 'Account Created',
                    message: data.message || 'Account created successfully! A verification link has been sent to the email address.',
                    icon: 'fa-user-check',
                    iconType: 'success'
                });
            } else {
                let errorMsg = data.message || 'Error creating account';
                if (data.errors && typeof data.errors === 'object') {
                    const firstErr = Object.values(data.errors).flat()[0];
                    if (firstErr) errorMsg = firstErr;
                }
                PosDialog.alert({
                    title: 'Registration Error',
                    message: errorMsg,
                    icon: 'fa-triangle-exclamation',
                    iconType: 'danger'
                });
            }
        })
        .catch(err => {
            console.error(err);
            PosDialog.alert({
                title: 'Server Error',
                message: 'An error occurred while connecting to server.',
                icon: 'fa-circle-xmark',
                iconType: 'danger'
            });
        })
        .finally(() => {
            btn.textContent = 'Create Account';
            btn.disabled = false;
        });
    });

    // Change Password / PIN Submit
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const id = document.getElementById('cpUserId').value;
        const role = document.getElementById('cpUserRole').value || 'cashier';
        let payload = {};

        if (role === 'cashier') {
            const pin = document.getElementById('cpNewPin').value.trim();
            if (!/^\d{6}$/.test(pin)) {
                PosDialog.alert({
                    title: 'Invalid PIN',
                    message: 'Cashier PIN must be exactly 6 numeric digits.',
                    icon: 'fa-triangle-exclamation',
                    iconType: 'danger'
                });
                return;
            }
            payload.pin = pin;
        } else {
            const pwd = document.getElementById('cpNewPassword').value;
            const pwdConfirm = document.getElementById('cpNewPasswordConfirm')?.value;

            if (pwdConfirm && pwd !== pwdConfirm) {
                PosDialog.alert({
                    title: 'Password Mismatch',
                    message: 'The new password and confirmation do not match.',
                    icon: 'fa-triangle-exclamation',
                    iconType: 'danger'
                });
                return;
            }
            payload.password = pwd;
        }

        const btn = document.getElementById('savePasswordBtn');
        btn.textContent = 'Updating...';
        btn.disabled = true;

        fetch(`${BASE}/api/users/${id}/password`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                PosDialog.alert({
                    title: 'Credentials Updated',
                    message: data.message || 'Credentials updated successfully!',
                    icon: 'fa-key',
                    iconType: 'success'
                });
                closeChangePasswordModal();
                loadAccounts();
            } else {
                PosDialog.alert({
                    title: 'Update Error',
                    message: 'Error updating credentials: ' + (data.message || JSON.stringify(data.errors)),
                    icon: 'fa-triangle-exclamation',
                    iconType: 'danger'
                });
            }
        })
        .catch(err => {
            console.error(err);
            PosDialog.alert({
                title: 'Error',
                message: 'An error occurred.',
                icon: 'fa-circle-xmark',
                iconType: 'danger'
            });
        })
        .finally(() => {
            btn.textContent = 'Save Changes';
            btn.disabled = false;
        });
    });

});
