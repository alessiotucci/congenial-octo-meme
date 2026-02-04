// ************************************************************************** //
//     File: src\frontend\js\dashboard.js                                     //
//     Author: atucci <atucci@student.42.fr>                                  //
//     Created: 2026/02/04 09:28:47                                           //
//     Updated: 2026/02/04 09:28:51                                           //
//     System: unknown [SurfaceLaptopmy]                                      //
//     Hardware: unknown | RAM: Unknown                                       //
// ************************************************************************** //



// Main Router
function showDashboard(role)
{
    switchView('view-dashboard');
    const container = document.getElementById('dashboardContent');
    container.innerHTML = ''; // Clear

    switch(role)
	{
        case 'customer':
            renderCustomerDashboard(container);
            break;
        case 'rider':
            renderRiderDashboard(container);
            break;
        case 'food_place':
            renderRestaurantDashboard(container);
            break;
        default:
            container.innerHTML = `<div class="alert alert-danger">Unknown Role: ${role}</div>`;
    }
}

/* ************************************************************************** */
/* GLOBAL CACHE (To store items so we can edit/update them later)             */
/* ************************************************************************** */
let restaurantMenuCache = []; 

/* ************************************************************************** */
/* FUNCTION: LOAD & RENDER MENU ITEMS                                       */
/* ************************************************************************** */
async function loadMenuItems() {
    const container = document.getElementById('menuContainer');
    
    if (!currentState.entityId) {
        console.warn("⚠️ No Entity ID. Waiting...");
        return; 
    }

    try {
        const res = await fetch(`https://localhost/A_project_forUniversity/src/api/menu_item/read_by_place.php?id=${currentState.entityId}`);
        const responseData = await res.json();
        
        // 1. SAVE TO CACHE (Critical for Update/Delete)
        restaurantMenuCache = responseData.data || []; 
        const items = restaurantMenuCache;

        container.innerHTML = '';

        // Empty State
        if (items.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <h4 class="text-muted">Your menu is empty</h4>
                    <button class="btn btn-sm btn-outline-primary rounded-pill" onclick="openAddModal()">
                        Create First Item
                    </button>
                </div>`;
            return;
        }

        // Populated State
        items.forEach(item => {
            // Determine Color/Text based on availability
            const isAvailable = (item.is_available == 1 || item.is_available === true);
            const statusBadge = isAvailable 
                ? '<span class="badge bg-success-subtle text-success rounded-pill">In Stock</span>'
                : '<span class="badge bg-danger-subtle text-danger rounded-pill">Sold Out</span>';
            
            const btnClass = isAvailable ? 'btn-outline-warning' : 'btn-outline-success';
            const btnIcon  = isAvailable ? 'fa-ban' : 'fa-check';
            const btnText  = isAvailable ? 'Set Out of Stock' : 'Set In Stock';

            const cardHTML = `
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm item-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title fw-bold mb-0 text-truncate">${item.name}</h5>
                                <span class="badge bg-light text-dark border">€${item.price}</span>
                            </div>
                            <p class="card-text text-muted small text-truncate-2">${item.description || ''}</p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                ${statusBadge}
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-light text-primary" onclick="openEditModal(${item.id})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light text-danger" onclick="deleteItem(${item.id})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <button class="btn btn-sm ${btnClass} w-100 mt-2" onclick="toggleStock(${item.id})">
                                <i class="fas ${btnIcon} me-2"></i>${btnText}
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += cardHTML;
        });

    } catch (error) {
        console.error("Load Error", error);
    }
}

