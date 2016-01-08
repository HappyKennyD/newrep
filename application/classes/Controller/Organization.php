<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Organization extends Controller_Core {

    public function action_show()
    {
        $id = (int)$this->request->param('id',0);
        $page = ORM::factory('Page',$id);
        if ( ! $page->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $page_main = ORM::factory('Page')->where('key', '=', 'institute')->find();
        $this->add_cumb($page_main->name,'');
        $this->add_cumb($page->parent->name,'');
        $this->add_cumb($page->name,'');
        $content = $page->organization;
        $people = $page->people->order_by('order','asc');
        $paginate = Paginate::factory($people)->paginate()->render();
        $people = $people->find_all();
        $this->set('people',$people)->set('paginate',$paginate)->set('content',$content);
    }

}
