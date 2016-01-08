<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search extends Controller_Core
{

    public function action_index()
    {
        $search = $this->request->param('string', "");
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
            if (Security::xss_clean(Arr::get($_POST, 'audio', '') == 'on'))
                $c.='audio_';
            if (Security::xss_clean(Arr::get($_POST, 'calendar', '') == 'on'))
                $c.='calendar_';
            if (Security::xss_clean(Arr::get($_POST, 'video', '') == 'on'))
                $c.='video_';
            if (Security::xss_clean(Arr::get($_POST, 'interactive', '') == 'on'))
                $c.='interactive_';
            $c = substr($c,0,strlen($c)-1);
            if (empty($c)) {
                $c = 'all';
            }
            if ($c == 'news_publication_books_biographies_chronologies_audio_calendar_video_interactive'){
                $c = 'all';
            }
        }

        $search = Security::xss_clean(mysql_real_escape_string($search));

        $this->add_cumb('Search', 'search');
        if (!empty($search)) {
            $this->add_cumb($search, '');
        }

        if (empty($search)) {
            $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
            if (!empty($search)) {
                $this->redirect('search/'.$c.'/'. $search, 301);
            }
        }

        $this->set('search', $search);
        $search = Database::instance()->escape($search);

        $images = Array();
        $audiorow = Array();
        $i = 0;
        $k = 0;
        /* Таблицы:
        biography (name, desc, text)
        books (title, author, description)
        calendar (title, desc, text)
        news (title, desc, text)
        pages_contents (title, description, text)
        publications (title, desc, text)

        ? audio
        + biography
        + briefings
        + books
        + calendar
         chronologies
         +debates
         ent_quests
         experts
         expert_answers
         expert_questions
         infographs
         library_categories
         links
         materials
         material_files
        + news
         organizations
         pages
        + pages_contents
         peoples
         photosets
         points
        + publications
         sliders
         spec_project_titles
         tags
         thanks
         +video
         zhuzes
        */

        // audio
        // Заголовки аудиозаписей (audio)
        if ($c == 'all' || strpos($c,'audio')!==false)
        {
            $sql = "SELECT DISTINCT audio.*,
                  MATCH (title) AGAINST ($search IN BOOLEAN MODE) AS relevance
                  FROM audio
                  WHERE MATCH (title) AGAINST ($search IN BOOLEAN MODE)
                  AND published = '1'
                  AND show_$this->language = '1'
                  ORDER BY relevance DESC";
            /*$sql = "SELECT DISTINCT audio.*,
                  MATCH (title) AGAINST ($search) AS relevance
                  FROM audio
                  WHERE MATCH (title) AGAINST ($search)
                  AND published = '1'
                  AND show_$this->language = '1'
                  ORDER BY relevance DESC";*/
//            $audios = DB::query(Database::SELECT, $sql)->as_object()->execute();
//            foreach ($audios as $audio) {
//                if ( !empty($audio->{'title'}) ) {
//                    $sql1="SELECT COUNT(file_path)
//                    FROM storages
//                    WHERE id='$audio->storage_id'";
//                    $query =DB::select()->from('storages')->where('id', '=', $audio->storage_id)->limit(1);
//
//                    $filepath = $query->execute();
//                    foreach ($filepath as $item  ){
//                    $audiorow[$k]['audio']=$item;}
//                    $this->set('audio_cat'.$k, $filepath);
//                    $k++;
//                    //$filepath = DB::query(Database::SELECT, $sql1)->limit(1)->execute();
//                    // $filepathrow = $filepath->as_array();
//                    $results[$i] = array('type' => 'audio',
//                        'id' => $audiorow[$k-1],
//                        'title' => $audio->title,
//                        'desc' => $audio->rubric,
//                        'controller' => 'audio',
//                        'relevance' => $audio->relevance);
//                    $i++;
//                }
//            }
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
            foreach ($videos as $video) {
                if ( !empty($video->{'title'}) ) {
                    $results[$i] = array('type' => 'video',
                        'id' => $video->id,
                        'title' => $video->title,
                        'desc' => $video->description,
                        'controller' => 'video',
                        'relevance' => $video->relevance);
                    $i++;
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
            foreach ($bios as $bio) {
                if (!empty($bio->{'name_' . $this->language}) AND !empty($bio->{'desc_' . $this->language})) {
                    $results[$i] = array('type' => 'bio',
                        'id' => $bio->id,
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

        //Заголовки, описание и текст статей (publications)
        if ($c == 'all' || strpos($c,'publication')!==false)
        {
            /* Разбить на 2 запроса можно, чтобы искал сначала в заголовке, но статьи дублируются
             * $sql = "SELECT DISTINCT publications.*,
                  MATCH (title_$this->language) AGAINST ($search IN BOOLEAN MODE) AS relevance
                  FROM publications
                  WHERE MATCH (title_$this->language) AGAINST ($search IN BOOLEAN MODE)
                  AND published = '1'
                  ORDER BY relevance DESC";
            $pages = DB::query(Database::SELECT, $sql)->as_object()->execute();
            foreach ($pages as $page) {
                if (!empty($page->{'title_' . $this->language}) AND !empty($page->{'desc_' . $this->language})) {
                    $results[$i] = array('type' => 'news', 'id' => $page->id,
                        'title' => $page->{'title_' . $this->language},
                        'desc' => $page->{'desc_' . $this->language},
                        'controller' => 'publications',
                        'relevance' => $page->relevance
                    );
                    if ($page->image != 0) {
                        $results[$i]['image'] = FileData::filepath($page->image);
                    }
                    $i++;
                }
            }
            $sql = "SELECT DISTINCT publications.*,
                  MATCH (desc_$this->language, text_$this->language) AGAINST ($search IN BOOLEAN MODE) AS relevance
                  FROM publications
                  WHERE MATCH (desc_$this->language, text_$this->language) AGAINST ($search IN BOOLEAN MODE)
                  AND published = '1'
                  ORDER BY relevance DESC";
            $pages = DB::query(Database::SELECT, $sql)->as_object()->execute();
            foreach ($pages as $page) {
                if (!empty($page->{'title_' . $this->language}) AND !empty($page->{'desc_' . $this->language})) {
                    $results[$i] = array('type' => 'news', 'id' => $page->id,
                        'title' => $page->{'title_' . $this->language},
                        'desc' => $page->{'desc_' . $this->language},
                        'controller' => 'publications',
                        'relevance' => $page->relevance
                    );
                    if ($page->image != 0) {
                        $results[$i]['image'] = FileData::filepath($page->image);
                    }
                    $i++;
                }
            }*/


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
            foreach ($pages as $page) {
                if (!empty($page->{'title_' . $this->language}) AND !empty($page->{'description_' . $this->language})) {
                    $results[$i] = array('type' => 'page',
                        'id' => $page->id,
                        'title' => $page->{'title_' . $this->language},
                        'desc' => $page->{'description_' . $this->language},
                        'controller' => 'contents',
                        'relevance' => $page->relevance);
                    if ($page->image != 0) {
                        $results[$i]['image'] = FileData::filepath($page->image);
                    }
                    $i++;
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
            foreach ($books as $book) {
                $book->description = strip_tags($book->description);
                $desc = preg_replace('[<br/>|<br />|<p>]', ' ', $book->description);
                $desc = mb_substr(htmlspecialchars(Security::xss_clean(trim(strip_tags($desc)))), 0, 200);
                $last = strripos($desc, ' ');
                $desc = substr($desc, 0, $last + 1);
                $desc .= '...';
                $results[$i] = array('type' => 'book', 'id' => $book->id, 'title' => $book->title, 'desc' => $desc, 'controller' => 'books', 'relevance' => $book->relevance);
                $i++;
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
            foreach ($pages as $page) {
                if (!empty($page->{'title_' . $this->language}) AND !empty($page->{'desc_' . $this->language})) {
                    $results[$i] = array('type' => 'news', 'id' => $page->id,
                        'title' => $page->{'title_' . $this->language},
                        'desc' => $page->{'desc_' . $this->language},
                        'controller' => 'publications',
                        'relevance' => $page->relevance
                    );
                    if ($page->image != 0) {
                        $results[$i]['image'] = FileData::filepath($page->image);
                    }
                    $i++;
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

            $news = DB::query(Database::SELECT, $sql)->as_object()->execute();
            foreach ($news as $page) {
                if (!empty($page->{'title_' . $this->language}) AND !empty($page->{'desc_' . $this->language})) {
                    $results[$i] = array('type' => 'news', 'id' => $page->id,
                        'title' => $page->{'title_' . $this->language},
                        'desc' => $page->{'desc_' . $this->language},
                        'controller' => 'news',
                        'relevance' => $page->relevance);
                    if ($page->image != 0) {
                        $results[$i]['image'] = FileData::filepath($page->image);
                    }
                    $i++;
                }
            }
        }

        //Поиск (interactive)
        if ($c == 'all' || strpos($c,'interactive')!==false)
        {
            $sql = "SELECT DISTINCT debates.*,
                  MATCH (title, description) AGAINST ($search IN BOOLEAN MODE) AS relevance
                  FROM debates
                  WHERE MATCH (title, description) AGAINST ($search IN BOOLEAN MODE)
                  AND language = '$this->language'
                  ORDER BY relevance DESC";
            /*$sql = "SELECT DISTINCT debates.*,
                  MATCH (title, description) AGAINST ($search) AS relevance
                  FROM debates
                  WHERE MATCH (title, description) AGAINST ($search)
                  AND language = '$this->language'
                  ORDER BY relevance DESC";*/
//            $pages = DB::query(Database::SELECT, $sql)->as_object()->execute();
//            foreach ($pages as $page) {
//                if (!empty($page->{'title'}) AND !empty($page->{'description'})) {
//                    $results[$i] = array('type' => 'debates', 'id' => $page->id,
//                        'title' => $page->{'title'},
//                        'desc' => $page->{'description'},
//                        'controller' => 'debate',
//                        'relevance' => $page->relevance
//                    );
//                    $i++;
//                }
//            }
            $sql = "SELECT DISTINCT briefings.*,
                  MATCH (title_$this->language) AGAINST ($search IN BOOLEAN MODE) AS relevance
                  FROM briefings
                  WHERE MATCH (title_$this->language) AGAINST ($search IN BOOLEAN MODE)
                  AND published = '1'
                  ORDER BY relevance DESC";
            /*$sql = "SELECT DISTINCT briefings.*,
                  MATCH (title_$this->language) AGAINST ($search) AS relevance
                  FROM briefings
                  WHERE MATCH (title_$this->language) AGAINST ($search)
                  AND published = '1'
                  ORDER BY relevance DESC";*/
//            $pages = DB::query(Database::SELECT, $sql)->as_object()->execute();
//            foreach ($pages as $page) {
//                if (!empty($page->{'title_' . $this->language}) ) {
//                    $results[$i] = array('type' => 'briefings', 'id' => $page->id,
//                        'title' => $page->{'title_' . $this->language},
//                        'desc' => $page->{'desc_' . $this->language},
//                        'controller' => 'briefings',
//                        'relevance' => $page->relevance
//                    );
//                    $i++;
//                }
//            }
            $sql = "SELECT DISTINCT ent_quests.*,
                  MATCH (text) AGAINST ($search IN BOOLEAN MODE) AS relevance
                  FROM ent_quests
                  WHERE MATCH (text) AGAINST ($search IN BOOLEAN MODE)
                  AND published = '1'
                  ORDER BY relevance DESC";
            /*$sql = "SELECT DISTINCT ent_quests.*,
                  MATCH (text) AGAINST ($search) AS relevance
                  FROM ent_quests
                  WHERE MATCH (text) AGAINST ($search)
                  AND published = '1'
                  ORDER BY relevance DESC";*/
//            $pages = DB::query(Database::SELECT, $sql)->as_object()->execute();
//            foreach ($pages as $page) {
//                if (!empty($page->{'text'}) ) {
//                    $results[$i] = array('type' => 'ent_quests', 'id' => $page->ent_id,
//                        'title' => $page->{'text'},
//                        'desc' => $page->{'text'},
//                        'ent_id' =>$page->{'ent_id'},
//                        'controller' => 'ent',
//                        'relevance' => $page->relevance
//                    );
//
//                    $i++;
//                }
//            }
            $sql = "SELECT DISTINCT educations.*,
                  MATCH (title) AGAINST ($search IN BOOLEAN MODE) AS relevance
                  FROM educations
                  WHERE MATCH (title) AGAINST ($search IN BOOLEAN MODE)
                  AND published = '1'
                  AND language = '$this->language'
                  ORDER BY relevance DESC";
            /*$sql = "SELECT DISTINCT educations.*,
                  MATCH (title) AGAINST ($search) AS relevance
                  FROM educations
                  WHERE MATCH (title) AGAINST ($search)
                  AND published = '1'
                  AND language = '$this->language'
                  ORDER BY relevance DESC";*/
            $pages = DB::query(Database::SELECT, $sql)->as_object()->execute();
            foreach ($pages as $page) {
                if (!empty($page->{'title'}) ) {
                    $results[$i] = array('type' => 'educations', 'id' => $page->id,
                        'title' => $page->{'title'},
                        'desc' => $page->{'title'},
                        'controller' => 'scorm',
                        'relevance' => $page->relevance
                    );
                    $i++;
                }
            }
        }

        //Заголовки, описание и текст календаря (calendar)
        if ($c == 'all' || strpos($c,'calendar')!==false)
        {
            $sql = "SELECT DISTINCT calendar.*,
                  MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search IN BOOLEAN MODE) AS relevance
                  FROM calendar
                  WHERE MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search IN BOOLEAN MODE)
                  AND published = '1'
                  ORDER BY relevance DESC";
            /*$sql = "SELECT DISTINCT calendar.*,
                  MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search) AS relevance
                  FROM calendar
                  WHERE MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search)
                  AND published = '1'
                  ORDER BY relevance DESC";*/
//            $pages = DB::query(Database::SELECT, $sql)->as_object()->execute();
//            foreach ($pages as $page) {
//                if (!empty($page->{'title_' . $this->language}) AND !empty($page->{'desc_' . $this->language})) {
//                    $results[$i] = array('type' => 'news',
//                        'id' => $page->id,
//                        'title' => $page->{'title_' . $this->language},
//                        'desc' => $page->{'desc_' . $this->language},
//                        'controller' => 'calendar',
//                        'relevance' => $page->relevance
//                    );
//                    if ($page->image != 0) {
//                        $results[$i]['image'] = FileData::filepath($page->image);
//                    }
//                    $i++;
//                }
//            }
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
    }


}
