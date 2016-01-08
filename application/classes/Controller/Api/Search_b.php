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

            //Заголовки, описание и текст страниц
            $pages = ORM::factory('Pages_Content')->where(DB::expr('MATCH(title_' . $this->language . ', description_' . $this->language . ', text_' . $this->language . ')'), '', $query_a)->or_where('title_' . $this->language, 'like', $query_b)->and_where('published', '=', 1)->and_where('title_'.$this->language,'<>','')->and_where('description_' . $this->language,'<>','')->and_where('text_' . $this->language,'<>','')->find_all();
            foreach ($pages as $page)
            {
                if (!empty($page->title) AND !empty($page->description)) {

                    $coverUrl = '';

                    if ($page->image != 0) {
                        $coverUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $page->picture->file_path;
                    }
                    $data[$i]['id']             = $page->id;
                    $data[$i]['title']          = $page->title;
                    $data[$i]['createdDate']    = $page->date;
                    $data[$i]['coverUrl']       = $coverUrl;
                    $data[$i]['preview']        = $page->description;
                    $data[$i]['url']            = URL::site('/',true)  . $this->language . '/contents/view/' . $page->id;
                    $data[$i]['entryType']      = 'LIST_79';

                    $i++;
                }
            }

            //Заголовки, описание и текст статей
            $publications = ORM::factory('Publication')->where_open()->where(DB::expr('MATCH(title_' . $this->language . ')'), '', $query_a)->or_where(DB::expr('MATCH(desc_' . $this->language . ')'), '', $query_a)->or_where(DB::expr('MATCH(text_' . $this->language . ')'), '', $query_a)->or_where('title_' . $this->language, 'like', $query_b)->where_close()->and_where('published', '<>', 0)->and_where('title_'.$this->language, '<>', '')->find_all();

            foreach ($publications as $item) {
                if (!empty($item->title) AND !empty($item->desc)) {
                    $coverUrl = '';
                    if (!empty($item->picture->file_path)) {
                        $coverUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $item->picture->file_path;
                    }
                    $data[$i]['id']            = $item->id;
                    $data[$i]['title']         = $item->title;
                    $data[$i]['createdDate']   = $item->date;
                    $data[$i]['coverUrl']      = $coverUrl;
                    $data[$i]['preview']       = $item->desc;
                    $data[$i]['url']           = URL::site('/', true) . $this->language . '/publications/view/' . $item->id;
                    $data[$i]['entryType']     = 'PUBLICATIONS';
                    $i++;
                }
            }

            //Мнение экспертов
            $opinions = ORM::factory('Expert_Opinion')->where(DB::expr('MATCH(title_' . $this->language . ')'), '', $query_a)->or_where(DB::expr('MATCH(description_' . $this->language . ')'), '', $query_a)->or_where(DB::expr('MATCH(text_' . $this->language . ')'), '', $query_a)->or_where('title_' . $this->language, 'like', $query_b)->find_all();

            foreach ($opinions as $item) {

                $data[$i]['id']             = $item->id;
                $data[$i]['title']          = $item->title;
                $data[$i]['preview']        = $item->description;
                $data[$i]['createdDate']    = $item->date;
                $data[$i]['url']            = URL::site('/', true) . $this->language . '/expert/view/' . $item->id;
                $data[$i]['entryType']      = 'EXPERT_OPINION';
                $i++;
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