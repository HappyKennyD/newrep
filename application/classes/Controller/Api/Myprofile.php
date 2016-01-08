<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Myprofile extends Controller_Api_Private{

    public function action_index()
    {
        $hasDebates = false ;
        $avatar = '';

        $user = ORM::factory('User', $this->user_id);
        $user_id = $user->id;

        /* участие в дебатах */
        $debates = ORM::factory('Debate')->where_open()->where('author_id', '=', $user_id)->or_where('opponent_id', '=', $user_id)->where_close()->and_where('is_closed', '=', 0)->and_where('is_public', '=', 1);
        $clone = clone $debates;
        if( $clone->count_all()!=0 )
        {
            $hasDebates = true ;
        }

        if( !empty($user->profile->img->file_path) )
        {
            $avatar = 'http://'.$_SERVER['SERVER_NAME'] . '/' . $user->profile->img->file_path;
        }

        $this->data['userId']      = $user_id;
        $this->data['login']       = $user->show_name();
        $this->data['name']        = $user->profile->first_name;
        $this->data['surname']     = $user->profile->last_name;
        $this->data['position']    = $user->get_specialization();
        $this->data['hasDebates']  = $hasDebates;
        $this->data['photoUrl']    = $avatar;
        $this->data['about']       =  $user->profile->about;

        $this->api->check_notice($this->user_id);

        $notice = ORM::factory('Api_Notice')->where('user_id', '=', $this->user_id)->and_where('flag', '=', 0)->order_by('date', 'desc')->group_by('object_id')->find_all();

        if( $notice->count() >0 )
        {
            $this->data['notifCount']  = $notice->count();
        }
        else
        {
            $this->data['notifCount']  = 0;
        }

        $this->response->body(json_encode($this->data));
    }
}