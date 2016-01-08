<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Smartscorm extends Controller_Api_Core
{

    public function action_index()
    {
        header('Access-Control-Allow-Origin: *');
		$courses = ORM::factory('Education')->where('language', '=', $this->language)->where('published','=', 1)->order_by('number');
        $paginate = Paginate::factory($courses)->paginate(NULL, NULL, 30)->page_count();
        $courses = $courses->find_all();

        foreach($courses as $k=>$v)
        {
            $this->data['list'][] = array(
                'id' => $v->id,
                'title' => $v->title,
                'url' => URL::site('/'.$this->language.'/api/smartscorm/view/'.$v->id,true)
            );
        }

        $this->date['pagecount'] = $paginate;

        $this->response->body(json_encode($this->data));
    }

    public function action_view()
    {
        header('Access-Control-Allow-Origin: *');
		$id = (int)$this->request->param('id', 0);

        $material = ORM::factory('Education', $id);

        if (!$material->loaded())
        {
            $this->data['error'] = '404 Not found';
            $this->response->body(json_encode($this->data));
            return;
        }

        $materials = $material->materials->find_all();

        $this->data = array(
            'id' => $material->id,
            'title' => $material->title,
        );
        foreach ($materials as $k=>$v)
        {
            $this->data['materials'][] = array(
                'type' => $v->type,
                'path' => URL::media('media/scorm/'.$material->id . '/' . $v->path,true),
                'title' => $v->title
            );
        }


        $this->response->body(json_encode($this->data));


    }
}