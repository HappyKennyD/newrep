<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Smartpersonalii extends Controller_Api_Core
{

    public function action_index()
    {
        header('Access-Control-Allow-Origin: *');
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

        $this->data['alphabet'] = $alphabet_new;



        $categories1 =  ORM::factory('Biography_Category')->where('era', '=', '1')->find_all();
        $halyk_kaharmany = ORM::factory('Biography_Category',9);

        foreach($categories1 as $k=>$v)
        {
            $cat = array();
            $cat['id'] = $v->id;
            $cat['title'] = $v->title;
            $this->data['categories1'][] = $cat;

        }
        $this->data['halyk_kaharmany'] = array(
            'id' => $halyk_kaharmany->id,
            'title' => $halyk_kaharmany->title
        );

        $category = (int)($this->request->param('id', 0));

        $biography = ORM::factory('Biography')->where('published','=', 1)->where('name_'.$this->language, '<>', '');
        if ($category != 0)
        {
            $biography = $biography->where('category_id', '=', $category);

            $cat =  ORM::factory('Biography_Category', $category);

        }
        else
        {
            $biography = $biography->where('category_id', 'NOT IN', array(3,4,6,7,8,15));

        }

        $alpha = Security::xss_clean($this->request->param('category', ""));
        //SEO. закрываем сортировку
        if ($alpha!='')
        {
            $sort=1;
            Kotwig_View::set_global('sort',$sort);
        }
        //end_SEO

        if (!empty($alpha))
        {
            $biography = $biography->where_open()->where('name_'.$lang, 'like', $alpha.'%')->or_where('name_'.$lang, 'like', '% '.$alpha.'%')->where_close();
        }
        $biography = $biography->order_by('order');
        $paginate = Paginate::factory($biography)->paginate(NUll, NULL, 10)->page_count();
        $biography = $biography->find_all();
        if (count($biography) == 0)
        {
            $this->data['error'] = I18n::get('Sorry.');
        }
        else{
            foreach($biography as $k=>$v)
            {
                $bio = array();
                $bio['id'] = $v->id;
                $bio['name'] = $v->name;
                $bio['image'] = URL::media('/images/w140-h187-cct-si/'.$v->picture->file_path, true);
                $bio['date_start'] = $v->getYear('date_start');
                $bio['date_finish'] =  $v->getYear('date_finish');
                $bio['desc'] = $v->desc;
                $bio['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->language.URL::site('api/smartpersonalii/view/'.$v->id);
                $this->data['list'][] = $bio;
            }
        }

        $this->data['pagecount'] = $paginate;
        $this->data['alpha'] = $alpha;
        $this->data['category'] = $category;

        $this->response->body(json_encode($this->data));
    }

    public function action_view()
    {
        header('Access-Control-Allow-Origin: *');
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
            foreach($read as $k=>$v)
            {
                $this->data['read'][] = array(
                    'num' => $k,
                    'title' => $v->title,
                    'link' => $v->link
                );
            }
        }
        $references = $biography->attach->where('key', '=', 'references')->find_all();
        if (count($references)>0)
        {
            foreach($references as $k=>$v)
            {
                $this->data['references'][] = array(
                    'num' => $k,
                    'title' => $v->title,
                    'link' => $v->link
                );
            }

        }

        $this->data['item'] = array(
            'id' => $biography->id,
            'desc' => $biography->desc,
            'text' => $biography->text,
            'image' => URL::media('/images/w220-h294-cct-si/'.$biography->picture->file_path, TRUE),
            'name' => $biography->name,
            'category' => $biography->category->title,
            'date_start' => $biography->date_start,
            'date_finish'=> $biography->date_finish,
            'birthplace' => $biography->birthplace,
            'deathplace' => $biography->deathplace,


        );

        $this->response->body(json_encode($this->data));
    }
}
