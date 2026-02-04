// ************************************************************************** //
//     File: js/app.js                                                        //
//     Author: atucci <atucci@student.42.fr>                                  //
//     Created: 2026/01/22 14:50:42                                           //
//     Updated: 2026/01/22 14:50:43                                           //
//     System: Linux [e4r2p4.42roma.it]                                       //
//     Hardware: Intel Core i5-8600 | RAM: 15GB                               //
// ************************************************************************** //

document.addEventListener('DOMContentLoaded', function()
{
    console.log('As-Porto App initialized!');
    updateRoleDisplay('customer');
    // Check session immediately
    checkSession(); 
    // Keyboard shortcut (Escape to home)
    document.addEventListener('keydown', function(event)
	{
        if (event.key === 'Escape') resetToHome();
    });
});