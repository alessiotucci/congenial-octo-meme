// ************************************************************************** //
//     File: src\frontend\js\utils.js                                         //
//     Author: atucci <atucci@student.42.fr>                                  //
//     Created: 2026/02/04 09:29:06                                           //
//     Updated: 2026/02/04 09:29:09                                           //
//     System: unknown [SurfaceLaptopmy]                                      //
//     Hardware: unknown | RAM: Unknown                                       //
// ************************************************************************** //

// This file contains configuration, state, and helper functions used by everyone.
// It depends on nothing.


const ROLE_FORMS = {
    customer: {
        endpoint: 'https://localhost/A_project_forUniversity/src/api/customer/create.php',
        html: `
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">First Name</label><input type="text" class="form-control" name="first_name" required></div>
                <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" class="form-control" name="last_name" required></div>
                <div class="col-md-12"><label class="form-label">Phone Number</label><input type="tel" class="form-control" name="phone_number_original" placeholder="+1 555-0199" required></div>
                <div class="col-md-12"><label class="form-label">Nickname (Optional)</label><input type="text" class="form-control" name="nick_name"></div>
            </div>`
    },
    rider: {
        endpoint: 'https://localhost/A_project_forUniversity/src/api/delivery_driver/create.php',
        html: `
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">First Name</label><input type="text" class="form-control" name="first_name" required></div>
                <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" class="form-control" name="last_name" required></div>
                <div class="col-md-12"><label class="form-label">Phone Number</label><input type="tel" class="form-control" name="phone_number_original" required></div>
            </div>`
    },
    food_place: {
        endpoint: 'https://localhost/A_project_forUniversity/src/api/food_place/create.php',
        html: `
            <h6 class="text-muted mb-3">Restaurant Details</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-12"><label class="form-label">Restaurant Name</label><input type="text" class="form-control" name="name" required></div>
                <div class="col-md-6"><label class="form-label">Cuisine Type</label><input type="text" class="form-control" name="food_type"></div>
                <div class="col-md-6"><label class="form-label">Opening Hours</label><input type="text" class="form-control" name="opening_hours"></div>
                <div class="col-md-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
            </div>
            <h6 class="text-muted mb-3">Business Address</h6>
            <div class="row g-3">
                <div class="col-md-8"><label class="form-label">Street Address</label><input type="text" class="form-control" name="address_line1" required></div>
                <div class="col-md-4"><label class="form-label">Street No.</label><input type="text" class="form-control" name="street_number" required></div>
                <div class="col-md-6"><label class="form-label">City</label><input type="text" class="form-control" name="city" required></div>
                <div class="col-md-6"><label class="form-label">Postal Code</label><input type="text" class="form-control" name="postal_code" required></div>
                <input type="hidden" name="country_id" value="1">
            </div>`
    }
};

const roleConfig = {
    customer: { icon: 'fas fa-user-tie', title: 'Customer', message: 'Tired of unfair delivery fees?', subtext: 'Join thousands eating better, paying less.', color: '#654EA3' },
    food_place: { icon: 'fas fa-utensils', title: 'Food Place', message: 'Your digital menu, your rules', subtext: 'Grow your business with fair fees.', color: '#7353ED' },
    rider: { icon: 'fas fa-motorcycle', title: 'Delivery Driver', message: 'Your time, your pay', subtext: 'We don\'t take a cut. We help you connect.', color: '#5365ED' }
};


let currentState = {
    currentView: 'home',
    selectedRole: 'customer',
	// This holds the specific ID for the role (e.g., food_place_id, customer_id)
	entityId: null,
    userId: null,       // Set after Step 1 registration OR Login
    isLoggedIn: false,  // Track auth status
    userRole: null,     // Track logged in role
    csrfToken: null    // Security token

};



function switchView(viewName)
{
    const allViews = document.querySelectorAll('.view, .view-section'); // Updated selector to catch login/dashboard
    allViews.forEach(view => {
        view.classList.remove('active');
        view.classList.add('d-none'); // Using Bootstrap d-none instead of custom 'hidden' class for consistency
        view.classList.add('hidden'); // Keeping your custom class just in case
    });
    
    const targetView = document.getElementById(viewName);
    if (targetView) {
        targetView.classList.remove('hidden');
        targetView.classList.remove('d-none');
        targetView.classList.add('active');
    }
    
    // Update the URL and push a new state to the history
    const newUrl = `/${viewName}`; // e.g., '/registration/step1'
    window.history.pushState({ view: viewName }, '', newUrl);

    currentState.currentView = viewName;
    window.scrollTo(0, 0);
    console.log(`Switched to view: ${viewName}`);
}

function updateRoleDisplay(role)
{
    if (!roleConfig[role]) return;
    const config = roleConfig[role];
    
    document.getElementById('roleIcon').className = `${config.icon} fa-3x`;
    document.getElementById('welcomeMessage').textContent = config.message;
    document.getElementById('welcomeSubtext').textContent = config.subtext;
    document.getElementById('roleBadge').textContent = config.title;
    document.getElementById('registrationCard').setAttribute('data-role', role);
    
    currentState.selectedRole = role;
    console.log(`Role updated to: ${role}`);
}

