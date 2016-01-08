<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Entries extends Controller_Api_Core
{

    public function action_index()
    {
        $i = 0;

        $explode = explode('_', strtolower($this->entryType));
        if ($explode[0]=='list'){
            $this->entryType=$explode[0];
            $this->entryId=$explode[1];

        }

        if (!empty($this->entryType))
        {
            switch ($this->entryType)
            {
                /* Поиск в статьях */
                case 'publications':

                    if (!empty($this->searchText))
                    {
                        $query_b = '%' . $this->searchText . '%';
                        $this->searchText = Database::instance()->escape($this->searchText);
                        $query_a = DB::expr(' AGAINST(' . $this->searchText . ') ');

                        $publications = ORM::factory('Publication')->distinct('true')->where(DB::expr('MATCH(title_' . $this->language . ')'), '', $query_a)->or_where(DB::expr('MATCH(desc_' . $this->language . ')'), '', $query_a)->or_where(DB::expr('MATCH(text_' . $this->language . ')'), '', $query_a)->or_where('title_' . $this->language, 'like', $query_b)->and_where('published', '=', 1)->limit($this->limit)->offset($this->offset)->find_all();

                        foreach ($publications as $item)
                        {
                            if (!empty($item->title) AND !empty($item->desc)) {
                                $coverUrl = '';
                                if (!empty($item->picture->file_path)) {
                                    $coverUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $item->picture->file_path;
                                }
                                $this->data[$i]['id'] = $item->id;
                                $this->data[$i]['title'] = $item->title;
                                $this->data[$i]['createdDate'] = $item->date;
                                $this->data[$i]['coverUrl'] = $coverUrl;
                                $this->data[$i]['preview'] = $item->desc;
                                $this->data[$i]['url'] = URL::site('/', true) . $this->language . '/publications/view/' . $item->id;
                                $i++;
                            }
                        }
                    } else
                    {
                        $list = ORM::factory('Publication')->where('title_' . $this->language, '<>', '')->where('published', '=', 1)->order_by('date', 'DESC')->limit($this->limit)->offset($this->offset)->find_all();

                        foreach ($list as $item)
                        {
                            $coverUrl = '';
                            if (!empty($item->picture->file_path))
                            {
                                $coverUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $item->picture->file_path;
                            }

                            $this->data[$i]['id'] = $item->id;
                            $this->data[$i]['title'] = $item->title;
                            $this->data[$i]['createdDate'] = $item->date;
                            $this->data[$i]['coverUrl'] = $coverUrl;
                            $this->data[$i]['preview'] = $item->desc;
                            $this->data[$i]['url'] = URL::site('/', true) . $this->language . '/publications/view/' . $item->id;
                            $i++;
                        }
                    }

                break;

                case 'expert_opinion':
                    /* Поиск в экспретах */
                    if (!empty($this->searchText))
                    {
                        $query_b = '%' . $this->searchText . '%';
                        $this->searchText = Database::instance()->escape($this->searchText);
                        $query_a = DB::expr(' AGAINST(' . $this->searchText . ') ');

                        $opinions = ORM::factory('Expert_Opinion')->distinct('true')->where(DB::expr('MATCH(title_' . $this->language . ')'), '', $query_a)->or_where(DB::expr('MATCH(description_' . $this->language . ')'), '', $query_a)->or_where(DB::expr('MATCH(text_' . $this->language . ')'), '', $query_a)->or_where('title_' . $this->language, 'like', $query_b)->limit($this->limit)->offset($this->offset)->find_all();

                        foreach ($opinions as $item)
                        {

                            $this->data[$i]['id'] = $item->id;
                            $this->data[$i]['title'] = $item->title;
                            $this->data[$i]['preview'] = $item->description;
                            $this->data[$i]['createdDate'] = $item->date;
                            $this->data[$i]['url'] = URL::site('/', true) . $this->language . '/expert/view/' . $item->id;
                            $i++;
                        }
                    } else
                    {
                        $opinions = ORM::factory('Expert_Opinion')->where('title_' . $this->language, '<>', '')->order_by('date', 'DESC')->limit($this->limit)->offset($this->offset)->find_all();
                        foreach ($opinions as $item)
                        {
                            $this->data[$i]['id'] = $item->id;
                            $this->data[$i]['title'] = $item->title;
                            $this->data[$i]['createdDate'] = $item->date;
                            $this->data[$i]['preview'] = $item->description;
                            $this->data[$i]['url'] = URL::site('/', true) . $this->language . '/expert/view/' . $item->id;
                            $i++;
                        }
                    }
                break;

                case 'list':
                    //пункты меню
                    if (!empty($this->searchText))
                    {
                        $query_b = '%' . $this->searchText . '%';
                        $this->searchText = Database::instance()->escape($this->searchText);
                        $query_a = DB::expr(' AGAINST(' . $this->searchText . ') ');

                        $contents = ORM::factory('Pages_Content')->where(DB::expr('MATCH(title_' . $this->language . ', description_' . $this->language . ', text_' . $this->language . ')'), '', $query_a)->or_where('title_' . $this->language, 'like', $query_b)->and_where('published', '=', 1)->and_where('title_'.$this->language,'<>','')->and_where('description_' . $this->language,'<>','')->and_where('text_' . $this->language,'<>','')->limit($this->limit)->offset($this->offset)->find_all();

                        foreach ($contents as $item)
                        {

                            $coverUrl = '';
                            if (!empty($item->picture->file_path))
                            {
                                $coverUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $item->picture->file_path;
                            }

                            $this->data[$i]['id']           = $item->id;
                            $this->data[$i]['title']        = $item->title;
                            $this->data[$i]['createdDate']  = $item->date;
                            $this->data[$i]['coverUrl']     = $coverUrl;
                            $this->data[$i]['preview']      = $item->description;
                            $this->data[$i]['url']          = URL::site('/', true) . $this->language . '/contents/list/' . $item->id;
                            $i++;
                        }
                    }
                    else
                    {

                        $id = $this->entryId;
                        $page = ORM::factory('Page', $id);

                        $contents = $page->content->where('published', '=', 1)->where('title_' . $this->language, '<>', '')->order_by('date', 'DESC');
                        $contents = $contents->limit($this->limit)->offset($this->offset)->find_all();

                        foreach ($contents as $item) {

                            $coverUrl = '';
                            if (!empty($item->picture->file_path)) {
                                $coverUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/' . $item->picture->file_path;
                            }

                            $this->data[$i]['id']             = $item->id;
                            $this->data[$i]['title']          = $item->title;
                            $this->data[$i]['createdDate']    = $item->date;
                            $this->data[$i]['coverUrl']       = $coverUrl;
                            $this->data[$i]['preview']        = $item->description;
                            $this->data[$i]['url']            = URL::site('/', true) . $this->language . '/contents/list/' . $item->id;
                            $i++;
                        }
                    }

                break;

                case 'debate':

                    $nowdate = date('Y-m-d H:i:s');
                    $status = false;
                    //поиск в дебатах
                    if (!empty($this->searchText))
                    {
                        $query_b = '%' . $this->searchText . '%';
                        $this->searchText = Database::instance()->escape($this->searchText);
                        $query_a = DB::expr(' AGAINST(' . $this->searchText . ') ');

                        $debate = ORM::factory('Debate')->where(DB::expr('MATCH(title, description)'), '', $query_a)->and_where('is_closed', '=', '0')->or_where('title', 'like', $query_b)->and_where('is_public', '=', '1')->and_where('language', '=', $this->language)->limit($this->limit)->offset($this->offset)->find_all();

                        foreach ($debate as $item)
                        {
                            if ($item->is_closed == 0)
                            {
                                if (Date::translate($item->end_time, 'U') < Date::translate($nowdate, 'U') AND Date::translate($item->end_time, 'U') != 0)
                                {
                                    $status = false;
                                }
                                else
                                {
                                    $status = true;
                                }
                            }

                            $this->data[$i]['id']                 = $item->id;
                            $this->data[$i]['title']              = $item->title;
                            $this->data[$i]['preview']            = $item->description;
                            $this->data[$i]['createdDate']        = $item->date;
                            $this->data[$i]['authorId']           = $item->author->id;
                            $this->data[$i]['authorUsername']     = $item->author->username;
                            $this->data[$i]['opponentId']         = $item->opponent->id;
                            $this->data[$i]['opponentUsername']   = $item->opponent->username;
                            $this->data[$i]['isActive']           = $status;
                            $this->data[$i]['endTime']            = $item->end_time;
                            $this->data[$i]['url']                = URL::site('/', true) . $this->language . '/debate/view/' . $item->id;

                            $i++;
                        }
                    }
                    else
                    {
                        $list = ORM::factory('Debate')->where_open()->and_where('is_closed', '=', 0)->where('is_public', '=', 1)->where_close()->and_where('language', '=', $this->language)->order_by('date', 'DESC')->limit($this->limit)->offset($this->offset)->find_all();

                        foreach ($list as $item)
                        {
                            if ($item->is_closed == 0)
                            {
                                if (Date::translate($item->end_time, 'U') < Date::translate($nowdate, 'U') AND Date::translate($item->end_time, 'U') != 0)
                                {
                                    $status = false;
                                }
                                else
                                {
                                    $status = true;
                                }
                            }

                            $this->data[$i]['id']                 = $item->id;
                            $this->data[$i]['title']              = $item->title;
                            $this->data[$i]['preview']            = $item->description;
                            $this->data[$i]['createdDate']        = $item->date;
                            $this->data[$i]['authorId']           = $item->author->id;
                            $this->data[$i]['authorUsername']     = $item->author->username;
                            $this->data[$i]['opponentId']         = $item->opponent->id;
                            $this->data[$i]['opponentUsername']   = $item->opponent->username;
                            $this->data[$i]['isActive']           = $status;
                            $this->data[$i]['endTime']            = $item->end_time;
                            $this->data[$i]['url']                = URL::site('/', true) . $this->language . '/debate/view/' . $item->id;

                            $i++;
                        }
                    }

                break;

                case 'today':

                    $month    = (int) $this->request->param('m', date('m'));
                    $day      = (int) $this->request->param('d', date('d'));
                    $list     = ORM::factory('Calendar')->where('day','=',$day)->and_where('month', '=', $month)->and_where('title_' . $this->language, '<>', '')->and_where('text_' . $this->language, '<>', '')->find_all();
                    foreach ($list as $item)
                    {
                        $this->data[$i]['id']           = $item->id;
                        $year = '';
                        if(!empty($item->year))
                        {
                            $year = trim($item->year) . '-';
                        }

                        $this->data[$i]['dateHistory']  = $year.trim($item->month).'-'.trim($item->day);
                        $this->data[$i]['title']        = $item->title;

                        $i++;
                    }


                break;
            }
        }
        else
        {
            $this->data['error'] = 'Unknown Exception';
        }

        $this->response->body(json_encode($this->data));
    }

}