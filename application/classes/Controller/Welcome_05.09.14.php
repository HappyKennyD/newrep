<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Controller_Core
{

    public function action_index()
    {
        $publications_slider = ORM::factory('Publication')->where('title_' . I18n::$lang,'<>','')->where('published', '=', 1)->and_where('is_slider', '=', 1)->order_by('order', 'desc')->limit(4)->find_all()->as_array('id');
        $this->set('publications_slider', $publications_slider);
        if (count($publications_slider))
            $publications = ORM::factory('Publication')->where('title_' . I18n::$lang,'<>','')->where('published', '=', 1)->and_where('id', 'NOT IN', $publications_slider)->and_where('is_important', '=', 1)->order_by('date', 'DESC')->limit(3)->find_all();
        else
            $publications = ORM::factory('Publication')->where('title_' . I18n::$lang,'<>','')->where('published', '=', 1)->and_where('is_important', '=', 1)->order_by('date', 'DESC')->limit(3)->find_all();
        $this->set('publications', $publications);

        $biography = ORM::factory('Biography')->where('name_' . I18n::$lang,'<>','')->where('published', '=', 1)->where('era', '=', 1)->and_where('category_id', 'NOT IN', array(3,4,6,7,8,15))->and_where('image', '>', 0)->order_by('is_important', 'DESC')->limit(4)->find_all();

        $this->set('biography', $biography);

        $day_in_history = ORM::factory('Calendar')->where('day', '=', date('j'))->and_where('month', '=', date('n'))->order_by('is_important','desc')->limit(2)->find_all();
        $this->set('day_in_history', $day_in_history);

        $debate = ORM::factory('Debate')->where('language','=', $this->language)->where('is_public','=',1)->order_by('date','DESC')->limit(3)->find_all();
        $this->set('debate', $debate);

        $expert_opinions=ORM::factory('Expert_Opinion')->where('title_' . I18n::$lang,'<>','')->order_by('date', 'desc')->limit(3)->find_all();
        $this->set('expert_opinions', $expert_opinions);

        $video = ORM::factory('Video')->where('published','=',1)->and_where('language','=',$this->language)->order_by('date','desc')->limit(3)->find_all();
        $this->set('video', $video);

        $audio = ORM::factory('Audio')->where('published','=',1)->and_where('is_important', '=', 1)->and_where('show_'.I18n::$lang, '=', 1)->order_by('title')->limit(3)->find_all();
        $this->set('audio', $audio);

        $infographs = ORM::factory('Infograph')->where('published','=',1)->where('language', '=', $this->language)->limit(3)->find_all();
        $this->set('infographs', $infographs);

        $photosets = ORM::factory('Photoset')->where('published','=',1)->where('is_important','=',1)->where('show_'.$this->language, '=', 1)->order_by('date', 'DESC')->limit(3)->find_all();
        $photo = array();
        foreach ($photosets as $i=>$album)
        {
            $photo[$i]['id'] = $album->id;
            $photo[$i]['name'] = $album->name;
            $photo[$i]['date'] = $album->date;
            $attach = $album->attach->where('main', '=', '1')->find();
            $photo[$i]['file_path'] = $attach->photo->file_path;
        }
        $this->set('photosets', $photo);

        $this->set('cities', ORM::factory('City')->find_all());

        $briefings = ORM::factory('Briefing')->where('title_'.I18n::$lang, '<>', '')->where('published', '=', 1)->order_by('date', 'DESC')->limit(3)->find_all();
        $this->set('briefings', $briefings);

        // BEGIN - хронология событий будет делаться аяксом
        $list_era = ORM::factory('Chronology')->where('lvl', '=', 2)->order_by('lft', 'asc')->find_all(); //список эпох
        $current = 0;
        if (count($list_era)) {
            //список листов для первой эпохи
            $list_list = $list_era[4]->children;
            $current = $list_era[4]->id;
            if (count($list_list)) {
                $list_period = $list_list[0]->children(FALSE, 'ASC', 3);
                $list_event = $list_list[0]->lines->where('title_'.i18n::$lang,'<>','')->order_by('line_x', 'asc')->find_all();
            }
        }
        $this->set('list_era', $list_era)->set('list_period', $list_period)->set('list_event', $list_event)->set('current', $current);
        // $this->metadata->description(__('“Kazakhstan History” portal'));
	$this->metadata->description(__('Official web-portal about the history of Kazakhstan. Historical facts and credible information from the Stone Age to the present day. The whole history of Kazakhstan in one reference'));
// END

    }

    /*
     * Смена версий просмотра обычный - слабовидящие
    */
    public function action_version()
    {
        if (isset($_COOKIE['version_site']))
        {
            setcookie('version_site', 0, time() - 3600,'/');
        }
        else
        {
            setcookie('version_site', 1, time() + 63244800,'/');
        }
        $this->redirect('/');
        //
    }

    /*
     * Меню второгог уровня для слабовидящих
     */
    public function action_menu()
    {

    }
	
	public function action_test()
{
 echo Debug::vars(gd_info());
}
} // End Welcome
