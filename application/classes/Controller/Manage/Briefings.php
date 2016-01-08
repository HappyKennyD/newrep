<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Briefings extends Controller_Manage_Core {

	public function action_index()
	{
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        if (!empty($search))
        {
            $this->redirect('manage/briefings/search/'.$search);
        }
        $briefings = ORM::factory('Briefing')->order_by('date', 'DESC');
        $paginate = Paginate::factory($briefings)->paginate(NULL, NULL, 10)->render();
        $briefings = $briefings->find_all();
        $this->set('briefings', $briefings);
        $this->set('paginate', $paginate);
	}

    public function action_search()
    {
        $search = Security::xss_clean(addslashes($this->request->param('id', '')));
        $this->set('search', $search);
        $query_m = DB::expr(' MATCH(title_ru, title_kz, title_en) ');
        $query_a = DB::expr(' AGAINST("'.$search.'") ');

        $briefings = ORM::factory('Briefing')->where($query_m, '', $query_a)->find_all();
        $this->set('briefings', $briefings);

        $totalcount = sizeof($briefings);
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
        $briefing = ORM::factory('Briefing', $id);
        if ( !$briefing->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        //$comments = $briefing->comments->order_by('date')->find_all();
        $this->set('briefing', $briefing);//->set('comments', $comments);
    }

    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $briefing = ORM::factory('Briefing', $id);
        $errors = NULL;
        if ( $post = $this->request->post() )
        {
            try
            {
                $video = (int)Arr::get($_POST, 'video', 0);
                if ($video == 1)
                {
                    $brif_on = ORM::factory('Briefing')->where('video', '=', '1')->find();
                    if ($brif_on->loaded())
                    {
                        $brif_on->video = 0;
                        $brif_on->save();
                    }
                }

                $link = trim($_POST['link']);
                if (strpos($link, 'v='))
                {
                    $e = explode('v=', $link);
                    $link = $e[1];
                    $post['link'] = $link;
                }
                else if (strpos($link, '.be/'))
                {
                    $e = explode('.be/', $link);
                    $link = $e[1];
                    $post['link'] = $link;
                }
                else
                {
                    $post['link'] = $link;
                }


                $post['date'] = date('Y-m-d H:i:s',strtotime($post['date']));
                $briefing->title = Security::xss_clean(Arr::get($post,'title',''));
                $briefing->desc = Security::xss_clean(Arr::get($post,'desc',''));
                $briefing->text = Security::xss_clean(Arr::get($post,'text',''));
                $briefing->values($post, array('date', 'published', 'link', 'video'))->save();

                $event = ($id)?'edit':'create';
                $loger = new Loger($event,$briefing->title);
                $loger->logThis($briefing);

                Message::success('Брифинг сохранен');
                $this->redirect('manage/briefings/view/'.$briefing->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('item', $briefing);
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $briefing = ORM::factory('Briefing', $id);
        if (!$briefing->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $loger = new Loger('delete',$briefing->title);
            $loger->logThis($briefing);
            $briefing->delete();
            Message::success('Брифинг удален');
            $this->redirect('manage/briefings');
        }
        else
        {
            $this->set('record', $briefing)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/briefing'));
        }
    }

    /*public function action_delComment()
    {
        $id = (int) $this->request->param('id', 0);
        $comment = ORM::factory('Briefings_Comment', $id);
        if (!$comment->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        $brif_id = $comment->briefing_id;
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $comment->delete();
            Message::success('Комментарий удален');
            $this->redirect('manage/briefings/view/'.$brif_id);
        }
        else
        {
            $this->set('record', $comment)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/briefings/view/'.$brif_id));
        }
    }*/


    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $briefing = ORM::factory('Briefing', $id);
        if ( !$briefing->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $briefing->published )
        {
            $briefing->published = 0;
            $briefing->save();
            Message::success('Брифинг скрыт');
        }
        else
        {
            $briefing->published = 1;
            $briefing->save();
            Message::success('Брифинг опубликован');
        }
        $this->redirect('manage/briefings/');
    }

    /*public function action_publComment()
    {
        $id = $this->request->param('id', 0);
        $comment = ORM::factory('Briefings_Comment', $id);
        if ( !$comment->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $comment->published )
        {
            $comment->published = 0;
            $comment->save();
            Message::success('Комментарий скрыт');
        }
        else
        {
            $comment->published = 1;
            $comment->save();
            Message::success('Комментарий опубликован');
        }
        $this->redirect('manage/briefings/view/'.$comment->briefing_id);
    }*/

}