function togglePasswordVisibility()
{
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function goToRegistration(event)
{
    if (event)
		event.preventDefault();
    switchView('view-registration-step1');
}

function backToStep1(event)
{
    if (event)
		event.preventDefault();
    switchView('view-registration-step1');
}

function resetToHome(event)
{
    if (event)
		event.preventDefault();
    switchView('view-home');
    document.getElementById('registrationForm').reset();
    updateRoleDisplay('customer');
}

function goToLogin(event)
{
    if (event)
		event.preventDefault();
    switchView('view-login');
}

/* ============================================================================
   CART MANAGEMENT (LocalStorage)
   ============================================================================ */
   const CART_KEY = 'wheelie_cart';

   const cartManager = {
	   // 1. GET CART
	   get: () => {
		   const stored = localStorage.getItem(CART_KEY);
		   return stored ? JSON.parse(stored) : { restaurantId: null, restaurantName: null, items: [] };
	   },
   
	   // 2. SAVE CART
	   save: (cart) => {
		   localStorage.setItem(CART_KEY, JSON.stringify(cart));
		   // Trigger a custom event so the UI updates immediately
		   document.dispatchEvent(new Event('cartUpdated'));
	   },
   
	   // 3. ADD ITEM (The Smart Logic)
	   add: (item, restaurantId, restaurantName) => {
		   let cart = cartManager.get();
   
		   // CONFLICT CHECK: Are we ordering from a different restaurant?
		   if (cart.restaurantId && cart.restaurantId != restaurantId) {
			   if (!confirm(`Your cart contains items from "${cart.restaurantName}". Discard them to order from "${restaurantName}"?`)) {
				   return false; // User cancelled
			   }
			   cart = { restaurantId: null, items: [] }; // Reset
		   }
   
		   // Initialize if empty
		   if (!cart.restaurantId) {
			   cart.restaurantId = restaurantId;
			   cart.restaurantName = restaurantName;
		   }
   
		   // CHECK DUPLICATES: Do we already have this item?
		   const existingItem = cart.items.find(i => i.id === item.id);
		   if (existingItem) {
			   existingItem.quantity += 1; // Increment
		   } else {
			   cart.items.push({ ...item, quantity: 1 }); // Add new
		   }
   
		   cartManager.save(cart);
		   return true;
	   },
   
	   // 4. REMOVE ITEM (Decrease qty or delete)
	   remove: (itemId) => {
		   let cart = cartManager.get();
		   const index = cart.items.findIndex(i => i.id === itemId);
		   
		   if (index !== -1) {
			   if (cart.items[index].quantity > 1) {
				   cart.items[index].quantity -= 1;
			   } else {
				   cart.items.splice(index, 1);
			   }
		   }
		   
		   // If cart is empty, clear the restaurant link
		   if (cart.items.length === 0) {
			   cart.restaurantId = null;
			   cart.restaurantName = null;
		   }
		   
		   cartManager.save(cart);
	   },
   
	   // 5. CLEAR
	   clear: () => {
		   localStorage.removeItem(CART_KEY);
		   document.dispatchEvent(new Event('cartUpdated'));
	   },
	   
	   // 6. CALCULATE TOTAL
	   getTotal: () => {
		   const cart = cartManager.get();
		   return cart.items.reduce((total, item) => total + (item.price * item.quantity), 0);
	   }
   };



   /* -------------------------------------------------------------------------- */
/* SHARED: VIEW SINGLE ORDER MODAL                                            */
/* Used by: Customer, Driver, Restaurant                                      */
/* -------------------------------------------------------------------------- */
async function viewSharedOrder(orderId) {
    // 1. Ensure Modal HTML exists in the DOM
    if (!document.getElementById('sharedOrderModal')) {
        document.body.insertAdjacentHTML('beforeend', `
            <div class="modal fade" id="sharedOrderModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold">Order Details #${orderId}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="sharedOrderContent">
                            <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
                        </div>
                        <div class="modal-footer border-0">
                            <button class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
    } else {
        // Update title if modal already exists
        document.querySelector('#sharedOrderModal .modal-title').innerText = `Order Details #${orderId}`;
        document.getElementById('sharedOrderContent').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    }

    // 2. Show Modal
    const modalEl = document.getElementById('sharedOrderModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    // 3. Fetch Data
    try {
        const res = await fetch(`https://localhost/A_project_forUniversity/src/api/food_order/read_single.php?id=${orderId}`);
        
        if (!res.ok)
			throw new Error("Order not found");
        
        const order = await res.json();
        
        // 4. Render Content
        // We handle missing fields gracefully using (||)
        const itemsHtml = order.items && order.items.length > 0 
            ? order.items.map(i => `
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span><b>${i.quantity}x</b> ${i.item_name}</span>
                    <span class="text-muted">€${i.price_at_order || '?'}</span>
                </li>`).join('') 
            : '<li class="list-group-item text-muted">No items found</li>';
			document.getElementById('sharedOrderContent').innerHTML = `
            <div class="text-center mb-4">
                <h4 class="fw-bold">${order.restaurant_name}</h4>
                <div class="text-muted small mb-2">
                    <i class="fas fa-arrow-down"></i> Delivering to
                </div>
                <h5 class="fw-bold">${order.customer_name}</h5>
                <p class="text-muted small"><i class="fas fa-map-marker-alt text-danger"></i> ${order.delivery_address}</p>
            </div>

            <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded mb-3">
                <span class="badge bg-white text-dark border">${order.status_name}</span>
                <span class="h4 text-success fw-bold mb-0">€${order.total}</span>
            </div>

            <h6 class="text-muted small text-uppercase fw-bold border-bottom pb-2 mb-2">Order Items</h6>
            <ul class="list-group list-group-flush mb-3">
                ${itemsHtml}
            </ul>
        `;
		

    } catch (e) {
        document.getElementById('sharedOrderContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i> Could not load order details.
            </div>`;
    }
}