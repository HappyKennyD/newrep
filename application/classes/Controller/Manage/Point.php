<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Point extends Controller_Manage_Core {

    public function action_view()
    {
        $id = $this->request->param('id', 0);
        $point = ORM::factory('Point', $id);
        if (!$point->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $this->set('point', $point);
    }

    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $point = ORM::factory('Point', $id);
        if (!$point->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $this->set('point', $point);

        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        $this->set('uploader', $uploader);

        if ($this->request->method() == 'POST')
        {
            $name = Security::xss_clean(Arr::get($_POST, 'name', ''));
            $desc = Security::xss_clean(Arr::get($_POST, 'desc', ''));
            $text = Security::xss_clean(Arr::get($_POST, 'text', ''));
            $district_id = (int) Arr::get($_POST, 'district_id', 0);
            $image = (int) Arr::get($_POST, 'image', 0);
            $marker = (int) Arr::get($_POST, 'marker', 0);
            $published = (int) Arr::get($_POST, 'published', 0);

            try
            {
                $point->district_id = $district_id;
                $point->name = $name;
                $point->desc = $desc;
                $point->text = $text;
                $point->image = $image;
                $point->marker = $marker;
                $point->published = $published;
                $point->save();
                $event = ($id)?'edit':'create';
                $loger = new Loger($event,$point->name);
                $loger->logThis($point);

                $this->redirect('manage/point/view/'.$point->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $point = ORM::factory('Point', $id);
        if (!$point->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $loger = new Loger($event,$point->name);
            $loger->logThis($point);
            $redirect = 'manage/maps/view/'.$point->district_id;
            $point->delete();
            $this->redirect($redirect);
        }
        else
        {
            $this->set('record', $point)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/maps/view/'.$point->district_id));
        }
    }

    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $point = ORM::factory('Point', $id);
        if (!$point->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if ($point->published)
        {
            $point->published = 0;
            $point->save();
        }
        else
        {
            $point->published = 1;
            $point->save();
        }

        $this->redirect('manage/maps/view/'.$point->district_id);
    }

    public function action_clearImage()
    {
        $id = $this->request->param('id', 0);
        $point = ORM::factory('Point', $id);
        if ($point->loaded())
        {
            $point->image = 0;
            $point->save();
        }
        $this->redirect('manage/point/edit/'.$id);
    }
}
