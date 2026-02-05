// ************************************************************************** //
//     File: src\frontend\js\customer.js                                      //
//     Author: atucci <atucci@student.42.fr>                                  //
//     Created: 2026/02/04 12:17:20                                           //
//     Updated: 2026/02/04 12:17:24                                           //
//     System: unknown [SurfaceLaptopmy]                                      //
//     Hardware: unknown | RAM: Unknown                                       //
// ************************************************************************** //

/* -------------------------------------------------------------------------- */
/* RENDER: CUSTOMER DASHBOARD                                                 */
/* -------------------------------------------------------------------------- */

function renderCustomerDashboard(container) {
    // 1. Header & Navigation
    const headerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3 sticky-top bg-white py-2" style="z-index: 1000;">
            <div>
                <h2 class="mb-0 fw-bold">👋 Hungry?</h2>
                <button class="btn btn-link p-0 text-decoration-none" onclick="renderCustomerDashboard(document.getElementById('dashboardContent'))" id="backToRestaurantsBtn" style="display:none">
                    <i class="fas fa-arrow-left me-1"></i> Back to Restaurants
                </button>
            </div>
            <button class="btn btn-outline-danger btn-sm" onclick="logout()">Logout</button>
        </div>

        <ul class="nav nav-pills mb-4" id="customerTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4" 
                        id="restaurants-tab" 
                        data-bs-toggle="pill" 
                        data-bs-target="#pills-restaurants" 
                        type="button" 
                        role="tab">
                    <i class="fas fa-utensils me-2"></i>Restaurants
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" 
                        id="orders-tab" 
                        data-bs-toggle="pill" 
                        data-bs-target="#pills-orders" 
                        type="button" 
                        role="tab"
                        onclick="loadOrderHistory()"> <i class="fas fa-receipt me-2"></i>My Orders
                </button>
            </li>
        </ul>
    `;

    // 2. Tab Content Areas
    const contentHTML = `
        <div class="tab-content" id="pills-tabContent">
            
            <div class="tab-pane fade show active" id="pills-restaurants" role="tabpanel">
                <div id="customerContentArea">
                    <div id="restaurantGrid" class="row g-3">
                        <div class="col-12 text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-muted">Finding good food near you...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pills-orders" role="tabpanel">
                <div class="text-center py-5 text-muted">Loading orders...</div>
            </div>
        </div>

        <div id="floatingCartContainer"></div>
    `;

    container.innerHTML = headerHTML + contentHTML;
    
    // 3. Initialize Listeners & Load Data
    document.removeEventListener('cartUpdated', updateCartUI);
    document.addEventListener('cartUpdated', updateCartUI);

    loadFoodPlaces(); // Load restaurants into Tab 1
    updateCartUI();   // Check for existing cart items
}

/* -------------------------------------------------------------------------- */
/* LOGIC: LOAD RESTAURANTS                                                    */
/* -------------------------------------------------------------------------- */
async function loadFoodPlaces() {
    const grid = document.getElementById('restaurantGrid');
    document.getElementById('backToRestaurantsBtn').style.display = 'none'; // Hide back button
    
    try {
        const response = await fetch('https://localhost/A_project_forUniversity/src/api/food_place/read.php');
        const json = await response.json();
        const places = json.data || [];

        grid.innerHTML = '';

        if (places.length === 0) {
            grid.innerHTML = `<div class="col-12 text-center py-5"><h3>😔 No restaurants found.</h3></div>`;
            return;
        }

        places.forEach(place => {
            const cardHTML = `
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm hover-card" onclick="viewRestaurantMenu(${place.id}, '${place.name}')" style="cursor: pointer;">
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 120px; border-radius: 8px 8px 0 0;">
                            <i class="fas fa-store fa-3x text-muted opacity-25"></i>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-1">${place.name}</h5>
                            <p class="small text-muted mb-0"><i class="fas fa-utensils me-1"></i> ${place.type || 'General'}</p>
                            <p class="small text-muted"><i class="fas fa-map-marker-alt me-1"></i> ${place.location}</p>
                        </div>
                    </div>
                </div>`;
            grid.innerHTML += cardHTML;
        });

    } catch (error) {
        console.error("Error:", error);
    }
}

/* -------------------------------------------------------------------------- */
/* LOGIC: VIEW MENU (The Ordering Interface)                                  */
/* -------------------------------------------------------------------------- */
async function viewRestaurantMenu(id, name)
{
    const container = document.getElementById('customerContentArea');
    document.getElementById('backToRestaurantsBtn').style.display = 'inline-block'; // Show back button

	container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <p>Loading menu for ${name}...</p>
        </div>`;

    try {
        const res = await fetch(`https://localhost/A_project_forUniversity/src/api/menu_item/read_by_place.php?id=${id}`);
        const json = await res.json();
        const items = json.data || [];

        container.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="fw-bold">${name}</h3>
                <span class="badge bg-light text-dark border">${items.length} Items</span>
            </div>
            <div class="row g-3" id="menuGrid"></div>
        `;

        const menuGrid = document.getElementById('menuGrid');

        if (items.length === 0) {
            menuGrid.innerHTML = `<div class="col-12 text-center text-muted py-5">This restaurant has no menu items yet.</div>`;
            return;
        }

        items.forEach(item => {
            // Only show available items
            if (!item.is_available) return;

            const cardHTML = `
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">${item.name}</h6>
                                <p class="small text-muted mb-2 text-truncate-2">${item.description || ''}</p>
                                <span class="fw-bold text-primary">€${item.price}</span>
                            </div>
                            <button class="btn btn-outline-primary btn-sm rounded-circle" 
                                    style="width: 32px; height: 32px; padding: 0;"
                                    onclick="addToCart('${item.id}', '${item.name}', ${item.price}, ${id}, '${name}')">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
            menuGrid.innerHTML += cardHTML;
        });

    }
	catch (error)
	{
        container.innerHTML = `<div class="alert alert-danger">Failed to load menu.</div>`;
    }
}

