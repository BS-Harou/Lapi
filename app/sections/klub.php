<?php

class Params extends DefaultParams {
	public $items;
	public $url_vlevo;
	public $url_dvlevo;
	public $url_vpravo;
	public $url_dvpravo;
	public $club;
	public $club_name;
	public $favourite;
	public $more_new_posts;
	public $can_add;
	public $fav_url;
}


class Item {
	public $nick;
	public $id;
	public $id_num;
	public $avatar_url;
	public $text;
	public $time;
	public $title;
	public $can_delete;
	public $is_new;
	public $is_mine;
	public $is_bookmarked;
	public $bookmark_id;
}

$query = $_SERVER['QUERY_STRING'];  

$klub = stripString($_GET['klub']);

if ($_GET['mod'] === 'book') {
	callFile('http://www.lapiduch.cz/index.php?'.$query);
}
$doc = getFile('http://www.lapiduch.cz/klub.php?'.$query);

$xpath = new DOMXpath($doc);
$elements = $xpath->query('//table[substring(@id,1,4)="post"]');
$navi = $xpath->query('//td[@class="knavi"]/a');

if (is_null($elements) || $elements->length == 0) {  
	$app->redirect('odhlasit');
}

require_once($app->dirModels . '/Post.php');

$posts = new Posts();
$posts->fetch(array(
	'where' => 'club="' . $klub . '" AND owner="' . $app->user->nick . '"'
));


$params = new Params();
$params->club = $klub;
$params->favourite = preg_match('/remove/', getKlubURL($navi->item(5))) ? 'fav' : '';

$clubName = $xpath->query('//td[@class="sT"]/b/font');
$params->club_name = $clubName->item(0)->nodeValue;

$more_new_posts = $xpath->query('//td[@class="knavi"]//font[@color="#FF0000"]');
$params->more_new_posts = $more_new_posts->length > 0 ? 'vice' : '';

$canAdd = $xpath->query('//textarea');
$params->can_add = $canAdd->length > 0;

$params->vlevo   = getKlubURL($navi->item(0)); // next page
$params->dvlevo  = getKlubURL($navi->item(1)); // first page
$params->dvpravo = getKlubURL($navi->item(2)); // last page
$params->vpravo  = getKlubURL($navi->item(3)); // prev page
$params->fav_url = $klub . '&' . getKlubURL($navi->item(5));



foreach ($elements as $post) {
	$item = new Item();

	/**
	 * Post ID
	 */

	$item->id = $post->getAttribute('id');
	$item->id_num = getNumber($item->id);

	/**
	 * URL of avatar
	 */

	
	$avatar = $xpath->query('//table[@id="' . $item->id . '"]//td[@class="iko" or @class="ikoN"]//img');	
	//$avatar = $xpath->query("//table[@id='" . $item->id . "']//td[@class='iko']//img");
	$avatar = $avatar->item(0);
	if (!$avatar || strlen($avatar->getAttribute('src')) == 0) {
		$psts = $xpath->query('//table[@id="' . $item->id . '"]');
		echo htmlspecialchars(get_inner_html( $psts->item(0) ));
		exit;
	}
	$item->avatar_url = $avatar->getAttribute('src');

	/**
	 * Nickname
	 */

	$nick = $xpath->query('//table[@id="'. $item->id . '"]//a/b');
	$nick = $nick->item(0);
	$item->nick = $nick->nodeValue;

	/**
	 * Is this post mine?
	 */

	$item->is_mine = $item->nick == $app->user->nick;

	/**
	 * Time
	 */
	
	$cas = $nick->parentNode->nextSibling->nextSibling->nextSibling;
	while ($cas->nodeType != 3 && $cas->previousSibling) $cas = $cas->previousSibling; // pravděpodobně už neni potřeba
	$item->time = getTime($cas->nodeValue);

	/**
	 * Is the post new?
	 */

	$is_new = $xpath->query('//table[@id="' . $item->id . '"]//span[@class="PrA2"]');	
	$item->is_new = $is_new->length > 0 ? 'nove-post' : '';	

	/**
	 * Is the POST bookmarked by user?
	 */
	$bookmark = $posts->firstWhere(array('post_id' => $item->id));
	$item->is_bookmarked =  $bookmark != NULL;
	if ($item->is_bookmarked) {
		$item->bookmark_id = $bookmark->get('id');
	}

	/**
	 * Title and text content
	 */

	$text = $xpath->query('//table[@id="' . $item->id . '"]//td[@class="PrC"]');
	$text = $text->item(0);  

	$title = $text->removeChild($text->firstChild);
	$item->title = $title->nodeValue;

	$text->removeChild($text->firstChild); // remove <br />
	if ($app->user->settings->get('show_spoilers')) {
		removeSpoilers($text);
	}
	if ($is_new->length == 0 && $app->user->settings->get('hide_old_images')) {
		hideImages($text);
	}

	$item->text = get_inner_html($text);
	$item->text = fix_replies($item->text);

	if ($app->user->settings->get('linkify')) {
		$item->text = linkify($item->text);
	}

	/**
	 * Can the user delete the post?
	 */

	$canDelete = $xpath->query('//table[@id="' . $item->id . '"]//input[@type="checkbox"]');
	$item->can_delete = $canDelete->length > 0;

	

	/**
	 * Add the item object to array
	 */

	$params->items[] = $item;

}


if ($app->user->settings->get('old_style')) {
	render('klub', $params);
} else {
	render('new_klub', $params);	
}