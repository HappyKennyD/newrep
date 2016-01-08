<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Map extends Controller_Manage_Core
{

    public function action_index()
    {
        $cities = ORM::factory('City')->find_all();
        $this->set('cities', $cities);
        //список городов
        //редактировать
    }

    public function action_point()
    {
        $xy = Security::xss_clean($this->request->param('id', 0));
        $e = explode('-', $xy);
        $x = round($e[0]);
        $y = round($e[1]);



    }

    public function action_edit()
    {
        $id = (int)$this->request->param('id', 0);
        $city = ORM::factory('City',$id);
        if (!$city->loaded())
            throw new HTTP_Exception_404('Page not found');
        $errors = array();
        $token = Arr::get($_POST, 'token', false);
        if ($this->request->method() == 'POST')
        {
            if (!empty($_POST['name']))
            {
                try
                {
                    $city->values($_POST, array('name', 'tooltip'))->save();

                    $this->redirect('manage/map/');
                }
                catch (ORM_Validation_Exception $e)
                {
                    $errors = $e->errors('');
                }
            }
            else
            {
                $errors = array('title' => 'text fields must not be empty');
            }
        }
        $this->set('city',$city)->set('errors', $errors)->set('r',Arr::get($_GET, 'r', 'manage/map'));
    }

    public function action_view()
    {
        $id = (int)$this->request->param('id', 0);
        $city = ORM::factory('City', $id);
        if (!$city->loaded())
            throw new HTTP_Exception_404('Page not found');
        $this->set('city',$city);
    }

}