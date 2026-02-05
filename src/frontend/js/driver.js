// ************************************************************************** //
//     File: src\frontend\js\driver.js                                        //
//     Author: atucci <atucci@student.42.fr>                                  //
//     Created: 2026/02/04 12:14:56                                           //
//     Updated: 2026/02/04 12:14:58                                           //
//     System: unknown [SurfaceLaptopmy]                                      //
//     Hardware: unknown | RAM: Unknown                                       //
// ************************************************************************** //




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
            // Added: onclick and cursor style
            grid.innerHTML += `
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm h-100" 
                         style="cursor: pointer; transition: transform 0.2s;" 
                         onclick="viewSharedOrder(${order.id})"
                         onmouseover="this.style.transform='scale(1.02)'"
                         onmouseout="this.style.transform='scale(1)'">
                         
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-0">${order.restaurant_name} <i class="fas fa-arrow-right small text-muted mx-1"></i> ${order.customer_name}</h6>
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