<?php

class LapiCollection {
	public $model = 'LapiModel';
	public $models = array();
	public $db_table;

	public function __construct($attr=NULL) {
		if (method_exists($this, 'initialize')) {
			$this->initialize();
		}
		if (is_array($attr)) {
			$this->fetch($attr);
		}
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
			if ($this->models[$i]->getId() === $id) return $this->models[$i];
		}
		for ($i=0; $i<count($this->models); $i++) {
			if ($this->models[$i]->cid === $id) return $this->models[$i];
		}
	}

	public function at($index) {
		return $this->models[$index];
	}

	public function length() {
		return count($this->models);
	}

	public function fetch($attr=array()) {
		global $app;

		if (!isset($this->db_table)) {
			return false;
		}

		$rt = $app->db->select($this->db_table, $attr);

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

		$rt = $app->db->query('SELECT FOUND_ROWS() AS amount');

		if (!$rt) {
			return false;
		}

		$data = $rt->fetch_object();

		return $data->amount;
	}

	public function findWhere($attrs) {
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