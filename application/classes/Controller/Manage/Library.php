<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Library extends Controller_Manage_Core
{
    public function action_index()
    {
        $type1= (int) Arr::get($_GET, 'type', 1);
        $cat = (int) Arr::get($_GET, 'category', 0);
        $books = ORM::factory('Book')->order_by('id', 'desc');

        if ($cat>0)
        {
            $books->and_where('category_id', '=', $cat);
        }

        if ($cat==0 and $type1==0)
        {
            $books->and_where('category_id', '=', $cat);

        }

        $paginate = Paginate::factory($books)->paginate(NULL, NULL, 10)->render();
        $books = $books->find_all();
        $this->set('books', $books)->set('paginate', $paginate);

        $cats = ORM::factory('Library_Category')->fulltree;
        $this->set('cats', $cats);
        $this->set('type',$type1);

        if ($cat > 0)
        {
            $cat = ORM::factory('Library_Category', $cat);
            if ($cat->loaded())
            {
                $this->set('cat', $cat);
            }
        }
    }

    public function action_show_ru()
    {
        $id = $this->request->param('id', 0);
        $book = ORM::factory('Book', $id);
        if ( !$book->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $book->show_ru = $book->show_ru == 0 ? 1 : 0;
        $book->save();
        $this->redirect($this->referrer);
    }

    public function action_show_kz()
    {
        $id = $this->request->param('id', 0);
        $book = ORM::factory('Book', $id);
        if ( !$book->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $book->show_kz = $book->show_kz == 0 ? 1 : 0;
        $book->save();
        $this->redirect($this->referrer);
    }

    public function action_show_en()
    {
        $id = $this->request->param('id', 0);
        $book = ORM::factory('Book', $id);
        if ( !$book->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $book->show_en = $book->show_en == 0 ? 1 : 0;
        $book->save();
        $this->redirect($this->referrer);
    }

    public function action_book()
    {
        $id = (int) $this->request->param('id', 0);
        $book = ORM::factory('Book', $id);
        if (!$book->loaded())
        {
            throw new HTTP_Exception_404;
        }

        $this->set('book', $book);
    }

    public function action_show()
    {
        $id = (int) $this->request->param('id', 0);
        $book = ORM::factory('Book', $id);
        if (!$book->loaded())
        {
            throw new HTTP_Exception_404;
        }

        $this->set('book', $book);
    }

    public function action_important()
    {
        $id = $this->request->param('id', 0);
        $book = ORM::factory('Book', $id);
        if ( !$book->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $book->is_important )
        {
            $book->is_important = 0;
            $book->save();
            Message::success(I18n::get('Book removed from main'));
        }
        else
        {
            $book->is_important = 1;
            $book->save();
            Message::success(I18n::get('Book on main'));
        }
        $this->redirect($this->referrer);
    }

    public function action_editbook()
    {
        //die('lol');

        $id = (int) $this->request->param('id', 0);
        $book = ORM::factory('Book', $id);
        $selected = (int) Arr::get($_GET, 'category', 0);
        $this->set('selected', $selected);
        $this->set('book', $book);
        $cats = ORM::factory('Library_Category')->fulltree;
        $this->set('cats', $cats);

        $uploader = View::factory('storage/pdf')->set('user_id', $this->user->id)->render();
        $this->set('uploader', $uploader);

        $pdf = $book->pdf;
        if ($pdf->loaded())
        {
            $this->set('pdf', $pdf);
        }
        else
        {
            $this->set('pdf', FALSE);
        }

        if ($this->request->method() == 'POST')
        {
            $type = Security::xss_clean(Arr::get($_POST, 'type', ''));
            $language = Security::xss_clean(Arr::get($_POST, 'language', ''));
            $subject = Security::xss_clean(Arr::get($_POST, 'subject', ''));
            $class = Security::xss_clean(Arr::get($_POST, 'class', ''));
            $title = Security::xss_clean(Arr::get($_POST, 'title', ''));
            $storage_id = (int) Arr::get($_POST, 'storage_id', 0);
            $cover_id = (int) Arr::get($_POST, 'cover_id', 0);
            $description = Security::xss_clean(Arr::get($_POST, 'description', ''));
            $category_id = (int) Arr::get($_POST, 'category_id', 0);
            $published = (int) Arr::get($_POST, 'published', 0);
            $letter = UTF8::strtoupper(UTF8::substr($title, 0 , 1));
            $author = Security::xss_clean(Arr::get($_POST, 'author', ''));
            $publisher = Security::xss_clean(Arr::get($_POST, 'publisher', ''));
            $year = (int) Arr::get($_POST, 'year', 0);
            $RandomNum = time();
            $i = 0;
           // $post = $this->request->post();
           // $post['date'] = date('Y-m-d H:i:s',strtotime($post['date']));
          //$date =  $post['date'];
            $message = "";


            try
            {
                $book->file_path = $RandomNum;
                $book->kol = $i;
                $book->type = $type;
                $book->language = $language;
                $book->subject = $subject;
                $book->class = $class;
                $book->title = $title;
                $book->storage_id = $storage_id;
                $book->cover_id = $cover_id;
                $book->description = $description;
                $book->category_id = $category_id;
                $book->published = $published;
                $book->author = $author;
                $book->year = $year;
                $book->publisher = $publisher;
                $book->letter = $letter;
                $book->save();
                if($_FILES) {
                    $output_dir = "media/upload/" . $RandomNum . "/";
                    mkdir($output_dir, 0777);
                    if (isset($_FILES["myfile"])) {

                        $ImageName = str_replace(' ', '-', strtolower($_FILES['myfile']['name']));
                        $ImageType = $_FILES['myfile']['type']; //"image/png", image/jpeg etc.

                        $ImageExt = substr($ImageName, strrpos($ImageName, '.'));
                        $ImageExt = str_replace('.', '', $ImageExt);
                        if ($ImageExt != "pdf") {
                            $message = "Invalid file format only <b>\"PDF\"</b> allowed.";
                        }
                        else {
                            $ImageName      = preg_replace("/\.[^.\s]{3,4}$/", "", $ImageName);
                            $NewImageName = $RandomNum.'.'.$ImageExt;
                            move_uploaded_file($_FILES["myfile"]["tmp_name"], $output_dir . $NewImageName);
                            $location = "convert -density 200";
                            $name = $output_dir . $NewImageName;

                            $pdftext = file_get_contents($name);
                            $num = preg_match_all("/\/Page\W/", $pdftext, $dummy);

                            for ($i = 0; $i < $num; ++$i) {
                                $nameto = $output_dir . $RandomNum . "-" . $i . ".png";
                                $convert = $location . " " . $name . "[" . $i . "]" . " " . "-append -resize 850" . $nameto;
                                exec($convert);
                            }

                            $dir = opendir($output_dir);
                            $i = 0;
                            while (false !== ($file = readdir($dir))) {
                                if (strpos($file, '.png',1) ) {
                                    $i++;
                                }
                            }
                        }
                    }
                }
                $event = ($id)?'edit':'create';
                $loger = new Loger($event, $book->title);
                $loger->log($book);
                $this->set('RandomNum', $RandomNum);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }


        }
    }

    public function action_clearcover()
    {
        $id = (int) $this->request->param('id', 0);
        $book = ORM::factory('Book', $id);
        if (!$book->loaded())
        {
            throw new HTTP_Exception_404;
        }

        $book->cover_id = 0;
        $book->save();
        $this->redirect('manage/library/editbook/'.$book->id);
    }

    public function action_removepdf()
    {
        $id = (int) $this->request->param('id', 0);
        $book = ORM::factory('Book', $id);
        if (!$book->loaded())
        {
            throw new HTTP_Exception_404;
        }

        $book->storage_id = 0;
        $book->save();
        $this->redirect('manage/library/editbook/'.$book->id);
    }

    public function action_published()
    {
        $id = (int) $this->request->param('id', 0);
        $book = ORM::factory('Book', $id);
        if ( !$book->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $book->published )
        {
            $book->published = 0;
            $book->save();
            Message::success(I18n::get('Book hided'));
        }
        else
        {
            $book->published = 1;
            $book->save();
            Message::success(I18n::get('Book unhided'));
        }
        $this->redirect($this->referrer);
    }

    public function action_categorypublished()
    {
        $id = (int) $this->request->param('id', 0);
        $category = ORM::factory('Library_Category', $id);
        if ( !$category->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $category->published )
        {
            $category->published = 0;
            $category->save();
            Message::success(I18n::get('Category hided'));
        }
        else
        {
            $category->published = 1;
            $category->save();
            Message::success(I18n::get('Category unhided'));
        }
        $this->redirect('manage/library/category');
    }

    public function action_delete()
    {
        $id = (int) $this->request->param('id', 0);
        $book = ORM::factory('Book', $id);
        if (!$book->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $loger = new Loger('delete', $book->title);
            $loger->log($book);

            $book->delete();

            Message::success(I18n::get('Record deleted'));
            $this->redirect('manage/library');
        }
        else
        {
            $this->set('record', $book)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/library'));
        }
    }

    public function action_category()
    {
        $cats = ORM::factory('Library_Category')->fulltree;
        $this->set('cats', $cats);
    }

    public function action_newcategory()
    {
        $id = (int) $this->request->param('id', 0);

        $parent_category = ORM::factory('Library_Category', $id);

        if (!$parent_category->loaded())
        {
            $this->set('newroot', TRUE);
        }

        if( $post = $this->request->post() )
        {
            $category = ORM::factory('Library_Category');
            $category->name = Security::xss_clean(Arr::get($post,'name',''));
            $category->key = Security::xss_clean(Arr::get($post,'key',''));

            if ($parent_category->loaded())
            {
                $category->insert_as_last_child($parent_category);
            }
            else
            {
                $category->make_root();
            }
            $category->save();

            Message::success(I18n::get('Category added'));
            $this->redirect('manage/library/category');
        }
        $this->set('cancel_url', Url::media('manage/library/category'));
    }

    public function action_editcategory()
    {
        $id = (int) $this->request->param('id', 0);
        $category = ORM::factory('Library_Category', $id);
        if ( !$category->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if( $post = $this->request->post() )
        {
            $category->name = Security::xss_clean(Arr::get($post,'name',''));
            $category->key = Security::xss_clean(Arr::get($post,'key',''));
            $category->save();
            Message::success(I18n::get('Category changed'));
            $this->redirect('manage/library/category');
        }
        $this->set('category', $category)->set('cancel_url', Url::media('manage/library/category'));
    }

    public function action_upcategory()
    {
        $id = (int)$this->request->param('id', 0);
        $category = ORM::factory('Library_Category', $id);
        if (!$category->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $up_brother = ORM::factory('Library_Category')
            ->where('parent_id','=', $category->parent_id)
            ->and_where('rgt','=', $category->lft - 1)
            ->find();
        if (empty($up_brother->id))
        {
            Message::warn(I18n::get("Category in the top"));
            $this->redirect('manage/library/category');
        }
        $category->move_to_prev_sibling($up_brother);
        Message::success(I18n::get('Category moved up'));
        $this->redirect('manage/library/category');
    }

    public function action_downcategory()
    {
        $id = (int)$this->request->param('id', 0);
        $category = ORM::factory('Library_Category', $id);
        if (!$category->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $down_brother = ORM::factory('Library_Category')
            ->where('parent_id','=', $category->parent_id)
            ->and_where('lft','=', $category->rgt + 1)
            ->find();
        if (empty($down_brother->id))
        {
            Message::warn(I18n::get("Category in the down"));
            $this->redirect('manage/library/category');
        }
        $category->move_to_next_sibling($down_brother);
        Message::success(I18n::get('Category moved down'));
        $this->redirect('manage/library/category');
    }

    public function action_deletecategory()
    {
        $id = (int)$this->request->param('id', 0);
        $category = ORM::factory('Library_Category', $id);
        if ( !$category->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {

            $category->delete();
            Message::success(I18n::get("Category deleted"));
            $this->redirect('manage/library/category');
        }
        else
        {
            $this->set('record', $category)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/library/category'));
        }
    }

    public function action_chapters()
    {
        $id = (int) $this->request->param('id', 0);
        $book = ORM::factory('Book', $id);
        if ( !$book->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $chapters = $book->chapters->order_by('number')->find_all();
        $this->set('book', $book)->set('chapters', $chapters);
    }

    public function action_editchapter()
    {
        $id = (int) $this->request->param('id', 0);
        $chapter = ORM::factory('Book_Chapter', $id);
        $book_id = (int) Arr::get($_GET, 'book', 0);
        if ($book_id==0 and $chapter->loaded())
        {
            $book_id = $chapter->book_id;
        }
        $book = ORM::factory('Book', $book_id);
        if ( !$book->loaded() )
        {
            throw new HTTP_Exception_404;
        }

        $this->set('book', $book)->set('chapter', $chapter);

        if ($this->request->method() == 'POST')
        {
            $title = Security::xss_clean(Arr::get($_POST, 'title', ''));
            $text = Security::xss_clean(Arr::get($_POST, 'text', ''));
            $published = (int) Arr::get($_POST, 'published', 0);
            $number = (int) Arr::get($_POST, 'number', 0);

            try
            {
                $chapter->title = $title;
                $chapter->text = $text;
                $chapter->published = $published;
                $chapter->number = $number;
                $chapter->book_id = $book_id;
                $chapter->save();
                $this->redirect('manage/library/chapters/'.$book->id);
            }
            catch (ORM_Validation_Exception $e)
            {
                $errors = $e->errors($e->alias());
                $this->set('errors',$errors);
            }
        }
    }

    public function action_deletechapter()
    {
        $id = (int) $this->request->param('id', 0);
        $chapter = ORM::factory('Book_Chapter', $id);
        if (!$chapter->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $token = Arr::get($_POST, 'token', false);
        if (($this->request->method() == Request::POST) && Security::token() === $token)
        {
            $book_id = $chapter->book_id;
            $chapter->delete();
            Message::success(I18n::get('Record deleted'));
            $this->redirect('manage/library/chapters/'.$book_id);
        }
        else
        {
            $this->set('record', $chapter)->set('token', Security::token(true))->set('cancel_url', Url::media('manage/library/chapters/'.$chapter->book_id));
        }
    }

    public function action_showchapter()
    {
        $id = (int) $this->request->param('id', 0);
        $chapter = ORM::factory('Book_Chapter', $id);
        if ( !$chapter->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        if ( $chapter->published )
        {
            $chapter->published = 0;
            $chapter->save();
            Message::success(I18n::get('Record hided'));
        }
        else
        {
            $chapter->published = 1;
            $chapter->save();
            Message::success(I18n::get('Record unhided'));
        }
        $this->redirect('manage/library/chapters/'.$chapter->book_id);
    }

    public function action_pluschapter()
    {
        $id = (int) $this->request->param('id', 0);
        $item = ORM::factory('Book_Chapter', $id);
        if ( !$item->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $item->number = $item->number + 1;
        $item->save();
        $this->redirect('manage/library/chapters/'.$item->book_id);
    }

    public function action_minuschapter()
    {
        $id = (int) $this->request->param('id', 0);
        $item = ORM::factory('Book_Chapter', $id);
        if ( !$item->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $item->number = $item->number - 1;
        $item->save();
        $this->redirect('manage/library/chapters/'.$item->book_id);
    }

    public function action_viewchapter()
    {
        $id = (int) $this->request->param('id', 0);
        $chapter = ORM::factory('Book_Chapter', $id);
        if ( !$chapter->loaded() )
        {
            throw new HTTP_Exception_404;
        }
        $this->set('chapter', $chapter);
    }
    public function action_addsub()
    {
        if($this->request->is_ajax()){
            $book_id = (int) Arr::get($_POST, 'book_id', 0);
            $category_id = (int) Arr::get($_POST, 'category_id', 0);
            $subs = ORM::factory('Book_Subcategory');
            $subs->book_id=$book_id;
            $subs->category_id=$category_id;
            $subs->save();
        }
    }
    public function action_showsub()
    {
        $id = (int) $this->request->param('id', 0);
        $subs = ORM::factory('Book_Subcategory')->where('book_id','=',$id)->find_all();
        $all_subs=array();
        foreach( $subs as $sub)
        {
           $all_subs[]=array('name'=>implode(" - ", ORM::factory('Library_Category',$sub->category_id)->tree()),'book_id'=>$id,'category_id'=>$sub->category_id);
        }
        $this->set('all', $all_subs);
    }
    public function action_delsub()
    {
        if($this->request->is_ajax()){
            $book_id = (int) Arr::get($_POST, 'book_id', 0);
            $category_id = (int) Arr::get($_POST, 'category_id', 0);
            $subs = ORM::factory('Book_Subcategory')->where('book_id','=',$book_id)->and_where('category_id','=',$category_id)->find();
            $subs->delete();
        }
    }
}
