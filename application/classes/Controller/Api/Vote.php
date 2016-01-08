<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Vote extends Controller_Api_Private{

    public function action_index()
    {
        $opinionId = Security::xss_clean(Arr::get($this->post, 'opinionId', ''));
        $voteValue = strtolower( Security::xss_clean(Arr::get($this->post, 'voteValue', '')) );
        if( !empty($opinionId) AND !empty($voteValue) )
        {

            $user = ORM::factory('User', $this->user_id);
            $opinion = ORM::factory('Debate_Opinion', $opinionId);
            $poll_user = ORM::factory('Debate_Poll')->where('user_id', '=', $this->user_id)->and_where('branch_id', '=', $opinionId)->find();

            if( $poll_user->loaded() )
            {
                $this->data['error'] = 'You have already voted';
                $this->response->body( json_encode($this->data) );
            }
            elseif($opinion->debate->author_id == $this->user_id OR $opinion->debate->opponent_email == $user->email )
            {
                $this->data['error'] = 'Member can not vote';
                $this->response->body( json_encode($this->data) );
            }
            else
            {
                $poll              = ORM::factory('Debate_Poll');
                $poll->branch_id   = $opinionId;
                $poll->variant     = 1;
                $poll->user_id     = $this->user_id;
                $poll->save();

                switch ($voteValue)
                {
                    case 'like':
                        $opinion->plus += 1;
                    break;

                    case 'dislike':
                        $opinion->minus += 1;
                    break;
                }

                $opinion->save();

                $this->response->body( json_encode(true) );
            }

        }
    }

}