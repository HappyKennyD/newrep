<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Twig template controller
 *
 * @package    Kotwig
 * @author     John Heathco <jheathco@gmail.com>
 */
abstract class Kohana_Controller_Kotwig extends Controller {

	/**
	 * @var Kotwig_View  Kohana twig template
	 */
	protected $template = NULL;
    protected $default_template = '';
    protected $vars = array();

    public function set($name, $value = null)
    {
        if (is_array($name))
        {
            Arr::merge($this->vars, $name);
        }
        elseif (is_string($name))
        {
            $this->vars[$name] = $value;
        }

        return $this;
    }

    public function get($name, $default = null)
    {
        return (isset($this->vars[$name])?$this->vars[$name]:$default);
    }

    public function bind($name, &$variable)
    {
        $this->vars[$name] =& $variable;

        return $this;
    }
	
	public function before()
	{
        $this->default_template = $this->request->controller().'/'.$this->request->action();

        if ($this->request->directory())
        {
            // Preprend directory if needed
            $this->default_template = $this->request->directory().'/'.$this->default_template;
        }

        $this->template = Kotwig_View::factory();
        Kotwig_View::set_global('_request', $this->request);
        Kotwig_View::set_global('_response', $this->response);
    }
	
	public function after()
	{
        if ($this->template->get_filename() == null)
        {
            $this->template->set_filename(strtolower($this->default_template));
        }
        $this->template->set($this->vars);
        $this->response->body($this->template->render());
	}

} // End Controller_Kotwig