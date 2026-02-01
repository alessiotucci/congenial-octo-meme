// ************************************************************************** //
//     File: js/app.js                                                        //
//     Author: atucci <atucci@student.42.fr>                                  //
//     Created: 2026/01/22 14:50:42                                           //
//     Updated: 2026/01/22 14:50:43                                           //
//     System: Linux [e4r2p4.42roma.it]                                       //
//     Hardware: Intel Core i5-8600 | RAM: 15GB                               //
// ************************************************************************** //
/* ============================================================================
   WHEELIE GOOD FOOD - REGISTRATION JAVASCRIPT
   ============================================================================
   
   This file handles:
   1. Single Page Application (SPA) navigation (switching between views)
   2. Form validation and submission
   3. Dynamic role-based styling (colors, icons, messages)
   4. User interaction (password visibility toggle, form events)
   
   KEY CONCEPTS FOR BEGINNERS:
   - DOM: Document Object Model (the HTML structure JavaScript can manipulate)
   - Event Listeners: Code that "listens" for user actions (click, change, submit)
   - Data Attributes: HTML attributes like data-role that store information
   - CSS Variables: --role-primary, --role-secondary (colors that can change)
   
   ============================================================================ */

/* ============================================================================
   1. ROLE CONFIGURATION OBJECT
   ============================================================================
   
   This object stores all the data for each role (Customer, Food Place, Rider).
   
   WHY USE AN OBJECT?
   - Keeps all role information in one place
   - Easy to add/remove roles
   - Single source of truth
   
   Each role has:
   - icon: FontAwesome icon class (changes the emoji/symbol)
   - title: Display name for the role
   - message: Welcome message (changes when user selects role)
   - subtext: Supporting message
   
   EXAMPLE FLOW:
   User clicks "Food Place" in dropdown
   → JavaScript looks up roleConfig.food_place
   → Updates HTML with icon, title, message, subtext
   → CSS applies the color gradient
   → All happens instantly (no page reload!)
*/

const roleConfig = {
    customer: {
        icon: 'fas fa-user-tie',           // Icon CSS class from Font Awesome
        title: 'Customer',                 // Role name display
        message: 'Tired of unfair delivery fees?',           // Main welcome message
        subtext: 'Join thousands eating better, paying less.',  // Supporting text
        color: '#6B4BA0'                   // Backup color (CSS variables are primary)
    },
    food_place: {
        icon: 'fas fa-utensils',           // Fork and spoon icon
        title: 'Food Place',
        message: 'Your digital menu, your rules',
        subtext: 'Grow your business with fair fees.',
        color: '#E67E22'
    },
    rider: {
        icon: 'fas fa-motorcycle',         // Motorcycle icon
        title: 'Delivery Driver',
        message: 'Your time, your pay',
        subtext: 'We don\'t take a cut. We help you connect.',
        color: '#27AE60'
    }
};

/* ============================================================================
   2. STATE MANAGEMENT
   ============================================================================
   
   These variables track the current state of the app.
   
   WHY TRACK STATE?
   - We need to remember which role user selected
   - We need to remember which view is currently displayed
   - If we want to go back to Step 1, we need to know what role was selected
   
   In a real app with backend, this would come from a server or database.
   For now, it's stored in JavaScript memory (resets on page reload).
*/

let currentState = {
    currentView: 'home',           // Which section is visible? ('home', 'step1', 'step2')
    selectedRole: 'customer',      // Which role did user choose?
    userEmail: null,               // Email they entered (would be sent to server)
    userPassword: null             // Password (would be hashed and sent to server)
};

/* ============================================================================
   3. VIEW MANAGEMENT (Single Page App Navigation)
   ============================================================================
   
   In a Single Page Application (SPA), we DON'T reload the page.
   Instead, we:
   - Hide all views
   - Show only the view the user wants to see
   - Use CSS transitions for smooth appearance
   
   This function does that work. It's called whenever we want to switch views.
   
   PARAMETERS:
   - viewName: The ID of the section to show ('view-home', 'view-registration-step1', etc.)
   
   WHAT IT DOES:
   1. Find all elements with class "view"
   2. Remove the "active" class from all (hide them)
   3. Add "active" class to the view we want (show it)
   4. Remove "hidden" class from that view
   5. Update our state to track which view is active
   
   EXAMPLE:
   switchView('view-registration-step1') 
   → Shows the registration form
   → Hides home view
   → Updates currentState.currentView
*/

