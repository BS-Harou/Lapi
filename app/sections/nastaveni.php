<?php

include_once('app/models/User.php');
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
		$user = new User();
		$user->setSettings($_POST);
		$params->errorMsg = $user->saveSettings();
	}

	$app->redirect('nastaveni');
}

render('nastaveni', $params);