/* -------------------------------------------------------------------------- */
/* ACTION: DELETE ITEM                                                        */
/* -------------------------------------------------------------------------- */
async function deleteItem(id) {
    if(!confirm("Are you sure you want to delete this dish?")) return;

    try {
        const response = await fetch('https://localhost/A_project_forUniversity/src/api/menu_item/delete.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });

        if(response.ok) {
            console.log("🗑️ Item deleted");
            loadMenuItems(); // Refresh grid
        } else {
            alert("Failed to delete item.");
        }
    } catch (e) {
        console.error(e);
        alert("Network Error");
    }
}

/* -------------------------------------------------------------------------- */
/* ACTION: TOGGLE STOCK (Update)                                              */
/* -------------------------------------------------------------------------- */
async function toggleStock(id) {
    // 1. Find the full object from our cache
    const item = restaurantMenuCache.find(i => i.id == id);
    if (!item) return;

    // 2. Flip the status
    // Note: PHP expects boolean or 1/0. 
    const newStatus = (item.is_available == 1) ? 0 : 1;

    // 3. Prepare Payload (PHP requires ALL fields for update)
    const payload = {
        id: item.id,
        name: item.name,
        description: item.description,
        price: item.price,
        category: item.category || 'Main', // Default if missing
        is_available: newStatus
    };

    try {
        const response = await fetch('https://localhost/A_project_forUniversity/src/api/menu_item/update_status.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if(response.ok) {
            console.log("🔄 Stock updated");
            loadMenuItems(); // Refresh grid
        } else {
            alert("Update failed.");
        }
    } catch (e) {
        console.error(e);
    }
}

/* -------------------------------------------------------------------------- */
/* ACTION: OPEN MODALS                                                        */
/* -------------------------------------------------------------------------- */
function openAddModal() {
    // Clear form
    document.getElementById('addMenuForm').reset();
    document.getElementById('menuItemId').value = ''; // Clear ID (Hidden Input)
    document.getElementById('modalTitle').innerText = 'New Dish';
    document.getElementById('modalSubmitBtn').innerText = 'Add to Menu';
    
    const modal = new bootstrap.Modal(document.getElementById('addMenuModal'));
    modal.show();
}

function openEditModal(id) {
    const item = restaurantMenuCache.find(i => i.id == id);
    if (!item) return;

    // Populate Form
    const form = document.getElementById('addMenuForm');
    form.querySelector('[name="name"]').value = item.name;
    form.querySelector('[name="price"]').value = item.price;
    form.querySelector('[name="description"]').value = item.description;
    form.querySelector('[name="category"]').value = item.category || 'Main';
    form.querySelector('#isAvailableCheck').checked = (item.is_available == 1);
    
    // CRITICAL: Set the Hidden ID so we know it's an Update, not Create
    document.getElementById('menuItemId').value = item.id;

    // Change UI Text
    document.getElementById('modalTitle').innerText = 'Edit Dish';
    document.getElementById('modalSubmitBtn').innerText = 'Save Changes';

    const modal = new bootstrap.Modal(document.getElementById('addMenuModal'));
    modal.show();
}










function renderRiderDashboard(container) {
    
    // 1. HEADER
    const headerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0 fw-bold"><i class="fas fa-motorcycle me-2 text-primary"></i>Rider Portal</h2>
                <p class="text-muted small mb-0"> deliver smiles (and pizza)</p>
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
                <button class="nav-link active rounded-pill px-4" id="pills-jobs-tab" data-bs-toggle="pill" data-bs-target="#pills-jobs" type="button" role="tab" onclick="loadAvailableJobs()">
                    <i class="fas fa-map-marker-alt me-2"></i>Available Jobs
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4" id="pills-history-tab" data-bs-toggle="pill" data-bs-target="#pills-history" type="button" role="tab" onclick="loadRiderHistory()">
                    <i class="fas fa-history me-2"></i>My Deliveries
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            
            <div class="tab-pane fade show active" id="pills-jobs" role="tabpanel">
                <div id="riderGrid" class="row g-3">
                    <div class="col-12 text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2 text-muted">Scanning area for orders...</p>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="pills-history" role="tabpanel">
                <div id="historyGrid" class="row g-3">
                    <div class="text-center py-5 text-muted">Loading history...</div>
                </div>
            </div>
        </div>
    `;

    container.innerHTML = headerHTML + bodyHTML;

    // Load Default View
    loadAvailableJobs();
}

/* -------------------------------------------------------------------------- */
/* LOGIC: LOAD AVAILABLE JOBS                                                 */
/* -------------------------------------------------------------------------- */
async function loadAvailableJobs() {
    const grid = document.getElementById('riderGrid');
    
    try {
        // We need a specific endpoint for this. 
        // It fetches Status 3 (Ready) that are Unassigned OR Assigned to Me
        const res = await fetch(`https://localhost/A_project_forUniversity/src/api/food_order/read_for_driver.php?id=${currentState.entityId}`);
        const json = await res.json();
        const orders = json.data || [];

        grid.innerHTML = '';

        if (orders.length === 0) {
            grid.innerHTML = `
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fas fa-coffee fa-3x mb-3 opacity-25"></i>
                    <h4>No orders available</h4>
                    <p>Relax for a bit, we'll notify you when food is ready.</p>
                </div>`;
            return;
        }

        orders.forEach(order => {
            // LOGIC: Is this MY active job, or a pool job?
            // If assigned_driver_id matches My ID, it's my active mission.
            const isMyJob = (order.assigned_driver_id == currentState.entityId);
            
            let cardClass = isMyJob ? 'border-primary border-2 shadow' : 'border-0 shadow-sm';
            let badge = isMyJob 
                ? '<span class="badge bg-primary"><i class="fas fa-exclamation-circle me-1"></i>Current Mission</span>' 
                : '<span class="badge bg-success text-white">Ready for Pickup</span>';
            
            let btnAction = isMyJob
                ? `<button class="btn btn-success w-100 py-2 fw-bold" onclick="completeDelivery(${order.id})">
                     <i class="fas fa-check-double me-2"></i>Mark Delivered
                   </button>`
                : `<button class="btn btn-outline-primary w-100 py-2" onclick="acceptJob(${order.id})">
                     <i class="fas fa-hand-paper me-2"></i>Accept Delivery
                   </button>`;

            // Address display logic
            const addressDisplay = `${order.address_line1}, ${order.city}`;

            const html = `
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 ${cardClass}">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <strong>#${order.id}</strong>
                            ${badge}
                        </div>
                        <div class="card-body">
                            <h5 class="card-title mb-1">${order.restaurant_name}</h5>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i> To: ${order.customer_name}
                            </p>
                            
                            <div class="alert alert-light border small mb-3">
                                <i class="fas fa-location-arrow me-2"></i>
                                <strong>${addressDisplay}</strong>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="small text-muted">${order.item_count} Items</span>
                                <span class="fw-bold">Earn: €${order.delivery_fee}</span>
                            </div>
                            ${btnAction}
                        </div>
                    </div>
                </div>`;
            grid.innerHTML += html;
        });

    } catch (e) {
        console.error(e);
        grid.innerHTML = '<div class="alert alert-danger">Error loading jobs.</div>';
    }
}

