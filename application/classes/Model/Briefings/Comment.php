<?php
class Model_Briefings_Comment extends ORM
{
    protected $_belongs_to = array(
        'user' => array(
            'model'=> 'User',
            'far_key'=> 'id',
            'foreign_key'=>'user_id'
        )
    );

}