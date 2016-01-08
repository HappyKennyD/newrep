<?php
class Controller_Manage_Language extends Controller_Manage_Core
{
    public function action_select()
    {
        $language = $this->request->param('id', 'ru');
        $language = in_array($language, array('ru', 'kz', 'en'))?$language:'ru';
        //$message = '_language before change to "'.$this->request->param('id').'": '.Session::instance()->get('_language').'<br>';
        Session::instance()->set('_language', $language);
//	phpinfo();/
//	die(Session::$default);
	//$message .= 'after: '.Session::instance()->get('_language');
	//echo $message;
        $url = Arr::get($_GET, 'r', 'manage');
        $this->redirect($url);
    }
}
