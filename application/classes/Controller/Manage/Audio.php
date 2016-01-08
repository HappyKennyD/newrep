<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Audio extends Controller_Manage_Core {

	public function action_index()
	{
        $cat = (int) Arr::get($_GET, 'category', 0);
        $audio = ORM::factory('Audio');
        if ($cat>0)
        {
            $audio = $audio->and_where('category_id', '=', $cat)->order_by('numb');
        }else $audio = $audio->order_by('title');
        $paginate = Paginate::factory($audio)->paginate(NULL, NULL, 10)->render();
        $audio = $audio->find_all();
        $this->set('audio', $audio);
        $this->set('paginate', $paginate);

        $cats = ORM::factory('Audio_Category')->fulltree;
        $this->set('cats', $cats);

        if ($cat > 0)
        {
            $cat = ORM::factory('Audio_Category', $cat);
            if ($cat->loaded())
            {
                $this->set('cat', $cat);
            }
        }
	}

    public function action_clearfile()
    {
        $id = (int) $this->request->param('id', 0);
        $audio = ORM::factory('Audio', $id);
        if (!$audio->loaded())
        {
            throw new HTTP_Exception_404;
        }

        $audio->storage_id = 0;
        $audio->save();
        $this->redirect('manage/audio/edit/'.$id);
    }

    public function action_important()
    {
        $id = $this->request->param('id', 0);
        $audio = ORM::factory('Audio', $id);
        if ( !$audio->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $audio->is_important )
        {
            $audio->is_important = 0;
            $audio->save();
        }
        else
        {
            $audio->is_important = 1;
            $audio->save();
        }
        $this->redirect('manage/audio');
    }

    public function action_show_ru()
    {
        $id = $this->request->param('id', 0);
        $audio = ORM::factory('Audio', $id);
        if ( !$audio->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $audio->show_ru = $audio->show_ru == 0 ? 1 : 0;
        $audio->save();
        $this->redirect('manage/audio/');
    }

    public function action_show_kz()
    {
        $id = $this->request->param('id', 0);
        $audio = ORM::factory('Audio', $id);
        if ( !$audio->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $audio->show_kz = $audio->show_kz == 0 ? 1 : 0;
        $audio->save();
        $this->redirect('manage/audio/');
    }

    public function action_show_en()
    {
        $id = $this->request->param('id', 0);
        $audio = ORM::factory('Audio', $id);
        if ( !$audio->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $audio->show_en = $audio->show_en == 0 ? 1 : 0;
        $audio->save();
        $this->redirect('manage/audio/');
    }

    public function action_edit()
    {
        $id = (int) $this->request->param('id', 0);
        $audio = ORM::factory('Audio', $id);
        $this->set('audio', $audio);

        $uploader = View::factory('storage/audio')->set('user_id', $this->user->id)->render();
        $this->set('uploader', $uploader);

        $cats = ORM::factory('Audio_Category')->fulltree;
        $this->set('cats', $cats);

        if ($this->request->method() == 'POST')
        {
            $title = Security::xss_clean(Arr::get($_POST, 'title', ''));
            $category_id = (int) Arr::get($_POST, 'category_id', 0);
            $rubric = Security::xss_clean(Arr::get($_POST, 'rubric', ''));
            $storage_id = (int) Arr::get($_POST, 'storage_id', 0);
            $published = (int) Arr::get($_POST, 'published', 0);

            if(!$id){
                $item_last = ORM::factory('Audio')
                    ->where('category_id','=',$category_id)
                    ->order_by('numb','desc')
                    ->find();
                if (!empty($item_last->id))
                {
                    $audio->numb = $item_last->numb + 1;
                }
                else
                {
                    $audio->numb = 1;
                }
            }
            try
            {
                $audio->title = $title;
                $audio->category_id = $category_id;
                $audio->rubric = $rubric;
                $audio->storage_id = $storage_id;
                $audio->published = $published;
                $audio->save();

                $event = ($id)?'edit':'create';
                $loger = new Loger($event,$audio->title);
                $loger->log($audio);

                $this->redirect('manage/audio/');
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
    }

    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $audio = ORM::factory('Audio', $id);
        if ( !$audio->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $audio->published )
        {
            $audio->published = 0;
            $audio->save();
            Message::success(I18n::get('Audio hided'));
        }
        else
        {
            $audio->published = 1;
            $audio->save();
            Message::success(I18n::get('Audio unhided'));
        }
        $this->redirect('manage/audio/');
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $audio = ORM::factory('Audio', $id);
        if (!$audio->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $loger = new Loger('delete',$audio->title);
            $loger->log($audio);
            $audio->delete();
            Message::success(I18n::get('Record deleted'));
            $this->redirect('manage/audio');
        }
        else
        {
            $this->set('record', $audio)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/audio'));
        }
    }

    public function action_category()
    {
        $cats = ORM::factory('Audio_Category')->fulltree;
        $this->set('cats', $cats);
    }

    public function action_newcategory()
    {
        $id = (int) $this->request->param('id', 0);
        if ($id == 0)
        {
            throw new HTTP_Exception_404;
        }
        $parent_category = ORM::factory('Audio_Category', $id);
        if( $post = $this->request->post() )
        {
            $category = ORM::factory('Audio_Category');
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
            $this->redirect('manage/audio/category');
        }
        $this->set('cancel_url', Url::media('manage/audio/category'));
    }

    public function action_editcategory()
    {
        $id = (int) $this->request->param('id', 0);
        $category = ORM::factory('Audio_Category', $id);
        if ( !$category->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if( $post = $this->request->post() )
        {
            $category->name = Security::xss_clean(Arr::get($post,'name',''));
            $category->save();
            Message::success(I18n::get('Category changed'));
            $this->redirect('manage/audio/category');
        }
        $this->set('category', $category)->set('cancel_url', Url::media('manage/audio/category'));
    }

    public function action_upcategory()
    {
        $id = (int)$this->request->param('id', 0);
        $category = ORM::factory('Audio_Category', $id);
        if (!$category->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $up_brother = ORM::factory('Audio_Category')
            ->where('parent_id','=', $category->parent_id)
            ->and_where('rgt','=', $category->lft - 1)
            ->find();
        if (empty($up_brother->id))
        {
            Message::warn(I18n::get("Category in the top"));
            $this->redirect('manage/audio/category');
        }
        $category->move_to_prev_sibling($up_brother);
        Message::success(I18n::get('Category moved up'));
        $this->redirect('manage/audio/category');
    }

    public function action_downcategory()
    {
        $id = (int)$this->request->param('id', 0);
        $category = ORM::factory('Audio_Category', $id);
        if (!$category->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $down_brother = ORM::factory('Audio_Category')
            ->where('parent_id','=', $category->parent_id)
            ->and_where('lft','=', $category->rgt + 1)
            ->find();
        if (empty($down_brother->id))
        {
            Message::warn(I18n::get("Category in the down"));
            $this->redirect('manage/audio/category');
        }
        $category->move_to_next_sibling($down_brother);
        Message::success(I18n::get('Category moved down'));
        $this->redirect('manage/audio/category');
    }

    public function action_deletecategory()
    {
        $id = (int)$this->request->param('id', 0);
        $category = ORM::factory('Audio_Category', $id);
        if ( !$category->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {

            $category->delete();
            Message::success(I18n::get("Category deleted"));
            $this->redirect('manage/audio/category');
        }
        else
        {
            $this->set('record', $category)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/audio/category'));
        }
    }
    public function action_up()
    {
        $id = (int)$this->request->param('id', 0);
        $audio = ORM::factory('Audio', $id);
        if (!$audio->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $up_brother = ORM::factory('Audio')
            ->where('category_id','=', $audio->category_id)
            ->and_where('numb','<',$audio->numb)->order_by('numb','desc')
            ->find();
        if (empty($up_brother->id))
        {
            Message::warn(I18n::get("Trek in the top"));
            $this->redirect('manage/audio?category='.$audio->category_id);
        }
        $numb=$audio->numb;
        $audio->numb=$up_brother->numb;
        $audio->save();
        $up_brother->numb=$numb;
        $up_brother->save();
        $this->redirect('manage/audio?category='.$audio->category_id);
    }

    public function action_down()
    {
        $id = (int)$this->request->param('id', 0);
        $audio = ORM::factory('Audio', $id);
        if (!$audio->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $up_brother = ORM::factory('Audio')
            ->where('category_id','=', $audio->category_id)
            ->and_where('numb','>',$audio->numb)->order_by('numb','asc')
            ->find();
        if (empty($up_brother->id))
        {
            Message::warn(I18n::get("Trek in the down"));
            $this->redirect('manage/audio?category='.$audio->category_id);
        }
        $numb=$audio->numb;
        $audio->numb=$up_brother->numb;
        $audio->save();
        $up_brother->numb=$numb;
        $up_brother->save();
        $this->redirect('manage/audio?category='.$audio->category_id);
    }
}
