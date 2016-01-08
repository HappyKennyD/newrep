<?php
class Model_Log extends ORM
{
    protected $_table_name = 'log';

    protected $_belongs_to = array(
        'user' => array(
            'model'=> 'User',
            'far_key'=> 'id',
            'foreign_key'=>'user_id'
        )
    );

    public function countThis($string)
    {
        $string = strip_tags($string);
        $e = explode(" ", $string);
        foreach ($e as $k=>$word)
        {
            if (UTF8::strlen($word) < 2)
            {
                unset($e[$k]);
            }
        }
        return count($e);
    }
}