function switchView(viewName) {
    // STEP 1: Get all elements with class "view" (all our sections)
    const allViews = document.querySelectorAll('.view');
    
    // STEP 2: Loop through each view and hide it
    allViews.forEach(view => {
        view.classList.remove('active');   // Remove "active" class
        view.classList.add('hidden');      // Add "hidden" class
    });
    
    // STEP 3: Find the view we want to show
    const targetView = document.getElementById(viewName);
    
    // STEP 4: Show only that view
    if (targetView) {
        targetView.classList.remove('hidden');  // Remove "hidden"
        targetView.classList.add('active');     // Add "active" (triggers CSS animation)
    }
    
    // STEP 5: Remember which view is active (update our state)
    currentState.currentView = viewName;
    
    // OPTIONAL: Scroll to top of page (so user sees the new view)
    window.scrollTo(0, 0);
    
    console.log(`📍 Switched to view: ${viewName}`);
}

/* ============================================================================
   4. UPDATING ROLE DISPLAY (The Magic Button)
   ============================================================================
   
   THIS IS THE KEY FUNCTION FOR ROLE-BASED STYLING!
   
   When user selects a role from the dropdown, this function:
   - Changes the card's data-role attribute (triggers CSS color changes)
   - Updates the icon
   - Updates the welcome message
   - Updates the role badge
   - Animates the transition
   
   THE REASON THIS WORKS:
   - HTML has: <div class="registration-card" id="registrationCard" data-role="customer">
   - CSS has: [data-role="customer"] { --role-primary: #6B4BA0; } etc.
   - When JS changes data-role, CSS automatically updates colors
   - This is called "reactive" design (change data, UI updates automatically)
   
   PARAMETERS:
   - role: The selected role ('customer', 'food_place', or 'rider')
   
   FLOW EXAMPLE (User selects "Rider"):
   1. updateRoleDisplay('rider') is called
   2. Look up roleConfig.rider
   3. Get the HTML elements we need to update
   4. Change the icon <i> tag
   5. Change the h2 welcome message
   6. Change the role badge text
   7. Change the card's data-role attribute (this triggers CSS color change!)
   8. Update our state (remember which role was selected)
*/

function updateRoleDisplay(role) {
    // STEP 1: Validate that the role exists in our config
    if (!roleConfig[role]) {
        console.error(`❌ Role '${role}' not found in roleConfig`);
        return;
    }
    
    // STEP 2: Get the role's configuration data
    const config = roleConfig[role];
    
    // STEP 3: Get the HTML elements we need to update
    // querySelectorAll returns a "NodeList" (like an array of HTML elements)
    const roleIcon = document.getElementById('roleIcon');
    const welcomeMessage = document.getElementById('welcomeMessage');
    const welcomeSubtext = document.getElementById('welcomeSubtext');
    const roleBadge = document.getElementById('roleBadge');
    const registrationCard = document.getElementById('registrationCard');
    
    // STEP 4: Update the icon
    // className replaces ALL classes, so we preserve 'fas' and 'fa-3x'
    roleIcon.className = `${config.icon} fa-3x`;
    
    // STEP 5: Update the welcome message (the big text)
    welcomeMessage.textContent = config.message;
    
    // STEP 6: Update the supporting text
    welcomeSubtext.textContent = config.subtext;
    
    // STEP 7: Update the role badge ("Customer", "Food Place", etc.)
    roleBadge.textContent = config.title;
    
    // STEP 8: Update the card's data-role attribute
    // This is crucial! The CSS uses [data-role="..."] selectors
    // When we change this, CSS color variables update automatically
    registrationCard.setAttribute('data-role', role);
    
    // STEP 9: Update our state (remember which role is selected)
    currentState.selectedRole = role;
    
    // STEP 10: Log this action (helpful for debugging)
    console.log(`✨ Role updated to: ${role}`);
}

