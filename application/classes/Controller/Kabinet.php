<<<<<<< HEAD
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
=======
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
>>>>>>> 3b228de6725e664597d96d986d02299d8e14cbd6
