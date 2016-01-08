<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Smartphoto extends Controller_Api_Core
{

    public function action_index()
    {
        header('Access-Control-Allow-Origin: *');
		$category = (int) $this->request->param('id', 0);
        //SEO. закрываем сортировку
        if ($category!=0)
        {
            $sort=1;
            Kotwig_View::set_global('sort',$sort);
        }
        //end_SEO
        $categories = ORM::factory('Photosets_Category')->find_all();

        foreach ($categories as $c)
        {
            $this->data['categories'][] = array(
                'id' => $c->id,
                'name'=>$c->name
            );
        }

        $this->data['category'] = $category;

        $photosets = ORM::factory('Photoset')->where('published','=', 1)
            ->where('show_'.$this->language, '=', 1)->order_by('order','asc');
        if ($category != 0)
        {
            $photosets = $photosets->where('category_id', '=', $category);

        }

        $photosets = $photosets->order_by('order', 'ASC');
        $paginate = Paginate::factory($photosets)->paginate(NULL, NULL, 12)->page_count();
        $photosets = $photosets->find_all();
        if (count($photosets)<1)
        {
            $this->data['error'] = 'Error photo';
        }

        $this->data['pagecount'] = $paginate;
        foreach($photosets as $p)
        {

            $img = ($p->isCover()?'http://'.$_SERVER['HTTP_HOST'].URL::media('images/w300-h225-ccc-si/'.$p->pathCover()):'http://'.$_SERVER['HTTP_HOST'].URL::media('images/w300-h225-ccc-si/media/images/nocover.jpg'));
            $this->data['photosets'][] = array(
                'url' => 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->language.URL::site('api/smartphoto/view/'.$p->id),
                'image' => $img,
                'name' => $p->name(),
                'category' => $p->category->name,

            );
        }

        $this->response->body(json_encode($this->data));
    }

    public function action_view()
    {
        header('Access-Control-Allow-Origin: *');
		$id = (int) $this->request->param('id', 0);
        $photoset = ORM::factory('Photoset')->where('id', '=', $id)->where('show_'.$this->language, '=', 1)->find();
        if (!$photoset->loaded())
        {
            $this->data['error'] = '404 Not Found';
            $this->response->body(json_encode($this->data));
            return;
        }
        $attach = $photoset->attach->order_by('order','asc')->find_all();

        $this->data['name'] = $photoset->name();

        foreach($attach as $a) {
            $this->data['photos'][] = array(
                'name' => $a->name,
                'thumb' => 'http://'.$_SERVER['HTTP_HOST'].URL::media('images/w184-h184-ccc-si/'.$a->photo->file_path),
                'full' => 'http://'.$_SERVER['HTTP_HOST'].URL::media($a->photo->file_path),
            );
        }

        $this->response->body(json_encode($this->data));
    }
}
