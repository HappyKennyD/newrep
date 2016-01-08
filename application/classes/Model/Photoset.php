<?php
class Model_Photoset extends ORM
{
    protected $_i18n_fields = array('name');

    protected $_has_many = array(
        'attach'    => array(
            'model'       => 'Photosets_Attachment',
            'foreign_key' => 'photoset_id',
        )
    );

    protected $_belongs_to = array(
        'category' => array(
            'model'         => 'Photosets_Category',
            'far_key'       => 'id',
            'foreign_key'   => 'category_id'
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

    public function pathCover()
    {
        $cover = $this->attach->where('main', '=', 1)->find();
        if ($cover->loaded())
        {
            return $cover->photo->file_path;
        }
        else
        {
            $cover = $this->attach->order_by('id')->find();
            return $cover->photo->file_path;
        }

    }

    public function isCover()
    {
        $cover = $this->attach->where('main', '=', 1)->find();
        if ($cover->loaded())
        {
            return true;
        }
        else
        {
            return false;
        }

    }

    public function countphoto()
    {
        $attach = $this->attach->find_all();
        $count = count($attach);
        return $count;
    }

    public function name()
    {
        if ($this->name <> '')
        {
            return $this->name;
        }
        elseif ($this->name_ru <> '')
        {
            return $this->name_ru;
        }
        elseif ($this->name_kz <> '')
        {
            return $this->name_kz;
        }
        elseif ($this->name_en <> '')
        {
            return $this->name_en;
        }
        else
        {
            return false;
        }

    }

}