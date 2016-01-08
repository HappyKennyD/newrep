<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Video extends Controller_Manage_Core
{

    public function action_index()
    {
        $video = array();
      /*  for ($i = 0; $i < 10000; $i++):
            $k       = $i * 50 + 1;
            $feedURL = 'http://gdata.youtube.com/feeds/api/users/UCIfGluvYLEAohXwU1_332oA/uploads?start-index=' . $k . '&max-results=50';
            try
            {
                $result = Cache::instance('file')->get('youtube-cache' . $i, false);
            }
            catch (Exception $e)
            {
                $result = false;
            }
            if (!$result)
            {
                $result = file_get_contents($feedURL);
                Cache::instance('file')->set('youtube-cache' . $i, $result, 3600);
            }
            $sxml = simplexml_load_string($result);
            foreach ($sxml->entry as $entry)
            {
                $media       = $entry->children('media', true);
                $link        = str_replace('&feature=youtube_gdata_player', '', $media->group->player->attributes()->url);
                $title       = $media->group->title;
                $description = $media->group->description;
                $when        = $entry->published;
                if (strpos($link, 'v='))
                {
                    $e    = explode('v=', $link);
                    $link = $e[1];
                }
                else
                {
                    if (strpos($link, 'youtu.be/'))
                    {
                        $e    = explode('youtu.be/', $link);
                        $link = $e[1];
                    }
                }
                $video_base = ORM::factory('Video')->where('link', '=', $link)->find();
                if (!isset($video_base->id))
                {
                    $video_save                 = ORM::factory('Video', $video_base->id);
                    $video_save->link           = $link;
                    $video_save->title          = $title;
                    $video_save->date           = $when;
                    $video_save->description  = $description;
                    $video_save->published      = 0;
                    $video_save->save();
                }

                $video[] = $entry;
            }
            if (count($video) % 50 != 0)
            {
                break;
            }
        endfor;*/
        $count_video_not_category = ORM::factory('Video')->where('category_id', '=', 0)->count_all();
        $categories               = ORM::factory('Category')->fulltree;
        $this->set('list', $categories)->set('count_video_not_category', $count_video_not_category);
    }

    public function action_search()
    {
        $search = Security::xss_clean(addslashes($this->request->param('id', '')));
        $this->set('search', $search);
        $query_m = DB::expr(' MATCH(title) ');
        $query_a = DB::expr(' AGAINST("' . $search . '") ');

        $video = ORM::factory('Video')->where($query_m, '', $query_a)->find_all();
        $this->set('video', $video);

        $totalcount = sizeof($video);
        $sorry      = '';
        if ($totalcount == 0)
        {
            $sorry = 'Извините, ничего не найдено';
        }
        $this->set('sorry', $sorry);
    }

    public function action_view()
    {
        $id    = $this->request->param('id', 0);
        $video = ORM::factory('Video', $id);
        if (!$video->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $category = 0;
        if ($video->category_id)
        {
            $category = $video->category;
        }
        $this->set('video', $video)->set('category', $category);
    }

    public function action_edit()
    {
        $id          = $this->request->param('id', 0);
        $category    = 0;
        $category_id = Arr::get($_GET, 'category', 0);
        $video       = ORM::factory('Video', $id);
        if ($category_id)
        {
            $category = ORM::factory('Category', $category_id);
        }
        elseif ($id and $video->category_id)
        {
            $category = $video->category;
        }

        //$categories = ORM::factory('Category')->where('lvl', '=', 1)->find_all();
        $categories = ORM::factory('Category')->fulltree;
        $errors     = null;
        if ($post = $this->request->post())
        {
            try
            {
                $link = $_POST['link'];
                if (strpos($link, 'v='))
                {
                    $e            = explode('v=', $link);
                    $link         = $e[1];
                    $post['link'] = $link;
                }
                else
                {
                    if (strpos($link, 'youtu.be/'))
                    {
                        $e            = explode('youtu.be/', $link);
                        $link         = $e[1];
                        $post['link'] = $link;
                    }
                    else
                    {
                        $post['link'] = $link;
                    }
                }

                $post['date'] = date('Y-m-d H:i:s', strtotime($post['date']));
                $video->values($post, array('title', 'date', 'link', 'description', 'published', 'category_id', 'language'))->save();

                $event = ($id) ? 'edit' : 'create';
                $loger = new Loger($event, $video->title);
                $loger->log($video);

                $this->redirect('manage/video/view/' . $video->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors', $errors);
            }
        }
        $this->set('item', $video)->set('categories', $categories)->set('category', $category);
    }

    public function action_delete()
    {
        $id    = (int)$this->request->param('id', 0);
        $video = ORM::factory('Video', $id);
        if (!$video->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $category = 0;
        if ($video->category_id)
        {
            $category = $video->category;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $loger = new Loger('delete', $video->title);
            $loger->log($video);
            $video->delete();
            Message::success('Запись удалена');
            $this->redirect('manage/video/category/' . $category);
        }
        else
        {
            $this->set('record', $video)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/video/category/' . $category))->set('category', $category);
        }
    }

    public function action_published()
    {
        $id    = $this->request->param('id', 0);
        $video = ORM::factory('Video', $id);
        if (!$video->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $category = '';
        if ($video->category_id)
        {
            $category = $video->category;
        }
        if ($video->published)
        {
            $video->published = 0;
            $video->save();
            Message::success('Запись скрыта');
        }
        else
        {
            $video->published = 1;
            $video->save();
            Message::success('Запись опубликована');
        }
        $this->redirect('manage/video/category/' . $category);
    }

    public function action_category()
    {
        $id       = (int)$this->request->param('id', 0);
        $category = 0;
        if ($id)
        {
            $category = ORM::factory('Category', $id);
            if (!$category->loaded())
            {
                throw new HTTP_Exception_404;
            }
            $video = $category->videos->order_by('date', 'DESC');
        }
        else
        {
            $video = ORM::factory('Video')->where('category_id', '=', 0)->order_by('date', 'DESC');
        }
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        if (!empty($search))
        {
            $this->redirect('manage/video/search/' . $search);
        }
        $paginate = Paginate::factory($video)->paginate(null, null, 10)->render();
        $video    = $video->find_all();
        $this->set('video', $video);
        $this->set('paginate', $paginate);
        $this->set('category', $category);
    }

    public function action_newcategory()
    {
        $id          = (int)$this->request->param('id', 0);
        $parent_page = ORM::factory('Category', $id);
        if ($post = $this->request->post())
        {
            $item       = ORM::factory('Category');
            $item->name = Security::xss_clean(Arr::get($post, 'name', ''));
            if ($parent_page->loaded())
            {
                $item->insert_as_last_child($parent_page);
            }
            else
            {
                $item->make_root();
            }
            Message::success('Категория добавлена');
            $this->redirect('manage/video');
        }
    }


    public function action_editcategory()
    {
        $id   = (int)$this->request->param('id', 0);
        $item = ORM::factory('Category', $id);
        $this->set('item', $item);
        if ($post = $this->request->post())
        {
            $item->name = Security::xss_clean(Arr::get($post, 'name', ''));
            $item->save();
            Message::success('Категория сохранена');
            $this->redirect('manage/video');
        }
    }

    public function action_deletecategory()
    {
        $id   = (int)$this->request->param('id', 0);
        $item = ORM::factory('Category', $id);
        if (!$item->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $item->delete();
            Message::success('Категория удалена');
            $this->redirect('manage/video');
        }
        else
        {
            $this->set('record', $item)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/video'));
        }
        $this->template->set_filename('manage/video/delete');
    }

/*
     * Показать на главной/скрыть с главной
     */
    public function action_important()
    {
        $id    = $this->request->param('id', 0);
        $video = ORM::factory('Video', $id);
        if (!$video->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $category = '';
        if ($video->category_id)
        {
            $category = $video->category;
        }
        if ($video->is_important)
        {
            $video->is_important = 0;
            $video->save();
            Message::success('Запись убрана с главной страницы');
        }
        else
        {
            $video->is_important = 1;
            $video->save();
            Message::success('Запись опубликована на главной странице');
        }
        $this->redirect('manage/video/category/' . $category);
    }





}
