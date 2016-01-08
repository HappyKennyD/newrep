<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Storage extends Controller
{

	public function action_index()
	{
		$this->set('user', Auth::instance()->get_user());
	}

	public function action_upload()
	{
		if($this->request->method() == "POST")
		{
			$user_id = Arr::get($_POST, 'user_id', 0);
            $photogalery = Arr::get($_POST, 'photogalery', 0);
            echo Storage::instance()->add($_FILES['Filedata'], $user_id, $photogalery);
		}
		exit;
	}
}