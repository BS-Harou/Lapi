<?php

class Params extends DefaultParams {
	/**
	 * Get the amount of unread messages
	 */
	function unread_msg_count() {
		global $app;
		$doc = getFile('http://www.lapiduch.cz/');
		$xpath = new DOMXpath($doc);

		$is_logged = $xpath->query('//td[@class="uziv"]//img');
		if (!$is_logged || $is_logged->length == 0) {
			$app->redirect('odhlasit');
		}

		$msg_count_ele = $xpath->query('//td[@class="uziv"]//font[@color="red"]');
		if ($msg_count_ele->length > 0) {
			return $msg_count_ele->item(0)->nodeValue;
		} 

		return 0;
	}
}

render('menu', new Params);
