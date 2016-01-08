<?php defined('SYSPATH') or die('No direct script access.');

class Comments
{

	/** @var Comments */
	static protected $instance;

	static public function instance()
	{
		if(!isset(static::$instance))
		{
			static::$instance = new static();
		}

		return static::$instance;
	}


    public function save($message,$table,$object_id)
    {
        $user_id = Auth::instance()->get_user()->id;
        $comment = ORM::factory('Comment');
        try
        {
            $comment->user_id = $user_id;
            $comment->object_id = $object_id;
            $comment->table = Security::xss_clean($table);
            $comment->text = $message;
            $comment->date = date("Y:m:d H:i:s");
            $comment->save();
            return $comment;
        }
        catch(ORM_Validation_Exception $e)
        {

        }
    }

    public function CommentsList($table, $object_id,$user)
    {
        return ORM::factory('Comment')->where('table','=',$table)->where('object_id','=',$object_id)->where_open()->where('status', '=', 1)->or_where('user_id','=',$user)->where_close()->order_by('date', 'ASC')->limit(15)->find_all();
    }

	public function form($user)
	{
        return View::factory('index')->set('user',$user)->render();
	}

	public function comment_count($table,$object_id)
	{
		return ORM::factory('Comment')->where('object_id', '=',$object_id)->and_where('table','=',$table)->and_where('status', '=', 1)->count_all();
	}

    public function comment_user_count($user)
    {
        return ORM::factory('Comment')->where('user_id', '=',$user)->count_all();
    }
}
