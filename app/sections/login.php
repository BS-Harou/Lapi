<?php

/**
 * Define functions
 */
function login($user, $pass) {
	global $app;

	$postdata = http_build_query(array('user' => $user, 'pass' => $pass));


	$ch = curl_init('http://www.lapiduch.cz/log.php');
	curl_setopt($ch, CURLOPT_POST, 2);  // 2 = number of request vars
	curl_setopt($ch, CURLOPT_HEADER, true); // include headers in output
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$my_headers = curl_exec($ch);    
	curl_close($ch);

	$my_headers = preg_split("/\n\r?/", trim($my_headers));

	$tmp1 = explode('=', $my_headers[4]);

	if (count($tmp1) == 2 && trim($tmp1[1]) != 'book') {   
		$_SESSION['lapi_lopuch'] = trim($tmp1[1]);
		$tmp2 = explode('=', $my_headers[5]);
		$_SESSION['lapi_user'] = trim($tmp2[1]);

		$app->user->nick = $_SESSION['lapi_user'];
		$app->user->getSettings();
		
		return true;
	}

	return false;   
}

/**
 * Params
 */

class Params extends DefaultParams {
	public $error_msg = false;
}

$params = new Params();

/**
 * Move menu if logged
 */

if ($params->IS_LOGGED()) {
	$app->redirect( $app->user->settings->get('start_page') );
}

if (isset($_GET['odhlasit'])) {
	$params->error_msg = 'Byli jste odhlášeni.';
}

/**
 * Handle POST
 */
if (isset($_POST['user'])) {
	if (login($_POST['user'], $_POST['pass'])) {
		$app->redirect( $app->user->settings->get('start_page') );
	} else {
		$params->error_msg = 'Nepodařilo se přihlásit.';
	}
}

/**
 * Render
 */
render('login', $params);