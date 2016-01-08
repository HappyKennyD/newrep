<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Audio extends Controller_Core {

	public function action_index()
	{
        $category_id = (int) Arr::get($_GET, 'category', 0);
        //SEO. закрываем сортировку
        if ($category_id!=0)
        {
            $sort=1;
            Kotwig_View::set_global('sort',$sort);
        }
        //end_SEO
        $audio = ORM::factory('Audio')->where('published','=',1)->and_where('show_'.I18n::$lang, '=', 1)->and_where('storage_id', '>', 0);
        if($category_id!=0) {
            $audio = $audio->and_where('category_id', '=', $category_id);
            $audio = $audio->order_by('numb');
        } else $audio = $audio->order_by('title');
        $paginate = Paginate::factory($audio)->paginate(NULL, NULL, 12)->render();
        $audio = $audio->find_all();
        if($category_id!=0) {
            $this->add_cumb('Audio','audio');
            $category = ORM::factory('Audio_Category', $category_id);
            $this->add_cumb($category->name,'');
        } else $this->add_cumb('Audio','');
        $this->set('name_category', ($category_id!=0?$category->name:''));
        $this->set('selected_cat', $category_id);
        $this->set('audio', $audio);
        $this->set('paginate', $paginate);


        $for_category=ORM::factory('Audio')->where('published','=',1)->and_where('show_'.I18n::$lang, '=', 1)->and_where('storage_id', '>', 0)->find_all();
        $list_category=array();
        foreach( $for_category as $cat_id ){
            array_push($list_category,$cat_id->category_id);
            $category = ORM::factory('Audio_Category', $cat_id->category_id);
            $list_category = array_merge ($list_category,$category->rev());
        }
        $list_category = array_unique($list_category);
        $cats = ORM::factory('Audio_Category')->fulltree;
        $filter_cat=array();
        foreach( $cats as $cat ){
            if(in_array($cat->id,$list_category)){
                array_push($filter_cat,$cat);
             }
        }
        $this->set('cats', $filter_cat);
	}
}
