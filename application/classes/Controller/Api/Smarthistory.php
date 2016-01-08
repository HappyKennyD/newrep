<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Smarthistory extends Controller_Api_Core
{

    public function action_index()
    {
        header('Access-Control-Allow-Origin: *');
		$p = ORM::factory('Page')->where('key', '=', 'history')->find();
        $page_main = array();
        $children_pages = array();
        $children_pages_last = array();
        $children_pages_last_last = array();

        $page_main = array('id' => $p->id, 'name' => $p->name, 'description' => $p->description);
        $children_pages = $p->children(); //ORM::factory('Page')->where('parent_id','=',$p->id)->find_all();//только первый уровень детей
        $this->data['page_main'] = $page_main;
        //второй уровень детей.
        foreach ($children_pages as $ch_p) {


            if ($ch_p->id == 232 OR $ch_p->id == 159) {
                continue;
            }

            $childs = array(
                'id' => $ch_p->id,
                'name' => $ch_p->name,
                'description' => $ch_p->description
            );

            $children_pages_last[$ch_p->id] = $ch_p->children();
            //третий уровень детей
            foreach ($children_pages_last[$ch_p->id] as $ch_p_l) {
                $childsl = array(
                    'id' => $ch_p_l->id,
                    'name' => $ch_p_l->name,
                    'description' => $ch_p_l->description,
                    'url' => 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->language.URL::site('api/smarthistory/list/'.$ch_p_l->id)
                );
                $children_pages_last_last[$ch_p_l->id] = $ch_p_l->children();
                foreach ($children_pages_last_last[$ch_p_l->id] as $ch_p_l_l)
                {
                    $childsl['childs'][] = array(
                        'id' => $ch_p_l_l->id,
                        'name' => $ch_p_l_l->name,
                        'description' => $ch_p_l_l->description,
                        'url' => 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->language.URL::site('api/smarthistory/list/'.$ch_p_l_l->id)
                    );
                }
                $childs['childs'][] = $childsl;

            }
            $this->data['childs'][] = $childs;

        }


        $this->response->body(json_encode($this->data));
    }

    public function action_list()
    {
        header('Access-Control-Allow-Origin: *');
		$id = (int) $this->request->param('id', 0);
        $page = ORM::factory('Page',$id);
        $key_arr = array('publications', 'debate','expert','organization');

        $e = explode('_', $page->key);

        $paginate = '';

        if (!$page->loaded())
        {
            $this->data['error'] = '404';
            $this->response->body(json_encode($this->data));
            return;
        }

        if ($page->static)
        {
            $this->data['error'] = 'STATIC PAGE';
            $this->response->body(json_encode($this->data));
            return;
        }else
        {
            //$this->data[''];
            $this->data['page'] = array(
                'id' => $page->id,
                'name' => $page->name,
                'description' => $page->description
            );

            $contents = $page->content->where('published', '=', 1)->where('title_'.I18n::$lang, '<>', '')->order_by('date', 'DESC');

            if ($id==378 || $id==384 || $id==385)

                $paginate = Paginate::factory($contents)->paginate(NULL, NULL, 400)->page_count();

            else
                $paginate = Paginate::factory($contents)->paginate(NULL, NULL, 10)->page_count();

            $contents = $contents->find_all();

            if ($page->key == 'message-list' OR $page->key == 'articles-list')
            {
                $c = $page->content->where('published', '=', 1)->order_by('date', 'DESC')->find();

                $cont = array(
                    'id' => $c->id,
                    'page_id' => $c->page_id,
                    'type' => $c->type,
                    'title' => $c->title,
                    'description' => $c->description,
                );
                if ($c->picture->file_path)
                    $cont['image'] = URL::media('images/w300-h225-ccc-si/'.$c->picture->file_path);
                else
                    $cont['image'] = URL::media('images/w300-h225-ccc-si/media/images/nocover.jpg');

                $this->data['last_message'][] = $cont;

            }

            foreach($contents as $k=>$c){
                $cont = array(
                    'id' => $c->id,
                    'page_id' => $c->page_id,
                    'type' => $c->type,
                    'title' => $c->title,
                    'description' => $c->description,
                    'url' => 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->language.URL::site('api/smarthistory/view/'.$c->id)

                );
                if ($c->picture->file_path)
                    $cont['image'] = 'http://'.$_SERVER['HTTP_HOST'].URL::media('images/w300-h225-ccc-si/'.$c->picture->file_path);
                else
                    $cont['image'] = 'http://'.$_SERVER['HTTP_HOST'].URL::media('images/w300-h225-ccc-si/media/images/nocover.jpg');

                $this->data['contents'][] = $cont;
            }

            $this->data['pagecount'] = $paginate;
        }

        $this->data['pagecount'] = $paginate;
        $cont_p = ORM::factory('Page')->where('parent_id', '=', $id)->find_all();
        $this->data['cont_p'] = $cont_p->as_array();

        $this->response->body(json_encode($this->data));
    }

    public  function action_view()
    {
        header('Access-Control-Allow-Origin: *');
		$id = (int) $this->request->param('id', 0);
        $content = ORM::factory('Pages_Content')->where('id','=',$id)->and_where('published','=',1)->find();
        if (!$content->loaded())
        {
            $this->data['error'] = '404 Not Found';
            $this->response->body(json_encode($this->data));
            return;
        }
        if (!$content->translation())
        {
            $this->data['error'] = '404 No translation';
            $this->response->body(json_encode($this->data));
            return;
        }
        $page = ORM::factory('Page',$content->page_id);
        $path = $page->parents();

        $this->data['page'] = array(
            'id' => $page->id,
            'name' => $page->name,
            'description' => $page->description
        );

        $c = $content;
        $cont = array(
            'id' => $c->id,
            'page_id' => $c->page_id,
            'type' => $c->type,
            'title' => $c->title,
            'description' => $c->description,
            'text' => $c->text

        );
        if ($c->picture->file_path)
            $cont['image'] = URL::media('images/w300-h225-ccc-si/'.$c->picture->file_path);
        else
            $cont['image'] = URL::media('images/w300-h225-ccc-si/media/images/nocover.jpg');

        $this->data['contents'][] = $cont;

        $this->response->body(json_encode($this->data));
    }
}

