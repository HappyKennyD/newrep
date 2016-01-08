<?php defined('SYSPATH') or die('No direct script access.');
class Massiv extends ArrayObject
{

    public function count_all()
    {
        return count($this);
    }

    public function limit_offset($count, $start){

        $kol = $this->count_all();
        for ($i=0; $i<$kol; $i++)
            if ($i<$start || $i>=$start+$count)
                unset($this[$i]);
    }

    public function qSortDate_add($arr, $first, $last)
    {
        $i=$first;
        $j=$last;
        $x =$arr[($first+$last)/2];
        do{
            while ($arr[$i]->date_add > $x->date_add) ++$i;
            while ($arr[$j]->date_add < $x->date_add) --$j;
            if($i<=$j){
                $temp = $arr[$i];
                $arr[$i] = $arr[$j];
                $arr[$j] = $temp;
                $i++;
                $j--;
            }
        }while($i<=$j);
        if ($first<$j) $this->qSortDate_add($arr,$first,$j);
        if ($i<$last) $this->qSortDate_add($arr,$i,$last);
    }

}