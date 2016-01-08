<?php
class Controller_Ajax_Comments extends Controller
{

    public function action_send_comment()
    {
        if (Auth::instance()->logged_in()) {
//            $array =  $this->request->param('id', 0);
//            header('Content-type: application/json');
            $text = Arr::get($_POST, 'text', '');
            $array =  Arr::get($_POST, 'id', '');
            $param = explode("-",$array);
            $table = (isset($param[0]))?$param[0]:false;
            $object_id  = (isset($param[1]))?$param[1]:false;
            $user = Auth::instance()->get_user();
            if ( ! empty($text))
            {
                $comment = ORM::factory('Comment');
                try
                {
                    $comment->user_id = $user->pk();
                    $comment->object_id = $object_id;
                    $comment->table = Security::xss_clean($table);
                    $comment->text = Security::xss_clean($text);
                    $comment->date = date("Y:m:d H:i:s");
                    $comment->save();

//                    $email='kaz.ehistory@gmail.com';
//
//                    Email::connect();
//                    Email::View('new_comment');
//                    Email::set(array('url' => URL::media('/manage/comments', true)));
//                    Email::send($email, array('no-reply@e-history.kz', 'e-history.kz'), "Новый комментарий на e-history.kz", '', true);

                    $photo = 0;
                    if (isset($comment->user->profile->img->file_path))
                    {
                        $photo = $comment->user->profile->img->file_path;
                    }
                    $data = array('photo'=>$photo,
                                  'name'=>$comment->user->show_name(),
                                  'id'=>$user->pk(),
                                  'text'=>$text,
                                  'date'=>date('d.m.Y', strtotime($comment->date)).', '.date('H:i', strtotime($comment->date)));
                    $this->response->body(json_encode($data));
                }
                catch(ORM_Validation_Exception $e)
                {
                    $this->response->body(json_encode(false));
                }
            }
            else
            {
                $this->response->body(json_encode(-1));
            }
        }
    }

    public function action_deploy_comment()
    {
        header('Content-type: application/json');
        $count_comment = (int) Arr::get($_POST, 'count_comment', 0);
        $array = Arr::get($_POST, 'id', '');
        $param = explode("-",$array);
        $table = (isset($param[0]))?$param[0]:false;
        $object_id  = (int)(isset($param[1]))?$param[1]:false;
        $data = array();
        if ($count_comment > 0)
        {
            $next_comment = ORM::factory('Comment')->where('table','=',$table)->and_where('object_id','=',$object_id)->and_where('status','=',1)->order_by('date', 'asc')->limit(15)->offset($count_comment)->find_all();
            foreach ($next_comment as $item)
            {
                $photo = 0;
                if (isset($item->user->profile->img->file_path))
                {
                    $photo = $item->user->profile->img->file_path;
                }
                $data[] = array('photo'=>$photo,
                                'name'=>$item->user->show_name(),
                                'user_id'=>$item->user_id,
                                'text'=>$item->text,
                                'date'=>date('d.m.Y', strtotime($item->date)).', '.date('H:i', strtotime($item->date)));
            }
            $this->response->body(json_encode($data));
        }
        else
        {
            $this->response->body(json_encode($data));
        }
    }

}