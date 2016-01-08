<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Smartindex extends Controller_Api_Core
{

    public function action_index()
    {
		header('Access-Control-Allow-Origin: *');
        $photosets = ORM::factory('Photoset')->where('published','=',1)->where('is_important','=',1)->where('show_'.$this->language, '=', 1)->order_by('date', 'DESC')->limit(1)->find_all();

        if (count($photosets)<1)
        {
            $this->data['error'] = 'Publication not found';
        }

        foreach ($photosets as $k=>$v)
        {

            $this->data['lastphoto']['id'] = $v->id;
            $this->data['lastphoto']['name'] = $v->name;
            $this->data['lastphoto']['date'] = $v->date;
            $attach = $v->attach->where('main', '=', '1')->find();
            $this->data['lastphoto']['file_path'] = 'http://'.$_SERVER['HTTP_HOST'].URL::media('/images/w505-h680/'.$attach->photo->file_path);
        }

        $publications = ORM::factory('Publication')->where('title_' . I18n::$lang,'<>','')->where('published', '=', 1)->and_where('is_slider', '=', 1)->order_by('order', 'desc')->limit(3)->find_all();
        $pub = array();

        foreach ($publications as $k=>$v) {
            $coverUrl = '';
            if (!empty($v->picture->file_path)) {
                $coverUrl = 'http://'.$_SERVER['HTTP_HOST'].URL::media('/images/w333-h214/'.$v->picture->file_path);
            }
            $pub['id'] = $v->id;
            $pub['url'] = 'http://'.$_SERVER['HTTP_HOST'].URL::site('api/smartpublications/view/'.$v->id);
            $pub['title'] = $v->title;
            $pub['desc'] = strip_tags($v->desc);
            $pub['coverUrl'] = $coverUrl;
            $this->data['publications'][] = $pub;
        }

        $this->response->body(json_encode($this->data));
    }
}
