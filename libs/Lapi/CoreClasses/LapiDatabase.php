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

	public function select($table, $attr=array()) {

		$q = 'SELECT SQL_CALC_FOUND_ROWS * FROM `' . $this->escape($table) . '`';
		if (isset($attr['where'])) $q .= self::parseWhere($attr['where']);
		if (isset($attr['order'])) $q .= ' ORDER BY ' . $attr['order'];
		if (isset($attr['limit'])) $q .= ' LIMIT ' . $attr['limit'];


		return $this->query($q);
	}

	public function delete($table, $attr=array()) {
		$q = 'DELETE FROM `' . $this->escape($table) . '`';
		if (isset($attr['where'])) $q .= self::parseWhere($attr['where']);
		if (isset($attr['limit'])) $q .= ' LIMIT ' . $attr['limit'];

		return $this->query($q);
	}

	public function insert($table, $data, $attr=NULL) {
		$sqlStringA = '';
		$sqlStringB = '';

		foreach ($data as $key => $value) {			
			$sqlStringA .= '`' . $this->escape($key) . '`,';
			$sqlStringB .= '"' . $this->escape($value) . '",';
		}
		$sqlStringA = preg_replace('/,$/', '', $sqlStringA);
		$sqlStringB = preg_replace('/,$/', '', $sqlStringB);

		$q = 'INSERT INTO `' . $this->escape($table) . '` (' . $sqlStringA . ') VALUES(' . $sqlStringB . ')';

		return $this->query($q);
	}

	public function update($table, $data, $attr=NULL) {
		$sqlString = '';

		foreach ($data as $key => $value) {			
			$sqlString .= '`' . $this->escape($key) . '`="' . $this->escape($value) . '",';
		}
		$sqlString = preg_replace('/,$/', '', $sqlString);

		$q = 'UPDATE `' . $this->escape($table) . '` SET ' . $sqlString;
		if (isset($attr['where'])) $q .= self::parseWhere($attr['where']);
		if (isset($attr['limit'])) $q .= ' LIMIT ' . $attr['limit'];


		return $this->query($q);
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

	static function parseWhere($arr) {
		if (is_string($arr)) {
			return ' WHERE ' . $arr;
		} else if (!is_array($arr) || count($arr) == 0) {
			return;
		}

		global $app;
		$str = ' WHERE ';
		foreach ($arr as $key => $value)  {
			$str .= '`' . $app->db->escape($key) . '`="' . $app->db->escape($value) . '" AND ';
		}
		$str = preg_replace('/\sAND\s$/', '', $str);

		return $str;
	}
}