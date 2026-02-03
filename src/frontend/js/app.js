// ************************************************************************** //
//     File: js/app.js                                                        //
//     Author: atucci <atucci@student.42.fr>                                  //
//     Created: 2026/01/22 14:50:42                                           //
//     Updated: 2026/01/22 14:50:43                                           //
//     System: Linux [e4r2p4.42roma.it]                                       //
//     Hardware: Intel Core i5-8600 | RAM: 15GB                               //
// ************************************************************************** //

/* ============================================================================
   1. CONFIGURATION
   ============================================================================ */
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

/* ============================================================================
   2. STATE MANAGEMENT
   ============================================================================ */
let currentState = {
    currentView: 'home',
    selectedRole: 'customer',
    userId: null,       // Set after Step 1 registration OR Login
    isLoggedIn: false,  // Track auth status
    userRole: null,     // Track logged in role
    csrfToken: null     // Security token
};

/* ============================================================================
   3. VIEW MANAGEMENT
   ============================================================================ */
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

/* ============================================================================
   4. UI UPDATES & INTERACTION
   ============================================================================ */
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

function togglePasswordVisibility() {
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

/* ============================================================================
   5. REGISTRATION LOGIC
   ============================================================================ */
async function handleStep1Submit(event) {
    event.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const role = document.getElementById('role').value;

    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerText = "Creating Account...";

    try {
        const response = await fetch('https://localhost/A_project_forUniversity/src/api/user/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password, role })
        });

        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Registration failed');

        console.log('✅ Server Response:', data);
        currentState.userEmail = email;
        currentState.selectedRole = role;
        
        if (data.id) currentState.userId = data.id; 
        else throw new Error("Server did not return a User ID!");

        switchView('view-registration-step2');
        updateStep2Display(role);

    } catch (error) {
        console.error('❌ Error:', error);
        alert('Registration Failed: ' + error.message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = "Create Account";
    }
}

function updateStep2Display(role) {
    const step2Card = document.getElementById('step2Card');
    const formContainer = document.getElementById('step2FormContainer');
    const template = ROLE_FORMS[role];
    
    formContainer.innerHTML = '';
    if (template) {
        formContainer.innerHTML = `
            <form id="step2Form" data-endpoint="${template.endpoint}" onsubmit="handleStep2Submit(event)">
                ${template.html}
                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-lg btn-success text-white">Complete Setup <i class="fas fa-check ms-2"></i></button>
                </div>
            </form>`;
    } else {
        formContainer.innerHTML = '<div class="alert alert-danger">Error: Unknown role type.</div>';
    }
    step2Card.setAttribute('data-role', role);
    console.log(`Step 2 loaded for: ${role}`);
}

async function handleStep2Submit(event) {
    event.preventDefault();
    const form = event.target;
    const endpoint = form.getAttribute('data-endpoint');
    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());

    if (!currentState.userId) {
        alert("Critical Error: User ID missing. Please refresh.");
        return;
    }
    payload.user_id = currentState.userId;
    console.log("📤 Sending to", endpoint, payload);

    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Creation failed');

        console.log("✅ Success:", data);
        alert(`Profile Created! ID: ${data.id}. Please Login.`);
        
        // Redirect to Login after successful registration
        goToLogin();

    } catch (error) {
        console.error("❌ Error: ", error);
        alert("Error: " + error.message);
        submitBtn.disabled = false;
    }
}

/* ============================================================================
   6. AUTHENTICATION (Login & Session)
   ============================================================================ */
async function handleLogin(event) {
    event.preventDefault();
    const email = document.getElementById('loginEmail').value;
    const pass  = document.getElementById('loginPassword').value;

    try {
        const response = await fetch('https://localhost/A_project_forUniversity/src/api/user/login.php', {
            method: 'POST',
            credentials: 'include', // CRITICAL for XAMPP Sessions
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email, password: pass })
        });

        const data = await response.json();
        if (!response.ok) throw new Error(data.message);

        console.log("✅ Logged in!", data);
        currentState.isLoggedIn = true;
        currentState.userId = data.user_id;
        currentState.userRole = data.role;
        currentState.csrfToken = data.csrf_token;
        
        showDashboard(data.role);

    } catch (error) {
        alert("Login Failed: " + error.message);
    }
}

