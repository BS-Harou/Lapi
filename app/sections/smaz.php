<?php

/**
 * Mazani z uschovny je vyreseno hrozne...
 */

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
	global $app;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_COOKIE, 'lopuch=' . $_SESSION['lapi_lopuch'] . '; user=' . $app->user->nick); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
	$source = curl_exec($ch);                 
	$doc = new DOMDocument();
}

function remove_stash_post($id) {
	global $app;
	require_once($app->dirModels . '/Post.php');

	$post = new Post(array(
		'id' => (int) $id
	));

	$post->destroy(array(
		'where' => array(
			'owner' => $app->user->nick
		)
	));
}

function stash_redirect($club) {
	global $app;
	if (!isset($club) || !$club) {
		$app->redirect('uschovna');	
	} else {
		$app->redirect('klub?klub=' . stripString($club));
	}
}

/**
 * Params
 */

class Params extends DefaultParams {
	public $klub;
	public $post;
	public $type;
}

/**
 * Handle request
 */
if (isset($_POST['type'])) {

	if ($_POST['type'] == 'posta') {
		remove_msg_post($_POST['post']);
		$app->redirect('posta');
	} if ($_POST['type'] == 'uschovna') {
		remove_stash_post($_POST['post']);
		stash_redirect($_POST['club']);		
	} else { // type=klub
		remove_post($_POST['klub'], $_POST['post']);
		$app->redirect('klub?klub=' . $_POST['klub']);
	}
} else if (isset($_GET['type'])) {
	if ($_GET['type'] == 'uschovna') {
		remove_stash_post($_GET['post']);
		stash_redirect($_GET['club']);		
	} else {
		$params = new Params();
		$params->type = stripString($_GET['type']);
		$params->post = stripString($_GET['post']);
		$params->klub = stripString($_GET['klub']);
		render('smaz', $params);
	}
} else {
	render('error');
}