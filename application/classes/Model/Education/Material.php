<?php
class Model_Education_Material extends ORM
{
    protected $_belongs_to = array(
        'course' => array('model' => 'Education'),
    );
}