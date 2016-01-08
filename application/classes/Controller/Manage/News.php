<?php defined('SYSPATH') or die('No direct script access.');

/*
 * Реализация управления новостями
 */
class Controller_Manage_News extends Controller_Manage_Core {

    /*
     * Список новостей
     */
	public function action_index()
	{
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        if (!empty($search))
        {
            $this->redirect('manage/news/search/'.$search);
        }
        $news = ORM::factory('News')->order_by('date', 'DESC');
        $paginate = Paginate::factory($news)->paginate(NULL, NULL, 10)->render();
        $news = $news->find_all();
        $this->set('news', $news);
        $this->set('paginate', $paginate);
	}

    /*
     * Поиск новости
     */
    public function action_search()
    {
        $search = Security::xss_clean(addslashes($this->request->param('id', '')));
        $this->set('search', $search);
        $query_m = DB::expr(' MATCH(title_ru, title_kz, title_en) ');
        $query_a = DB::expr(' AGAINST("'.$search.'") ');

        $news = ORM::factory('News')->where($query_m, '', $query_a)->find_all();
        $this->set('news', $news);

        $totalcount = sizeof($news);
        $sorry = '';
        if ($totalcount==0)
        {
            $sorry = 'Извините, ничего не найдено';
        }
        $this->set('sorry', $sorry);
    }

    /*
     * Просмотр новости
     */
    public function action_view()
    {
        $id = $this->request->param('id', 0);
        $news = ORM::factory('News', $id);
        if ( !$news->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $tags = $news->tags->find_all()->as_array('id','name');
        $tags = implode(', ', $tags);
        $this->set('news', $news)->set('tags',$tags);
    }

    /*
     * Редактирование/ добавление новости
     */
    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $news = ORM::factory('News', $id);
        $errors = NULL;
        $tags = $news->tags->find_all()->as_array('id','name');
        if ($tags)
        {
            $tags = implode(', ', $tags);
            $this->set('news_tags', $tags);
        }
        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        if ( $post = $this->request->post() )
        {
            try
            {
                $tags = $news->tags->find_all();
                foreach ($tags as $tag)
                {
                    ORM::factory('Tag', $tag->id)->delete();
                }
                $tag_arr = preg_split("/[\s,]+/", $post['tags']);
                $post['date'] = date('Y-m-d H:i:s',strtotime($post['date']));
                $news->title = Security::xss_clean(Arr::get($post,'title',''));
                $news->desc = Security::xss_clean(Arr::get($post,'desc',''));
                $news->text = Security::xss_clean(Arr::get($post,'text',''));
                $news->values($post, array('image', 'date', 'is_important', 'published'))->save();

                $event = ($id)?'edit':'create';
                $loger = new Loger($event,$news->title);
                $loger->logThis($news);

                foreach ($tag_arr as $tag_one)
                {
                    if (!empty($tag_one))
                    {
                        $tag_add = ORM::factory('Tag');
                        $tag_add->news_id = $news->id;
                        $tag_add->name = Security::xss_clean($tag_one);
                        $tag_add->save();
                    }
                }

                Message::success('Новость сохранена');
                $this->redirect('manage/news/view/'.$news->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('uploader',$uploader);
        $this->set('item', $news);
    }

    /*
     * Удаление новости
     */
    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $news = ORM::factory('News', $id);
        if (!$news->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $tags = $news->tags->find_all();
            foreach ($tags as $tag)
            {
                ORM::factory('Tag', $tag->id)->delete();
            }
            $loger = new Loger('delete',$news->title);
            $loger->logThis($news);
            $news->delete();
            Message::success('Новость удалена');
            $this->redirect('manage/news');
        }
        else
        {
            $this->set('record', $news)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/news'));
        }
    }

    /*
     * Опубликовать/скрыть новость
     */
    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $news = ORM::factory('News', $id);
        if ( !$news->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $news->published )
        {
            $news->published = 0;
            $news->save();
            Message::success('Новость скрыта');
        }
        else
        {
            $news->published = 1;
            $news->save();
            Message::success('Новость опубликована');
        }
        $this->redirect('manage/news/');
    }

    /*
     * Показать на главной/скрыть с главной
     */
    public function action_important()
    {
        $id = $this->request->param('id', 0);
        $news = ORM::factory('News', $id);
        if ( !$news->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $news->is_important )
        {
            $news->is_important = 0;
            $news->save();
            Message::success('Новость убрана с главной');
        }
        else
        {
            $news->is_important = 1;
            $news->save();
            Message::success('Новость добавлена на главную');
        }
        $this->redirect('manage/news/');
    }

    /*
     * Удалить изображение в новости
     * TODO реализовать через ajax
     */
    public function action_clearImage()
    {
        $id = $this->request->param('id', 0);
        $news = ORM::factory('News',$id);
        if ($news->loaded())
        {
            $news->image = 0;
            $news->save();
        }
        $this->redirect('manage/news/edit/'.$id);
    }

}
