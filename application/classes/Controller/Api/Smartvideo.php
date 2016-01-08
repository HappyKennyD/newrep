<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Smartvideo extends Controller_Api_Core
{

    public function action_index()
    {
        header('Access-Control-Allow-Origin: *');
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
        }

        $video = $video->order_by('date', 'DESC');
        $paginate = Paginate::factory($video)->paginate(NULL, NULL, 12)->page_count();
        $video = $video->find_all();

        $cat=ORM::factory('Category')->fulltree;

        foreach($cat as $c) {
            $this->data['cat'][] = array(
                'id' => $c->id,
                'name' => $c->name,
                'level' => $c->lvl,
                'url' => 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->language.URL::site('api/smartvideo/'.$c->id),
            );
        }

        foreach($video as $v)
        {
            $this->data['video'][] = array(
                'id' => $v->id,
                'link' => $v->link,
                'title' => $v->title,
                'description' => $v->description,
                'preview' => 'http://img.youtube.com/vi/'.$v->link.'/1.jpg',
                'url' => 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->language.URL::site('api/smartvideo/view/'.$v->id),
                'category' => $v->category->name,

            );
        }

        $this->data['sel_id'] = $id;
        $this->data['page_count'] = $paginate;

        $this->response->body(json_encode($this->data));
    }

    public function action_view()
    {
        header('Access-Control-Allow-Origin: *');
		$id = $this->request->param('id', 0);
        $video = ORM::factory('Video',$id);
        if ( ! $video->loaded())
        {
            $this->data['error'] = '404 Not Found';
            $this->response->body(json_encode($this->data));
            return;
        }
        $category = ORM::factory('Category',$video->category_id);

        $other_video = ORM::factory('Video')->where('published','=',1)
            ->and_where('language','=',$this->language)
            ->and_where('category_id','=',$category->id)
            ->and_where('id','!=',$id);
        if ($category->lvl==3)
        {
            $other_video = $other_video->order_by('date','asc');
        }
        else
        {
            $other_video = $other_video->limit(10)->order_by(Db::expr('rand()'));
        }


        $other_video = $other_video->find_all();
        
        $this->data['video'] = array(
            'id' => $video->id,
            'link' => $video->link,
            'title' => $video->title,
            'description' => $video->description,
            'preview' => 'http://img.youtube.com/vi/'.$video->link.'/1.jpg',
            'url' => 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->language.URL::site('api/smartvideo/view/'.$video->id),
            'category' => $video->category->name,    
        );

        foreach($video as $v)
        {
            $this->data['other_video'][] = array(
                'id' => $v->id,
                'link' => $v->link,
                'title' => $v->title,
                'description' => $v->description,
                'preview' => 'http://img.youtube.com/vi/'.$v->link.'/1.jpg',
                'url' => 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->language.URL::site('api/smartvideo/view/'.$v->id),
                'category' => $v->category->name,

            );
        }

        $this->response->body(json_encode($this->data));
    }
}
