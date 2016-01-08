<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Manage_Report extends Controller_Manage_Core
{

    public function action_index()
    {
        $start = Arr::get($_POST, 'start', date('Y-m-d 00:00:00'));
        $end   = Arr::get($_POST, 'end', date('Y-m-d 23:59:59'));

        $logs = ORM::factory('Log')->where('date', '>=', $start)->and_where('date', '<=', $end)->find_all();

        $data = array();

        $models = array(
            'Model_Publication'     => __('Publications'),
            'Model_News'            => __('News'),
            'Model_Pages_Content'   => __('Pages'),
            'Model_Expert_Opinion'  => __('Expert opinions'),
            'Model_Biography'       => __('Biographies'),
            'Model_Briefing'        => __('Briefings'),
            'Model_Calendar'        => __('Calendar'),
            'Model_Chronology_Line' => __('Chronology'),
            'Model_Video'           => __('Video'),
            'Model_Audio'           => __('Audio'),
            'Model_Photoset'        => __('Photosets'),
            'Model_Point'           => __('Point'),
            'Model_Infograph'       => __('Infographics'),
            'Model_Slider'          => __('Slider'),
            'Model_Book'            => __('Library'),
            'Model_Debate'            => __('Debate'),
        );

        $all   = array();
        $today = array();
        foreach ($models as $model => $garbage)
        {
            $e = explode('_', $model);
            array_shift($e);
            $count         = ORM::factory(implode('_', $e))->count_all();
            $all[$model]   = $count;
            $today[$model] = 0;
        }
        $this->set('all', $all);

        foreach ($logs as $log)
        {
            $user  = ORM::factory('User', $log->user_id);
            $model = $log->model;
            $u     = $user->email . ', ' . $user->username;
            if (isset($data[$model][$u]['count']))
            {
                $data[$model][$u]['count']++;
            }
            else
            {
                $data[$model][$u]['count'] = 1;
            }

            if (isset($data[$model][$u]['words']))
            {
                $data[$model][$u]['words'] = $data[$model][$u]['words'] + $log->count;
            }
            else
            {
                $data[$model][$u]['words'] = $log->count;
            }

            $today[$model]++;

            if ($model == 'Model_Publication')
            {
                $publication                 = ORM::factory('Publication', $log->content_id);
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/publications/publication/view/' . $publication->id) . '">' . $publication->title . '</a>';
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($publication->loaded()) ? 1 : 0;
            }
            if ($model == 'Model_News')
            {
                $news                        = ORM::factory('News', $log->content_id);
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/publications/news/view/' . $news->id) . '">' . $news->title . '</a>';
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($news->loaded()) ? 1 : 0;
            }
            if ($model == 'Model_Pages_Content')
            {
                $content                     = ORM::factory('Pages_Content', $log->content_id);
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/contents/show/' . $content->id) . '">' . $content->title . '</a>';
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($content->loaded()) ? 1 : 0;
            }
            if ($model == 'Model_Expert_Opinion')
            {
                $opinion                     = ORM::factory('Expert_Opinion', $log->content_id);
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/expertopinions/view/' . $opinion->id) . '">' . $opinion->title . '</a>';
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($opinion->loaded()) ? 1 : 0;

            }

            if ($model == 'Model_Debate')
            {
                $debate                     = ORM::factory('Debate', $log->content_id);
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/debate/edit/' . $debate->id) . '">' . $debate->title . '</a>';
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($debate->loaded()) ? 1 : 0;

            }
            if ($model == 'Model_Biography')
            {
                $biography                   = ORM::factory('Biography', $log->content_id);
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/biography/view/' . $biography->id) . '">' . $biography->name . '</a>';
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($biography->loaded()) ? 1 : 0;
            }
            if ($model == 'Model_Briefing')
            {
                $briefing                    = ORM::factory('Briefing', $log->content_id);
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/briefings/view/' . $briefing->id) . '">' . $briefing->title . '</a>';
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($briefing->loaded()) ? 1 : 0;

            }
            if ($model == 'Model_Calendar')
            {
                $calendar                    = ORM::factory('Calendar', $log->content_id);
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/calendar/view/' . $calendar->id) . '">' . $calendar->title . '</a>';
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($calendar->loaded()) ? 1 : 0;
            }
            if ($model == 'Model_Chronology_Line')
            {
                $line                        = ORM::factory('Chronology_Line', $log->content_id);
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/chronology/edit/' . $line->id) . '">' . $line->title . '</a>';
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($line->loaded()) ? 1 : 0;

            }
            if ($model == 'Model_Video')
            {
                $video                       = ORM::factory('Video', $log->content_id);
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/video/view/' . $video->id) . '">' . $video->title . '</a>';
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($video->loaded()) ? 1 : 0;

            }
            if ($model == 'Model_Audio')
            {
                $audio                       = ORM::factory('Audio', $log->content_id);
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/audio/edit/' . $audio->id) . '">' . $audio->title . '</a>';
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($audio->loaded()) ? 1 : 0;
            }
            if ($model == 'Model_Photoset')
            {
                $photoset                    = ORM::factory('Photoset', $log->content_id);
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/photosets/view/' . $photoset->id) . '">' . $photoset->name . '</a>';
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($photoset->loaded()) ? 1 : 0;
            }
            if ($model == 'Model_Point')
            {
                $point                       = ORM::factory('Point', $log->content_id);
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/point/edit/' . $point->id) . '">' . $point->name . '</a>';
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($point->loaded()) ? 1 : 0;

            }
            if ($model == 'Model_Infograph')
            {
                $infograph                   = ORM::factory('Infograph', $log->content_id);
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/infographs/view/' . $infograph->id) . '">' . $infograph->title . '</a>';
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($infograph->loaded()) ? 1 : 0;

            }
            if ($model == 'Model_Slider')
            {
                $slider                      = ORM::factory('Slider', $log->content_id);
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/sliders/edit/' . $slider->id . '?type=slider') . '">' . $slider->title . '</a>';
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($slider->loaded()) ? 1 : 0;
            }
            if ($model == 'Model_Book')
            {
                $book                        = ORM::factory('Book', $log->content_id);
                $data[$model][$u]['pubs'][]  = '<a target="_blank" href="' . URL::media('manage/library/show/' . $book->id) . '">' . $book->title . '</a>';
                $data[$model][$u]['event'][] = $log->event;
                $data[$model][$u]['title'][] = $log->title;
                $data[$model][$u]['has'][]   = ($book->loaded()) ? 1 : 0;
            }
        }

        $this->set('today', $today);
        $this->set('data', $data)->set('models', $models)->set('dates', array('start' => date('d.m.Y', strtotime($start)), 'end' => date('d.m.Y', strtotime($end))));
    }

}
