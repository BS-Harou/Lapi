<?php


class Settings extends LapiModel {
	public $db_table = 'user_settings';
	public $idAttribute = 'nick';

	public $defaults = array(
		'nick' => NULL,
		'start_page' => 'menu',
		'right_corner' => false, // false = id, true = time
		'show_spoilers' => false,
		'hide_avatars' => false,
		'old_style' => false,
		'hide_old_images' => false,
		'new_post_color' => false, //'#f0f0fe'; (when false, the default CSS is used)
		'linkify' => false,
		'slideout' => false
	);

	public function validate() {
		if (!in_array($this->get('start_page'), array('menu', 'nove', 'oblibene'))) {
			return 'Stránka po přihlášení je neplatná';
		}

		$this->toBool('right_corner');
		$this->toBool('show_spoilers');
		$this->toBool('hide_avatars');
		$this->toBool('old_style');
		$this->toBool('hide_old_images');
		$this->toBool('linkify');

		if (trim($this->get('new_post_color')) == '') {
			$this->set('new_post_color', false);
		} else if ($this->get('new_post_color') && !isValidCSSColor($this->get('new_post_color'))) {
			return 'Formát barvy je chybný';
		}

		return false;
	}

	public function toBool($prop) {
		$this->set($prop, !!$this->get($prop));
	}
}

