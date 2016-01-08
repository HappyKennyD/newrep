<?php defined('SYSPATH') or die('No direct script access.');

class Model_Storage extends ORM
{

	protected $_table_name = 'storages';

	public function upload($name, $path, $user_id)
	{
		$this->name    = $name;
		$this->path    = $path;
		$this->date    = date("d");
		$this->user_id = $user_id;
		return $this->save();
	}


}
