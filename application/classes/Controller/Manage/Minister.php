<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Minister extends Controller_Manage_Core {

	public function action_index()
	{
        $minister = ORM::factory('Page')->where('key', '=', 'minister')->find();
        if ($minister->loaded())
        {
            $this->set('item', $minister);
            $minister_content = ORM::factory('Pages_Content')->where('page_id', '=', $minister->id)->find();
            $this->set('item_cont', $minister_content);
        }
        else $this->set('error', 0);

	}

    public function action_edit()
    {
        $id = (int)$this->request->param('id', 0);
        $minister = ORM::factory('Page', $id);
        $errors = 0;
        $minister_content = ORM::factory('Pages_Content')->where('page_id', '=', $id)->find();

        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        $this->set('uploader',$uploader);
        if ($this->request->method() == 'POST')
        {
            try
            {
                $minister->name = Security::xss_clean($_POST['title']);
                //$minister->description = Security::xss_clean($_POST['desc']);
                $minister->key = 'minister';
                $minister->static = 1;
                $minister->save();

                $minister_content->page_id = $minister->id;
                $minister_content->type = 'static';
                $minister_content->title = Security::xss_clean($_POST['title']);
                $minister_content->description = Security::xss_clean($_POST['desc']);
                $minister_content->date = date('Y-m-d H:i:s');
                $minister_content->published = 1;
                $minister_content->text = Security::xss_clean($_POST['text']);
                $minister_content->image = (int)$_POST['image'];
                $minister_content->save();

                //заполнение publication_type, publication_id в storage
                $storage = ORM::factory('Storage', $minister_content->image);
                $storage->publication_type = 'page';
                $storage->publication_id = $minister->id;
                $storage->save();
                $pattern = '/<img.+?src="\/?(.+?)".*?>/';
                if (preg_match_all($pattern, $_POST['text'], $matches))
                {
                    foreach ($matches[1] as $match)
                    {
                        $storage_path = ORM::factory('Storage')->where('file_path', 'like', $match)->find();
                        if ($storage_path)
                            $st = ORM::factory('Storage',$storage_path->id);
                            if ($st->loaded())
                            {
                                $st->publication_type = 'page';
                                $st->publication_id = $minister->id;
                                $st->save();
                            }

                    }
                }
                ///////////////////////////////////
                $this->redirect('manage/minister/index');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = 1;
            }

            $this->set('errors', $errors);
        }

        $this->set('item', $minister)->set('item_cont', $minister_content);
    }

    public function action_clearImage()
    {
        $id = (int)$this->request->param('id', 0);
        $minister = ORM::factory('Pages_Content')->where('page_id', '=', $id)->find();
        if ($minister->loaded())
        {
            $minister->image = 0;
            $minister->save();
        }
        $this->redirect('manage/minister/edit/'.$id);
    }

}
