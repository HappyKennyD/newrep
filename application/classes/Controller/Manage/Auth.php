<?php
class Controller_Manage_Auth extends Controller_Kotwig
{
    /** @var  Model_Metadata */
    protected $metadata;
    public function before()
    {
        parent::before();
        $this->metadata = Model::factory('Metadata');
        $this->set('_metadata', $this->metadata);
        Auth::instance()->auto_login();
        $this->set('_language', i18n::lang());
        $this->metadata->title('Вход в панель управления', false);
    }

    public function action_index()
    {
        $this->redirect('manage/auth/login');
    }

    public function action_login()
    {
        $captcha = Captcha::instance();
        $this->template->captcha = $captcha;
        $this->template->message = '';
        if (Auth::instance()->logged_in())
        {
            $this->redirect('/');
        }

        $username = Arr::get($_POST, 'username', '');
        $password = Arr::get($_POST, 'password', '');
        $remember = (bool)Arr::get($_POST, 'remember', false);
        $error = false;
        $message = false;  
        if ($this->request->method() == Request::POST)
        {
            $cpt = Captcha::valid($_POST['captcha']);
            if ($cpt) {
                if (Auth::instance()->login($username, $password, $remember)) {
                    $this->redirect('manage');
                } else {
                    $error = true;
                    //$this->set('error', true);
                }
            }
            else {
                    $message = true;
                    //$this->set('error', true);
            }
        }
        $this->set('username', $username)->set('remember', $remember?'checked':'')->set('error',$error)->set('message',$message);
    }

    public function action_logout()
    {
        if (Auth::instance()->logged_in())
        {
            Auth::instance()->logout();
        }
        $this->redirect('manage/auth/login');
    }
}