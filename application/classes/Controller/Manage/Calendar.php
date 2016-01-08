<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Calendar extends Controller_Manage_Core {

    public function action_index()
    {
        $month = date('n');
        $day = date('j');
        if (Arr::get($_GET,'m',0)) {
            $month = (int)Arr::get($_GET,'m', 0);
            if ($month!=date('n'))
            {
                $day = 1;
            }
        }
        $array_month = array(1 => " January", 2 => " February", 3 => " March", 4 => " April",
                             5 => " May", 6 => " June", 7 => " July", 8 => " August",
                             9 => " September", 10 => " October", 11 => " November", 12 => " December");
        $sdate = (Arr::get($_GET,'sdate')!='')?date("Y-m-01",strtotime(Arr::get($_GET,'sdate'))):Date('Y-m-01');
        $edate = (Arr::get($_GET,'sdate')!='')?date("Y-m-01",strtotime(Arr::get($_GET,'sdate')." +1 month")):Date('Y-m-01',strtotime("+1 month"));
        $this->set('month',$month)->set('day',$day)->set('text_month',$array_month[$month])->set('count_day_in_month',date('t',strtotime('01-'.$month.'-2012')))
        ->set('sdate',$sdate)->set('edate',$edate);
    }

    public function action_list()
    {
        if (!$month = Arr::get($_GET,'m',0) OR !$day = Arr::get($_GET,'d',0)) {
            throw new HTTP_Exception_404('Page not found');
        }
        $list = ORM::factory('Calendar')->where('day','=',$day)->and_where('month','=',$month);
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->set('list', $list)->set('day',$day)->set('month',$month);
        $this->set('paginate', $paginate);
    }

    /*
    * Просмотр
    */
    public function action_view()
    {
        $id = $this->request->param('id', 0);
        $item = ORM::factory('Calendar', $id);
        if ( !$item->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('item', $item)->set('day',$item->day)->set('month',$item->month);
    }

    /*
     * Редактирование/ добавление
     */
    public function action_edit()
    {
        if ($id = $this->request->param('id', 0))
        {
            $item = ORM::factory('Calendar', $id);
            if (!$item->loaded())
            {
                throw new HTTP_Exception_404;
            }
            $month = $item->month;
            $day = $item->day;
        }
        else
        {
            if (!$month = Arr::get($_GET,'m',0) OR !$day = Arr::get($_GET,'d',0)) {
                throw new HTTP_Exception_404('Page not found');
            }
            $item = ORM::factory('Calendar');
        }
        $errors = NULL;
        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        if ( $post = $this->request->post() )
        {
            try
            {
                $item->title = Security::xss_clean(Arr::get($post,'title',''));
                $item->desc = Security::xss_clean(Arr::get($post,'desc',''));
                $item->text = Security::xss_clean(Arr::get($post,'text',''));
                $item->day = $day;
                $item->month = $month;
                $item->year = Security::xss_clean(Arr::get($post,'year',''));
                $item->values($post, array('image', 'published', 'is_important'))->save();
                $event = ($id)?'edit':'create';
                $loger = new Loger($event,$item->title);
                $loger->logThis($item);
                Message::success('Сохранено');
                $this->redirect('manage/calendar/view/'.$item->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
        $this->set('uploader',$uploader);
        $this->set('item', $item)->set('day',$day)->set('month',$month);
    }

    /*
     * Удаление
     */
    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $item = ORM::factory('Calendar', $id);
        if (!$item->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $month = $item->month;
        $day = $item->day;
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $loger = new Loger('delete',$item->title);
            $loger->logThis($item);
            $item->delete();
            Message::success('Удалено');
            $this->redirect('manage/calendar/list?m='.$month.'&d='.$day);
        }
        else
        {
            $this->set('record', $item)->set('month',$month)->set('day',$day)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/calendar/list?m='.$month.'&d='.$day));
        }
    }

    /*
     * Опубликовать/скрыть
     */
    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $item = ORM::factory('Calendar', $id);
        if (!$item->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if ( $item->published )
        {
            $item->published = 0;
            $item->save();
            Message::success('Скрыто');
        }
        else
        {
            $item->published = 1;
            $item->save();
            Message::success('Опубликовано');
        }
        $this->redirect('manage/calendar/list?m='.$item->month.'&d='.$item->day);
    }

    public function action_important()
    {
        $id = $this->request->param('id', 0);
        $item = ORM::factory('Calendar', $id);
        if (!$item->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if ( $item->is_important )
        {
            $item->is_important = 0;
            $item->save();
            Message::success('Убрано с главной');
        }
        else
        {
            $item->is_important = 1;
            $item->save();
            Message::success('На главную');
        }
        $this->redirect('manage/calendar/list?m='.$item->month.'&d='.$item->day);
    }

    /*
     * Удалить изображение
     * TODO реализовать через ajax
     */
    public function action_clearimage()
    {
        $id = $this->request->param('id', 0);
        $item = ORM::factory('Calendar', $id);
        if ($item->loaded())
        {
            $item->image = 0;
            $item->save();
        }
        $this->redirect('manage/calendar/edit/'.$item->id);
    }


}