<?php
class HTTP_Exception_500 extends Kohana_HTTP_Exception_500
{
    public function get_response()
    {
        $response = Response::factory();

        $view = Kotwig_View::factory('errors/500');
        $response->status(500);
        $response->body($view->render());

        return $response;
    }
}