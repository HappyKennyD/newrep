<?php
class Subtool
{
    public static function findincontents($ids,$idscat){
        $contents=ORM::factory('Pages_Content')->where('id','in',$ids)->and_where('page_id','in',$idscat)->find_all()->as_array(null,'page_id');
        return $contents;
    }

    public static function findinbooks($ids,$idscat){
        $books = ORM::factory('Book')->where('id','in',$ids)->and_where('category_id','in',$idscat)->find_all()->as_array(null,'id');
        return $books;
    }

    public static function media($str){
        $domain='e-history.kz';
        //$domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        $forreturn=('http://'.$domain.$str);
       return $forreturn;
    }

    public static function multiple($number,$multiple){
        if ($number % $multiple == 0) {return true;} else {return false;}
    }

}