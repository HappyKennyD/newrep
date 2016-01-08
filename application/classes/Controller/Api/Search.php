<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Search extends Controller_Api_Core{

    public function action_index()
    {
        $i = 0;


        if( !empty($this->searchText) )
        {
            $data = array();
            $query_b = '%' . $this->searchText . '%';
            $this->searchText = Database::instance()->escape($this->searchText);
            $query_a = DB::expr(' AGAINST(' . $this->searchText . ') ');

            $search = $this->searchText;

            $sql = "SELECT DISTINCT pages_contents.*, MATCH (title_$this->language, description_$this->language, text_$this->language) AGAINST ($search) AS relevance FROM pages_contents WHERE MATCH (title_$this->language, description_$this->language, `text_$this->language`) AGAINST ($search) AND `published` = '1' ORDER BY relevance DESC";
            $pages = DB::query(Database::SELECT, $sql)->as_object()->execute();

            $i=0;

            foreach ($pages as $page)
            {
                if (!empty($page->{'title_'.$this->language}) AND !empty($page->{'description_'.$this->language})) {
                    $coverUrl = '';

                    if ($page->image != 0) {
                        $storage=ORM::factory('Storage',$page->image);
                        if ($storage->loaded()){
                            $coverUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $storage->file_path;
                        }
                    }

                    /*$data['id']             = $page->id;
                    $data['title']          = $page->{'title_'.$this->language};
                    $data['createdDate']    = $page->date;
                    $data['coverUrl']       = $coverUrl;
                    $data['preview']        = $page->{'description_'.$this->language};
                    $data['url']            = URL::site('/',true)  . $this->language . '/contents/view/' . $page->id;
                    $data['entryType']      = 'LIST_79';
                    $data['relevance']      = $page->relevance;*/

                    $data[$i] = array(
                        'id'=>$page->id,
                        'title'=>$page->{'title_'.$this->language},
                        'createdDate'=>$page->date,
                        'coverUrl'=>$coverUrl,
                        'preview'=>$page->{'description_'.$this->language},
                        'url'=>URL::site('/',true)  . $this->language . '/contents/view/' . $page->id,
                        'entryType'=>'LIST_79',
                        'relevance'=>$page->relevance
                    );


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

            $publications = DB::query(Database::SELECT, $sql)->as_object()->execute();
            foreach ($publications as $item) {
                if (!empty($item->{'title_' . $this->language}) AND !empty($item->{'desc_' . $this->language})) {
                    $coverUrl = '';
                    if ($item->image != 0) {
                        $storage=ORM::factory('Storage',$item->image);
                        if ($storage->loaded()){
                            $coverUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $storage->file_path;
                        }
                    }

                   /* $data['id']            = $item->id;
                    $data['title']         = $item->{'title_' . $this->language};
                    $data['createdDate']   = $item->date;
                    $data['coverUrl']      = $coverUrl;
                    $data['preview']       = $item->{'desc_' . $this->language};
                    $data['url']           = URL::site('/', true) . $this->language . '/publications/view/' . $item->id;
                    $data['entryType']     = 'PUBLICATIONS';
                    $data['relevance']      = $item->relevance;*/

                    $data[$i] = array(
                        'id'=>$item->id,
                        'title'=>$item->{'title_'.$this->language},
                        'createdDate'=>$item->date,
                        'coverUrl'=>$coverUrl,
                        'preview'=>$item->{'desc_'.$this->language},
                        'url'=>URL::site('/',true)  . $this->language . '/publications/view/' . $item->id,
                        'entryType'=>'PUBLICATIONS',
                        'relevance'=>$item->relevance
                    );

                    $i++;
                }
            }

            //Мнение экспертов
            $sql = "SELECT DISTINCT expert_opinions.*,
              MATCH (title_$this->language, description_$this->language, text_$this->language) AGAINST ($search) AS relevance
              FROM expert_opinions
              WHERE MATCH (title_$this->language, description_$this->language, text_$this->language) AGAINST ($search)
              ORDER BY relevance DESC";

            $opinions = DB::query(Database::SELECT, $sql)->as_object()->execute();

            //$opinions = ORM::factory('Expert_Opinion')->where(DB::expr('MATCH(title_' . $this->language . ')'), '', $query_a)->or_where(DB::expr('MATCH(description_' . $this->language . ')'), '', $query_a)->or_where(DB::expr('MATCH(text_' . $this->language . ')'), '', $query_a)->or_where('title_' . $this->language, 'like', $query_b)->find_all();

            foreach ($opinions as $item) {
                if (!empty($item->{'title_' . $this->language}) AND !empty($item->{'description_' . $this->language})) {
                    /*$data['id']             = $item->id;
                    $data['title']          = $item->{'title_' . $this->language};
                    $data['preview']        = $item->{'description_' . $this->language};
                    $data['createdDate']    = $item->date;
                    $data['url']            = URL::site('/', true) . $this->language . '/expert/view/' . $item->id;
                    $data['entryType']      = 'EXPERT_OPINION';*/

                    $data[$i] = array(
                        'id'=>$item->id,
                        'title'=>$item->{'title_'.$this->language},
                        'createdDate'=>$item->date,
                        'preview'=>$item->{'description_'.$this->language},
                        'url'=>URL::site('/',true)  . $this->language . '/expert/view/' . $item->id,
                        'entryType'=>'EXPERT_OPINION',
                        'relevance'=>$item->relevance
                    );
                    $i++;
                }
            }

            if(!empty($data)){
                usort($data, function ($a, $b) {
                    if ($a['relevance'] == $b['relevance']) return 0;
                    return $a['relevance'] < $b['relevance'] ? 1 : -1;
                });

                $old_results = $data;
                unset($data);

                $i = 0;
                foreach ($old_results as $result) {
                    $data[$i]['id']            = $result['id'];
                    $data[$i]['title']         = $result['title'];
                    $data[$i]['createdDate']   = $result['createdDate'];
                    $data[$i]['preview']       = $result['preview'];
                    $data[$i]['url']           = $result['url'];
                    $data[$i]['entryType']     = $result['entryType'];
                    if (isset($result['coverUrl'])){
                        $data[$i]['coverUrl']      = $result['coverUrl'];
                    }
                    $data[$i]['relevance']      = $result['relevance'];
                    $i++;
                }

            }
        }
        else
        {
            $this->data['error'] = 'Unknown Exception';
        }

        $j = 0;

        for($i = $this->offset ;$i < $this->limit + $this->offset; $i++)
        {
            if( !empty($data[$i]) )
            {
                $this->data[$j] = $data[$i];
                $j++;
            }
            else
            {
                break;
            }
        }

        $this->response->body(json_encode($this->data));
    }

}