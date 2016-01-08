<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Chronology extends Controller_Manage_Core {

    public function action_index()
    {
        $list = ORM::factory('Chronology')->fulltree;
        $this->set('list',$list);
    }

    public function action_new()
    {
        $id = (int) $this->request->param('id', 0);
        $parent_page = ORM::factory('Chronology',$id);
        $siblings = $parent_page->children;
        if( $post = $this->request->post() )
        {
            $page = ORM::factory('Chronology');
            $page->name = Security::xss_clean(Arr::get($post,'name',''));
            $page->start = Security::xss_clean(Arr::get($post,'start',''));
            $page->finish = Security::xss_clean(Arr::get($post,'finish',''));
            $page->values($post, array('key'));
            if ($parent_page->loaded())
            {
                $page->insert_as_last_child($parent_page);
            }
            else
            {
                $page->make_root();
            }
            if ($parent_page->lvl > 2)
            {
            $i = 0;
            foreach ($siblings as $item)
            {
                $element = ORM::factory('Chronology',$item->id);
                $element->value = $post['value'][$i];
                $element->save();
                $i++;
            }
            $page->value = $post['value'][$i];
            $page->save();
            }
            Message::success('Добавлено');
            $this->redirect('manage/chronology');
        }
        $this->set('cancel_url', Url::media('manage/chronology'))->set('siblings',$siblings)->set('period',$parent_page);
    }

    public function action_edit()
    {
        $id = (int) $this->request->param('id', 0);
        $page = ORM::factory('Chronology',$id);
        if ( !$page->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $period_brother = ORM::factory('Chronology')
            ->where('parent_id','=',$page->parent->parent_id)->order_by('lft','ASC')->find_all();
        $siblings = ORM::factory('Chronology')->where('parent_id','=',$page->parent_id)->order_by('lft','asc')->find_all();
        if( $post = $this->request->post() )
        {
            $new_layer = false;
            if ($page->parent_id!=Arr::get($post,'layer',$page->parent_id))
            {
                $new_layer = true;
            }
            if ($new_layer)
            {
                $page_new = ORM::factory('Chronology');
                $page_new->name_ru = $page->name_ru;
                $page_new->name_kz = $page->name_kz;
                $page_new->name_en = $page->name_en;
                $page_new->start_ru = $page->start_ru;
                $page_new->start_kz = $page->start_kz;
                $page_new->start_en = $page->start_en;
                $page_new->finish_ru = $page->finish_ru;
                $page_new->finish_kz = $page->finish_kz;
                $page_new->finish_en = $page->finish_en;
                $page_new->value = $page->value;
            }
            else
            {
                $page_new = $page;
            }
            $page_new->name = Security::xss_clean(Arr::get($post,'name',''));
            $page_new->start = Security::xss_clean(Arr::get($post,'start',''));
            $page_new->finish = Security::xss_clean(Arr::get($post,'finish',''));
            $page_new->values($post, array('key'));
            if ($new_layer)
            {
                $parent_page = ORM::factory('Chronology', Arr::get($post,'layer',$page->parent_id));
                $page_new->insert_as_last_child($parent_page);
            }
            else
            {
                $page_new->save();
            }
            if ($page->lvl > 3 AND ! $new_layer)
            {
                $i = 0;
                foreach ($siblings as $item)
                {
                    $element = ORM::factory('Chronology',$item->id);
                    $element->value = $post['value'][$i];
                    $element->save();
                    $i++;
                }
            }
            if ($new_layer)
            {
                $page->delete();
            }
            Message::success('Сохранено');
            $this->redirect('manage/chronology');
        }
        $this->set('page', $page)
            ->set('siblings', $siblings)
            ->set('cancel_url', Url::media('manage/chronology'))
            ->set('period', $page->parent)
            ->set('period_brother', $period_brother);
    }


    public function action_delete()
    {
        $id = (int)$this->request->param('id', 0);
        $page = ORM::factory('Chronology', $id);
        if ( !$page->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {

            $page->delete();
            Message::success('Раздел удален');
            $this->redirect('manage/chronology');
        }
        else
        {
            $this->set('record', $page)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/chronology'));
        }
    }

    /*
 * Перемещение раздела по списку вверх
 */
    public function action_up()
    {
        $id = (int)$this->request->param('id', 0);
        $page = ORM::factory('Chronology', $id);
        if (!$page->loaded())
        {
            throw new HTTP_Exception_404;
        }

        $page_up_brother = ORM::factory('Chronology')
            ->where('parent_id','=',$page->parent_id)
            ->and_where('rgt','<',$page->lft)
            ->order_by('rgt','DESC')
            ->limit(1)
            ->find();

        if (empty($page_up_brother->id))
        {
            Message::warn('Раздел уже находится на вверху списка');
            $this->redirect('manage/chronology');
        }
        $page->move_to_prev_sibling($page_up_brother);
        Message::success('Раздел перемещен вверх');
        $this->redirect('manage/chronology');
    }

    /*
    * Перемещение раздела по списку вниз
    */
    public function action_down()
    {
        $id = (int)$this->request->param('id', 0);
        $page = ORM::factory('Chronology', $id);
        if (!$page->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $page_down_brother = ORM::factory('Chronology')
            ->where('parent_id','=',$page->parent_id)
            ->and_where('lft','>',$page->rgt)
            ->order_by('lft','ASC')
            ->limit(1)
            ->find();
        if (empty($page_down_brother->id))
        {
            Message::warn('Раздел уже находится внизу списка');
            $this->redirect('manage/chronology');
        }
        $page->move_to_next_sibling($page_down_brother);
        Message::success('Раздел перемещен вниз');
        $this->redirect('manage/chronology');
    }


    public function action_one()
    {
        $id = (int) $this->request->param('id', 0);
        $parent_page = ORM::factory('Chronology',$id);
        if( $post = $this->request->post() )
        {
            $page = ORM::factory('Chronology');
            $page->name = Security::xss_clean(Arr::get($post,'name',''));
            $page->start = Security::xss_clean(Arr::get($post,'start',''));
            $page->finish = Security::xss_clean(Arr::get($post,'finish',''));
            if ($parent_page->loaded())
            {
                $page->insert_as_last_child($parent_page);
            }
            else
            {
                $page->make_root();
            }
            Message::success('Добавлено');
            $this->redirect('manage/chronology');
        }
        $this->set('cancel_url', Url::media('manage/chronology'))->set('period',$parent_page);
    }
}