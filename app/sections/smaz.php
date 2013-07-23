<?php

/**
 * Define functions
 */
function remove_post($club, $id) {
	$query = http_build_query(
		array(
			'vystup1' => 'Smaž', 
			'method1' => '1',
			'klub' => $club,
			'vyber[]' => $id
		)
	);
	remove_item($query, 'http://www.lapiduch.cz/klub.php?klub=' . $club);
}  

function remove_msg_post($id) {
	$query = http_build_query(
		array(
			'vystup1' => 'Smaž', 
			'method1' => '1',
			'vyber[]' => $id
		)
	);
	remove_item($query, 'http://www.lapiduch.cz/posta.php');
}

function remove_item($query, $url) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_COOKIE, 'lopuch=' . $_SESSION['lapi_lopuch'] . '; user=' . $_SESSION['lapi_user']); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
	$source = curl_exec($ch);                 
	$doc = new DOMDocument();
}

/**
 * Handle request
 */
if (isset($_POST['type'])) {

	if ($_POST['type'] == 'posta') {
		remove_msg_post($_POST['post']);
		$app->redirect('posta');
	} else { // type=klub
		remove_post($_POST['klub'], $_POST['post']);
		$app->redirect('klub?klub=' . $_POST['klub']);
	}
} else {
	render('error');
}