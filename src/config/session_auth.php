<?php
/* ************************************************************************** */
/*     File: session_auth.php                                                 */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/02/03 17:01:35                                           */
/*     Updated: 2026/02/03 17:03:52                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

/* ************************************************************************ */
/* DESCRIPTION: Forces cookies to be secure and unreadable by JavaScript    */
/* ************************************************************************ */

function start_secure_session()
{
	if (session_status() === PHP_SESSION_NONE)
	{
		// 1. COOKIE SECURITY
		// Prevents JavaScript access (Stops XSS)
		ini_set('session.cookie_httponly', 1); 
		// Prevents sending cookie to other sites (Stops CSRF)
		ini_set('session.cookie_samesite', 'Strict'); 
		// Only allow HTTPS (Turn this off ONLY for localhost development)
		// ini_set('session.cookie_secure', 1); 
		// 2. START
		session_start();
		// 3. FIXATION PROTECTION
		// Change the ID every time to prevent "Session Fixation" attacks
		if (!isset($_SESSION['CREATED']))
		{
			$_SESSION['CREATED'] = time();
		}
		else if (time() - $_SESSION['CREATED'] > 1800)
		{
			// Restart session every 30 minutes
			session_regenerate_id(true);
			$_SESSION['CREATED'] = time();
		}
	}
}

// Helper to check if user is logged in
function require_auth()
{
	start_secure_session();
	if (!isset($_SESSION['user_id']))
	{
		http_response_code(401);
		echo json_encode(["message" => "Unauthorized. Please login."]);
		exit();
	}
}
?>
