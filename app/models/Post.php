<?php

class Post extends LapiModel {
	public $defaults = array(
		'id'    =>  NULL,
		'owner' =>  NULL,
		'nick'  => '<no nick>',
		'title' => '',
		'text'  => 'Lorem ipsum sit dolor...',
		'time'  => '1/1/1 00:00',
		'club'  => '<no club>',
		'avatar_url' => 'anonymous.png',
		'post_id' => 0
	);
	public $db_table = 'uschovna';
}


class Posts extends LapiCollection {
	public $db_table = 'uschovna';
}