<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Photos extends Controller_Core {

	public function action_index()
	{
        $photos = ORM::factory('Storage')->where('publication_id', '>', '0')->find_all();

        foreach ($photos as $photo)
        {
            if ($photo->publication_type == 'news')
            {
                $news = ORM::factory('News',$photo->publication_id);
                if ($news->loaded())
                {
                    $title = $news->title;
                }
            }
            elseif ($photo->publication_type == 'leader')
            {
                $page = ORM::factory('Leader',$photo->publication_id);
                if ($page->loaded()) $title = $page->name;
            }
            else
            {
                $page = ORM::factory('Pages_content',$photo->publication_id);
                if ($page->loaded()) $title = $page->name;
            }

            if (!isset($title)) $title = I18n::get("This publication is absent");
            $photo_arr[] = array('date'=>$photo->date, 'path'=>$photo->file_path,
                'publication_id'=>$photo->publication_id, 'type'=>$photo->publication_type, 'title'=>$title);
        }

        $this->set('photos', $photo_arr);
        $this->add_cumb('Photos','/');
	}

}