<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Kabinet extends Controller_Core {

    public function action_index()
    {
        if(!Auth::instance()->logged_in())
        {
            $this->redirect('/auth/register');
        }
    }

    public function action_test()
    {

    }
}
