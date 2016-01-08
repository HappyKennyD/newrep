<?php
class Date extends Kohana_Date
{
    public static function ru_date($date, $format = 'j F Y')
    {
        if ($date=='now')
            $date = date($format);
        $ff = str_replace('F', '%\m\o\n\t\h%', $format);
        $date = strtotime($date);

        $result = date($ff, $date);
        $result = str_replace('%month%', __(date('F', $date)), $result);

        return $result;
    }

    public static function translate($date, $format = 'j F Y')
    {
        $months['ru'] = array('', 'Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря');
        $months['kz'] = array('', 'Қаңтар', 'Ақпан', 'Наурыз', 'Сәуір', 'Мамыр', 'Маусым', 'Шілде', 'Тамыз', 'Қыркүйек', 'Қазан', 'Қараша', 'Желтоқсан');
        $months['en'] = array('', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        if ($date=='now') { $date = date($format); }
        $ff = str_replace('F', '%\m\o\n\t\h%', $format);
        $date = strtotime($date);
        $result = date($ff, $date);
        $result = str_replace('%month%', $months[I18n::$lang][date('n', $date)], $result);
        return $result;
    }
}