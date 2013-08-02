<?php
error_reporting(E_ALL - E_NOTICE); ini_set('display_errors', 'On'); 
//header('Content-Type: text/html; charset=utf-8');

/**
 * Sessions
 */

session_start();

/**
 * Templating library
 */

require_once('libs/Mustache/Autoloader.php');
Mustache_Autoloader::register();

/**
 * Database login
 */
require_once('db_login.php');

/**
 * Main Lapi Library
 */

require_once('libs/Lapi/init.php');

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
		$params = new DefaultParams();
		$app->redirect($app->user->settings->get('start_page'));
	}
} else {
	include($app->dirSections . '/login.php');
}


