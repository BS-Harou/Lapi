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

		set_session_info();

		return true;
	}

	return false;
}

function set_session_info() {
	global $app;
	$doc = getFile('http://www.lapiduch.cz/');
	$xpath = new DOMXpath($doc);

	$is_logged = $xpath->query('//td[@class="uziv"]//img');
	if (!$is_logged || $is_logged->length == 0) {
		$app->redirect('odhlasit');
	}

	$msg_count_ele = $xpath->query('//td[@class="uziv"]//font[@color="red"]');
	$_SESSION['info_messages'] = '';
	if ($msg_count_ele->length > 0) {
		$tmp = intval($msg_count_ele->item(0)->nodeValue);
		if ($tmp > 0) {
			$_SESSION['info_messages'] = "(" . $tmp . ")";
		} else {
			$_SESSION['info_messages'] = '';
		}
	}

	$avatar_ele = $xpath->query('//td[@class="uziv"]/img');
	$_SESSION['info_avatar'] = '';
	if ($avatar_ele->length > 0) {
		$_SESSION['info_avatar'] = $avatar_ele->item(0)->getAttribute('src');
	}

	$username_ele = $xpath->query('//td[@class="uziv"]/b');
	$_SESSION['info_username'] = '';
	if ($username_ele->length > 0) {
		$_SESSION['info_username'] = $username_ele->item(0)->nodeValue;
	}


	return 0;
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