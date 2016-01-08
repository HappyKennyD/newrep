<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Smartbriefings extends Controller_Api_Core
{

    public function action_index()
    {
		header('Access-Control-Allow-Origin: *');
	   $briefings = ORM::factory('Briefing')->where('published','=', 1)->order_by('date', 'DESC');
        $paginate = Paginate::factory($briefings)->paginate(NULL, NULL, 10)->page_count();
        $briefings = $briefings->find_all();

        foreach ($briefings as $k=>$v)
        {
            $this->data['list'][] = array(
                'id' => $v->id,
                'title' => $v->title,
                'desc' => $v->desc,
                'date' => Date::ru_date($v->date, 'd F Y'),
                'url' => URL::site('/'.$this->language.'/api/smartbriefings/view/'.$v->id,true),
            );
        }

        $this->data['pagecount'] = $paginate;


        $this->response->body(json_encode($this->data));
    }

    public function action_view()
    {
        header('Access-Control-Allow-Origin: *');
		$id = (int) $this->request->param('id', 0);
        $briefing = ORM::factory('Briefing', $id);
        if (!$briefing->loaded())
        {
            $this->data['error'] = '404 Not found';
            $this->response->body(json_encode($this->data));
            return;
        }

        $this->data[] = array(
            'id' => $briefing->id,
            'title' => $briefing->title,
            'desc' => $briefing->desc,
            'date' => Date::ru_date($briefing->date, 'd F Y'),
            'text' => $briefing->text,
            'video' => $briefing->video,
            'link' => $briefing->link
        );
        
        $this->response->body(json_encode($this->data));


    }
}