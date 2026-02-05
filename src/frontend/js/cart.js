// ************************************************************************** //
//     File: src/frontend/js/cart.js                                          //
//     Author: atucci <atucci@student.42.fr>                                  //
//     Created: 2026/02/05 21:18:46                                           //
//     Updated: 2026/02/05 21:41:29                                           //
//     System: Linux [atucci-Surface-Laptop-3]                                //
//     Hardware: Intel Core i5-1035G7 | RAM: 7GB                              //
// ************************************************************************** //


// Local cache for addresses to avoid re-fetching constantly within the same session
let customerAddresses = [];

/* -------------------------------------------------------------------------- */
/* 1. OPEN CHECKOUT MODAL (With URL Update)                                   */
/* -------------------------------------------------------------------------- */
async function openCheckoutModal() {
    const modalEl = document.getElementById('checkoutModal');
    if (!modalEl) return;

    const modalBody = modalEl.querySelector('.modal-body');
    const modal = new bootstrap.Modal(modalEl);

    // 1. UPDATE URL to /cart
    // We don't use switchView because we don't want to hide the dashboard.
    // We just want to 'fake' the URL.
    const newUrl = '/cart';
    window.history.pushState({ modal: 'cart' }, '', newUrl);
    
    // 2. Setup listeners to handle closing/back button (Run once)
    setupCartModalListeners(modalEl);

    // Show loading state
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2 text-muted">Checking delivery options...</p>
        </div>`;
    
    modal.show();

    // Fetch Addresses
    try {
        if (!currentState.entityId) throw new Error("User not identified");
        const res = await fetch(`http://localhost:8000/api/address/read_by_customer.php?id=${currentState.entityId}`);
        const data = await res.json();
        customerAddresses = data.data || []; 
    } catch (e) {
        console.warn("Address fetch warning:", e);
        customerAddresses = [];
    }

    renderCheckoutContent(modalEl);
}

/**
 * NEW HELPER: Handles URL history when closing the modal
 */
function setupCartModalListeners(modalEl) {
    // Prevent adding duplicate listeners
    if (modalEl.dataset.hasListeners) return;

    // A. When the modal is fully hidden (clicked X or clicked outside)
    modalEl.addEventListener('hidden.bs.modal', () => {
        // If the URL is still /cart, go back to /view-dashboard
        if (window.location.pathname === '/cart') {
            // Go back in history (restores /view-dashboard)
            window.history.back(); 
        }
    });

    // B. Handle Browser "Back" Button
    window.addEventListener('popstate', (event) => {
        // If user clicks Back, and we are no longer on /cart, close the modal
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance && window.location.pathname !== '/cart') {
            modalInstance.hide();
        }
    });

    modalEl.dataset.hasListeners = "true";
}

/* -------------------------------------------------------------------------- */
/* 2. RENDER CONTENT (Swap between Address Form vs Order Summary)             */
/* -------------------------------------------------------------------------- */
function renderCheckoutContent(modalEl) {
    const cart = cartManager.get();
    const totalPrice = cartManager.getTotal().toFixed(2);

    const body = modalEl.querySelector('.modal-body');
    const footer = modalEl.querySelector('.modal-footer');

    // SCENARIO A: CART IS EMPTY (Edge case)
    if (cart.items.length === 0) {
        body.innerHTML = '<div class="text-center py-4 text-muted">Your cart is empty.</div>';
        footer.style.display = 'none';
        return;
    }

    // SCENARIO B: NO ADDRESS FOUND -> SHOW CREATE FORM
    if (customerAddresses.length === 0) {
        footer.style.display = 'none'; // Hide checkout buttons
        body.innerHTML = `
            <div class="alert alert-warning border-0 d-flex align-items-center mb-4">
                <i class="fas fa-map-marker-alt me-3 fa-2x"></i>
                <div>
                    <strong>No Address Found</strong><br>
                    Please add a delivery address to continue.
                </div>
            </div>

            <form id="newAddressForm" onsubmit="handleCreateAddress(event)">
                <h6 class="fw-bold mb-3">New Address Details</h6>
                <div class="mb-3">
                    <input type="text" class="form-control" name="address_line1" placeholder="Street Address (e.g. Via Roma 10)" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <input type="text" class="form-control" name="city" placeholder="City" required>
                    </div>
                    <div class="col-6">
                        <input type="text" class="form-control" name="postal_code" placeholder="Postal Code" required>
                    </div>
                </div>
                <input type="hidden" name="country_id" value="1">
                <div id="addressErrorArea" class="text-danger small mb-2"></div>
                <button type="submit" id="saveAddressBtn" class="btn btn-primary w-100 py-2 rounded-pill">
                    Save & Continue
                </button>
            </form>
        `;
        return;
    }

    // SCENARIO C: READY TO CHECKOUT
    footer.style.display = 'flex';

    // Address Dropdown
    const addressOptions = customerAddresses.map(addr =>
        `<option value="${addr.id}">${addr.address_line1}, ${addr.city}</option>`
    ).join('');

    body.innerHTML = `
        <h6 class="text-muted small text-uppercase mt-2">Restaurant: <b>${cart.restaurantName}</b></h6>

        <ul class="list-group list-group-flush mb-4 border-top border-bottom">
            ${cart.items.map(item => `
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                    <div>
                        <span class="fw-bold text-primary me-2">${item.quantity}x</span> ${item.name}
                        <div class="small text-muted">€${item.price}</div>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary"
                                onclick="cartManager.remove('${item.id}'); renderCheckoutContent(document.getElementById('checkoutModal'))">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button class="btn btn-outline-primary"
                                onclick="addToCart('${item.id}', '', ${item.price}, ${cart.restaurantId}, ''); renderCheckoutContent(document.getElementById('checkoutModal'))">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </li>
            `).join('')}
        </ul>

        <div class="bg-light p-3 rounded mb-3">
            <label class="form-label small fw-bold text-muted text-uppercase">Delivering To</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-home"></i></span>
                <select class="form-select border-start-0 ps-0" id="deliveryAddressSelect">
                    ${addressOptions}
                </select>
            </div>
            <button class="btn btn-link btn-sm p-0 mt-2 text-decoration-none"
                    onclick="customerAddresses=[]; renderCheckoutContent(document.getElementById('checkoutModal'))">
                + Add different address
            </button>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <span class="h5 mb-0">Total</span>
            <span class="h4 mb-0 fw-bold text-success">€${totalPrice}</span>
        </div>
        <div id="checkoutErrorArea" class="text-danger small mt-2 text-end"></div>
    `;

    // Footer Buttons
    footer.innerHTML = `
        <button class="btn btn-link text-danger text-decoration-none"
                onclick="cartManager.clear(); bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();">
            Clear Cart
        </button>
        <button class="btn btn-success w-100 rounded-pill py-2 shadow-sm" id="placeOrderBtn" onclick="placeOrder()">
            Place Order <i class="fas fa-arrow-right ms-2"></i>
        </button>
    `;
}

