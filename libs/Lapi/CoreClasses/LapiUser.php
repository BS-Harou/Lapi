<?php
class LapiUser {
	public $nick = NULL;
	public $settings;
	public function __construct($nick=NULL) {
		global $app;
		require_once($app->dirModels . '/Settings.php');
		$this->settings = new Settings();
		if ($nick != NULL) {
			$this->nick = stripString($nick);
			return;
		}

		if (!isset($_SESSION['lapi_user'])) return;
		$this->nick = $_SESSION['lapi_user'];
		$this->getSettings();
	}

	public function getSettings() {
		$this->settings->set('nick', $this->nick);

		if (!isset($_SESSION['settings_start_page'])) {
			$this->settings->fetch();
			$this->settingsToSession();
		} else {
			foreach($this->settings->attributes as $key => $value) {
				$this->settings->set($key, isset($_SESSION['settings_' . $key]) ? $_SESSION['settings_' . $key] : $value);
			}
		}

		return $this->settings;
	}

	public function setSettings($arr) {
		foreach($this->settings->attributes as $key => $value) {
			$arr[$key] = $arr[$key] === 'true' ? true : $arr[$key];
			$arr[$key] = $arr[$key] === 'false' ? false : $arr[$key];
			if (array_key_exists($key, $arr) && $key != $this->settings->idAttribute) {
				$this->settings->set($key, $arr[$key]);
			}
		}
	}

	public function saveSettings() {

		if (!$this->settings->save()) {
			return $settings->validationError;
		}

		$this->settingsToSession();

		return false; // false = no error, neccesary for Mustache	
	}

	public function settingsToSession() {
		foreach($this->settings->attributes as $key => $value) {
			$_SESSION['settings_' . $key] = $value;
		}
	}
}