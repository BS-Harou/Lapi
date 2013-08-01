<?php
class Params extends DefaultParams {
	public $items;
	public $category_name;
	public $has_items;
	public $show_search = false;
}

class Item {
	public $url;
	public $name;
	public $new_count;
}

$params = new Params();


$se = iconv('utf-8', 'windows-1250', htmlspecialchars($_GET['search']));
if (!$se) {
	$cat = intval($_GET["cat"]);
	$doc = getFile('http://www.lapiduch.cz/index.php?mod=category&cat=' . $cat);
} else {
	$doc = getFile('http://www.lapiduch.cz/katpopis.php?searchtext=' . $se);
	$params->show_search = true;
}

$xpath = new DOMXpath($doc);
$elements = $xpath->query('//table[@class="vypisKlubu"]//td[1]/a');
$catName = $xpath->query('//p[@class="s3"]/b/font');


if (!$elements) {
	$app->redirect('odhlasit');
}


$params->category_name = $catName->item(0)->nodeValue;
$params->has_items = $elements->length > 0;

foreach ($elements as $element) {
	$item = new Item();
	$item->url = getKlubURL($element);
	$item->name = $element->firstChild->nodeValue;
	
	$tmp = $xpath->query('../../td[@class="L1n"]/span[@class="cervena"]', $element);
	if ($tmp->length>0) {
		$tmp = $tmp->item(0);
		$item->new_count = getNumber($tmp->nodeValue);
	}

	$params->items[] = $item;
}  

render('kluby', $params);