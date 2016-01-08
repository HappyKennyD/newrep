<?php defined('SYSPATH') or die('No direct script access.');

class CommentsUrl
{
    public static function giveurl($object_id,$table)
    {
        switch($table)
        {
            case 'briefing': $str="/ru/briefings/view/$object_id";break;
            case 'video': $str="/ru/video/view/$object_id";break;
            case 'book': $str="/ru/books/view/$object_id";break;
            case 'publications': $str="/ru/publications/view/$object_id";break;
            case 'pages_contents': $str="/ru/contents/view/$object_id";break;

            default: $str="Не удалось сгенерировать ссылку на страницу комментария";
        }
    return $str;
    }
}