<?php
class URL extends Kohana_URL
{
    public static $language = '';

    public static function site($uri = '', $protocol = NULL, $index = TRUE)
    {
        if (!empty(static::$language))
        {
            $uri = static::$language . '/' . $uri;
        }
        return parent::site($uri, $protocol, $index);
    }

    public static function media($uri = '', $protocol = NULL, $index = TRUE)
    {
        return parent::site($uri, $protocol, $index);
    }

    public static function current($protocol = null, $index = true)
    {
        $uri = Request::initial()->uri();
        return parent::site($uri, $protocol, $index);
    }

    public static function canonical()
    {
        $link = explode("/",URL::current(true));
        $i=0;
        $uri='';
        for ($i=0;$i<=count($link)-2;$i++)
        {
            if ($i!=count($link)-2)
            {
                $uri .= $link[$i].'/';
            }
            else
            {
                $uri .= $link[$i];
            }

        }
        return $uri;
    }
}