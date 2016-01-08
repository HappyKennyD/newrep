<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_First extends Controller_Manage_Core {

	public function action_index()
	{
        $first_list=ORM::factory('First')->find_all();
        $this->set('firstlist',$first_list);
    }

    public function action_new()
    {
        if (($this->request->method() == Request::POST)){
            $first_list=ORM::factory('First');

            $link=arr::get($_POST,'link','');
            $title=arr::get($_POST,'title','');

            try{
            $first_list->link=$link;
            $first_list->title=$title;
            $first_list->save();
            $this->redirect('manage/first');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
    }

    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $first_list=ORM::factory('First',$id);

        if ( !$first_list->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $this->set('item',$first_list);

        if (($this->request->method() == Request::POST)){
            $link=arr::get($_POST,'link','');
            $title=arr::get($_POST,'title','');

            try{
                $first_list->link=$link;
                $first_list->title=$title;
                $first_list->save();
                $this->set('success','');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
    }

    public function action_show_ru()
    {
        $id = $this->request->param('id', 0);
        $first = ORM::factory('First', $id);
        if ( !$first->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $first->show_ru = $first->show_ru == 0 ? 1 : 0;
        $first->save();
        $this->redirect('manage/first/');
    }

    public function action_show_kz()
    {
        $id = $this->request->param('id', 0);
        $first = ORM::factory('First', $id);
        if ( !$first->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $first->show_kz = $first->show_kz == 0 ? 1 : 0;
        $first->save();
        $this->redirect('manage/first/');
    }

    public function action_show_en()
    {
        $id = $this->request->param('id', 0);
        $first = ORM::factory('First', $id);
        if ( !$first->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $first->show_en = $first->show_en == 0 ? 1 : 0;
        $first->save();
        $this->redirect('manage/first/');
    }

    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $first = ORM::factory('First', $id);
        if ( !$first->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $first->is_published = $first->is_published == 0 ? 1 : 0;
        $first->save();
        $this->redirect('manage/first/');
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $audio = ORM::factory('First', $id);
        if (!$audio->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $audio->delete();
        $this->redirect('manage/first/');
    }

}
