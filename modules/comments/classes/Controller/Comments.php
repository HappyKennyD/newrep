<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Comments extends Controller
{
    public function before()
    {
        parent::before();
    }

    public function action_index()
    {
        $array  = $this->request->param('id', false);
        $param = explode("-",$array);
        $table = (isset($param[0]))?$param[0]:false;
        $object_id  = (int)(isset($param[1]))?$param[1]:false;
        $comments = Comments::instance()->CommentsList($table, $object_id, Auth::instance()->get_user());
        $comments_count = Comments::instance()->comment_count($table, $object_id);
        $template  = View::factory('index');
        $template->set('commentaries', $comments)
            ->set('id',$array)
            ->set('user',Auth::instance()->get_user())
            ->set('comments_count',$comments_count)
            ->render();

        $this->response->body($template->render());
    }

	public function action_list()
	{
        $array  = $this->request->param('id', false);
        $param = explode("-",$array);
        $table = (isset($param[0]))?$param[0]:false;
        $object_id  = (int)(isset($param[1]))?$param[1]:false;
		$comments = Comments::instance()->CommentsList($table, $object_id, Auth::instance()->get_user());
        if (Auth::instance()->logged_in())
        {
            $user = true;
        }else{
            $user = false;
        }
        $template  = View::factory('show');
        $template->set('commentaries', $comments)->set('id',$array)->set('user',$user)->render();

		$this->response->body($template->render());
	}

	public function action_form()
	{
        if (Auth::instance()->logged_in())
        {

            $array  = $this->request->param('id', false);
            $param = explode("-",$array);
            $table = (isset($param[0]))?$param[0]:'';
            $object_id  = (int)(isset($param[1]))?$param[1]:'';
            $user = Auth::instance()->get_user();
            if(Request::$initial->method() == Request::POST)
            {
                $message     = Arr::get($_POST, 'message');
                $save  = Comments::instance()->save($message,$table,$object_id);
                $this->response->body(Comments::instance()->form($user));
                    header( 'Location: ' . URL::base() . Request::$initial->uri() ) ;
                    die();
                    //HTTP::redirect(Request::$initial->uri());
            }
            else
            {
                $this->response->body(Comments::instance()->form($user));
            }

        }
        else
        {
            $this->response->body('<div style="margin: 15px 0 15px 0; text-align: center;">'.__('Для добавления комментариев необходимо авторизоваться.').'</div>');
        }
	}

}