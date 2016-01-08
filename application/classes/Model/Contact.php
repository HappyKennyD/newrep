<?php
class Model_Contact extends ORM
{
    protected $_i18n_fields = array('name');

    protected $_has_many = array(
        'attach'    => array(
            'model'       => 'Contacts_Attachment',
            'foreign_key' => 'contact_id',
        )
    );

    public function filters()
    {
        return array(
            'name_en' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'name_ru' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
            'name_kz' => array(
                array('str_replace', array('&nbsp;', ' ', ':value'))
            ),
        );
    }

}