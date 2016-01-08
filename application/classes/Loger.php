<?php defined('SYSPATH') or die('No direct script access.');
class Loger
{
    protected $event;
    protected $title;

    public function __construct($event = 'create', $title = '')
    {
        $this->event = $event;
        $this->title = $title;
    }

    public function logThis($object)
    {
        $fields = array('title' => 'title', 'desc' => 'desc', 'text' => 'text');
        $count  = 0;

        $class = get_class($object);

        if ($class == 'Model_Pages_Content' || $class == 'Model_Expert_Opinion' || $class == 'Model_Chronology_Line')
        {
            $fields['desc'] = 'description';
        }
        if ($class == 'Model_Biography' || $class == 'Model_Point')
        {
            $fields['title'] = 'name';
        }
        if ($class == 'Model_Exhibit')
        {
            $fields['desc'] = 'description';
            $fields['text'] = 'published';
        }

        foreach ($fields as $field)
        {
            $count = $count + $this->countThis($object->$field);
        }

        $log             = ORM::factory('Log');
        $log->user_id    = Auth::instance()->get_user()->id;
        $log->model      = $class;
        $log->content_id = $object->id;
        $log->title      = $this->title;
        $log->event      = $this->event;
        $log->language   = I18n::$lang;
        $log->date       = date('Y-m-d H:i:s', time());
        $log->count      = $count;
        $log->save();
    }

    public function log($object)
    {
        $class = get_class($object);

        if ($class == 'Model_Video' || $class == 'Model_Photoset' || $class == 'Model_Infograph' || $class == 'Model_Slider' || $class == 'Model_Audio' || $class == 'Model_Book')
        {
            $log             = ORM::factory('Log');
            $log->user_id    = Auth::instance()->get_user()->id;
            $log->model      = $class;
            $log->event      = $this->event;
            $log->title      = $this->title;
            $log->content_id = $object->id;
            $log->language   = I18n::$lang;
            $log->date       = date('Y-m-d H:i:s', time());
            $log->count      = 0;
            $log->save();
        }
    }

    private function countThis($string)
    {
        $string = strip_tags($string);
        $e      = explode(" ", $string);
        foreach ($e as $k => $word)
        {
            if (UTF8::strlen($word) < 2)
            {
                unset($e[$k]);
            }
        }

        return count($e);
    }
}