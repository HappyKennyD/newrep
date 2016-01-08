<?php

defined('SYSPATH') or die('No direct script access.');

class Model_Calling extends ORM
{
    protected $_table_name = 'callings';

    protected $_i18n_fields = array('title');

    protected $_belongs_to = array(
        'file_pdf' => array(
            'model' => 'Storage',
            'foreign_key' => 'storage_id',
        ),

        'file_cover' => array(
            'model' => 'Storage',
            'foreign_key' => 'cover_id',
        ),
    );

    public function rules()
    {
        return array(
            'title_'.i18n::lang() => array(
                array('not_empty'),
            ),
            'storage_id' => array(
                array('not_empty'),
            )
        );
    }

}
