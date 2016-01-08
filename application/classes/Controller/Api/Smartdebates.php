<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Smartdebates extends Controller_Api_Core
{

    public function action_index()
    {
        header('Access-Control-Allow-Origin: *');
		$list = ORM::factory('Debate')->where_open()->where('is_public', '=', 1);
        $id = (int)$this->request->param('id', 0);
        $status = Security::xss_clean(Arr::get($_GET, 'status', ''));
        //SEO. закрываем сортировку
        if ($id!=0 or $status!='')
        {
            $sort=1;
            Kotwig_View::set_global('sort',$sort);
        }
        //end_SEO
        $nowdate = date('Y-m-d H:i:s');
        
        $user = ORM::factory('User', $id);

        if (Auth::instance()->logged_in()) {
            $has_ma_access = Auth::instance()->get_user()->has_access('ma');
        } else {
            $has_ma_access = 0;
        }
        if (!$has_ma_access) {
            $list = $list->and_where('is_closed', '=', 0);
        } else {
            $list = $list->or_where('is_public', '=', 0);
        }

        switch ($status) {
            case "active":
                $list->and_where('end_time', '>', $nowdate);
                $this->set('status', 'active');
                break;
            case "ending":
                $list->and_where('end_time', '<', $nowdate);
                $this->set('status', 'ending');
                break;
            default:
                if ($id) {
                    $list->and_where('author_id', '=', $id);
                    $this->set('id', $id);
                } else {
                    if (Auth::instance()->logged_in()) {
                        $list->or_where('author_id', '=', Auth::instance()->get_user()->id)->or_where('opponent_email', '=', Auth::instance()->get_user()->email);
                    }
                }
                $this->data['status'] = 'all';
        }

        $list->where_close()->and_where('language', '=', $this->language)->order_by('date', 'DESC');
        $paginate = Paginate::factory($list)->paginate(NULL, NULL, 10)->page_count();
        $list = $list->find_all();
        $this->data['pagecount'] = $paginate;
        $this->data['nowdate'] = $nowdate;
        $status = false;
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
            $deb = array();
            $deb['id']                 = $item->id;
            $deb['title']              = $item->title;
            $deb['preview']            = $item->description;
            $deb['createdDate']        = $item->date;
            $deb['authorId']           = $item->author->id;
            $deb['authorUsername']     = $item->author->username;
            $deb['opponentId']         = $item->opponent->id;
            $deb['opponentUsername']   = $item->opponent->username;
            $deb['isActive']           = $status;
            $deb['endTime']            = $item->end_time;
            $deb['url']                = URL::site('/', true) . $this->language . '/api/smartdebates/view/' . $item->id;
            $this->data['list'][] = $deb;

        }

        $this->response->body(json_encode($this->data));
    }

    public function action_view()
    {
        header('Access-Control-Allow-Origin: *');
		$id = (int)$this->request->param('id', 0);
        $debate = ORM::factory('Debate', $id);

        if (!isset($has_access)) {
            $has_access = 0;
        }

        if (!isset($has_ma_access)) {
            $has_ma_access = 0;
        }

        if ((!$debate->loaded()) or (!$has_ma_access and ((($debate->is_public != 1) and (!$has_access)) or ($debate->is_closed)))) {
            $this->data['error'] = 'Not access to this debate';
            $this->response->body(json_encode($this->data));
            return;
        }

        $nowdate = date('Y-m-d H:i:s');

        if ($debate->author->profile->img->file_path)
            $this->data['author_avatar']= URL::media('/images/w140-h140-ccc-si/'.$debate->author->profile->img->file_path,true);
        else
            $this->data['author_avatar']= URL::media('/images/w140-h140-ccc-si/media/images/no_user.jpg',true);

        if ($debate->opponent->profile->img->file_path)
            $this->data['opponent_avatar']= URL::media('/images/w140-h140-ccc-si/'.$debate->opponent->profile->img->file_path,true);
        else
            $this->data['opponent_avatar']= URL::media('/images/w140-h140-ccc-si/media/images/no_user.jpg',true);
//URL.media('/images/w140-h140-ccc-si/media/images/no_user.jpg')
        $this->data['id']                 = $debate->id;
        $this->data['title']              = $debate->title;
        $this->data['preview']            = $debate->description;
        $this->data['createdDate']        = $debate->date;
        $this->data['authorId']           = $debate->author->id;
        $this->data['authorUsername']     = $debate->author->username;
        $this->data['opponentId']         = $debate->opponent->id;
        $this->data['opponentUsername']   = $debate->opponent->username;
        $this->data['endTime']            = $debate->end_time;
        $this->data['url']                = URL::site('/', true) . $this->language . '/api/smartdebates/view/' . $debate->id;
        $this->data['commentCount']       = $debate->comments_count;
        $this->data['nowdate'] = $nowdate;

        $opinions = ORM::factory('Debate_Opinion')->where('debate_id', '=', $id)->order_by('date', 'ASC')->find_all();
        $comments = ORM::factory('Debate_Comment')->where('debate_id', '=', $id);
        if (!$has_ma_access) {
            $comments = $comments->and_where('hide', '=', 0);
        }
        $comments = $comments->order_by('date', 'ASC');
        $comments_count = clone $comments;
        $comments_count = $comments_count->count_all();
        $comments = $comments->find_all();
        foreach ($opinions as $k=>$v)
        {
            $op = array();
            $op['moderator_text'] = $v->moderator_text;
            $op['date'] = $v->date;
            $op['minus'] = $v->minus;
            $op['plus'] = $v->plus;
            $this->data['opinions'][] = $op;
        }
        $this->response->body(json_encode($this->data));


    }
}