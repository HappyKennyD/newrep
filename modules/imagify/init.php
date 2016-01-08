<?php defined('SYSPATH') or die('No direct script access.');
Route::set('images', 'images/<options>/<image>', array('image' => '.*'))
	->defaults(array(
		'controller' => 'imagify',
		'action' => 'process',
		));