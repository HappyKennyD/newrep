<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Thanks extends Controller_Manage_Core {

    public function action_index()
    {
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        if (!empty($search))
        {
            $this->redirect('manage/thanks/search/'.$search);
        }
        $thanks = ORM::factory('Thank')->order_by('order');
        $paginate = Paginate::factory($thanks)->paginate(NULL, NULL, 10)->render();
        $thanks = $thanks->find_all();
        $this->set('list', $thanks);
        $this->set('paginate', $paginate);
    }

    public function action_search()
    {
        $search = Security::xss_clean(addslashes($this->request->param('id', '')));
        $this->set('search', $search);
        $query_m = DB::expr(' MATCH(name_ru, name_kz, name_en) ');
        $query_a = DB::expr(' AGAINST("'.$search.'") ');

        $thanks = ORM::factory('Thank')->where($query_m, '', $query_a)->find_all();
        $this->set('list', $thanks);

        $totalcount = sizeof($thanks);
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
        $thank = ORM::factory('Thank', $id);
        if ( !$thank->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('item', $thank);
    }

    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $thank = ORM::factory('Thank', $id);
        $errors = NULL;
        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        if ( $post = $this->request->post() )
        {
            try
            {
                if ($id == 0)
                {
                    $last = ORM::factory('Thank')->order_by('order','Desc')->find();
                    $thank->order = ($last->order + 1);
                }
                $post['date'] = date('Y-m-d H:i:s');
                $thank->name = Security::xss_clean(Arr::get($post,'name',''));
                $thank->text = Security::xss_clean(Arr::get($post,'text',''));
                $thank->values($post, array('image', 'published', 'date'))->save();
                $this->redirect('manage/thanks/view/'.$thank->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('uploader',$uploader);
        $this->set('item', $thank);
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $thank = ORM::factory('Thank', $id);
        if (!$thank->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $thank->delete();
            Message::success(I18n::get('Record deleted'));
            $this->redirect('manage/thanks');
        }
        else
        {
            $this->set('record', $thank)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/thanks'));
        }
    }

    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $thank = ORM::factory('Thank', $id);
        if ( !$thank->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $thank->published )
        {
            $thank->published = 0;
            $thank->save();
            Message::success(I18n::get('Record hided'));
        }
        else
        {
            $thank->published = 1;
            $thank->save();
            Message::success(I18n::get('Record unhided'));
        }
        $this->redirect('manage/thanks');
    }

    public function action_clearImage()
    {
        $id = $this->request->param('id', 0);
        $thank = ORM::factory('Thank',$id);
        if ($thank->loaded())
        {
            $thank->image = 0;
            $thank->save();
        }
        $this->redirect('manage/thanks/edit/'.$id);
    }

    public function action_down()
    {
        $id = (int)$this->request->param('id', 0);
        $thank = ORM::factory('Thank', $id);
        if (!$thank->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $thank->order = ($thank->order+1);
        $thank->save();

        $this->redirect('manage/thanks');
    }

    public function action_up()
    {
        $id = (int)$this->request->param('id', 0);
        $thank = ORM::factory('Thank', $id);
        if (!$thank->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if ($thank->order > 0)
        {
            $thank->order = ($thank->order-1);
            $thank->save();
        }

        $this->redirect('manage/thanks');
    }

}
