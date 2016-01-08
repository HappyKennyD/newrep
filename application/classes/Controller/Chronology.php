<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Chronology extends Controller_Core {

    public function action_event()
    {
        $id = (int) $this->request->param('id', 0);
        $event = ORM::factory('Chronology_Line',$id);
        if (!$event->loaded())
        {
            throw new HTTP_Exception_404('Not Found');
        }
        $this->add_cumb('Chronology of events','');
        $this->add_cumb($event->period->parent->name,'');
        $this->add_cumb($event->title,'');
        $this->set('item',$event);
    }

   /* public function action_period()
    {

    }*/

}
