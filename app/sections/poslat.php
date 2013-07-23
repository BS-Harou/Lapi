<?php
/**
 * Define functions
 */ 
function send_mail($kdo, $titulek, $body) {
	$query = http_build_query(
		array(
			'titulek' => iconv('utf-8', 'windows-1250', $titulek), 
			'body' => iconv('utf-8', 'windows-1250', $body) ,
			'kdo' => iconv('utf-8', 'windows-1250', $kdo),
			'Odeslat' => 'Odeslat',
			'method' => 'store',
		)
	);
	$ch = curl_init ('http://www.lapiduch.cz/posta.php');
	curl_setopt ($ch, CURLOPT_COOKIE, 'lopuch=' . $_SESSION['lapi_lopuch'] . '; user=' . $_SESSION['lapi_user']); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
	$source = curl_exec($ch);                 
	$doc = new DOMDocument();
}  


/**
 * Send mail
 */
$titulek = $_POST['titulek'] ? $_POST['titulek'] : '';
$body = $_POST['body'];
$kdo = $_POST['kdo'];
if ($kdo && $body) {
	send_mail($kdo, $titulek, $body);
	$app->redirect('posta');
}

/**
 * Render form
 */

class Params extends DefaultParams {
	public $send_to;
}

$params = new Params();
$params->send_to = htmlspecialchars($_GET['to']);

render('poslat', $params);