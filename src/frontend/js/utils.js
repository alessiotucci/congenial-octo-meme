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
    customer: { icon: 'fas fa-user-tie', title: 'Customer', message: 'Tired of unfair delivery fees?', subtext: 'Join thousands eating better, paying less.', color: '#73628A' },
    food_place: { icon: 'fas fa-utensils', title: 'Food Place', message: 'Your digital menu, your rules', subtext: 'Grow your business with fair fees.', color: '#313D5A' },
    rider: { icon: 'fas fa-motorcycle', title: 'Delivery Driver', message: 'Your time, your pay', subtext: 'We don\'t take a cut. We help you connect.', color: '#CBC5EA' }
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



function switchView(viewName) {
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
    
    currentState.currentView = viewName;
    window.scrollTo(0, 0);
    console.log(`📍 Switched to view: ${viewName}`);
}

function updateRoleDisplay(role) {
    if (!roleConfig[role]) return;
    const config = roleConfig[role];
    
    document.getElementById('roleIcon').className = `${config.icon} fa-3x`;
    document.getElementById('welcomeMessage').textContent = config.message;
    document.getElementById('welcomeSubtext').textContent = config.subtext;
    document.getElementById('roleBadge').textContent = config.title;
    document.getElementById('registrationCard').setAttribute('data-role', role);
    
    currentState.selectedRole = role;
    console.log(`✨ Role updated to: ${role}`);
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