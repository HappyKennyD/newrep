<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Test extends Controller_Core
{

    public function action_index()
    {
        /* $sql="ALTER TABLE `callings` CHANGE `title` `title_ru` VARCHAR( 500 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;";

         DB::query(NULL,$sql)->execute();

         $sql="ALTER TABLE `callings` ADD `title_kz` VARCHAR( 500 ) NOT NULL AFTER `title_ru` ,
 ADD `title_en` VARCHAR( 500 ) NOT NULL AFTER `title_kz` ;";

         DB::query(NULL,$sql)->execute();*/

        //->and_where('show_' . $this->language, '=', 1)->and_where('published', '=', 1)->find_all();

        /*$search_string='Новый заголовок для супер точности';
        $search_string= Security::xss_clean(mysql_real_escape_string($search_string));

        $sql="SELECT books.*,
              MATCH (title, author, description) AGAINST ('$search_string') AS relevance
              FROM books
              WHERE MATCH (title, author, description) AGAINST ('$search_string')
              AND show_$this->language = '1'
              AND published = '1'
              ORDER BY relevance DESC";

        $result=DB::query(Database::SELECT,$sql)->as_object()->execute();

        foreach ($result as $item){
            var_dump($item->title.' '.$item->relevance);
        }*/
        //exit;
        if ($this->request->post()){
            $name_ru=arr::get($_POST,'name_ru');

            $chronologies_lvl1=ORM::factory('Chronology')->where('parent_id','=',$name_ru)->find_all();
            $i=0;

            foreach ($chronologies_lvl1 as $item){
                $po=ORM::factory('Chronology_Line')->where('period_id','=',$item->id)->and_where('title_ru','<>','')->find_all();
                if ($po->count()!=0){
                    $i=$i+$po->count();
                }
            }

            echo('ru '.$i.'<br>');

            $chronologies_lvl1=ORM::factory('Chronology')->where('parent_id','=',$name_ru)->find_all();
            $i=0;

            foreach ($chronologies_lvl1 as $item){
                $po=ORM::factory('Chronology_Line')->where('period_id','=',$item->id)->and_where('title_kz','<>','')->find_all();
                if ($po->count()!=0){
                    $i=$i+$po->count();
                }
            }

            echo('kz '.$i.'<br>');

            $chronologies_lvl1=ORM::factory('Chronology')->where('parent_id','=',$name_ru)->find_all();
            $i=0;

            foreach ($chronologies_lvl1 as $item){
                $po=ORM::factory('Chronology_Line')->where('period_id','=',$item->id)->and_where('title_en','<>','')->find_all();
                if ($po->count()!=0){
                    $i=$i+$po->count();
                }
            }

            echo('en '.$i.'<br>');

           /* $type=arr::get($_POST,'type');

            if (isset($type)){
                $name=ORM::factory('Page')->where('name_ru','=',$name_ru)->find();
            }else{
                $name=ORM::factory('Page')->where('name_ru','LIKE','%'.$name_ru.'%')->find();
            }

            //$name=ORM::factory('Page')->where('name_ru','LIKE','%'.$name_ru.'%')->find();
            $id=$name->id;
            echo('Название: '.$name->name_ru.'<br>');
            echo('ID: '.$id.'<br>'.'<br>');

            //$sql="SELECT * FROM `pages_contents` WHERE `date`<'2014-02-28 00:00:00' AND `page_id`='$id' AND `title_ru`<>'';";
            $result=ORM::factory('Pages_Content')->where('date','<=','2014-02-28 23:59:59')->and_where('page_id','=',$id)->and_where('title_ru','<>','')->find_all();
            echo ("ru =".$result->count().'<br>');

            //$sql="SELECT * FROM `pages_contents` WHERE `date`<'2014-02-28 00:00:00' AND `page_id`='$id' AND `title_kz`<>'';";
            $result=ORM::factory('Pages_Content')->where('date','<=','2014-02-28 23:59:59')->and_where('page_id','=',$id)->and_where('title_kz','<>','')->find_all();
            echo ("kz =".$result->count().'<br>');

            //$sql="SELECT * FROM `pages_contents` WHERE `date`<'2014-02-28 00:00:00' AND `page_id`='$id' AND `title_en`<>'';";
            $result=ORM::factory('Pages_Content')->where('date','<=','2014-02-28 23:59:59')->and_where('page_id','=',$id)->and_where('title_en','<>','')->find_all();
            echo ("en =".$result->count().'<br>'.'<br>');

            //$sql="SELECT * FROM `pages_contents` WHERE `date`<'2014-02-28 00:00:00' AND `page_id`='$id' AND `title_ru`<>'' AND `published`='1';";
            $result=ORM::factory('Pages_Content')->where('date','<=','2014-02-28 23:59:59')->and_where('page_id','=',$id)->and_where('title_ru','<>','')->and_where('published','=','1')->find_all();
            echo ("ru published =".$result->count().'<br>');

            //$sql="SELECT * FROM `pages_contents` WHERE `date`<'2014-02-28 00:00:00' AND `page_id`='$id' AND `title_kz`<>'' AND `published`='1';";
            $result=ORM::factory('Pages_Content')->where('date','<=','2014-02-28 23:59:59')->and_where('page_id','=',$id)->and_where('title_kz','<>','')->and_where('published','=','1')->find_all();
            echo ("kz published =".$result->count().'<br>');

            //$sql="SELECT * FROM `pages_contents` WHERE `date`<'2014-02-28 00:00:00' AND `page_id`='$id' AND `title_en`<>'' AND `published`='1';";
            $result=ORM::factory('Pages_Content')->where('date','<=','2014-02-28 23:59:59')->and_where('page_id','=',$id)->and_where('title_en','<>','')->and_where('published','=','1')->find_all();
            echo ("en published =".$result->count().'<br>'.'<br>');*/
        }

        //$id= $this->request->param('id',0);



    }

}