/* -------------------------------------------------------------------------- */
/* 3. HANDLE ADDRESS CREATION (Async)                                         */
/* -------------------------------------------------------------------------- */
async function handleCreateAddress(event) {
    event.preventDefault();

    const form = event.target;
    const btn = document.getElementById('saveAddressBtn');
    const errorArea = document.getElementById('addressErrorArea');

    // UI Loading State
    const originalBtnText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
    errorArea.innerText = '';

    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());

    // Validation / Defaults
    payload.customer_id = currentState.entityId;
    payload.address_line2 = '';
    payload.region = '';
    payload.unit_number = '';
    payload.street_number = '0';

    try {
        const res = await fetch('http://localhost:8000/api/address/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (res.ok) {
            // Success: Reload modal logic (will auto-detect address and show Scenario C)
            openCheckoutModal();
        } else {
            throw new Error(data.message || "Failed to save address");
        }
    } catch (e) {
        errorArea.innerText = "Error: " + e.message;
        btn.disabled = false;
        btn.innerHTML = originalBtnText;
    }
}

/* -------------------------------------------------------------------------- */
/* 4. PLACE ORDER (Async & Redirection)                                       */
/* -------------------------------------------------------------------------- */
async function placeOrder() {
    const cart = cartManager.get();
    if (cart.items.length === 0) return;

    const btn = document.getElementById('placeOrderBtn');
    const errorArea = document.getElementById('checkoutErrorArea');
    const addressId = document.getElementById('deliveryAddressSelect').value;

    // UI Loading State
    const originalBtnText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    if(errorArea) errorArea.innerText = '';

    // Construct Payload
    const payload = {
        customer_id: currentState.entityId,
        food_place_id: cart.restaurantId,
        customer_address_id: addressId, 
        items: cart.items.map(i => ({
            menu_item_id: i.id,
            quantity: i.quantity,
            price_at_order: i.price
        })),
        requested_delivery_time: new Date(new Date().getTime() + 45*60000).toISOString().slice(0, 19).replace('T', ' ') 
    };

    console.log("🚀 Placing Order:", payload);

    try {
        const response = await fetch('http://localhost:8000/api/food_order/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok) throw new Error(data.message || "Order creation failed");

        // --- SUCCESS SEQUENCE ---
        console.log("✅ Order success. Redirecting to Dashboard...");

        // 1. Clear Data & Modal
        cartManager.clear(); 
        const modalEl = document.getElementById('checkoutModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        // 2. SWITCH VIEW (The Step You Requested)
        // This hides the cart/landing page and shows the main app view
        switchView('view-dashboard');

        // 3. RENDER DASHBOARD
        // We target the content area inside the dashboard view
        const dashboardContainer = document.getElementById('dashboardContent');
        if (dashboardContainer) {
            // Re-draw the dashboard to ensure tabs exist
            renderCustomerDashboard(dashboardContainer);
            
            // 4. SWITCH TO ORDERS TAB
            // We use a tiny timeout to ensure the DOM has finished rendering the tabs
            setTimeout(() => {
                const ordersTabBtn = document.getElementById('orders-tab');
                if (ordersTabBtn) {
                    // This creates a click event which does two things:
                    // A. Activates the Bootstrap Tab
                    // B. Triggers the onclick="loadOrderHistory()" we defined in customer.js
                    ordersTabBtn.click();
                }
            }, 50);
        }

        // 5. Scroll top
        window.scrollTo(0,0);

    } catch (error) {
        console.error("Order Error:", error);
        if(errorArea) errorArea.innerText = "Error: " + error.message;
        
        // Reset button so they can try again
        btn.disabled = false;
        btn.innerHTML = originalBtnText;
    }
}