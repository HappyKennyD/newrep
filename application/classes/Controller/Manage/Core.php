<?php
class Controller_Manage_Core extends Controller_Kotwig
{
    /** @var Model_Auth_User */
    protected $user = false;
    protected $title = '';
    protected $language = 'ru';
    protected $metadata = '';
    protected $referrer;

    public function before()
    {
        //$this->redirect('http://ehistory.kz/manage');
        parent::before();
	$this->response->headers('cache-control','private');
        // creating and attaching page metadata
        $this->metadata = new Model_Metadata();
        $this->metadata->title(__(Application::instance()->get('title')), false);
        $this->set('_metadata', $this->metadata);
        Auth::instance()->auto_login();
        if (!Auth::instance()->logged_in())
        {
            $this->redirect('manage/auth/login');
        }
        else
        {
            $id             = Auth::instance()->get_user()->id;
            $user           = ORM::factory('user', $id);
            $input          = $user->has('roles', ORM::factory('role', array('name' => 'admin'))) || $user->has('roles', ORM::factory('Role', array('name' => 'moderator')));
            $input_redactor = $user->has('roles', ORM::factory('Role', array('name' => 'redactor')));
            if (!$input && !$input_redactor)
            {
                $this->redirect('/manage/auth/logout');
            }
            if (!$input && (strtolower($this->request->controller()) != 'ehistory' && strtolower($this->request->controller()) != 'language'))
            {
                $this->redirect('manage/ehistory');
            }
        }
        $this->user = Auth::instance()->get_user();

        if (Request::$initial === Request::$current)
        {
            $messages = Notify::instance()->get_all_once();
            $this->set('_notifications', $messages);
        }

        $language       = Session::instance()->get('_language', 'ru');
        $this->language = in_array($language, array('ru', 'en', 'kz')) ? $language : 'ru';
        I18n::lang($this->language);
        $rr = Request::initial()->uri().urlencode(URL::query(null, true));
        $rr = trim($rr, '/');
        //$this->metadata->title('Sharua.kz', false);

        $countcomm = ORM::factory('Comment')->where('status','=','0')->count_all(); //смотрим сколько новых коментов

        $this->set('_user', $this->user)
             ->set('_language', $this->language)
             ->set('_return_url', $rr)
             ->set('_countcomm',$countcomm); //вносим в переменную количество новых коментов


        $knigi = ORM::factory('Book')->where('category_id','=','0')->find_all(); //смотрим сколько книг без категории

        if ($knigi)
        {
            if (count($knigi)>0)
            {
                $this->set('_uncatcount',count($knigi)); //вносим в переменную количество книг без категории
            }
        }

        $this->referrer = Request::initial()->referrer();

        if (Message::get())
        {
            $this->set('basic_message', Message::display('/message/basic'));
        }
    }

    public function after()
    {
        $this->metadata->title(__($this->title));
        parent::after();
    }
}
