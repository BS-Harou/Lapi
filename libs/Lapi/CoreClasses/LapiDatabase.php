<?php

class LapiDatabase {
	private $username;
	private $password;
	private $host;
	private $database;
	public  $mysqli;
	public  $isConnected = false;

	public function __construct($u, $p, $h, $d) {
		$this->username = $u;
		$this->password = $p;
		$this->host = $h;
		$this->database = $d;
	}

	public function query($q) {
		if (!$this->isConnected) {
			$this->realConnect();
		}
		return $this->mysqli->query($q);
	}

	public function dump() {
		var_dump($this->mysqli);
	}

	public function escape($str) {
		return mysql_escape_string($str);
	}

	private function realConnect() {
		$this->mysqli = new Mysqli($this->host, $this->username, $this->password, $this->database);
		$this->isConnected = $this->mysqli->connect_errno === 0 ? true : false;
	}
}