<?php
class Model_Infograph extends ORM
{
    protected $_i18n_fields = array('title');

    public function translation($lang)
    {
        switch($lang){
            case 'ru': if ($this->title_ru == '') {return FALSE;} break;
            case 'kz': if ($this->title_kz == '') {return FALSE;} break;
            case 'en': if ($this->title_en == '') {return FALSE;} break;
        }
        return TRUE;
    }

    protected $_belongs_to = array(
        'picture' => array(
            'model'=> 'Storage',
            'far_key'=> 'id',
            'foreign_key'=>'image'
        )
    );
}