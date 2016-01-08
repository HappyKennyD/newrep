<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Infographs extends Controller_Core {

	public function action_index()
	{
        $infographs = ORM::factory('Infograph')->where('published','=', 1)->and_where('language', '=', $this->language)->order_by('order','asc');
        $paginate = Paginate::factory($infographs)->paginate(NULL, NULL, 10)->render();
        $infographs = $infographs->find_all();
        $this->add_cumb('Infographics','/');
        $this->set('infographs', $infographs);
        $this->set('paginate', $paginate);
	}

    public function action_view()
    {
        $id = (int) $this->request->param('id', 0);
        $infograph = ORM::factory('Infograph', $id);
        if (!$infograph->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if (!$infograph->translation(I18n::$lang))
        {
            throw new HTTP_Exception_404('no_translation');
        }
        $this->add_cumb('Infographics','infographs');
        $this->add_cumb($infograph->title,'/');
        $this->set('infograph', $infograph);
    }

}
