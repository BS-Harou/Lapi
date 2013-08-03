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
		if (is_array($obj)) {
			for ($i=0; $i<count($obj); $i++) $this->models[] = $obj[$i];
		} else if ($obj instanceof LapiModel) {
			$this->models[] = $obj;
		}
	}

	public function remove($obj) {
		if ($obj instanceof LapiModel) {
			for ($i=0; $i<count($this->models); $i++) {
				if ($this->models[$i] === $obj) {
					array_splice($this->models, $i, 1);
				}
			}
		} else if (is_array($obj)) {
			for ($i=0; $i<count($this->models); $i++) {
				if (in_array($this->models[$i], $obj)) {
					array_splice($this->models, $i, 1);
				}
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

	public function parse($arr) {
		return $arr;
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

		$final_data = array();

		while ($data = $rt->fetch_object()) {
			$final_data[] = $data;
		}

		$final_data = $this->parse($final_data);

		$this->models = array();

		for ($i=0; $i < count($final_data); $i++) {
			$this->models[] = new $this->model($final_data[$i]);
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

	public function pluck($str) {
		$rt = array();
		for ($i=0,$j=$this->length(); $i < $j; $i++) {
			$rt[] = $this->get($str);
		}
		return $rt;
	}

	public function where($attrs) {
		$rt = array();
		for ($i=0,$j=$this->length(); $i < $j; $i++) {
			foreach ($attrs as $key => $value) {
				if ($this->at($i)->get($key) != $attrs[$key]) {
					continue 2;
				}
			}
			$rt[] = $this->at($i);
		}
		return $rt;
	}

	public function findWhere($attrs) {
		for ($i=0,$j=$this->length(); $i < $j; $i++) {
			foreach ($attrs as $key => $value) {
				if ($this->at($i)->get($key) != $attrs[$key]) {
					continue 2;
				}
			}
			return $this->at($i);
		}
		return NULL;
	}

	public function create($attrs, $options=array()) {
		if (!isset($this->model) || !$this->model || !is_string($this->model) || strlen($this->model) == 0) {
			return NULL;
		}

		$model = new $this->model($attrs);
		$is_saved = $model->save();
		if (!is_array($options) || !isset($options['wait']) || $options['wait'] != true || $is_saved == true) {
			$this->add($model);
		}
		return $model;
	}
}