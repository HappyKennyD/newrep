<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Rss extends Controller_Core
{
    public function action_index()
    {
        $info = array(
            'title'       => __($this->metadata->title),
            'pubDate'     => date('r'),
            'description' => $this->metadata->description,
            'language'    => I18n::lang(),
            'ttl'         => 60,
        );

        $news = ORM::factory('Publication')->where('published', '=', 1)->order_by('date', 'desc')->limit(40)->find_all();
        $items = array();
        foreach($news as $item)
        {
            $items[] = array(
                'title' => $item->title,
                'link'  => Url::site('publications/view/'.$item->id, true),
                'description' => strip_tags($item->desc),
                'pubDate' => date('r', strtotime($item->date))
            );
        }
        header('Content-Type: text/xml; charset=utf-8');
        $xml = Feed::create($info, $items, 'UTF-8');

        echo $xml; die;
    }

    public function action_feed()
    {
        $info = array(
            'title'       => __($this->metadata->title),
            'pubDate'     => date('r'),
            'description' => $this->metadata->description,
            'language'    => I18n::lang(),
            'ttl'         => 60,
        );

        $news = ORM::factory('Publication')->where('published', '=', 1)->order_by('date', 'desc')->limit(40)->find_all();
        $items = array();
        foreach($news as $item)
        {
            $items[] = array(
                'title' => $item->title,
                'description' => strip_tags($item->desc),
                'image' => isset($item->picture->file_path) && $item->picture->file_path ? URL::media($item->picture->file_path, TRUE) : '',
                'content' => strip_tags($item->text),
                'link'  => Url::site('publications/view/'.$item->id, true),
                'pubDate' => date('r', strtotime($item->date))
            );
        }
        header('Content-Type: text/xml; charset=utf-8');
        $xml = Feed::create($info, $items, 'UTF-8');

        echo $xml; die;
    }
}