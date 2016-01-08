<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Project extends Controller_Core {

    public function action_index()
    {
         $lang = Security::xss_clean($this->request->param('language'));

         $id_project = $this->request->param('project_id',0);
         //$c = $this->request->param('category');
         $c = (string) Arr::get($_GET, 'type', 0);
         $project = ORM::factory('Project', $id_project);
        $projects = ORM::factory('Project')->where('id', '=', $id_project);
        $projects = $projects->find_all();

        $types = ORM::factory('Material_Project')->where('project_id','=', $id_project)->find_all()->as_array(NULL ,'type');
        $this->set('types', $types);
        /*  if ( !$project->loaded() )
          {
              throw new HTTP_Exception_404;
          }*/
        $this->set('projects', $projects);


         if (empty($c)){
/*
             $ids = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','IN',array('publications','contents'))->find_all()->as_array(NULL ,'material_id');
             $ids1 = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','IN',array('publications','publicationproject'))->find_all()->as_array(NULL ,'material_id');
             //$list1 = ORM::factory('Publication')->where('id', 'IN', $ids)->where('published','=', 1)->find_all();
             //$list2 = ORM::factory('News')->where('id', 'IN', $ids)->where('published','=', 1)->find_all();
             if (empty($ids)){
                 $list2 ='';
             }
             else {
                 $list2 = ORM::factory('Pages_Content')->where('id', 'IN', $ids)->where('published','=', 1)->find_all();
             }
             if (empty($ids1)){
                 $list3='';
             }
             else{
             $list3 = ORM::factory('Publicationproject')->where('id', 'IN', $ids1)->where('published','=', 1)->find_all();
                 }
             $list = $list2;
             $this->set('list3', $list3);*/
             $c = 'contents';
         }


            if ($c == 'biography')
            {
                $ids = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','=','biography')->find_all()->as_array(NULL ,'material_id');
                $list = ORM::factory('Biography')->where('id', 'IN', $ids)->where('published','=', 1)->where('name_'.$this->language, '<>', '')->find_all();

                /*$sql = "SELECT DISTINCT biography.*

                  FROM biography, material_projects
                  WHERE material_projects.project_id = $id_project
                  AND material_projects.type = 'biography'
                  AND material_projects.material_id = biography.id
                  AND biography.published = '1'";
                /*$sql = "SELECT DISTINCT calendar.*,
                      MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search) AS relevance
                      FROM calendar
                      WHERE MATCH (title_$this->language, desc_$this->language, text_$this->language) AGAINST ($search)
                      AND published = '1'
                      ORDER BY relevance DESC";*/
                /*$list = DB::query(Database::SELECT, $sql)->as_object()->execute();
                die (var_dump($list));*/
               // die (var_dump($ids));
            }

            if ($c == 'video')
            {
                $ids = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','=','video')->find_all()->as_array(NULL ,'material_id');
                $list = ORM::factory('Video')->where('id', 'IN', $ids)->where('language','=',$this->language)->where('published','=', 1)->find_all();
            }
            if ($c == 'photo')
            {
                $ids = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','=','photosets')->find_all()->as_array(NULL ,'material_id');
                $list = ORM::factory('Photoset')->where('id', 'IN', $ids)->where('show_'.$this->language, '=', 1)->where('published','=', 1)->find_all();
            }
            if ($c == 'infographics')
            {
                $ids = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','=','infographics')->find_all()->as_array(NULL ,'material_id');
                $list = ORM::factory('Infograph')->where('id', 'IN', $ids)->where('title_'.$this->language, '<>', '')->where('published','=', 1)->find_all();
            }
            if ($c == 'contents')
            {
                $ids = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','IN',array('contents'))->find_all()->as_array(NULL ,'material_id');
                $ids1 = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','IN',array('publications'))->find_all()->as_array(NULL ,'material_id');
                $ids2 = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','IN',array('publicationproject'))->find_all()->as_array(NULL ,'material_id');
                //$list1 = ORM::factory('Publication')->where('id', 'IN', $ids)->where('published','=', 1)->find_all();
                //$list2 = ORM::factory('News')->where('id', 'IN', $ids)->where('published','=', 1)->find_all();
                if (empty($ids)){
                    $list1 ='';
                }
                else {
                    $list1 = ORM::factory('Pages_Content')->where('id', 'IN', $ids)->where('title_'.$this->language, '<>', '')->where('published','=', 1)->find_all();
                }
                if (empty($ids1)){
                    $list2='';
                }
                else{
                    $list2 = ORM::factory('Publication')->where('id', 'IN', $ids1)->where('title_'.$this->language, '<>', '')->where('published','=', 1)->find_all();
                }
                if (empty($ids2)){
                    $list3='';
                }
                else{
                    $list3 = ORM::factory('Publicationproject')->where('id', 'IN', $ids2)->where('title_'.$this->language, '<>', '')->where('published','=', 1)->find_all();
                }

                $list = $list1;
                $this->set('list2', $list2);
                $this->set('list3', $list3);


            }
            if ($c == 'books')
            {
                $ids = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','=','books')->find_all()->as_array(NULL ,'material_id');
                $list= ORM::factory('Book')->where('id', 'IN', $ids)->where('show_'.I18n::$lang, '=', 1)->where('published', '=', 1)->find_all();
              }


        $files = ORM::factory('Material_File')->where('material_id', '=', $id_project )->find_all();
        $files_test = ORM::factory('Material_File')->where('material_id', '=', $id_project )->limit(1)->find();



        $this->set('files', $files);
        $this->set('files_test', $files_test);
        $this->set('list', $list);
        $this->set('c', $c);
        $this->set('project', $project);

        $this->set('id_project',  $id_project);
        $this->set('name', $project->name);
        $this->set('desc', $project->desc);
    }

    public function action_view()
    {
        $lang = Security::xss_clean($this->request->param('language'));

        $id_project = $this->request->param('project_id',0);
        //$c = $this->request->param('category');
        $c = (string) Arr::get($_GET, 'type', 0);
        $material_id = (string) Arr::get($_GET, 'material_id', 0);
        $project = ORM::factory('Project', $id_project);
        $projects = ORM::factory('Project')->where('id', '=', $id_project);
        $projects = $projects->find_all();

        $types = ORM::factory('Material_Project')->where('project_id','=', $id_project)->find_all()->as_array(NULL ,'type');
        $this->set('material_id', $material_id);
        $this->set('types', $types);
        /*  if ( !$project->loaded() )
          {
              throw new HTTP_Exception_404;
          }*/
        $this->set('projects', $projects);


        if (empty($c)){

            $list = ORM::factory('Material_Project')->where('project_id','=', $id_project)->and_where('type','=','contents')->find_all();

        }
        else{

            if ($c == 'biography')
            {
                $list = ORM::factory('Biography')->where('id', '=', $material_id)->where('published','=', 1)->where('name_'.$this->language, '<>', '')->find_all();
            }
            if ($c == 'video')
            {
                $list = ORM::factory('Video')->where('id', '=', $material_id)->where('language','=',$this->language)->where('published','=', 1)->find_all();

                $video = ORM::factory('Video',$material_id);
                if ( ! $video->loaded())
                {
                    throw new HTTP_Exception_404;
                }

                $this->set('video', $video);


            }
            if ($c == 'photo')
            {
                $list = ORM::factory('Photoset')->where('id', '=', $material_id)->where('show_'.$this->language, '=', 1)->where('published','=', 1)->find_all();

                $photoset = ORM::factory('Photoset')->where('id', '=', $material_id)->where('show_'.$this->language, '=', 1)->find();
                if (!$photoset->loaded())
                {
                    throw new HTTP_Exception_404;
                }
                $attach = $photoset->attach->order_by('order','asc')->find_all();
                $this->set('attach', $attach);
                $this->set('photoset', $photoset);
            }
            if ($c == 'infographics')
            {
                $infograph = ORM::factory('Infograph', $material_id)->where('title_'.$this->language, '<>', '');
                $list = ORM::factory('Infograph')->where('id', '=', $material_id)->where('published','=', 1)->where('title_'.$this->language, '<>', '')->find_all();

                if (!$infograph->loaded())
                {
                    throw new HTTP_Exception_404;
                }
                if (!$infograph->translation(I18n::$lang))
                {
                    throw new HTTP_Exception_404('no_translation');
                }

                $this->set('infograph', $infograph);


            }
            if ($c == 'contents')
            {
                $list3 = ORM::factory('Pages_Content')->where('id', '=', $material_id)->where('title_'.$this->language, '<>', '')->where('published','=', 1)->find_all();
                $list = $list3;
            }
            if ($c == 'publications')
            {
                $list3 = ORM::factory('Publication')->where('id', '=', $material_id)->where('title_'.$this->language, '<>', '')->where('published','=', 1)->find_all();
                $list = $list3;
            }
            if ($c == 'public')
            {
                $list3 = ORM::factory('Publicationproject')->where('id', '=', $material_id)->where('title_'.$this->language, '<>', '')->where('published','=', 1)->find_all();
                $list = $list3;
            }
            if ($c == 'books')
            {
                $list= ORM::factory('Book')->where('id', '=', $material_id)->where('show_'.I18n::$lang, '=', 1)->where('published', '=', 1)->find_all();


                $book = ORM::factory('Book', $material_id)->where('published', '=', 1)->and_where('show_'.I18n::$lang, '=', 1);

                if (!$book->loaded())
                {
                    throw new HTTP_Exception_404;
                }


                $bookprov = ORM::factory('Book', $material_id);
                if (!$bookprov->translation(I18n::$lang))
                {
                    throw new HTTP_Exception_404('no_translation');
                    //$this->redirect(URL::media(''));
                }



                $book->views_count += 1;
                $book->save();

                $this->set('item', $book);





                if ($book->type == 'txt')
                {
                    $chapters = $book->chapters->where('published', '=', 1)->order_by('number')->find_all();
                    if (count($chapters) == 1)
                    {
                        $this->set('onechapter', $chapters[0]);
                    }
                    $this->set('chapters', $chapters);

                    $chapter = count($chapters) > 0 ? $chapters[0]->number : 0;
                    if (isset($_GET['chapter']) && intval($_GET['chapter'])>0)
                    {
                        $chapter = intval($_GET['chapter']);
                    }
                    $chapter = ORM::factory('Book_Chapter', $chapter);
                    if ($chapter->loaded())
                    {
                        $this->set('chapter', $chapter);
                    }
                }

                $most_viewed = $book->byViewsCount(5);
                $this->set('most_viewed', $most_viewed);

                $comments = ORM::factory('Comment')
                    ->where('table', '=', 'book')
                    ->and_where('status', '=', 1)
                    ->group_by('object_id')
                    ->order_by(Db::expr('SUM(status)'),'desc')
                    ->limit(5)
                    ->find_all()->as_array('id','object_id');
                if (count($comments))
                {
                    $exc_url = explode('/',$_SERVER['REQUEST_URI']);
                    $exc_url = $exc_url[count($exc_url)-1];
                    $most_comments = ORM::factory('Book')
                        ->where('id','IN', $comments)
                        ->and_where('show_'.I18n::$lang,'=',1)
                        ->and_where('id','<>',$exc_url)
                        ->find_all();
                    $this->set('most_comments', $most_comments);
                }


            }
        }

        $files = ORM::factory('Material_File')->where('material_id', '=', $id_project )->find_all();
        $files_test = ORM::factory('Material_File')->where('material_id', '=', $id_project )->limit(1)->find();



        $this->set('files', $files);
        $this->set('files_test', $files_test);
        $this->set('list', $list);
        $this->set('c', $c);
        $this->set('project', $project);

        $this->set('id_project',  $id_project);
        $this->set('name', $project->name_ru);
        $this->set('desc', $project->desc_ru);
    }

}
