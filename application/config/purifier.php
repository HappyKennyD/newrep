<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'finalize' => TRUE,
	'preload'  => FALSE,
	'settings' => array(
		/**
		 * Use the application cache for HTML Purifier
		 */
	    'Cache.SerializerPath' => APPPATH.'cache',
        'AutoFormat.RemoveSpansWithoutAttributes' => true,
        'HTML.SafeIframe' => true,
        'URI.SafeIframeRegexp' => '%^(http:|https:)?//(www.)?(youtube.com/embed/|slideshare.net/slideshow/)%'
	),
);
