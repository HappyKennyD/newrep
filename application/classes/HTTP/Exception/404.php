<?php
class HTTP_Exception_404 extends Kohana_HTTP_Exception_404
{
    protected $language;

    protected function detect_language()
    {
        $this->language = mb_strtolower((string)Request::initial()->param('language', false));
        if (!$this->language) {
            $this->language='ru';
        }
        I18n::lang($this->language);
        URL::$language = $this->language;
        return $this->language;
    }

    public function get_response()
    {
        $this->user = Auth::instance()->get_user();
        $this->detect_language();
        $response = Response::factory();
        $view = Kotwig_View::factory('errors/404');
        $response->status(404);

        if($this->getMessage())
        {
            $view->set('no_translation', $this->getMessage());
        }
        $response->body($view->render());
        return $response;
    }
}