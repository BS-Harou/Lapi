<?php

class Params extends DefaultParams {
	public $has_items;
	public $items;
}

class Item {
	public $url;
	public $name;
	public $new_count;
	public $is_header = false;
}

$doc = getFile('http://www.lapiduch.cz/?mod=book&new=0');
$xpath = new DOMXpath($doc);
$elements = $xpath->query('//table[@class="vypisKlubu"]//a | //td[@class="Lk"]');

if (!$elements) {
	$app->redirect('odhlasit');
}

$params = new Params();
$params->has_items = $elements->length > 0;


foreach ($elements as $element) {
	$item = new Item();

	if ($element->nodeName == 'td') {
		$item->name = $element->nodeValue;
		$item->is_header = true;

		//echo '<h1>'.$element->nodeValue.'</h1><div class="list">';
	} else {
		
		$item->url = getKlubURL($element);
		$item->name = $element->firstChild->nodeValue;

		$tmp = $xpath->query('../..//span[@class="L1p"]', $element);
		if ($tmp->length>0) {
			$tmp = $tmp->item(0);
			$item->new_count = getNumber($tmp->nodeValue);
		}
	}

	$params->items[] = $item;
}  
if ($one) {
	echo '</div>';
}

render('oblibene', $params);
