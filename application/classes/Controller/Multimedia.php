<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Multimedia extends Controller_Core {

	public function action_index()
	{
        $this->add_cumb(i18n::get("Мультимедиа"),'');
    }
}
