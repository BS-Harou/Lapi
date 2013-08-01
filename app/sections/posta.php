<?php

// u posty neresim zvyrazneni novych zprav

class Params extends DefaultParams {
	public $items;
	public $url_vlevo;
	public $url_dvlevo;
	public $url_vpravo;
	public $url_dvpravo;
	public $club;
}


class Item {
	public $nick;
	public $id;
	public $avatar_url;
	public $text;
	public $time;
	public $title;
	public $info;
	public $unread = false;
	public $is_mine;
	public $is_new;
}

$query = $_SERVER['QUERY_STRING'];

$doc = getFile('http://www.lapiduch.cz/posta.php?'.$query);
$xpath = new DOMXpath($doc);
 
$elements = $xpath->query('//form//table[@cellspacing="0" and @width="98%"]');
$mujavatar = $xpath->query('//td[@class="uziv"]/img');
$navi = $xpath->query('//td[@class="knavi"]/a');
				 
if (is_null($elements) || $navi->length == 0) {
	$app->redirect('odhlasit');
}

$params = new Params();

$params->vlevo   = getKlubURL($navi->item(0)); // next page
$params->dvlevo  = getKlubURL($navi->item(1)); // first page
$params->dvpravo = getKlubURL($navi->item(2)); // last page
$params->vpravo  = getKlubURL($navi->item(3)); // prev page



foreach ($elements as $post) {
	$item = new Item();
	
	/**
	 * ID
	 */
	$item->id = getNumber($post->getElementsByTagName('input')->item(0)->getAttribute('value'));

	/**
	 * URL of avatar & info (from/to)
	 */

	$avatar = $post->getElementsByTagName('img');
	$avatar = $avatar->item(1);
	$item->info = 'od';
	if ($avatar->getAttribute('src') === 'g/m.gif') {
		$avatar =  $mujavatar->item(0) ;
		$item->info = 'pro';
	}
	$item->avatar_url = $avatar->getAttribute('src');

	/**
	 * Nickname
	 */

	$nick = $post->getElementsByTagName('b');
	$nick = $nick->item(0);
	$item->nick = $nick->nodeValue;

	/**
	 * Is this post mine?
	 */

	$item->is_mine = strtolower($item->nick) == $app->user->nick;

	/**
	 * has the recep. read the message?
	 */

	$unread = $xpath->query('.//span[@class="PrA3"]', $post); 
	if ($unread->item(0)) {
		$item->unread = true;
	}

	/**
	 * Time
	 */

	$cas = $nick->parentNode->nextSibling->nextSibling->nextSibling;
	while ($cas->nodeType != 3 && $cas->previousSibling) $cas = $cas->previousSibling; // pravděpodobně už neni potřeba
	$item->time = getTime($cas->nodeValue);

	/**
	 * Is the post new?
	 */

	$is_new = $xpath->query('.//span[@class="PrA2"]', $post);	
	$item->is_new = $is_new->length > 0 ? 'nove-post' : '';	

	/**
	 * Title ad text content
	 */

	$text = $post->getElementsByTagName('td');
	$text = $text->item(6); 

	$nadpis = $text->removeChild($text->firstChild);
	$item->title = $nadpis->nodeValue;

	$text->removeChild($text->firstChild); 
	$item->text = get_inner_html($text);
	if ($app->user->settings->get('linkify')) {
		$item->text = linkify($item->text);
	}

	/**
	 * Add to list
	 */

	$params->items[] = $item;
}

if ($app->user->settings->get('old_style')) {
	render('posta', $params);
} else {
	render('new_posta', $params);	
}