<?php

class Model_Exhibit_Album extends ORM{
    protected $_i18n_fields = array('description', 'title','text');
    protected $_belongs_to = array('image'=> array(
        'model' => 'Storage',
        'far_key' => 'id',
        'foreign_key' => 'image_storage_id'
    ));
}