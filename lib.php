<?php

/**
 * Sessions
 */

session_start();

/**
 * Templating library
 */

require_once('libs/Mustache/Autoloader.php');
Mustache_Autoloader::register();

/**
 * Database
 */

require_once('db_login.php');

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

	private function realConnect() {
		$this->mysqli = new Mysqli($this->host, $this->username, $this->password, $this->database);
		$this->isConnected = $this->mysqli->connect_errno === 0 ? true : false;
	}
}

/**
 * App
 */
class LapiApplication {
	public $database;
	public $dirSections = 'app/sections';
	public $dirModels = 'app/models';
	public $dirTemplates = 'app/templates';
	public function __construct() {
		$this->database = new LapiDatabase(DB_USER, DB_PASS, DB_HOST, DB_DB);
	}
	public function redirect($section) {
		header('Location: /' . $section);
		exit;
	}
}

$app = new LapiApplication();
$GLOBALS["app"] = $app;


/**
 * Lapi Functions
 */
function getFile($url) {
	global $app;
	$source = callFile($url);    
	$doc = new DOMDocument();

	if (@$doc->loadHTML($source)) {
		return $doc;
	} else {
		$app->redirect('odhlasit');
	}
}

function callFile($url) {
	if (!isset($_SESSION['lapi_lopuch'])) {
		return '';
	}

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_COOKIE, 'lopuch='. $_SESSION['lapi_lopuch'] .'; user='. $_SESSION['lapi_user']); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	//curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);

	$data = iconv('windows-1250', 'utf-8', curl_exec($ch));
	$data = str_replace("windows-1250", "utf-8", $data);
	$data = mb_convert_encoding($data, "HTML-ENTITIES", "UTF-8");

	return $data;
	exit;
	return curl_exec($ch);
}			
					
function get_inner_html($node) { 
	$innerHTML= ''; 
	$children = $node->childNodes; 
	foreach ($children as $child) { 
		$innerHTML .= $child->ownerDocument->saveXML( $child ); 
	}

	return $innerHTML; 
}

function fix_replies($html) {
	return preg_replace('/href=\"klub\.php\?/', 'href="klub?', $html);
}

function removeSpoilers($node) {
	$elements = $node->getElementsByTagName('font');
	foreach ($elements as $element) { 
		$element->setAttribute('color', 'black');
	}
}

function hideImages($node) {
	$elements = $node->getElementsByTagName('img');
	foreach ($elements as $element) { 
		$element->setAttribute('data-src', $element->getAttribute('src'));
		$element->removeAttribute('src');
	}
}
 
function getKlubURL($ele) {
	$url = explode('?', $ele->getAttribute('href'));
	return $url[1];
} 

function getNumber($str) {
	return preg_replace('/[^0-9]/', '', $str);
}

function isValidCSSColor($color) {
	if (!isset($color) || !$color) 	return false;
	if ($color == 'transparent') return true;
	if (preg_match('/^#[0-9a-zA-Z]{6}$/', $color)) return true;
	if (preg_match('/^#[0-9a-zA-Z]{3}$/', $color)) return true;
	if (preg_match('/^(rgb|hsl)a?\([0-9\s,]+\)$/', $color)) return true;
	if (preg_match('/^(linear|radial)-gradient\([a-z%0-9\s,]+\)$/', $color)) return true;
	return false;
}

function getTime($str) {
	$str = str_replace('.', '/', $str);
	$str = str_replace('-', '', $str);
	$str = preg_replace('/^\s/', '', $str);  
	$str = explode(" ", $str);
	
	$str = $str[0] == date('j/n/Y') ? stripString($str[1]) : $str[0]; 
	return trim($str);
}

