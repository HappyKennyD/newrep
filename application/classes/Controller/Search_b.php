<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Search extends Controller_Core
{

    public function action_index()
    {
        $search = $this->request->param('string', "");

        $search = Security::xss_clean(mysql_real_escape_string($search));

        $this->add_cumb('Search', 'search');
        if (!empty($search)) {
            $this->add_cumb($search, '');
        }

        if (empty($search)) {
            $search = Security::xss_clean(Arr::get($_POST, 'search', ''));
            if (!empty($search)) {
                $this->redirect('search/' . $search, 301);
            }
        }

        $this->set('search', $search);
        $query_b = '%' . $search . '%';
        $search = Database::instance()->escape($search);

        //$results = new Massiv();
        $images = Array();
        $i = 0;


        /* Таблицы:
        biography (name, desc, text)
        books (title, author, description)
        calendar (title, desc, text)
        news (title, desc, text)
        pages_contents (title, description, text)
        publications (title, desc, text)
        */

        $query_a = DB::expr(' AGAINST(' . $search . ') ');

        //Заголовки, описание и текст биографий
        $sql = "SELECT DISTINCT biography.*,
              MATCH (name_$this->language, desc_$this->language, text_$this->language) AGAINST ($search) AS relevance
              FROM biography
              WHERE MATCH (name_$this->language, desc_$this->language, text_$this->language) AGAINST ($search)
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

        //Заголовки, описание и текст страниц

        $sql = "SELECT DISTINCT pages_contents.*,
              MATCH (title_$this->language, description_$this->language, text_$this->language) AGAINST ($search) AS relevance
              FROM pages_contents
              WHERE MATCH (title_$this->language, description_$this->language, text_$this->language) AGAINST ($search)
              AND published = '1'
              ORDER BY relevance DESC";

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

        //Заголовки книг, авторы

        $sql = "SELECT DISTINCT books.*,
              MATCH (title, author, description) AGAINST ($search) AS relevance
              FROM books
              WHERE MATCH (title, author, description) AGAINST ($search)
              AND published = '1'
              AND show_$this->language = '1'
              ORDER BY relevance DESC";

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


        //Заголовки, описание и текст новостей

        $sql = "SELECT DISTINCT news.*,
              MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search) AS relevance
              FROM news
              WHERE MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search)
              AND published = '1'
              ORDER BY relevance DESC";

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


        //Заголовки, описание и текст статей

        $sql = "SELECT DISTINCT publications.*,
              MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search) AS relevance
              FROM publications
              WHERE MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search)
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


        //Заголовки, описание и текст календаря

        $sql = "SELECT DISTINCT calendar.*,
              MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search) AS relevance
              FROM calendar
              WHERE MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search)
              AND published = '1'
              ORDER BY relevance DESC";

        $pages = DB::query(Database::SELECT, $sql)->as_object()->execute();

        foreach ($pages as $page) {
            if (!empty($page->{'title_' . $this->language}) AND !empty($page->{'desc_' . $this->language})) {
                $results[$i] = array('type' => 'news',
                    'id' => $page->id,
                    'title' => $page->{'title_' . $this->language},
                    'desc' => $page->{'desc_' . $this->language},
                    'controller' => 'calendar',
                    'relevance' => $page->relevance
                );
                if ($page->image != 0) {
                    $results[$i]['image'] = FileData::filepath($page->image);
                }
                $i++;
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
        $this->set('results', $results);
        $this->set('result', $result);
        $this->set('images', $images);
        $this->set('first_key', key($images));
        $this->set('max_key', sizeof($images));
        $this->set('total', sizeof($results));
        $paginate = Paginatemain::factory($results)->paginate()->render();
        $this->set('paginate', $paginate);
    }


}