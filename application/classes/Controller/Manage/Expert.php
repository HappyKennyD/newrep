<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Manage_Expert extends Controller_Manage_Core
{

    protected $page;

    public function before()
    {
        parent::before();

        $this->page = Security::xss_clean( (int) $this->request->param('page', 0) );
        if( empty($this->page) )
        {
            $this->page=1;
        }
    }

    public function action_index()
    {
        $list = ORM::factory('Expert')->order_by('date', 'DESC');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->set('experts', $list)->set('page', $this->page);;
        $this->set('paginate', $paginate);
    }

    public function action_delete()
    {
        $id = (int)$this->request->param('id', 0);
        $expert = ORM::factory('Expert', $id);
        if (!$expert->loaded()) {
            $this->redirect('manage/expert');
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token) {

            $expert->delete();
            $opinions = ORM::factory('Expert_Opinion')->where('expert_id', '=', $id)->find_all();
            foreach ($opinions as $item) {
                ORM::factory('Expert_Opinion', $item->id)->delete();
            }

            $list = ORM::factory('Expert');
            $paginate = Paginate::factory($list);
            $list = $list->find_all();
            $last_page=$paginate->page_count();

            if( $this->page > $last_page )
            {
                $this->page = $this->page -1;
            }

            if($this->page <=0 )
            {
                $this->page = 1;
            }

            Message::success(i18n::get('Judge and all his positions removed'));
            $this->redirect('manage/expert/page-'.$this->page);
        } else {
            $this->set('expert', $expert)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/expert/page-'.$this->page));
        }
    }

    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $expert = ORM::factory('Expert', $id);
        if($expert->loaded())
        {
            $flag = true;
        }
        else
        {
            $flag = false;
        }
        $user_id = $this->user->id;
        $uploader = View::factory('storage/image')->set('user_id', $user_id)->render();
        $this->set('uploader', $uploader);
        $this->set('expert', $expert);
        $this->set('page', $this->page);
        if ($this->request->method() == Request::POST) {
            try {
                $expert->name = Arr::get($_POST, 'name', '');
                $expert->image = (int)Arr::get($_POST, 'image', '');
                $expert->description = Arr::get($_POST, 'description', '');
                $expert->position = Arr::get($_POST, 'position', '');
                $expert->date = date('Y-m-d H:i:s');
                $expert->user_id = $user_id;
                $expert->save();

                if(!$flag)
                {
                    $list = ORM::factory('Expert');
                    $paginate = Paginate::factory($list);
                    $list = $list->find_all();
                    $this->page=$paginate->page_count();
                }

                Message::success(i18n::get('The expert retained'));
                $this->redirect('manage/expert/view/' . $expert->id.'/page-'.$this->page);
            } catch (ORM_Validation_Exception $e) {
                $errors = $e->errors($e->alias());
                foreach ($errors as $key => $item) {
                    $errors[preg_replace("/(_ru|_kz|_en)/", '', $key)] = preg_replace("/(_ru|_kz|_en)/", '', $item);
                }
                $this->set('expert', $_POST);
                $this->set('errors', $errors);
            }
        }
    }

    public function action_view()
    {
        $id = $this->request->param('id', 0);
        $expert = ORM::factory('expert', $id);
        if (!$expert->loaded()) {
            $this->redirect('manage/expert');
        }
        $this->set('item', $expert)->set('page', $this->page);
    }

    public function action_clearImage()
    {
        $id = $this->request->param('id', 0);
        $expert = ORM::factory('Expert', $id);
        if ($expert->loaded()) {
            $expert->image = 0;
            $expert->save();
        }
        $this->redirect('manage/expert/edit/' . $id);
    }
}