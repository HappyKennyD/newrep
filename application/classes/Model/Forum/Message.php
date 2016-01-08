<?php defined('SYSPATH') or die('No direct script access.');

class Model_Forum_Message extends ORM {

    public function filters()
    {
        return array(
            TRUE => array(
                array('trim'),
                array('Security::xss_clean')
            ),
        );
    }

    public function labels()
    {
        return array(
            'text'   => 'Сообщение',
        );
    }

    public function rules()
    {
        return array(
            'text' => array(
                array('not_empty'),
                array('min_length', array(':value', 50)),
               // array('max_length', array(':value', 500))
            )
        );
    }

    protected $_belongs_to = array(
        'user' => array(
            'model'         => 'User',
            'far_key'       => 'id',
            'foreign_key'   => 'user_id'
        ),
        'forum' => array(
            'model'         => 'Forum',
            'far_key'       => 'id',
            'foreign_key'   => 'section_id'
        ),
        'theme' => array(
            'model'         => 'Forum_Theme',
            'far_key'       => 'id',
            'foreign_key'   => 'theme_id'
        )
    );


    public function count_messages_user($id)
    {
        return $this->where('user_id','=', $id)->count_all();
    }

    /*
     * Возможность редактирования своего сообщения пользователю в течении 30 мин
     */
    public function ability_edit($id, $time=600)
    {
        $date=$this->where('id','=', $id);
        $date_create=strtotime($date->date);
        $date_now=strtotime( date('Y-m-d H:i:s') );
        if (abs($date_now-$date_create) > $time)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}