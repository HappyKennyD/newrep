<?php defined('SYSPATH') or die('No direct script access.');

class Model_Pages_Content extends ORM {

    protected $_i18n_fields = array('title', 'description', 'text');

    protected $_belongs_to = array(
        'page' => array(
            'model'         => 'Page',
            'far_key'       => 'id',
            'foreign_key'   => 'page_id'
        ),
        'picture' => array(
            'model'=> 'Storage',
            'far_key'=> 'id',
            'foreign_key'=>'image'
        )
    );

    public function translation()
    {
        if (strip_tags($this->title) == '' or strip_tags($this->text) == '')
        {
            return FALSE;
        }
        return TRUE;
    }
}
