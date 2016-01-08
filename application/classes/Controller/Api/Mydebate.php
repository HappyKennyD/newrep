<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api_Mydebate extends Controller_Api_Private
{

    public function action_index()
    {
        if (!empty($this->post))
        {
            $empt = array();
            $data = array();
            $i = 0;
            $status = false;
            $nowdate = date('Y-m-d H:i:s');
            if (!empty($this->searchText)) {
                $query_b = '%' . $this->searchText . '%';
                $this->searchText = Database::instance()->escape($this->searchText);
                $query_a = DB::expr(' AGAINST(' . $this->searchText . ') ');

                $debate = ORM::factory('Debate')->where_open()->where(DB::expr('MATCH(title, description)'), '', $query_a)
                    ->or_where('title', 'like', $query_b)->or_where('description', 'like', $query_b)
                    ->where_close()->and_where('is_closed', '=', '0')->and_where('is_public', '=', '1')
                    ->and_where('language', '=', $this->language)->where_open()->where('author_id', '=', $this->user_id)
                    ->or_where('opponent_id', '=', $this->user_id)->where_close()->limit($this->limit)->offset($this->offset)
                    ->find_all();

                foreach ($debate as $item) {
                    if ($item->is_closed == 0) {
                        if (Date::translate($item->end_time, 'U') < Date::translate($nowdate, 'U') AND Date::translate($item->end_time, 'U') != 0) {
                            $status = false;
                        } else {
                            $status = true;
                        }
                    }

                    $this->data[$i]['id'] = $item->id;
                    $this->data[$i]['title'] = $item->title;
                    $this->data[$i]['preview'] = $item->description;
                    $this->data[$i]['createdDate'] = $item->date;
                    $this->data[$i]['authorId'] = $item->author->id;
                    $this->data[$i]['authorUsername'] = $item->author->username;
                    $this->data[$i]['opponentId'] = $item->opponent->id;
                    $this->data[$i]['opponentUsername'] = $item->opponent->username;
                    $this->data[$i]['isActive'] = $status;
                    $this->data[$i]['endTime'] = $item->end_time;
                    $this->data[$i]['url'] = URL::site('/', true) . $this->language . '/debate/view/' . $item->id;

                    $i++;
                }
                $this->response->body(json_encode($this->data));
            } else {
                $debates = ORM::factory('Debate')->where_open()->where('author_id', '=', $this->user_id)
                    ->or_where('opponent_id', '=', $this->user_id)->where_close()->and_where('language', '=', $this->language)
                    ->and_where('is_public', '=', 1)->and_where('is_closed', '=', 0)->limit($this->limit)->offset($this->offset);
                if ($debates->loaded()) {
                    $this->response->body(json_encode($empt));
                } else {
                    $debates = $debates->find_all();
                    foreach ($debates as $item) {
                        $cur_date = time();
                        $end_date = strtotime($item->end_time);
                        if ($cur_date < $end_date)
                            $status = true;
                        $this->data[$i]['id'] = $item->id;
                        $this->data[$i]['title'] = $item->title;
                        $this->data[$i]['preview'] = $item->description;
                        $this->data[$i]['createdDate'] = $item->date;
                        $this->data[$i]['isActive'] = $status;
                        $this->data[$i]['authorId'] = $item->author->id;
                        $this->data[$i]['authorUsername'] = $item->author->username;
                        $this->data[$i]['opponentId'] = $item->opponent->id;
                        $this->data[$i]['opponentUsername'] = $item->opponent->username;
                        $this->data[$i]['endTime'] = $item->end_time;
                        $this->data[$i]['url'] = URL::site('/', true) . $this->language . '/debate/view/' . $item->id;
                        $this->response->body(json_encode($this->data));
                        $i++;
                    }
                }
            }
        }
    }
}