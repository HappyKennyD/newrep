<?php
class Controller_Manage_Lines extends Controller_Manage_Core
{
    protected $title = 'События';

    public function action_list()
    {
        $id = (int) $this->request->param('id', 0);
        $period = ORM::factory('Chronology',$id);
        if ( !$period->loaded() )
        {
            throw new HTTP_Exception_404('Page not found');
        }
        $list = $period->lines->order_by('line_x', 'asc');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->set('list', $list)->set('period', $period)->set('paginate', $paginate);
    }

    public function action_show()
    {
        $id = (int) $this->request->param('id', 0);
        $item = ORM::factory('Chronology_Line',$id);
        if ( !$item->loaded() )
        {
            throw new HTTP_Exception_404('Page not found');
        }
        $period = ORM::factory('Chronology', $item->period_id);
        $this->set('item', $item)->set('period', $period);
    }

    public function action_edit()
    {
        $id = (int) $this->request->param('id', 0);
        $item = ORM::factory('Chronology_Line', $id);
        if ( $item->loaded() )
        {
            $period = ORM::factory('Chronology', $item->period_id);
        }
        else
        {
            $period = ORM::factory('Chronology', Arr::get($_GET, 'period', 0));
            if ( !$period->loaded() )
            {
                throw new HTTP_Exception_404('Page not found');
            }
        }
        $period_brother = ORM::factory('Chronology')
            ->where('parent_id','=',$period->parent_id)->order_by('lft','ASC')->find_all();
        $siblings = ORM::factory('Chronology')->where('parent_id','=',$period->id)->order_by('lft','asc')->find_all();
        $list = $period->lines->order_by('line_x', 'asc')->find_all();
        $r = Url::media('manage/lines/list/'.$period->id);
        $uploader = View::factory('storage/image')->set('user_id',$this->user->id)->render();
        $this->set('uploader',$uploader);

        if( $post = $this->request->post() )
        {
            $item->period_id = Arr::get($post, 'layer', $period->id);
            $item->date = Security::xss_clean(Arr::get($post, 'date', ''));
            $item->title = Security::xss_clean(Arr::get($post, 'title', ''));
            $item->description = Security::xss_clean(Arr::get($post, 'description', ''));
            $item->text = Security::xss_clean(Arr::get($post, 'text', ''));
            $item->values($post, array('line_x','published','image'))->save();
            $event = ($id)?'edit':'create';
            $loger = new Loger($event,$item->title);
            $loger->logThis($item);

            $i = 0;
            foreach ($siblings as $el)
            {
                $element = ORM::factory('Chronology',$el->id);
                $element->value = $post['value'][$i];
                $element->save();
                $i++;
            }
            Message::success('Событие сохранено');
            $this->redirect('manage/lines/show/'.$item->id);
        }
        $this->set('item', $item)
            ->set('r', $r)
            ->set('period', $period)
            ->set('list', $list)
            ->set('siblings', $siblings)
            ->set('period_brother', $period_brother);
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $item = ORM::factory('Chronology_Line',$id);
        if ( !$item->loaded() )
        {
            throw new HTTP_Exception_404('Page not found');
        }
        $period = ORM::factory('Chronology',$item ->period_id);
        $token = Arr::get($_POST, 'token', false);
        if ( $this->request->post() AND Security::token() === $token )
        {
            $loger = new Loger('delete',$item->title);
            $loger->logThis($item);
            $item->delete();
            Message::success('Событие удалено');
            $this->redirect('manage/lines/list/'.$period->id);
        }
        else
        {
            $this->set('token', Security::token(true))
                ->set('r', Url::media('manage/lines/list/'.$period->id))
                ->set('period', $period);
        }
    }


    public function action_published()
    {
        $id = $this->request->param('id', 0);
        $content = ORM::factory('Chronology_Line', $id);
        if ( !$content->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $content->published )
        {
            $content->published = 0;
            $content->save();
            Message::success('Событие скрыто');
        }
        else
        {
            $content->published = 1;
            $content->save();
            Message::success('Событие показано');
        }
        $this->redirect('manage/lines/list/'.$content->period_id);
    }

    public function action_clearImage()
    {
        $id = $this->request->param('id', 0);
        $content = ORM::factory('Chronology_Line', $id);
        if ($content->loaded())
        {
            $content->image = 0;
            $content->save();
        }
        $this->redirect('manage/lines/edit/'.$id);
    }
}