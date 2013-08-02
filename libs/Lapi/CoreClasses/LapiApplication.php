<?php

class LapiApplication {
	public $database;
	public $dirSections = 'app/sections';
	public $dirModels = 'app/models';
	public $dirTemplates = 'app/templates';
	public $dirStatic = './static';
	public $user;
	public function __construct() {
		$this->database = new LapiDatabase(DB_USER, DB_PASS, DB_HOST, DB_DB);
	}
	public function getRealRoot() {
		$r = $_SERVER['PHP_SELF'];
		$r = preg_replace("/index\.php$/", '', $r);
		$r = preg_replace("/[^\w\s\+%\/]/", '', $r);
		return $r;
	}
	public function redirect($section) {
		header('Location: ' . $this->getRealRoot() . $section);
		exit;
	}
}