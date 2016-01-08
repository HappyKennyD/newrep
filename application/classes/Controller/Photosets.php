<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Photosets extends Controller_Core {

	public function action_index()
	{
        /*$year = (int)$this->request->param('year', 0);
        $list = Security::xss_clean($this->request->param('list', '0'));

        $attach = ORM::factory('Photosets_Attachment')->find_all();
        $count = count($attach);

        $rand = array();
        $ar = array();
        while (count($rand) < 15) {
            $r=rand(0, $count);
            if (!in_array($r, $ar))
            {
                $rand[] = ORM::factory('Photosets_Attachment')->limit(1)->offset($r)->find();
                $ar[] = $r;
            }
        }
        $photosets = ORM::factory('Photoset')->where('published','=', 1);
        if ($year > 0)
        {
            $photosets = $photosets->where(DB::expr('YEAR(date)'), '=', $year);
        }
        $photosets->order_by('date', 'DESC');
        $paginate = Paginate::factory($photosets)->paginate(NULL, NULL, 15)->render();
        $photosets = $photosets->find_all();

        $this->set('photosets', $photosets)->set('rand', $rand);
        $this->set('paginate', $paginate);
        if ($list == 'list')
        {
            $this->set('list', $list);
        }
        if ($year > 0)
        {
            $this->set('year', $year);
        }
        $this->add_cumb('Photosets','/');*/

        $category = (int) $this->request->param('category', 0);
        //SEO. закрываем сортировку
        if ($category!=0)
        {
            $sort=1;
            Kotwig_View::set_global('sort',$sort);
        }
        //end_SEO
        $categories = ORM::factory('Photosets_Category')->find_all();
        $this->set('categories', $categories)->set('category', $category);

        $photosets = ORM::factory('Photoset')->where('published','=', 1)
            ->where('show_'.$this->language, '=', 1)->order_by('order','asc');
        if ($category != 0)
        {
            $photosets = $photosets->where('category_id', '=', $category);
            $this->add_cumb('Photosets','photosets');
            $cat =  ORM::factory('Photosets_Category', $category);
            $this->add_cumb($cat->name,'/');
        }
        else{
            $this->add_cumb('Photosets','/');
        }
        $photosets = $photosets->order_by('order', 'ASC');
        $paginate = Paginate::factory($photosets)->paginate(NULL, NULL, 12)->render();
        $photosets = $photosets->find_all();
        if (count($photosets)<1)
        {
            $this->set('error', I18n::get('Error photo'));
        }
        $this->set('photosets', $photosets)->set('paginate', $paginate);

	}

    public function action_view()
    {
        $id = (int) $this->request->param('id', 0);
        $photoset = ORM::factory('Photoset')->where('id', '=', $id)->where('show_'.$this->language, '=', 1)->find();
        if (!$photoset->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $attach = $photoset->attach->order_by('order','asc')->find_all();
        $this->add_cumb('Photosets','photosets')->set('attach', $attach);
        $this->add_cumb($photoset->name,'/');
        $this->set('photoset', $photoset);
    }

}
