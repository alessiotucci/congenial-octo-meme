// ************************************************************************** //
//                                                                        //
//   Host: e4r2p4.42roma.it                                               //
//   File: app.js                                                         //
//   Created: 2026/01/22 13:02:33 | By: atucci <atucci@student.42.fr>     //
//   Updated: 2026/01/22 13:02:39                                         //
//   OS: Linux 6.5.0-44-generic x86_64 | CPU: Intel(R) Core(TM) i5-8600 CPU @ 3.10GHz | Mem: 16243476 kB    //
//                                                                        //
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
