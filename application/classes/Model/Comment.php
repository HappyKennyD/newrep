<?php
class Model_Comment extends ORM
{
    protected $_belongs_to = array(
        'user' => array(
            'model' => 'User',
            'foreign_key' => 'user_id',
            'far_key'=>'id'
        )
    );

    public function rules()
    {
        return array(
            'text' => array(
                array('not_empty'),
                //array('min_length', array(':value', 50)),
            )
        );
    }
}