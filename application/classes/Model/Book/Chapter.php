<?php
class Model_Book_Chapter extends ORM
{
    protected $_belongs_to = array(
        'book' => array(
            'model'=> 'Book',
            'far_key'=> 'id',
            'foreign_key'=>'book_id'
        ),
    );
}