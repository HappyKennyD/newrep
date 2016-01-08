<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Links extends Controller_Manage_Core {

    public function action_index()
    {
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        if (!empty($search))
        {
            $this->redirect('manage/links/search/'.$search);
        }
        $links = ORM::factory('Link')->order_by('order');
        $paginate = Paginate::factory($links)->paginate(NULL, NULL, 10)->render();
        $links = $links->find_all();
        $this->set('list', $links);
        $this->set('paginate', $paginate);
    }

    public function action_search()
    {
        $search = Security::xss_clean(addslashes($this->request->param('id', '')));
        $this->set('search', $search);
        $query_m = DB::expr(' MATCH(title_ru, title_kz, title_en) ');
        $query_a = DB::expr(' AGAINST("'.$search.'") ');

        $links = ORM::factory('Link')->where($query_m, '', $query_a)->find_all();
        $this->set('list', $links);

        $totalcount = sizeof($links);
        $sorry = '';
        if ($totalcount==0)
        {
            $sorry = 'Извините, ничего не найдено';
        }
        $this->set('sorry', $sorry);
    }

    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $link = ORM::factory('Link', $id);
        $errors = NULL;
        if ( $post = $this->request->post() )
        {
            try
            {
                if ($id == 0)
                {
                    $last = ORM::factory('Link')->order_by('order','Desc')->find();
                    $link->order = ($last->order + 1);
                }

                $link->title = Security::xss_clean(Arr::get($post,'title',''));
                $link->desc = Security::xss_clean(Arr::get($post,'desc',''));
                $link->link = Security::xss_clean(Arr::get($post,'link',''));
                $link->save();

                Message::success('Сохранено');
                $this->redirect('manage/links');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('item', $link);
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $link = ORM::factory('Link', $id);
        if (!$link->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $link->delete();
            Message::success('Удалено');
            $this->redirect('manage/links');
        }
        else
        {
            $this->set('record', $link)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/links'));
        }
    }

    public function action_down()
    {
        $id = (int)$this->request->param('id', 0);
        $link = ORM::factory('Link', $id);
        if (!$link->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $link->order = ($link->order+1);
        $link->save();

        $this->redirect('manage/links');
    }

    public function action_up()
    {
        $id = (int)$this->request->param('id', 0);
        $link = ORM::factory('Link', $id);
        if (!$link->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if ($link->order > 0)
        {
            $link->order = ($link->order-1);
            $link->save();
        }

        $this->redirect('manage/links');
    }

}
