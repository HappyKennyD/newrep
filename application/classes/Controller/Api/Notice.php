<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Api_Notice extends Controller_Api_Private
{

    public function action_index()
    {
        $this->api->check_notice($this->user_id);

        $get_notices = ORM::factory('Api_Notice')->where('user_id', '=', $this->user_id)->and_where('flag', '=', 0)->order_by('date', 'desc')->group_by('object_id')->find_all();

        if( $get_notices->count() > 0 )
        {
            $i=0;
            foreach($get_notices as $notice )
            {
                $this->data[$i]['entryId']     = $notice->object_id;
                $this->data[$i]['title']       = $notice->debate->title;
                $this->data[$i]['entryType']   = $notice->entry_type;
                $this->data[$i]['createdDate'] = $notice->date;

                $i++;
            }
        }

        $this->response->body(json_encode($this->data));
    }

}