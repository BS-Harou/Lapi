<?php
error_reporting(E_ALL - E_NOTICE); ini_set('display_errors', 'On'); 
//header('Content-Type: text/html; charset=utf-8');

include('lib.php');

/**
 * "Router"
 */

if (isset($_GET['str'])) {
	$str = stripString($_GET['str']);	
} else {
	$str = '';
}

if (isset($_SESSION['lapi_lopuch']) && strlen($_SESSION['lapi_lopuch']) > 0) {
	if ($str) {
		$path = $app->dirSections . '/' . $str . '.php';
		if (file_exists($path)) {
			include($path);
		} else {
			render('e404');
		}
	} else {
		include($app->dirSections . '/menu.php');
	}
} else {
	include($app->dirSections . '/login.php');
}


