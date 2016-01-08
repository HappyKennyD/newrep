<?php defined('SYSPATH') or die('No direct script access.');

class Controller_First extends Controller_Core {

	public function action_index()
	{
        $list=ORM::factory('First')->where('show_'.I18n::$lang,'=','1')->and_where('is_published','=','1')->find_all();
        $this->set('list',$list);
        $this->add_cumb(Kohana_I18n::get('Хроника деятельности Первого Президента'),'');
    }

    public function action_view()
    {
        $id=$this->request->param('id',0);
        $item=ORM::factory('First',$id);
        if (!$item->loaded()){
            throw new HTTP_Exception_404;
        }
        $this->set('item',$item);
        $this->add_cumb(Kohana_I18n::get('Хроника деятельности Первого Президента'),'first');
        $this->add_cumb($item->title,'');
    }
}
