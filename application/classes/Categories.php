<?php defined('SYSPATH') OR die('No direct access allowed.');
class Categories {

    public $code;
    protected $names;

    public function category($array)
    {
        foreach ($array as $value){
            $this->names[$value->id] = $value;
            $this->parents[$value->id] = $value->parent_id;
        }
        if (count($array)>0)
            $this->build();
        return $this->code;
    }

    protected function build($id=0)
    {
        if($id)
            $this->code[]= $this->names[$id];
        foreach ($this->parents as $PerentId => $PerentParentId)
            if($PerentParentId == $id)
                $this->build($PerentId);
    }
}