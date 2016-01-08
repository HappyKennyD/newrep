<?php
class Controller_Core extends Controller_Kotwig
{
    protected $language;

    /** @var Model_Auth_User */
    protected $user = false;
    /**
     * @var Model_Metadata
     */
    protected $metadata;

    protected function detect_language()
    {
        $this->language = mb_strtolower((string)$this->request->param('language', false));
        if (!$this->language)
        {
            $rr = Request::initial()->uri();
            $rr = trim($rr, '/');
            $rr = explode('/', $rr);
            if (in_array($rr[0], Application::instance()->get('language.list'))) { array_shift($rr); }
            $rr = Application::instance()->get('language.default').'/'.implode('/', $rr);
            $this->redirect($rr);

        }
        I18n::lang($this->language);

        return $this->language;
    }

    public function before()
    {
        parent::before();

        // detecting language, setting it
        $this->detect_language();
        $this->set('_language', $this->language);

        // creating and attaching page metadata
        $this->metadata = new Model_Metadata();
        $this->metadata->title(__(Application::instance()->get('title')), false);
        $this->set('_metadata', $this->metadata);

        //TODO: token auth
        /*
        if ($this->request->method() == Request::POST && Arr::get($_POST, 'token', '') !== Security::token())
        {
            throw new HTTP_Exception_403('Wrong token data');
        }
         */
        $this->set('_token', Security::token());

        // Handles return urls, cropping language out of it (will be appended by url.site at redirect time)
        $rr = Request::initial()->uri();
        $rr = trim($rr, '/');
        $rr = explode('/', $rr);
        if (in_array($rr[0], Application::instance()->get('language.list'))) { array_shift($rr); }
        $rr = implode('/', $rr);
        $this->set('_return', $rr);

        // detecting if user is logged in
        if (method_exists(Auth::instance(), 'auto_login'))
        {
            Auth::instance()->auto_login();
        }
        $this->user = Auth::instance()->get_user();
        $this->set('_user', $this->user);
    }
}