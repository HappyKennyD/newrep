<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Opinions extends Controller_Api_Core{

    public function action_index()
    {
        $i = 0;

        $debate_opinions = ORM::factory('Debate_Opinion')->where('debate_id', '=', $this->entryId)->order_by('date', 'ASC')->limit($this->limit)->offset($this->offset)->find_all();
        foreach ($debate_opinions as $item)
        {
            if( !empty($item->moderator_text) )
            {
                $text = $item->moderator_text;
            }
            else
            {
                $text = $item->opinoin;
            }

            if( !empty($item->user->profile->img->file_path) )
            {
                $avatar = 'http://'.$_SERVER['SERVER_NAME'] . '/' . $item->user->profile->img->file_path;
            }

            $this->data[$i]['id']          = $item->id;
            $this->data[$i]['author']      = $item->user->username;
            $this->data[$i]['authorId']    = $item->user->id;
            $this->data[$i]['photoUrl']    = $avatar;
            $this->data[$i]['createdDate'] = $item->date;
            $this->data[$i]['text']        = $text;
            $this->data[$i]['plus']        = $item->plus;
            $this->data[$i]['minus']       = $item->minus;

            $i++;
        }

        $this->response->body( json_encode($this->data) );
    }

}