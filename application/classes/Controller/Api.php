<?php

class Controller_Api extends Controller{
    public function after()
    {
        $this->response->headers('Content-Type','application/json');
        parent::after();
    }


    public function action_debates()
    {
        $id = (int)$this->request->param('id',null);
        $lang = $this->request->param('language', 'ru');
        I18n::lang($lang);
        $data = array();
        if ($id != null)
        {
            $item = ORM::factory('Debate',$id);
            if (!$item->loaded()){
                throw new HTTP_Exception_404;
            }else
            {
                $data['id'] = $item->id;
                $data['title'] = $item->title;
                $data['date'] = $item->date;
                $data['end_time'] = $item->end_time;
                $data['start_time'] = $item->start_time;
                $data['lifetime'] = $item->lifetime;
                $data['author_id'] = $item->author_id;
                $data['author_email'] = $item->author_email;
                $data['opponent_email'] = $item->opponent_email;
                $data['opponent_id'] = $item->opponent_id;
                $data['is_public'] = $item->is_public;
                $data['replier_id'] = $item->replier_id;
                $data['language'] = $item->language;
                $data['description'] = $item->description;
                $data['comments_count'] = $item->comments_count;
                $data['is_closed'] = $item->is_closed;
                $data['admin_create'] = $item->admin_create;
                $this->response->body(json_encode($data));
            }
        }else {
            $debate = ORM::factory('Debate')->where('is_public', '=', 1)->where('language', '=', I18n::lang())->order_by('date', 'DESC')->find_all();
            $i = 0; foreach($debate as $item)
            {
                $data[$i]['id'] = $item->id;
                $data[$i]['title'] = $item->title;
                $data[$i]['date'] = $item->date;
                $data[$i]['opponent'] = (trim($item->opponent->profile->first_name.' '.$item->opponent->profile->last_name) != '')?$item->opponent->profile->first_name.' '.$item->opponent->profile->last_name:$item->opponent->username;
                $data[$i]['author'] = (trim($item->author->profile->first_name.' '.$item->author->profile->last_name) != '')?$item->author->profile->first_name.' '.$item->author->profile->last_name:$item->author->username;
                $data[$i]['author_email'] = $item->author_email;
                $data[$i]['opponent_email'] = $item->opponent_email;
                $data[$i]['description'] = $item->description;
                $data[$i]['comments_count'] = $item->comments_count;
                $data[$i]['is_closed'] = $item->is_closed;
                $i++;
            }
            $this->response->body(json_encode($data));
        }

    }

}