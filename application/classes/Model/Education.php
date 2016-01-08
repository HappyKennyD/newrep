<?php
class Model_Education extends ORM
{
    protected $_has_many = array(
        'materials' => array(
            'model' => 'Education_Material',
        )
    );
}