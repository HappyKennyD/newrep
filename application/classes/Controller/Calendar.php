<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Calendar extends Controller_Core {

    public function action_event()
    {
        $id = (int) $this->request->param('id', 0);
        if ($id)
        {
            $select_event = ORM::factory('Calendar',$id);
            if (!$select_event->loaded())
            {
                throw new HTTP_Exception_404;
            }
            $month = (int) $select_event->month;
            $day = (int) $select_event->day;
        }
        else
        {
            $month = (int) $this->request->param('m', date('m'));
            $day = (int) $this->request->param('d', date('d'));
        }
        $this->add_cumb('Календарь событий','');
        $this->add_cumb(Date::ru_date('2013-'.$month.'-'.$day,'j F'),'');
        $list = ORM::factory('Calendar')->where('day','=',$day)->and_where('month','=',$month)->find_all();
        $list_tomorrow = ORM::factory('Calendar')->where('day','=',date("j", strtotime("tomorrow")))->and_where('month','=',date("n", strtotime("tomorrow")))->find_all();
        $tomorrow = date("Y-m-d", strtotime("tomorrow"));
        $this->set('month',$month)->set('day',$day)->set('list',$list)->set('list_tomorrow',$list_tomorrow)->set('tomorrow',$tomorrow)->set('id',$id);
    }

}
