<?php defined('SYSPATH') or die('No direct script access.');

return array(
    /**
     * image_directory is the directory that will be prepended to image path in url,
     * to calculate correct filesystem path to manipulated image, used in Imagify_Controller
     */
    'image_directory' 	=> realpath(APPPATH . '../'),

    /**
     * save_directory is used, if processed image caching is enabled, to exclude php from
     * future processing. Caution! Already cached images won't change if you change image keeping it's name!
     * Also keep in mind that it should match Imagify route to work correctly. Used in Imagify_Controller
     */
    'save_directory'  	=> realpath(APPPATH . '../images/'),

    /**
     * use processed images cache? Used in Imagify_Controller
     */
    'cache'             => true,

    /**
     * presets_only is useful in production enviroment, forbidding heavy server load from resizing images with unexpected or multiple dimensions
     * allowed presets are listed in allowed_presets option. Shorthand and keyword preset syntax is also supported.
     * It's reasonable to have it disabled while developing application.
     */
    'presets_only' 	    => false,
    'allowed_presets' 	=> array(
		'thumbs',
		'w200-h200-sa',
        'w500'
		),
    /**
     * keyword assosiated presets, you can use keyword instead of preset, thus allowing to make quick changes in site design
     */
    'keywords'          => array(
        'thumbs'    => 'w100-h150-sa-ccc',
        'page'      => 'w500',
    )
);