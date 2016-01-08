<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Photosets extends Controller_Manage_Core {

	public function action_index()
	{
        $photosets = ORM::factory('Photoset')->order_by('order', 'ASC');
        $paginate = Paginate::factory($photosets)->paginate(NULL, NULL, 10)->render();
        $photosets = $photosets->find_all();
        //нумерация с 1 для каждого отдельного фотосета
        /*$photo = ORM::factory('Photosets_Attachment')->order_by('photoset_id')->find_all();
        $i =0;
        $photo_id = array();//массив с id фотосетов
        foreach ($photo as $var)
        {
            if (in_array($var->photoset_id,$photo_id))
                continue;
            else
            {
                $photo_id[$i]=$var->photoset_id;
                $i++;
            }
        }
        foreach ($photo_id as $var)
        {
            $cat = ORM::factory('Photosets_Attachment')->where('photoset_id','=',$var)->find_all();
            $i =1;
            foreach ($cat as $val)
            {
                $val->order = $i;
                $val->save();
                $i++;
            }
        }*/
        $this->set('photosets', $photosets);
        $this->set('paginate', $paginate);
	}

    public function action_view()
    {
        $id = $this->request->param('id', 0);
        $photoset = ORM::factory('Photoset', $id);
        if ( !$photoset->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        /* костыль для наклейки водяного знака для фото без наклейки*/
       /* $attachs = $photoset->attach->where('watermark','=','0')->order_by('id', 'ASC')->limit(10)->find_all()->as_array();

        if (count($attachs)>0){
            foreach($attachs as $attach) {
                $image=Image::factory($attach->photo->file_path);
                $watermark= Image::factory('media/images/watermark.png');
                $newwidth=$image->width/4;
                $newheight=$image->height/4;
                $watermark->resize($newwidth, $newheight, Image::WIDTH);
                $image->watermark($watermark, $image->width - $watermark->width-2, $image->height - $watermark->height-2, $opacity =100);
                $image->save($attach->photo->file_path,100);
                $attach->watermark=1;
                $attach->save();
            }
        }*/

        $attachments = $photoset->attach->order_by('order'/*id*/, 'ASC')->find_all()->as_array();
        if (count($attachments)>0){
            $this->set('attachments', $attachments);
        }
        $this->set('photoset', $photoset);
    }

    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $photoset = ORM::factory('Photoset', $id);
        $errors = NULL;
        $attachments = $photoset->attach->order_by('id', 'ASC')->find_all()->as_array();
        $categories = ORM::factory('Photosets_Category')->find_all();
        $this->set('categories', $categories);

        $uploader = View::factory('storage/photos')->set('user_id',$this->user->id)->render();
        $this->set('uploader',$uploader);

        if ( $post = $this->request->post() )
        {
            try
            {
                foreach ($attachments as $attach)
                {
                    ORM::factory('Photosets_Attachment', $attach->id)->delete();
                }

                $post['date'] = date('Y-m-d H:i:s',strtotime($post['date']));
                $photoset->name = Security::xss_clean(Arr::get($post,'name',''));
                $photoset->location = Security::xss_clean(Arr::get($post,'location',''));
                if ($photoset->id == 0)
                {
                    //пордок фотосетов, стрелки вверх/вниз
                    $new_order = ORM::factory('Photoset')->find_all();
                    foreach ($new_order as $val)
                    {
                        $val->order = $val->order+1;
                        $val->save();
                    }
                }
                $photoset->values($post, array('date', 'published', 'is_important', 'category_id'))->save();
                $event = ($id)?'edit':'create';
                $loger = new Loger($event,$photoset->name);
                $loger->log($photoset);

                $i = 1;
                foreach ($_POST['photos'] as $photo)
                {
                    $photo_attach = ORM::factory('Photosets_Attachment')->where('photoset_id', '=', $photoset->id)
                        ->where('storage_id', '=', $photo)->find();
                    $photo_attach->photoset_id = $photoset->id;
                    $photo_attach->storage_id = $photo;
                    $photo_attach->name = $_POST['name_'.$photo];
                    //порядок для добавленных фоток
                    $photo_attach->order = $i;
                    $i++;
                    if (isset($_POST['main_'.$photo])) $photo_attach->main = 1; else $photo_attach->main = 0;
                    $photo_attach->save();
                }

                Message::success('Альбом сохранен');
                $this->redirect('manage/photosets/view/'.$photoset->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }

        if (count($attachments) > 0) $this->set('attachments', $attachments);
        $this->set('item', $photoset);
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $photoset = ORM::factory('Photoset', $id);
        if (!$photoset->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $attachments = $photoset->attach->find_all();
            foreach ($attachments as $attach)
            {
                ORM::factory('Photosets_Attachment', $attach->id)->delete();
            }
            $loger = new Loger('delete',($photoset->name)?$photoset->name:'без навания');
            $loger->log($photoset);
            $photoset->delete();
            Message::success('Фотоальбом удален');
            $this->redirect('manage/photosets');
        }
        else
        {
            $this->set('record', $photoset)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/photosets'));
        }
    }

    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $photoset = ORM::factory('Photoset', $id);
        if ( !$photoset->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $photoset->published )
        {
            $photoset->published = 0;
            $photoset->save();
            Message::success('Фотоальбом скрыт');
        }
        else
        {
            $photoset->published = 1;
            $photoset->save();
            Message::success('Фотоальбом опубликован');
        }
        $this->redirect('manage/photosets/');
    }

    public function action_important()
    {
        $id = $this->request->param('id', 0);
        $photoset = ORM::factory('Photoset', $id);
        if ( !$photoset->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $photoset->is_important )
        {
            $photoset->is_important = 0;
            $photoset->save();
            Message::success('Убрано с главной');
        }
        else
        {
            $photoset->is_important = 1;
            $photoset->save();
            Message::success('Добавлено на главную');
        }
        $this->redirect('manage/photosets/');
    }

    public function action_delImage()
    {
        $id = $this->request->param('id', 0);
        $attach = ORM::factory('Photosets_Attachment', $id);
        if ($attach->loaded())
        {
            $ph_id = $attach->photoset_id;
            $attach->delete();
            $this->redirect('manage/photosets/edit/'.$ph_id);
        }
        else throw new HTTP_Exception_404;
    }

    public function action_category()
    {
        $categories = ORM::factory('Photosets_Category')->find_all();
        $this->set('list',$categories);
    }

    public function action_editcategory()
    {
        $id = (int) $this->request->param('id', 0);
        $item = ORM::factory('Photosets_Category',$id);
        $this->set('item',$item);
        if ($post = $this->request->post())
        {
            $item->name = Security::xss_clean(Arr::get($post,'name',''));
            $item->save();
            Message::success('Категория сохранена');
            $this->redirect('manage/photosets/category');
        }
    }

    public function action_deletecategory()
    {
        $id = (int) $this->request->param('id', 0);
        $item = ORM::factory('Photosets_Category',$id);
        if ( !$item->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $item->delete();
            Message::success('Категория удалена');
            $this->redirect('manage/photosets/category');
        }
        else
        {
            $this->set('record', $item)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/photosets/category'));
        }
        $this->template->set_filename('manage/photosets/delete');
    }

    public function action_show_ru()
    {
        $id = $this->request->param('id', 0);
        $photoset = ORM::factory('Photoset', $id);
        if ( !$photoset->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $photoset->show_ru = $photoset->show_ru == 0 ? 1 : 0;
        $photoset->save();
        $this->redirect($this->referrer);
    }

    public function action_show_kz()
    {
        $id = $this->request->param('id', 0);
        $photoset = ORM::factory('Photoset', $id);
        if ( !$photoset->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $photoset->show_kz = $photoset->show_kz == 0 ? 1 : 0;
        $photoset->save();
        $this->redirect($this->referrer);
    }

    public function action_show_en()
    {
        $id = $this->request->param('id', 0);
        $photoset = ORM::factory('Photoset', $id);
        if ( !$photoset->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $photoset->show_en = $photoset->show_en == 0 ? 1 : 0;
        $photoset->save();
        $this->redirect($this->referrer);
    }

    public function action_up()
    {
        $id = (int)$this->request->param('id', 0);
        $photoset = ORM::factory('Photoset', $id);
        if (!$photoset->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $photoset_up = ORM::factory('Photoset')
            ->where('order','<',$photoset->order)->order_by('order','desc')
            ->find();

        if (empty($photoset_up->id))
        {
            $this->redirect('manage/photosets');
        }
        $order = $photoset->order;
        $photoset->order = $photoset_up->order;
        $photoset_up->order = $order;
        $photoset->save();
        $photoset_up->save();
        $this->redirect('manage/photosets');
    }

    public function action_down()
    {
        $id = (int)$this->request->param('id', 0);
        $photoset = ORM::factory('Photoset', $id);
        if (!$photoset->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $photoset_up = ORM::factory('Photoset')
            ->where('order','>',$photoset->order)->order_by('order','asc')
            ->find();
        if (empty($photoset_up->id))
        {
            $this->redirect('manage/photosets');
        }
        $order = $photoset->order;
        $photoset->order = $photoset_up->order;
        $photoset_up->order = $order;
        $photoset->save();
        $photoset_up->save();
        $this->redirect('manage/photosets');
    }

    public function action_photoup()
    {
        $id  = $this->request->param('id', 0);
        $photoset = ORM::factory('Photosets_Attachment',$id);

        if ( !$photoset->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $next_photo = ORM::factory('Photosets_Attachment')->where('order','<',$photoset->order)->and_where('photoset_id','=',$photoset->photoset_id)->order_by('order','desc')->find();
        if ( !$next_photo->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $order = $photoset->order;
        $photoset->order = $next_photo->order;
        $next_photo->order = $order;
        $photoset->save();
        $next_photo->save();
        $this->redirect('manage/photosets/view/'.$photoset->photoset_id);
    }

    public function action_photodown()
    {
        $id  = $this->request->param('id', 0);
        $photoset = ORM::factory('Photosets_Attachment',$id);

        if ( !$photoset->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $next_photo = ORM::factory('Photosets_Attachment')->where('order','>',$photoset->order)->and_where('photoset_id','=',$photoset->photoset_id)->order_by('order','asc')->find();
        if ( !$next_photo->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $order = $photoset->order;
        $photoset->order = $next_photo->order;
        $next_photo->order = $order;
        $photoset->save();
        $next_photo->save();
        $this->redirect('manage/photosets/view/'.$photoset->photoset_id);
    }
}
