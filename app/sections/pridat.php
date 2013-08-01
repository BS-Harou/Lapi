<?php
$klub = stripString($_GET['klub']);

/**
 * Define functions
 */
function send_post($club, $titulek, $body) {
	global $app;
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
	curl_setopt($ch, CURLOPT_COOKIE, 'lopuch=' . $_SESSION['lapi_lopuch'].'; user=' . $app->user->nick); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
	$source = curl_exec($ch);                 
}


/**
 * Params
 */

class Params extends DefaultParams {
	public $reply;
	public $error_msg;
	public $title;
}

$params = new Params();


/**
 * Data for new post
 */
$titulek = $_POST['titulek'] ? htmlspecialchars($_POST['titulek']) : '';
$body = $_POST['body'];
if ($body && $klub) {      
	send_post($klub, $titulek, $body);
	$app->redirect('klub?klub=' . $klub);
} else if ($titulek) {
	$params->error_msg = 'Musíte vyplnit text zprávy';
	$params->title = $titulek;
}

/**
 * Reply
 */
$to = htmlspecialchars($_GET['to']);
$id = getNumber(htmlspecialchars($_GET['id']));
$ta = '';
if ($to && $id) {
	$ta = '<a href="klub.php?klub='.$klub.'&to='.$id.'" class="reply">'.$to.' ['.$id.']</a>: ';
}

/**
 * Rendering
 */


$params->reply = $ta;

render('pridat', $params);