<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Links extends Controller_Core {

	public function action_index()
	{
        $links = ORM::factory('Link')->order_by('order');
        $paginate = Paginate::factory($links)->paginate(NULL, NULL, 10)->render();
        $links = $links->find_all();
        $this->add_cumb('Useful links','/');
        $this->set('links', $links);
        $this->set('paginate', $paginate);
	}

}
