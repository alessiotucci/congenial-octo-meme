// ************************************************************************** //
//     File: src\frontend\js\order.js                                         //
//     Author: atucci <atucci@student.42.fr>                                  //
//     Created: 2026/02/04 12:14:53                                           //
//     Updated: 2026/02/05 15:49:10                                           //
//     System: unknown [SurfaceLaptopmy]                                      //
//     Hardware: unknown | RAM: Unknown                                       //
// ************************************************************************** //


/* -------------------------------------------------------------------------- */
/* 1. LOAD ORDER HISTORY (The List View)                                      */
/* -------------------------------------------------------------------------- */
async function loadOrderHistory() {
    const container = document.getElementById('pills-orders'); // The tab pane
    
    // Loading State
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Fetching your history...</p>
        </div>`;

    if (!currentState.entityId) {
        container.innerHTML = '<div class="alert alert-warning">Please log in to view orders.</div>';
        return;
    }

    try {
        const res = await fetch(`http://localhost:8000/api/food_order/read_by_customer.php?id=${currentState.entityId}`);
        const json = await res.json();
        const orders = json.data || [];

        if (orders.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-4x text-muted mb-3 opacity-25"></i>
                    <h4>No orders yet</h4>
                    <p class="text-muted">You haven't ordered anything. Go to Restaurants!</p>
                </div>`;
            return;
        }

        // Build the HTML List
        let listHTML = `<div class="row g-3">`;
        
        orders.forEach(order => {
            // Helper for Status Colors
            let badgeClass = 'bg-secondary';
            if (order.status_id == 1) badgeClass = 'bg-warning text-dark'; // Pending
            if (order.status_id == 2) badgeClass = 'bg-info text-dark';    // Cooking
            if (order.status_id == 3) badgeClass = 'bg-primary';           // Delivering
            if (order.status_id == 4) badgeClass = 'bg-success';           // Delivered

            listHTML += `
                <div class="col-12 col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0 text-truncate">${order.restaurant}</h6>
                                <span class="badge ${badgeClass}">${order.status}</span>
                            </div>
                            <div class="text-muted small mb-3">
                                <i class="far fa-calendar-alt me-1"></i> ${new Date(order.date).toLocaleString()}
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">€${order.total}</span>
                                <button class="btn btn-sm btn-outline-primary rounded-pill" onclick="openOrderDetails(${order.id})">
                                    Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        listHTML += `</div>`;
        container.innerHTML = listHTML;

    }
	catch (error)
	{
        console.error(error);
        container.innerHTML = '<div class="alert alert-danger">Failed to load history.</div>';
    }
}

/* -------------------------------------------------------------------------- */
/* 2. OPEN ORDER DETAILS (The Receipt Modal)                                  */
/* -------------------------------------------------------------------------- */
async function openOrderDetails(orderId) {
    // 1. Create Modal HTML on the fly if it doesn't exist
    if (!document.getElementById('orderDetailModal')) {
        document.body.insertAdjacentHTML('beforeend', `
            <div class="modal fade" id="orderDetailModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Order #${orderId}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="orderDetailContent">
                            <div class="text-center"><div class="spinner-border text-primary"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }

    const modal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
    modal.show();
    
    const content = document.getElementById('orderDetailContent');

    try {
        const res = await fetch(`http://localhost:8000/api/food_order/read_single.php?id=${orderId}`);
        const order = await res.json();

        // 2. Render Receipt
        content.innerHTML = `
            <div class="text-center mb-4">
                <h3 class="fw-bold mb-0">${order.restaurant_name || 'Restaurant'}</h3>
                <span class="badge bg-light text-dark border mt-2">${order.status_name}</span>
            </div>
            
            <h6 class="text-muted small text-uppercase border-bottom pb-2">Items</h6>
            <ul class="list-group list-group-flush mb-4">
                ${order.items.map(item => `
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <div>
                            <span class="fw-bold">${item.quantity}x</span> ${item.item_name}
                        </div>
                        <span>€${item.price_at_order}</span>
                    </li>
                `).join('')}
            </ul>

            <div class="d-flex justify-content-between border-top pt-3">
                <span class="h5">Total Paid</span>
                <span class="h5 fw-bold text-success">€${order.total}</span>
            </div>
            <p class="text-center text-muted small mt-3">
                Ordered on: ${new Date(order.ordered_at).toLocaleString()}
            </p>
        `;

    }
	catch (e)
	{
        content.innerHTML = '<div class="alert alert-danger">Could not load details.</div>';
    }
}
