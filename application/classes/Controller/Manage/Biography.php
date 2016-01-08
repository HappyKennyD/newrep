<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Biography extends Controller_Manage_Core {

    protected $page;

    public function before()
    {
        parent::before();

        $this->page = Security::xss_clean( (int) $this->request->param('page', 0) );
        if( empty($this->page) )
        {
            $this->page=1;
        }
    }

    public function action_index()
    {
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        if (!empty($search))
        {
            $this->redirect('manage/biography/search/'.$search);
        }
        $category = Arr::get($_POST, 'category');
        if (isset($category) && $category == 0)
        {
            $this->redirect('manage/biography');
        }
        if (!isset($category))
        {
            $category = $this->request->param('category', 0);
        }
        $category = (int)$category;
        $categories = ORM::factory('Biography_Category')->order_by('era')->find_all();
        $this->set('categories', $categories)->set('category', $category)->set('page', $this->page);

        $biography = ORM::factory('Biography');
        if ($category != 0)
        {
            $biography = $biography->where('category_id', '=', $category);
        }
        $biography = $biography->order_by('order');
        if ($category == 0)
        {
            $paginate = Paginate::factory($biography)->paginate(NULL, NULL, 10)->render();
            $this->set('paginate', $paginate);
        }
        $biography = $biography->find_all();
        $this->set('list', $biography);
    }

    public function action_search()
    {
        $search = Security::xss_clean(addslashes($this->request->param('id', '')));
        $this->set('search', $search);
        $query_m = DB::expr(' MATCH(name_ru, name_kz, name_en) ');
        $query_a = DB::expr(' AGAINST("'.$search.'") ');

        $biography = ORM::factory('Biography')->where($query_m, '', $query_a)->find_all();
        $this->set('list', $biography);

        $totalcount = sizeof($biography);
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
        $biography = ORM::factory('Biography', $id);
        if ( !$biography->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('item', $biography)->set('page', $this->page);
    }

    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $biography = ORM::factory('Biography', $id);
        if ($biography->loaded())
        {
            $era = $biography->category->era;
            $flag = true;
        }
        else
        {
            $era = 1;
            $flag = false;
        }
        $categories = ORM::factory('Biography_Category')->order_by('era')->find_all();
        $this->set('categories', $categories)->set('era', $era);
        $errors = NULL;
        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        if ( $post = $this->request->post() )
        {
            try
            {
                if ($id == 0)
                {
                    $last = ORM::factory('Biography')->where('category_id', '=', (int)Arr::get($post, 'category_id'))->order_by('order','Desc')->find();
                    $biography->order = ($last->order + 1);
                }

                $biography->name = Security::xss_clean(Arr::get($post,'name',''));
                $biography->desc = Security::xss_clean(Arr::get($post,'desc',''));
                $biography->text = Security::xss_clean(Arr::get($post,'text',''));
                $biography->date_start = Security::xss_clean(Arr::get($post,'date_start',''));
                $biography->date_finish = Security::xss_clean(Arr::get($post,'date_finish',''));
                $biography->birthplace = Security::xss_clean(Arr::get($post,'birthplace',''));
                $biography->deathplace = Security::xss_clean(Arr::get($post,'deathplace',''));
                $biography->category_id = (int)Arr::get($post, 'category_id', 0);
                $biography->values($post, array('image', 'is_important', 'published'))->save();

                $event = ($id)?'edit':'create';
                $loger = new Loger($event,$biography->name);
                $loger->logThis($biography);

                if(!$flag)
                {
                    $list = ORM::factory('Biography');
                    $paginate = Paginate::factory($list);
                    $list = $list->find_all();
                    $this->page = $paginate->page_count();
                }

                Message::success('Сохранено');
                $this->redirect('manage/biography/view/'.$biography->id.'/page-'.$this->page);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('uploader',$uploader);
        $this->set('item', $biography)->set('page', $this->page);
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $biography = ORM::factory('Biography', $id);
        if (!$biography->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $loger = new Loger('delete',$biography->name);
            $loger->logThis($biography);
            $biography->delete();

            $list = ORM::factory('Biography');
            $paginate = Paginate::factory($list);
            $list = $list->find_all();
            $last_page=$paginate->page_count();

            if( $this->page > $last_page )
            {
                $this->page = $this->page -1;
            }

            if($this->page <=0 )
            {
                $this->page = 1;
            }

            Message::success('Удалено');
            $this->redirect('manage/biography/page-'.$this->page);
        }
        else
        {
            $this->set('record', $biography)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/biography/page-'.$this->page));
        }
    }

    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $biography = ORM::factory('Biography', $id);
        if ( !$biography->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $biography->published )
        {
            $biography->published = 0;
            $biography->save();
            Message::success('Скрыто');
        }
        else
        {
            $biography->published = 1;
            $biography->save();
            Message::success('Опубликовано');
        }
        $this->redirect('manage/biography/page-'.$this->page);
    }

    public function action_important()
    {
        $id = $this->request->param('id', 0);
        $biography = ORM::factory('Biography', $id);
        if ( !$biography->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $biography->is_important )
        {
            $biography->is_important = 0;
            $biography->save();
            Message::success('Убрано с главной');
        }
        else
        {
            $biography->is_important = 1;
            $biography->save();
            Message::success('Добавлено на главную');
        }
        $this->redirect('manage/biography/page-'.$this->page);
    }

    public function action_clearImage()
    {
        $id = $this->request->param('id', 0);
        $biography = ORM::factory('Biography',$id);
        if ($biography->loaded())
        {
            $biography->image = 0;
            $biography->save();
        }
        $this->redirect('manage/biography/edit/'.$id);
    }

    public function action_category()
    {
        $history = ORM::factory('Biography_Category')->where('era', '=', 1)->find_all();
        $contemporary = ORM::factory('Biography_Category')->where('era', '=', 2)->find_all();
        $this->set('history',$history)->set('contemporary', $contemporary);
    }

    public function action_editcategory()
    {
        $id = (int) $this->request->param('id', 0);
        $category = ORM::factory('Biography_Category',$id);
        $this->set('category',$category)->set('id', $id);
        if ($post = $this->request->post())
        {
            $category->title = Security::xss_clean(Arr::get($post,'title',''));
            $category->era = (int)Arr::get($post,'era','');
            $category->save();
            Message::success('Категория сохранена');
            $this->redirect('manage/biography/category');
        }
    }

    public function action_deletecategory()
    {
        $id = (int) $this->request->param('id', 0);
        $item = ORM::factory('Biography_Category',$id);
        if ( !$item->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $item->delete();
            Message::success('Категория удалена');
            $this->redirect('manage/biography/category');
        }
        else
        {
            $this->set('record', $item)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/biography/category'));
        }
        $this->template->set_filename('manage/biography/delete');
    }

    public function action_down()
    {
        $id = (int)$this->request->param('id', 0);
        $biography = ORM::factory('Biography', $id);
        if (!$biography->loaded())
        {
            throw new HTTP_Exception_404;
        }

        $biography->order = ($biography->order+1);
        $biography->save();

        $category = (int)$this->request->param('category', 0);
        if ($category != 0)
        {
            $this->redirect('manage/biography/index/'.$id.'/'.$category.'/page-'.$this->page);
        }
        else{
            $this->redirect('manage/biography/page-'.$this->page);
        }
    }

    public function action_up()
    {
        $id = (int)$this->request->param('id', 0);
        $biography = ORM::factory('Biography', $id);
        if (!$biography->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if ($biography->order > 0)
        {
            $biography->order = ($biography->order-1);
            $biography->save();
        }

        $category = (int)$this->request->param('category', 0);
        if ($category != 0)
        {
            $this->redirect('manage/biography/index/'.$id.'/'.$category.'/page-'.$this->page);
        }
        else{
            $this->redirect('manage/biography/page-'.$this->page);
        }
    }

    public function action_attachments()
    {
        $id = (int)$this->request->param('id', 0);
        $biography = ORM::factory('Biography', $id);
        if (!$biography->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $read = $biography->attach->where('key', '=', 'read')->find_all();
        $references = $biography->attach->where('key', '=', 'references')->find_all();
        $this->set('read',$read)->set('references', $references)->set('id', $id);
    }

    public function action_editattachments()
    {
        $id = (int) $this->request->param('id', 0);
        if ($id == 0)
        {
            $biography = (int)Arr::get($_GET,'biography',0);
            $this->set('biography', $biography);
            $this->set('cancel_url', Url::media('manage/biography/attachments/'.$biography));
        }
        else
        {
            $this->set('cancel_url', Url::media('manage/biography/attachments/'.$id));
        }
        $attach = ORM::factory('Biography_Attachment',$id);
        $this->set('attach',$attach);
        if ($post = $this->request->post())
        {
            $attach->title = Security::xss_clean(Arr::get($post,'title',''));
            $attach->key = Security::xss_clean(Arr::get($post,'key',''));
            $attach->link = Security::xss_clean(Arr::get($post,'link',''));
            if (isset($_POST['biography']))
            {
                $attach->biography_id = (int)Arr::get($post,'biography','');
            }
            $attach->save();
            Message::success('Ссылка сохранена');
            $this->redirect('manage/biography/attachments/'.$attach->biography_id);
        }
    }

    public function action_deleteattachments()
    {
        $id = (int) $this->request->param('id', 0);
        $item = ORM::factory('Biography_Attachment',$id);
        if ( !$item->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $biography = $item->biography_id;
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $item->delete();
            Message::success('Ссылка удалена');
            $this->redirect('manage/biography/attachments/'.$biography);
        }
        else
        {
            $this->set('record', $item)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/biography/attachments/'.$biography));
        }
        $this->template->set_filename('manage/biography/delete');
    }


}
