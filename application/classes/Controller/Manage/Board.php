<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Board extends Controller_Manage_Core {

    public function action_index()
    {
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        if (!empty($search))
        {
            $this->redirect('manage/board/search/'.$search);
        }
        $board = ORM::factory('Board')->order_by('date', 'DESC');
        $paginate = Paginate::factory($board)->paginate(NULL, NULL, 10)->render();
        $board = $board->find_all();
        $this->set('board', $board);
        $this->set('paginate', $paginate);
    }

    public function action_search()
    {
        $search = Security::xss_clean(addslashes($this->request->param('id', '')));
        $this->set('search', $search);
        $query_m = DB::expr(' MATCH(title_ru, title_kz, title_en) ');
        $query_a = DB::expr(' AGAINST("'.$search.'") ');

        $board = ORM::factory('Board')->where($query_m, '', $query_a)->find_all();
        $this->set('board', $board);

        $totalcount = sizeof($board);
        $sorry = '';
        if ($totalcount==0)
        {
            $sorry = 'Извините, ничего не найдено';
        }
        $this->set('sorry', $sorry);
    }

    public function action_view()
    {
        $id = (int) $this->request->param('id', 0);
        $board = ORM::factory('Board', $id);
        if ( !$board->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('board', $board);
    }

    public function action_edit()
    {
        $id = (int) $this->request->param('id', 0);
        $board = ORM::factory('Board', $id);
        $errors = NULL;
        $documents = ORM::factory('Document')->find_all();
        if ( $post = $this->request->post() )
        {
            try
            {
                $post['date'] = date('Y-m-d H:i:s',strtotime($post['date']));
                $board->title = Security::xss_clean(Arr::get($post,'title',''));
                $board->text = Security::xss_clean(Arr::get($post,'text',''));
                $board->values($post, array('date', 'published', 'document_id'))->save();

                $this->redirect('manage/board/view/'.$board->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('item', $board)->set('documents', $documents);
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            ORM::factory('Board',$id)->delete();
            $this->redirect('manage/board');
        }
        else
        {
            $board = ORM::factory('Board', $id);
            if ($board->loaded())
            {
                $this->set('record', $board)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/board'));
            }
            else
            {
                throw new HTTP_Exception_404;
            }
        }
    }

    public function action_published()
    {
        $id = (int) $this->request->param('id', 0);
        $board = ORM::factory('Board', $id);
        if ( !$board->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $board->published )
        {
            $board->published = 0;
            $board->save();
            Message::success('Документ скрыт');
        }
        else
        {
            $board->published = 1;
            $board->save();
            Message::success('Документ опубликован');
        }
        $this->redirect('manage/board/');
    }

}
