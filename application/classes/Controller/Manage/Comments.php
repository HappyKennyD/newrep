<?php

/*
 * Реализация управления комментариями
 */
class Controller_Manage_Comments extends Controller_Manage_Core
{
    protected $title = 'Комментарии';

    /*
     * Список комментариев
     */
    public function action_index()
    {
        $type = (int)Arr::get($_GET,'type',0);
        $list = ORM::factory('Comment')->where('status','=',$type)->order_by('date','desc');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->set('type',$type);
        $this->set('list', $list);
        $this->set('paginate', $paginate);
    }
    /*
     * Одобрение комментария
     */
    public function action_success()
    {
        $type = (int)Arr::get($_GET,'type',0);
        $id = $this->request->param('id', 0);
        $item = ORM::factory('Comment', $id);
        if ( !$item->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $item->status = 1;
        $item->save();
        Message::success('Комментарий одобрен');
        $this->redirect('manage/comments');
    }

    /*
     * Просмотр комментариев
     *
     */
    public function action_show()
    {
        $id = $this->request->param('id', 0);
        $item = ORM::factory('Comment', $id);
        if ( !$item->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('item',$item);
    }


    /*
     * Удаление комментария
     */
    public function action_delete()
    {
        $type = (int)Arr::get($_GET,'type',0);
        $id = (int) $this->request->param('id', 0);
        $item = ORM::factory('Comment', $id);
        if (!$item->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $item->delete();
            Message::success('Комментарий удален');
            $this->redirect('manage/comments?type='.$type);
        }
        else
        {
            $this->set('type',$type);
            $this->set('record', $item)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/comments?type='.$type));
        }
    }
}