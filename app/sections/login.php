<?php

/**
 * Define functions
 */
function login($user, $pass) {
	global $app;
	require_once($app->dirModels . '/User.php');

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


		$user = new User();
		$settings = $user->settings;
		if ($settings != NULL) {
			foreach ($settings as $key => $value) {
				$_SESSION['settings_' . $key] = $value;
			}
		}
		
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
	$app->redirect( $params->SETTINGS_START_PAGE() );
}

if (isset($_GET['odhlasit'])) {
	$params->error_msg = 'Byli jste odhlášeni.';
}

/**
 * Handle POST
 */
if (isset($_POST['user'])) {
	if (login($_POST['user'], $_POST['pass'])) {
		$app->redirect( $params->SETTINGS_START_PAGE() );
	} else {
		$params->error_msg = 'Nepodařilo se přihlásit.';
	}
}

/**
 * Render
 */
render('login', $params);