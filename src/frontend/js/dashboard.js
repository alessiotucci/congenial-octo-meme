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

    switch(role) {
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
/* -------------------------------------------------------------------------- */
/* RENDER: RESTAURANT DASHBOARD                                               */
/* -------------------------------------------------------------------------- */
function renderRestaurantDashboard(container) {
    
    // 1. HEADER SECTION (Mobile First: Stacked on phone, Row on Desktop)
    const headerHTML = `
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h2 class="mb-0 fw-bold"><i class="fas fa-utensils me-2 text-primary"></i>My Menu</h2>
                <p class="text-muted small mb-0">Manage what your customers see</p>
            </div>
            <div class="d-flex gap-2 w-100 w-md-auto">
                 <button class="btn btn-primary w-100 w-md-auto shadow-sm" onclick="openAddModal()">
                    <i class="fas fa-plus me-2"></i>Add Item
                </button>
                 <button class="btn btn-outline-danger w-auto" onclick="logout()" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    `;

    // 2. CONTENT & MODAL SECTION
    const bodyHTML = `
        <div id="menuContainer" class="row g-3">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading your kitchen...</p>
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
                                <input type="text" class="form-control form-control-lg" name="name" placeholder="e.g. Truffle Pasta" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-uppercase text-muted">Price (€)</label>
                                    <input type="number" step="0.01" class="form-control" name="price" placeholder="12.50" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-uppercase text-muted">Category</label>
                                    <select class="form-select" name="category">
                                        <option value="Main" selected>Main</option>
                                        <option value="Starter">Starter</option>
                                        <option value="Dessert">Dessert</option>
                                        <option value="Drink">Drink</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-uppercase text-muted">Description</label>
                                <textarea class="form-control" name="description" rows="2" placeholder="Delicious homemade pasta..."></textarea>
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

    // 3. INJECT INTO PAGE
    container.innerHTML = headerHTML + bodyHTML;
    // 4. LOAD DATA
    loadMenuItems(); 
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
        const response = await fetch('https://localhost/A_project_forUniversity/src/api/menu_item/update.php', {
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