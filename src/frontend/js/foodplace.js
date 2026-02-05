// ************************************************************************** //
//     File: src\frontend\js\foodplace.js                                     //
//     Author: atucci <atucci@student.42.fr>                                  //
//     Created: 2026/02/04 16:39:22                                           //
//     Updated: 2026/02/05 15:49:02                                           //
//     System: unknown [SurfaceLaptopmy]                                      //
//     Hardware: unknown | RAM: Unknown                                       //
// ************************************************************************** //

/* -------------------------------------------------------------------------- */
/* RENDER: RESTAURANT DASHBOARD (Tabs: Orders | Menu)                         */
/* -------------------------------------------------------------------------- */
function renderRestaurantDashboard(container) {
    
    // 1. HEADER
    const headerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0 fw-bold"><i class="fas fa-store me-2 text-primary"></i>Dashboard</h2>
                <p class="text-muted small mb-0">Manage orders and menu</p>
            </div>
            <button class="btn btn-outline-danger btn-sm" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    `;

    // 2. TABS & CONTENT
    const bodyHTML = `
        <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4" id="pills-orders-tab" data-bs-toggle="pill" data-bs-target="#pills-orders" type="button" role="tab" onclick="loadIncomingOrders()">
                    <i class="fas fa-bell me-2"></i>Incoming Orders
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="pills-menu-tab" data-bs-toggle="pill" data-bs-target="#pills-menu" type="button" role="tab" onclick="loadMenuItems()">
                    <i class="fas fa-utensils me-2"></i>My Menu
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            
            <div class="tab-pane fade show active" id="pills-orders" role="tabpanel">
                <div id="kitchenGrid" class="row g-3">
                    <div class="col-12 text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2 text-muted">Checking for hungry customers...</p>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pills-menu" role="tabpanel">
                <div class="d-flex justify-content-end mb-3">
                     <button class="btn btn-primary shadow-sm" onclick="openAddModal()">
                        <i class="fas fa-plus me-2"></i>Add Item
                    </button>
                </div>
                <div id="menuContainer" class="row g-3"></div>
            </div>
        </div>

        <div class="modal fade" id="addMenuModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="modalTitle">New Dish</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addMenuForm" onsubmit="handleMenuFormSubmit(event)">
                            <input type="hidden" name="id" id="menuItemId">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-uppercase text-muted">Dish Name</label>
                                <input type="text" class="form-control form-control-lg" name="name" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-uppercase text-muted">Price (€)</label>
                                    <input type="number" step="0.01" class="form-control" name="price" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-uppercase text-muted">Category</label>
                                    <select class="form-select" name="category">
                                        <option value="Main">Main</option>
                                        <option value="Starter">Starter</option>
                                        <option value="Dessert">Dessert</option>
                                        <option value="Drink">Drink</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-uppercase text-muted">Description</label>
                                <textarea class="form-control" name="description" rows="2"></textarea>
                            </div>
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" name="is_available" id="isAvailableCheck" checked>
                                <label class="form-check-label" for="isAvailableCheck">Available for order immediately</label>
                            </div>
                            <button type="submit" id="modalSubmitBtn" class="btn btn-primary w-100 py-2 rounded-pill">Add to Menu</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.innerHTML = headerHTML + bodyHTML;

    // Load Default View
    loadIncomingOrders();
}

/* -------------------------------------------------------------------------- */
/* LOGIC: LOAD INCOMING ORDERS (Uses the new API)                             */
/* -------------------------------------------------------------------------- */
async function loadIncomingOrders() {
    const grid = document.getElementById('kitchenGrid');
    
    try {
        const res = await fetch(`http://localhost:8000/api/food_order/read_by_place.php?id=${currentState.entityId}`);
        const json = await res.json();
        const orders = json.data || [];

        grid.innerHTML = '';

        if (orders.length === 0) {
            grid.innerHTML = `
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-25"></i>
                    <h4>All caught up!</h4>
                    <p>No orders pending action.</p>
                </div>`;
            return;
        }

		orders.forEach(order => {
            // Status Logic: 1=Pending (Needs Accept), 2=Cooking (Needs Driver)
            let actionBtn = '';
            let borderClass = '';

            // STATUS 1: PENDING -> Transition to 2 (Cooking)
            if (order.order_status_id == 1) {
                borderClass = 'border-warning border-3';
                // FIXED: Passing order.id to the function
                actionBtn = `<button class="btn btn-success w-100 fw-bold" onclick="acceptOrder(${order.id})">
                                <i class="fas fa-fire me-2"></i>Accept & Cook
                             </button>`;
            } 
            // STATUS 2: COOKING -> Transition to 3 (Ready/Delivering)
            else if (order.order_status_id == 2) {
                borderClass = 'border-info border-3';
                // FIXED: Passing order.id to the function
                actionBtn = `<button class="btn btn-primary w-100 fw-bold" onclick="deliverOrder(${order.id})">
                                <i class="fas fa-check me-2"></i>Ready for Driver
                             </button>`;
            }

            const html = `
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100 ${borderClass}">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <strong>#${order.id}</strong>
                            <span class="badge bg-light text-dark border">${order.status_name}</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title mb-1">${order.first_name} ${order.last_name}</h5>
                            <div class="text-muted small mb-3">
                                <i class="far fa-clock me-1"></i> ${new Date(order.order_datetime).toLocaleTimeString()}
                            </div>
                            
                            <ul class="list-group list-group-flush mb-3 small">
                                ${order.items.map(i => `
                                    <li class="list-group-item px-0 d-flex justify-content-between">
                                        <span><span class="fw-bold">${i.quantity}x</span> ${i.item_name}</span>
                                    </li>
                                `).join('')}
                            </ul>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3 pt-2 border-top">
                                <span class="text-muted small">Total</span>
                                <span class="fw-bold fs-5">€${order.total_amount}</span>
                            </div>
                            ${actionBtn}
                        </div>
                    </div>
                </div>`;
            grid.innerHTML += html;
        });

    } catch (e) {
        console.error(e);
        grid.innerHTML = '<div class="alert alert-danger">Error loading orders.</div>';
    }
}

/* -------------------------------------------------------------------------- */
/* LOGIC: ORDER STATE MANAGEMENT (SAGA STEP 2 & 3)                            */
/* -------------------------------------------------------------------------- */

// Shared Helper: Sends the PUT request to the API
async function updateOrderStatus(orderId, newStatusId) {
    // 1. Find the button that was clicked to show a loading state (Optional UX polish)
    // Since we don't pass the event, we can skip this or use a global loader.
    
    const payload = {
        id: orderId,
        status_id: newStatusId
    };

    console.log(`DEBUG: Updating Order #${orderId} to Status ${newStatusId}...`);

    try {
        const res = await fetch('http://localhost:8000/api/food_order/update_status.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        // 2. Handle Response
        if (res.ok) {
            // Success! Refresh the grid to show the new state
            // (e.g. Order #1 moves from "Pending" to "Cooking")
            await loadIncomingOrders();
            // Optional: Play a sound or show a toast
        } else {
            const err = await res.json();
            alert("Failed to update order: " + (err || "Unknown Error"));
        }

    } catch (e) {
        console.error(e);
        alert("Network Error: Could not update order.");
    }
}

// TODO1 Implementation: Accept Order (Pending -> Cooking)
async function acceptOrder(orderId)
{
    // Status 2 = Cooking
    await updateOrderStatus(orderId, 2);
}

// TODO2 Implementation: Ready for Driver (Cooking -> Ready/Delivering)
async function deliverOrder(orderId)
{
    // Status 3 = Ready / Out for Delivery
    await updateOrderStatus(orderId, 3);
}
