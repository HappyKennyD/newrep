<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Eljournal extends Controller_Core
{

    public function before()
    {
        parent::before();
        $this->add_cumb('Magazines', 'eljournal');
    }

    public function action_index()
    {
        $this->redirect('/');
    }

}