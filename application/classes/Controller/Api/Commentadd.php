<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Commentadd extends Controller_Api_Private
{
    public function action_index()
    {

        $explode = explode('_', strtolower($this->entryType));
        if ($explode[0]=='list'){
            $this->entryType='pages_contents';
        }

        if ( !empty($this->text))
        {
            switch ($this->entryType) {
                case 'publications':;
                case 'pages_contents':
                    $comment = ORM::factory('Comment');
                    try
                    {
                        $comment->user_id = $this->user_id;
                        $comment->object_id = $this->entryId;
                        $comment->table = Security::xss_clean($this->entryType);
                        $comment->text = $this->text;
                        $comment->date = date("Y:m:d H:i:s");
                        $comment->save();

                        $this->response->body(json_encode(true));
                    }
                    catch(ORM_Validation_Exception $e)
                    {
                        $this->response->body(json_encode(false));
                    }
                break;

                case 'expert_opinion':
                    $expert_comment = ORM::factory('Expert_Comment');
                    try
                    {
                        $expert_comment->text               = $this->text;
                        $expert_comment->user_id            = $this->user_id;
                        $expert_comment->date               = date("Y:m:d H:i:s");
                        $expert_comment->opinion_id         = $this->entryId;
                        $expert_comment->moderator_id       = 0;
                        $expert_comment->moderator_decision = 1;

                        $expert_comment->save();

                        $this->response->body(json_encode(true));
                    }
                    catch(ORM_Validation_Exception $e)
                    {
                        $this->data['error'] = 'Unknown Exception';
                        $this->response->body(json_encode($this->data));
                    }
                break;

                case 'debate':
                    $debate = ORM::factory('Debate_Comment');
                    try{
                        $debate->debate_id = $this->entryId;
                        $debate->date = date('Y-m-d H:i:s');
                        $debate->comment = $this->text;
                        $debate->user_id = $this->user_id;
                        $debate->save();
                        $debate = ORM::factory('Debate', $this->entryId);
                        $debate->comments_count += 1;
                        $debate->save();
                        $this->response->body(json_encode(true));
                    }
                    catch(ORM_Validation_Exception $e)
                    {
                        $this->data['error'] = 'Unknown Exception';
                        $this->response->body(json_encode($this->data));
                    }

                    break;
            }
        }
        else
        {
            $this->data['error'] = 'Unknown Exception';
            $this->response->body(json_encode($this->data));
        }
    }
}
