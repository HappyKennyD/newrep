<?php defined('SYSPATH') or die('No direct script access.');

class Controller_170let extends Controller_Core {

    public function action_index()
    {
        $ent = ORM::factory('Test_variant')->where('published', '=', 1)->find_all();
        $this->add_cumb('Игра «Правда или ложь»', '');
        $this->set('ent', $ent);
    }

    public function action_test()
    {
        $id = (int) $this->request->param('id', 0);
        $ent = ORM::factory('Test_variant', $id);
        if (!$ent->loaded())
        {
            throw new HTTP_Exception_404;
        }
        $this->set('ent', $ent);

        $range = range('A', 'E');
        $this->set('range', $range);

        /*$quests = ORM::factory('Test_Quests')->where('test_variant_id', '=', $ent->id)->and_where('published', '=', 1)->order_by(NULL,'RAND()')->find_all();*/
$sql = "SELECT DISTINCT test_quests.* 
FROM test_quests
WHERE test_quests.test_variant_id=3 
AND test_quests.published = 1 
AND test_quests.language = \"".$this->language."\"
ORDER BY RAND() 
LIMIT 15";
$quests = DB::query(Database::SELECT, $sql)->as_object()->execute();
        $q = array();
        $i = 0;
        foreach ($quests as $quest)
        {
            $q[$i]['quest'] = $quest;
//            $q[$i]['variants'] = ORM::factory('Quest_Variant')->where('quest_id', '=', $quest->id)->and_where('published', '=', 1)->order_by('number')->find_all();
            $i++;
        }

        $this->set('q', $q);

        $this->add_cumb('Игра «Правда или ложь»', '');
    }
}
