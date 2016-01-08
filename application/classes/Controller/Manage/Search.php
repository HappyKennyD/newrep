<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Search extends Controller_Manage_Core
{

    public function action_index()
    {
        $search = $this->request->param('string', "");
        $id_project = $this->request->param('project_id',0);
        $c = $this->request->param('category');
        if (empty($c)) {
            if (Security::xss_clean(Arr::get($_POST, 'news', '') == 'on'))
                $c.='news_';
            if (Security::xss_clean(Arr::get($_POST, 'publication', '') == 'on'))
                $c.='publication_';
            if (Security::xss_clean(Arr::get($_POST, 'books', '') == 'on'))
                $c.='books_';
            if (Security::xss_clean(Arr::get($_POST, 'biographies', '') == 'on'))
                $c.='biographies_';
            if (Security::xss_clean(Arr::get($_POST, 'chronologies', '') == 'on'))
                $c.='chronologies_';
            if (Security::xss_clean(Arr::get($_POST, 'photo', '') == 'on'))
                $c.='photo_';
            if (Security::xss_clean(Arr::get($_POST, 'infographics', '') == 'on'))
                $c.='infographics_';
            if (Security::xss_clean(Arr::get($_POST, 'video', '') == 'on'))
                $c.='video_';
            if (Security::xss_clean(Arr::get($_POST, 'interactive', '') == 'on'))
                $c.='interactive_';
            $c = substr($c,0,strlen($c)-1);
            if (empty($c)) {
                $c = 'all';
            }
            if ($c == 'news_publication_books_biographies_chronologies_photo_infographics_video_interactive'){
                $c = 'all';
            }
        }

        $search = Security::xss_clean(mysql_real_escape_string($search));



        if (empty($search)) {
            $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
            if (!empty($search)) {
                $this->redirect('/manage/search/'.$id_project.'/'.$c.'/'. $search, 301);
            }
        }

        $this->set('search', $search);
        $search = Database::instance()->escape($search);

        $images = Array();
        $audiorow = Array();
        $i = 0;
        $k = 0;

        // audio
        // Заголовки аудиозаписей (audio)
        if ($c == 'all' || strpos($c,'photo')!==false)
        {

            $sql = "SELECT DISTINCT photosets.*,
                  MATCH (name_$this->language) AGAINST ($search IN BOOLEAN MODE) AS relevance
                  FROM photosets
                  WHERE MATCH (name_$this->language) AGAINST ($search IN BOOLEAN MODE)
                  AND published = '1'
                  ORDER BY relevance DESC";
            $photos = DB::query(Database::SELECT, $sql)->as_object()->execute();
            $ids_photos = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','=','photosets')->find_all()->as_array(NULL ,'material_id');

            foreach ($photos as $photo) {
                if (!empty($photo->{'name_' . $this->language}) ) {
                    if (in_array($photo->id, $ids_photos)){
                    $results[$i] = array('type' => 'photo', 'id' => $photo->id,
                        'title' => $photo->{'name_' . $this->language},
                        'desc' => $photo->location,
                        'inc'=> 'true',
                        'controller' => 'photosets',
                        'relevance' => $photo->relevance
                    );

                    $i++;
                }
                    else{
                        $results[$i] = array('type' => 'photo', 'id' => $photo->id,
                            'title' => $photo->{'name_' . $this->language},
                            'desc' => $photo->location,
                            'inc'=> 'false',
                            'controller' => 'photosets',
                            'relevance' => $photo->relevance
                        );

                        $i++;
                    }

                }
            }

        }

        //video
        //Заголовок, описание и язык(video)
        if ($c == 'all' || strpos($c,'video')!==false)
        {
            $sql = "SELECT DISTINCT video.*,
                  MATCH (title) AGAINST ($search IN BOOLEAN MODE) AS relevance
                  FROM video
                  WHERE MATCH (title) AGAINST ($search IN BOOLEAN MODE)
                  AND published = '1'
                  AND language = '$this->language'
                  ORDER BY relevance DESC";
            /*$sql = "SELECT DISTINCT video.*,
                  MATCH (title) AGAINST ($search) AS relevance
                  FROM video
                  WHERE MATCH (title) AGAINST ($search)
                  AND published = '1'
                  AND language = '$this->language'
                  ORDER BY relevance DESC";*/
            $videos = DB::query(Database::SELECT, $sql)->as_object()->execute();
            $ids_video = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','=','video')->find_all()->as_array(NULL ,'material_id');

            foreach ($videos as $video) {
                if ( !empty($video->{'title'}) ) {
                    if (in_array($video->id, $ids_video)){
                    $results[$i] = array('type' => 'video',
                        'id' => $video->id,
                        'title' => $video->title,
                        'inc'=>'true',
                        'desc' => $video->description,
                        'controller' => 'video',
                        'relevance' => $video->relevance);
                    $i++;
                    }
                    else{
                        $results[$i] = array('type' => 'video',
                            'id' => $video->id,
                            'title' => $video->title,
                            'inc'=>'false',
                            'desc' => $video->description,
                            'controller' => 'video',
                            'relevance' => $video->relevance);
                        $i++;
                    }
                }
            }
        }

        // biography
        // Заголовки, описание и текст биографий (biography)
        if ($c == 'all' || strpos($c,'biographies')!==false)
        {
            $sql = "SELECT DISTINCT biography.*,
                  MATCH (name_$this->language, desc_$this->language, text_$this->language) AGAINST ($search IN BOOLEAN MODE) AS relevance
                  FROM biography
                  WHERE MATCH (name_$this->language, desc_$this->language, text_$this->language) AGAINST ($search IN BOOLEAN MODE)
                  AND published = '1'
                  ORDER BY relevance DESC";
            $bios = DB::query(Database::SELECT, $sql)->as_object()->execute();
            $ids_bios = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','=','biography')->find_all()->as_array(NULL ,'material_id');

            foreach ($bios as $bio) {
                if (!empty($bio->{'name_' . $this->language}) AND !empty($bio->{'desc_' . $this->language})) {
                    if (in_array($bio->id, $ids_bios))
                    {
                    $results[$i] = array('type' => 'bio',
                        'id' => $bio->id,
                        'inc' => 'true',
                        'title' => $bio->{'name_' . $this->language},
                        'desc' => $bio->{'desc_' . $this->language},
                        'controller' => 'biography',
                        'relevance' => $bio->relevance);
                    if ($bio->image != 0) {
                        $results[$i]['image'] = FileData::filepath($bio->image);
                    }
                    $i++;
                    }
                    else
                    {
                        $results[$i] = array('type' => 'bio',
                            'id' => $bio->id,
                            'inc'=> 'false',
                            'title' => $bio->{'name_' . $this->language},
                            'desc' => $bio->{'desc_' . $this->language},
                            'controller' => 'biography',
                            'relevance' => $bio->relevance);
                        if ($bio->image != 0) {
                            $results[$i]['image'] = FileData::filepath($bio->image);
                        }
                        $i++;
                    }
                }
            }
        }

        //Заголовки, описание и текст статей (publications)
        if ($c == 'all' || strpos($c,'news')!==false)
        {



            //Заголовки, описание и текст страниц (pages_contents)
            $sql = "SELECT DISTINCT pages_contents.*,
                              MATCH (title_$this->language, description_$this->language, text_$this->language) AGAINST ($search IN BOOLEAN MODE) AS relevance
                              FROM pages_contents
                              WHERE MATCH (title_$this->language, description_$this->language, text_$this->language) AGAINST ($search IN BOOLEAN MODE)
                              AND published = '1'
                              ORDER BY relevance DESC";
            /*$sql = "SELECT DISTINCT pages_contents.*,
                  MATCH (title_$this->language, description_$this->language, text_$this->language) AGAINST ($search) AS relevance
                  FROM pages_contents
                  WHERE MATCH (title_$this->language, description_$this->language, text_$this->language) AGAINST ($search)
                  AND published = '1'
                  ORDER BY relevance DESC";*/
            $pages = DB::query(Database::SELECT, $sql)->as_object()->execute();
            $ids_news = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','IN',array('contents'))->find_all()->as_array(NULL ,'material_id');

            foreach ($pages as $page) {
                if (!empty($page->{'title_' . $this->language}) AND !empty($page->{'description_' . $this->language})) {
                    if (in_array($page->id, $ids_news)){
                    $results[$i] = array('type' => 'page',
                        'id' => $page->id,
                        'title' => $page->{'title_' . $this->language},
                        'desc' => $page->{'description_' . $this->language},
                        'controller' => 'contents',
                        'inc' => 'true',
                        'relevance' => $page->relevance);
                    if ($page->image != 0) {
                        $results[$i]['image'] = FileData::filepath($page->image);
                    }
                    $i++;}
                    else{
                        $results[$i] = array('type' => 'page',
                            'id' => $page->id,
                            'title' => $page->{'title_' . $this->language},
                            'desc' => $page->{'description_' . $this->language},
                            'controller' => 'contents',
                            'inc' => 'false',
                            'relevance' => $page->relevance);
                        if ($page->image != 0) {
                            $results[$i]['image'] = FileData::filepath($page->image);
                        }
                        $i++;
                    }
                }
            }
        }



        //Заголовки книг, авторы (books)
        if ($c == 'all' || strpos($c,'books')!==false)
        {
            $sql = "SELECT DISTINCT books.*,
                  MATCH (title, author, description) AGAINST ($search IN BOOLEAN MODE) AS relevance
                  FROM books
                  WHERE MATCH (title, author, description) AGAINST ($search IN BOOLEAN MODE)
                  AND published = '1'
                  AND show_$this->language = '1'
                  ORDER BY relevance DESC";
            /*$sql = "SELECT DISTINCT books.*,
                  MATCH (title, author, description) AGAINST ($search) AS relevance
                  FROM books
                  WHERE MATCH (title, author, description) AGAINST ($search)
                  AND published = '1'
                  AND show_$this->language = '1'
                  ORDER BY relevance DESC";*/
            $books = DB::query(Database::SELECT, $sql)->as_object()->execute();
            $ids_books = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','=','books')->find_all()->as_array(NULL ,'material_id');

            foreach ($books as $book) {
                if (in_array($book->id, $ids_books)){
                $book->description = strip_tags($book->description);
                $desc = preg_replace('[<br/>|<br />|<p>]', ' ', $book->description);
                $desc = mb_substr(htmlspecialchars(Security::xss_clean(trim(strip_tags($desc)))), 0, 200);
                $last = strripos($desc, ' ');
                $desc = substr($desc, 0, $last + 1);
                $desc .= '...';
                $results[$i] = array('type' => 'book', 'id' => $book->id, 'inc' => 'true', 'title' => $book->title, 'desc' => $desc, 'controller' => 'books', 'relevance' => $book->relevance);
                $i++;
                }
                else
                {
                    $book->description = strip_tags($book->description);
                    $desc = preg_replace('[<br/>|<br />|<p>]', ' ', $book->description);
                    $desc = mb_substr(htmlspecialchars(Security::xss_clean(trim(strip_tags($desc)))), 0, 200);
                    $last = strripos($desc, ' ');
                    $desc = substr($desc, 0, $last + 1);
                    $desc .= '...';
                    $results[$i] = array('type' => 'book', 'id' => $book->id, 'inc' => 'false', 'title' => $book->title, 'desc' => $desc, 'controller' => 'books', 'relevance' => $book->relevance);
                    $i++;
                }
            }
        }

        //Заголовки, описание и текст новостей (news)
        if ($c == 'all' || strpos($c,'news')!==false)
        {

            $sql = "SELECT DISTINCT publications.*,
                  MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search IN BOOLEAN MODE) AS relevance
                  FROM publications
                  WHERE MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search IN BOOLEAN MODE)
                  AND published = '1'
                  ORDER BY relevance DESC";
            $pages = DB::query(Database::SELECT, $sql)->as_object()->execute();
            $ids_news = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','IN',array('publications'))->find_all()->as_array(NULL ,'material_id');

            foreach ($pages as $page) {
                if (!empty($page->{'title_' . $this->language}) AND !empty($page->{'desc_' . $this->language})) {

                    if (in_array($page->id, $ids_news)){
                    $results[$i] = array('type' => 'news', 'id' => $page->id,
                        'title' => $page->{'title_' . $this->language},
                        'desc' => $page->{'desc_' . $this->language},
                        'controller' => 'publications',
                        'inc' => 'true',
                        'relevance' => $page->relevance
                    );
                    if ($page->image != 0) {
                        $results[$i]['image'] = FileData::filepath($page->image);
                    }
                    $i++;
                    }
                    else{
                        $results[$i] = array('type' => 'news', 'id' => $page->id,
                            'title' => $page->{'title_' . $this->language},
                            'desc' => $page->{'desc_' . $this->language},
                            'controller' => 'publications',
                            'inc' => 'false',
                            'relevance' => $page->relevance
                        );
                        if ($page->image != 0) {
                            $results[$i]['image'] = FileData::filepath($page->image);
                        }
                        $i++;
                    }

                }
            }


            $sql = "SELECT DISTINCT news.*,
                  MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search IN BOOLEAN MODE) AS relevance
                  FROM news
                  WHERE MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search IN BOOLEAN MODE)
                  AND published = '1'
                  ORDER BY relevance DESC";
            /*$sql = "SELECT DISTINCT news.*,
                  MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search) AS relevance
                  FROM news
                  WHERE MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search)
                  AND published = '1'
                  ORDER BY relevance DESC";*/
            $ids_news = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','IN',array('publications','contents'))->find_all()->as_array(NULL ,'material_id');

            $news = DB::query(Database::SELECT, $sql)->as_object()->execute();
            foreach ($news as $page) {
                if (!empty($page->{'title_' . $this->language}) AND !empty($page->{'desc_' . $this->language})) {
                    if (in_array($page->id, $ids_news)){
                    $results[$i] = array('type' => 'news', 'id' => $page->id,
                        'title' => $page->{'title_' . $this->language},
                        'desc' => $page->{'desc_' . $this->language},
                        'controller' => 'news',
                        'inc' => 'true',
                        'relevance' => $page->relevance);
                    if ($page->image != 0) {
                        $results[$i]['image'] = FileData::filepath($page->image);
                    }
                    $i++;}
                    else{
                        $results[$i] = array('type' => 'news', 'id' => $page->id,
                            'title' => $page->{'title_' . $this->language},
                            'desc' => $page->{'desc_' . $this->language},
                            'controller' => 'news',
                            'inc' => 'false',
                            'relevance' => $page->relevance);
                        if ($page->image != 0) {
                            $results[$i]['image'] = FileData::filepath($page->image);
                        }
                        $i++;
                    }

                }
            }
        }



        //Заголовки, описание и текст infographics (infographics)
        if ($c == 'all' || strpos($c,'infographics')!==false)
        {
            $sql = "SELECT DISTINCT infographs.*,
                  MATCH (title_$this->language) AGAINST ($search IN BOOLEAN MODE) AS relevance
                  FROM infographs
                  WHERE MATCH (title_$this->language) AGAINST ($search IN BOOLEAN MODE)
                  AND published = '1'
                  ORDER BY relevance DESC";
            /*$sql = "SELECT DISTINCT calendar.*,
                  MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search) AS relevance
                  FROM calendar
                  WHERE MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search)
                  AND published = '1'
                  ORDER BY relevance DESC";*/
            $pages = DB::query(Database::SELECT, $sql)->as_object()->execute();
            $ids_inf = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','=','infographics')->find_all()->as_array(NULL ,'material_id');

            foreach ($pages as $page) {
                if (!empty($page->{'title_' . $this->language})) {
                    if (in_array($page->id, $ids_inf))
                    {
                    $results[$i] = array('type' => 'news',
                        'id' => $page->id,
                        'inc' => 'true',
                        'title' => $page->{'title_' . $this->language},
                        'desc' => $page->{'title_' . $this->language},
                        'controller' => 'infographics',
                        'relevance' => $page->relevance
                    );
                    if ($page->image != 0) {
                        $results[$i]['image'] = FileData::filepath($page->image);
                    }
                    $i++;
                    }
                    else{
                        $results[$i] = array('type' => 'news',
                            'id' => $page->id,
                            'inc' => 'false',
                            'title' => $page->{'title_' . $this->language},
                            'desc' => $page->{'title_' . $this->language},
                            'controller' => 'infographics',
                            'relevance' => $page->relevance
                        );
                        if ($page->image != 0) {
                            $results[$i]['image'] = FileData::filepath($page->image);
                        }
                        $i++;
                    }
                }
            }
        }

        if(!empty($results)){
            usort($results, function ($a, $b) {
                if ($a['relevance'] == $b['relevance']) return 0;
                return $a['relevance'] < $b['relevance'] ? 1 : -1;
            });
            $old_results = $results;
            $results = new Massiv();
            $i = 0;
            foreach ($old_results as $result) {
                $results[$i] = array('type' => $result['type'],
                    'id' => $result['id'],
                    'title' => $result['title'],
                    'desc' => $result['desc'],
                    'inc' => $result['inc'],
                    'controller' => $result['controller'],
                    'relevance' => $result['relevance']);
                if (isset($result['image'])) {
                    $images[$i] = $result['image'];
                }
                $i++;
            }
        } else {
            $results = new Massiv();
        }

        $images = array_unique($images);

        $result = clone $results;
        $this->set('c', $c);
        $this->set('results', $results);
        $this->set('result', $result);
        $this->set('images', $images);
        $this->set('audiorow', $audiorow);
        $this->set('first_key', key($images));
        $this->set('max_key', sizeof($images));
        $this->set('total', sizeof($results));
        $paginate = Paginatemain::factory($results)->paginate()->render();
        $this->set('paginate', $paginate);

        $category = ORM::factory('Project')->where('published', '=', '1')->find_all();
        $this->set('category', $category);

        $this->set('id_project', $id_project);
    }

    public function action_add()
    {

                $id = $this->request->param('material_id', 0);
                $id_project = $this->request->param('project_id',0);
                $article = ORM::factory('Material_Project');

                $article->material_id = $id;
                $article->project_id = $id_project;


                $article->save();
                //$this->redirect('/manage/search/'.$id_project, 301);

                Message::success(i18n::get('The material sent to the moderator. Thank you!'));


    }
}
