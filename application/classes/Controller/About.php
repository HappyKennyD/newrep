<?php defined('SYSPATH') or die('No direct script access.');

class Controller_About extends Controller_Core {

    public function action_index()
    {
        $thanks = ORM::factory('Thank')->where('published','=', 1)->order_by('order');
        $paginate = Paginate::factory($thanks)->paginate(NULL, NULL, 10)->render();
        $thanks = $thanks->find_all();
        $this->add_cumb('About project','/');
        $this->set('thanks', $thanks);
        $this->set('paginate', $paginate);
    }

}
