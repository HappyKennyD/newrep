<?php defined('SYSPATH') or die('No direct script access.');

class TimeDifference
{
    public static function give($date,$lifetime=0)
    {
        $date=date('Y-m-d H:i:s', strtotime("+" . $lifetime . " hours", strtotime($date)));
        $difference = strtotime($date) - time();
        $array = array();
        $array['day'] = floor($difference / 86400);
        $difference -= $array['day'] * 86400;
        $array['hour'] = floor($difference / 3600);
        $difference -= $array['hour'] * 3600;
        $array['minute'] = ceil($difference / 60);
        return $array;
    }
}