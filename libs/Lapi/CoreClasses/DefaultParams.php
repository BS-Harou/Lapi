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
		return isset($_SESSION['lapi_lopuch']);
	}

	public function BASE_URL() {
		return 'http://' . $_SERVER['SERVER_NAME'];
	}

	public function SETTINGS_START_PAGE() {
		return $this->getSettings('start_page');
	}

	public function SETTINGS_HIDE_AVATARS() {
		return $this->getSettings('hide_avatars');
	}

	public function SETTINGS_RIGHT_CORNER() {
		return $this->getSettings('right_corner');
	}

	public function SETTINGS_SHOW_SPOILERS() {
		return $this->getSettings('show_spoilers');
	}

	public function SETTINGS_OLD_STYLE() {
		return $this->getSettings('old_style');
	}

	public function SETTINGS_NEW_POST_COLOR() {
		return $this->getSettings('new_post_color');
	}

	public function SETTINGS_HIDE_OLD_IMAGES() {
		return $this->getSettings('hide_old_images');
	}

	public function SETTINGS_LINKIFY() {
		return $this->getSettings('linkify');
	}
}