/* ============================================================================
   5. PASSWORD VISIBILITY TOGGLE
   ============================================================================
   
   When user clicks the eye icon next to password, show/hide the password.
   
   HOW IT WORKS:
   1. Get the password input element
   2. Get the icon element
   3. Check if password is currently hidden (type="password")
   4. If hidden: change to type="text" and show eye-slash icon
   5. If visible: change back to type="password" and show eye icon
   
   EXAMPLE:
   User clicks the eye icon
   → togglePasswordVisibility() is called
   → Input type changes from "password" to "text"
   → Password becomes visible
   → Icon changes from eye to eye-slash
   
   User clicks again
   → Input type changes back to "password"
   → Password is hidden again
   → Icon changes back to eye
*/

function togglePasswordVisibility() {
    // STEP 1: Get the password input field
    const passwordInput = document.getElementById('password');
    
    // STEP 2: Get the icon element
    const toggleIcon = document.getElementById('toggleIcon');
    
    // STEP 3: Check current password visibility
    if (passwordInput.type === 'password') {
        // Password is currently hidden → Show it
        passwordInput.type = 'text';           // Change input type
        toggleIcon.classList.remove('fa-eye'); // Remove eye icon
        toggleIcon.classList.add('fa-eye-slash');  // Add eye-slash icon
    } else {
        // Password is currently visible → Hide it
        passwordInput.type = 'password';       // Change back
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

/* ============================================================================
   6. FORM SUBMISSION (Step 1)
   ============================================================================
   
   When user fills the registration form and clicks "Create Account",
   this function is called.
   
   REMEMBER: We're not sending to a server yet (frontend only).
   So this just:
   - Gets the form data
   - Does some simple validation
   - Shows Step 2
   - Remembers their information in currentState
   
   IN A REAL APP:
   - We would use AJAX to send data to PHP backend
   - Backend would validate and create the user
   - Backend would return success/error
   - We'd show Step 2 only if backend says "success"
   
   PARAMETERS:
   - event: The form submission event (tells JavaScript to not reload the page)
   
   WHY event.preventDefault()?
   - Default form behavior is to reload the page
   - We're a Single Page App, so we DON'T want to reload
   - preventDefault() stops the page reload
*/

function handleStep1Submit(event) {
    // STEP 1: Prevent default form submission (which would reload page)
    event.preventDefault();
    
    // STEP 2: Get the form data from user input
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const role = document.getElementById('role').value;
    
    // STEP 3: Basic validation (in real app, backend does this)
    if (!email || !password || !role) {
        alert('❌ Please fill in all fields');
        return;  // Exit function early
    }
    
    // STEP 4: More validation (email format)
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('❌ Please enter a valid email address');
        return;
    }
    
    // STEP 5: More validation (password length)
    if (password.length < 8) {
        alert('❌ Password must be at least 8 characters');
        return;
    }
    
    // STEP 6: If all validation passes, save to state
    currentState.userEmail = email;
    currentState.userPassword = password;  // In real app, never store plain password
    currentState.selectedRole = role;
    
    // STEP 7: Log the form data (debugging)
    console.log('📝 Form submitted with:', {
        email: email,
        role: role,
        // Don't log password in real apps!
    });
    
    // STEP 8: Show Step 2 (this switches views)
    switchView('view-registration-step2');
    
    // STEP 9: Update Step 2 card to match selected role (colors, icon, etc.)
    updateStep2Display(role);
    
    // STEP 10: Show success message (optional animation/notification)
    console.log('✅ Step 1 complete! Moving to Step 2...');
}

/* ============================================================================
   7. UPDATE STEP 2 DISPLAY
   ============================================================================
   
   After user completes Step 1, we need to update Step 2 card.
   The Step 2 card should match the role selected in Step 1
   (same colors, same role badge, etc.)
   
   PARAMETERS:
   - role: The role user selected ('customer', 'food_place', 'rider')
*/

function updateStep2Display(role) {
    // Get the Step 2 card element
    const step2Card = document.getElementById('step2Card');
    
    // Get the config for this role
    const config = roleConfig[role];
    
    // Update the card's data-role attribute (this changes colors)
    step2Card.setAttribute('data-role', role);
    
    // Find the form container where Step 2 form will load
    const formContainer = document.getElementById('step2FormContainer');
    
    // Clear any previous form
    formContainer.innerHTML = '';
    
    // Add role-specific message (placeholder for now)
    formContainer.innerHTML = `
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>${config.title} Setup:</strong><br>
            Step 2 form for ${config.title} will appear here.
        </div>
        <p class="text-center text-muted">
            We'll collect the details specific to ${config.title}s soon!
        </p>
    `;
    
    console.log(`🎨 Step 2 updated for role: ${role}`);
}

/* ============================================================================
   8. NAVIGATION FUNCTIONS
   ============================================================================
   
   These are simple helpers to navigate between views.
   They're called from HTML buttons via onclick attributes.
   
   WHY SEPARATE FUNCTIONS?
   - Makes HTML cleaner (onclick="goToRegistration()" is cleaner than inline JS)
   - Easier to test
   - Easier to add logic later (animations, validation, etc.)
   
   EXAMPLES:
   - goToRegistration() → Show Step 1 form
   - backToStep1() → Go from Step 2 back to Step 1
   - resetToHome() → Go back to home page
*/

// Navigate to Registration Step 1
function goToRegistration(event) {
    if (event) event.preventDefault();  // Prevent default link behavior
    switchView('view-registration-step1');
}

// Go back from Step 2 to Step 1
function backToStep1(event) {
    if (event) event.preventDefault();
    switchView('view-registration-step1');
}

// Reset to home page (used by "Back to Home" buttons)
function resetToHome(event) {
    if (event) event.preventDefault();
    switchView('view-home');
    
    // Optional: Reset the form (clear inputs)
    document.getElementById('registrationForm').reset();
    
    // Optional: Reset role back to default
    updateRoleDisplay('customer');
}

/* ============================================================================
   9. INITIALIZATION (Run when page loads)
   ============================================================================
   
   The DOMContentLoaded event fires when the HTML is fully loaded.
   We use this to:
   - Run any setup code
   - Add event listeners
   - Initialize the page with default values
   
   WHY NOT JUST RUN CODE AT TOP OF FILE?
   - If code runs before HTML loads, elements won't exist yet
   - querySelector() would return null (element not found)
   - DOMContentLoaded ensures elements exist before we interact with them
   
   In this app, we don't have much initialization needed.
   But this is where we'd add it (analytics, load saved data, etc.)
*/

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 App initialized!');
    
    // Set initial role display (shows Customer by default)
    updateRoleDisplay('customer');
    
    // Log initial state (helpful for debugging)
    console.log('📊 Initial state:', currentState);
    
    // Optional: Add keyboard shortcut (Press Escape to go home)
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            resetToHome();
        }
    });
});

