<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Acts extends Controller_Manage_Core {

    public function action_index()
    {
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        if (!empty($search))
        {
            $this->redirect('manage/acts/search/'.$search);
        }
        $acts = ORM::factory('Acts')->order_by('date', 'DESC');
        $paginate = Paginate::factory($acts)->paginate(NULL, NULL, 10)->render();
        $acts = $acts->find_all();
        $this->set('acts', $acts);
        $this->set('paginate', $paginate);
    }

    public function action_search()
    {
        $search = Security::xss_clean(addslashes($this->request->param('id', '')));
        $this->set('search', $search);
        $query_m = DB::expr(' MATCH(title_ru, title_kz, title_en) ');
        $query_a = DB::expr(' AGAINST("'.$search.'") ');

        $acts = ORM::factory('Acts')->where($query_m, '', $query_a)->find_all();
        $this->set('acts', $acts);

        $totalcount = sizeof($acts);
        $sorry = '';
        if ($totalcount==0)
        {
            $sorry = 'Извините, ничего не найдено';
        }
        $this->set('sorry', $sorry);
    }

    public function action_view()
    {
        $id = $this->request->param('id', 0);
        $acts = ORM::factory('acts', $id);
        if ( !$acts->loaded() )
        {
            throw new HTTP_Exception_404;

        }
        $this->set('acts', $acts);
    }

    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $acts = ORM::factory('Acts', $id);
        $errors = NULL;
        if ( $post = $this->request->post() )
        {
            try
            {
                $acts->title = Security::xss_clean(Arr::get($post, 'title', ''));
                $acts->text = Security::xss_clean(Arr::get($post, 'text', ''));
                $acts->published = 1;
                $acts->date = date('Y-m-d H:i:s', strtotime(Arr::get($post, 'date','')));
                $acts->save();
                Message::success('Акт сохранен');
                $this->redirect('manage/acts/view/'.$acts->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('item', $acts);
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $token = Arr::get($_POST, 'token', false);
        $acts = ORM::factory('Acts', $id);
        if ( ! $acts->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if ($this->request->post() && Security::token() === $token)
        {
            $acts->delete();
            Message::success('Акт удален');
            $this->redirect('manage/acts');
        }
        else
        {
            $this->set('record', $acts)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/acts'));
        }
    }

}
