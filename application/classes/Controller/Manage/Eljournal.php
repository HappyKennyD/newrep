<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Eljournal extends Controller_Manage_Core
{

    public function action_index()
    {
        $this->redirect('manage/eljournal/list');
    }

    public function action_edit()
    {
        for ($i = 0; $i <= 4; $i++) {
            $year_calling[$i] = date('Y') - 2 + $i;
        }
        $month_calling = array("Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");
        $user_id = Auth::instance()->get_user()->id;
        $id = (int)$this->request->param('id', 0);
        $calling = ORM::factory('Calling', $id);
        $uploader = View::factory('storage/unofile')->set('user_id', $user_id)->render();
        $this->set('item', $calling);
        $this->set('uploader', $uploader);
        $this->set('month_calling', $month_calling);
        $this->set('year_calling', $year_calling);
        $this->set('year_calling_def', '2');
        $this->set('month_calling_def', date('n') - 1);

        if ($this->request->post()) {
            try {
            $calling->title = $_POST['title'];

            $attach = Arr::get($_POST, 'attachments');
            $data = array('storage_id' => $attach);
            $calling->values($data);
            $calling->save();
            $storage = ORM::factory('Storage', $attach);

            $image = new Imagick();
            $image->readImage($storage->file_path . '[0]');
            $image->setImageResolution(300, 300);
            $image->resampleImage(300, 300, imagick::FILTER_UNDEFINED, 0);
            $filename = md5(date('Y-m-d H:i:s')) . '.png';
            $directory = 'media/upload/' . $this->user->id . '/' . date('Y') . '/' . date('m') . '/' . date('d');
            if (!is_readable($directory)) {
                mkdir($directory, 02777);
            }
            chmod($directory, 02777);
            $image->writeImage($directory . '/' . $filename);
            $filestorage = ORM::factory('Storage');
            $filestorage->user_id = $this->user->id;
            $filestorage->name = $filename;
            $filestorage->file_path = $directory . '/' . $filename;
            $filestorage->date = date('Y-m-d H:i:s');
            $filestorage->type = 'png';
            $filestorage->mime = 'application/octet-stream';
            $filestorage->save();
            $calling->cover_id = $filestorage->id;

            $calling->date = date('Y-m-d H:i', strtotime(Arr::get($_POST, 'date', time())));
            $calling->size = filesize($storage->file_path);

                $calling->save();
                $this->redirect('manage/eljournal/list/');
            } catch (ORM_Validation_Exception $e) {
                $this->set('errors',$e->errors());
            }
        }
    }

    public function action_list()
    {

        $month_calling = array("Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");
        $this->set('month_calling', $month_calling);
        $callings = ORM::factory('Calling')
            ->order_by('date', 'DESC')
            ->order_by('id', 'DESC')
            ->find_all();

        $this->set('callings', $callings);
    }

    public
    function action_delete()
    {
        /* if (($this->user == false) || (!$this->user->has_access('ma'))) {
             $this->redirect('/');
         }*/

        $id = $this->request->param('id', 0);
        $callings = ORM::factory('Calling', $id)->delete();
        $this->redirect('manage/eljournal/list');
    }

}

