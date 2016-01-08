<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Publications extends Controller_Core {

    public function action_index()
    {
        $list = ORM::factory('Publication')->where('title_'.$this->language, '<>', '')->where('published','=', 1)->order_by('order', 'DESC');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->add_cumb('Publications','/');
        $this->set('list', $list);
        $this->set('paginate', $paginate);
    }

    public function action_view()
    {
        $id = (int) $this->request->param('id', 0);
        $item = ORM::factory('Publication', $id);
        if (!$item->loaded())
        {
            throw new HTTP_Exception_404;
        }

        if (!$item->translation())
        {
            throw new HTTP_Exception_404('no_translation');
        }

        $comment = ORM::factory('Comment')->where('object_id', '=', $id)->order_by('date', 'ASC')->find_all();;

        $this->add_cumb('Publications','publications');
        $this->add_cumb($item->title,'/');
        $tags = $item->tags->where('type','=','publication')->find_all()->as_array('id','name');
        $tags = implode(', ', $tags);
        $this->set('item', $item)->set('tags',$tags)->set('comments', $comment);


        /*$query = DB::select('t2.news_id', array(DB::expr('COUNT("t2.id")'), 'cnt'))
            ->from(array('tags', 't'))
            ->join(array('tags', 't2'))
            ->on('t2.name', '=', 't.name')
            ->where('t.news_id', '=', $id)
            ->group_by('t2.news_id')
            ->order_by('cnt', 'Desc')
            ->limit(4);
        $result = $query->execute()->as_array();
        if ($result)
        {
            foreach ($result as $res)
            {
                if ($res['news_id'] != $id)
                {
                    $news_tag = ORM::factory('News', $res['news_id']);
                    $news_tags[] = array('id'=>$res['news_id'], 'title'=>$news_tag->title, 'date'=>$news_tag->date, 'desc'=>$news_tag->desc);
                }
            }
            if (isset($news_tags))
            {
                $this->set('news_tags', $news_tags)->set('count_news_tags',count($news_tags));
            }
        }*/

        $metadesc=$item->desc;
        if(!empty($metadesc)) {
            $this->metadata->description($metadesc);
        }
        else {
            $this->metadata->snippet($item->text);
        }
    }

}
