<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Shezhire extends Controller_Core {

    public function before()
    {
        parent::before();
        $title=ORM::factory('Specprojecttitle',4)->title;
        $this->set('title',$title);
    }

	public function action_index()
	{
        $sliders_spec=ORM::factory('Specproject')
            ->where('sproject','=',4)
            ->and_where('in_slider','=',1)
            ->and_where('spec_published','=',1)
            ->find_all()
            ->as_array(null,'id_publication');
        if(count($sliders_spec)>0){
            $sliders=ORM::factory('Publication')->where('id','in',$sliders_spec)->find_all();
            $this->set('sliders1',$sliders);
        }

        $middles_spec=ORM::factory('Specproject')
            ->where('sproject','=',4)
            ->and_where('in_middle','=',1)
            ->and_where('spec_published','=',1)
            ->find_all()
            ->as_array(null,'id_publication');
        if (count($middles_spec)){
            $middles=ORM::factory('Publication')->where('id','in',$middles_spec)->find_all();
            $this->set('middles',$middles);
        }

        $bottoms_spec=ORM::factory('Specproject')
            ->where('sproject','=',4)
            ->and_where('in_bottom','=',1)
            ->and_where('spec_published','=',1)
            ->find_all()
            ->as_array(null,'id_publication');
        if (count($bottoms_spec)>0){
            $bottoms=ORM::factory('Publication')->where('id','in',$bottoms_spec)->find_all();
            $this->set('bottoms',$bottoms);
        }
	}
    
    public function action_zhuz()
	{
        $zhuz_type=$this->request->param('id','old');

        switch ($zhuz_type){
            case 'old': $id=6; break;
            case 'mid': $id=3; break;
            case 'jr': $id=4; break;
        }

        $all=ORM::factory('Zhuze')->where('parent_id','=',$id)->order_by('name_'.strtolower(I18n::lang()))->find_all();

        $words=array();

        if (count($all)>0){
            $letter='А';
            foreach ($all as $one){
                if (UTF8::strtoupper(UTF8::substr($one->name, 0 , 1))!=$letter){
                    $letter=(UTF8::strtoupper(UTF8::substr($one->name, 0 , 1)));
                }
                $words[$letter][]=array('letter'=>UTF8::strtoupper(UTF8::substr($one->name, 0 , 1)), 'word'=>$one->name, 'id'=>$one->id_publication);
            }
        }

        //var_dump($words);


        $this->add_cumb(i18n::get('Шежире – древо единства казахов'),'shezhire');
        if ($zhuz_type=='old'){
            $this->add_cumb(i18n::get('Старший жуз'),'');
        } elseif ($zhuz_type=='mid'){
            $this->add_cumb(i18n::get('Средний жуз'),'');
        } else{
            $this->add_cumb(i18n::get('Младший жуз'),'');
        }
        $this->set('nomer',0);
        $this->set('zhuz',$zhuz_type);
        $this->set('words',$words);
	}
    
    public function action_page()
	{
        $id = (int) $this->request->param('id',0);
        $publication=ORM::factory('Publication',$id);

        if ( !$publication->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $this->add_cumb(i18n::get('Шежире – древо единства казахов'),'shezhire');
        $this->add_cumb($publication->title,'shezhire');
        $this->set('item',$publication);
	}

}
