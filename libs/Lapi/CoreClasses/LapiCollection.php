<?php

class LapiCollection {
	public $model = 'LapiModel';
	public $models = array();
	public $db_table;

	private function parseWhere($arr) {
		if (!is_array($arr) || count($arr) == 0) {
			return;
		}

		global $app;
		$str = ' WHERE ';
		foreach ($arr as $key => $value)  {
			$str .= $app->database->escape($key) . '="' . $app->database->escape($value) . '" AND ';
		}
		$str = preg_replace('/\sAND\s$/', '', $str);

		return $str;
	}

	public function add($obj) {
		// if instance of LapiModel
		$this->models[] = $obj;
	}

	public function remove($obj) {
		for ($i=0; $i<count($this->models); $i++) {
			if ($this->models[$i] === $obj) {
				unset($this->models[$i]);
				// move the array!
			}
		}
	}

	public function reset($arr) {
		$this->models = array();
		for ($i=0; $i<count($arr); $i++) {
			$this->add($arr[$i]);
		}
	}

	public function get($id) {
		for ($i=0; $i<count($this->models); $i++) {
			if ($this->models[$i]->getId() === $id || $this->models[$i]->cid === $id) {
				return $this->models[$i];
			}
		}
	}

	public function at($index) {
		return $this->models[$index];
	}

	public function length() {
		return count($this->models);
	}

	public function fetch($attr) {
		global $app;

		if (!isset($this->db_table)) {
			return false;
		}

		$q = 'SELECT SQL_CALC_FOUND_ROWS * FROM ' . $this->db_table;

		if (isset($attr['where'])) {
			if (is_array($attr['where'])) {
				$q .= $this->parseWhere($attr['where']);
			} else {
				$q .= ' WHERE ' . $attr['where'];
			}
		}	
		if (isset($attr['order'])) 	$q .= ' ORDER BY ' . $attr['order'];
		if (isset($attr['limit'])) 	$q .= ' LIMIT ' . $attr['limit'];

		$rt = $app->database->query($q);

		if (!$rt || $rt->num_rows === 0) {
			return false;
		}

		$this->models = array();

		while ($data = $rt->fetch_object()) {
			$this->models[] = new $this->model($data);
		}

		return true;
	}

	public function allRowsCount() {
		global $app;

		if (!isset($this->db_table)) {
			return false;
		}

		$rt = $app->database->query('SELECT FOUND_ROWS() AS amount');

		if (!$rt) {
			return false;
		}

		$data = $rt->fetch_object();

		return $data->amount;
	}

	public function firstWhere($attrs) {
		for ($i=0; $i < $this->length(); $i++) {
			foreach ($attrs as $key => $value) {
				if ($this->at($i)->get($key) != $attrs[$key]) {
					continue 2;
				}
			}
			return $this->at($i);
		}
		return NULL;
	}
}