<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Manage_Expertopinions extends Controller_Manage_Core
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

        $this->set('page', $this->page);
    }

    public function action_index()
    {
        $list = ORM::factory('Expert_Opinion')->order_by('date', 'DESC');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->render();
        $list = $list->find_all();
        $this->set('opinions', $list);
        $this->set('paginate', $paginate);
    }

    public function action_delete()
    {
        $id = (int)$this->request->param('id', 0);
        $expert = ORM::factory('Expert_Opinion', $id);
        if (!$expert->loaded()) {
            $this->redirect('manage/expertopinions');
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token) {
            $loger = new Loger('delete',$expert->title);
            $loger->logThis($expert);
            $expert->delete();

            $list = ORM::factory('Expert_Opinion');
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

            Message::success(i18n::get('The position of the expert removed'));
            $this->redirect('manage/expertopinions/page-'.$this->page);
        } else {
            $this->set('item', $expert)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/expertopinions/page-'.$this->page));
        }
    }

    public function action_edit()
    {
        $id = $this->request->param('id', 0);
        $opinion = ORM::factory('Expert_Opinion', $id);
        $experts = ORM::factory('Expert')->order_by('name_' . I18n::$lang)->find_all();
        $user_id = $this->user->id;
        $this->set('opinion', $opinion);
        $this->set('experts', $experts);
        if ($this->request->method() == Request::POST) {
            try {
                $opinion->expert_id = Arr::get($_POST, 'expert_id', '');
                $opinion->title = Arr::get($_POST, 'title', '');
                $opinion->description = Arr::get($_POST, 'description', '');
                $opinion->text = Arr::get($_POST, 'text', '');
                $opinion->protected = Arr::get($_POST, 'protected', '');
                $opinion->date = date('Y-m-d H:i:s');
                $opinion->user_id = $user_id;
                $opinion->save();

                $event = ($id)?'edit':'create';
                $loger = new Loger($event,$opinion->title);
                $loger->logThis($opinion);
                Message::success(i18n::get('The position of an expert retained'));
                $this->redirect('manage/expertopinions/view/' . $opinion->id . '/page-' . $this->page);
            } catch (ORM_Validation_Exception $e) {
                $errors = $e->errors($e->alias());
                foreach ($errors as $key => $item) {
                    $errors[preg_replace("/(_ru|_kz|_en)/", '', $key)] = preg_replace("/(_ru|_kz|_en)/", '', $item);
                }
                $this->set('opinion', $_POST);
                $this->set('errors', $errors);
            }
        }
    }

    public function action_view()
    {
        $id = $this->request->param('id', 0);
        $expert = ORM::factory('Expert_Opinion', $id);
        if (!$expert->loaded()) {
            $this->redirect('manage/expertopinions');
        }
        $this->set('item', $expert);
    }

}