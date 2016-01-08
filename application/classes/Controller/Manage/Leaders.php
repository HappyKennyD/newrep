<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Leaders extends Controller_Manage_Core {

	public function action_index()
	{
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        if (!empty($search))
        {
            $this->redirect('manage/leaders/search/'.$search);
        }
        $leaders = ORM::factory('Leader')->order_by('order','DESC');
        $paginate = Paginate::factory($leaders)->paginate(NULL, NULL, 10)->render();
        $leaders = $leaders->find_all();
        $this->set('leaders', $leaders);
        $this->set('paginate', $paginate);
	}

    public function action_search()
    {
        $search = Security::xss_clean(addslashes($this->request->param('id', '')));
        $this->set('search', $search);
        $query_m = DB::expr(' MATCH(name_ru, name_kz, name_en) ');
        $query_a = DB::expr(' AGAINST("'.$search.'") ');

        $leaders = ORM::factory('Leader')->where($query_m, '', $query_a)->find_all();
        $this->set('leaders', $leaders);

        $totalcount = sizeof($leaders);
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
        $leader = ORM::factory('Leader', $id);
        if ( !$leader->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('leader', $leader);
    }

    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $leader = ORM::factory('Leader', $id);
        $errors = NULL;
        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        if ( $post = $this->request->post() )
        {
            try
            {
                $leader->name = Security::xss_clean(Arr::get($post, 'name',''));
                $leader->post = Security::xss_clean(Arr::get($post, 'post',''));
                $leader->contact = Security::xss_clean(Arr::get($post, 'contact',''));
                $leader->phone = Security::xss_clean(Arr::get($post, 'phone',''));
                $leader->fax = Security::xss_clean(Arr::get($post, 'fax',''));
                $leader->contact_name = Security::xss_clean(Arr::get($post, 'contact_name',''));
                $leader->text = Security::xss_clean(Arr::get($post, 'text',''));
                $leader->values($post, array('image', 'published'))->save();

                $this->redirect('manage/leaders/view/'.$leader->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('uploader',$uploader);
        $this->set('item', $leader);
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            ORM::factory('Leader',$id)->delete();
            $this->redirect('manage/leaders');
        }
        else
        {
            $leader = ORM::factory('Leader', $id);
            if ($leader->loaded())
            {
                $this->set('record', $leader)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/leader'));
            }
            else
            {
                throw new HTTP_Exception_404;
            }
        }
    }

    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $leader = ORM::factory('Leader', $id);
        if ( !$leader->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $leader->published )
        {
            $leader->published = 0;
            $leader->save();
            Message::success('Запись скрыта');
        }
        else
        {
            $leader->published = 1;
            $leader->save();
            Message::success('Запись опубликована');
        }
        $this->redirect('manage/leaders/');
    }

    public function action_clearImage()
    {
        $id = $this->request->param('id', 0);
        $leader = ORM::factory('Leader',$id);
        if ($leader->loaded())
        {
            $leader->image = 0;
            $leader->save();
        }
        $this->redirect('manage/leaders/edit/'.$id);
    }

    public function action_up()
    {
        $id = (int)$this->request->param('id', 0);
        $leader = ORM::factory('Leader', $id);
        if (!$leader->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $leader->order = ($leader->order+1);
        $leader->save();

        $this->redirect('manage/leaders');
    }

    public function action_down()
    {
        $id = (int)$this->request->param('id', 0);
        $leader = ORM::factory('Leader', $id);
        if (!$leader->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if ($leader->order > 0)
        {
            $leader->order = ($leader->order-1);
            $leader->save();
        }

        $this->redirect('manage/leaders');
    }

}
