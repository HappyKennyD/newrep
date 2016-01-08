<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Exhibits extends Controller_Core {

	public function action_index()
	{

        $exhibits = ORM::factory('Exhibit')->where('published','=',1);
        $paginate = Paginate::factory($exhibits)->paginate(null,null,12)->render();
        $this->set('albums', $exhibits->find_all())->set('paginate',$paginate);
	}

    public function action_album()
    {

        $id = (int)$this->request->param('id',0);
        $exhibit = ORM::factory('Exhibit',$id);
        $albums = ORM::factory('Exhibit')->find_all();
        if (!$exhibit->loaded()){
            throw new HTTP_Exception_404;
        }
        $this->set('exhibit',$exhibit)->set('albums',$albums);
    }

    public function action_view()
    {
        $id = (int)$this->request->param('id',0);
        $exhibit = ORM::factory('Exhibit_Album',$id);
        if (!$exhibit->loaded()){
            throw new HTTP_Exception_404;
        }
        $this->set('item',$exhibit);
    }

    public function action_gallery()
    {

    }
}
