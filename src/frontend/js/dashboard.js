// ************************************************************************** //
//     File: src\frontend\js\dashboard.js                                     //
//     Author: atucci <atucci@student.42.fr>                                  //
//     Created: 2026/02/04 09:28:47                                           //
//     Updated: 2026/02/04 09:28:51                                           //
//     System: unknown [SurfaceLaptopmy]                                      //
//     Hardware: unknown | RAM: Unknown                                       //
// ************************************************************************** //

/* ************************************************************************** */
/* File: js/dashboard.js                                                    */
/* ************************************************************************** */

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

// ----------------------------------------------------------------------------
// RESTAURANT DASHBOARD LOGIC (Mobile First)
// ----------------------------------------------------------------------------
function renderRestaurantDashboard(container) {
    // 1. Mobile-First Header (Stack vertically on phone, row on desktop)
    const headerHTML = `
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h2 class="mb-0 fw-bold"><i class="fas fa-utensils me-2 text-primary"></i>My Menu</h2>
                <p class="text-muted small mb-0">Manage what your customers see</p>
            </div>
            <div class="d-flex gap-2 w-100 w-md-auto">
                 <button class="btn btn-primary w-100 w-md-auto shadow-sm" onclick="showAddMenuModal()">
                    <i class="fas fa-plus me-2"></i>Add Item
                </button>
                 <button class="btn btn-outline-danger w-auto" onclick="logout()" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>
    `;

    // 2. The Content Container (Grid for responsiveness)
    const contentHTML = `
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
                        <h5 class="modal-title fw-bold">New Dish</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addMenuForm" onsubmit="handleCreateMenuItem(event)">
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
                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill">Add to Menu</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.innerHTML = headerHTML + contentHTML;

    // 3. Trigger Data Fetch
    // We assume you have a 'read.php' endpoint. If not, we simulate empty state for now.
    loadMenuItems(); 
}


/* ************************************************************************** */
/* FUNCTION: LOAD & RENDER MENU ITEMS                                       */
/* ************************************************************************** */
async function loadMenuItems()
{
    const container = document.getElementById('menuContainer');
    
    // 1. SAFETY CHECK
    // If the user refreshed the page, we might not have the entityId yet.
    if (!currentState.entityId)
	{
		console.log(`MY LOG: ${currentState.entityId}`);
        console.warn("WARNING LOG: No Entity ID found. Waiting for session check...");
        container.innerHTML = `<div class="col-12 text-center py-5 text-muted">Loading profile...</div>`;
        return; 
    }

    try {
        console.log(`📡 Fetching menu for Restaurant ID: ${currentState.entityId}`);

        // 2. REAL API CALL
        const res = await fetch(`https://localhost/A_project_forUniversity/src/api/menu_item/read_by_place.php?id=${currentState.entityId}`);
        const responseData = await res.json();
        
        // 3. PARSE DATA
        // The API now returns { data: [...] }
        const items = responseData.data || []; 

        container.innerHTML = '';

        // 4. EMPTY STATE
        if (items.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="mb-3">
                        <span class="fa-stack fa-3x">
                            <i class="fas fa-circle fa-stack-2x text-light"></i>
                            <i class="fas fa-utensils fa-stack-1x text-muted"></i>
                        </span>
                    </div>
                    <h4 class="text-muted">Your menu is empty</h4>
                    <p class="small text-muted mb-4">Start adding delicious dishes to get orders!</p>
                    <button class="btn btn-sm btn-outline-primary rounded-pill" onclick="showAddMenuModal()">
                        Create First Item
                    </button>
                </div>
            `;
            return;
        }

        // 5. POPULATED STATE
        items.forEach(item => {
            // Note: We use item.is_available (boolean) directly now
            const stockBadge = item.is_available 
                ? '<span class="badge bg-success-subtle text-success rounded-pill">In Stock</span>'
                : '<span class="badge bg-danger-subtle text-danger rounded-pill">Sold Out</span>';

            const cardHTML = `
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm item-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title fw-bold mb-0 text-truncate">${item.name}</h5>
                                <span class="badge bg-light text-dark border">€${item.price}</span>
                            </div>
                            <p class="card-text text-muted small text-truncate-2">${item.description || 'No description provided.'}</p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                ${stockBadge}
                                
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-light text-muted" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light text-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += cardHTML;
        });

    } catch (error) {
        console.error("❌ Load Error:", error);
        container.innerHTML = `<div class="alert alert-danger">Failed to load menu. Is the server running?</div>`;
    }
}