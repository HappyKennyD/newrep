<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller_Kotwig extends Kohana_Controller_Kotwig {
	public function before() {
		parent::before();
		
		$this->response->headers('cache-control','private');
	}
}
