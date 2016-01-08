<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Faststart extends Controller_Core {

    public function action_index()
    {
        $sql = "SELECT * FROM books";

        $item = DB::query(Database::SELECT, $sql)->as_object()->execute();

        $this->set('books', $item);
    }


    public function action_view()
    {
        $id = (int) $this->request->param('id', 0);
        $user = $this->user;
        $this->set('user', $user);
        $sql = "SELECT * FROM books WHERE id=".$id;
        $item = DB::query(Database::SELECT, $sql)->as_object()->execute();
        $this->set('books', $item);

        $sql = "SELECT * FROM books ORDER BY RAND() LIMIT 3";
        $it = DB::query(Database::SELECT, $sql)->as_object()->execute();
        $this->set('books2', $it);

        $news = ORM::factory('Book', $id);
        if (!$news->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $path = $news->file_path;
        $file_path = 'media/upload/'.$path.'/';
        $dir = opendir($file_path);
        $i = 0;
        while (false !== ($file = readdir($dir))) {
            if (strpos($file, '.png',1) ) {
                $i++;
            }
        }
        $this->set('kol',  $i);

        $comments = ORM::factory('Comment')
            ->where('table', '=', 'book')
            ->and_where('status', '=', 1)
            ->group_by('object_id')
            ->order_by(Db::expr('SUM(status)'),'desc')
            ->limit(5)
            ->find_all()->as_array('id','object_id');
        if (count($comments))
        {
            $exc_url = explode('/',$_SERVER['REQUEST_URI']);
            $exc_url = $exc_url[count($exc_url)-1];
            $most_comments = ORM::factory('Book')
                ->where('id','IN', $comments)
                ->and_where('show_'.I18n::$lang,'=',1)
                ->and_where('id','<>',$exc_url)
                ->find_all();
            $this->set('most_comments', $most_comments);
        }
    }

}
