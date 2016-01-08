<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Books extends Controller_Core {

    public function action_index()
    {
        $sql = "SELECT * FROM books";

        $item = DB::query(Database::SELECT, $sql)->as_object()->execute();

        $this->set('books', $item);
    }

    public function  action_read()
    {
        $scope = Security::xss_clean($this->request->param('scope'));
        $scope_category = ORM::factory('Library_Category')->where('key', '=', $scope)->find();
        if (!$scope_category->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if ($scope_category->published == 0)
        {
            throw new HTTP_Exception_404;
        }
        $this->set('scope', $scope_category);

        $id = (int) $this->request->param('id', 0);
        $number = (int) Arr::get($_GET, 'chapter', 0);
        $book = ORM::factory('Book', $id)->where('published', '=', 1)->and_where('show_'.I18n::$lang, '=', 1);
        if (!$book->loaded())
        {
            throw new HTTP_Exception_404;
        }

        $bookprov = ORM::factory('Book', $id);
        if (!$bookprov->translation(I18n::$lang))
        {
            throw new HTTP_Exception_404('no_translation');
            //$this->redirect(URL::media(''));
        }

        $this->set('book', $book);

        $this->add_cumb($scope_category->name, 'books/'.$scope_category->key);
        $cumb = $book->title;
        if ($book->author != '')
        {
            $cumb .= '. '.$book->author;
        }
        $this->add_cumb($book->category->name, 'books/'.$scope_category->key.'?category='.$book->category->id);
        $this->add_cumb($cumb, 'books/'.$scope_category->key.'/view/'.$book->id);

        if ($book->type == 'pdf')
        {
            $this->add_cumb('View', '/');
        }

        if ($book->type == 'txt')
        {
            $chapters = $book->chapters->find_all();
            $this->add_cumb('View', 'books/'.$scope_category->key.'/read/'.$book->id.'?chapter='.$chapters[0]->number);
        }

        if ($book->type == 'txt')
        {
            $chapter = ORM::factory('Book_Chapter')->where('number', '=', $number)->and_where('book_id', '=', $book->id)->find();
            if (!$chapter->loaded())
            {
                throw new HTTP_Exception_404;
            }
            $this->set('chapter', $chapter);

            $next = ORM::factory('Book_Chapter')->where('number', '>', $chapter->number)->and_where('book_id', '=', $book->id)->limit(1)->find();
            if ($next->loaded())
            {
                $this->set('next', $next);
            }

            $this->add_cumb(I18n::get('Chapter').' '.$chapter->number.'. '.$chapter->title, '/');
        }
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

    public function save($message,$table,$object_id)
    {
        $user_id = Auth::instance()->get_user()->id;
        $comment = ORM::factory('Comment');
        try
        {
            $comment->user_id = $user_id;
            $comment->object_id = $object_id;
            $comment->table = Security::xss_clean($table);
            $comment->text = $message;
            $comment->date = date("Y:m:d H:i:s");
            $comment->save();
            return $comment;
        }
        catch(ORM_Validation_Exception $e)
        {

        }
    }

    private function goodbrowser()
    {
        $browser = 'good';

        if (UTF8::strpos($_SERVER['HTTP_USER_AGENT'], 'Opera'))
        {
            $browser = 'bad';
        }
        if (UTF8::strpos($_SERVER['HTTP_USER_AGENT'], 'OPR'))
        {
            $browser = 'bad';
        }
        if (UTF8::strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
        {
            $browser = 'bad';
        }

        if ($browser == 'good')
        {
            return TRUE;
        }
        return FALSE;
    }

    private function getLettersArray($category_id, $scope)
    {
        $books = ORM::factory('Book')
            ->join('library_categories', 'LEFT')->on('book.category_id', '=', 'library_categories.id')->select('library_categories.scope')
            ->where('book.published', '=', 1)->and_where('show_'.I18n::$lang, '=', 1)->and_where('library_categories.scope', '=', $scope);
        if ($category_id > 0)
        {
            $books = $books->and_where('category_id', '=', $category_id);
        }
        $books = $books->find_all();

        $letters_array = array();

        foreach ($books as $book)
        {
            if (!in_array($book->letter, $letters_array))
            {
                $letters_array[] = $book->letter;
            }
        }

        sort($letters_array);
        return $letters_array;
    }
}
