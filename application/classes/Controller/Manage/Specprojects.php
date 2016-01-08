<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Specprojects extends Controller_Manage_Core {

    /*
     * Список
     */

    public function action_index()
    {
        $type = $this->request->param('type');
        $search = Security::xss_clean(Arr::get($_POST, 'search', ''));

        if ($post = $this->request->post()){
            $title=Security::xss_clean(Arr::get($post,'title1',''));

            if ($title!=''){
                $titles=ORM::factory('Specprojecttitle',$type);
                $titles->title=$title;
                $titles->save();
                message::success('Успешно изменено');
                $this->redirect('manage/specprojects/'.$type);
            } else{
                message::error('Поле не может быть пустым.');
                $this->redirect('manage/specprojects/'.$type);
            }
        }

        if (!empty($search))
        {
            $this->redirect('manage/specprojects/'.$type.'/search/'.$search);
        }
        $public = ORM::factory('Publication')->join('spec_projects','LEFT')->on('publication.id','=','spec_projects.id_publication')->select('publication.*','spec_projects.spec_published','spec_projects.in_slider','spec_projects.in_middle','spec_projects.in_bottom')->where('spec_projects.sproject','=',$type)->order_by('order','desc')->order_by('date', 'DESC');
        $paginate = Paginate::factory($public)->paginate(NULL, NULL, 10)->render();
        $public = $public->find_all();

        $title=ORM::factory('Specprojecttitle',$type)->title;

        $this->set('title',$title);
        $this->set('list', $public)->set('type',$type);
        $this->set('paginate', $paginate);
    }

    /*
     * Поиск
     */
   /* public function action_search()
    {
        $type = $this->request->param('type');
        $search = Security::xss_clean(addslashes($this->request->param('id', '')));
        $this->set('search', $search);
        $query_m = DB::expr(' MATCH(title_ru, title_kz, title_en) ');
        $query_a = DB::expr(' AGAINST("'.$search.'") ');

        $public = ORM::factory('Publication')->where($query_m, '', $query_a)->find_all();
        $this->set('list', $public)->set('type',$type);

        $totalcount = sizeof($public);
        $sorry = '';
        if ($totalcount==0)
        {
            $sorry = 'Извините, ничего не найдено';
        }
        $this->set('sorry', $sorry);
    }*/

    /*
     * Просмотр
     */
    public function action_view()
    {
        $type = $this->request->param('type');
        $id = $this->request->param('id', 0);
        $public = ORM::factory('Publication', $id);
        if ( !$public->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $tags = $public->tags->where('type','=',$type)->find_all()->as_array('id','name');
        $tags = implode(', ', $tags);
        $this->set('item', $public)->set('tags',$tags)->set('type',$type);
    }

    /*
     * Редактирование/ добавление
     */
    public function action_edit()
    {
        $type = $this->request->param('type');
        $id = $this->request->param('id', 0);
        $zhuzid=(int)Arr::get($_GET,'zhuzid',0);

        $public = ORM::factory('Publication', $id);
        $spublic=ORM::factory('Specproject')->where('id_publication','=',$id)->find();

        if ($spublic->spec_published!=NULL){
            $this->set('publ',$spublic->spec_published);
        }
        else
        {
            $this->set('publ',0);
        }

        if ($spublic->in_slider!=NULL){
            $this->set('in_slider',$spublic->in_slider);
        }
        else
        {
            $this->set('in_slider',0);
        }

        if ($spublic->in_middle!=NULL){
            $this->set('in_middle',$spublic->in_middle);
        }
        else
        {
            $this->set('in_middle',0);
        }

        if ($spublic->in_bottom!=NULL){
            $this->set('in_bottom',$spublic->in_bottom);
        }
        else
        {
            $this->set('in_bottom',0);
        }

        $errors = NULL;
        $tags = $public->tags->where('type','=','Publication')->find_all()->as_array('id','name');
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
                $tags = $public->tags->where('type','=','Publication')->find_all();
                foreach ($tags as $tag)
                {
                    ORM::factory('Tag', $tag->id)->delete();
                }
                $tag_arr = preg_split("/[\\s,]+/", $post['tags']);
                $post['date'] = date('Y-m-d H:i:s',strtotime($post['date']));

                if ($id == 0)
                {
                    $item_last = ORM::factory('Publication')
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
                $public->is_slider = 0;
                $public->values($post, array('image','carved_id', 'date', 'is_important', 'slider_position'));
                $public->user_id = ($id)?$public->user_id:Auth::instance()->get_user()->id;
                $public->save();

                if ($zhuzid==0){
                    $sid=$public->id;
                    $spectab=ORM::factory('Specproject')->where('id_publication','=',$id)->find();
                    $spectab->id_publication=$sid;
                    $spectab->sproject=$type;
                    $spectab->spec_published=Arr::get($post,'published','');
                    $spectab->in_slider=Arr::get($post,'in_slider','');
                    $spectab->in_middle=Arr::get($post,'in_middle','');
                    $spectab->in_bottom=Arr::get($post,'in_bottom','');
                    $spectab->save();
                } else{
                    $tree_zhuz=ORM::factory('Zhuze',$zhuzid);
                    $tree_zhuz->id_publication=$public->id;
                    $tree_zhuz->save();
                }

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
                Message::success('Сохранено');
                $this->redirect('manage/specprojects/'.$type.'/view/'.$public->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('type',$type);
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

        $zhuzid=(int)Arr::get($_GET,'zhuzid',0);


        $public = ORM::factory('Publication', $id);
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


            if ($zhuzid!=0){
                $zhuz=ORM::factory('Zhuze',$zhuzid);
                $zhuz->id_publication='';
                $zhuz->save();
                Message::success('Удалено');
                $this->redirect('manage/specprojects/zhuzes');
            }else{
                $spectab=ORM::factory('Specproject')->where('id_publication','=',$id)->find();
                $spectab->delete();
                Message::success('Удалено');
                $this->redirect('manage/specprojects/'.$type);
            }
        }
        else
        {
            $this->set('record', $public)->set('type',$type)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/specprojects/'.$type));
        }
    }

    /*
     * Опубликовать/скрыть
     */
    public function action_published()
    {
        $type = $this->request->param('type');
        $id = $this->request->param('id', 0);
        $spublic = ORM::factory('Specproject')->where('id_publication','=',$id)->find();
        if ( !$spublic->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $spublic->spec_published!=0 )
        {
            $spublic->spec_published = 0;
            $spublic->save();
            Message::success('Скрыто');
        }
        else
        {
            $spublic->spec_published = 1;
            $spublic->save();
            Message::success('Опубликовано');
        }
        $this->redirect('manage/specprojects/'.$type);
    }

    public function action_slider()
    {
        $type = $this->request->param('type');
        $id = $this->request->param('id', 0);
        $spublic = ORM::factory('Specproject')->where('id_publication','=',$id)->find();
        if ( !$spublic->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $spublic->in_slider!=0 )
        {
            $spublic->in_slider = 0;
            $spublic->save();
            Message::success('Убрано из слайдера');
        }
        else
        {
            $spublic->in_slider = 1;
            $spublic->save();
            Message::success('Добавлено в слайдер');
        }
        $this->redirect('manage/specprojects/'.$type);
    }

    public function action_middle()
    {
        $type = $this->request->param('type');
        $id = $this->request->param('id', 0);
        $spublic = ORM::factory('Specproject')->where('id_publication','=',$id)->find();
        if ( !$spublic->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $spublic->in_middle!=0 )
        {
            $spublic->in_middle = 0;
            $spublic->save();
            Message::success('Убрано из среднего блока');
        }
        else
        {
            $spublic->in_middle = 1;
            $spublic->save();
            Message::success('Добавлено в средний блок');
        }
        $this->redirect('manage/specprojects/'.$type);
    }

    public function action_bottom()
    {
        $type = $this->request->param('type');
        $id = $this->request->param('id', 0);
        $spublic = ORM::factory('Specproject')->where('id_publication','=',$id)->find();
        if ( !$spublic->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $spublic->in_bottom!=0 )
        {
            $spublic->in_bottom = 0;
            $spublic->save();
            Message::success('Убрано из нижнего блока');
        }
        else
        {
            $spublic->in_bottom = 1;
            $spublic->save();
            Message::success('Добавлено в нижний блок');
        }
        $this->redirect('manage/specprojects/'.$type);
    }

    /*
     * Показать на главной/скрыть с главной
     */
   /* public function action_important()
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
        $this->redirect('manage/specprojects/'.$type);
    }*/

    /*
    * ДОбавить в слайдер на главной/убрать со слайдера на главной
    */
   /* public function action_slider()
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
        $this->redirect('manage/specprojects/'.$type);
    }*/

    /*
     * Удалить изображение
     * TODO реализовать через ajax
     */
    public function action_clearImage()
    {
        $type = $this->request->param('type');
        $id = $this->request->param('id', 0);
        $public = ORM::factory(ucfirst($type),$id);
        if ($public->loaded())
        {
            $public->carved_id = 0;
            $public->image = 0;
            $public->save();
        }
        $this->redirect('manage/specprojects/'.$type.'/edit/'.$id);
    }

   /* public function action_down()
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
            $this->redirect('manage/publications/'.$type);
        }
        $order = $item->order;
        $item->order = $item_up->order;
        $item_up->order = $order;
        $item->save();
        $item_up->save();
        $this->redirect('manage/specprojects/'.$type);
    }*/

  /*  public function action_up()
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
            $this->redirect('manage/specprojects/'.$type);
        }
        $order = $item->order;
        $item->order = $item_up->order;
        $item_up->order = $order;
        $item->save();
        $item_up->save();
        $this->redirect('manage/specprojects/'.$type);
    }*/

    public function action_zhuzes()
    {
        $cats = ORM::factory('Zhuze')->fulltree;
        $this->set('cats', $cats);
    }

    public function action_newcategory()
    {
        $id = (int) $this->request->param('id', 0);
        if ($id == 0)
        {
            throw new HTTP_Exception_404;
        }
        $parent_category = ORM::factory('Zhuze', $id);
        if( $post = $this->request->post() )
        {
            $category = ORM::factory('Zhuze');
            $category->name = Security::xss_clean(Arr::get($post,'name',''));
            if ($parent_category->loaded())
            {
                $category->insert_as_last_child($parent_category);
            }
            else
            {
                $category->make_root();
            }
            $category->save();

            Message::success(I18n::get('Category added'));
            $this->redirect('manage/specprojects/zhuzes');
        }
        $this->set('cancel_url', Url::media('manage/specprojects/zhuzes'));
    }

    public function action_editcategory()
    {
        $id = (int) $this->request->param('id', 0);
        $category = ORM::factory('Zhuze', $id);
        if ( !$category->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if( $post = $this->request->post() )
        {
            $category->name = Security::xss_clean(Arr::get($post,'name',''));
            $category->save();
            Message::success(I18n::get('Category changed'));
            $this->redirect('manage/specprojects/zhuzes');
        }
        $this->set('category', $category)->set('cancel_url', Url::media('manage/specprojects/zhuzes'));
    }

    public function action_upcategory()
    {
        $id = (int)$this->request->param('id', 0);
        $category = ORM::factory('Zhuze', $id);
        if (!$category->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $up_brother = ORM::factory('Zhuze')
            ->where('parent_id','=', $category->parent_id)
            ->and_where('rgt','=', $category->lft - 1)
            ->find();
        if (empty($up_brother->id))
        {
            Message::warn(I18n::get("Category in the top"));
            $this->redirect('manage/specprojects/zhuzes');
        }
        $category->move_to_prev_sibling($up_brother);
        Message::success(I18n::get('Category moved up'));
        $this->redirect('manage/specprojects/zhuzes');
    }

    public function action_downcategory()
    {
        $id = (int)$this->request->param('id', 0);
        $category = ORM::factory('Zhuze', $id);
        if (!$category->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $down_brother = ORM::factory('Zhuze')
            ->where('parent_id','=', $category->parent_id)
            ->and_where('lft','=', $category->rgt + 1)
            ->find();
        if (empty($down_brother->id))
        {
            Message::warn(I18n::get("Category in the down"));
            $this->redirect('manage/specprojects/zhuzes');
        }
        $category->move_to_next_sibling($down_brother);
        Message::success(I18n::get('Category moved down'));
        $this->redirect('manage/specprojects/zhuzes');
    }

    public function action_deletecategory()
    {
        $id = (int)$this->request->param('id', 0);
        $category = ORM::factory('Zhuze', $id);
        if ( !$category->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {

            $category->delete();
            Message::success(I18n::get("Category deleted"));
            $this->redirect('manage/specprojects/zhuzes');
        }
        else
        {
            $this->set('record', $category)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/specprojects/zhuzes'));
        }
    }

}