async function checkSession() {
    console.log("🔍 Checking Session...");
    try {
        const response = await fetch('https://localhost/A_project_forUniversity/src/api/user/validate_session.php', {
            method: 'GET',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.is_logged_in) {
            console.log("🔄 Session Restored for:", data.role);
            currentState.isLoggedIn = true;
            currentState.userId = data.user_id;
            currentState.userRole = data.role;
            currentState.csrfToken = data.csrf_token;
            showDashboard(data.role);
        } else {
            console.log("⚪ User is guest");
            // ERROR FIX: You had 'view-landing' but your HTML ID is 'view-home'
            switchView('view-home'); 
        }
    } catch (err) {
        console.error("Session check failed", err);
        switchView('view-home'); // Fallback to home
    }
}

async function logout() {
    try {
        await fetch('https://localhost/A_project_forUniversity/src/api/user/logout.php', {
            method: 'POST',
            credentials: 'include'
        });
        console.log("✅ Server session destroyed");
    } catch (error) {
        console.warn("⚠️ Logout network error", error);
    }

    currentState.isLoggedIn = false;
    currentState.userId = null;
    currentState.userRole = null;
    document.getElementById('dashboardContent').innerHTML = '';
    
    console.log("👋 Logged out");
    goToLogin(); // Send them to login screen
}

/* ============================================================================
   7. DASHBOARD LOGIC
   ============================================================================ */
function showDashboard(role) {
    switchView('view-dashboard');
    const container = document.getElementById('dashboardContent');
    container.innerHTML = ''; 

    let dashboardHTML = '';
    // NOTE: Ensure these cases match your DB role values exactly
    switch(role) {
        case 'customer':
            dashboardHTML = `
                <div class="container mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>🍽️ Hungry? Order Now</h2>
                        <button class="btn btn-outline-danger" onclick="logout()">Logout</button>
                    </div>
                    <div class="alert alert-info">API Ready. Waiting for restaurant list...</div>
                </div>`;
            break;
        case 'rider':
            dashboardHTML = `
                <div class="container mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>🛵 Delivery Zone</h2>
                        <button class="btn btn-outline-danger" onclick="logout()">Logout</button>
                    </div>
                    <div class="alert alert-warning">3 Orders Pending Pickup</div>
                </div>`;
            break;
        case 'food_place': // Matches your DB
        case 'merchant':
            dashboardHTML = `
                <div class="container mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>👨‍🍳 Kitchen Dashboard</h2>
                        <button class="btn btn-outline-danger" onclick="logout()">Logout</button>
                    </div>
                    <div class="alert alert-primary">Manage your Menu & Orders</div>
                </div>`;
            break;
        default:
            dashboardHTML = `<div class="alert alert-danger">Error: Unknown Role (${role}) <button onclick="logout()">Logout</button></div>`;
    }
    container.innerHTML = dashboardHTML;
}

/* ============================================================================
   8. NAVIGATION HELPERS
   ============================================================================ */
function goToRegistration(event) {
    if (event) event.preventDefault();
    switchView('view-registration-step1');
}

function backToStep1(event) {
    if (event) event.preventDefault();
    switchView('view-registration-step1');
}

function resetToHome(event) {
    if (event) event.preventDefault();
    switchView('view-home');
    document.getElementById('registrationForm').reset();
    updateRoleDisplay('customer');
}

function goToLogin(event) {
    if (event) event.preventDefault();
    switchView('view-login');
}

/* ============================================================================
   9. INITIALIZATION
   ============================================================================ */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 App initialized!');
    updateRoleDisplay('customer');
    
    // Check session immediately
    checkSession(); 
    
    // Keyboard shortcut (Escape to home)
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') resetToHome();
    });
});