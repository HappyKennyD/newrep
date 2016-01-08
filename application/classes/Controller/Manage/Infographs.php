<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Infographs extends Controller_Manage_Core {

	public function action_index()
	{
        $infographs = ORM::factory('Infograph')->where('language', '=', $this->language)->order_by('order','asc');
        $paginate = Paginate::factory($infographs)->paginate(NULL, NULL, 10)->render();
        $infographs = $infographs->find_all();
        /*$i = 1;
        foreach ($infographs as $val)
        {
            $val->order = $i;
            $val->save();
            $i++;
        }*/
        $this->set('infographs', $infographs);
        $this->set('paginate', $paginate);
	}

    public function action_view()
    {
        $id = $this->request->param('id', 0);
        $infograph = ORM::factory('Infograph', $id);
        if ( !$infograph->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('infograph', $infograph);
    }

    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $infograph = ORM::factory('Infograph', $id);
        $language = $infograph->loaded()?$infograph->language:$this->language;
        $this->set('language', $language);
        $errors = NULL;
        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        if ( $post = $this->request->post() )
        {
            try
            {
                $post['date'] = date('Y-m-d H:i:s',strtotime($post['date']));
                $infograph->title = Security::xss_clean(Arr::get($post,'title',''));
                if ($infograph->id == 0)
                {
                    $new_order = ORM::factory('Infograph')->find_all();
                    foreach ($new_order as $val)
                    {
                        $val->order = $val->order+1;
                        $val->save();
                    }
                }
                $infograph->values($post, array('image', 'published', 'language','date'))->save();
                $event = ($id)?'edit':'create';
                $loger = new Loger($event,$infograph->title);
                $loger->log($infograph);
                $this->redirect('manage/infographs/view/'.$infograph->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('uploader',$uploader);
        $this->set('item', $infograph);
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $infograph = ORM::factory('Infograph', $id);
        if (!$infograph->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $loger = new Loger('delete',$infograph->title);
            $loger->log($infograph);
            $infograph->delete();
            Message::success('Запись удалена');
            $this->redirect('manage/infographs');
        }
        else
        {
            $this->set('record', $infograph)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/infographs'));
        }
    }

    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $infograph = ORM::factory('Infograph', $id);
        if ( !$infograph->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $infograph->published )
        {
            $infograph->published = 0;
            $infograph->save();
            Message::success('Запись скрыта');
        }
        else
        {
            $infograph->published = 1;
            $infograph->save();
            Message::success('Запись опубликована');
        }
        $this->redirect('manage/infographs/');
    }

    public function action_clearImage()
    {
        $id = $this->request->param('id', 0);
        $infograph = ORM::factory('Infograph',$id);
        if ($infograph->loaded())
        {
            $infograph->image = 0;
            $infograph->save();
        }
        $this->redirect('manage/infographs/edit/'.$id);
    }

    public function action_up()
    {
        $id = (int)$this->request->param('id', 0);
        $infograph = ORM::factory('Infograph', $id);
        if (!$infograph->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $infograph_up = ORM::factory('Infograph')
            ->where('order','<',$infograph->order)->order_by('order','desc')
            ->find();

        if (empty($infograph_up->id))
        {
            $this->redirect('manage/infographs');
        }
        $order = $infograph->order;
        $infograph->order = $infograph_up->order;
        $infograph_up->order = $order;
        $infograph->save();
        $infograph_up->save();
        $this->redirect('manage/infographs');
    }

    public function action_down()
    {
        $id = (int)$this->request->param('id', 0);
        $infograph = ORM::factory('Infograph', $id);
        if (!$infograph->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $infograph_up = ORM::factory('Infograph')
            ->where('order','>',$infograph->order)->order_by('order','asc')
            ->find();
        if (empty($infograph_up->id))
        {
            $this->redirect('manage/infographs');
        }
        $order = $infograph->order;
        $infograph->order = $infograph_up->order;
        $infograph_up->order = $order;
        $infograph->save();
        $infograph_up->save();
        $this->redirect('manage/infographs');
    }
}
