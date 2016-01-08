<?php
class Model_Event_Presskit extends ORM
{
    protected $_belongs_to = array(
        'event' => array(
            'model'=> 'Event',
            'far_key'=> 'event_id',
            'foreign_key'=>'id',
        ),
        'storage' => array(
            'model'=> 'Storage',
            'far_key'=> 'id',
            'foreign_key'=>'storage_id',
        ),
    );

    public function rules()
    {
        return array(
            'storage_id' => array(
                array('not_empty')
            ),
            'event_id' => array(
                array('not_empty')
            ),
        );
    }
}