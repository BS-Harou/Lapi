<?php

function change_password($oldp, $newp, $againp) {
	$query = http_build_query(
		array(
			'oldpass' => iconv('utf-8', 'windows-1250', $oldp), 
			'newpass' => iconv('utf-8', 'windows-1250', $newp),
			'againpass' => iconv('utf-8', 'windows-1250', $againp),
		)
	);
	$ch = curl_init('http://www.lapiduch.cz/passwd.php');
	curl_setopt($ch, CURLOPT_COOKIE, 'lopuch=' . $_SESSION['lapi_lopuch'].'; user=' . $app->user->nick); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
	$source = curl_exec($ch);                 
}

class Params extends DefaultParams {
	public $errorMsg = false;
	public function is_selected() {
		$sp = $this->SETTINGS_START_PAGE();
		return function ($text) use ($sp) {
			if ($text === $sp) {
				return 'selected';
			} else {
				return '';
			}
		};
	}
}

$params = new Params();

if (isset($_POST['settings_type'])) {
	if ($_POST['settings_type'] == 'mobile') {
		$app->user->setSettings($_POST);
		$params->errorMsg = $app->user->saveSettings();
	} else if ($_POST['settings_type'] == 'pass') {
		if (!isset($_POST['newp']) || !$_POST['newp']) {
			$params->errorMsg = 'Nové heslo nesmí být prázdné';
		} else if ($_POST['newp'] != $_POST['againp']) {
			$params->errorMsg = 'Hesla se nerovnají';
		} else {
			change_password($_POST['oldp'], $_POST['newp'], $_POST['againp']);
		}
	}
}

render('nastaveni', $params);
