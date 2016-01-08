<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Opinionadd extends Controller_Api_Private{

    public function action_index()
    {
        $debate = ORM::factory('Debate', $this->entryId);

        if( $debate->author_id == $this->user_id OR $debate->opponent_id == $this->user_id )
        {
            if( $debate->end_time > date("Y-m-d H:i:s") AND !$debate->is_closed )
            {
                if ( $debate->replier_id == $this->user_id )
                {
                    try
                    {
                        $opinion                  = ORM::factory('Debate_Opinion');
                        $opinion->debate_id       = $this->entryId;
                        $opinion->author_id       = $this->user_id;
                        $opinion->date            = date('Y-m-d H:i:s');
                        $opinion->moderator_text  = $this->text;
                        $opinion->opinion         = $this->text;
                        $opinion->save();

                        $debate->replier_id = ($debate->replier_id == $debate->author_id) ? ($debate->opponent_id) : ($debate->author_id);
                        $debate->save();

                        $this->response->body(json_encode(true));
                    }
                    catch (ORM_Validation_Exception $e)
                    {
                        $this->data['error'] = $e->errors($e->alias());
                        $this->response->body(json_encode($this->data));
                    }

                }
                else
                {
                    $this->data['error'] = 'Wait answer opponent';
                    $this->response->body(json_encode($this->data));
                }
            }
            else
            {
                $this->data['error'] = 'Debate is closed or completed';
                $this->response->body(json_encode($this->data));
            }
        }
        else
        {
            $this->data['error'] = 'Unknown Exception';
            $this->response->body(json_encode($this->data));
        }
    }

}