<?php

class Model_Audio_Category extends ORM_MPTT
{
    protected $_i18n_fields = array('name');

    protected $_has_many = array(
        'audios' => array(
            'model' => 'Audio',
            'foreign_key' => 'category_id',
        ),
    );

    public function mama()
    {
        $cats = ORM::factory('Audio_Category')->where('parent_id', '=', $this->id)->find_all();
        if (count($cats) > 0)
        {
            return TRUE;
        }
        return FALSE;
    }

    public function tree()
    {
        $result = array();
        $result[] = $this->name;
        $parent = $this->parent_id;
        while ($parent > 0)
        {
            $cat = ORM::factory('Audio_Category', $parent);
            $result[] = $cat->name;
            $parent = $cat->parent_id;
        }
        return array_reverse($result);
    }

    public function rev()
    {
        $result = array();
        $result[] = $this->id;
        $parent = $this->parent_id;
        while ($parent > 0)
        {
            $cat = ORM::factory('Audio_Category', $parent);
            $result[] = $cat->id;
            $parent = $cat->parent_id;
        }
        return array_reverse($result);
    }
}