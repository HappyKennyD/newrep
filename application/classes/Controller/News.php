<?php defined('SYSPATH') or die('No direct script access.');

class Controller_News extends Controller_Core {

	public function action_index()
	{
        $news = ORM::factory('News')->where('title_'.$this->language, '<>', '')->where('published','=', 1)->order_by('date', 'DESC');
        $paginate = Paginate::factory($news)->paginate(NULL, NULL, 10)->render();
        $news = $news->find_all();
        /*$news_comments = ORM::factory('News')
            ->with('comments')
            ->where('comments.table', '=', 'news')
            ->and_where('comments.status', '=', 1)
            ->group_by('comments.object_id')
            ->order_by(Db::expr('SUM(comments.status)'),'desc')
            ->limit(2)
            ->find_all();*/
        $comments = ORM::factory('Comment')
            ->where('table', '=', 'publications')
            ->and_where('status', '=', 1)
            ->group_by('object_id')
            ->order_by(Db::expr('SUM(status)'),'desc')
            ->limit(2)
            ->find_all()->as_array('id','object_id');
        if (count($comments))
        {
            $publications = ORM::factory('Publication')->where('id','IN', $comments)->find_all();
            $this->set('publications', $publications);
        }

        $this->add_cumb('News','/');
        $this->set('news', $news);
        $this->set('paginate', $paginate);
	}

    public function action_view()
    {
        $id = (int) $this->request->param('id', 0);
        $news = ORM::factory('News', $id);
        if (!$news->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $this->add_cumb('News','news');
        $this->add_cumb($news->title,'/');
        $tags = $news->tags->find_all()->as_array('id','name');
        $tags = implode(', ', $tags);
        $this->set('news', $news)->set('tags',$tags);
    }

}
