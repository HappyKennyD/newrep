<?php defined('SYSPATH') or die('No direct script access.');
class Controller_Api_Smartent extends Controller_Api_Core
{

    public function action_index()
    {
        header('Access-Control-Allow-Origin: *');
		$ent = ORM::factory('Ent')->where('published', '=', 1)->where('language','=',$this->language)->find_all();

        foreach ($ent as $k=>$v)
        {
            $this->data['list'][] = array(
                'id' => $v->id,
                'title' => $v->title,
                'url' => URL::site('/'.$this->language.'/api/smartent/view/'.$v->id,true)
            );
        }

        $this->response->body(json_encode($this->data));
    }

    public function action_view()
    {
        header('Access-Control-Allow-Origin: *');
		$id = (int) $this->request->param('id', 0);
        $ent = ORM::factory('Ent', $id);
        if (!$ent->loaded())
        {
            $this->data['error'] = '404 Not found';
            $this->response->body(json_encode($this->data));
            return;
        }

        $this->data[] = array(
            'id' => $ent->id,
            'title' => $ent->title,
        );


        $range = range('A', 'E');
        $this->data['range'] = $range;

        $quests = ORM::factory('Ent_Quest')->where('ent_id', '=', $ent->id)->and_where('published', '=', 1)->order_by('number')->find_all();
        $q = array();

        foreach ($quests as $quest)
        {
            $q = array();
            $q['quest'] = array(
                'text' => $quest->text
            );

            $variants = ORM::factory('Quest_Variant')->where('quest_id', '=', $quest->id)->and_where('published', '=', 1)->order_by('number')->find_all();
            foreach ($variants as $v)
            {
                $q['variants'][] = array(
                    'text' => $v->text,
                    'right' => $v->right,
                );
            }

            $this->data['quests'][] = $q;

        }

        $this->response->body(json_encode($this->data));


    }
}