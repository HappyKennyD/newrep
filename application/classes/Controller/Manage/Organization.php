<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Реализация управления списком организаций образования и науки
 */
class Controller_Manage_Organization extends Controller_Manage_Core {

    public function action_index()
    {
        $page_root = ORM::factory('Page')->where('key','=','institute')->find();
        $pages = $page_root->descendants;
        $this->set('pages',$pages)->set('page_root', $page_root);
    }

    public function action_edit()
    {
        $id = (int) $this->request->param('id', 0);
        $content = ORM::factory('Organization', $id);
        if ( $content->loaded() )
        {
            $page = ORM::factory('Page', $content->page_id);
            $e = explode('_', $page->key);
            if ( $e[0] == 'organization')
            {
                $to = ORM::factory('Organization')->where('page_id','=',$e[1])->find();
                $this->redirect('manage/organization/edit/'.$to->id.'?page='.$e[1]);
            }
        }
        else
        {
            $page = ORM::factory('Page', Arr::get($_GET, 'page', 0));
            if ( !$page->loaded() )
            {
                throw new HTTP_Exception_404('Page not found');
            }
        }
        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        $this->set('uploader',$uploader);

        if( $post = $this->request->post() )
        {
            $content->page_id = $page->id;
            $content->title = Security::xss_clean(Arr::get($post, 'title', ''));
            $content->description = Security::xss_clean(Arr::get($post, 'description', ''));
            $content->address = Security::xss_clean(Arr::get($post, 'address', ''));
            $content->coords_label = Security::xss_clean(Arr::get($post, 'coords_label', ''));
            $content->scale = Security::xss_clean(Arr::get($post, 'scale', ''));
            $content->center_map = Security::xss_clean(Arr::get($post, 'center_map', ''));
            $content->fax = Security::xss_clean(Arr::get($post, 'fax', ''));
            $content->email = Security::xss_clean(Arr::get($post, 'email', ''));
            $content->site = str_replace(array("http://","https://"), "", Security::xss_clean(Arr::get($post, 'site', '')));
            $content->social_vk = str_replace(array("http://","https://"), "", Security::xss_clean(Arr::get($post, 'social_vk', '')));
            $content->social_fb = str_replace(array("http://","https://"), "", Security::xss_clean(Arr::get($post, 'social_fb', '')));
            $content->social_twitter = str_replace(array("http://","https://"), "", Security::xss_clean(Arr::get($post, 'social_twitter', '')));
            $content->date=date("Y-m-d H:i:s");
            $phone = "";
            foreach ($post['phone'] as $item)
            {
                if ($item)
                {
                    $phone =  $phone.$item .'|';
                }
            }
            $content->phone = Security::xss_clean($phone);
            $content->values($post, array('image'))->save();
            Message::success('Описание для "'.$page->name.'" сохранено');
            $this->redirect('manage/organization');
        }
        $this->set('content',$content)->set('r',Url::media('manage/organization'))->set('page', $page);
    }

    public function action_clearImage()
    {
        $id = $this->request->param('id', 0);
        $content = ORM::factory('Organization', $id);
        if ($content->loaded())
        {
            $content->image = 0;
            $content->save();
        }
        $this->redirect('manage/organization/edit/'.$id);
    }

    public function action_people()
    {
        $id = (int) $this->request->param('id', 0);
        $page = ORM::factory('Page', $id);
        if ( !$page->loaded() )
        {
            throw new HTTP_Exception_404('Page not found');
        }
        $people = $page->people->order_by('order','asc');
        $paginate = Paginate::factory($people)->paginate(NULL, NULL, 10)->render();
        $people = $people->find_all();
        $this->set('list',$people)->set('page',$page)->set('paginate',$paginate);
    }

    public function action_editpeople()
    {
        $id = (int) $this->request->param('id', 0);
        $people = ORM::factory('People', $id);
        if ( $people->loaded() )
        {
            $page = ORM::factory('Page', $people->page_id);
        }
        else
        {
            $page = ORM::factory('Page', Arr::get($_GET, 'page', 0));
            if ( !$page->loaded() )
            {
                throw new HTTP_Exception_404('Page not found');
            }
        }
        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        $this->set('uploader',$uploader)->set('content',$people)->set('r',Url::media('manage/organization/people/'.$page->id));
        if ($post = $this->request->post())
        {
            if ($id == 0)
            {
                $people_last = ORM::factory('People')
                    ->order_by('order','desc')
                    ->find();
                if (!empty($people_last->id))
                {
                    $people->order = $people_last->order + 1;
                }
                else
                {
                    $people->order = 1;
                }
            }
            $people->fio = Security::xss_clean(Arr::get($post,'fio',''));
            $people->page_id = $page->id;
            $people->description = Security::xss_clean(Arr::get($post,'description',''));
            $people->rank = Security::xss_clean(Arr::get($post,'rank',''));
            $people->values($post, array('image'))->save();
            Message::success('Сотрудник сохранен');
            $this->redirect('manage/organization/people/'.$page->id);
        }
    }

    public function action_delete()
    {
        $id = (int)$this->request->param('id', 0);
        $token = Arr::get($_POST, 'token', false);
        $people = ORM::factory('People',$id);
        $page_id = $people->page_id;
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $people->delete();
            Message::success('Сотрудник удален');
            $this->redirect('manage/organization/people/'.$page_id);
        }
        else
        {
            $this->set('token', Security::token(true))->set('cancel_url', Url::media('manage/organization/people/'.$page_id));
        }
    }

    public function action_up()
    {
        $id = (int)$this->request->param('id', 0);
        $item = ORM::factory('People', $id);
        if (!$item->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $item_up = ORM::factory('People')
            ->where('order','<',$item->order)->order_by('order','desc')
            ->find();
        if (empty($item_up->id))
        {
            $this->redirect('manage/organization/people/'.$item->page_id);
        }
        $order = $item->order;
        $item->order = $item_up->order;
        $item_up->order = $order;
        $item->save();
        $item_up->save();
        $this->redirect('manage/organization/people/'.$item->page_id);
    }

    public function action_down()
    {
        $id = (int)$this->request->param('id', 0);
        $item = ORM::factory('People', $id);
        if (!$item->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $item_up = ORM::factory('People')
            ->where('order','>',$item->order)->order_by('order','asc')
            ->find();
        if (empty($item_up->id))
        {
            $this->redirect('manage/organization/people/'.$item->page_id);
        }
        $order = $item->order;
        $item->order = $item_up->order;
        $item_up->order = $order;
        $item->save();
        $item_up->save();
        $this->redirect('manage/organization/people/'.$item->page_id);
    }

}
