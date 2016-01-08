<?php

/*
 * Реализация управления контентом
 */
class Controller_Manage_Contents extends Controller_Manage_Core
{
    protected $title = 'Контент';

    /*
     * Если страница статичная то редирект на просмотр контента
     * Если не статичная то редирект на список контента
     */
    public function action_index()
    {
        $id = (int) $this->request->param('id', 0);
        $page = ORM::factory('Page',$id);
        if ( !$page->loaded() )
        {
            throw new HTTP_Exception_404('Page not found');
        }
        if ($page->static)
        {
            $content = $page->content->where('type', '=', 'static')->find();
            $this->redirect('manage/contents/show/'.$content);
        }else
        {
            $find_childs=ORM::factory('Page')->where('parent_id','=',$id)->find_all()->as_array(null,'id');
            if (count($find_childs)==0){
                $this->redirect('manage/contents/list/'.$id);
            }
            else{
                Message::error('В этот раздел нельзя добавлять содержимое.');
                $this->redirect('manage/pages');
            }
        }

    }

    /*
     * Список контента  вразделе
     */
    public function action_list()
    {
        $id = (int) $this->request->param('id', 0);
        $page = ORM::factory('Page',$id);
        if ( !$page->loaded() )
        {
            throw new HTTP_Exception_404('Page not found');
        }
        $contents = $page->content->order_by('date', 'DESC');
        $paginate = Paginate::factory($contents)->paginate(NULL, NULL, 10)->render();
        $contents = $contents->find_all();
        $this->set('contents', $contents)->set('page', $page)->set('paginate', $paginate);
    }

    /*
     * Просмотр контента
     */
    public function action_show()
    {
        $id = (int) $this->request->param('id', 0);
        $content = ORM::factory('Pages_Content',$id);
        if ( !$content->loaded() )
        {
            throw new HTTP_Exception_404('Page not found');
        }
        $page = ORM::factory('Page', $content->page_id);
        $this->set('content', $content)->set('page', $page);

    }

    /*
     * Редактирование/добавление контента
     */
    public function action_edit()
    {
        $r = Arr::get($_GET, 'r', 'show');
        $id = (int) $this->request->param('id', 0);
        $content = ORM::factory('Pages_Content', $id);
        if ( $content->loaded() )
        {
            $page = ORM::factory('Page', $content->page_id);
        }
        else
        {
            $page = ORM::factory('Page', Arr::get($_GET, 'page', 0));
            if ( !$page->loaded() )
            {
                throw new HTTP_Exception_404('Page not found');
            }
        }
        if ($r=='show')
        {
            $r = Url::media('manage/contents/show/'.$id);
        }
        else
        {
            $r = Url::media('manage/contents/list/'.$page->id);
        }

        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        $this->set('uploader',$uploader);

        if( $post = $this->request->post() )
        {
            if ($page->static)
                $content->type = 'static';
            else
                $content->type = 'dynamic';
            $content->page_id = $page->id;
            $content->date = date('Y-m-d H:i', strtotime(Arr::get($post, 'date', '')));
            $content->title = Security::xss_clean(Arr::get($post, 'title', ''));
            $content->description = Security::xss_clean(Arr::get($post, 'description', ''));
            $content->text = Security::xss_clean(Arr::get($post, 'text', ''));
            $content->values($post, array('published','image'))->save();
            $event = ($id)?'edit':'create';
            $loger = new Loger($event,$content->title);
            $loger->logThis($content);

            Message::success('Контент сохранен');
            $this->redirect('manage/contents/show/'.$content->id);
        }
        $this->set('content',$content)->set('r',$r)->set('page', $page);
    }

    /*
     * Удаление контента
     */
    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $content = ORM::factory('Pages_Content',$id);
        if ( !$content->loaded() )
        {
            throw new HTTP_Exception_404('Page not found');
        }
        $page = ORM::factory('Page',$content->page_id);
        $token = Arr::get($_POST, 'token', false);
        if ( $this->request->post() AND Security::token() === $token )
        {
            $loger = new Loger('delete',$content->title);
            $loger->logThis($content);
            $content->delete();
            Message::success('Контент удален');
            $this->redirect('manage/contents/list/'.$page->id);
        }
        else
        {
            $this->set('token', Security::token(true))
                ->set('r', Url::media('manage/contents/list/'.$page->id))
                ->set('page', $page);
        }
    }

    /*
     * Опубликовать/скрыть контент
     */
    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $content = ORM::factory('Pages_Content', $id);
        if ( !$content->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $content->published )
        {
            $content->published = 0;
            $content->save();
            Message::success('Контент скрыт');
        }
        else
        {
            $content->published = 1;
            $content->save();
            Message::success('Контент опубликован');
        }
        $this->redirect('manage/contents/list/'.$content->page_id);
    }

    public function action_clearImage()
    {
        $id = $this->request->param('id', 0);
        $content = ORM::factory('Pages_Content', $id);
        if ($content->loaded())
        {
            $content->image = 0;
            $content->save();
        }
        $this->redirect('manage/contents/edit/'.$id);
    }
}