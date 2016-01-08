<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Maps extends Controller_Manage_Core
{
    public function action_index()
    {
        $districts = ORM::factory('District')->order_by('name_'.I18n::$lang)->find_all();
        $this->set('districts', $districts);
    }

    public function action_view()
    {
        $id = (int) $this->request->param('id', 0);
        $district = ORM::factory('District', $id);
        if (!$district->loaded())
        {
            throw new HTTP_Exception_404;
        }

        $this->set('district', $district);

        $this->set('points', ORM::factory('Point')->where('district_id', '=', $id)->order_by('name_'.I18n::$lang)->find_all());
    }

    public function action_point()
    {
        $xy = Security::xss_clean($this->request->param('id', 0));
        $e = explode('-', $xy);

        if (count($e)!=3)
        {
            throw new HTTP_Exception_404;
        }

        $district_id = (int) $e[0];
        $x = round($e[1]);
        $y = round($e[2]);

        $district = ORM::factory('District', $district_id);
        if (!$district->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $this->set('district', $district);

        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        $this->set('uploader', $uploader);

        if ($this->request->method() == 'POST')
        {
            $name = Security::xss_clean(Arr::get($_POST, 'name', ''));
            $desc = Security::xss_clean(Arr::get($_POST, 'desc', ''));
            $text = Security::xss_clean(Arr::get($_POST, 'text', ''));
            $image = (int) Arr::get($_POST, 'image', 0);
            $marker = (int) Arr::get($_POST, 'marker', 0);
            $published = (int) Arr::get($_POST, 'published', 0);

            try
            {
                $point = ORM::factory('Point');
                $point->district_id = $district_id;
                $point->name = $name;
                $point->desc = $desc;
                $point->text = $text;
                $point->image = $image;
                $point->marker = $marker;
                $point->x = $x;
                $point->y = $y;
                $point->published = $published;
                $point->save();
                //$event = ($id)?'edit':'create';
                //$loger = new Loger($event,$point->name);
                //$loger->logThis($point);

                $this->redirect('manage/maps/view/'.$district_id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
    }

    public function action_coor()
    {
        $xy = Security::xss_clean($this->request->param('id', 0));
        $e = explode('-', $xy);

        if (count($e)!=3)
        {
            throw new HTTP_Exception_404;
        }

        $point_id = (int) $e[0];
        $x = round($e[1]);
        $y = round($e[2]);
        $point = ORM::factory('Point',$point_id);
        $point->x = $x;
        $point->y = $y;
        $point->save();
        $this->redirect('manage/maps/view/'.$point->district_id);
    }

}