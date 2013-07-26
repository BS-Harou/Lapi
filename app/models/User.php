<?php


class Settings {
	public $start_page = 'menu';
	public $right_corner = false; // false = id, true = time
	public $show_spoilers = false;
	public $hide_avatars = false;
	public $old_style = false;
	public $hide_old_images = false;
	public $new_post_color = false; //'#f0f0fe'; (when false, the default CSS is used)
	public $linkify = false;
	public function isInvalid() {
		if ($this->start_page && !in_array($this->start_page, array('menu', 'nove', 'oblibene'))) {
			return "Stránka po přihlášení je neplatná";
		}

		$this->right_corner = !!$this->right_corner;
		$this->show_spoilers = !!$this->show_spoilers;
		$this->hide_avatars = !!$this->hide_avatars;
		$this->old_style = !!$this->old_style;
		$this->hide_old_images = !!$this->hide_old_images;
		$this->linkify = !!$this->linkify;

		if ($this->new_post_color && !isValidCSSColor($this->new_post_color)) {
			return 'Formát barvy je chybný';
		}

		return false;
	}
}

class User {
	public $nick;
	public $settings;
	public $inDatabase = false;
	public function __construct($nick=NULL) {
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
		global $app;
		if (!isset($_SESSION['settings_start_page'])) {
		
			$result = $app->database->query('SELECT * FROM user_settings WHERE nick="' . $this->nick . '" LIMIT 1');
			if (!$result || $result->num_rows == 0) {
				return $this->settings;
			} else {
				$this->inDatabase = true;
			}

			$dbSettings = $result->fetch_object(); 

			foreach($this->settings as $key => $value) {
				$this->settings->{$key} = isset($dbSettings->{$key}) ? $dbSettings->{$key} : $value;
			}


			return $this->settings;

		} else {
			foreach($this->settings as $key => $value) {
				$this->settings->{$key} = isset($_SESSION['settings_' . $key]) ? $_SESSION['settings_' . $key] : $value;
			}
		}

		return $this->settings;
	}

	public function setSettings($arr) {
		foreach($this->settings as $key => $value) {
			$arr[$key] = $arr[$key] === 'true' ? true : $arr[$key];
			$arr[$key] = $arr[$key] === 'false' ? false : $arr[$key];
			$this->settings->{$key} = $arr[$key];
		}
	}

	public function saveSettings() {
		global $app;
		$r = $this->settings->isInvalid();
		if ($r != false) {
			return $r;
		}

		$result = $app->database->query('SELECT * FROM user_settings WHERE nick="' . $this->nick . '" LIMIT 1');
		if ($result && $result->num_rows === 1) {
			$this->inDatabase = true;
		}

		$sqlStr = '';
		foreach ($this->settings as $key => $value) {
			if ($this->inDatabase) {
				$sqlStr .= $key . '="' . $value . '",';
			} else {
				$sqlStr .= '"' . $value . '",';
			}
		}
		$sqlStr = preg_replace('/,$/', '', $sqlStr);

		if ($this->inDatabase) {
			$app->database->query('UPDATE user_settings SET ' . $sqlStr . ' WHERE nick="' . $this->nick . '"');
		} else {
			$r = $app->database->query('INSERT INTO user_settings VALUES("' . $this->nick . '", ' . $sqlStr . ')');
		}

		foreach($this->settings as $key => $value) {
			$_SESSION['settings_' . $key] = $value;
		}

		return false; // false = no error
		
	}
}
