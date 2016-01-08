<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Publicationprojects extends Controller_Manage_Core {

    /*
     * Список
     */
    public function action_index()
    {
        $type = $this->request->param('type');
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
        if (!empty($search))
        {
            $this->redirect('manage/publicationprojects/'.$type.'/search/'.$search);
        }
        $public = ORM::factory(ucfirst($type))->order_by('order','desc')->order_by('date', 'DESC');
        $paginate = Paginate::factory($public)->paginate(NULL, NULL, 10)->render();
        $public = $public->find_all();
        $this->set('list', $public)->set('type',$type);
        $this->set('paginate', $paginate);
    }

    /*
     * Поиск
     */
    public function action_search()
    {
        $type = $this->request->param('type');
        $search = Security::xss_clean(addslashes($this->request->param('id', '')));
        $this->set('search', $search);
        $query_m = DB::expr(' MATCH(title_ru, title_kz, title_en) ');
        $query_a = DB::expr(' AGAINST("'.$search.'") ');

        $public = ORM::factory(ucfirst($type))->where($query_m, '', $query_a)->find_all();
        $this->set('list', $public)->set('type',$type);

        $totalcount = sizeof($public);
        $sorry = '';
        if ($totalcount==0)
        {
            $sorry = 'Извините, ничего не найдено';
        }
        $this->set('sorry', $sorry);
    }

    /*
     * Просмотр
     */
    public function action_view()
    {
        $type = $this->request->param('type');
        $id = $this->request->param('id', 0);
        $public = ORM::factory(ucfirst($type), $id);
        if ( !$public->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $tags = $public->tags->where('type','=',$type)->find_all()->as_array('id','name');
        $tags = implode(', ', $tags);

        $sql = "SELECT project . *
FROM material_projects, project
WHERE material_projects.material_id =  $id
AND material_projects.type =  'publicationproject'
AND material_projects.project_id = project.id";
        $projects = DB::query(Database::SELECT, $sql)->as_object()->execute();

        foreach ($projects as $project) {

            $project_name = $project->{'name_' . $this->language};

                }
        $this->set('item', $public)->set('tags',$tags)->set('type',$type)->set('project', $project_name);
    }

    /*
     * Редактирование/ добавление
     */
    public function action_edit()
    {
        $type = $this->request->param('type');
        $id = $this->request->param('id', 0);
        $public = ORM::factory(ucfirst($type), $id);
        $errors = NULL;
        $projects = ORM::factory('Project')->find_all();
        $this->set('projects', $projects);
        $tags = $public->tags->where('type','=',$type)->find_all()->as_array('id','name');
        if ($tags)
        {
            $tags = implode(', ', $tags);
            $this->set('public_tags', $tags);
        }
        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        if ( $post = $this->request->post() )
        {
            try
            {
                $tags = $public->tags->where('type','=',$type)->find_all();
                foreach ($tags as $tag)
                {
                    ORM::factory('Tag', $tag->id)->delete();
                }
                $tag_arr = preg_split("/[\\s,]+/", $post['tags']);
                $post['date'] = date('Y-m-d H:i:s',strtotime($post['date']));

                if ($id == 0 AND $type == 'publication')
                {
                    $item_last = ORM::factory(ucfirst($type))
                        ->order_by('order','desc')
                        ->find();
                    if (!empty($item_last->id))
                    {
                        $public->order = $item_last->order + 1;
                    }
                    else
                    {
                        $public->order = 1;
                    }
                }

                $public->title = Security::xss_clean(Arr::get($post,'title',''));
                $public->desc = Security::xss_clean(Arr::get($post,'desc',''));
                $public->text = Security::xss_clean(Arr::get($post,'text',''));
                if ($type == "publication")
                {
                    $public->is_slider = Arr::get($post, 'is_slider', 0);
                }
                $public->values($post, array('image','carved_id', 'is_important', 'slider_position', 'published'));
                if ($this->language=='ru')
                {
                $public->date_ru = $post['date'];
                $public->date = $post['date'];

                }
                if ($this->language=='en')
                {
                    $public->date_en = $post['date'];
                    $public->date = $post['date'];
                }
                if ($this->language=='kz')
                {
                    $public->date_kz = $post['date'];
                    $public->date = $post['date'];
                }
                $public->user_id = ($id)?$public->user_id:Auth::instance()->get_user()->id;
                $public->save();
                $event = ($id)?'edit':'create';
                $loger = new Loger($event,$public->title);
                $loger->logThis($public);

                foreach ($tag_arr as $tag_one)
                {
                    if (!empty($tag_one))
                    {
                        $tag_add = ORM::factory('Tag');
                        $tag_add->public_id = $public->id;
                        $tag_add->type = $type;
                        $tag_add->name = Security::xss_clean($tag_one);
                        $tag_add->save();
                    }
                }


                $project_id = Arr::get($_POST, 'project_id');
                $type = 'publicationproject';
                if ($id == 0)
                {

                    $article = ORM::factory('Material_Project');
                    $article->material_id =$public->id;
                    $article->project_id = $project_id;
                    $article->type = $type;
                    $article->save();


                }

                else{
                    $query = DB::update('material_projects')->set(array('project_id' => $project_id))->where('material_id', '=', $public->id)->and_where('type','=',$type);
                    $result = $query->execute();
                }

              /*  }
                else{
                $article = ORM::factory('Material_Project')->where('material_id','=',$id)->and_where('type', '=', $type)->find_all();
                if (!$article){
                    $article = ORM::factory('Material_Project');
                    $article->material_id = $id;
                    $article->project_id = $project_id;
                    $article->type = $type;
                    $article->save();
                }
                else {

                }}*/


                Message::success('Сохранено');
                $this->redirect('manage/publicationprojects/'.$type.'/view/'.$public->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('uploader',$uploader);
        $this->set('item', $public)->set('type',$type);
    }

    /*
     * Удаление
     */
    public function action_delete()
    {
        $type = $this->request->param('type');
        $id = (int) $this->request->param('id', 0);
        $public = ORM::factory(ucfirst($type), $id);
        if (!$public->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $tags = $public->tags->where('type','=',$type)->find_all();
            foreach ($tags as $tag)
            {
                ORM::factory('Tag', $tag->id)->delete();
            }
            $loger = new Loger('delete',$public->title);
            $loger->logThis($public);
            $public->delete();
            Message::success('Удалено');
            $this->redirect('manage/publicationprojects/'.$type);
        }
        else
        {
            $this->set('record', $public)->set('type',$type)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/publications/'.$type));
        }
    }

    /*
     * Опубликовать/скрыть
     */
    public function action_published()
    {
        $type = $this->request->param('type');
        $id = $this->request->param('id', 0);
        $public = ORM::factory(ucfirst($type), $id);
        if ( !$public->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $public->published )
        {
            $public->published = 0;
            $public->save();
            Message::success('Скрыто');
        }
        else
        {
            $public->published = 1;
            $public->save();
            Message::success('Опубликовано');
        }
        $this->redirect('manage/publicationprojects/'.$type);
    }

    /*
     * Показать на главной/скрыть с главной
     */
    public function action_important()
    {
        $type = $this->request->param('type');
        $id = $this->request->param('id', 0);
        $public = ORM::factory(ucfirst($type), $id);
        if ( !$public->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $public->is_important )
        {
            $public->is_important = 0;
            $public->save();
            Message::success('Убрано с главной');
        }
        else
        {
            $public->is_important = 1;
            $public->save();
            Message::success('Добавлена на главную');
        }
        $this->redirect('manage/publicationprojects/'.$type);
    }

    /*
    * ДОбавить в слайдер на главной/убрать со слайдера на главной
    */
    public function action_slider()
    {
        $type = $this->request->param('type');
        $id = $this->request->param('id', 0);
        $public = ORM::factory(ucfirst($type), $id);
        if ( !$public->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $public->is_slider )
        {
            $public->is_slider = 0;
            $public->save();
            Message::success('Убрано со слайдера на главной');
        }
        else
        {
            $public->is_slider = 1;
            $public->save();
            Message::success('Добавлена в слайдер на главной');
        }
        $this->redirect('manage/publicationprojects/'.$type);
    }

    /*
     * Удалить изображение
     * TODO реализовать через ajax
     */
    public function action_clearImage()
    {
        $type = $this->request->param('type');
        $id = $this->request->param('id', 0);

        if ($type!="1" and $type!="2" and $type!="3" and $type!="4"){
        $public = ORM::factory(ucfirst($type),$id);
        if ($public->loaded())
        {
            $public->carved_id = 0;
            $public->image = 0;
            $public->save();
        }

            $this->redirect('manage/publicationprojects/'.$type.'/edit/'.$id);
        }
        else{
            $public = ORM::factory('Publication',$id);
            if ($public->loaded())
            {
                $public->carved_id = 0;
                $public->image = 0;
                $public->save();
            }

            $this->redirect('manage/specprojects/'.$type.'/edit/'.$id);
        }
    }

    public function action_down()
    {
        $id = (int)$this->request->param('id', 0);
        $type = $this->request->param('type');
        $item = ORM::factory(ucfirst($type), $id);
        if (!$item->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $item_up = ORM::factory(ucfirst($type))
            ->where('order','<',$item->order)->order_by('order','desc')
            ->find();
        if (empty($item_up->id))
        {
            $this->redirect('manage/publicationprojects/'.$type);
        }
        $order = $item->order;
        $item->order = $item_up->order;
        $item_up->order = $order;
        $item->save();
        $item_up->save();
        $this->redirect('manage/publicationprojects/'.$type);
    }

    public function action_up()
    {
        $id = (int)$this->request->param('id', 0);
        $type = $this->request->param('type');
        $item = ORM::factory(ucfirst($type), $id);
        if (!$item->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $item_up = ORM::factory(ucfirst($type))
            ->where('order','>',$item->order)->order_by('order','asc')
            ->find();
        if (empty($item_up->id))
        {
            $this->redirect('manage/publicationprojects/'.$type);
        }
        $order = $item->order;
        $item->order = $item_up->order;
        $item_up->order = $order;
        $item->save();
        $item_up->save();
        $this->redirect('manage/publicationprojects/'.$type);
    }

}
