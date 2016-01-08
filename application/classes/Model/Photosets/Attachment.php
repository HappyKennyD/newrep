<?php
class Model_Photosets_Attachment extends ORM
{
    protected $_i18n_fields = array('name');

    protected $_belongs_to = array(
        'photo' => array(
            'model'=> 'Storage',
            'far_key'=> 'id',
            'foreign_key'=>'storage_id'
        )
    );

    public function GetDate($id)
    {
        $photoset = ORM::factory('Photoset', $id);
        return $photoset->date;
    }
}