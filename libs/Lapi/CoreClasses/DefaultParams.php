<?php

class DefaultParams {
	private function getSettings($str) {
		global $app;
		return isset($_SESSION[$str]) ? $_SESSION[$str] : $app->user->settings->get($str);
	}

	public function DIR_STATIC() {
		global $app;
		return $app->dirStatic;
	}

	public function IS_LOGGED() {
		return isset($_SESSION['lapi_lopuch']) && strlen($_SESSION['lapi_lopuch']) > 0;
	}

	public function INFO_USERNAME() {
		return $_SESSION['info_username'];
	}

	public function INFO_AVATAR() {
		return $_SESSION['info_avatar'];
	}

	public function INFO_MESSAGES() {
		return $_SESSION['info_messages'];
	}

	public function BASE_URL() {
		global $app;
		return 'http://' . $_SERVER['SERVER_NAME'] . $app->getRealRoot();
	}

	public function __get($name) {
		if (!preg_match("/^SETTINGS_/", $name)) {
			return false;
		}
		$val = preg_replace("/^SETTINGS_/", '', strtolower($name));
		return $this->getSettings($val);
	}

	public function __isset($name) {
		if (!preg_match("/^SETTINGS_/", $name)) {
			return false;
		}
		$val = preg_replace("/^SETTINGS_/", '', strtolower($name));
		return $val != NULL;
	}
}