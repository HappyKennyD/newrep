<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Twig loader.
 *
 * @package  Kotwig
 * @author   John Heathco <jheathco@gmail.com>
 */
class Kohana_Kotwig {

	/**
	 * @var  object  Kotwig instance
	 */
	public static $instance;
	
	/**
	 * @var  Twig_Environment
	 */
	public $twig;

	/**
	 * @var  object  Kotwig configuration (Kohana_Config object)
	 */
	public $config;

	public static function instance()
	{
		if ( ! Kotwig::$instance)
		{
			Kotwig::$instance = new static();
			
			// Load Twig configuration
			Kotwig::$instance->config = Kohana::$config->load('kotwig');

			// Create the the loader
            $views = Kohana::include_paths();
            $look_in = array();
            foreach ($views as $key => $view)
            {
                $dir = $view.Kotwig::$instance->config->templates;
                if (is_dir($dir))
                {
                    $look_in[] = $dir;
                }

            }
			$loader = new Twig_Loader_Filesystem($look_in);

			// Set up Twig
			Kotwig::$instance->twig = new Twig_Environment($loader, Kotwig::$instance->config->environment);

            foreach (Kotwig::$instance->config->extensions as $extension)
            {
                // Load extensions
                Kotwig::$instance->twig->addExtension(new $extension);
            }

            foreach (Kotwig::$instance->config->globals as $global => $object)
            {
                // Load globals
                Kotwig::$instance->twig->addGlobal($global, $object);
            }

            foreach (Kotwig::$instance->config->filters as $filter => $object)
            {
                // Load filters
                Kotwig::$instance->twig->addFilter($filter, $object);
            }

        }

		return Kotwig::$instance;
	}

	final private function __construct()
	{
		// This is a singleton class
	}

} // End Kotwig
