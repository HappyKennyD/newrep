<?php
class Application
{
    /**
     * @var Application
     */
    protected static $instance;

    /**
     * @return Application
     */
    public static function instance()
    {
        if (!isset(static::$instance))
        {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = Kohana::$config->load('application.'.$key);
        return isset($value)?$value:$default;
    }
}