function linkify($value, $protocols = array('http', 'mail'), array $attributes = array(), $mode = 'normal') {
	// Link attributes
	$attr = '';
	foreach ($attributes as $key => $val) {
		$attr = ' ' . $key . '="' . htmlentities($val) . '"';
	}

	$links = array();

	// Extract existing links and tags
	$value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) { 
		return '<' . array_push($links, $match[1]) . '>'; 
	}, $value);

	// Extract text links for each protocol
	foreach ((array)$protocols as $protocol) {
		switch ($protocol) {
			case 'http':
			case 'https': $value = preg_replace_callback($mode != 'all' ? '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i' : '~([^\s<]+\.[^\s<]+)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { if ($match[1]) $protocol = $match[1]; $link = $match[2] ?: $match[3]; return '<' . array_push($links, '<a' . $attr . ' href="' . $protocol . '://' . $link . '">' . $link . '</a>') . '>'; }, $value); break;
			case 'mail': $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) { return '<' . array_push($links, '<a' . $attr . ' href="mailto:' . $match[1] . '">' . $match[1] . '</a>') . '>'; }, $value); break;
			case 'twitter': $value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) { return '<' . array_push($links, '<a' . $attr . ' href="https://twitter.com/' . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1] . '">' . $match[0] . '</a>') . '>'; }, $value); break;
			default: $value = preg_replace_callback($mode != 'all' ? '~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i' : '~([^\s<]+)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { return '<' . array_push($links, '<a' . $attr . ' href="' . $protocol . '://' . $match[1] . '">' . $match[1] . '</a>') . '>'; }, $value); break;
		}
	}

	// Insert all link
	return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) { return $links[$match[1] - 1]; }, $value);
}

function stripString($str) {
	return preg_replace('/[^a-zA-Z0-9\-_:]/', '', $str);	
}

/**
 * Rendering
 */

class DefaultParams {
	protected $_user;
	private function getSettings($str) {
		global $app;
		if (!isset($_user)) {
			require_once($app->dirModels . '/User.php');
			$_user = new User();
		}
		return isset($_SESSION[$str]) ? $_SESSION[$str] : $_user->settings->{$str};
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
	 
function render($name, $params = NULL) {
	global $app;
	if (is_null($params)) $params = new DefaultParams();
	$m = new Mustache_Engine();

	$name = preg_replace('/[^a-zA-Z0-9\-_]/', '', $name);

	$body = file_get_contents($app->dirTemplates . '/' . $name . '.html');
	$layout = file_get_contents($app->dirTemplates . '/layout.html');

	$body = str_replace('{{BODY}}', $body, $layout);

	echo $m->render($body, $params);
}

/**
 * Default Models & Collections
 */

class LapiModel {
	public $attributes = array();
	public $idAttribute =  'id';
	public $defaults = array();
	public $cid = '';
	public $validationError = '';
	public $db_table;
	public function __construct($data) {

		foreach ($data as $key => $value) {
			$this->set($key, $value);
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
			return false;
		}

		if (!$this->isValid()) {
			return $this->validationError;
		}

		$is_new = $this->isNew();
		$sqlStringA = '';
		$sqlStringB = '';

		foreach ($this->attributes as $key => $value) {
			if ($is_new) {
				$sqlStringA .= $key . ',';
				$sqlStringB .= '"' . $value . '",';
			} else {
				$sqlStringA .= $key . '="' . value . '",';
			}
		}

		$sqlStringA = preg_replace('/,$/', '', $sqlStringA);
		$sqlStringB = preg_replace('/,$/', '', $sqlStringB);

		if ($is_new) {
			$q = 'INSERT INTO ' . $this->db_table . ' (' . $sqlStringA . ') VALUES(' . $sqlStringB . ')';
		} else {
			$q = 'UPDATE ' . $this->db_table . ' SET ' . $sqlStringB;
		}

		return !!@$app->database->query($q);
	}
	public function fetch() {
		global $app;
		if (!isset($this->db_table)) {
			return false;
		}

		$q = 'SELECT * FROM ' . $this->db_table . ' WHERE ' . $this->idAttribute . '=' . $this->getId() . ' LIMIT 1';

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
		if (!isset($this->db_table)) {
			return false;
		}

		if (!$this->getId()) {
			return false;
		}

		$q = 'DELETE FROM ' . $this->db_table . ' WHERE ' . $this->idAttribute . '="' . $this->getId() . '"';

		if (isset($attr['where'])) 	$q .= ' AND ' . $attr['where'];

		$q .= ' LIMIT 1';

		return !!@$app->database->query($q);
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

			$rt = $app->database->query('SELECT 1 FROM ' . $this->db_table . ' WHERE ' . $this->idAttribute . '=' . $this->getId() . ' LIMIT 1');
			return !$rt || $rt->num_rows === 0;
		}
		return true;
	}
}

class LapiCollection {
	public $model = 'LapiModel';
	public $models = array();
	public $db_table;

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

		if (isset($attr['where'])) 	$q .= ' WHERE ' . $attr['where'];
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