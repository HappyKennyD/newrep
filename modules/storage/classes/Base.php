<?php defined('SYSPATH') or die('No direct script access.');

class Base
{

	public $path;

	/** @var Storage */
	static protected $instance;

	static public function instance()
	{
		if(!isset(static::$instance))
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	public function file_save($file, $user_id,$newName)
	{
		$path       = $this->get_path($user_id);
		$this->path = $path;
		return Upload::save($file, $newName, $path);
	}

	public function remove($id)
	{

	}

	public function file_rename($id, $name)
	{
        $storage = ORM::factory('Storage',$id);
        if ($storage->loaded())
        {
            $storage->name = $name;
            return $storage->save();
        }
        else
        {
            return false;
        }

	}

	public function get_path($user_id)
	{
		$config    = Kohana::$config->load('storage');
		$directory = $config['dir'].$user_id.'/'.date("Y").'/'.date("m").'/'.date("d");
		$p         = explode('/', $directory);
		$directory = '';
		for($i = 1; $i < count($p); $i++)
		{
			$directory .= $p[$i].'/';
			if(!is_dir($directory))
			{
				mkdir($directory, 02777);

				chmod($directory, 02777);
			}
		}
		return $directory;
	}

    public function save_social_photo($remote_path,$user_id)
    {
        $name = md5(date("Y:m:d:H:i:s").'sda@^');
        $path    = $this->get_path($user_id);
        $absolute = trim($path,'/').'/'.trim($name,'/').'.png';
        $image   = file_get_contents(trim($remote_path));
        file_put_contents($absolute, $image);
        try
        {
            Image::factory($absolute);
        }
        catch(Kohana_Exception $e)
        {
            return 0;
        }

        $storage = ORM::factory('Storage');
        try
        {
            $storage->file_path = $absolute;
            $storage->user_id = $user_id;
            $storage->name = $absolute;
            if ($storage->save())
            {
                return $storage->id;
            }
        }
        catch(ORM_Validation_Exception $e)
        {
            return var_dump($e->errors());
        }
    }
}
