// ************************************************************************** //
//     File: js/app.js                                                        //
//     Author: atucci <atucci@student.42.fr>                                  //
//     Created: 2026/01/22 14:50:42                                           //
//     Updated: 2026/01/22 14:50:43                                           //
//     System: Linux [e4r2p4.42roma.it]                                       //
//     Hardware: Intel Core i5-8600 | RAM: 15GB                               //
// ************************************************************************** //

// This function first hide all the elements, then select and show the one with 
// viewId
function my_showView(viewId)
{
    // 1. Hide all views
    const views = document.querySelectorAll('.view');
    views.forEach(view => {
        view.classList.add('hidden');
        view.classList.remove('active');
    });

    // 2. Show the selected view
    const selectedView = document.getElementById('view-' + viewId);
    if (selectedView)
    {
        selectedView.classList.remove('hidden');
        selectedView.classList.add('active');
    }
}

//TODO: understand better this function
function my_navigateTo(viewId)
{
    // 1. Change the visual content (call your showView logic)
    my_showView(viewId);    
    // 2. "Fake" the URL update
    // param 1: state object (data to save for later)
    // param 2: title (mostly unused)
    // param 3: the new URL to show in the bar
    history.pushState({ id: viewId }, null, `/${viewId}`);
}

// This function is used to prevent the redirection to the href='#';
// I could have used the 'return false;' inline in js but this is clear;
function my_handleNav(event, viewId)
{
    event.preventDefault();     
    my_navigateTo(viewId);
}

// Listen for the back/forward button click
window.addEventListener('popstate', (event) => {
    // If we have saved state (viewId), show that view
    if (event.state && event.state.id)
    {
        showView(event.state.id);
    }
    else
    {
        // Default to home if no state exists
        showView('home');
    }
});

// Default: Run this when page loads
document.addEventListener('DOMContentLoaded', () => {
    // Ensure Home is visible
    showView('home'); 
    console.log("App loaded successfully!");
});

