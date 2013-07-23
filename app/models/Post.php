<?php

class Post extend LapiModel {
	public $defaults = array(
		'nick' => '<no nick>',
		'title' => '',
		'text' => 'Lorem ipsum sit dolor...',
		'time' => date('u'),
		'id' =>  NULL,
		'club' => '<no club>',
		'avatar__url' => 'anonymous.png'
	);
	public $db_table = 'posts';
}