/* -------------------------------------------------------------------------- */
/* CART LOGIC: UI UPDATES & CHECKOUT                                          */
/* -------------------------------------------------------------------------- */

// 1. Bridge between HTML OnClick and CartManager
function addToCart(itemId, itemName, itemPrice, restId, restName) {
    const item = { id: itemId, name: itemName, price: itemPrice };
    const success = cartManager.add(item, restId, restName);
    
    if (success) {
        // Optional: Simple toast or animation could go here
        console.log("Added to cart");
    }
}


/* -------------------------------------------------------------------------- */
/* CART LOGIC: UI UPDATES & CHECKOUT                                          */
/* -------------------------------------------------------------------------- */

// Global Cache for addresses (so we don't fetch them on every render)
let customerAddresses = [];

/**
 * 1. updateCartUI
 * Renders the Floating "View Cart" Button and the empty Modal Shell.
 * This runs every time an item is added/removed.
 */
function updateCartUI() {
    const cart = cartManager.get();
    const container = document.getElementById('floatingCartContainer');
    
    // Guard clause: If we aren't on the customer view, stop.
    if (!container) return;

    // A. Hide if empty
    if (cart.items.length === 0) {
        container.innerHTML = '';
        return;
    }

    // B. Calculate Totals
    const totalQty = cart.items.reduce((sum, i) => sum + i.quantity, 0);
    const totalPrice = cartManager.getTotal().toFixed(2);

    // C. Render Shell
    // We render the Modal *Shell* here so it exists in the DOM, 
    // but we leave the body empty until the user clicks the button.
    container.innerHTML = `
        <div class="fixed-bottom p-3" style="z-index: 1050;">
            <button class="btn btn-primary w-100 shadow-lg rounded-pill d-flex justify-content-between align-items-center py-3 px-4"
                    onclick="openCheckoutModal()">
                <span class="badge bg-white text-primary rounded-pill">${totalQty}</span>
                <span class="fw-bold">View Cart</span>
                <span class="fw-bold">€${totalPrice}</span>
            </button>
        </div>
        
        <div class="modal fade" id="checkoutModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Checkout</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-0">
                        </div>
                    <div class="modal-footer border-0">
                        </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * 2. openCheckoutModal
 * Triggered when user clicks the floating button.
 * Fetches addresses -> Decides what to show.
 */
async function openCheckoutModal() {
    const modalEl = document.getElementById('checkoutModal');
    const modalBody = modalEl.querySelector('.modal-body');
    const modal = new bootstrap.Modal(modalEl);

    // Show loading state while we check the database
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary"></div>
            <p class="mt-2 text-muted">Checking delivery options...</p>
        </div>`;
    
    modal.show();

    // Fetch Addresses from API
    try {
        const res = await fetch(`https://localhost/A_project_forUniversity/src/api/address/read_by_customer.php?id=${currentState.entityId}`);
        const data = await res.json();
        customerAddresses = data.data || []; 
    } catch (e) {
        console.warn("Address fetch failed", e);
        customerAddresses = [];
    }

    // Render the correct view (Address Form OR Order Summary)
    renderCheckoutContent(modalEl);
}

/**
 * 3. renderCheckoutContent
 * The "Brain" of the modal. Swaps between "Add Address" and "Place Order".
 */
