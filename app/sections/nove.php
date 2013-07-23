<?php        

class Params extends DefaultParams {
	public $filled_list;
	public $items;
}

class Item {
	public $url;
	public $name;
	public $new_count;
}

$doc = getFile('http://www.lapiduch.cz/?mod=book');
$xpath = new DOMXpath($doc);
$elements = $xpath->query('//table[@class="vypisKlubu"]//tr[descendant::span[@class="L1p"]]//a');

if (!$elements) {
	$app->redirect('odhlasit');
}

$params = new Params();
$params->filled_list = $elements->length > 0;

foreach ($elements as $element) {
	$item = new Item();
	$item->url = getKlubURL($element);
	$item->name = $element->firstChild->nodeValue;

	$tmp = $xpath->query('../..//span[@class="L1p"]', $element); 
	if ($tmp->length>0) {
		$tmp = $tmp->item(0);
		$item->new_count = getNumber($tmp->nodeValue);
	}

	$params->items[] = $item;
}  

render('nove', $params);

