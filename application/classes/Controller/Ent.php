<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Ent extends Controller_Core {

	public function action_index()
	{
        $ent = ORM::factory('Ent')->where('published', '=', 1)->where('language','=',$this->language)->find_all();
        $this->add_cumb('ENT', '');
        $this->set('ent', $ent);
	}

    public function action_test()
    {
        $id = (int) $this->request->param('id', 0);
        $ent = ORM::factory('Ent', $id);
        if (!$ent->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $this->set('ent', $ent);

        $range = range('A', 'E');
        $this->set('range', $range);

        $quests = ORM::factory('Ent_Quest')->where('ent_id', '=', $ent->id)->and_where('published', '=', 1)->order_by('number')->find_all();
        $q = array();
        $i = 0;
        foreach ($quests as $quest)
        {
            $q[$i]['quest'] = $quest;
            $q[$i]['variants'] = ORM::factory('Quest_Variant')->where('quest_id', '=', $quest->id)->and_where('published', '=', 1)->order_by('number')->find_all();
            $i++;
        }

        $this->set('q', $q);

        $this->add_cumb('ENT', 'ent');
        $this->add_cumb($ent->title, '');
    }
}
