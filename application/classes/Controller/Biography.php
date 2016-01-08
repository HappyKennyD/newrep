<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Biography extends Controller_Core {

    public function action_index()
    {
        $alphabet = array(
            'ru'=>array('А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'),
            'kz'=>array('А','Ә','Б','В','Г','Ғ','Д','Е','Ё','Ж','З','И','Й','К','Қ','Л','М','Н','Ң','О','Ө','П','Р','С','Т','У','Ү','Ұ','Ф','Х','Һ','Ц','Ч','Ш','Щ','Ъ','Ы','I','Ь','Э','Ю','Я'),
            'en'=>array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'));
        $lang = Security::xss_clean($this->request->param('language'));
        foreach($alphabet[$lang] as $alpha)
        {
            $biog = ORM::factory('Biography')->where('published','=', 1)
                ->where_open()->where('name_'.$lang, 'like', $alpha.'%')->or_where('name_'.$lang, 'like', '% '.$alpha.'%')->where_close()
                ->find();
            if ($biog->loaded())
            {
                $alphabet_new[] = $alpha;
            }
        }
        $this->set('alphabet', $alphabet_new);

        $categories1 =  ORM::factory('Biography_Category')->where('era', '=', '1')->find_all();
        $halyk_kaharmany = ORM::factory('Biography_Category',9);
        $this->set('halyk_kaharmany',$halyk_kaharmany);
        $categories2 =  ORM::factory('Biography_Category')->where('era', '=', '2')->find_all();
        $this->set('categories1', $categories1)->set('categories2', $categories2);

        $category = (int)($this->request->param('category', 0));
        $alpha = Security::xss_clean($this->request->param('alpha', ""));
        //SEO. закрываем сортировку
        if ($alpha!='')
        {
            $sort=1;
            Kotwig_View::set_global('sort',$sort);
        }
        //end_SEO
        $biography = ORM::factory('Biography')->where('published','=', 1)->where('name_'.$this->language, '<>', '');
        if ($category != 0)
        {
                $biography = $biography->where('category_id', '=', $category);
                $this->add_cumb('Personalia','biography');
                $cat =  ORM::factory('Biography_Category', $category);
                $this->add_cumb($cat->title,'/');
        }
        else
        {
            $biography = $biography->where('category_id', 'NOT IN', array(3,4,6,7,8,15));
            $this->add_cumb('Personalia','/');
        }
        if (!empty($alpha))
        {
            $biography = $biography->where_open()->where('name_'.$lang, 'like', $alpha.'%')->or_where('name_'.$lang, 'like', '% '.$alpha.'%')->where_close();
        }
        $biography = $biography->order_by('order');
        $paginate = Paginate::factory($biography)->paginate(NUll, NULL, 10)->render();
        $biography = $biography->find_all();
        if (count($biography) == 0)
        {
            $this->set('error', I18n::get('Sorry.'));
        }

        /* метатэг description */
        $biography_meta = ORM::factory('Page')->where('key','=', 'biography_' . $category .'_1')->find();
        $this->metadata->description($biography_meta->description);


        $this->set('list', $biography)->set('paginate', $paginate)->set('category', $category)->set('alpha', $alpha);
    }

    public function action_view()
    {
        $id = (int) $this->request->param('id', 0);
        $id_pub = array('id'=>$id,'published'=>1);
	//$biography = ORM::factory('Biography', $id);
	$biography = ORM::factory('Biography', $id_pub);
        if (!$biography->loaded())
        {
            throw new HTTP_Exception_404;
        }
        if (!$biography->translation())
        {
            throw new HTTP_Exception_404('no_translation');
        }
        $read = $biography->attach->where('key', '=', 'read')->find_all();
        if (count($read)>0)
        {
            $this->set('read', $read);
        }
        $references = $biography->attach->where('key', '=', 'references')->find_all();
        if (count($references)>0)
        {
            $this->set('references', $references);
        }
        $this->add_cumb('Personalia','biography');
        $this->add_cumb($biography->name,'/');
        $this->set('item', $biography);

        $metadesc=$biography->desc;
        if(!empty($metadesc)) {
            $this->metadata->description($metadesc);
        }
        else {
            $this->metadata->snippet($biography->text);
        }
    }

}
