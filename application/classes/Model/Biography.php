<?php
class Model_Biography extends ORM
{
    protected $_table_name = 'biography';

    protected $_i18n_fields = array('name', 'text', 'desc', 'deathplace', 'birthplace', 'date_start', 'date_finish');

    protected $_belongs_to = array(
        'picture' => array(
            'model'=> 'Storage',
            'far_key'=> 'id',
            'foreign_key'=>'image'
        ),

        'category' => array(
            'model' => 'Biography_Category',
            'far_key' => 'id',
            'foreign_key' => 'category_id',
        )
    );

    protected $_has_many = array(
        'attach'    => array(
            'model'       => 'Biography_Attachment',
            'foreign_key' => 'biography_id',
        )
    );

    public function getYear($field = 'date_start')
    {
        if (preg_match("/\d{4}/", $this->$field, $matches))
        {
            return $matches[0];
        }
        else{
            return false;
        }
    }

    public function translation()
    {
        if ($this->name == '' or $this->text == '' or $this->desc=='')
        {
            return FALSE;
        }
        return TRUE;
    }
}