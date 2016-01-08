<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Map extends Controller_Core {

    public function action_view()
    {
        $id = (int) $this->request->param('id', 0);
        $point = ORM::factory('Point', $id);
        if (!$point->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $this->set('point', $point);
        $this->add_cumb('Int map','');
        $this->add_cumb($point->name,'/');
    }

}
