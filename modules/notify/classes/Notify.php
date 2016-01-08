<?php defined('SYSPATH') or die('No direct script access.');

class Notify {
    /**
     * @var Notify
     */
    static protected $instance;

    static public function instance()
    {
        if (!isset(self::$instance))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected $messages = array();
    protected $key      = 'notify-messages';
    protected $session;
    public    $autosave = true;

    protected function __construct()
    {
        $this->session  = Session::instance();
        $this->messages = $this->session->get($this->key, array());
    }

    public function save()
    {
        $this->session->set($this->key, $this->messages);

        return $this;
    }

    public function add($message, $type = 'info')
    {
        $this->messages[] = array('message' => $message, 'type' => $type);
        if ($this->autosave)
        {
            $this->save();
        }
        return $this;
    }

    public function get($default = '')
    {
        return isset($this->messages[0])?$this->messages[0]:$default;
    }

    public function get_all()
    {
        return $this->messages;
    }

    public function get_once($default = '')
    {
        $var = array_pop($this->messages);

        return ($var == null)?$default:$var;
    }

    public function get_all_once()
    {
        $m = $this->messages;
        $this->messages = array();
        $this->save();

        return $m;
    }

    public function flush_all()
    {
        $this->messages = array();
        $this->save();

        return $this;
    }

    public function __destruct()
    {
        $this->save();
    }
}