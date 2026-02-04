// ************************************************************************** //
//     File: src\frontend\js\auth.js                                          //
//     Author: atucci <atucci@student.42.fr>                                  //
//     Created: 2026/02/04 09:28:43                                           //
//     Updated: 2026/02/04 09:28:46                                           //
//     System: unknown [SurfaceLaptopmy]                                      //
//     Hardware: unknown | RAM: Unknown                                       //
// ************************************************************************** //

/**
 * Core Login Function - Decoupled from HTML Forms
 * Can be called by the Login Form OR the Registration Auto-Login
 */

//1)
async function handleStep1Submit(event)
{
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

//2
function updateStep2Display(role)
{
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



//3
async function handleLogin(event)
{
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
        if (!response.ok)
			throw new Error(data.message);

        console.log("✅ Logged in (dumb asf)!", data);
        currentState.isLoggedIn = true;
        currentState.userId = data.user_id;
        currentState.userRole = data.role;
        currentState.csrfToken = data.csrf_token;
		currentState.entityId = data.entity_id; // DUMB MFS
        
        showDashboard(data.role);

    }
	catch (error)
	{
        alert("Login Failed: " + error.message);
    }
}

//4
async function checkSession()
{
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
        }
		else
		{
            console.log("⚪ User is guest");
            switchView('view-home'); 
        }
    } catch (err) {
        console.error("Session check failed", err);
        switchView('view-home'); // Fallback to home
    }
}

//5
async function logout()
{
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

//6)
async function performLogin(email, password)
{
    console.log("Ok bro... Authenticating...", email);
    
    try {
        const response = await fetch('https://localhost/A_project_forUniversity/src/api/user/login.php', {
            method: 'POST',
            credentials: 'include', // CRITICAL for XAMPP Sessions
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email, password: password })
        });

        const data = await response.json();
        
        if (!response.ok) throw new Error(data.message || "Login failed");

        console.log("✅ Logged in COJONE!", data);
		console.log(`bitch: data.entity_id: ${data.entity_id}`);
        
        // Update Global State (from utils.js)
        currentState.isLoggedIn = true;
        currentState.userId = data.user_id;
        currentState.userRole = data.role;
        currentState.csrfToken = data.csrf_token;
		
		

		if (data.entity_id)
		{
            currentState.entityId = data.entity_id;
			console.log(`MY DEBUG SHIT: ${currentState.entityId}`);
        }
		else
		{
            console.warn("DEBUG_API did not return entity_id. Some features may fail.");
            // Fallback: If your API isn't updated yet, this will stay null
            currentState.entityId = null; 
        }
        
        // Redirect to Dashboard (from dashboard.js)
        showDashboard(data.role);
        return true;

    }
	catch (error)
	{
        console.error("Login Error:", error);
        alert("Authentication Failed: " + error.message);
        return false;
    }
}

/**
 * Event Handler for the actual Login Form
 */
async function handleLoginFormSubmit(event)
{
    event.preventDefault(); // Stop page reload
    const email = document.getElementById('loginEmail').value;
    const pass  = document.getElementById('loginPassword').value;
    
    // Call the shared function
    await performLogin(email, pass);
}

//TODO: old version, updated by gemini
//async function handleStep2Submit(event)
//{
//    event.preventDefault();
//    const form = event.target;
//    const endpoint = form.getAttribute('data-endpoint');
//    const formData = new FormData(form);
//    const payload = Object.fromEntries(formData.entries());
//
//    if (!currentState.userId)
//	{
//        alert("Critical Error: User ID missing. Please refresh.");
//        return;
//    }
//
//    payload.user_id = currentState.userId;
//    console.log("📤 Sending to", endpoint, payload);
//
//    const submitBtn = form.querySelector('button[type="submit"]');
//    submitBtn.disabled = true;
//
//    try {
//        const response = await fetch(endpoint, {
//            method: 'POST',
//            headers: { 'Content-Type': 'application/json' },
//            body: JSON.stringify(payload)
//        });
//
//        const data = await response.json();
//        if (!response.ok) throw new Error(data.message || 'Creation failed');
//
//        console.log("✅ Success:", data);
//        alert(`Profile Created! ID: ${data.id}. Please Login.`);
//        
//        // Redirect to Login after successful registration
//		//TODO: instead of redirect to another form to perform the login, should we just log the user in after the creation?
//        goToLogin();
//
//    } catch (error) {
//        console.error("❌ Error: ", error);
//        alert("Error: " + error.message);
//        submitBtn.disabled = false;
//    }
//}

//new version
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
    console.log("📤 Creating Profile...", endpoint);

    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Finalizing...';

    try {
        // 1. Create the Entity (Customer/Driver/Restaurant)
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Creation failed');

        console.log("✅ Profile Created:", data);

		if (data.id)
		{
            currentState.entityId = data.id;
            console.log("ok bro, Entity Linked. ID:", currentState.entityId);
        }
        
        // 2. AUTO-LOGIN MOMENT
        // We use the email/password we saved in Step 1
        if (currentState.userEmail && currentState.userPassword)
		{
            console.log("🔄 Auto-logging in new user...");
            const loginSuccess = await performLogin(currentState.userEmail, currentState.userPassword);
            
            // If login worked, we are done (performLogin handles the redirect)
            if (loginSuccess)
					return; 
        }
		else
		{
            console.warn("⚠️ Credentials lost from memory. Redirecting to manual login.");
            goToLogin();
        }

    } catch (error) {
        console.error("❌ Error: ", error);
        alert("Error: " + error.message);
        submitBtn.disabled = false;
        submitBtn.innerText = "Complete Setup";
    }
}


/* ************************************************************************** */
/* FUNCTION: CREATE MENU ITEM                                               */
/* ************************************************************************** */
async function handleCreateMenuItem(event)
{
    event.preventDefault();

    // 1. Get Data
    const form = event.target;
    const formData = new FormData(form);
    const payload = Object.fromEntries(formData.entries());

    // 2. Add Critical IDs
    // We need the food_place_id. Since we logged in as a User, 
    // we assume 'currentState.userId' links to a FoodPlace.
    // OPTION A: You stored food_place_id in currentState during login.
    // OPTION B (MVP): We send user_id and PHP finds the restaurant.
    
    // For now, let's assume we need to pass food_place_id. 
    // If you don't have it in state, we might hit a bug here.
    // payload.food_place_id = currentState.foodPlaceId; // IDEAL
    
    // HACK FOR NOW: If your API requires food_place_id explicitly, we need to know it.
    // If your API can infer it from the logged-in user, great.
    // Let's assume we pass a placeholder or you fixed the API to accept user_id.
    payload.food_place_id = currentState.userId; // Temporary assumption: User ID = Place ID (or close enough for dev)

    // Fix Checkbox (FormData doesn't send "false" for unchecked boxes, it sends nothing)
    payload.is_available = form.querySelector('#isAvailableCheck').checked ? 1 : 0;

    console.log("🍔 Creating Item:", payload);

    try {
        const response = await fetch('https://localhost/A_project_forUniversity/src/api/menu_item/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (!response.ok) throw new Error(data || 'Failed to create item');

        // Success
        console.log("Item Created: you sure bro?");
        
        // Hide Modal (Bootstrap API)
        const modalEl = document.getElementById('addMenuModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        modalInstance.hide();
        
        form.reset();
        
        // Refresh the Grid
        loadMenuItems(); 

    } catch (error) {
        alert("Error: " + error.message);
    }
}

// Helper to open modal
function showAddMenuModal() {
    const modal = new bootstrap.Modal(document.getElementById('addMenuModal'));
    modal.show();
}