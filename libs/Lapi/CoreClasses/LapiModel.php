<?php



class LapiModel {
	public $attributes = array();
	public $idAttribute =  'id';
	public $defaults = array();
	public $cid = '';
	public $validationError = '';
	public $db_table;
	public function __construct($data=NULL) {
		if ($data != NULL) {
			foreach ($data as $key => $value) {
				$this->set($key, $value);
			}
		}

		//$this->cid = hash();
		$this->fillDefaults();

		if (method_exists($this, 'initialize')) {
			$this->initialize();
		}
	}
	public function fillDefaults() {
		foreach ($this->defaults as $key => $value) {
			if (!$this->has($key)) {
				$this->set($key, $value);
			}
		}
	}
	public function get($index) {
		return $this->has($index) ? $this->attributes[$index] : NULL;
	}
	public function escape($index) {
		return $this->has($index) ? htmlspecialchars($this->attributes[$index]) : NULL;
	}
	public function set($index, $value) {
		return $this->attributes[$index] = $value;
	}
	public function remove($index) {
		unset($this->attributes[$index]);
	}
	public function has($index) {
		return isset($this->attributes[$index]);
	}
	public function clear() {
		$this->attributes = array();
	}
	public function getId() {
		return $this->get($this->idAttribute);
	}
	public function setId($value) {
		return $this->set($this->idAttribute, $value);
	}
	public function save() {
		global $app;
		if (!isset($this->db_table)) {
			$this->validationError = 'MySQL error! Missing table name.';
			return false;
		}

		if (!$this->isValid()) {
			return false;
		}

		$is_new = $this->isNew();
		$sqlStringA = '';
		$sqlStringB = '';

		foreach ($this->attributes as $key => $value) {
			if ($is_new) {
				$sqlStringA .= $app->database->escape($key) . ',';
				$sqlStringB .= '"' . $app->database->escape($value) . '",';
			} else {
				$sqlStringA .= $app->database->escape($key) . '="' . $app->database->escape($value) . '",';
			}
		}

		$sqlStringA = preg_replace('/,$/', '', $sqlStringA);
		$sqlStringB = preg_replace('/,$/', '', $sqlStringB);

		if ($is_new) {
			$q = 'INSERT INTO ' . $this->db_table . ' (' . $sqlStringA . ') VALUES(' . $sqlStringB . ')';
		} else {
			$q = 'UPDATE ' . $this->db_table . ' SET ' . $sqlStringA . ' WHERE ' . $this->idAttribute . '="' . $app->database->escape($this->getId()) . '" LIMIT 1';
		}

		$rt = !!@$app->database->query($q);

		if (!$rt) {
			throw new Exception('LapiModel Error. Can\'t save model to MySQL');
		}

		return true;
	}
	public function fetch() {
		global $app;
		if (!isset($this->db_table)) {
			return false;
		}

		$q = 'SELECT * FROM ' . $this->db_table . ' WHERE ' . $this->idAttribute . '="' . $app->database->escape($this->getId()) . '" LIMIT 1';

		$rt = $app->database->query($q);

		if (!$rt || $rt->num_rows == 0) {
			return false;
		}

		$data = $rt->fetch_object();

		foreach ($data as $key => $value) {
			$this->set($key, $value);
		}
		
		return true;
	}
	public function destroy($attr) {
		global $app;
		if (!isset($this->db_table) || !$this->getId()) {
			return false;
		}

		$q = 'DELETE FROM ' . $this->db_table . ' WHERE ' . $this->idAttribute . '="' . $app->database->escape($this->getId()) . '"';
		if (isset($attr['where'])) $q .= ' AND ' . $attr['where'];
		$q .= ' LIMIT 1';

		$rt = !!@$app->database->query($q);

		if (!$rt) {
			throw new Exception('LapiModel Error. Can\'t remove model from MySQL');
		}

		return true;
	}
	public function validate() {
		return 0;
	}
	public function isValid() {
		$this->validationError = $this->validate();
		return !$this->validationError;
	}
	public function parse($store) {
		foreach($store as $key => $value) {
			$this->set($key, $value);
		}
	}
	public function isNew() {
		global $app;
		if ($this->getId() && strlen($this->getId()) > 0) {
			if (!isset($this->db_table)) {
				return false;
			}

			$q = 'SELECT 1 FROM ' . $this->db_table . ' WHERE ' . $this->idAttribute . '="' . ($app->database->escape($this->getId())) . '" LIMIT 1';

			$rt = $app->database->query($q);
			return (!$rt || $rt->num_rows === 0);
		} 
		return true;
	}
	public function pick($arr) {
		$rt = array();

		for ($i=0; $i < count($arr); $i++) {
			if ($this->has($arr[$i])) {
				$rt[ $arr[$i] ] = $this->get( $arr[$i] );
			}
		}

		return $rt;
	}
	public function omit($arr) {
		$rt = array();
		$keys = $this->keys();

		for ($i=0; $i < count($keys); $i++) {
			if (!in_array($keys[$i], $arr)) {
				$rt[ $keys[$i] ] = $this->get( $keys[$i] );
			}
		}
		
		return $rt;
	}
	public function keys() {
		$rt = array();
		foreach($this->attributes as $key => $value) {
			$rt[] = $key;
		}
		return $rt;
	}
	public function values() {
		$rt = array();
		foreach($this->attributes as $key => $value) {
			$rt[] = $value;
		}
		return $rt;
	}
	public function pairs() {
		$rt = array();
		foreach($this->attributes as $key => $value) {
			$rt[] = array($key, $value);
		}
		return $rt;
	}
}