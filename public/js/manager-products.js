const BASE = (function() {
    const pathname = window.location.pathname;
    const idx = pathname.toLowerCase().indexOf('/backend/public');
    return idx !== -1 ? pathname.substring(0, idx + '/backend/public'.length) : '';
})();

// ==========================================
// Product Management
// ==========================================
const modal = document.getElementById('productModal');
const form = document.getElementById('productForm');
const modalTitle = document.getElementById('modalTitle');

function openAddModal() {
    form.reset();
    document.getElementById('productId').value = '';
    modalTitle.innerText = 'Add Product';
    modal.style.display = 'flex';
}

function openEditModal(product) {
    form.reset();
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name;
    document.getElementById('productCategory').value = product.category;
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productDiscountedPrice').value = product.discounted_price || '';
    
    modalTitle.innerText = 'Edit Product';
    modal.style.display = 'flex';
}

function closeModal() {
    modal.style.display = 'none';
}

if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const id = document.getElementById('productId').value;
        
        let url = BASE + '/api/products';
        if (id) {
            url += '/' + id;
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-User-Id': localStorage.getItem('userId') || '',
                'X-User-Name': localStorage.getItem('userName') || '',
                'X-User-Role': localStorage.getItem('userRole') || ''
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                PosDialog.alert({
                    title: 'Product Save Error',
                    message: data.message || 'Error saving product details. Please check required fields.',
                    icon: 'fa-circle-exclamation',
                    iconType: 'danger'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            PosDialog.alert({
                title: 'Server Error',
                message: 'An error occurred while saving the product.',
                icon: 'fa-circle-xmark',
                iconType: 'danger'
            });
        });
    });
}

async function deleteProduct(id, name) {
    const confirmed = await PosDialog.confirm({
        title: 'Delete Menu Item',
        message: `Are you sure you want to delete "${name}" from the menu? This action cannot be undone.`,
        icon: 'fa-trash-can',
        iconType: 'danger',
        confirmText: 'Delete Item',
        cancelText: 'Cancel',
        confirmType: 'confirm-danger'
    });

    if (confirmed) {
        fetch(BASE + '/api/products/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-User-Id': localStorage.getItem('userId') || '',
                'X-User-Name': localStorage.getItem('userName') || '',
                'X-User-Role': localStorage.getItem('userRole') || ''
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                PosDialog.alert({
                    title: 'Delete Failed',
                    message: data.message || 'Failed to delete the product.',
                    icon: 'fa-circle-exclamation',
                    iconType: 'danger'
                });
            }
        })
        .catch(err => {
            console.error('Error deleting product:', err);
            PosDialog.alert({
                title: 'Server Error',
                message: 'An error occurred while communicating with the server.',
                icon: 'fa-circle-xmark',
                iconType: 'danger'
            });
        });
    }
}

// ==========================================
// Add-ons Management (Owner & Manager)
// ==========================================
const addonsModal = document.getElementById('addonsModal');
const addonsList = document.getElementById('addonsList');
const addonForm = document.getElementById('addonForm');
const addonIdInput = document.getElementById('addonId');
const addonNameInput = document.getElementById('addonName');
const addonPriceInput = document.getElementById('addonPrice');
const addonCategoryInput = document.getElementById('addonCategory');
const addonSubmitText = document.getElementById('addonSubmitText');
const cancelAddonEditBtn = document.getElementById('cancelAddonEditBtn');

function openAddonsModal() {
    if (addonsModal) {
        addonsModal.style.display = 'flex';
        resetAddonForm();
        fetchAddons();
    }
}

function closeAddonsModal() {
    if (addonsModal) {
        addonsModal.style.display = 'none';
    }
}

function resetAddonForm() {
    if (addonForm) {
        addonForm.reset();
        addonIdInput.value = '';
        if (addonCategoryInput) addonCategoryInput.value = 'food';
        addonSubmitText.textContent = 'Add';
        cancelAddonEditBtn.style.display = 'none';
        document.getElementById('saveAddonBtn').innerHTML = '<i class="fa-solid fa-plus"></i> <span id="addonSubmitText">Add</span>';
    }
}

