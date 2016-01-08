<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Video extends Controller_Core {

	public function action_index()
	{
        $id = $this->request->param('id', 0);
        //SEO. закрываем сортировку
        if ($id!=0)
        {
            $sort=1;
            Kotwig_View::set_global('sort',$sort);
        }
        //end_SEO
        $category = ORM::factory('Category',$id);
        $video = ORM::factory('Video')->where('published','=',1)->and_where('language','=',$this->language);
        if ($category->loaded())
        {
            $video = $video->and_where_open()->and_where('category_id','=',$id);
            if ($category->has_children())
            {
                $video = $video->or_where('category_id', 'IN', $category->children->as_array('id'));
            }
            $video = $video->and_where_close();
            $this->add_cumb('Video','video');
            $this->add_cumb($category->name,'');
        }
        else
        {
            $this->add_cumb('Video','');
        }
        $video = $video->order_by('date', 'DESC');
        $paginate = Paginate::factory($video)->paginate(NULL, NULL, 12)->render();
        $video = $video->find_all();
        $categories = ORM::factory('Category')->find_all();
        $mas_categories = array();
        foreach ($categories as $item)
        {
            $video_count = ORM::factory('Video')->where('published','=',1)
                ->and_where('language','=',$this->language)
                ->and_where_open()->and_where('category_id','=',$item->id);
            /* if ($item->has_children())
            {
                $video_count = $video_count->or_where('category_id', 'IN', $item->children->as_array('id'));
            }
            $video_count = $video_count->and_where_close()->count_all(); */
            if ($video_count)
            {
                $mas_categories[] = $item;
            }
        }

        $cat=ORM::factory('Category')->fulltree;
        $count_comments = Comments::instance();
        $this->set('cat', $cat);
        $this->set('video', $video)->set('categories',$mas_categories)->set('select_id',$id);
        $this->set('paginate', $paginate)->set('count_comments', $count_comments);
	}

    public function action_view()
    {
        $id = $this->request->param('id', 0);
        $video = ORM::factory('Video',$id);
        if ( ! $video->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $category = ORM::factory('Category',$video->category_id);
        $this->add_cumb('Video','video');
        $other_video = ORM::factory('Video')->where('published','=',1)
            ->and_where('language','=',$this->language)
            ->and_where('category_id','=',$category->id)
            ->and_where('id','!=',$id);
        if ($category->lvl==3)
        {
            $this->add_cumb($category->parent->name, 'video/'.$category->parent->id);
            $this->add_cumb($category->name, '');
            $other_video = $other_video->order_by('date','asc');
        }
        else
        {
            $this->add_cumb($category->name, 'video/'.$category->id);
            $other_video = $other_video->limit(10)->order_by(Db::expr('rand()'));
        }

        $this->add_cumb($video->title, '');

        $other_video = $other_video->find_all();
        $this->set('video', $video)->set('other_video',$other_video);

        $this->metadata->snippet($video->description);
    }

}
