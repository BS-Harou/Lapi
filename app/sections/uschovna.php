<?php

require_once($app->dirModels . '/Post.php');

/**
 * Functions
 */

function get_post($club, $id) {
	global $app;
	$doc = getFile('http://www.lapiduch.cz/klub.php?klub=' . $club . '&to=' . $id);

	$xpath = new DOMXpath($doc);
	$elements = $xpath->query('//table[substring(@id,1,4)="post"]');

	if (!$elements || $elements->length == 0) {
		render('error');
	}

	$post = $elements->item(0);
	$post_id = $post->getAttribute('id');

	$item = array();

	$item['post_id'] = $post_id;

	

	/**
	 * URL of avatar
	 */
	$avatar = $xpath->query('//table[@id="' . $post_id . '"]//td[@class="iko" or @class="ikoN"]//img');	
	$avatar = $avatar->item(0);
	$item['avatar_url'] = $avatar->getAttribute('src');

	/**
	 * Nickname
	 */
	$nick = $xpath->query('//table[@id="'. $post_id . '"]//a/b');
	$nick = $nick->item(0);
	$item['nick'] = $nick->nodeValue;

	/**
	 * Time
	 */
	$cas = $nick->parentNode->nextSibling->nextSibling->nextSibling;
	while ($cas->nodeType != 3 && $cas->previousSibling) $cas = $cas->previousSibling; // pravděpodobně už neni potřeba
	$item['time'] = $cas->nodeValue;

	/**
	 * Title and text content
	 */
	$text = $xpath->query('//table[@id="' . $post_id . '"]//td[@class="PrC"]');
	$text = $text->item(0);  

	$title = $text->removeChild($text->firstChild);
	$item['title'] = $title->nodeValue;

	$text->removeChild($text->firstChild); // remove <br />

	$item['text'] = get_inner_html($text);
	$item['text'] = addslashes(fix_replies($item['text']));

	$item['club'] = $club;
	$item['owner'] = $app->user->nick;

	return $item;


}

/**
 * Params
 */

class Params extends DefaultParams {
	public $items = array();
	public $has_items = false;
	public $vlevo;
	public $dvlevo;
	public $vpravo;
	public $dvpravo;
}


/*class Item {
	public $nick;
	public $avatar_url;
	public $text;
	public $time;
	public $title;
}*/

/**
 * Actions
 */

if (isset($_GET['action'])) {

	if ($_GET['action'] == 'add') {
		$data = get_post(stripString($_GET['club']), stripString($_GET['id']));
		$post = new Post($data);

		$posts = new Posts(array(
			'where' => $post->pick(array('owner', 'club', 'post_id'))
		));

		if ($posts->length() == 0) {
			$post->save();	
		}
		
		$app->redirect('klub?klub=' . stripString($_GET['club']));
	}

}


$limit = '15';
$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;

if (isset($offset) && $offset > 0) {
	$limit = $offset . ', 15';
}

$posts = new Posts(array(
	'where' => 'owner="' . stripString($app->user->nick) . '"',
	'order' => 'id DESC',
	'limit' => $limit
));

$maxp = $posts->allRowsCount();

$params = new Params();
$params->has_items = $posts->length() > 0;

$params->vlevo = 'offset=' . ($offset - 15 > 0 ? $offset - 15 : 0);
$params->dvlevo = '';
$params->vpravo = 'offset=' . ($offset + 15 < $maxp ? $offset + 15 : $offset);
$params->dvpravo  = 'offset=' . ($maxp - 15 < 0 ? 0 : $maxp - 15);

for ($i=0; $i<$posts->length(); $i++) {
	$arr = $posts->at($i)->attributes;
	$arr['time'] = getTime($arr['time']);
	$arr['post_id_num'] = getNumber($arr['post_id']);
	$params->items[] = $arr;

}

render('uschovna', $params);