<?php
class Controller_Manage_Education extends Controller_Manage_Core
{
    public function action_index()
    {
        $scorms   = ORM::factory('Education')->where('language', '=', $this->language)->order_by('number');
        $paginate = Paginate::factory($scorms)->paginate(null, null, 10)->render();
        $scorms   = $scorms->find_all();
        $this->set('education', $scorms);
        $this->set('paginate', $paginate);
    }

    public function action_add()
    {
        if ($this->request->method() == Request::POST)
        {
            $file = isset($_FILES['file']['tmp_name']) ? $_FILES['file']['tmp_name'] : false;
            if ($file)
            {
                $number = (int)isset($_FILES['file']['name']) ? $_FILES['file']['name'] : 0;
                $zip    = new ZipArchive();

                if ($zh = $zip->open($file))
                {
                    $sub_dir = (bool)$zip->getFromName((int)$number . '/imsmanifest.xml');
                    var_dump($sub_dir);
                    if (!$sub_dir)
                    {
                        $manifest = (string)$zip->getFromName('imsmanifest.xml');
                    }
                    else
                    {
                        $manifest = (string)$zip->getFromName((int)$number . '/imsmanifest.xml');
                    }

                    $xml = simplexml_load_string($manifest);
                    if ($manifest && $xml)
                    {
                        $education         = ORM::factory('Education');
                        $education->number = $number;

                        $orgs    = $xml->organizations;
                        $attrs   = $orgs->attributes();
                        $default = (string)$attrs['default'];
                        $title   = '';
                        foreach ($orgs->children() as $org)
                        {
                            $attrs = $org->attributes();

                            if ((string)$attrs['identifier'] == $default)
                            {
                                $title = ((string)$org->title);
                            }
                        }

                        $education->title    = $title;
                        $education->language = $this->language;
                        $education->date=date("Y-m-d H:i:s");
                        $education->save();

                        $scdir  = realpath(APPPATH . '../media/scorm') . DIRECTORY_SEPARATOR;
                        $edudir = $scdir . $education->id . DIRECTORY_SEPARATOR;
                        umask(0);
                        mkdir($edudir, 0777);
                        $zip->extractTo($edudir);
                        $zip->close();

                        $types = array(
                            'anim' => 'Интерактивный материал',
                            'text' => 'Текстовый материал',
                            'test' => 'Интерактивный тест',
                            'task' => 'Задание',
                        );

                        $materials = $xml->resources;
                        foreach ($materials->children() as $material)
                        {
                            $attrs = $material->attributes();
                            foreach ($attrs as $attr => $value)
                            {
                                if ($attr == 'href')
                                {
                                    $match = array();
                                    preg_match('~([^/]+)/.*~', (string)$value, $match);
                                    $material               = ORM::factory('Education_Material');
                                    $material->education_id = $education->id;
                                    if (!$sub_dir)
                                    {
                                        $material->href = 'media/scorm/' . $education->id . DIRECTORY_SEPARATOR . (string)$value;
                                    }
                                    else
                                    {
                                        $material->href = 'media/scorm/' . $education->id . "/" . (int)$number . "/" . DIRECTORY_SEPARATOR . (string)$value;
                                    }

                                    $material->type  = $match[1];
                                    $material->title = $types[$match[1]];

                                    $material->path = $sub_dir ? (int)$number . "/" . (string)$value : (string)$value;
                                    $material->save();
                                }
                            }
                        }

                        Message::success('Файл был загружен, материалы добавлены');
                        $this->redirect('manage/education');
                    }
                    Message::error('Архив не содержит SCORM-данных, в корне архива не найдет imsmanifest.xml');
                    $this->redirect('manage/education');
                }
                Message::error('Произошла ошибка разбора архива');
                $this->redirect('manage/education');
            }
            Message::error('Файл не был выбран');
            $this->redirect('manage/education');
        }
    }

    public function action_edit()
    {
        $id    = (int)$this->request->param('id', 0);
        $token = Arr::get($_POST, 'token', false);
        $scorm = ORM::factory('Education', $id);

        if ($this->request->method() == Request::POST)
        {
            $scorm->values($_POST, array('title', 'language', 'number'));
            $scorm->save();
            Message::success('Изменения успешно сохранены');
            $this->redirect('manage/education');
        }

        $this->set('item', $scorm);
    }

    public function action_delete()
    {
        $id    = (int)$this->request->param('id', 0);
        $token = Arr::get($_POST, 'token', false);
        $scorm = ORM::factory('Education', $id);

        if (($this->request->method() == Request::POST) && $token === Security::token())
        {
            if ($scorm->loaded())
            {
                $scorm->delete();
            }

            Message::success('ЦОР успешно удален');
            $this->redirect('manage/education');
        }

        $this->set('record', $scorm)->set('token', Security::token(true));
    }

    public function action_publish()
    {
        $id    = (int)$this->request->param('id', 0);
        $scorm = ORM::factory('Education', $id);
        if ($scorm->loaded())
        {
            $scorm->published = ($scorm->published + 1) % 2;
            $scorm->save();
        }

        Message::success('ЦОР успешно изменен');
        $this->redirect('manage/education');
    }
}