function fetchAddons() {
    if (!addonsList) return;
    addonsList.innerHTML = '<div style="padding: 15px; text-align: center; color: #888;"><i class="fa-solid fa-spinner fa-spin"></i> Loading add-ons...</div>';
    
    fetch(BASE + '/api/addons')
        .then(res => res.json())
        .then(data => {
            addonsList.innerHTML = '';
            if (data.length === 0) {
                addonsList.innerHTML = '<div style="padding: 15px; text-align: center; color: #888;">No add-ons created yet.</div>';
                return;
            }

            data.forEach(addon => {
                const div = document.createElement('div');
                div.style.cssText = 'display:flex; justify-content:space-between; padding:10px 12px; border-bottom:1px solid #f0f0f0; align-items:center; transition:background 0.2s;';
                div.onmouseover = () => div.style.background = '#fcfbf9';
                div.onmouseout = () => div.style.background = 'transparent';

                const priceDisplay = parseFloat(addon.price) > 0 ? `₱ ${parseFloat(addon.price).toFixed(2)}` : '<span style="color:#2e7d32; font-weight:600;">Free</span>';
                
                let catBadge = '<span style="display:inline-block; font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:12px; background:#e0f2fe; color:#0369a1; margin-left:6px;">☕ Drinks</span>';
                if (addon.category === 'food') {
                    catBadge = '<span style="display:inline-block; font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:12px; background:#fef3c7; color:#b45309; margin-left:6px;">🍲 Food</span>';
                } else if (addon.category === 'all') {
                    catBadge = '<span style="display:inline-block; font-size:0.75rem; font-weight:700; padding:2px 8px; border-radius:12px; background:#f3f4f6; color:#4b5563; margin-left:6px;">🌐 All</span>';
                }

                // Safe JSON payload for inline edit
                const safeName = addon.name.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                const safeCat = addon.category || 'drinks';

                div.innerHTML = `
                    <div style="flex:1;">
                        <div style="display:flex; align-items:center; gap:4px;">
                            <strong style="color:#2c1a14; font-family:'Montserrat',sans-serif;">${addon.name}</strong>
                            ${catBadge}
                        </div>
                        <div style="font-size:0.85rem; color:#8d786c; font-family:'Poppins',sans-serif; margin-top:2px;">${priceDisplay}</div>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="button" onclick="editAddon(${addon.id}, '${safeName}', ${addon.price}, '${safeCat}')" title="Edit Add-on" style="background:#eadeca; color:#2c1a14; border:none; border-radius:6px; padding:6px 10px; cursor:pointer; font-size:0.85rem; transition:0.2s;">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button type="button" onclick="deleteAddon(${addon.id}, '${safeName}')" title="Deduct/Delete Add-on" style="background:#fce8e6; color:#c5221f; border:none; border-radius:6px; padding:6px 10px; cursor:pointer; font-size:0.85rem; transition:0.2s;">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                `;
                addonsList.appendChild(div);
            });
        })
        .catch(err => {
            console.error(err);
            addonsList.innerHTML = '<div style="padding: 15px; text-align: center; color: #c5221f;">Failed to load add-ons.</div>';
        });
}

function editAddon(id, name, price, category) {
    addonIdInput.value = id;
    addonNameInput.value = name;
    addonPriceInput.value = price;
    if (addonCategoryInput) addonCategoryInput.value = category || 'drinks';
    cancelAddonEditBtn.style.display = 'inline-block';
    document.getElementById('saveAddonBtn').innerHTML = '<i class="fa-solid fa-check"></i> <span id="addonSubmitText">Update</span>';
    addonNameInput.focus();
}

