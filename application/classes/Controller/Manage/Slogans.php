<?php
/*
 * Реализация управления разделами сайта
 */
class Controller_Manage_Slogans extends Controller_Manage_Core
{
    protected $title = 'Разделы';

    /*
     * Список всех разделов и подразделов
     */
    public function action_index()
    {
//        $id = (int) $this->request->param('id', 0);

        $slogans = ORM::factory('slogan');
        $slogans = $slogans->find_all();
        $this->set('slogans', $slogans);
//        die($slogans);
        if ($this->request->method() == 'POST')
        {
            $arr_slogans = ORM::factory('slogan')->find_all();
            foreach ($arr_slogans as $slogans){
                $text = Security::xss_clean(Arr::get($_POST, $slogans->id, ''));
                try
                {
                    $slogans=ORM::factory('slogan', $slogans->id);
                    $slogans->text = $text;
                    $slogans->save();

                }
                catch (ORM_Validation_Exception $e)
                {
                    $errors = $e->errors($e->alias());
                    $this->set('errors',$errors);
                }
            }

            $this->redirect('manage/slogans/');

//            die($text);
        }
        //Перемещаем на место первого дочернего узла
        /*$hto = ORM::factory('Page',200);
        $pod_kogo = ORM::factory('Page',329);
        $hto->move_to_first_child($pod_kogo);
        */
        $search = Arr::get($_POST, 'search', '');
        if (!empty($search))
        {
            $this->redirect('manage/pages/search/'.$search);
        }
        $pages = ORM::factory('Page')->fulltree;
        $this->set('pages',$pages);
    }

    /*
     * Поиск по разделам
     */
    public function action_search()
    {
        $search = addslashes($this->request->param('id', ''));
        $this->set('search', $search);
        $query_m = DB::expr(' MATCH(name_ru, name_kz, name_en) ');
        $query_a = DB::expr(' AGAINST("'.$search.'") ');

        $pages = ORM::factory('Page')->where($query_m, '', $query_a)->find_all();
        $parents = array();
        foreach ($pages as $page)
        {
            $parents[$page->id] = array();
            $parent_id = $page->parent_id;
            while ($parent_id != 0)
            {
                $p = ORM::factory('Page', $parent_id);
                $parents[$page->id][] = $p;
                $parent_id = $p->parent_id;
            }
            $parents[$page->id] = array_reverse($parents[$page->id]);
        }
        $this->set('pages', $pages)->set('parents', $parents);

        $totalcount = sizeof($pages);
        $sorry = '';
        if ($totalcount==0)
        {
            $sorry = 'Извините, ничего не найдено';
        }
        $this->set('sorry', $sorry);
    }

    /*
     * Добавление нового раздела или подраздела
     */
    public function action_new()
    {
        $id = (int) $this->request->param('id', 0);
        $redirect = Arr::get($_GET,'redirect', 'pages');
        $parent_page = ORM::factory('Page',$id);
        if( $post = $this->request->post() )
        {
            $page = ORM::factory('Page');
            $page->name = Security::xss_clean(Arr::get($post,'name',''));
            $page->description = Security::xss_clean(Arr::get($post,'description',''));
            $page->values($post, array('static','key'));
            if ($parent_page->loaded())
            {
                $page->insert_as_last_child($parent_page);
            }
            else
            {
                $page->make_root();
            }
            if ($page->static){
                $content = ORM::factory('Pages_Content');
                $content->page_id = $page;
                $content->type = 'static';
                $content->date = date("Y-m-d H:i:s");
                $content->save();
            }
            $k=Security::xss_clean(Arr::get($post, 'key', ''));
            $e = explode('_', $k);
            if( $e[0] == 'organization'){
                $to = ORM::factory('Organization');
                $to->page_id = $page;
                $to->save();
            }
            Message::success('Раздел добавлен');
            $this->redirect('manage/'.$redirect);
        }
        $this->set('cancel_url', Url::media('manage/'.$redirect));
    }

    /*
     * Редактирование существующего раздела
     */
    public function action_edit()
    {
        $id = (int) $this->request->param('id', 0);
        $page = ORM::factory('Page',$id);
        $redirect = Arr::get($_GET,'redirect', 'pages');
        if ( !$page->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if( $post = $this->request->post() )
        {
            $page->static = 0;
            $page->name = Security::xss_clean(Arr::get($post,'name',''));
            $page->description = Security::xss_clean(Arr::get($post,'description',''));
            $page->values($_POST, array('static', 'key'))->save();
            if ($page->static and $page->content->where('type','=','static')->count_all() == 0)
            {
                $content = ORM::factory('Pages_Content');
                $content->page_id = $page;
                $content->type = 'static';
                $content->date = date("Y-m-d H:i:s");
                $content->save();
            }
            Message::success('Раздел сохранен');
            $this->redirect('manage/'.$redirect.'#'.$page->id);
        }
        $this->set('page',$page)->set('cancel_url', Url::media('manage/'.$redirect));
    }

    /*
     * Удаление раздела и всех подразделов с ним связанных
     */
    public function action_delete()
    {
        $id = (int)$this->request->param('id', 0);
        $redirect = Arr::get($_GET,'redirect', 'pages');
        $page = ORM::factory('Page', $id);
        if ( !$page->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {

            $page->delete();
            Message::success('Раздел удален');
            $this->redirect('manage/'.$redirect);
        }
        else
        {
            $this->set('record', $page)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/'.$redirect));
        }
    }

    /*
     * Перемещение раздела по списку вверх
     */
    public function action_up()
    {
        $id = (int)$this->request->param('id', 0);
        $redirect = Arr::get($_GET,'redirect', 'pages');
        $page = ORM::factory('Page', $id);
        if (!$page->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $page_up_brother = ORM::factory('Page')
            ->where('parent_id','=',$page->parent_id)
            ->and_where('rgt','=',$page->lft - 1)
            ->find();
        if (empty($page_up_brother->id))
        {
            Message::warn('Раздел уже находится на вверху списка');
            $this->redirect('manage/pages');
        }
        $page->move_to_prev_sibling($page_up_brother);
        Message::success('Раздел перемещен вверх');
        $this->redirect('manage/'.$redirect);
    }

    /*
    * Перемещение раздела по списку вниз
    */
    public function action_down()
    {
        $id = (int)$this->request->param('id', 0);
        $redirect = Arr::get($_GET,'redirect', 'pages');
        $page = ORM::factory('Page', $id);
        if (!$page->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $page_down_brother = ORM::factory('Page')
            ->where('parent_id','=',$page->parent_id)
            ->and_where('lft','=',$page->rgt + 1)
            ->find();
        if (empty($page_down_brother->id))
        {
            Message::warn('Раздел уже находится внизу списка');
            $this->redirect('manage/pages');
        }
        $page->move_to_next_sibling($page_down_brother);
        Message::success('Раздел перемещен вниз');
        $this->redirect('manage/'.$redirect);
    }

}