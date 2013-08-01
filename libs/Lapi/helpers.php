<?php

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
	global $app;
	if (!isset($_SESSION['lapi_lopuch'])) {
		return '';
	}

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_COOKIE, 'lopuch='. $_SESSION['lapi_lopuch'] .'; user='. $app->user->nick); 
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
	if (!isset($color))	return false;
	if (!$color) return true;
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