if (addonForm) {
    addonForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const id = addonIdInput.value;
        const name = addonNameInput.value.trim();
        const price = parseFloat(addonPriceInput.value) || 0;
        const category = addonCategoryInput ? addonCategoryInput.value : 'drinks';

        const url = id ? (BASE + '/api/addons/' + id) : (BASE + '/api/addons');

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name, price, category })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                resetAddonForm();
                fetchAddons();
                PosDialog.toast ? PosDialog.toast(data.message || 'Add-on saved successfully!') : null;
            } else {
                PosDialog.alert({
                    title: 'Add-on Error',
                    message: data.message || 'Could not save add-on.',
                    icon: 'fa-triangle-exclamation',
                    iconType: 'danger'
                });
            }
        })
        .catch(err => {
            console.error(err);
            PosDialog.alert({
                title: 'Error',
                message: 'An unexpected error occurred while saving add-on.',
                icon: 'fa-triangle-exclamation',
                iconType: 'danger'
            });
        });
    });
}

async function deleteAddon(id, name) {
    const confirmed = await PosDialog.confirm({
        title: 'Remove Add-on',
        message: `Are you sure you want to remove/deduct the add-on "${name}"?`,
        icon: 'fa-trash-can',
        iconType: 'warning',
        confirmText: 'Remove',
        cancelText: 'Cancel',
        confirmType: 'confirm-danger'
    });

    if (confirmed) {
        fetch(BASE + '/api/addons/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (addonIdInput.value == id) {
                    resetAddonForm();
                }
                fetchAddons();
            } else {
                PosDialog.alert({
                    title: 'Error',
                    message: data.message || 'Failed to remove add-on.',
                    icon: 'fa-triangle-exclamation',
                    iconType: 'danger'
                });
            }
        })
        .catch(err => {
            console.error(err);
            PosDialog.alert({
                title: 'Error',
                message: 'Network error deleting add-on.',
                icon: 'fa-triangle-exclamation',
                iconType: 'danger'
            });
        });
    }
}

// ==========================================
// Global Discounts Management
// ==========================================
const discountsModal = document.getElementById('discountsModal');
const discountsList = document.getElementById('discountsList');
const addDiscountForm = document.getElementById('addDiscountForm');

function openDiscountsModal() {
    discountsModal.style.display = 'flex';
    fetchDiscounts();
}

function closeDiscountsModal() {
    discountsModal.style.display = 'none';
}

function fetchDiscounts() {
    fetch(BASE + '/api/discounts')
        .then(res => res.json())
        .then(data => {
            discountsList.innerHTML = '';
            if (data.length === 0) {
                discountsList.innerHTML = '<div style="padding: 15px; text-align: center; color: #888;">No discounts configured yet.</div>';
                return;
            }
            data.forEach(d => {
                const div = document.createElement('div');
                div.style.cssText = 'display:flex; justify-content:space-between; padding:10px; border-bottom:1px solid #eee; align-items:center;';
                div.innerHTML = `
                    <div><strong>${d.name}</strong> (${d.percentage}%)</div>
                    <button onclick="deleteDiscount(${d.id})" style="background:#c5221f; color:#fff; border:none; border-radius:4px; padding:4px 8px; cursor:pointer;"><i class="fa-solid fa-trash"></i></button>
                `;
                discountsList.appendChild(div);
            });
        });
}

if (addDiscountForm) {
    addDiscountForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const name = document.getElementById('discountName').value;
        const percentage = document.getElementById('discountPercent').value;

        fetch(BASE + '/api/discounts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ name, percentage })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('discountName').value = '';
                document.getElementById('discountPercent').value = '';
                fetchDiscounts();
            } else {
                PosDialog.alert({
                    title: 'Discount Error',
                    message: 'Error adding discount.',
                    icon: 'fa-triangle-exclamation',
                    iconType: 'danger'
                });
            }
        });
    });
}

async function deleteDiscount(id) {
    const confirmed = await PosDialog.confirm({
        title: 'Delete Discount',
        message: 'Are you sure you want to delete this discount rate?',
        icon: 'fa-percent',
        iconType: 'warning',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        confirmType: 'confirm-danger'
    });

    if (confirmed) {
        fetch(BASE + '/api/discounts/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                fetchDiscounts();
            }
        });
    }
}

