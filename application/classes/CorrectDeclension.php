<?php defined('SYSPATH') or die('No direct script access.');

class CorrectDeclension
{
    public static function give($num, $str1 = '1комментарий', $str2 = '2комментария', $str5 = '5комментариев', $str21 = '21комментарий')
    {
        $val = $num % 100;

        if ($val > 10 && $val < 20) {
            return $str5;
        } else {
            $val = $num % 10;
            if ($val == 1) {
                if ($num == 1) {
                    return $str1;
                } else {
                    return $str21;
                }

            } elseif ($val > 1 && $val < 5) {
                return $str2;
            } else {
                return $str5;
            }
        }
    }
}