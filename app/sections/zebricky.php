<?php    

$doc = getFile('http://www.lapiduch.cz');
$xpath = new DOMXpath($doc);
$elements = $xpath->query('//ol[preceding-sibling::p[@class="hore2"]]');
if (is_null($elements) || $elements->length != 3) {
	render('error');
	exit;
}

class Params extends DefaultParams {
	public $list = array();
}

class ItemList {
	public $list_name;
	public $items = array();
}

class Item {
	public $name;
	public $url;
}

function generateList($name, $root) {
	$list = new ItemList();
	$list->list_name = $name;
	$tmp = $root;
	foreach ($tmp->childNodes as $element) {
		if ($element->nodeName === 'li') {
			$item = new Item();
			$item->url = getKlubURL($element->firstChild);
			$item->name = $element->firstChild->nodeValue;
			$list->items[] = $item;
		}
	}  
	
	return $list;
}

$params = new Params();

$params->list[] = generateList('Nejnavštěvovanější', $elements->item(0));
$params->list[] = generateList('Nejpřispívanější ', $elements->item(1));
$params->list[] = generateList('Nejnovější ', $elements->item(2));
 


render('zebricky', $params);
