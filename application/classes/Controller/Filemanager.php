<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Filemanager extends Controller
{
    public function action_rimages()
    {
        if (!empty($_FILES['file']))
        {
            $_FILES['file']['type'] = strtolower($_FILES['file']['type']);
            if ($_FILES['file']['type'] == 'image/png'
                || $_FILES['file']['type'] == 'image/jpg'
                || $_FILES['file']['type'] == 'image/gif'
                || $_FILES['file']['type'] == 'image/jpeg'
                || $_FILES['file']['type'] == 'image/pjpeg')
            {
                $user_id = Auth::instance()->get_user()->id;
                $storage_id = Storage::instance()->add($_FILES['file'], $user_id);
                if ($storage_id)
                {
                    $image  = ORM::factory('Storage',$storage_id);
                    $fname = URL::media($image->file_path);
                    // displaying file
                    $array = array(
                        'filelink' => $fname //$path . 'images/w500-m500/' . $fname
                    );
                    echo stripslashes(json_encode($array));
                    exit;
                }
                else
                {
                    throw new HTTP_Exception_404('Ошибка при сохранении');
                }
            }
            else
            {
                throw new HTTP_Exception_404('Не верный формат картинки');
            }
        }
        else
        {
            throw new HTTP_Exception_404('Not Found');
        }


        /*


        $dir = APPPATH . '../media/upload/';
        $_FILES['file']['type'] = strtolower($_FILES['file']['type']);

        if ($_FILES['file']['type'] == 'image/png'
            || $_FILES['file']['type'] == 'image/jpg'
            || $_FILES['file']['type'] == 'image/gif'
            || $_FILES['file']['type'] == 'image/jpeg'
            || $_FILES['file']['type'] == 'image/pjpeg'
        )
        {
            // setting file's mysterious name
            $fname = md5(date('YmdHis')) . '.jpg';
            $file = $dir . $fname;

            // copying
            move_uploaded_file($_FILES['file']['tmp_name'], $file);



            $path = Kohana::$config->load('publication')->get('static');
            // displaying file
            $array = array(
                'filelink' => $path . 'images/w500-sa/media/upload/' . $fname
            );

            echo stripslashes(json_encode($array));
            exit;
        }
        */
    }

    public function action_fupload()
    {

        if (!empty($_FILES['file']))
        {
            $user_id = Auth::instance()->get_user()->id;
            $storage_id = Storage::instance()->add($_FILES['file'], $user_id);
            if ($storage_id)
            {
                $image  = ORM::factory('Storage', $storage_id);
                $fname = URL::media($image->file_path);
                // displaying file
                $array = array(
                    'filelink' => $fname, //$path . 'images/w500-m500/' . $fname
                    'filename' => $image->name
                );
                echo stripslashes(json_encode($array));
                exit;
            }
            else
            {
                throw new HTTP_Exception_404('Ошибка при сохранении');
            }
        }
        else
        {
            throw new HTTP_Exception_404('Не верный формат картинки');
        }

        ////////////////////
        /*
        $dir = APPPATH . '../media/upload/';
        $fname = substr(md5(time()), 0, 8) . '_' . $_FILES['file']['name'];
        move_uploaded_file($_FILES['file']['tmp_name'], $dir . $fname);

        $array = array(
            'filelink' => Url::site('/media/upload/').'/'.  $fname,
            'filename' => $_FILES['file']['name']
        );

        echo stripslashes(json_encode($array));
        exit;
        */



    }
}