<?php defined('SYSPATH') or die('No direct script access.');

class Storage extends Base
{
    public function save_jcrop_photo($remote_path,$user_id)
    {
        $name = md5(date("Y:m:d:H:i:s").'sda@^');
        $path    = $this->get_path($user_id);
        $absolute = trim($path,'/').'/'.trim($name,'/').'.png';
        $image   = file_get_contents(trim($remote_path));
        file_put_contents($absolute, $image);
        try
        {
            Image::factory($absolute, 'Imagick');
        }
        catch(Kohana_Exception $e)
        {
            return 0;
        }

        $storage = ORM::factory('Storage');
        try
        {
            $storage->file_path = $absolute;
            $storage->date    = date('Y-m-d H:i:s');
            $storage->user_id = $user_id;
            $storage->name = $name;
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

	public function add($file, $user_id, $photogalery=0)
	{

        $doc = array('mp3','jpg','jpeg','jpe','png','gif','xls','xlsx','doc','docx','ppt','pptx','rar','zip','7zip','7z','flv','mp4','3gp','wmv','avi','pdf','psd', 'odt', 'ods', 'odp');
        $o = explode('.',$file['name']);
        $t = array_pop($o);
        if (!in_array(strtolower($t),$doc))
        {
            throw new HTTP_Exception_403('Файлы не разрешенны для закачки на сервер');
        }

        $newName = md5($file['name'].date('Y:m:d H:i:s')).'.'.$t;
        $storage = ORM::factory('Storage');
		if($filename = $this->file_save($file, $user_id,$newName))
		{
//		die($filename);

            //$storage->name = $file['name'];
            $storage->name = (htmlspecialchars(Security::xss_clean(addslashes($file['name'])))!='')?htmlspecialchars(Security::xss_clean(addslashes($file['name']))):'безымянный файл';
			$storage->file_path = $this->path.$newName;
			$storage->user_id = $user_id;
			$storage->date    = date('Y-m-d H:i:s');
			$storage->mime    = $file['type'];
            $storage->type = $t;
			if($storage->save())
			{
                if($photogalery==1)
                {
                    if(file_exists('media/images/watermark.png'))
                    {
                        $image=Image::factory($this->path.$newName, 'Imagick');
                        $watermark= Image::factory('media/images/watermark.png', 'Imagick');

                        $newwidth=$image->width/4;
                        $newheight=$image->height/4;
                        $watermark->resize($newwidth, $newheight, Image::WIDTH);

                        $image->watermark($watermark, $image->width - $watermark->width-2, $image->height - $watermark->height-2, $opacity =100);
                        $image->save($this->path.$newName,100);
                    }
                }

				return $storage->id;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

    public static  function to_byte($val)
    {
        $val = trim($val);

        switch (strtolower(substr($val, -1)))
        {
            case 'm': $val = (int)substr($val, 0, -1) * 1048576; break;
            case 'k': $val = (int)substr($val, 0, -1) * 1024; break;
            case 'g': $val = (int)substr($val, 0, -1) * 1073741824; break;
            case 'b':
                switch (strtolower(substr($val, -2, 1)))
                {
                    case 'm': $val = (int)substr($val, 0, -2) * 1048576; break;
                    case 'k': $val = (int)substr($val, 0, -2) * 1024; break;
                    case 'g': $val = (int)substr($val, 0, -2) * 1073741824; break;
                    default : break;
                } break;
            default: break;
        }
        return $val.' B';
    }
}
