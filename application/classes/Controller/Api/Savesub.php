<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api_Savesub extends Controller_Api_Private
{

    public function action_index()
    {
        if (!empty($this->post)){
        //$users_set = ORM::factory('Subscription_Setting')->where('user_id', '=', $this->user_id)->find();
        $save_settings = ORM::factory('Subscription_Setting')->where('user_id', '=', $this->user_id)->find();
        $save_subscription = ORM::factory('Subscription');
        //$categories = '';

        //$mes = array();
        $delete_sub = ORM::factory('Subscription')->where('user_id', '=', $this->user_id)->find_all();
        foreach ($delete_sub as $del) {
            $del->delete();
        }

        $ids = Security::xss_clean($this->post['subIds']);

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $save_subscription = ORM::factory('Subscription')
                    ->set('user_id', $this->user_id)
                    ->set('category_id', $id)
                    ->set('date', date('Y-m-d, H:i:s'))
                    ->save();
            }
        }

        $per = Security::xss_clean(trim(strtolower($this->post['interval'])));

        switch ($per) {
            case 'day':
                $period = 1;
                break;
            case 'week':
                $period = 2;
                break;
            case 'month':
                $period = 3;
                break;
            default:
                $period = 2;
                break;
        }

        $lang = Security::xss_clean(strtolower($this->post['language']));

        if (!$save_settings->loaded()) {
            $save_settings->user_id=$this->user_id;
            $save_settings->lang=$lang;
            $save_settings->period=$period;
            $save_settings->save();
        } else {
            $save_settings->lang=$lang;
            $save_settings->period=$period;
            $save_settings->save();
        }

        $this->response->body(json_encode(TRUE));

        /*
        if (!$users_set->loaded()) {
            $save_settings->set('user_id', $this->user_id)
                ->set('lang', $this->language)
                ->set('period', $period)
                ->save();
        }
        if ($users_set->lang != $this->language or $users_set->period != $period) {
            $users_set->set('user_id', $this->user_id)
                ->set('lang', $this->language)
                ->set('period', $period)
                ->save();
        }

        if (($save_settings->saved() or $users_set->saved() or
                ($users_set->lang == $this->language and $users_set->period == $period)) and
            $save_subscription->saved()
        ) {

            $this->response->body(json_encode('true'));
        } else {
            $this->response->body(json_encode($mes));
        }*/
        }
        else {
            $this->response->body(json_encode(FALSE));
        }

    }
}