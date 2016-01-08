<?php defined('SYSPATH') or die('No direct script access.');

class Model_Metadata extends Model
{
    public $title = '';
    public $description = '';
    public $charset = 'utf-8';

    public function title($title, $append = true, $separator = ' · ')
    {
        $title = __($title);
        $this->title = $append?($title . $separator . $this->title):$title;
        return $this;
    }

    public function description($description)
    {
       //$description = __($description);
        $this->description = strip_tags($description);
        return $this;
    }

    public function snippet($text='')
    {
        $description='';
        $chars=array('.', '!', '?', ':', '"');
         if( !empty($text) ) {
             $text=strip_tags($text);
             $arr=explode(' ',$text);
             foreach($arr as $k=>$v) {
                 if(!empty($v)) {
                     $countdescription=UTF8::strlen($description);
                     $countword=UTF8::strlen($v);
                     if( ( ($countdescription-1)+($countword) )>140 ){
                         break;
                     }
                     else {
                         $description.=$v. ' ';
                     }
                 }
             }
             $description=rtrim($description);
             if (!empty($description))
             {
                $lastchar=$description[UTF8::strlen($description)-1];

                 if($lastchar==',') {
                     $description=UTF8::substr($description, 0, UTF8::strlen($description)-1);
                 }

                 if(!in_array($lastchar, $chars)) {
                     $description.='...';
                 }
             }
         }
        $this->description = $description;
        return $this;
    }
}
