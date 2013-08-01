<?php

class Params extends DefaultParams {
	public $has_items;
	public $items;
	public $old_search;
}


class Item {
	public $url;
	public $name;
}

$doc = getFile('http://www.lapiduch.cz/index.php?mod=category');
$xpath = new DOMXpath($doc);
$elements = $xpath->query('//center/table//td[1]/a');
if (is_null($elements)) {
	$app->redirect('odhlasit');
}

$params = new Params();
$params->has_items = $elements->length > 0;
// $params->items = array();
$params->old_search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';


foreach ($elements as $element) {
	$item = new Item();
	$item->url = getKlubURL($element);
	$item->name = $element->parentNode->nodeValue;
	$params->items[] = $item;
}

render('kategorie', $params);