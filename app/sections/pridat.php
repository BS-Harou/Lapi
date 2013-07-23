<?php
$klub = $_GET['klub'];

/**
 * Define functions
 */
function send_post($club, $titulek, $body) {
	$query = http_build_query(
		array(
			'titulek' => iconv('utf-8', 'windows-1250', $titulek), 
			'body' => iconv('utf-8', 'windows-1250', $body) ,
			'klub' => $club,
			'Odeslat' => 'Odeslat',
			'method' => 'store',
		)
	);
	$ch = curl_init ('http://www.lapiduch.cz/klub.php?klub=' . $club);
	curl_setopt ($ch, CURLOPT_COOKIE, 'lopuch='.$_SESSION['lapi_lopuch'].'; user='.$_SESSION['lapi_user']); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
	$source = curl_exec ($ch);                 
	$doc = new DOMDocument();
}

/**
 * Data for new post
 */
$titulek = $_POST['titulek'] ? $_POST['titulek'] : '';
$body = $_POST['body'];
if ($body && $klub) {      
	send_post($klub, $titulek, $body);
	$app->redirect('klub?klub=' . $klub);
}

/**
 * Reply
 */
$to = htmlspecialchars($_GET['to']);
$id = getNumber(htmlspecialchars($_GET['id']));
$ta = '';
if ($to && $id) {
	$ta = '<a href="http://www.lapiduch.cz/klub.php?klub='.$klub.'&to='.$id.'" class="reply">'.$to.' ['.$id.']</a>: ';
}

/**
 * Rendering
 */

class Params extends Default Params {
	public $reply;
}

$params = new Params();
$params->reply = $ta;

render('pridat', $params);