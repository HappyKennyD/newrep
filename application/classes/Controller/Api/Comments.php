<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Comments extends Controller_Api_Core
{

    public function action_index()
    {
        $i = 0;
        $explode = explode('_', strtolower($this->entryType));
        if ($explode[0]=='list'){
            $this->entryType='pages_contents';
        }
        switch ($this->entryType) {
            case 'publications':
            case 'pages_contents':
                $allComments = ORM::factory('Comment')->where('status', '=', 1)
                    ->and_where('object_id', '=', $this->entryId)
                    ->and_where('table', '=', $this->entryType)
                    ->order_by('date', 'DESC')
                    ->limit($this->limit)
                    ->offset($this->offset)->find_all();

                foreach ($allComments as $item) {
                    $this->data[$i]['commentId'] = $item->id;
                    $this->data[$i]['createdDate'] = $item->date;
                    $this->data[$i]['text'] = $item->text;

                    $this->data[$i]['author']=$this->userdata($item->user_id);

                    $i++;
                }
                break;

            case 'expert_opinion':
                $allComments = ORM::factory('Expert_Comment')->where('moderator_decision', '=', 1)
                    ->and_where('opinion_id', '=', $this->entryId)
                    ->order_by('date', 'DESC')
                    ->limit($this->limit)
                    ->offset($this->offset)->find_all();

                foreach ($allComments as $item) {
                    $this->data[$i]['commentId'] = $item->id;
                    $this->data[$i]['createdDate'] = $item->date;
                    $this->data[$i]['text'] = $item->text;

                    $this->data[$i]['author']=$this->userdata($item->user_id);

                    $i++;
                }
                break;

            case 'debate':
                $allComments = ORM::factory('Debate_Comment')
                    ->where('hide','=',0)
                    ->where('debate_id', '=', $this->entryId)
                    ->order_by('date', 'DESC')
                    ->limit($this->limit)
                    ->offset($this->offset)->find_all();

                foreach ($allComments as $item) {
                    $this->data[$i]['commentId'] = $item->id;
                    $this->data[$i]['createdDate'] = $item->date;
                    $this->data[$i]['text'] = $item->comment;

                    $this->data[$i]['author']=$this->userdata($item->user_id);

                    $i++;
                }

                break;

            default:
                $allComments = array();
        }

        $this->response->body(json_encode($this->data));
    }

    function userdata($userid){
        $user = ORM::factory('User', $userid);
        if (($user->profile->first_name != '') and ($user->profile->last_name != '')) {
            $username = $user->profile->first_name . ' ' . $user->profile->last_name;
        } else {
            $username = $user->username;
        }

        if (!empty($user->profile->img->file_path)){
        $author = array(
            'name' => $username,
            'id' => $user->id,
            'photoUrl' => URL::media($user->profile->img->file_path, true)
        );} else{
            $author = array(
                'name' => $username,
                'id' => $user->id,
                'photoUrl' => '');
        }

        return $author;
    }

}