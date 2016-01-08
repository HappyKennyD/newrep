<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Smartpublications extends Controller_Api_Core
{

    public function action_index()
    {
        header('Access-Control-Allow-Origin: *');
		$search = Security::xss_clean(isset($_GET['search'])?$_GET['search']:'');
        if (!empty($search))
        {
            $query_b = '%' . $search . '%';
            $this->searchText = Database::instance()->escape($search);
            $query_a = DB::expr(' AGAINST(' . $this->searchText . ') ');
            $list = ORM::factory('Publication')->distinct('true')->where(DB::expr('MATCH(title_' . $this->language . ')'), '', $query_a)->or_where(DB::expr('MATCH(desc_' . $this->language . ')'), '', $query_a)->or_where(DB::expr('MATCH(text_' . $this->language . ')'), '', $query_a)->or_where('title_' . $this->language, 'like', $query_b)->and_where('published', '=', 1)->limit($this->limit)->offset($this->offset)->find_all();
        } else {
            $list = ORM::factory('Publication')->where('title_'.$this->language, '<>', '')->where('published','=', 1)->order_by('order', 'DESC');
            $this->data['page_count'] = Paginate::factory($list)->paginate(NULL, NULL, 10)->page_count();
            $list = $list->find_all();
        }


        $pub = array();
        $this->data['search'] = $search;
        foreach ($list as $k=>$v) {
            $pub['id'] = $v->id;
            $pub['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->language.URL::site('api/smartpublications/view/'.$v->id);
            $pub['title'] = $v->title;
            $pub['desc'] = strip_tags($v->desc);
            $pub['image'] = 'http://'.$_SERVER['HTTP_HOST'].URL::media('/images/w205-h160/'.$v->picture->file_path);
            $this->data['publications'][] = $pub;
        }

        $this->response->body(json_encode($this->data));
    }

    public function action_view()
    {
        header('Access-Control-Allow-Origin: *');
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

        $this->data['id'] = $item->id;
        $this->data['title'] = $item->title;
        $this->data['text'] = $item->text;
        $this->data['image'] = 'http://'.$_SERVER['HTTP_HOST'].URL::media($item->picture->file_path);

        $tags = $item->tags->where('type','=','publication')->find_all()->as_array('id','name');
        $tags = implode(', ', $tags);
        $this->data['tags'] = (array) $tags;

        $this->response->body(json_encode($this->data));
    }
}
