<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Manage_Ehistory extends Controller_Manage_Core
{
    public function before()
    {
        parent::before();
        $input = $this->user->has('roles', ORM::factory('role', array('name' => 'redactor')));
        if (!$input) {
            $this->redirect('manage/materials');
        }
    }

    public function action_index()
    {
        $list = ORM::factory('Material')->where('is_journal', '=', 1)->where('is_moderator', '=', 0);
        $sort = Security::xss_clean(Arr::get($_GET, 'sort', 'work'));
        switch ($sort) {
            case "work":
                $list->and_where('status', '=', 2);
                $this->set('sort', 'work');
                break;
            case "accept":
                $list->where('status', '=', 1);
                $this->set('sort', 'accept');
                break;
            case "reject":
                $list->and_where('status', '=', 0);
                $this->set('sort', 'reject');
                break;
            default:
                $this->set('sort', 'all');
        }
        $list->order_by('date', 'DESC');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->set('materials', $list);
        $this->set('paginate', $paginate);
    }

    public function action_view()
    {
        $id = $this->request->param('id');
        $material = ORM::factory('Material', $id);
        if (!$material->loaded()) {
            $this->redirect('manage/ehistory');
        }
        $files = ORM::factory('Material_File')->where('material_id', '=', $id)->find_all();
        $this->set('files', $files);
        $this->set('material', $material);
    }

    public function action_accept()
    {
        $id = $this->request->param('id');
        $material = ORM::factory('Material', $id);
        if (!$material->loaded() or $material->status != 2 or $material->is_moderator == 1) {
            $this->redirect('manage/ehistory');
        }
        $this->set('material', $material);
        $token = Arr::get($_POST, 'token', false);
        $return = Arr::get($_POST, 'r', 'manage/ehistory');
        $this->set('return', Url::site($return));
        if ($this->request->method() == Request::POST && Security::token() === $token) {
           $message = Arr::get($_POST, 'message', '');
            if ($message != '') {
                $material->status = 1;
                $material->mod_message = $message;
                $material->moderator_id = $this->user->id;
                $material->save();
                $this->redirect('manage/ehistory');
            }
            else {
                $this->set('message',$message)->set('token', Security::token(true))->set('errors',true);
            }
        } else {
            $this->set('token', Security::token(true));
        }
    }

    public function action_reject()
    {
        $id = $this->request->param('id');
        $material = ORM::factory('Material', $id);
        if (!$material->loaded() or $material->status != 2 or $material->is_moderator == 1) {
            $this->redirect('manage/ehistory');
        }
        $this->set('material', $material);
        $token = Arr::get($_POST, 'token', false);
        $return = Arr::get($_POST, 'r', 'manage/ehistory');
        $this->set('return', Url::site($return));
        if ($this->request->method() == Request::POST && Security::token() === $token) {
            $message = Arr::get($_POST, 'message', '');
            if ($message != '') {
                $material->status = 0;
                $material->mod_message = $message;
                $material->moderator_id = $this->user->id;
                $material->save();
                $this->redirect('manage/ehistory');
            }
            else {
                $this->set('message',$message)->set('token', Security::token(true))->set('errors',true);
            }
        } else {
            $this->set('token', Security::token(true));
        }
    }

}