<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Ornek extends Controller_Core {

    public function before()
    {
        parent::before();
        $title=ORM::factory('Specprojecttitle',3)->title;
        $this->set('title',$title);
    }


    public function action_index()
	{
        $sliders_spec=ORM::factory('Specproject')
            ->where('sproject','=',3)
            ->and_where('in_slider','=',1)
            ->and_where('spec_published','=',1)
            ->find_all()
            ->as_array(null,'id_publication');
        if(count($sliders_spec)>0){
            $sliders=ORM::factory('Publication')->where('id','in',$sliders_spec)->find_all();
            $this->set('sliders1',$sliders);
        }

        $middles_spec=ORM::factory('Specproject')
            ->where('sproject','=',3)
            ->and_where('in_middle','=',1)
            ->and_where('spec_published','=',1)
            ->find_all()
            ->as_array(null,'id_publication');
        if (count($middles_spec)){
            $middles=ORM::factory('Publication')->where('id','in',$middles_spec)->find_all();
            $this->set('middles',$middles);
        }

        $bottoms_spec=ORM::factory('Specproject')
            ->where('sproject','=',3)
            ->and_where('in_bottom','=',1)
            ->and_where('spec_published','=',1)
            ->find_all()
            ->as_array(null,'id_publication');
        if (count($bottoms_spec)>0){
            $bottoms=ORM::factory('Publication')->where('id','in',$bottoms_spec)->find_all();
            $this->set('bottoms',$bottoms);
        }
	}
    
    public function action_page()
	{
        $id = (int) $this->request->param('id',0);
        $publication=ORM::factory('Publication',$id);

        if ( !$publication->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $this->add_cumb(i18n::get('Тайны казахских орнаментов'),'ornek');
        $this->add_cumb($publication->title,'ornek');
        $this->set('item',$publication);
	}

}
