<?php
class Controller_Manage_Sliders extends Controller_Manage_Core
{
    protected $title = 'Слайдер';

    public function action_index()
    {
        $type = Arr::get($_GET,'type','slider');
        if ($type == 'link')
            $this->title = 'Ссылки на сайты партнёров';
        elseif ($type == 'banner')
            $this->title = 'Баннера на главной страницы';

        $sliders = ORM::factory('Slider')->where('type','=',$type)->order_by('order','asc')->find_all();
        $this->set('sliders',$sliders)->set('type',$type)->set('title', $this->title);
    }

    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $slider = ORM::factory('Slider', $id);
        $type = Arr::get($_GET,'type','slider');
        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        $this->set('uploader',$uploader);
        $this->set('slider',$slider)->set('r', Url::media('manage/sliders?type='.$type))->set('type',$type);
        if ($post = $this->request->post())
        {
            if ($id == 0)
            {
                $slider_last = ORM::factory('Slider')
                    ->order_by('order','desc')
                    ->find();
                if (!empty($slider_last->id))
                {
                    $slider->order = $slider_last->order + 1;
                }
                else
                {
                    $slider->order = 1;
                }
                $slider->link_ru = Security::xss_clean(Arr::get($post,'link',''));
                $slider->link_kz = Security::xss_clean(Arr::get($post,'link',''));
                $slider->link_en = Security::xss_clean(Arr::get($post,'link',''));
            }else
            {
                $slider->link = Security::xss_clean(Arr::get($post,'link',''));
            }
            $slider->type = $type;
            $slider->title = Security::xss_clean(Arr::get($post,'title',''));
            $slider->values($post, array('image','is_active'))->save();
            $event = ($id)?'edit':'create';
            $loger = new Loger($event,$slider->link);
            $loger->log($slider);
            $this->redirect('manage/sliders?type='.$type);
        }
    }

    public function action_delete()
    {
        $id = (int)$this->request->param('id', 0);
        $type = Arr::get($_GET,'type','slider');
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $slider = ORM::factory('Slider',$id);
            $loger = new Loger('delete',$slider->link_ru);
            $loger->log($slider);
            $slider->delete();
            $this->redirect('manage/sliders/?type='.$type);
        }
        else
        {
            $this->set('token', Security::token(true))->set('r', Url::media('manage/sliders?type='.$type));
        }
    }

    public function action_active()
    {
        $id = (int)$this->request->param('id', 0);
        $type = Arr::get($_GET,'type','slider');
        $slider = ORM::factory('Slider',$id);
        if ($slider->is_active)
        {
            $slider->is_active = 0;
            $slider->save();
        }
        else
        {
            $slider->is_active = 1;
            $slider->save();
        }
        $this->redirect('manage/sliders?type='.$type);
    }

    public function action_up()
    {
        $id = (int)$this->request->param('id', 0);
        $type = Arr::get($_GET,'type','slider');
        $slider = ORM::factory('Slider', $id);
        if (!$slider->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $slider_up = ORM::factory('Slider')
            ->where('order','<',$slider->order)->order_by('order','desc')
            ->find();
        if (empty($slider_up->id))
        {
            $this->redirect('manage/sliders?type='.$type);
        }
        $order = $slider->order;
        $slider->order = $slider_up->order;
        $slider_up->order = $order;
        $slider->save();
        $slider_up->save();
        $this->redirect('manage/sliders?type='.$type);
    }

    public function action_down()
    {
        $id = (int)$this->request->param('id', 0);
        $type = Arr::get($_GET,'type','slider');
        $slider = ORM::factory('Slider', $id);
        if (!$slider->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $slider_up = ORM::factory('Slider')
            ->where('order','>',$slider->order)->order_by('order','asc')
            ->find();
        if (empty($slider_up->id))
        {
            $this->redirect('manage/sliders?type='.$type);
        }
        $order = $slider->order;
        $slider->order = $slider_up->order;
        $slider_up->order = $order;
        $slider->save();
        $slider_up->save();
        $this->redirect('manage/sliders?type='.$type);
    }

}