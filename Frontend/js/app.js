// ************************************************************************** //
//     File: app.js                                                           //
//     Author: atucci <atucci@student.42.fr>                                  //
//     Created: 2026/01/22 13:28:22                                           //
//     Updated: 2026/01/22 13:28:39                                           //
//     System: Linux [e4r2p4.42roma.it]                                       //
//     Hardware: Intel Core i5-8600 | RAM: 15GB                               //
// ************************************************************************** //

// A simple SPA Router
function showView(viewId) {
    // 1. Hide all views
    const views = document.querySelectorAll('.view');
    views.forEach(view => {
        view.classList.add('hidden');
        view.classList.remove('active');
    });

    // 2. Show the selected view
    const selectedView = document.getElementById('view-' + viewId);
    if (selectedView) {
        selectedView.classList.remove('hidden');
        selectedView.classList.add('active');
    }
}

// Default: Run this when page loads
document.addEventListener('DOMContentLoaded', () => {
    // Ensure Home is visible
    showView('home'); 
    console.log("App loaded successfully!");
});