function renderCheckoutContent(modalEl) {
    const cart = cartManager.get();
    const totalPrice = cartManager.getTotal().toFixed(2);
    
    const body = modalEl.querySelector('.modal-body');
    const footer = modalEl.querySelector('.modal-footer');

    // --- SCENARIO A: NO ADDRESS (Show Creation Form) ---
    if (customerAddresses.length === 0) {
        footer.style.display = 'none'; // Hide checkout buttons
        body.innerHTML = `
            <div class="alert alert-warning border-0 d-flex align-items-center">
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
                <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill">Save & Continue</button>
            </form>
        `;
        return;
    }

    // --- SCENARIO B: ADDRESS EXISTS (Show Order Summary) ---
    footer.style.display = 'flex';
    
    // Create dropdown options
    const addressOptions = customerAddresses.map(addr => 
        `<option value="${addr.id}">${addr.address_line1}, ${addr.city}</option>`
    ).join('');

    body.innerHTML = `
        <h6 class="text-muted small text-uppercase mt-3">From: ${cart.restaurantName}</h6>
        
        <ul class="list-group list-group-flush mb-4">
            ${cart.items.map(item => `
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <span class="fw-bold text-primary">${item.quantity}x</span> ${item.name}
                        <div class="small text-muted">€${item.price}</div>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary" 
                                onclick="cartManager.remove('${item.id}'); renderCheckoutContent(document.getElementById('checkoutModal'))">
                            <i class="fas fa-minus small"></i>
                        </button>
                        <button class="btn btn-outline-primary" 
                                onclick="addToCart('${item.id}', '', ${item.price}, ${cart.restaurantId}, ''); renderCheckoutContent(document.getElementById('checkoutModal'))">
                            <i class="fas fa-plus small"></i>
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

        <div class="d-flex justify-content-between border-top pt-3">
            <span class="h5 mb-0">Total</span>
            <span class="h5 mb-0 fw-bold text-success">€${totalPrice}</span>
        </div>
    `;
    
    // Footer Actions
    footer.innerHTML = `
        <button class="btn btn-link text-danger text-decoration-none" 
                onclick="cartManager.clear(); bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();">
            Clear Cart
        </button>
        <button class="btn btn-success w-100 rounded-pill py-2 shadow-sm" onclick="placeOrder()">
            Place Order <i class="fas fa-check ms-2"></i>
        </button>
    `;
}

/**
 * 4. handleCreateAddress
 * Handles the submission of the "New Address" form inside the modal.
 */
async function handleCreateAddress(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());

    // Required by your PHP API Transaction Logic
    payload.customer_id = currentState.entityId;
    
    // Defaults for optional fields
    payload.address_line2 = ''; 
    payload.region = ''; 
    payload.unit_number = '';
    payload.street_number = '0'; 

    try {
        const res = await fetch('https://localhost/A_project_forUniversity/src/api/address/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        if (res.ok) {
            // If successful, reload the modal. 
            // openCheckoutModal will re-fetch addresses, find the new one, and show Scenario B.
            openCheckoutModal(); 
        } else {
            const err = await res.json();
            alert("Address creation failed: " + (err.message || "Unknown Error"));
        }
    } catch (e) {
        console.error(e);
        alert("Network Error");
    }
}





/* -------------------------------------------------------------------------- */
/* LOGIC: SEND ORDER TO API                                                   */
/* -------------------------------------------------------------------------- */
async function placeOrder() {
    const cart = cartManager.get();
    if (cart.items.length === 0) return;

    // Prepare Payload
    // PHP expects: customer_id, food_place_id, customer_address_id, items array
    
    // WARNING: We are using a Hardcoded Address ID (1) because we haven't built Address Management yet.
    // If you delete address ID 1 from DB, this will fail.
    const addressId = document.getElementById('deliveryAddressSelect').value;
	console.log(`Dumb mfs this that ${currentState.entityId} is not null`);
    const payload = {
        customer_id: currentState.entityId, // The Customer ID we captured during login
        food_place_id: cart.restaurantId,
        customer_address_id: addressId, 
        items: cart.items.map(i => ({
            menu_item_id: i.id,
            quantity: i.quantity,
            price_at_order: i.price // Sending price just in case, though backend might calculate it
        })),
        requested_delivery_time: new Date(new Date().getTime() + 45*60000).toISOString().slice(0, 19).replace('T', ' ') // +45 mins from now
    };

    console.log("🚀 Sending Order:", payload);

    try
	{
        const response = await fetch('https://localhost/A_project_forUniversity/src/api/food_order/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (!response.ok)
			throw new Error(data.message);

		// 1. HIDE MODAL FIRST (Before destroying it)
		const modalEl = document.getElementById('checkoutModal');
		const modalInstance = bootstrap.Modal.getInstance(modalEl);
		if (modalInstance)
		{
			modalInstance.hide();
		}

        // SUCCESS
        cartManager.clear(); // Wipe LocalStorage
        
        alert(`✅ Order Placed! Total Charged: €${data.total_charged}`);
        
        // Optional: Switch to "Orders" tab
        // document.getElementById('orders-tab').click();

    }
	catch (error)
	{
		console.log(error.message);
        alert("DEBUG: " + error.message);
		console.log(payload);
    }
}