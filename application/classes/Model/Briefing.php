<?php
class Model_Briefing extends ORM
{
    protected $_i18n_fields = array('title', 'desc', 'text');

    protected $_has_many = array(
        'comments'    => array(
            'model'       => 'Briefings_Comment',
            'foreign_key' => 'briefing_id',
        )
    );

    public function publComments($id)
    {
        $comments = ORM::factory('Briefings_Comment')->where('briefing_id', '=', $id)->where('published', '=', '1')->find_all();
        $count_comments = count($comments);
        return $count_comments;
    }

    public function notpublComments($id)
    {
        $comments = ORM::factory('Briefings_Comment')->where('briefing_id', '=', $id)->where('published', '=', '0')->find_all();
        $count_comments = count($comments);
        return $count_comments;
    }

}