/* -------------------------------------------------------------------------- */
/* LOGIC: LOAD HISTORY                                                        */
/* -------------------------------------------------------------------------- */
async function loadRiderHistory() {
    const grid = document.getElementById('historyGrid');
    
    try {
        // Status 4 = Delivered
        const res = await fetch(`https://localhost/A_project_forUniversity/src/api/food_order/read_driver_history.php?id=${currentState.entityId}`);
        const json = await res.json();
        const orders = json.data || [];

        grid.innerHTML = '';
        
        if (orders.length === 0) {
            grid.innerHTML = '<div class="col-12 text-center py-5">No past deliveries found.</div>';
            return;
        }

        orders.forEach(order => {
            grid.innerHTML += `
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-0">${order.restaurant_name} -> ${order.customer_name}</h6>
                                <div class="text-muted small">${new Date(order.order_datetime).toLocaleString()}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success">+€${order.delivery_fee}</div>
                                <span class="badge bg-light text-muted border">Delivered</span>
                            </div>
                        </div>
                    </div>
                </div>`;
        });

    } catch (e) {
        grid.innerHTML = '<div class="alert alert-danger">Error loading history.</div>';
    }
}

/* -------------------------------------------------------------------------- */
/* ACTIONS: ACCEPT & COMPLETE                                                 */
/* -------------------------------------------------------------------------- */

// 1. Accept Job (Assigns Driver ID)
async function acceptJob(orderId) {
    if(!confirm("Accept this delivery?")) return;
    
    const payload = {
        id: orderId,
        driver_id: currentState.entityId
    };

    try {
        const res = await fetch('https://localhost/A_project_forUniversity/src/api/food_order/assign_driver.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (res.ok) {
            loadAvailableJobs(); // Refresh to see "Current Mission" status
        } else {
            alert("Could not accept job. It might have been taken.");
        }
    } catch (e) {
        console.error(e);
    }
}

// 2. Complete Job (Update Status to 4)
async function completeDelivery(orderId) {
    if(!confirm("Confirm delivery complete?")) return;

    const payload = {
        id: orderId,
        status_id: 4 // Delivered
    };

    try {
        const res = await fetch('https://localhost/A_project_forUniversity/src/api/food_order/update_status.php', {
            method: 'PUT', // We reuse the generic status update endpoint
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (res.ok) {
            alert("Great job! Delivery recorded.");
            loadAvailableJobs(); // Grid should be empty or show pool now
        } else {
            alert("Error completing delivery.");
        }
    } catch (e) {
        console.error(e);
    }
}