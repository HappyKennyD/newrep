<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Forum extends Controller_Core {

    public function before()
    {
        parent::before();
        $moderator = false;
        if ( $this->user AND $this->user->has_access('ma') )
        {
            $moderator = true;
        }
        $this->set('moderator', $moderator);

    }

    /*
     * тестовый action
     */
    public function action_join() {
        /* 10 пользователей у которых наибольшее колличесто сообщений за последние 30 дней */
        $top_users=ORM::factory('User')->select(DB::expr('count(forum_messages.id) AS count'))
            ->join('forum_messages')->on('User.id','=','forum_messages.user_id')
            ->where('forum_messages.date','>=', DB::expr('DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)'))
            ->group_by('User.id')->order_by('count','DESC')->limit(10)->find_all();

        foreach($top_users as $k) {
            echo $k->count.'<br />';
        }

        if( $top_users->count()>0 ) {
            $this->set('top_users', $top_users);
        }
        exit();
    }

    public function action_index()
    {
        $forum = ORM::factory('Forum')->order_by('id','asc')->find_all();
        $this->add_cumb('Forum','/');
        $this->set('forum', $forum);
        /* description */

    }

    /*
     * Добавление модератором нового раздела или подраздела форума
     */
    public function action_new()
    {
        if ( ! $this->user OR ! $this->user->has_access('ma') )
        {
            throw new HTTP_Exception_404;
        }

        if( $post = $this->request->post() )
        {
            $forum = ORM::factory('Forum');
            $forum->name = Security::xss_clean(Arr::get($post,'name',''));

            $forum->date = date('Y-m-d H:i:s');
            $forum->save();

            $this->redirect('forum');
        }
        $this->add_cumb('Forum','forum');
        $this->add_cumb('New theme','/');
    }

    /*
     * Редактирование модератором разделов форума
     */
    public function action_edit()
    {
        if ( ! $this->user OR ! $this->user->has_access('ma') )
        {
            throw new HTTP_Exception_404;
        }
        $id = (int) $this->request->param('id', 0);
        $forum = ORM::factory('Forum',$id);
        if ( !$forum->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if( $post = $this->request->post() )
        {
            $forum->name = Security::xss_clean(Arr::get($post,'name',''));
           // $forum->description = Security::xss_clean(Arr::get($post,'description',''));
            $forum->save();
            $this->redirect('forum');
        }
        $this->add_cumb('Forum','forum');
        $this->add_cumb($forum->name,'/');
        $this->set('item',$forum);
    }

    /*
     * Удаление модератором разделов или тем(добавленных пользователями) форума
     */
    public function action_delete()
    {
        if ( ! $this->user OR ! $this->user->has_access('ma') )
        {
            throw new HTTP_Exception_404;
        }
        $id = (int)$this->request->param('id', 0);
        $forum_theme = ORM::factory('Forum_Theme', $id);
        if ( !$forum_theme->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        $forum=ORM::factory('Forum', $forum_theme->forum_id);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $forum_theme->delete();
            $this->redirect('forum/list/'.$forum->id, 301);
        }
        else
        {
            $this->set('item', $forum_theme)->set('token', Security::token(true));
        }
        $this->add_cumb('Forum','forum');
        $this->add_cumb($forum->name,'forum/list/'.$forum->id);
        $this->add_cumb($forum_theme->name,'forum/show/'.$forum_theme->id);
        $this->add_cumb('Удалить тему','/');
    }

    /*
     * Список тем конкретного раздела
     */
    public function action_list()
    {
        $id = (int) $this->request->param('id', 0);
        $forum = ORM::factory('Forum',$id);
        if ( ! $forum->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $list=$forum->forum_theme->order_by('id', 'desc');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();

        $this->add_cumb('Forum','forum');
        $this->add_cumb($forum->name,'/');
        $this->set('forum',$forum)->set('list', $list)->set('paginate', $paginate);
        /* description */
        $this->metadata->description($forum->name);
    }

    /*
     * Добавление новой темы пользователями
     */
    public function action_theme()
    {
        if ( ! $this->user )
        {
            throw new HTTP_Exception_404;
        }
        $id = (int) $this->request->param('id', 0);
        $forum = ORM::factory('Forum',$id);

        if ( !$forum->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if( $post = $this->request->post() )
        {
                /* Чтобы правильно отработало правило not_empty */
                $_POST['title'] = trim($_POST['title']);
                $_POST['message'] = trim($_POST['message']);
                //$post = Validation::factory($this->request->post());
                $post = Validation::factory($_POST);
                $post->rule('title', 'not_empty')
                    -> rule('message','not_empty')
                    -> rule('title', 'min_length', array(':value', 2))
                    -> rule('message', 'min_length', array(':value', 5));

                if ($post->check())
                {
                    $theme = ORM::factory('Forum_Theme');
                    $theme->name = Arr::get($post,'title','');
                    $theme->forum_id=$id;
                    $theme->date = date('Y-m-d H:i:s');
                    $theme->save();
                    /* первое сообщение в теме */
                    $forum_message=ORM::factory('Forum_Message');
                    $forum_message->user_id=$this->user->pk();
                    $forum_message->section_id=$id;
                    $forum_message->theme_id=$theme->id;
                    $forum_message->companion_id=0;
                    $forum_message->text = Arr::get($post,'message','');
                    $forum_message->date = date('Y-m-d H:i:s');
                    $forum_message->first_message=1;
                    $forum_message->save();

                    /* уведомление для создателя темы включено по умолчанию */
                    $forum_notification=ORM::factory('Forum_Notification');
                    $forum_notification->theme_id=$theme->id;
                    $forum_notification->user_id=$this->user->pk();
                    $forum_notification->notification=1;
                    $forum_notification->save();

                    $this->redirect('forum/show/'.$theme->id,301);
                }
                else
                {
                    $errors = $post->errors('validation');
                    $this->set('errors',$errors)->set('post',$post);
                }
        }
        $this->add_cumb('Forum','forum');
        $this->add_cumb($forum->name, 'forum/list/'.$forum->id);
        $this->add_cumb('New theme','/');
    }

    /*
     * Просмотр одной темы форума
     */
    public function action_show()
    {
        $id = (int) $this->request->param('id', 0);
        $page=(int) $this->request->param('page',0);
        $theme = ORM::factory('Forum_Theme',$id);
        $section=ORM::factory('Forum', $theme->forum_id);
        if ( !$theme->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $notification=ORM::factory('Forum_Notification')->where('user_id','=',$this->user)->and_where('theme_id', '=', $id)->find();

        $messages = $theme->forum_message->where('first_message', '<>', 1)->and_where('companion_id','=',0)->order_by('date', 'asc');
        $paginate = Paginate::factory($messages)->paginate(NULL, NULL, 4)->render();
        $messages = $messages->find_all();

        $this->add_cumb('Forum','forum');
        $this->add_cumb($section->name,'forum/list/'.$section->id);
        $this->add_cumb($theme->name,'/');
        /* description */
        $first_message=$theme->forum_message->where('first_message', '=', 1)->find();
        $this->metadata->snippet($first_message->text);

        $this->set('theme', $theme)->set('page', $page)->set('messages', $messages)->set('notification',$notification->notification)->set('paginate', $paginate);

        if( $post = $this->request->post() )
        {
            if ( !$this->user )
            {
                throw new HTTP_Exception_404;
            }
            try
            {
                $email_notification=Security::xss_clean(Arr::get($post,'notification',0));
                $message = ORM::factory('Forum_Message');
                $message->text = Security::xss_clean(Arr::get($post,'text',''));
                $message->user_id = $this->user->pk();
                $message->section_id=$theme->forum_id;
                $message->theme_id = $id;
                $message->companion_id=0;
                $message->date = date('Y-m-d H:i:s');
                $message->save();
                if(!empty($email_notification)) {
                    $notification=1;
                }
                else{
                    $notification=0;
                }
                $notification_user=ORM::factory('Forum_Notification')->where('user_id','=',$this->user->pk())->and_where('theme_id','=',$id)->find();
                if($notification_user->loaded()) {
                    $notification_user->notification=$notification;
                    $notification_user->update();
                }
                else{
                    $notification_user->user_id=$this->user->pk();
                    $notification_user->theme_id=$id;
                    $notification_user->notification=$notification;
                    $notification_user->save();
                }

                /* чтобы при добавлении нового сообщения нормально отработала пагинация */
                $messages = $theme->forum_message->where('first_message', '<>', 1)->order_by('date', 'asc');
                $paginate = Paginate::factory($messages)->paginate(NULL, NULL, 4);
                $last_page=$paginate->page_count();
                $link='/'.$this->language.'/forum/show/'.$id.'/page-'.$last_page;
                /* email уведомление подписанным на тему пользователям */
                $users=ORM::factory('User')
                    ->join('forum_notifications', 'LEFT')->on('user.id','=','forum_notifications.user_id')
                    ->where('forum_notifications.notification','=','1')->and_where('forum_notifications.theme_id','=',$id)->and_where('forum_notifications.user_id','<>',$this->user)->find_all();

                if($users->count()>0) {
                    Email::connect();
                    Email::View('forum_notification');
                    foreach($users as $user) {
                        Email::set(array('id'=>$user->id,'receivername' => $user->show_name(),'sendername'=>$this->user->show_name(), 'url' =>$link ));
                        Email::send($user->email, array('no-reply@e-history.kz', 'e-history.kz'), "Ответ в теме ".$theme->name, '', true);
                    }
                }

                $this->redirect('forum/show/'.$id.'/page-'.$last_page,301);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors', $errors);
                $this->set('message', $_POST);
            }
        }

    }

    /*
     * Отправка сообщения
     */
   /* public function action_message()
    {
        if ( ! $this->user )
        {
            throw new HTTP_Exception_404;
        }
        $id = (int) $this->request->param('id', 0);
        $theme = ORM::factory('Forum_Theme', $id);
        if ( ! $theme->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if( $post = $this->request->post() )
        {
            try
            {
                $message = ORM::factory('Forum_Message');
                $message->text = Security::xss_clean(Arr::get($post,'text',''));
                $message->user_id = $this->user->pk();
                $message->section_id=$theme->forum_id;
                $message->theme_id = $id;
                $message->date = date('Y-m-d H:i:s');
                $message->save();
                $this->redirect(Request::initial()->referrer());
            }
            catch (ORM_Validation_Exception $e)
            {

                $errors = $e->errors($e->alias());
                $this->set('errors', $errors);
            }
        }
        else
        {
            throw new HTTP_Exception_404;
        }
    }*/

    /*
     * Удаление сообщение модератором
     */
    public function action_deletemes()
    {
        if ( !$this->user OR !$this->user->has_access('ma') )
        {
            throw new HTTP_Exception_404;
        }

        $id = (int) $this->request->param('id', 0);
        $page = (int) $this->request->param('page',0);
        $message = ORM::factory('Forum_Message', $id);

        if ( !$message->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $message->moderator = 0;
            $message->save();
            $this->redirect('forum/show/'.$message->theme->id.'/page-'.$page, 301);
        }
        else
        {
             $this->set('token', Security::token(true));
        }
        $this->add_cumb('Forum','forum');
        $this->add_cumb($message->forum->name,'forum/list/'.$message->forum->id);
        $this->add_cumb($message->theme->name,'forum/show/'.$message->theme->id);
        $this->add_cumb('Delete message','/');
    }

    public function action_editmes()
    {
        if ( !$this->user )
        {
            throw new HTTP_Exception_404;
        }
        $id = (int) $this->request->param('id', 0);
        $page = (int) $this->request->param('page',0);
        $message = ORM::factory('Forum_Message', $id);

        if ( !$message->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        if($this->user->has_access('ma') OR $this->user->id==$message->user_id)
        {
            /* если не модератор/админ, то запретить редактирование если время вышло */
            if(!$this->user->has_access('ma'))
            {
                if(!$message->ability_edit($id))
                {
                    throw new HTTP_Exception_404;
                }
            }
            $token = Arr::get($_POST, 'token', false);
            if( ($this->request->method() == Request::POST) )
            {
                try
                {
                    $message->text = Arr::get($_POST, 'text', '');
                    $message->date = date('Y-m-d H:i:s');
                    $message->save();
                    $this->redirect('forum/show/'.$message->theme->id.'/page-'.$page, 301);
                }
                catch (ORM_Validation_Exception $e)
                {
                    $errors = $e->errors($e->alias());
                    $id = (int) $this->request->param('id', 0);
                    $message = ORM::factory('Forum_Message', $id);
                    $this->set('message', $message)->set('post', $_POST)->set('errors', $errors);
                }
            }

            $this->set('message', $message)->set('page', $page)->set('token', Security::token(true));
            $this->add_cumb('Forum','forum');
            $this->add_cumb($message->forum->name,'forum/list/'.$message->forum->id);
            $this->add_cumb($message->theme->name,'forum/show/'.$message->theme->id);
            $this->add_cumb('Edit','/');
        }
        else
        {
            throw new HTTP_Exception_404;
        }
    }

}