/* ============================================================================
   10. HELPER FUNCTIONS & UTILITIES
   ============================================================================
   
   Small utility functions that help with common tasks.
*/

/**
 * Log a message with timestamp (for debugging)
 * 
 * USAGE: logWithTime('User registered successfully')
 * OUTPUT: [13:45:32] User registered successfully
 */
function logWithTime(message) {
    const time = new Date().toLocaleTimeString();
    console.log(`[${time}] ${message}`);
}

/**
 * Validate email format
 * 
 * USAGE: if (isValidEmail('test@example.com')) { ... }
 * RETURNS: true if valid, false otherwise
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Get the current selected role
 * 
 * USAGE: let role = getCurrentRole()
 * RETURNS: 'customer', 'food_place', or 'rider'
 */
function getCurrentRole() {
    return currentState.selectedRole;
}

/**
 * Reset form to empty
 * 
 * USAGE: clearForm()
 * EFFECT: Clears all input fields
 */
function clearForm() {
    const form = document.getElementById('registrationForm');
    if (form) {
        form.reset();
    }
}

/* ============================================================================
   11. DEVELOPMENT NOTES FOR FUTURE
   ============================================================================
   
   When we're ready to add the backend:
   
   1. AJAX INTEGRATION:
      - Replace handleStep1Submit() with async function
      - Use fetch() or XMLHttpRequest to send data to PHP
      - Example: 
        fetch('/api/auth/register', {
            method: 'POST',
            body: JSON.stringify({ email, password, role })
        })
      
   2. STEP 2 FORMS:
      - Add different form HTML for each role
      - Show/hide based on currentState.selectedRole
      - Load entity-specific forms (Customer form, FoodPlace form, etc.)
      
   3. ERROR HANDLING:
      - Show error messages if registration fails
      - Display server-side validation errors
      
   4. SUCCESS:
      - Show success message
      - Redirect to dashboard or next page
      
   5. SESSION MANAGEMENT:
      - Store user token in localStorage (after login)
      - Send token with future API requests
      - Log user out when token expires
   
   ============================================================================ */