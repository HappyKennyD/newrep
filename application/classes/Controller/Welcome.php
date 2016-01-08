<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Controller_Core
{

    public function action_index()
    {
        $books = ORM::factory('book');
        $slogans = ORM::factory('slogan');
//        $post = $this->request->post('test');
//        $sql = " SELECT * FROM books ";



        $books = $books->find_all();
        $slogans = $slogans->find_all();
        $this->set('books', $books);
        $this->set('slogans', $slogans);
//        $this->set('books', $item);

    }

    public function action_register()
    {

    }

	public function action_test()
{
 echo Debug::vars(gd_info());
}
} // End Welcome
