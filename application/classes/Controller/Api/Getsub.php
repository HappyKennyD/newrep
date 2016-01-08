<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api_Getsub extends Controller_Api_Private
{

    public function action_index()
    {
        $all_subs_user = ORM::factory('Subscription')->where('user_id', '=', $this->user_id)->find_all()->as_array(null, 'category_id');
        if (count($all_subs_user) == 0) {
            $all_subs_user = array();
        }

        $sub_settings = ORM::factory('Subscription_Setting')->where('user_id', '=', $this->user_id)->find();

        if ($sub_settings->loaded()) {
            $this->data['language']=$sub_settings->lang;
            switch (strtolower($sub_settings->period)) {
                case 1:
                    $this->data['interval'] = 'day';
                    break;
                case 2:
                    $this->data['interval'] = 'week';
                    break;
                case 3:
                    $this->data['interval'] = 'month';
                    break;
                default:
                    $this->data['interval'] = 'week';
                    break;
            }
        } else {
            $this->data['language'] = '';
            $this->data['interval'] = '';
        }

        if (empty($this->data['language'] )) {
            $this->data['language'] = 'ru';
        }

        if (empty($this->data['interval'] )) {
            $this->data['interval'] = 'week';
        }


        $categories = ORM::factory('Page')->where('lvl', '=', 2)->find_all();

        foreach ($categories as $category) {
            $subcategories = ORM::factory('Page')->where('lvl', '=', '3')->and_where('parent_id', '=', $category->id)->find_all();
            $all_subcat = array();
            foreach ($subcategories as $subcategory) {
                if (in_array($subcategory->id, $all_subs_user)) {
                    $all_subcat[] = array(
                        'id' => $subcategory->id,
                        'name' => $subcategory->name,
                        'isSub' => true,
                        'children' => array()
                    );
                } else {
                    $all_subcat[] = array(
                        'id' => $subcategory->id,
                        'name' => $subcategory->name,
                        'isSub' => false,
                        'children' => array()
                    );
                }
            }
            if (in_array($category->id, $all_subs_user)) {
                $this->data['categories'][] = array(
                    'id' => $category->id,
                    'name' => $category->name,
                    'isSub' => true,
                    'children' => $all_subcat
                );
            } else {
                $this->data['categories'][] = array(
                    'id' => $category->id,
                    'name' => $category->name,
                    'isSub' => false,
                    'children' => $all_subcat
                );
            }
        }

        $this->response->body(json_encode($this->data));
    }
}