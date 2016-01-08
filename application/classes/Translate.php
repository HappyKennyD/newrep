<?php defined('SYSPATH') or die('No direct script access.');

class Translate
{
    protected static $dictionary = array(
        'а' => 'a',
        'ә' => 'e',
        'б' => 'b',
        'ц' => 'ts',
        'ч' => 'ch',
        'д' => 'd',
        'ё' => 'yo',
        'е' => 'e',
        'ф' => 'f',
        'г' => 'g',
        'ғ' => 'gh',
        'һ' => 'h',
        'х' => 'h',
        'ы' => 'i',
        'ж' => 'j',
        'к' => 'k',
        'қ' => 'q',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'ң' => 'n',
        'о' => 'o',
        'ө' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'ш' => 'sh',
        'щ' => 'tsh',
        'т' => 't',
        'у' => 'u',
        'ү' => 'yu',
        'в' => 'v',
        'ұ' => 'yi',
        'й' => 'i',
        'з' => 'z',
        'э' => 'e',
        'и' => 'i',
        'і' => 'i',
        'ю' => 'yu',
        'я' => 'ya',
        ' ' => '-',
        'a' => 'a', 'b' => 'b', 'c' => 'c', 'd' => 'd', 'e' => 'e', 'f' => 'f', 'g' => 'g', 'h' => 'h', 'i' => 'i', 'j' => 'j', 'k' => 'k', 'l' => 'l', 'm' => 'm', 'n' => 'n', 'o' => 'o', 'p' => 'p', 'q' => 'q', 'r' => 'r', 's' => 's', 't' => 't', 'u' => 'u', 'v' => 'v', 'w' => 'w', 'x' => 'x', 'y' => 'y', 'z' => 'z',
        '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '0' => '0',
    );

    static public function url($text)
    {
        mb_regex_encoding(Kohana::$charset);
        mb_internal_encoding(Kohana::$charset);
        $text = trim($text);
        $text = mb_strtolower($text);
        //$keys = array_keys(self::$dictionary);
        $length = mb_strlen($text);
        $result = '';
        for($i = 0; $i <= $length; $i++)
        {
            $letter = mb_substr($text, $i, 1);
            if (array_key_exists($letter, self::$dictionary))
            {
                $result.=self::$dictionary[$letter];
            }
        }
        return $result;
    }
}