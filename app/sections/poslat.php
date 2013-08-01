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
	curl_setopt ($ch, CURLOPT_COOKIE, 'lopuch=' . $_SESSION['lapi_lopuch'] . '; user=' . $app->user->nick); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
	$source = curl_exec($ch);
}  

/**
 * Params
 */

class Params extends DefaultParams {
	public $send_to;
	public $error_msg;
	public $title;
	public $text;
}

$params = new Params();


/**
 * Send mail
 */
$titulek = $_POST['titulek'] ? htmlspecialchars($_POST['titulek']) : '';
$body = $_POST['body'];
$kdo = stripString($_POST['kdo']);
if ($kdo && $body) {
	send_mail($kdo, $titulek, $body);
	$app->redirect('posta');
} else if ($titulek || $body || $kdo) {
	$params->error_msg = 'Musíte vyplnit adresáta a text zprávy';
}

/**
 * Render form
 */

if (isset($kdo) || $kdo) {
	$params->send_to = $kdo;
} else if (isset($_GET['to'])) {
	$params->send_to = stripString($_GET['to']);
}

$params->title = stripString($titulek);
$params->text = htmlspecialchars($body);

render('poslat', $params);