// ==========================================
// Void PIN Management (Exclusive for Owner & Manager)
// ==========================================
const voidPinModal = document.getElementById('voidPinModal');
const voidPinForm = document.getElementById('voidPinForm');
const newVoidPinInput = document.getElementById('newVoidPin');
const confirmVoidPinInput = document.getElementById('confirmVoidPin');
const pinValidationMsg = document.getElementById('pinValidationMsg');

function openVoidPinModal() {
    if (voidPinModal) {
        voidPinModal.style.display = 'flex';
        if (voidPinForm) voidPinForm.reset();
        if (pinValidationMsg) pinValidationMsg.style.display = 'none';
        resetPinEye('newVoidPin', 'eyeIconNew');
        resetPinEye('confirmVoidPin', 'eyeIconConfirm');
    }
}

function closeVoidPinModal() {
    if (voidPinModal) {
        voidPinModal.style.display = 'none';
        if (voidPinForm) voidPinForm.reset();
        if (pinValidationMsg) pinValidationMsg.style.display = 'none';
    }
}

function togglePinEye(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function resetPinEye(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input) input.type = 'password';
    if (icon) {
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

if (confirmVoidPinInput && newVoidPinInput) {
    function checkPinMatch() {
        const pin = newVoidPinInput.value;
        const confirm = confirmVoidPinInput.value;
        if (!confirm) {
            pinValidationMsg.style.display = 'none';
            return;
        }
        if (pin === confirm) {
            pinValidationMsg.style.display = 'block';
            pinValidationMsg.style.color = '#2e7d32';
            pinValidationMsg.innerHTML = '<i class="fa-solid fa-circle-check"></i> PINs match';
        } else {
            pinValidationMsg.style.display = 'block';
            pinValidationMsg.style.color = '#c5221f';
            pinValidationMsg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> PINs do not match';
        }
    }

    newVoidPinInput.addEventListener('input', checkPinMatch);
    confirmVoidPinInput.addEventListener('input', checkPinMatch);
}

if (voidPinForm) {
    voidPinForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const pin = newVoidPinInput.value.trim();
        const pinConfirmation = confirmVoidPinInput.value.trim();
        const btn = document.getElementById('saveVoidPinBtn');

        if (!/^\d{4}$/.test(pin)) {
            PosDialog.alert({
                title: 'Invalid PIN',
                message: 'Void PIN must be exactly 4 numeric digits.',
                icon: 'fa-key',
                iconType: 'warning'
            });
            return;
        }

        if (pin !== pinConfirmation) {
            PosDialog.alert({
                title: 'PIN Mismatch',
                message: 'The confirmation PIN does not match the new PIN.',
                icon: 'fa-triangle-exclamation',
                iconType: 'danger'
            });
            return;
        }

        btn.textContent = 'Saving...';
        btn.disabled = true;

        try {
            const res = await fetch(BASE + '/api/void-pin/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-User-Role': (localStorage.getItem('userRole') || '').toLowerCase()
                },
                body: JSON.stringify({
                    pin: pin,
                    pin_confirmation: pinConfirmation
                })
            });

            const data = await res.json();
            if (data.success) {
                closeVoidPinModal();
                PosDialog.alert({
                    title: 'Void PIN Updated',
                    message: data.message || 'Void PIN successfully updated!',
                    icon: 'fa-circle-check',
                    iconType: 'success'
                });
            } else {
                PosDialog.alert({
                    title: 'Failed to Update PIN',
                    message: data.message || 'Could not update Void PIN.',
                    icon: 'fa-triangle-exclamation',
                    iconType: 'danger'
                });
            }
        } catch (err) {
            console.error(err);
            PosDialog.alert({
                title: 'Error',
                message: 'A network error occurred while updating the Void PIN.',
                icon: 'fa-triangle-exclamation',
                iconType: 'danger'
            });
        } finally {
            btn.textContent = 'Save Void PIN';
            btn.disabled = false;
        }
    });
}
