<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Test extends Controller_Manage_Core {

    public function action_index()
    {
        $contents = ORM::factory('Pages_Content')->find_all();
        foreach ($contents as $item)
        {
            $content = ORM::factory('Pages_Content', $item->id);
            $content->text_ru = Security::xss_clean($item->text_ru);
            $content->text_kz = Security::xss_clean($item->text_kz);
            $content->text_en = Security::xss_clean($item->text_en);
            $content->save();
            set_time_limit(2500);
        }
    }
}