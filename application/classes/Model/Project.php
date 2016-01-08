<?php
class Model_Project extends ORM
{
    protected $_table_name = 'project';

    protected $_i18n_fields = array('name', 'text', 'desc');

    protected $_belongs_to = array(
        'picture' => array(
            'model'=> 'Storage',
            'far_key'=> 'id',
            'foreign_key'=>'image'
        )
    );

    public function translation()
    {
   /*     if ($this->name == '' or $this->text == '' or $this->desc=='')
        {
            return FALSE;
        }
        return TRUE;*/
    }
}