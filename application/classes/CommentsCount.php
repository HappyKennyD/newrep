<?php
class CommentsCount
{
    public static function getcount($table, $object_id)
    {
        $count=ORM::factory('Comment')->where('table','=',$table)->and_where('object_id','=',$object_id)->find_all()->as_array(null,'id');
        return count($count);
    }
}