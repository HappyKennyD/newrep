<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Manage_Materials extends Controller_Manage_Core
{

    public function action_index()
    {
        $list = ORM::factory('Material')->where('is_moderator', '=', 1)->and_where('is_journal', '=', 0);
        $sort = Security::xss_clean(Arr::get($_GET, 'sort', 'work'));
        switch ($sort) {
            case "work":
                $list->and_where('status', '=', 2);
                $this->set('sort', 'work');
                break;
            case "accept":
                $list->and_where('status', '=', 1);
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

    public function action_ehistory()
    {
        $list = ORM::factory('Material')->where('status', '=', 1)->and_where('is_journal', '=', 1);
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
            $this->redirect('manage/materials');
        }
        $files = ORM::factory('Material_File')->where('material_id', '=', $id)->find_all();
        $this->set('files', $files);
        $this->set('material', $material);
    }

    public function action_accept()
    {
        $id = $this->request->param('id');
        $material = ORM::factory('Material', $id);
        if (!$material->loaded() or $material->status != 2 or $material->is_moderator == 0) {
            $this->redirect('manage/materials');
        }

        $user_id=$material->user_id;
        $lang=$material->lang_notice;
        $user_email=ORM::factory('User',$user_id)->email;
        $this->set('material', $material);
        $token = Arr::get($_POST, 'token', false);
        $return = Arr::get($_POST, 'r', 'manage/materials');
        $this->set('return', Url::site($return));
        if ($this->request->method() == Request::POST && Security::token() === $token) {
            $message = Arr::get($_POST, 'message', '');
            if ($message != '') {
                $material->status = 1;
                $material->mod_message = $message;
                $material->moderator_id = $this->user->id;
                $material->save();

                $prelang=i18n::lang();
                I18n::lang($lang);
                Email::connect();
                Email::View('review_accept_'.$lang);
                Email::set(array('message'=>I18n::get('Оставленный вами материал одобрен к публикации на портале "История Казахстана".')));
                Email::send($user_email, array('no-reply@e-history.kz', 'e-history.kz'), I18n::get('Рассмотрение материала на портале "История Казахстана" e-history.kz'), '', true);
                I18n::lang($prelang);

                $this->redirect('manage/materials');
            } else {
                $this->set('message', $message)->set('token', Security::token(true))->set('errors', true);
            }
        } else {
            $this->set('token', Security::token(true));
        }
    }

    public function action_reject()
    {
        $id = $this->request->param('id');
        $material = ORM::factory('Material', $id);
        if (!$material->loaded()  or $material->status != 2 or $material->is_moderator == 0) {
            $this->redirect('manage/materials');
        }

        $user_id=$material->user_id;
        $lang=$material->lang_notice;
        $user_email=ORM::factory('User',$user_id)->email;
        $this->set('material', $material);
        $token = Arr::get($_POST, 'token', false);
        $return = Arr::get($_POST, 'r', 'manage/materials');
        $this->set('return', Url::site($return));
        if ($this->request->method() == Request::POST && Security::token() === $token) {
            $message = Arr::get($_POST, 'message', '');
            if ($message != '') {
                $material->status = 0;
                $material->mod_message = $message;
                $material->moderator_id = $this->user->id;
                $material->save();

                $prelang=i18n::lang();
                I18n::lang($lang);
                Email::connect();
                Email::View('review_accept_'.$lang);
                Email::set(array('message'=>I18n::get('Редакционной коллегией портала "История Казахстана" было отказано в публикации оставленного вами материала.')));
                Email::send($user_email, array('no-reply@e-history.kz', 'e-history.kz'), I18n::get('Рассмотрение материала на портале "История Казахстана" e-history.kz'), '', true);
                I18n::lang($prelang);

                $this->redirect('manage/materials');
            } else {
                $this->set('message', $message)->set('token', Security::token(true))->set('errors', true);
            }
        } else {
            $this->set('token', Security::token(true));
        }
    }

    public function action_show()
    {
        $id = $this->request->param('id', 0);
        $material = ORM::factory('Material', $id);
        $material->mod_public = ($material->mod_public + 1) % 2;
        $material->save();
        $this->redirect('manage/materials');
    }

}